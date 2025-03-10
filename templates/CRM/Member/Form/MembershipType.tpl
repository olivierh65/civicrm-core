{*
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC. All rights reserved.                        |
 |                                                                    |
 | This work is published under the GNU AGPLv3 license with some      |
 | permitted exceptions and without any warranty. For full license    |
 | and copyright information, see https://civicrm.org/licensing       |
 +--------------------------------------------------------------------+
*}
{* this template is used for adding/editing/deleting membership type  *}
{if $action eq 8}
  {include file="CRM/Core/Form/EntityForm.tpl"}
{else}
<div class="crm-block crm-form-block crm-membership-type-form-block">

  <div class="form-item" id="membership_type_form">
    <div class="crm-submit-buttons">{include file="CRM/common/formButtons.tpl" location="top"}</div>
    <table class="form-layout-compressed">
      {foreach from=$tpl_standardised_fields item=fieldName}
       {assign var=fieldSpec value=$entityFields.$fieldName}
       <tr class="crm-{$entityInClassFormat}-form-block-{$fieldName}">
          {include file="CRM/Core/Form/Field.tpl"}
        </tr>
      {/foreach}
      <tr class="crm-membership-type-form-block-financial_type_id">
        <td class="label">{$form.financial_type_id.label}</td>
        <td>{$form.financial_type_id.html}<br />
          <span class="description">{ts}Select the financial type assigned to fees for this membership type (for example 'Membership Fees'). This is required for all membership types - including free or complimentary memberships.{/ts}</span>
        </td>
      </tr>
      <tr class="crm-membership-type-form-block-auto_renew">
        <td class="label">{$form.auto_renew.label}</td>
        {if $authorize}
          <td>{$form.auto_renew.html}</td>
        {else}
          <td>{ts}You will need to select and configure a supported payment processor (currently Authorize.Net, PayPal Pro, or PayPal Website Standard) in order to offer automatically renewing memberships.{/ts} {docURL page="user/contributions/payment-processors"}</td>
        {/if}
      </tr>
      <tr class="crm-membership-type-form-block-duration_unit_interval">
        <td class="label">{$form.duration_unit.label}</td>
        <td>{$form.duration_interval.html}&nbsp;&nbsp;{$form.duration_unit.html}<br />
          <span class="description">{ts}Duration of this membership (e.g. 30 days, 2 months, 5 years, 1 lifetime){/ts}</span>
        </td>
      </tr>
      <tr class="crm-membership-type-form-block-period_type">
        <td class="label">{$form.period_type.label}</td>
        <td>{$form.period_type.html}<br />
          <span class="description">{ts}Select 'rolling' if membership periods begin at date of signup. Select 'fixed' if membership periods begin on a set calendar date.{/ts} {help id="period-type" file="CRM/Member/Page/MembershipType.hlp"}</span>
        </td>
      </tr>
      <tr id="fixed_start_day_row" class="crm-membership-type-form-block-fixed_period_start_day">
        <td class="label">{$form.fixed_period_start_day.label}</td>
        <td>{$form.fixed_period_start_day.html}<br />
          <span class="description">{ts}Month and day on which a <strong>fixed</strong> period membership or subscription begins. Example: A fixed period membership with Start Day set to Jan 01 means that membership periods would be 1/1/06 - 12/31/06 for anyone signing up during 2006.{/ts}</span>
        </td>
      </tr>
      <tr id="fixed_rollover_day_row" class="crm-membership-type-form-block-fixed_period_rollover_day">
        <td class="label">{$form.fixed_period_rollover_day.label}</td>
        <td>{$form.fixed_period_rollover_day.html}<br />
          <span class="description">{ts}Membership signups on or after this date cover the following calendar year as well. Example: If the rollover day is November 30, membership period for signups during December will cover the following year.{/ts}</span>
        </td>
      </tr>
      <tr id="month_fixed_rollover_day_row" class="crm-membership-type-form-block-fixed_period_rollover_day">
        <td class="label">{$form.month_fixed_period_rollover_day.label}</td>
        <td>{$form.month_fixed_period_rollover_day.html}<br />
          <span class="description">{ts}Membership signups on or after this day of the month cover the rest of the month plus the specified number of months.{/ts}</span>
        </td>
      </tr>
      <tr class="crm-membership-type-form-block-relationship_type_id">
        <td class="label">{$form.relationship_type_id.label}</td>
        <td>
          {$form.relationship_type_id.html}<br />
          <span class="description">{ts}Memberships can be automatically granted to related contacts by selecting a Relationship Type.{/ts} {help id="rel-type" file="CRM/Member/Page/MembershipType.hlp"}</span>
          {if $membershipRecordsExists}
            <div class="status message">{ts}There are membership records associated with this membership type. Changing this setting will not automatically update existing memberships (and those that would inherit a membership), which may cause inconsistent results.{/ts}</div>
          {/if}
        </td>
      </tr>
      <tr id="maxRelated" class="crm-membership-type-form-block-max_related">
        <td class="label">{$form.max_related.label}</td>
        <td>{$form.max_related.html}<br />
          <span class="description">{ts}Maximum number of related memberships (leave blank for unlimited).{/ts}</span>
        </td>
      </tr>
      <tr class="crm-membership-type-form-block-visibility">
        <td class="label">{$form.visibility.label}</td>
        <td>{$form.visibility.html}<br />
          <span class="description">{ts}Can this membership type be used for self-service signups ('Public'), or is it only for CiviCRM users with 'Edit Contributions' permission ('Admin').{/ts}</span>
        </td>
      </tr>
      <tr class="crm-membership-type-form-block-weight">
        <td class="label">{$form.weight.label}</td>
        <td>{$form.weight.html}</td>
      </tr>
      <tr class="crm-membership-type-form-block-is_active">
        <td class="label">{$form.is_active.label}</td>
        <td>{$form.is_active.html}</td>
      </tr>
    </table>
    <div class="spacer"></div>

    <fieldset><legend>{ts}Renewal Reminders{/ts}</legend>
      <div class="help">
        {capture assign=reminderLink}{crmURL p='civicrm/admin/scheduleReminders' q='reset=1'}{/capture}
        {icon icon="fa-info-circle"}{/icon}
        {ts 1=$reminderLink}Configure membership renewal reminders using <a href="%1">Schedule Reminders</a>. If you have previously configured renewal reminder templates, you can re-use them with your new scheduled reminders.{/ts} {docURL page="user/email/scheduled-reminders"}
      </div>
    </fieldset>

    {include file="CRM/common/customDataBlock.tpl"}

    <div class="crm-submit-buttons">{include file="CRM/common/formButtons.tpl" location="bottom"}</div>
  {/if}
    <div class="spacer"></div>
  </div>
</div>

{include file="CRM/common/deferredFinancialType.tpl" context='MembershipType'}
{literal}
<script type="text/javascript">
CRM.$(function($) {
  showHidePeriodSettings();
  $('#duration_unit').change(function(){
    showHidePeriodSettings();
  });

  $('#period_type').change(function(){
    showHidePeriodSettings();
  });

  {/literal}
  {if $action eq 2}
  {literal}
    showHideMaxRelated($('#relationship_type_id').val());
    $('#relationship_type_id').change(function(){
      showHideMaxRelated($('#relationship_type_id').val());
    });
  {/literal}{else}{literal}
    showHideMaxRelated($('#relationship_type_id :selected').val());
    $('#relationship_type_id').change(function(){
      showHideMaxRelated($('#relationship_type_id :selected').val());
    });
  {/literal}{/if}{literal}
});

function showHidePeriodSettings() {
  if ((cj("#period_type :selected").val() == "fixed") &&
    (cj("#duration_unit :selected").val() == "year")) {
    cj('#fixed_start_day_row, #fixed_rollover_day_row').show();
    cj('#month_fixed_rollover_day_row').hide();
    if (!cj("#fixed_period_start_day_M, #fixed_period_start_day_d").val()) {
      cj("#fixed_period_start_day_M, #fixed_period_start_day_d").val("1");
    }
    if (!cj("#fixed_period_rollover_day_M").val()) {
      cj("#fixed_period_rollover_day_M").val("12");
    }
    if (!cj("#fixed_period_rollover_day_d").val()) {
      cj("#fixed_period_rollover_day_d").val("31");
    }
    cj("#month_fixed_rollover_day_row").val("");
  }
  else if ((cj("#period_type :selected").val() == "fixed" ) &&
    (cj("#duration_unit :selected").val() == "month" )) {
    cj('#month_fixed_rollover_day_row').show();
    cj('#fixed_start_day_row, #fixed_rollover_day_row').hide();
    cj("#fixed_period_start_day_M, #fixed_period_start_day_d").val("");
    cj("#fixed_period_rollover_day_M, #fixed_period_rollover_day_d").val("");
  }
  else {
    cj('#fixed_start_day_row, #fixed_rollover_day_row, #month_fixed_rollover_day_row').hide();
    cj("#fixed_period_start_day_M, #fixed_period_start_day_d").val("");
    cj("#fixed_period_rollover_day_M, #fixed_period_rollover_day_d").val("");
    cj("#month_fixed_rollover_day_row").val("");
  }
}

//load the auto renew msg if recur allow.
{/literal}{if $authorize and $allowAutoRenewMsg}{literal}
CRM.$(function($) {
  setReminder( null );
});
{/literal}{/if}{literal}

function setReminder( autoRenewOpt ) {
  //don't process.
  var allowToProcess = {/literal}'{$allowAutoRenewMsg}'{literal};
  if ( !allowToProcess ) {
    return;
  }
  if ( !autoRenewOpt ) {
    autoRenewOpt = cj( 'input:radio[name="auto_renew"]:checked').val();
  }
  funName = 'hide();';
  if ( autoRenewOpt == 1 || autoRenewOpt == 2 ) funName = 'show();';
  eval( "cj('#autoRenewalMsgId')." + funName );
}

function showHideMaxRelated(relTypeId) {
  if (relTypeId) {
    cj('#maxRelated').show();
  }
  else {
    cj('#maxRelated').hide();
  }
}
</script>
{/literal}
