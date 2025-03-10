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

/**
 * @package CRM
 * @copyright CiviCRM LLC https://civicrm.org/licensing
 */

/**
 * This class generates form components for processing Event.
 */
class CRM_Event_Form_Registration_Confirm extends CRM_Event_Form_Registration {

  /**
   * The values for the contribution db object.
   *
   * @var array
   */
  public $_values;

  /**
   * The total amount.
   *
   * @var float
   */
  public $_totalAmount;

  public $submitOnce = TRUE;

  /**
   * Monetary fields that may be submitted.
   *
   * These should get a standardised format in the beginPostProcess function.
   *
   * These fields are common to many forms. Some may override this.
   * @var array
   */
  protected $submittableMoneyFields = ['total_amount', 'net_amount', 'non_deductible_amount', 'fee_amount', 'tax_amount', 'amount'];

  /**
   * Set variables up before form is built.
   */
  public function preProcess() {
    parent::preProcess();

    // lineItem isn't set until Register postProcess
    $this->_lineItem = $this->get('lineItem');

    $this->_params = $this->get('params');
    $this->_params[0]['tax_amount'] = $this->get('tax_amount');

    $this->_params[0]['is_pay_later'] = $this->get('is_pay_later');
    $this->assign('is_pay_later', $this->_params[0]['is_pay_later']);
    $this->assign('pay_later_receipt', $this->_params[0]['is_pay_later'] ? $this->_values['event']['pay_later_receipt'] : NULL);

    CRM_Utils_Hook::eventDiscount($this, $this->_params);

    if (!empty($this->_params[0]['discount']) && !empty($this->_params[0]['discount']['applied'])) {
      $this->set('hookDiscount', $this->_params[0]['discount']);
      $this->assign('hookDiscount', $this->_params[0]['discount']);
    }

    $this->preProcessExpress();

    if ($this->_values['event']['is_monetary']) {
      $this->_params[0]['invoiceID'] = $this->get('invoiceID');
    }
    $this->assign('defaultRole', FALSE);
    if (($this->_params[0]['defaultRole'] ?? NULL) == 1) {
      $this->assign('defaultRole', TRUE);
    }

    if (empty($this->_params[0]['participant_role_id']) &&
      $this->_values['event']['default_role_id']
    ) {
      $this->_params[0]['participant_role_id'] = $this->_values['event']['default_role_id'];
    }

    if (isset($this->_values['event']['confirm_title'])) {
      $this->setTitle($this->_values['event']['confirm_title']);
    }

    // Personal campaign page
    if ($this->_pcpId) {
      $params = CRM_Contribute_Form_Contribution_Confirm::processPcp($this, $this->_params[0]);
      $this->_params[0] = $params;
    }

    $this->set('params', $this->_params);
  }

  public function setDefaultValues() {
    if (!$this->showPaymentOnConfirm) {
      return [];
    }
    // Set default payment processor as default payment_processor radio button value
    if (!empty($this->_paymentProcessors)) {
      foreach ($this->_paymentProcessors as $pid => $value) {
        if (!empty($value['is_default'])) {
          $defaults['payment_processor_id'] = $pid;
          break;
        }
      }
    }
    if (!empty($this->_values['event']['is_pay_later']) && empty($this->_defaults['payment_processor_id'])) {
      $defaults['is_pay_later'] = 1;
    }
    return $defaults ?? [];
  }

  /**
   * Pre process function for Paypal Express confirm.
   * @todo this is just a step in refactor as payment processor specific code does not belong in generic forms
   *
   * @return bool
   * @throws \CRM_Core_Exception
   */
  private function preProcessExpress() {
    if ($this->_contributeMode !== 'express') {
      return FALSE;
    }
    $params = [];
    // rfp == redirect from paypal
    // @fixme rfp is probably not required - the getPreApprovalDetails should deal with any payment-processor specific 'stuff'
    $rfp = CRM_Utils_Request::retrieve('rfp', 'Boolean', CRM_Core_DAO::$_nullObject, FALSE, NULL, 'GET');

    //we lost rfp in case of additional participant. So set it explicitly.
    if ($rfp || ($this->_params[0]['additional_participants'] ?? FALSE)) {
      if (!empty($this->_paymentProcessor) &&  $this->_paymentProcessor['object']->supports('preApproval')) {
        $preApprovalParams = $this->_paymentProcessor['object']->getPreApprovalDetails($this->get('pre_approval_parameters'));
        $params = array_merge($this->_params, $preApprovalParams);
      }
      CRM_Core_Payment_Form::mapParams($this->_bltID, $params, $params, FALSE);

      // set a few other parameters that are not really specific to this method because we don't know what
      // will break if we change this.
      $params['amount'] = $this->_params[0]['amount'];
      if (!empty($this->_params[0]['discount'])) {
        $params['discount'] = $this->_params[0]['discount'];
        $params['discountAmount'] = $this->_params[0]['discountAmount'];
        $params['discountMessage'] = $this->_params[0]['discountMessage'];
      }

      $params['amount_level'] = $this->_params[0]['amount_level'];
      $params['currencyID'] = $this->_params[0]['currencyID'];

      // also merge all the other values from the profile fields
      $values = $this->controller->exportValues('Register');
      $skipFields = [
        'amount',
        "street_address-{$this->_bltID}",
        "city-{$this->_bltID}",
        "state_province_id-{$this->_bltID}",
        "postal_code-{$this->_bltID}",
        "country_id-{$this->_bltID}",
      ];

      foreach ($values as $name => $value) {
        // skip amount field
        if (!in_array($name, $skipFields)) {
          $params[$name] = $value;
        }
        if (substr($name, 0, 6) == 'price_') {
          $params[$name] = $this->_params[0][$name];
        }
      }
      $this->set('getExpressCheckoutDetails', $params);
    }
    $this->_params[0] = array_merge($this->_params[0], $params);
    $this->_params[0]['is_primary'] = 1;
    return TRUE;
  }

  /**
   * Overwrite action, since we are only showing elements in frozen mode no help display needed.
   *
   * @return int
   */
  public function getAction() {
    if ($this->_action & CRM_Core_Action::PREVIEW) {
      return CRM_Core_Action::VIEW | CRM_Core_Action::PREVIEW;
    }
    else {
      return CRM_Core_Action::VIEW;
    }
  }

  /**
   * Build the form object.
   */
  public function buildQuickForm() {
    $this->assignToTemplate();

    if ($this->_values['event']['is_monetary'] &&
      ($this->_params[0]['amount'] || $this->_params[0]['amount'] == 0) &&
      !$this->_requireApproval
    ) {
      $this->_amount = [];

      $taxAmount = 0;
      foreach ($this->_params as $k => $v) {
        if ($v == 'skip') {
          continue;
        }
        $individualTaxAmount = 0;
        $append = '';
        //display tax amount on confirmation page
        $taxAmount += $v['tax_amount'];
        if (is_array($v)) {
          $this->cleanMoneyFields($v);
          foreach ([
            'first_name',
            'last_name',
          ] as $name) {
            if (isset($v['billing_' . $name]) &&
              !isset($v[$name])
            ) {
              $v[$name] = $v['billing_' . $name];
            }
          }

          if (!empty($v['first_name']) && !empty($v['last_name'])) {
            $append = $v['first_name'] . ' ' . $v['last_name'];
          }
          else {
            //use an email if we have one
            foreach ($v as $v_key => $v_val) {
              if (substr($v_key, 0, 6) == 'email-') {
                $append = $v[$v_key];
              }
            }
          }

          $this->_amount[$k]['amount'] = $v['amount'];
          if (!empty($v['discountAmount'])) {
            $this->_amount[$k]['amount'] -= $v['discountAmount'];
          }

          $this->_amount[$k]['label'] = preg_replace('//', '', $v['amount_level']) . '  -  ' . $append;
          $this->_part[$k]['info'] = ($v['first_name'] ?? '') . ' ' . ($v['last_name'] ?? '');
          if (empty($v['first_name'])) {
            $this->_part[$k]['info'] = $append;
          }

          /*CRM-16320 */
          $individual[$k]['totalAmtWithTax'] = $this->_amount[$k]['amount'];
          $individual[$k]['totalTaxAmt'] = $individualTaxAmount + $v['tax_amount'];
          $this->_totalAmount = $this->_totalAmount + $this->_amount[$k]['amount'];
          if (!empty($v['is_primary'])) {
            $this->set('primaryParticipantAmount', $this->_amount[$k]['amount']);
          }
        }
      }

      if (CRM_Invoicing_Utils::isInvoicingEnabled()) {
        $this->assign('totalTaxAmount', $taxAmount);
        $this->assign('taxTerm', CRM_Invoicing_Utils::getTaxTerm());
        $this->assign('individual', $individual);
        $this->set('individual', $individual);
      }

      $this->assign('part', $this->_part);
      $this->set('part', $this->_part);
      $this->assign('amounts', $this->_amount);
      $this->assign('totalAmount', $this->_totalAmount);
      $this->set('totalAmount', $this->_totalAmount);

      $showPaymentOnConfirm = (in_array($this->_eventId, \Civi::settings()->get('event_show_payment_on_confirm')) || in_array('all', \Civi::settings()->get('event_show_payment_on_confirm')));
      $this->assign('showPaymentOnConfirm', $showPaymentOnConfirm);
      if ($showPaymentOnConfirm) {
        $isPayLater = CRM_Core_DAO::getFieldValue('CRM_Event_DAO_Event', $this->_eventId, 'is_pay_later');
        $this->setPayLaterLabel($isPayLater ? $this->_values['event']['pay_later_text'] : '');
        $this->_paymentProcessorIDs = explode(CRM_Core_DAO::VALUE_SEPARATOR, $this->_values['event']['payment_processor'] ?? NULL);
        $this->assignPaymentProcessor($isPayLater);
        CRM_Core_Payment_ProcessorForm::buildQuickForm($this);
        $this->addPaymentProcessorFieldsToForm();
      }
    }

    if ($this->_priceSetId && !CRM_Core_DAO::getFieldValue('CRM_Price_DAO_PriceSet', $this->_priceSetId, 'is_quick_config')) {
      $lineItemForTemplate = [];
      if (!empty($this->_lineItem) && is_array($this->_lineItem)) {
        foreach ($this->_lineItem as $key => $value) {
          if (!empty($value)) {
            $lineItemForTemplate[$key] = $value;
          }
        }
      }
      if (!empty($lineItemForTemplate)) {
        $this->assignLineItemsToTemplate($lineItemForTemplate);
      }
    }

    //display additional participants profile.
    self::assignProfiles($this);

    //consider total amount.
    $this->assign('isAmountzero', $this->_totalAmount <= 0);

    $contribButton = ts('Continue');
    $this->addButtons([
      [
        'type' => 'back',
        'name' => ts('Go Back'),
      ],
      [
        'type' => 'next',
        'name' => $contribButton,
        'isDefault' => TRUE,
      ],
    ]);

    $defaults = [];
    $fields = [];
    if (!empty($this->_fields)) {
      foreach ($this->_fields as $name => $dontCare) {
        $fields[$name] = 1;
      }
    }
    $fields["billing_state_province-{$this->_bltID}"] = $fields["billing_country-{$this->_bltID}"] = $fields["email-{$this->_bltID}"] = 1;
    foreach ($fields as $name => $dontCare) {
      if (isset($this->_params[0][$name])) {
        $defaults[$name] = $this->_params[0][$name];
        if (substr($name, 0, 7) == 'custom_') {
          $timeField = "{$name}_time";
          if (isset($this->_params[0][$timeField])) {
            $defaults[$timeField] = $this->_params[0][$timeField];
          }
          if (isset($this->_params[0]["{$name}_id"])) {
            $defaults["{$name}_id"] = $this->_params[0]["{$name}_id"];
          }
        }
        elseif (in_array($name, CRM_Contact_BAO_Contact::$_greetingTypes)
          && !empty($this->_params[0][$name . '_custom'])
        ) {
          $defaults[$name . '_custom'] = $this->_params[0][$name . '_custom'];
        }
      }
    }

    $this->setDefaults($defaults);
    $showPaymentOnConfirm = (in_array($this->_eventId, \Civi::settings()->get('event_show_payment_on_confirm')) || in_array('all', \Civi::settings()->get('event_show_payment_on_confirm')));
    if (!$showPaymentOnConfirm) {
      $this->freeze();
    }

    //lets give meaningful status message, CRM-4320.
    $this->assign('isOnWaitlist', $this->_allowWaitlist);
    $this->assign('isRequireApproval', $this->_requireApproval);

    // Assign Participant Count to Lineitem Table
    $this->assign('pricesetFieldsCount', CRM_Price_BAO_PriceSet::getPricesetCount($this->_priceSetId));
    $this->addFormRule(['CRM_Event_Form_Registration_Confirm', 'formRule'], $this);
  }

  /**
   * Apply form rule.
   *
   * @param array $fields
   * @param array $files
   * @param \CRM_Event_Form_Registration_Confirm $form
   *
   * @return array|bool
   */
  public static function formRule($fields, $files, $form) {
    $errors = [];
    $eventFull = CRM_Event_BAO_Participant::eventFull($form->_eventId, FALSE, $form->_values['event']['has_waitlist'] ?? FALSE);
    if ($eventFull && empty($form->_allowConfirmation)) {
      if (empty($form->_allowWaitlist)) {
        CRM_Utils_System::redirect(CRM_Utils_System::url('civicrm/event/register', "reset=1&id={$form->_eventId}", FALSE, NULL, FALSE, TRUE));
      }
    }
    $form->_feeBlock = $form->_values['fee'];
    CRM_Event_Form_Registration_Register::formatFieldsForOptionFull($form);

    if (!empty($form->_priceSetId) &&
      !$form->_requireApproval && !$form->_allowWaitlist
      ) {
      $errors = self::validatePriceSet($form, $form->_params);
      if (!empty($errors)) {
        CRM_Core_Session::setStatus(ts('You have been returned to the start of the registration process and any sold out events have been removed from your selections. You will not be able to continue until you review your booking and select different events if you wish.'), ts('Unfortunately some of your options have now sold out for one or more participants.'), 'error');
        CRM_Core_Session::setStatus(ts('Please note that the options which are marked or selected are sold out for participant being viewed.'), ts('Sold out:'), 'error');
        CRM_Utils_System::redirect(CRM_Utils_System::url('civicrm/event/register', "_qf_Register_display=true&qfKey={$fields['qfKey']}"));
      }
    }

    if ($form->showPaymentOnConfirm && empty($form->_requireApproval) && !empty($form->_totalAmount)
      && $form->_totalAmount > 0 && !isset($fields['payment_processor_id'])
    ) {
      $errors['payment_processor_id'] = ts('Please select a Payment Method');
    }

    return empty($errors) ? TRUE : $errors;
  }

  /**
   * Process the form submission.
   *
   * @throws \CRM_Core_Exception
   */
  public function postProcess() {
    $now = date('YmdHis');

    $this->_params = $this->get('params');
    $this->cleanMoneyFields($this->_params);

    if (!empty($this->_params[0]['contact_id'])) {
      // unclear when this would be set & whether it could be checked in getContactID.
      // perhaps it relates to when cid is in the url
      //@todo someone who knows add comments on the various contactIDs in this form
      $contactID = $this->_params[0]['contact_id'];
    }
    else {
      $contactID = $this->getContactID();
    }

    // if a discount has been applied, lets now deduct it from the amount
    // and fix the fee level
    if (!empty($this->_params[0]['discount']) && !empty($this->_params[0]['discount']['applied'])) {
      foreach ($this->_params as $k => $v) {
        if (($this->_params[$k]['amount'] ?? NULL) > 0 && !empty($this->_params[$k]['discountAmount'])) {
          $this->_params[$k]['amount'] -= $this->_params[$k]['discountAmount'];
          $this->_params[$k]['amount_level'] .= ($this->_params[$k]['discountMessage'] ?? NULL);
        }
      }
      $this->set('params', $this->_params);
    }

    // CRM-4320, lets build array of cancelled additional participant ids
    // those are drop or skip by primary at the time of confirmation.
    // get all in and then unset those we want to process.
    $cancelledIds = $this->_additionalParticipantIds;

    $params = $this->_params;
    if ($this->_values['event']['is_monetary']) {
      $this->set('finalAmount', $this->_amount);
    }
    $participantCount = [];
    $totalTaxAmount = 0;

    //unset the skip participant from params.
    //build the $participantCount array.
    //maintain record for all participants.
    foreach ($params as $participantNum => $record) {
      if ($record == 'skip') {
        unset($params[$participantNum]);
        $participantCount[$participantNum] = 'skip';
      }
      elseif ($participantNum) {
        $participantCount[$participantNum] = 'participant';
      }
      $totalTaxAmount += $record['tax_amount'] ?? 0;
      if (!empty($record['is_primary'])) {
        $taxAmount = &$params[$participantNum]['tax_amount'];
      }
      //lets get additional participant id to cancel.
      if ($this->_allowConfirmation && is_array($cancelledIds)) {
        $additionalId = $record['participant_id'] ?? NULL;
        if ($additionalId && $key = array_search($additionalId, $cancelledIds)) {
          unset($cancelledIds[$key]);
        }
      }
    }
    $taxAmount = $totalTaxAmount;
    $payment = $registerByID = $primaryCurrencyID = $contribution = NULL;
    $paymentObjError = ts('The system did not record payment details for this payment and so could not process the transaction. Please report this error to the site administrator.');

    $fields = [];
    foreach ($params as $key => $value) {
      CRM_Event_Form_Registration_Confirm::fixLocationFields($value, $fields, $this);
      if ($this->_allowWaitlist
        || $this->_requireApproval
        || (!empty($value['is_pay_later']) && !$this->_isBillingAddressRequiredForPayLater)
        || empty($value['is_primary'])
      ) {
        // This is confusing because unnecessary code around it has been removed. It is not
        // clear why we do this / whether we should.
        if (!empty($value['is_pay_later'])) {
          $this->_values['params']['is_pay_later'] = TRUE;
        }
      }

      //Unset ContactID for additional participants and set RegisterBy Id.
      if (empty($value['is_primary'])) {
        $contactID = $value['contact_id'] ?? NULL;
        $registerByID = $this->get('registerByID');
        if ($registerByID) {
          $value['registered_by_id'] = $registerByID;
        }
      }
      else {
        $value['amount'] = $this->_totalAmount;
      }

      $contactID = CRM_Event_Form_Registration_Confirm::updateContactFields($contactID, $value, $fields, $this);

      // lets store the contactID in the session
      // we dont store in userID in case the user is doing multiple
      // transactions etc
      // for things like tell a friend
      if (!$this->getContactID() && !empty($value['is_primary'])) {
        CRM_Core_Session::singleton()->set('transaction.userID', $contactID);
      }

      $value['description'] = ts('Online Event Registration') . ': ' . $this->_values['event']['title'];
      $value['accountingCode'] = $this->_values['event']['accountingCode'] ?? NULL;

      $pending = FALSE;
      if ($this->_allowWaitlist || $this->_requireApproval) {
        //get the participant statuses.
        $waitingStatuses = CRM_Event_PseudoConstant::participantStatus(NULL, "class = 'Waiting'");
        if ($this->_allowWaitlist) {
          $value['participant_status_id'] = $value['participant_status'] = array_search('On waitlist', $waitingStatuses);
        }
        else {
          $value['participant_status_id'] = $value['participant_status'] = array_search('Awaiting approval', $waitingStatuses);
        }

        //there might be case user selected pay later and
        //now becomes part of run time waiting list.
        $value['is_pay_later'] = FALSE;
      }
      elseif ($this->_values['event']['is_monetary']) {
        // required only if paid event
        if (is_array($this->_paymentProcessor)) {
          $payment = $this->_paymentProcessor['object'];
        }
        if (!empty($this->_paymentProcessor) &&  $this->_paymentProcessor['object']->supports('preApproval')) {
          $preApprovalParams = $this->_paymentProcessor['object']->getPreApprovalDetails($this->get('pre_approval_parameters'));
          $value = array_merge($value, $preApprovalParams);
        }
        $result = NULL;

        if (!empty($value['is_pay_later']) ||
          $value['amount'] == 0 ||
          // The concept of contributeMode is deprecated.
          $this->_contributeMode == 'checkout' ||
          $this->_contributeMode == 'notify'
        ) {
          if ($value['amount'] != 0) {
            $pending = TRUE;
            //get the participant statuses.
            $pendingStatuses = CRM_Event_PseudoConstant::participantStatus(NULL, "class = 'Pending'");
            $status = !empty($value['is_pay_later']) ? 'Pending from pay later' : 'Pending from incomplete transaction';
            $value['participant_status_id'] = $value['participant_status'] = array_search($status, $pendingStatuses);
          }
        }
        elseif (!empty($value['is_primary'])) {
          CRM_Core_Payment_Form::mapParams($this->_bltID, $value, $value, TRUE);
          // payment email param can be empty for _bltID mapping
          // thus provide mapping for it with a different email value
          if (empty($value['email'])) {
            $value['email'] = CRM_Utils_Array::valueByRegexKey('/^email-/', $value);
          }

          // If registering from waitlist participant_id is set but contact_id is not.
          // We need a contact ID to process the payment so set the "primary" contact ID.
          if (empty($value['contact_id'])) {
            $value['contact_id'] = $contactID;
          }

          if (is_object($payment)) {
            // Not quite sure why we don't just user $value since it contains the data
            // from result
            // @todo ditch $result & retest.
            list($result, $value) = $this->processPayment($payment, $value);
          }
          else {
            throw new CRM_Core_Exception($paymentObjError);
          }
        }

        $value['receive_date'] = $now;
        if ($this->_allowConfirmation) {
          $value['participant_register_date'] = $this->_values['participant']['register_date'];
        }

        $createContrib = $value['amount'] != 0;
        // force to create zero amount contribution, CRM-5095
        if (!$createContrib && ($value['amount'] == 0)
          && $this->_priceSetId && $this->_lineItem
        ) {
          $createContrib = TRUE;
        }

        if ($createContrib && !empty($value['is_primary']) &&
          !$this->_allowWaitlist && !$this->_requireApproval
        ) {
          // if paid event add a contribution record
          //if primary participant contributing additional amount
          //append (multiple participants) to its fee level. CRM-4196.
          if (count($params) > 1) {
            $value['amount_level'] .= ts(' (multiple participants)') . CRM_Core_DAO::VALUE_SEPARATOR;
          }

          //passing contribution id is already registered.
          $contribution = $this->processContribution($value, $result, $contactID, $pending);
          $value['contributionID'] = $contribution->id;
          $value['receive_date'] = $contribution->receive_date;
          $value['trxn_id'] = $contribution->trxn_id;
          $value['contributionID'] = $contribution->id;
        }
        $value['contactID'] = $contactID;
        $value['eventID'] = $this->_eventId;
        $value['item_name'] = $value['description'];
      }

      if (!empty($value['contributionID'])) {
        $this->_values['contributionId'] = $value['contributionID'];
      }

      //CRM-4453.
      if (!empty($value['is_primary'])) {
        $primaryCurrencyID = $value['currencyID'] ?? NULL;
      }
      if (empty($value['currencyID'])) {
        $value['currencyID'] = $primaryCurrencyID;
      }

      // CRM-11182 - Confirmation page might not be monetary
      if ($this->_values['event']['is_monetary']) {
        if (!$pending && !empty($value['is_primary']) &&
          !$this->_allowWaitlist && !$this->_requireApproval
        ) {
          // transactionID & receive date required while building email template
          $this->assign('trxn_id', $value['trxn_id'] ?? NULL);
          $this->assign('receive_date', CRM_Utils_Date::mysqlToIso($value['receive_date'] ?? NULL));
          $this->set('receiveDate', CRM_Utils_Date::mysqlToIso($value['receive_date'] ?? NULL));
          $this->set('trxnId', $value['trxn_id'] ?? NULL);
        }
      }

      $value['fee_amount'] = $value['amount'] ?? NULL;
      $this->set('value', $value);

      // handle register date CRM-4320
      if ($this->_allowConfirmation) {
        $registerDate = $params['participant_register_date'] ?? NULL;
      }
      elseif (!empty($params['participant_register_date']) &&
        is_array($params['participant_register_date'])
      ) {
        $registerDate = CRM_Utils_Date::format($params['participant_register_date']);
      }
      else {
        $registerDate = date('YmdHis');
      }
      $this->assign('register_date', $registerDate);

      $this->confirmPostProcess($contactID, $contribution);
    }

    //handle if no additional participant.
    if (!$registerByID) {
      $registerByID = $this->get('registerByID');
    }

    $this->set('participantIDs', $this->_participantIDS);

    // create line items, CRM-5313
    if ($this->_priceSetId &&
      !empty($this->_lineItem)
    ) {
      // take all processed participant ids.
      $allParticipantIds = $this->_participantIDS;

      // when participant re-walk wizard.
      if ($this->_allowConfirmation &&
        !empty($this->_additionalParticipantIds)
      ) {
        $allParticipantIds = array_merge([$registerByID], $this->_additionalParticipantIds);
      }

      $entityTable = 'civicrm_participant';
      $totalTaxAmount = 0;
      $dataArray = [];
      foreach ($this->_lineItem as $key => $value) {
        if ($value == 'skip') {
          continue;
        }
        if ($entityId = $allParticipantIds[$key] ?? NULL) {
          // do cleanup line  items if participant re-walking wizard.
          if ($this->_allowConfirmation) {
            CRM_Price_BAO_LineItem::deleteLineItems($entityId, $entityTable);
          }
          $lineItem[$this->_priceSetId] = $value;
          CRM_Price_BAO_LineItem::processPriceSet($entityId, $lineItem, $contribution, $entityTable);
        }
        if (CRM_Invoicing_Utils::isInvoicingEnabled()) {
          foreach ($value as $line) {
            if (isset($line['tax_amount']) && isset($line['tax_rate'])) {
              $totalTaxAmount = $line['tax_amount'] + $totalTaxAmount;
              if (isset($dataArray[$line['tax_rate']])) {
                $dataArray[$line['tax_rate']] = $dataArray[$line['tax_rate']] + ($line['tax_amount'] ?? 0);
              }
              else {
                $dataArray[$line['tax_rate']] = $line['tax_amount'] ?? NULL;
              }
            }
          }
        }
      }
      $this->assign('dataArray', $dataArray);
      $this->assign('totalTaxAmount', $totalTaxAmount);
    }

    //update status and send mail to cancelled additional participants, CRM-4320
    if ($this->_allowConfirmation && is_array($cancelledIds) && !empty($cancelledIds)) {
      $cancelledId = array_search('Cancelled',
        CRM_Event_PseudoConstant::participantStatus(NULL, "class = 'Negative'")
      );
      CRM_Event_BAO_Participant::transitionParticipants($cancelledIds, $cancelledId);
    }

    $isTest = FALSE;
    if ($this->_action & CRM_Core_Action::PREVIEW) {
      $isTest = TRUE;
    }

    $primaryParticipant = $this->get('primaryParticipant');

    if (empty($primaryParticipant['participantID'])) {
      CRM_Core_Error::deprecatedFunctionWarning('This line is not logically reachable.');
      $primaryParticipant['participantID'] = $registerByID;
    }
    //otherwise send mail Confirmation/Receipt
    $primaryContactId = $this->get('primaryContactId');

    // for Transfer checkout.
    // The concept of contributeMode is deprecated.
    if (($this->_contributeMode == 'checkout' ||
        $this->_contributeMode == 'notify'
      ) && empty($params[0]['is_pay_later']) &&
      !$this->_allowWaitlist && !$this->_requireApproval &&
      $this->_totalAmount > 0
    ) {

      //build an array of custom profile and assigning it to template
      $customProfile = CRM_Event_BAO_Event::buildCustomProfile($registerByID, $this->_values, NULL, $isTest);
      if (count($customProfile)) {
        $this->assign('customProfile', $customProfile);
        $this->set('customProfile', $customProfile);
      }

      // do a transfer only if a monetary payment greater than 0
      if ($this->_values['event']['is_monetary'] && $primaryParticipant) {
        if ($payment && is_object($payment)) {
          //CRM 14512 provide line items of all participants to payment gateway
          $primaryContactId = $this->get('primaryContactId');

          //build an array of cId/pId of participants
          $additionalIDs = CRM_Event_BAO_Event::buildCustomProfile($registerByID, NULL, $primaryContactId, $isTest, TRUE);

          //need to copy, since we are unsetting on the way.
          $copyParticipantCountLines = $participantCount;

          //lets carry all participant params w/ values.
          foreach ($additionalIDs as $participantID => $contactId) {
            $participantNum = $participantID;
            if ($participantID == $registerByID) {
              // This is the is primary participant.
              $participantNum = 0;
            }
            else {
              if ($participantNum = array_search('participant', $copyParticipantCountLines)) {
                //if no participant found break.
                if ($participantNum === NULL) {
                  break;
                }
                //unset current participant so we don't check them again
                unset($copyParticipantCountLines[$participantNum]);
              }
            }
            // get values of line items
            if ($this->_amount) {
              $amount = [];
              $amount[$participantNum]['label'] = preg_replace('//', '', $params[$participantNum]['amount_level']);
              $amount[$participantNum]['amount'] = $params[$participantNum]['amount'];
              $params[$participantNum]['amounts'] = $amount;
            }

            if (!empty($this->_lineItem)) {
              $lineItems = $this->_lineItem;
              $lineItem = [];
              if ($lineItemValue = ($lineItems[$participantNum] ?? NULL)) {
                $lineItem[] = $lineItemValue;
              }
              $params[$participantNum]['lineItem'] = $lineItem;
            }

            //only add additional participants and not the primary participant as we already have that
            //added to $primaryParticipant so that this change doesn't break or require changes to
            //existing gateway implementations
            $primaryParticipant['participants_info'][$participantID] = $params[$participantNum];
          }

          //get event custom field information
          $groupTree = CRM_Core_BAO_CustomGroup::getTree('Event', NULL, $this->_eventId, 0, $this->_values['event']['event_type_id']);
          $primaryParticipant['eventCustomFields'] = $groupTree;

          // call postprocess hook before leaving
          $this->postProcessHook();

          $this->processPayment($payment, $primaryParticipant);
        }
        else {
          throw new CRM_Core_Exception($paymentObjError);
        }
      }
    }
    else {

      //build an array of cId/pId of participants
      $additionalIDs = CRM_Event_BAO_Event::buildCustomProfile($registerByID,
        NULL, $primaryContactId, $isTest,
        TRUE
      );
      //let's send mails to all with meaningful text, CRM-4320.
      $this->assign('isOnWaitlist', $this->_allowWaitlist);
      $this->assign('isRequireApproval', $this->_requireApproval);

      //need to copy, since we are unsetting on the way.
      $copyParticipantCount = $participantCount;

      //let's carry all participant params w/ values.
      foreach ($additionalIDs as $participantID => $contactId) {
        $participantNum = NULL;
        if ($participantID == $registerByID) {
          $participantNum = 0;
        }
        else {
          if ($participantNum = array_search('participant', $copyParticipantCount)) {
            unset($copyParticipantCount[$participantNum]);
          }
        }
        if ($participantNum === NULL) {
          break;
        }

        //carry the participant submitted values.
        $this->_values['params'][$participantID] = $params[$participantNum];
      }

      foreach ($additionalIDs as $participantID => $contactId) {
        $participantNum = 0;
        if ($participantID == $registerByID) {
          //set as Primary Participant
          $this->assign('isPrimary', 1);
          //build an array of custom profile and assigning it to template.
          $customProfile = CRM_Event_BAO_Event::buildCustomProfile($participantID, $this->_values, NULL, $isTest);

          if (count($customProfile)) {
            $this->assign('customProfile', $customProfile);
            $this->set('customProfile', $customProfile);
          }
          $this->_values['params']['additionalParticipant'] = FALSE;
        }
        else {
          //take the Additional participant number.
          if ($participantNum = array_search('participant', $participantCount)) {
            unset($participantCount[$participantNum]);
          }
          // Change $this->_values['participant'] to include additional participant values
          $ids = $participantValues = [];
          $participantParams = ['id' => $participantID];
          CRM_Event_BAO_Participant::getValues($participantParams, $participantValues, $ids);
          $this->_values['participant'] = $participantValues[$participantID];

          $this->assign('isPrimary', 0);
          $this->assign('customProfile', NULL);
          //Additional Participant should get only it's payment information
          if (!empty($this->_amount)) {
            $amount = [];
            $params = $this->get('params');
            $amount[$participantNum]['label'] = preg_replace('//', '', $params[$participantNum]['amount_level']);
            $amount[$participantNum]['amount'] = $params[$participantNum]['amount'];
            $this->assign('amounts', $amount);
          }
          if ($this->_lineItem) {
            $lineItems = $this->_lineItem;
            $lineItem = [];
            if ($lineItemValue = ($lineItems[$participantNum] ?? NULL)) {
              $lineItem[] = $lineItemValue;
            }
            if (CRM_Invoicing_Utils::isInvoicingEnabled()) {
              $individual = $this->get('individual');
              $dataArray[key($dataArray)] = $individual[$participantNum]['totalTaxAmt'];
              $this->assign('dataArray', $dataArray);
              $this->assign('totalAmount', $individual[$participantNum]['totalAmtWithTax']);
              $this->assign('totalTaxAmount', $individual[$participantNum]['totalTaxAmt']);
              $this->assign('individual', [$individual[$participantNum]]);
            }
            $this->assign('lineItem', $lineItem);
          }
          $this->_values['params']['additionalParticipant'] = TRUE;
          $this->assign('isAdditionalParticipant', $this->_values['params']['additionalParticipant']);
        }

        //pass these variables since these are run time calculated.
        $this->_values['params']['isOnWaitlist'] = $this->_allowWaitlist;
        $this->_values['params']['isRequireApproval'] = $this->_requireApproval;

        //send mail to primary as well as additional participants.
        CRM_Event_BAO_Event::sendMail($contactId, $this->_values, $participantID, $isTest);
      }
    }
  }

  /**
   * Process the contribution.
   *
   * @param array $params
   * @param array $result
   * @param int $contactID
   * @param bool $pending
   *
   * @return \CRM_Contribute_BAO_Contribution
   *
   * @throws \CRM_Core_Exception
   */
  private function processContribution(
    $params, $result, $contactID,
    $pending = FALSE
  ) {
    $form = $this;
    // Note this used to be shared with the backoffice form & no longer is, some code may no longer be required.
    $transaction = new CRM_Core_Transaction();

    $now = date('YmdHis');
    $receiptDate = NULL;

    if (!empty($form->_values['event']['is_email_confirm'])) {
      $receiptDate = $now;
    }

    // CRM-20264: fetch CC type ID and number (last 4 digit) and assign it back to $params
    CRM_Contribute_Form_AbstractEditPayment::formatCreditCardDetails($params);

    $contribParams = [
      'contact_id' => $contactID,
      'financial_type_id' => !empty($form->_values['event']['financial_type_id']) ? $form->_values['event']['financial_type_id'] : $params['financial_type_id'],
      'receive_date' => $now,
      'total_amount' => $params['amount'],
      'tax_amount' => $params['tax_amount'],
      'amount_level' => $params['amount_level'],
      'invoice_id' => $params['invoiceID'],
      'currency' => $params['currencyID'],
      'source' => !empty($params['participant_source']) ? $params['participant_source'] : $params['description'],
      'is_pay_later' => $params['is_pay_later'] ?? 0,
      'campaign_id' => $params['campaign_id'] ?? NULL,
      'card_type_id' => $params['card_type_id'] ?? NULL,
      'pan_truncation' => $params['pan_truncation'] ?? NULL,
      // The ternary is probably redundant - paymentProcessor should always be set.
      // For pay-later contributions it will be the pay-later processor.
      'payment_processor' => $this->_paymentProcessor ? $this->_paymentProcessor['id'] : NULL,
      'payment_instrument_id' => $this->_paymentProcessor ? $this->_paymentProcessor['payment_instrument_id'] : NULL,
    ];

    if (!$pending && $result) {
      $contribParams += [
        'fee_amount' => $result['fee_amount'] ?? NULL,
        'trxn_id' => $result['trxn_id'],
        'receipt_date' => $receiptDate,
      ];
    }

    $allStatuses = CRM_Contribute_PseudoConstant::contributionStatus(NULL, 'name');
    $contribParams['contribution_status_id'] = array_search('Completed', $allStatuses);
    if ($pending) {
      $contribParams['contribution_status_id'] = array_search('Pending', $allStatuses);
    }

    $contribParams['is_test'] = 0;
    if ($form->_action & CRM_Core_Action::PREVIEW || ($params['mode'] ?? NULL) === 'test') {
      $contribParams['is_test'] = 1;
    }

    if (!empty($contribParams['invoice_id'])) {
      $contribParams['id'] = CRM_Core_DAO::getFieldValue('CRM_Contribute_DAO_Contribution',
        $contribParams['invoice_id'],
        'id',
        'invoice_id'
      );
    }

    if (Civi::settings()->get('deferred_revenue_enabled')) {
      $eventStartDate = $form->_values['event']['start_date'] ?? NULL;
      if (strtotime($eventStartDate) > strtotime(date('Ymt'))) {
        $contribParams['revenue_recognition_date'] = date('Ymd', strtotime($eventStartDate));
      }
    }
    //create an contribution address
    // The concept of contributeMode is deprecated. Elsewhere we use the function processBillingAddress() - although
    // currently that is only inherited by back-office forms.
    if ($form->_contributeMode != 'notify' && empty($params['is_pay_later'])) {
      $contribParams['address_id'] = CRM_Contribute_BAO_Contribution::createAddress($params, $form->_bltID);
    }

    $contribParams['skipLineItem'] = 1;
    $contribParams['skipCleanMoney'] = 1;
    // create contribution record
    $contribution = CRM_Contribute_BAO_Contribution::add($contribParams);
    // CRM-11124
    CRM_Event_BAO_Participant::createDiscountTrxn($form->_eventId, $contribParams, NULL, CRM_Price_BAO_PriceSet::parseFirstPriceSetValueIDFromParams($params));

    // process soft credit / pcp pages
    if (!empty($params['pcp_made_through_id'])) {
      CRM_Contribute_BAO_ContributionSoft::formatSoftCreditParams($params, $form);
      CRM_Contribute_BAO_ContributionSoft::processSoftContribution($params, $contribution);
    }

    $transaction->commit();

    return $contribution;
  }

  /**
   * Fix the Location Fields.
   *
   * @todo Reconcile with the contribution method formatParamsForPaymentProcessor
   * rather than adding different logic to check when to keep the billing
   * fields. There might be a difference in handling guest/multiple
   * participants though.
   *
   * @param array $params
   * @param array $fields
   * @param CRM_Core_Form $form
   */
  public static function fixLocationFields(&$params, &$fields, &$form) {
    if (!empty($form->_fields)) {
      foreach ($form->_fields as $name => $dontCare) {
        $fields[$name] = 1;
      }
    }

    // If there's no 'first_name' in the profile then overwrite the names from
    // the billing fields (if they are set)
    if (is_array($fields)) {
      if (!array_key_exists('first_name', $fields)) {
        $nameFields = ['first_name', 'middle_name', 'last_name'];
        foreach ($nameFields as $name) {
          $fields[$name] = 1;
          if (array_key_exists("billing_$name", $params)) {
            $params[$name] = $params["billing_{$name}"];
            $params['preserveDBName'] = TRUE;
          }
        }
      }
    }

    // Add the billing names to the billing address, if a billing name is set
    if (!empty($params['billing_first_name'])) {
      $params["address_name-{$form->_bltID}"] = $params['billing_first_name'] . ' ' . ($params['billing_middle_name'] ?? '') . ' ' . ($params['billing_last_name'] ?? '');
      $fields["address_name-{$form->_bltID}"] = 1;
    }

    $fields["email-{$form->_bltID}"] = 1;
    $fields['email-Primary'] = 1;

    //if its pay later or additional participant set email address as primary.
    if ((!empty($params['is_pay_later']) || empty($params['is_primary']) ||
        !$form->_values['event']['is_monetary'] ||
        $form->_allowWaitlist ||
        $form->_requireApproval
      ) && !empty($params["email-{$form->_bltID}"])
    ) {
      $params['email-Primary'] = $params["email-{$form->_bltID}"];
    }
  }

  /**
   * Update contact fields.
   *
   * @param int $contactID
   * @param array $params
   * @param array $fields
   * @param CRM_Core_Form $form
   *
   * @return int
   */
  public static function updateContactFields($contactID, $params, $fields, &$form) {
    //add the contact to group, if add to group is selected for a
    //particular uf group

    // get the add to groups
    $addToGroups = [];
    if (empty($params['registered_by_id'])) {
      $topProfile = 'custom_pre_id';
      $additionalProfiles = 'custom_post_id';
    }
    else {
      $topProfile = 'additional_custom_pre_id';
      $additionalProfiles = 'additional_custom_post_id';
    }
    $profiles = $form->_values[$additionalProfiles] ?? [];
    if (!empty($form->_values[$topProfile])) {
      $profiles[] = $form->_values[$topProfile];
    }
    if (!empty($profiles)) {
      $uFGroups = \Civi\Api4\UFGroup::get(FALSE)
        ->addSelect('add_to_group_id')
        ->addWhere('id', 'IN', $profiles)
        ->execute();
      foreach ($uFGroups as $uFGroup) {
        if (!empty($uFGroup['add_to_group_id'])) {
          $addToGroups[$uFGroup['add_to_group_id']] = $uFGroup['add_to_group_id'];
        }
      }
    }

    // check for profile double opt-in and get groups to be subscribed
    $subscribeGroupIds = CRM_Core_BAO_UFGroup::getDoubleOptInGroupIds($params, $contactID);

    foreach ($addToGroups as $k) {
      if (array_key_exists($k, $subscribeGroupIds)) {
        unset($addToGroups[$k]);
      }
    }

    // since we are directly adding contact to group lets unset it from mailing
    if (!empty($addToGroups)) {
      foreach ($addToGroups as $groupId) {
        if (isset($subscribeGroupIds[$groupId])) {
          unset($subscribeGroupIds[$groupId]);
        }
      }
    }
    if ($contactID) {
      $ctype = CRM_Core_DAO::getFieldValue(
        'CRM_Contact_DAO_Contact',
        $contactID,
        'contact_type'
      );

      if (array_key_exists('contact_id', $params) && empty($params['contact_id'])) {
        // we unset this here because the downstream function ignores the contactID we give it
        // if it is set & it is difficult to understand the implications of 'fixing' this downstream
        // but if we are passing a contact id into this function it's reasonable to assume we don't
        // want it ignored
        unset($params['contact_id']);
      }

      $contactID = CRM_Contact_BAO_Contact::createProfileContact(
        $params,
        $fields,
        $contactID,
        $addToGroups,
        NULL,
        $ctype,
        TRUE
      );
    }
    else {

      foreach (CRM_Contact_BAO_Contact::$_greetingTypes as $greeting) {
        if (!isset($params[$greeting . '_id'])) {
          $params[$greeting . '_id'] = CRM_Contact_BAO_Contact_Utils::defaultGreeting('Individual', $greeting);
        }
      }

      $contactID = CRM_Contact_BAO_Contact::createProfileContact($params,
        $fields,
        NULL,
        $addToGroups,
        NULL,
        NULL,
        TRUE
      );
      $form->set('contactID', $contactID);
    }

    //get email primary first if exist
    $subscriptionEmail = ['email' => $params['email-Primary'] ?? NULL];
    if (!$subscriptionEmail['email']) {
      $subscriptionEmail['email'] = $params["email-{$form->_bltID}"] ?? NULL;
    }
    // subscribing contact to groups
    if (!empty($subscribeGroupIds) && $subscriptionEmail['email']) {
      CRM_Mailing_Event_BAO_MailingEventSubscribe::commonSubscribe($subscribeGroupIds, $subscriptionEmail, $contactID);
    }

    return $contactID;
  }

  /**
   * Assign Profiles to the template.
   *
   * @param CRM_Event_Form_Registration_Confirm $form
   *
   * @throws \CRM_Core_Exception
   */
  public static function assignProfiles($form) {
    $participantParams = $form->_params;
    $formattedValues = $profileFields = [];
    $count = 1;
    foreach ($participantParams as $participantNum => $participantValue) {
      if ($participantNum) {
        $prefix1 = 'additional';
        $prefix2 = 'additional_';
      }
      else {
        $prefix1 = '';
        $prefix2 = '';
      }
      if ($participantValue !== 'skip') {
        //get the customPre profile info
        if (!empty($form->_values[$prefix2 . 'custom_pre_id'])) {
          $values = $groupName = [];
          CRM_Event_BAO_Event::displayProfile($participantValue,
            $form->_values[$prefix2 . 'custom_pre_id'],
            $groupName,
            $values,
            $profileFields
          );

          if (count($values)) {
            $formattedValues[$count][$prefix1 . 'CustomPre'] = $values;
          }
          $formattedValues[$count][$prefix1 . 'CustomPreGroupTitle'] = $groupName['groupTitle'] ?? NULL;
        }
        //get the customPost profile info
        if (!empty($form->_values[$prefix2 . 'custom_post_id'])) {
          $values = $groupName = [];
          foreach ($form->_values[$prefix2 . 'custom_post_id'] as $gids) {
            $val = [];
            CRM_Event_BAO_Event::displayProfile($participantValue,
              $gids,
              $group,
              $val,
              $profileFields
            );
            $values[$gids] = $val;
            $groupName[$gids] = $group;
          }

          if (count($values)) {
            $formattedValues[$count][$prefix1 . 'CustomPost'] = $values;
          }

          if (isset($formattedValues[$count][$prefix1 . 'CustomPre'])) {
            $formattedValues[$count][$prefix1 . 'CustomPost'] = array_diff_assoc($formattedValues[$count][$prefix1 . 'CustomPost'],
              $formattedValues[$count][$prefix1 . 'CustomPre']
            );
          }

          $formattedValues[$count][$prefix1 . 'CustomPostGroupTitle'] = $groupName;
        }
        $count++;
      }
      $form->_fields = $profileFields;
    }
    if (!empty($formattedValues)) {
      $form->assign('primaryParticipantProfile', $formattedValues[1]);
      $form->set('primaryParticipantProfile', $formattedValues[1]);
      if ($count > 2) {
        unset($formattedValues[1]);
        $form->assign('addParticipantProfile', $formattedValues);
        $form->set('addParticipantProfile', $formattedValues);
      }
    }
  }

  /**
   * Submit in test mode.
   *
   * Do not use this - we have figured out how to emulate form in tests now.
   *
   * See ConfirmTest.
   *
   * @deprecated
   *
   * @param $params
   */
  public static function testSubmit($params) {
    $form = new CRM_Event_Form_Registration_Confirm();
    // This way the mocked up controller ignores the session stuff.
    $_SERVER['REQUEST_METHOD'] = 'GET';
    $_REQUEST['id'] = $form->_eventId = $params['id'];
    $form->controller = new CRM_Event_Controller_Registration();
    $form->_params = $params['params'];
    // This happens in buildQuickForm so emulate here.
    $form->_amount = $form->_totalAmount = $params['totalAmount'] ?? 0;
    $form->set('params', $params['params']);
    $form->_values['custom_pre_id'] = $params['custom_pre_id'] ?? NULL;
    $form->_values['custom_post_id'] = $params['custom_post_id'] ?? NULL;
    $form->_values['event'] = $params['event'] ?? NULL;
    $form->_contributeMode = $params['contributeMode'];
    $eventParams = ['id' => $params['id']];
    CRM_Event_BAO_Event::retrieve($eventParams, $form->_values['event']);
    $form->set('registerByID', $params['registerByID']);
    if (!empty($params['paymentProcessorObj'])) {
      $form->_paymentProcessor = $params['paymentProcessorObj'];
    }
    $form->postProcess();
    return $form;
  }

  /**
   * Process the payment, redirecting back to the page on error.
   *
   * @param \CRM_Core_Payment $payment
   * @param $value
   *
   * @return array
   */
  private function processPayment($payment, $value) {
    try {
      $params = $this->prepareParamsForPaymentProcessor($value);
      $result = $payment->doPayment($params, 'event');
      return [$result, $value];
    }
    catch (\Civi\Payment\Exception\PaymentProcessorException $e) {
      Civi::log()->error('Payment processor exception: ' . $e->getMessage());
      CRM_Core_Session::singleton()->setStatus($e->getMessage());
      CRM_Utils_System::redirect(CRM_Utils_System::url('civicrm/event/register', "id={$this->_eventId}"));
    }
    return [];
  }

  /**
   * Clean money fields from the form.
   *
   * @param array $params
   */
  protected function cleanMoneyFields(&$params) {
    foreach ($this->submittableMoneyFields as $moneyField) {
      foreach ($params as $index => $paramField) {
        if (isset($paramField[$moneyField])) {
          $params[$index][$moneyField] = CRM_Utils_Rule::cleanMoney($paramField[$moneyField]);
        }
      }
    }
  }

}
