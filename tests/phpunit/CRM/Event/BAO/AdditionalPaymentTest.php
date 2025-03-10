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

use Civi\Api4\EntityFinancialAccount;

/**
 * Class CRM_Event_BAO_AdditionalPaymentTest
 *
 * @group headless
 */
class CRM_Event_BAO_AdditionalPaymentTest extends CiviUnitTestCase {

  /**
   * Contact ID.
   *
   * @var int
   */
  protected $contactID;

  /**
   * Event ID.
   *
   * @var int
   */
  protected $eventID;

  /**
   * Set up.
   *
   * @throws \CRM_Core_Exception
   */
  public function setUp(): void {
    parent::setUp();
    $this->contactID = $this->individualCreate();
    $event = $this->eventCreate();
    $this->eventID = $event['id'];
  }

  /**
   * Cleanup after test.
   *
   * @throws \CRM_Core_Exception
   */
  public function tearDown(): void {
    $this->eventDelete($this->eventID);
    $this->quickCleanUpFinancialEntities();
    parent::tearDown();
  }

  /**
   * Check that all tests that have created payments have created them with the right financial entities.
   *
   * Ideally this would be on CiviUnitTestCase but many classes would still fail. Also, it might
   * be good if it only ran on tests that created at least one contribution.
   *
   * @throws \CRM_Core_Exception
   */
  protected function assertPostConditions(): void {
    $this->validateAllPayments();
    $this->validateAllContributions();
  }

  /**
   * Helper function to record participant with paid contribution.
   *
   * @param int $feeTotal
   * @param int $actualPaidAmt
   * @param array $participantParams
   * @param array $contributionParams
   *
   * @return array
   * @throws \CRM_Core_Exception
   */
  protected function addParticipantWithPayment($feeTotal, $actualPaidAmt, $participantParams = [], $contributionParams = []) {
    $priceSetId = $this->eventPriceSetCreate($feeTotal);
    CRM_Price_BAO_PriceSet::addTo('civicrm_event', $this->eventID, $priceSetId);
    // -- processing priceSet using the BAO
    $lineItems = [];
    $priceSet = CRM_Price_BAO_PriceSet::getSetDetail($priceSetId, TRUE, FALSE);
    $priceSet = $priceSet[$priceSetId] ?? NULL;
    $feeBlock = $priceSet['fields'] ?? NULL;
    $params['price_2'] = $feeTotal;
    $tempParams = $params;

    CRM_Price_BAO_PriceSet::processAmount($feeBlock,
      $params, $lineItems
    );
    foreach ($lineItems as $lineItemID => $lineItem) {
      $lineItems[$lineItemID]['entity_table'] = 'civicrm_participant';
    }

    $participantParams = array_merge(
      [
        'send_receipt' => 1,
        'is_test' => 0,
        'is_pay_later' => 0,
        'event_id' => $this->eventID,
        'register_date' => date('Y-m-d') . " 00:00:00",
        'role_id' => 1,
        'status_id' => 14,
        'source' => 'Event_' . $this->eventID,
        'contact_id' => $this->contactID,
        'note' => 'Note added for Event_' . $this->eventID,
        'fee_level' => 'Price_Field - 55',
      ],
      $participantParams
    );

    // create participant contribution with partial payment
    $contributionParams = array_merge(
      [
        'total_amount' => $feeTotal,
        'source' => 'Fall Fundraiser Dinner: Offline registration',
        'currency' => 'USD',
        'receipt_date' => 'today',
        'contact_id' => $this->contactID,
        'financial_type_id' => 4,
        'payment_instrument_id' => 4,
        'contribution_status_id' => 'Pending',
        'receive_date' => 'today',
        'api.Payment.create' => ['total_amount' => $actualPaidAmt],
        'line_items' => [['line_item' => $lineItems, 'params' => $participantParams]],
      ],
      $contributionParams
    );

    $contribution = $this->callAPISuccess('Order', 'create', $contributionParams);
    $participant = $this->callAPISuccessGetSingle('participant', []);
    $this->callAPISuccessGetSingle('ParticipantPayment', ['contribution_id' => $contribution['id'], 'participant_id' => $participant['id']]);

    return [
      'participant' => $participant,
      'contribution' => $this->callAPISuccessGetSingle('Contribution', ['id' => $contribution['id']]),
      'lineItem' => $lineItems,
      'params' => $tempParams,
      'feeBlock' => $feeBlock,
      'priceSetId' => $priceSetId,
    ];
  }

  /**
   * See https://lab.civicrm.org/dev/core/issues/153
   *
   * @throws \CRM_Core_Exception
   */
  public function testPaymentWithCustomPaymentInstrument() {
    $feeAmt = 100;
    $amtPaid = 0;

    // Create undetermined Payment Instrument
    $paymentInstrumentID = $this->createPaymentInstrument(['label' => 'Undetermined'], 'Accounts Receivable');

    // record pending payment for an event
    $result = $this->addParticipantWithPayment(
      $feeAmt, $amtPaid,
      ['is_pay_later' => 1],
      [
        'total_amount' => 100,
        'payment_instrument_id' => $paymentInstrumentID,
        'contribution_status_id' => CRM_Core_PseudoConstant::getKey('CRM_Contribute_BAO_Contribution', 'contribution_status_id', 'Pending'),
      ]
    );
    $contributionID = $result['contribution']['id'];

    // check payment info
    $paymentInfo = CRM_Contribute_BAO_Contribution::getPaymentInfo($result['participant']['id'], 'event');
    $this->assertEquals($feeAmt, $this->callAPISuccessGetValue('Contribution', ['return' => 'total_amount', 'id' => $contributionID]), 'Total amount recorded is not correct');
    $this->assertEquals($amtPaid, round($paymentInfo['paid']), 'Amount paid is not correct');
    $this->assertEquals($feeAmt, CRM_Contribute_BAO_Contribution::getContributionBalance($contributionID), 'Balance is not correct');
    $this->assertEquals('Pending Label**', $paymentInfo['contribution_status'], 'Contribution status is not correct');

    // make additional payment via 'Record Payment' form
    $form = new CRM_Contribute_Form_AdditionalPayment();
    $submitParams = [
      'contact_id' => $result['contribution']['contact_id'],
      'contribution_id' => $contributionID,
      'total_amount' => 100,
      'currency' => 'USD',
      'trxn_date' => '2017-04-11 13:05:11',
      'payment_processor_id' => 0,
      'payment_instrument_id' => CRM_Core_PseudoConstant::getKey('CRM_Contribute_BAO_Contribution', 'payment_instrument_id', 'Check'),
      'check_number' => '#123',
    ];
    $form->cid = $result['contribution']['contact_id'];
    $form->testSubmit($submitParams);

    // check payment info again and see if the payment is completed
    $contribution = $this->callAPISuccessGetSingle('Contribution', ['id' => $contributionID]);
    $this->assertEquals($feeAmt, $contribution['total_amount'], 'Total amount recorded is not proper');
    $this->assertEquals($feeAmt, CRM_Core_BAO_FinancialTrxn::getTotalPayments($contributionID), 'Amount paid is not correct');
    $this->assertEquals(CRM_Contribute_BAO_Contribution::getContributionBalance($contributionID), 0, 'Balance amount is not proper');
    $this->assertEquals('Completed', $contribution['contribution_status'], 'Contribution status is not correct');

    $this->callAPISuccess('OptionValue', 'delete', ['id' => $paymentInstrumentID]);
  }

  /**
   * Create Payment Instrument.
   *
   * @param array $params
   * @param string $financialAccountName
   *
   * @return int
   * @noinspection PhpDocMissingThrowsInspection
   */
  protected function createPaymentInstrument(array $params = [], string $financialAccountName = 'Donation'): int {
    $params = array_merge([
      'label' => 'Payment Instrument - new',
      'option_group_id' => 'payment_instrument',
      'is_active' => 1,
    ], $params);
    $newPaymentInstrument = $this->callAPISuccess('OptionValue', 'create', $params)['id'];

    $relationTypeID = key(CRM_Core_PseudoConstant::accountOptionValues('account_relationship', NULL, " AND v.name LIKE 'Asset Account is' "));

    $financialAccountParams = [
      'entity_table' => 'civicrm_option_value',
      'entity_id' => $newPaymentInstrument,
      'account_relationship' => $relationTypeID,
      'financial_account_id' => $this->callAPISuccess('FinancialAccount', 'getValue', ['name' => $financialAccountName, 'return' => 'id']),
    ];
    EntityFinancialAccount::create()->setValues($financialAccountParams)->execute();

    return CRM_Core_PseudoConstant::getKey('CRM_Contribute_BAO_Contribution', 'payment_instrument_id', $params['label']);
  }

  /**
   * @see https://issues.civicrm.org/jira/browse/CRM-13964
   *
   * @throws \CRM_Core_Exception
   */
  public function testAddPartialPayment() {
    // First add an unrelated contribution since otherwise the contribution and
    // participant ids are all 1 so they might match by accident.
    $this->contributionCreate(['contact_id' => $this->individualCreate([], 0, TRUE)]);

    $feeAmt = 100;
    $amtPaid = (float) 60;
    $balance = (float) 40;
    $result = $this->addParticipantWithPayment($feeAmt, $amtPaid);
    $amountPaid = CRM_Core_BAO_FinancialTrxn::getTotalPayments($result['contribution']['id']);
    $contributionBalance = CRM_Contribute_BAO_Contribution::getContributionBalance($result['contribution']['id']);

    $this->assertEquals($feeAmt, $this->callAPISuccess('Contribution', 'getvalue', ['return' => 'total_amount', 'id' => $result['contribution']['id']]), 'Total amount recorded is not correct');
    $this->assertEquals($amtPaid, $amountPaid, 'Amount paid is not correct');
    $this->assertEquals($balance, $contributionBalance, 'Balance is not correct');

    $this->assertEquals('Partially paid', $result['contribution']['contribution_status']);
    $this->assertEquals('Partially paid', $result['participant']['participant_status']);

    // Check the record payment link has the right id and that it doesn't
    // match by accident.
    $this->assertNotEquals($result['contribution']['id'], $result['participant']['id']);
    $paymentInfo = CRM_Contribute_BAO_Contribution::getPaymentInfo($result['participant']['id'], 'event');
    $this->assertEquals('Record Payment', $paymentInfo['payment_links'][0]['title']);
    $this->assertEquals($result['contribution']['id'], $paymentInfo['payment_links'][0]['qs']['id']);
  }

  /**
   * Test owed/refund info is listed on view payments.
   *
   * @throws \CRM_Core_Exception
   */
  public function testTransactionInfo() {
    $feeAmt = 100;
    $amtPaid = 80;
    $result = $this->addParticipantWithPayment($feeAmt, $amtPaid);
    $contributionID = $result['contribution']['id'];

    $this->callAPISuccess('Payment', 'create', [
      'contribution_id' => $contributionID,
      'total_amount' => 20,
      'payment_instrument_id' => 3,
      'participant_id' => $result['participant']['id'],
    ]);

    //Change selection to a lower amount.
    $params['price_2'] = 50;
    CRM_Price_BAO_LineItem::changeFeeSelections($params, $result['participant']['id'], 'participant', $contributionID, $result['feeBlock'], $result['lineItem']);

    $this->callAPISuccess('Payment', 'create', [
      'total_amount' => -50,
      'contribution_id' => $contributionID,
      'participant_id' => $result['participant']['id'],
      'payment_instrument_id' => 3,
    ]);
    $paymentInfo = CRM_Contribute_BAO_Contribution::getPaymentInfo($result['participant']['id'], 'event', TRUE);
    $transaction = $paymentInfo['transaction'];

    //Assert all transaction(owed and refund) are listed on view payments.
    $this->assertEquals(count($transaction), 3, 'Transaction Details is not proper');
    $this->assertEquals($transaction[0]['total_amount'], 80.00);
    $this->assertEquals($transaction[0]['status'], 'Completed');

    $this->assertEquals($transaction[1]['total_amount'], 20.00);
    $this->assertEquals($transaction[1]['status'], 'Completed');

    $this->assertEquals($transaction[2]['total_amount'], -50.00);
    $this->assertEquals($transaction[2]['status'], 'Refunded Label**');
  }

}
