<?php
/*
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC. All rights reserved.                        |
 |                                                                    |
 | This work is published under the GNU AGPLv3 license with some      |
 | permitted exceptions and without any warranty. For full license    |
 | and copyright information, see https://civicrm.org/licensing       |
 +--------------------------------------------------------------------+
 */

use Civi\Api4\ContributionRecur;

/**
 * Class CRM_Core_Payment_PayPalProIPNTest
 * @group headless
 */
class CRM_Core_Payment_PayPalIPNTest extends CiviUnitTestCase {
  protected $_contributionID;
  protected $_financialTypeID = 1;
  protected $_contactID;
  protected $_contributionRecurID;
  protected $_contributionPageID;
  protected $_paymentProcessorID;
  protected $_customFieldID;

  /**
   * Should financials be checked after the test but before tear down.
   *
   * Ideally all tests (or at least all that call any financial api calls ) should do this but there
   * are some test data issues and some real bugs currently blockinng.
   *
   * @var bool
   */
  protected $isValidateFinancialsOnPostAssert = TRUE;

  /**
   * Set up function.
   */
  public function setUp(): void {
    parent::setUp();
    $this->_paymentProcessorID = $this->paymentProcessorCreate(['is_test' => 0, 'payment_processor_type_id' => 'PayPal_Standard']);
    $this->_contactID = $this->individualCreate();
    $contributionPage = $this->callAPISuccess('ContributionPage', 'create', [
      'title' => 'Test Contribution Page',
      'financial_type_id' => $this->_financialTypeID,
      'currency' => 'USD',
      'payment_processor' => $this->_paymentProcessorID,
    ]);
    $this->_contributionPageID = $contributionPage['id'];
  }

  /**
   * Tear down function.
   */
  public function tearDown(): void {
    $this->quickCleanUpFinancialEntities();
    $this->quickCleanup(['civicrm_campaign']);
    parent::tearDown();
  }

  /**
   * Test IPN response updates contribution and invoice is attached in mail reciept
   *
   * The scenario is that a pending contribution exists and the IPN call will update it to completed.
   * And also if Tax and Invoicing is enabled, this unit test ensure that invoice pdf is attached with email recipet
   *
   * @throws \CRM_Core_Exception
   */
  public function testInvoiceSentOnIPNPaymentSuccess(): void {
    $this->enableTaxAndInvoicing();

    $pendingStatusID = CRM_Core_PseudoConstant::getKey('CRM_Contribute_BAO_Contribution', 'contribution_status_id', 'Pending');
    $completedStatusID = CRM_Core_PseudoConstant::getKey('CRM_Contribute_BAO_Contribution', 'contribution_status_id', 'Completed');
    $params = [
      'payment_processor_id' => $this->_paymentProcessorID,
      'contact_id' => $this->_contactID,
      'trxn_id' => NULL,
      'invoice_id' => 'xyz',
      'contribution_status_id' => $pendingStatusID,
      'is_email_receipt' => TRUE,
    ];
    $this->_contributionID = $this->contributionCreate($params);
    $contribution = $this->callAPISuccess('contribution', 'get', ['id' => $this->_contributionID, 'sequential' => 1]);
    // assert that contribution created before handling payment via paypal standard has no transaction id set and pending status
    $this->assertEquals(NULL, $contribution['values'][0]['trxn_id']);
    $this->assertEquals($pendingStatusID, $contribution['values'][0]['contribution_status_id']);

    global $_REQUEST;
    $_REQUEST = ['q' => CRM_Utils_System::url('civicrm/payment/ipn/' . $this->_paymentProcessorID)] + $this->getPaypalTransaction();

    $mut = new CiviMailUtils($this, TRUE);
    $paymentProcesors = $this->callAPISuccessGetSingle('PaymentProcessor', ['id' => $this->_paymentProcessorID]);
    $payment = Civi\Payment\System::singleton()->getByProcessor($paymentProcesors);
    $payment->handlePaymentNotification();

    // Check if invoice pdf is attached with contribution mail reciept
    $mut->checkMailLog([
      'Content-Transfer-Encoding: base64',
      'Content-Type: application/pdf',
      'filename=Invoice.pdf',
    ]);
    $mut->stop();

    $contribution = $this->callAPISuccess('contribution', 'get', ['id' => $this->_contributionID, 'sequential' => 1]);
    // assert that contribution is completed after getting response from paypal standard which has transaction id set and completed status
    $this->assertEquals($_REQUEST['txn_id'], $contribution['values'][0]['trxn_id']);
    $this->assertEquals($completedStatusID, $contribution['values'][0]['contribution_status_id']);
  }

  /**
   * Test IPN response updates contribution_recur & contribution for first &
   * second contribution.
   *
   * The scenario is that a pending contribution exists and the first call will
   * update it to completed. The second will create a new contribution.
   *
   * @throws \CRM_Core_Exception
   * @throws \Civi\API\Exception\UnauthorizedException
   */
  public function testIPNPaymentRecurSuccess(): void {
    $this->setupRecurringPaymentProcessorTransaction([], ['total_amount' => '15.00', 'contribution_page_id' => $this->ids['ContributionPage'][0] ?? NULL]);
    $mut = new CiviMailUtils($this, TRUE);
    $paypalIPN = new CRM_Core_Payment_PayPalIPN($this->getPaypalRecurTransaction());
    $paypalIPN->main();
    $recur = ContributionRecur::get()
      ->addWhere('contact_id', '=', $this->_contactID)
      ->addSelect('contribution_status_id:name')
      ->execute()->first();
    $this->assertEquals('In Progress', $recur['contribution_status_id:name']);
    $mut->checkMailLog(['https://www.sandbox.paypal.com/cgi-bin/webscr?cmd=_subscr-find'], ['civicrm/contribute/unsubscribe', 'civicrm/contribute/updatebilling']);
    $mut->stop();
    $contribution1 = $this->callAPISuccess('Contribution', 'getsingle', ['id' => $this->_contributionID]);
    $this->assertEquals(1, $contribution1['contribution_status_id']);
    $this->assertEquals('8XA571746W2698126', $contribution1['trxn_id']);
    // source gets set by processor
    $this->assertEquals('Online Contribution:', substr($contribution1['contribution_source'], 0, 20));
    $contributionRecur = $this->callAPISuccess('contribution_recur', 'getsingle', ['id' => $this->_contributionRecurID]);
    $this->assertEquals(5, $contributionRecur['contribution_status_id']);
    $paypalIPN = new CRM_Core_Payment_PayPalIPN($this->getPaypalRecurSubsequentTransaction());
    $paypalIPN->main();
    $contributions = $this->callAPISuccess('contribution', 'get', [
      'contribution_recur_id' => $this->_contributionRecurID,
      'sequential' => 1,
    ]);
    $this->assertEquals(2, $contributions['count']);
    $contribution2 = $contributions['values'][1];
    $this->assertEquals('secondone', $contribution2['trxn_id']);
    $paramsThatShouldMatch = [
      'total_amount',
      'net_amount',
      'fee_amount',
      'payment_instrument',
      'payment_instrument_id',
      'financial_type',
      'financial_type_id',
    ];
    foreach ($paramsThatShouldMatch as $match) {
      $this->assertEquals($contribution1[$match], $contribution2[$match]);
    }
  }

  /**
   * Test IPN response updates contribution_recur & contribution for first &
   * second contribution.
   *
   * @throws \CRM_Core_Exception
   */
  public function testIPNPaymentMembershipRecurSuccess(): void {
    $durationUnit = 'year';
    $this->setupMembershipRecurringPaymentProcessorTransaction(['duration_unit' => $durationUnit, 'frequency_unit' => $durationUnit]);
    $this->callAPISuccessGetSingle('membership_payment', []);
    $paypalIPN = new CRM_Core_Payment_PayPalIPN($this->getPaypalRecurTransaction());
    $paypalIPN->main();
    $contribution = $this->callAPISuccess('contribution', 'getsingle', ['id' => $this->_contributionID]);
    $membershipEndDate = $this->callAPISuccessGetValue('membership', ['return' => 'end_date']);
    $this->assertEquals(1, $contribution['contribution_status_id']);
    $this->assertEquals('8XA571746W2698126', $contribution['trxn_id']);
    // source gets set by processor
    $this->assertTrue(substr($contribution['contribution_source'], 0, 20) === "Online Contribution:");
    $contributionRecur = $this->callAPISuccess('contribution_recur', 'getsingle', ['id' => $this->_contributionRecurID]);
    $this->assertEquals(5, $contributionRecur['contribution_status_id']);
    $paypalIPN = new CRM_Core_Payment_PaypalIPN($this->getPaypalRecurSubsequentTransaction());
    $paypalIPN->main();
    $renewedMembershipEndDate = $this->membershipRenewalDate($durationUnit, $membershipEndDate);
    $this->assertEquals($renewedMembershipEndDate, $this->callAPISuccessGetValue('membership', ['return' => 'end_date']));
    $contribution = $this->callAPISuccess('contribution', 'get', [
      'contribution_recur_id' => $this->_contributionRecurID,
      'sequential' => 1,
    ]);
    $this->assertEquals(2, $contribution['count']);
    $this->assertEquals('secondone', $contribution['values'][1]['trxn_id']);
    $this->callAPISuccessGetCount('line_item', [
      'entity_id' => $this->ids['membership'],
      'entity_table' => 'civicrm_membership',
    ], 2);
    $this->callAPISuccessGetSingle('line_item', [
      'contribution_id' => $contribution['values'][1]['id'],
      'entity_table' => 'civicrm_membership',
    ]);
    $this->callAPISuccessGetSingle('membership_payment', ['contribution_id' => $contribution['values'][1]['id']]);
  }

  /**
   * Get IPN style details for an incoming recurring transaction.
   */
  public function getPaypalRecurTransaction(): array {
    return [
      'contactID' => $this->_contactID,
      'contributionID' => $this->_contributionID,
      'invoice' => 'xyz',
      'contributionRecurID' => $this->_contributionRecurID,
      'mc_gross' => '15.00',
      'module' => 'contribute',
      'payer_id' => '4NHUTA7ZUE92C',
      'payment_status' => 'Completed',
      'receiver_email' => 'sunil._1183377782_biz_api1.webaccess.co.in',
      'txn_type' => 'subscr_payment',
      'last_name' => 'Roberts',
      'payment_fee' => '0.63',
      'first_name' => 'Robert',
      'txn_id' => '8XA571746W2698126',
      'residence_country' => 'US',
    ];
  }

  /**
   * Get IPN style details for an incoming paypal standard transaction.
   */
  public function getPaypalTransaction() {
    return [
      'contactID' => $this->_contactID,
      'contributionID' => $this->_contributionID,
      'invoice' => 'xyz',
      'mc_gross' => '100.00',
      'mc_fee' => '5.00',
      'settle_amount' => '95.00',
      'module' => 'contribute',
      'payer_id' => 'FV5ZW7TLMQ874',
      'payment_status' => 'Completed',
      'receiver_email' => 'sunil._1183377782_biz_api1.webaccess.co.in',
      'txn_type' => 'web_accept',
      'last_name' => 'Roberty',
      'payment_fee' => '0.63',
      'first_name' => 'Robert',
      'txn_id' => '8XA571746W2698126',
      'residence_country' => 'US',
      'custom' => json_encode(['cgid' => 'test12345']),
    ];
  }

  /**
   * Get IPN-style details for a second incoming transaction.
   *
   * @return array
   */
  public function getPaypalRecurSubsequentTransaction() {
    return array_merge($this->getPaypalRecurTransaction(), ['txn_id' => 'secondone']);
  }

  /**
   * Test IPN response updates contribution and invoice is attached in mail reciept
   * Test also AlterIPNData intercepts at the right point and allows for custom processing
   * The scenario is that a pending contribution exists and the IPN call will update it to completed.
   * And also if Tax and Invoicing is enabled, this unit test ensure that invoice pdf is attached with email recipet
   */
  public function testhookAlterIPNDataOnIPNPaymentSuccess() {

    $pendingStatusID = CRM_Core_PseudoConstant::getKey('CRM_Contribute_BAO_Contribution', 'contribution_status_id', 'Pending');
    $completedStatusID = CRM_Core_PseudoConstant::getKey('CRM_Contribute_BAO_Contribution', 'contribution_status_id', 'Completed');
    $params = [
      'payment_processor_id' => $this->_paymentProcessorID,
      'contact_id' => $this->_contactID,
      'trxn_id' => NULL,
      'invoice_id' => 'xyz',
      'contribution_status_id' => $pendingStatusID,
      'is_email_receipt' => TRUE,
    ];
    $this->_contributionID = $this->contributionCreate($params);
    $this->createCustomField();
    $contribution = $this->callAPISuccessGetSingle('contribution', ['id' => $this->_contributionID]);
    // assert that contribution created before handling payment via paypal standard has no transaction id set and pending status
    $this->assertEquals(NULL, $contribution['trxn_id']);
    $this->assertEquals($pendingStatusID, $contribution['contribution_status_id']);
    $this->hookClass->setHook('civicrm_postIPNProcess', [$this, 'hookCiviCRMAlterIPNData']);
    global $_REQUEST;
    $_REQUEST = ['q' => CRM_Utils_System::url('civicrm/payment/ipn/' . $this->_paymentProcessorID)] + $this->getPaypalTransaction();

    $mut = new CiviMailUtils($this, TRUE);
    CRM_Core_Payment::handlePaymentMethod('PaymentNotification', ['processor_id' => $this->_paymentProcessorID]);

    $contribution = $this->callAPISuccessGetSingle('Contribution', ['id' => $this->_contributionID, 'sequential' => 1]);
    // assert that contribution is completed after getting response from paypal standard which has transaction id set and completed status
    $this->assertEquals($_REQUEST['txn_id'], $contribution['trxn_id']);
    $this->assertEquals($completedStatusID, $contribution['contribution_status_id']);
    $this->assertEquals('test12345', $contribution['custom_' . $this->_customFieldID]);
  }

  /**
   * Allow IPNs to validate when the supplied contact_id has been deleted from the database but there is a valid contact id in the contribution recur object or contribution object
   */
  public function testPayPalIPNSuccessDeletedContact() {
    $contactTobeDeleted = $this->individualCreate();
    $pendingStatusID = CRM_Core_PseudoConstant::getKey('CRM_Contribute_BAO_Contribution', 'contribution_status_id', 'Pending');
    $completedStatusID = CRM_Core_PseudoConstant::getKey('CRM_Contribute_BAO_Contribution', 'contribution_status_id', 'Completed');
    $params = [
      'payment_processor_id' => $this->_paymentProcessorID,
      'contact_id' => $this->_contactID,
      'trxn_id' => NULL,
      'invoice_id' => 'xyz',
      'contribution_status_id' => $pendingStatusID,
      'is_email_receipt' => TRUE,
    ];
    $this->_contributionID = $this->contributionCreate($params);
    $contribution = $this->callAPISuccess('contribution', 'get', ['id' => $this->_contributionID, 'sequential' => 1]);
    // assert that contribution created before handling payment via paypal standard has no transaction id set and pending status
    $this->assertEquals(NULL, $contribution['values'][0]['trxn_id']);
    $this->assertEquals($pendingStatusID, $contribution['values'][0]['contribution_status_id']);
    $payPalIPNParams = $this->getPaypalTransaction();
    $payPalIPNParams['contactID'] = $contactTobeDeleted;
    $this->callAPISuccess('Contact', 'delete', ['id' => $contactTobeDeleted, 'skip_undelete' => 1]);
    global $_REQUEST;
    $_REQUEST = ['q' => CRM_Utils_System::url('civicrm/payment/ipn/' . $this->_paymentProcessorID)] + $payPalIPNParams;
    // Now process the IPN noting that the contact id that was supplied with the IPN has been deleted but there is still a valid one on the contribution id
    $payment = CRM_Core_Payment::handlePaymentMethod('PaymentNotification', ['processor_id' => $this->_paymentProcessorID]);

    $contribution = $this->callAPISuccess('contribution', 'get', ['id' => $this->_contributionID, 'sequential' => 1]);
    // assert that contribution is completed after getting response from paypal standard which has transaction id set and completed status
    $this->assertEquals($_REQUEST['txn_id'], $contribution['values'][0]['trxn_id']);
    $this->assertEquals($completedStatusID, $contribution['values'][0]['contribution_status_id']);
  }

  /**
   * Store Custom data passed in from the PayPalIPN in a custom field
   */
  public function hookCiviCRMAlterIPNData($data) {
    if (!empty($data['custom'])) {
      $customData = json_decode($data['custom'], TRUE);
      $customField = $this->callAPISuccess('custom_field', 'get', ['label' => 'TestCustomFieldIPNHook']);
      $this->callAPISuccess('contribution', 'create', ['id' => $this->_contributionID, 'custom_' . $customField['id'] => $customData['cgid']]);
    }
  }

  /**
   * @return array
   */
  protected function createCustomField() {
    $customGroup = $this->customGroupCreate(['extends' => 'Contribution']);
    $fields = [
      'label' => 'TestCustomFieldIPNHook',
      'data_type' => 'String',
      'html_type' => 'Text',
      'custom_group_id' => $customGroup['id'],
    ];
    $field = CRM_Core_BAO_CustomField::create($fields);
    $this->_customFieldID = $field->id;
    return $customGroup;
  }

}
