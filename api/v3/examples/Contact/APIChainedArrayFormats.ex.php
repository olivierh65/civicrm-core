<?php

/**
 * @file
 */

/**
 * Test Generated example demonstrating the Contact.get API.
 *
 * This demonstrates the usage of chained api functions.
 * In this case no notes or custom fields have been created.
 *
 * @return array
 *   API result array
 */
function contact_get_example() {
  $params = [
    'id' => 3,
    'api.website.getValue' => [
      'return' => 'url',
    ],
    'api.Contribution.getCount' => [],
    'api.CustomValue.get' => 1,
    'api.Note.get' => 1,
    'api.Membership.getCount' => [],
  ];

  try {
    $result = civicrm_api3('Contact', 'get', $params);
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
function contact_get_expectedresult() {

  $expectedResult = [
    'is_error' => 0,
    'version' => 3,
    'count' => 1,
    'id' => 3,
    'values' => [
      '3' => [
        'contact_id' => '3',
        'contact_type' => 'Individual',
        'contact_sub_type' => '',
        'sort_name' => 'xyz3, abc3',
        'display_name' => 'abc3 xyz3',
        'do_not_email' => 0,
        'do_not_phone' => 0,
        'do_not_mail' => 0,
        'do_not_sms' => 0,
        'do_not_trade' => 0,
        'is_opt_out' => 0,
        'legal_identifier' => '',
        'external_identifier' => '',
        'nick_name' => '',
        'legal_name' => '',
        'image_URL' => '',
        'preferred_communication_method' => '',
        'preferred_language' => 'en_US',
        'first_name' => 'abc3',
        'middle_name' => '',
        'last_name' => 'xyz3',
        'prefix_id' => '',
        'suffix_id' => '',
        'formal_title' => '',
        'communication_style_id' => '1',
        'job_title' => '',
        'gender_id' => '',
        'birth_date' => '',
        'is_deceased' => 0,
        'deceased_date' => '',
        'household_name' => '',
        'organization_name' => '',
        'sic_code' => '',
        'contact_is_deleted' => 0,
        'current_employer' => '',
        'address_id' => '',
        'street_address' => '',
        'supplemental_address_1' => '',
        'supplemental_address_2' => '',
        'supplemental_address_3' => '',
        'city' => '',
        'postal_code_suffix' => '',
        'postal_code' => '',
        'geo_code_1' => '',
        'geo_code_2' => '',
        'state_province_id' => '',
        'country_id' => '',
        'phone_id' => '',
        'phone_type_id' => '',
        'phone' => '',
        'email_id' => '3',
        'email' => 'man3@yahoo.com',
        'on_hold' => 0,
        'im_id' => '',
        'provider_id' => '',
        'im' => '',
        'worldregion_id' => '',
        'world_region' => '',
        'languages' => 'English (United States)',
        'individual_prefix' => '',
        'individual_suffix' => '',
        'communication_style' => 'Formal',
        'gender' => '',
        'state_province_name' => '',
        'state_province' => '',
        'country' => '',
        'id' => '3',
        'api.website.getValue' => 'https://civicrm.org',
        'api.Contribution.getCount' => 2,
        'api.CustomValue.get' => [
          'is_error' => 0,
          'version' => 3,
          'count' => 0,
          'values' => [],
        ],
        'api.Note.get' => [
          'is_error' => 0,
          'version' => 3,
          'count' => 0,
          'values' => [],
        ],
        'api.Membership.getCount' => 0,
      ],
    ],
  ];

  return $expectedResult;
}

/*
 * This example has been generated from the API test suite.
 * The test that created it is called "testGetIndividualWithChainedArraysFormats"
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
