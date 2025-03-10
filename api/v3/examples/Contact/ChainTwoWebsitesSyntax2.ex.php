<?php

/**
 * @file
 */

/**
 * Test Generated example demonstrating the Contact.create API.
 *
 * Demonstrates creating two websites as an array.
 *
 * @return array
 *   API result array
 */
function contact_create_example() {
  $params = [
    'first_name' => 'abc3',
    'last_name' => 'xyz3',
    'contact_type' => 'Individual',
    'email' => 'man3@yahoo.com',
    'api.contribution.create' => [
      'receive_date' => '2010-01-01',
      'total_amount' => '100',
      'financial_type_id' => 1,
      'payment_instrument_id' => 1,
      'non_deductible_amount' => '10',
      'fee_amount' => '50',
      'net_amount' => '90',
      'trxn_id' => 12345,
      'invoice_id' => 67890,
      'source' => 'SSF',
      'contribution_status_id' => 1,
      'skipCleanMoney' => 1,
    ],
    'api.website.create' => [
      '0' => [
        'url' => 'https://civicrm.org',
      ],
      '1' => [
        'url' => 'https://chained.org',
        'website_type_id' => 2,
      ],
    ],
  ];

  try {
    $result = civicrm_api3('Contact', 'create', $params);
  }
  catch (CRM_Core_Exception $e) {
    // Handle error here.
    $errorMessage = $e->getMessage();
    $errorCode = $e->getErrorCode();
    $errorData = $e->getExtraParams();
    return [
      'is_error' => 1,
      'error_message' => $errorMessage,
      'error_code' => $errorCode,
      'error_data' => $errorData,
    ];
  }

  return $result;
}

/**
 * Function returns array of result expected from previous function.
 *
 * @return array
 *   API result array
 */
function contact_create_expectedresult() {

  $expectedResult = [
    'is_error' => 0,
    'version' => 3,
    'count' => 1,
    'id' => 3,
    'values' => [
      '3' => [
        'id' => '3',
        'contact_type' => 'Individual',
        'contact_sub_type' => '',
        'do_not_email' => 0,
        'do_not_phone' => 0,
        'do_not_mail' => 0,
        'do_not_sms' => 0,
        'do_not_trade' => 0,
        'is_opt_out' => 0,
        'legal_identifier' => '',
        'external_identifier' => '',
        'sort_name' => 'xyz3, abc3',
        'display_name' => 'abc3 xyz3',
        'nick_name' => '',
        'legal_name' => '',
        'image_URL' => '',
        'preferred_communication_method' => '',
        'preferred_language' => 'en_US',
        'hash' => '67eac7789eaee00',
        'api_key' => '',
        'first_name' => 'abc3',
        'middle_name' => '',
        'last_name' => 'xyz3',
        'prefix_id' => '',
        'suffix_id' => '',
        'formal_title' => '',
        'communication_style_id' => '1',
        'email_greeting_id' => '1',
        'email_greeting_custom' => '',
        'email_greeting_display' => '',
        'postal_greeting_id' => '1',
        'postal_greeting_custom' => '',
        'postal_greeting_display' => '',
        'addressee_id' => '1',
        'addressee_custom' => '',
        'addressee_display' => '',
        'job_title' => '',
        'gender_id' => '',
        'birth_date' => '',
        'is_deceased' => 0,
        'deceased_date' => '',
        'household_name' => '',
        'primary_contact_id' => '',
        'organization_name' => '',
        'sic_code' => '',
        'user_unique_id' => '',
        'created_date' => '2013-07-28 08:49:19',
        'modified_date' => '2012-11-14 16:02:35',
        'api.contribution.create' => [
          'is_error' => 0,
          'version' => 3,
          'count' => 1,
          'id' => 1,
          'values' => [
            '0' => [
              'id' => '1',
              'contact_id' => '3',
              'financial_type_id' => '1',
              'contribution_page_id' => '',
              'payment_instrument_id' => '1',
              'receive_date' => '20100101000000',
              'non_deductible_amount' => '10',
              'total_amount' => '100',
              'fee_amount' => '50',
              'net_amount' => '90',
              'trxn_id' => '12345',
              'invoice_id' => '67890',
              'invoice_number' => '',
              'currency' => 'USD',
              'cancel_date' => '',
              'cancel_reason' => '',
              'receipt_date' => '',
              'thankyou_date' => '',
              'source' => 'SSF',
              'amount_level' => '',
              'contribution_recur_id' => '',
              'is_test' => '',
              'is_pay_later' => '',
              'contribution_status_id' => '1',
              'address_id' => '',
              'check_number' => '',
              'campaign_id' => '',
              'creditnote_id' => '',
              'tax_amount' => 0,
              'revenue_recognition_date' => '',
              'is_template' => '',
              'contribution_type_id' => '1',
            ],
          ],
        ],
        'api.website.create' => [
          '0' => [
            'is_error' => 0,
            'version' => 3,
            'count' => 1,
            'id' => 1,
            'values' => [
              '0' => [
                'id' => '1',
                'contact_id' => '3',
                'url' => 'https://civicrm.org',
                'website_type_id' => '',
              ],
            ],
          ],
          '1' => [
            'is_error' => 0,
            'version' => 3,
            'count' => 1,
            'id' => 2,
            'values' => [
              '0' => [
                'id' => '2',
                'contact_id' => '3',
                'url' => 'https://chained.org',
                'website_type_id' => '2',
              ],
            ],
          ],
        ],
      ],
    ],
  ];

  return $expectedResult;
}

/*
 * This example has been generated from the API test suite.
 * The test that created it is called "testCreateIndividualWithContributionChainedArrays"
 * and can be found at:
 * https://github.com/civicrm/civicrm-core/blob/master/tests/phpunit/api/v3/ContactTest.php
 *
 * You can see the outcome of the API tests at
 * https://test.civicrm.org/job/CiviCRM-Core-Matrix/
 *
 * To Learn about the API read
 * https://docs.civicrm.org/dev/en/latest/api/
 *
 * Browse the API on your own site with the API Explorer. It is in the main
 * CiviCRM menu, under: Support > Development > API Explorer.
 *
 * Read more about testing here
 * https://docs.civicrm.org/dev/en/latest/testing/
 *
 * API Standards documentation:
 * https://docs.civicrm.org/dev/en/latest/framework/api-architecture/
 */
