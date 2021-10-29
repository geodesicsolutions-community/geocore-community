{* 17.04.0-11-g07e7ed5 *}
{$admin_messages}
<form action="index.php?mc=email_config&page=email_notify_config" method="post" class="form-horizontal form-label-left">
<fieldset>
	<legend>Admin Notifications</legend>
	<div class="x_content">
		<div class='form-group'>
		<label class='control-label col-md-6 col-sm-6 col-xs-12'>Register Complete: <input type="checkbox" name="send_register_complete_email_admin" value="1" {if $send_register_complete_email_admin}checked="checked"{/if} /></label>  
		</div>

		<div class='form-group'>
		<label class='control-label col-md-6 col-sm-6 col-xs-12'>Registration Verification System: <br><span class="small_font"><a href="index.php?page=sections_registration_edit_text&b=20&l=1" class="small_font">Edit Text</a></span></label>
		  <div class='col-md-6 col-sm-6 col-xs-12'>
			<select name="email_verify_system" class="form-control col-md-7 col-xs-12">
				<option value="disabled|disabled" {if !$use_email_verification_at_registration && !$admin_approves_all_registration}selected="selected"{/if}>Disable Verification System</option>
				<option value="enabled|disabled" {if $use_email_verification_at_registration && !$send_register_attempt_email_admin}selected="selected"{/if}>Verify E-Mail, with Notify ADMIN on Attempt OFF</option>
				<option value="enabled|enabled" {if $use_email_verification_at_registration && $send_register_attempt_email_admin}selected="selected"{/if}>Verify E-Mail, with Notify ADMIN on Attempt ON</option>
				<option value="admin_approve" {if $admin_approves_all_registration}selected="selected"{/if}>Admin Approves All Registrations</option>
			</select>
		  </div>
		</div>

		<div class='form-group'>
		<label class='control-label col-md-6 col-sm-6 col-xs-12'>Successful Listing: <input type="checkbox" name="send_admin_placement_email" value="1" {if $send_admin_placement_email}checked="checked"{/if} /></label>  
		</div>

		<div class='form-group'>
		<label class='control-label col-md-6 col-sm-6 col-xs-12'>Notify When Manual Payment Chosen: <input type="checkbox" name="user_set_hold_email" value="1" {if $user_set_hold_email}checked="checked"{/if} /></label>  
		</div>

		<div class='form-group'>
		<label class='control-label col-md-6 col-sm-6 col-xs-12'>Order Item Awaiting Approval: <input type="checkbox" name="admin_notice_item_approval" value="1" {if $admin_notice_item_approval}checked="checked"{/if} /></label>  
		</div>		

		<div class='form-group'>
		<label class='control-label col-md-6 col-sm-6 col-xs-12'>Notify Before Listing or Subscription Expires: <input type="checkbox" name="send_admin_end_email" value="1" {if $send_admin_end_email}checked="checked"{/if} /></label>  
		</div>	

		<div class='form-group'>
		<label class='control-label col-md-6 col-sm-6 col-xs-12'>Notify When Listing is Edited: <input type="checkbox" name="admin_email_edit" value="1" {if $admin_email_edit}checked="checked"{/if} /></label>  
		</div>	

	</div>
</fieldset>
<fieldset>
	<legend>User Notifications</legend>
	<div class="x_content">
	
		<div class='form-group'>
		<label class='control-label col-md-6 col-sm-6 col-xs-12'>Register Complete: <input type="checkbox" name="send_register_complete_email_client" value="1" {if $send_register_complete_email_client}checked="checked"{/if} /><br><span class="small_font"><a href="index.php?page=sections_registration_edit_text&b=21&l=1" class="small_font">Edit Text</a></span> 
		</label>  
		</div>	

		{if $is_order_notify}
			<div class='form-group'>
				<label class='control-label col-md-6 col-sm-6 col-xs-12'>Order Approved: {$tooltips.order} <input type="checkbox" name="notify_user_order_approved" value="1" {if $notify_user_order_approved}checked="checked"{/if} /><br><span class="small_font"><a href="index.php?page=sections_listing_process_edit_text&b=10207&l=1" class="small_font">Edit Text</a></span>
				</label> 
			</div>
		{/if}
		
		<div class='form-group'>
		<label class='control-label col-md-6 col-sm-6 col-xs-12'>New Listing Approved &amp; Live: <input type="checkbox" name="send_successful_placement_email" value="1" {if $send_successful_placement_email}checked="checked"{/if} /><br><span class="small_font"><a href="index.php?page=sections_listing_process_edit_text&b=51&l=1" class="small_font">Edit Text</a></span> 
		</label>  
		</div>	

		{foreach $exp_settings as $setting => $info}
		<div class='form-group'>
				<label class='control-label col-md-6 col-sm-6 col-xs-12'>{$info.label}: <br>
					{if $info.page_id}<a href="index.php?page=sections_listing_process_edit_text&b={$info.page_id}&l=1" class="small_font">{/if}
						Edit Text
					{if $info.page_id}</a>{/if}
				</label>
		  <div class='col-md-6 col-sm-6 col-xs-12 input-group'>
				<input type="text" name="{$setting}" size="5" value="{$info.adjustedExpire}" class="form-control col-md-7 col-xs-12 input-group-width40" /> 
				<select name="{$setting}_unit" class="form-control col-md-7 col-xs-12 input-group-width60">
					<option value="{$day}"{if $info.timeUnit == $day} selected="selected"{/if}>Days</option>
					<option value="{$hour}"{if $info.timeUnit == $hour} selected="selected"{/if}>Hours</option>
					<option value="{$minute}"{if $info.timeUnit == $minute} selected="selected"{/if}>Minutes</option>
					<option value="1"{if $info.timeUnit == 1} selected="selected"{/if}>Seconds</option>
				</select>
				<div class='input-group-addon'>Warning (0 to disable)</div>
		  </div>
		</div>
		{/foreach}

		<div class='form-group'>
		<label class='control-label col-md-6 col-sm-6 col-xs-12'>Resend Listing Expiration Warnings: </label>
		  <div class='col-md-6 col-sm-6 col-xs-12'>
			<select name="send_ad_expire_frequency" class="form-control col-md-7 col-xs-12">
				{foreach from=$email_expire_frequencies item="period" key="secs"}
					<option value="{$secs}" {if $send_ad_expire_frequency == $secs}selected="selected"{/if}>{$period}</option>
				{/foreach}
			</select>
		  </div>
		</div>

		<div class='form-group'>
		<label class='control-label col-md-6 col-sm-6 col-xs-12'>Subscription Expires Soon:<br><a href="index.php?page=sections_user_mgmt_edit_text&b=87&l=1" class="small_font">Edit Text</a></label>
		  <div class='col-md-6 col-sm-6 col-xs-12 input-group'><input name='subscription_expire_period_notice' size='4' value='{$subscription_expire_period_notice|string_format:"%d"}' class='form-control col-md-7 col-xs-12 input-group' /> 
		  <div class='input-group-addon'>Day Warning (0 to disable)</div>
		  </div>
		</div>

		{if $send_balance_reminder_button}
		        <div class='form-group'>
		        <label class='control-label col-md-6 col-sm-6 col-xs-12'>Negative Account Balance Reminder:<br><a href="index.php?page=sections_user_mgmt_edit_text&b=177&l=1" class="small_font">Edit Text</a><br><span class="label-button">{$send_balance_reminder_button}</span> </label>
		          <div class='col-md-6 col-sm-6 col-xs-12 input-group'>
				<div class="form-control col-md-7 col-xs-12 input-group-width40" style="background-color: #EEE;">Every</div>
				<input name="negative_balance_reminder" value="{$negative_balance_reminder}" size="3" class="form-control col-md-7 col-xs-12 input-group-width60" style="border-right:1px solid #DDD;" /> 
				<div class='input-group-addon'>Days (0 to disable)</div>
		          </div>
		          
       		 	</div>
		{/if}
		
		{if $is_a}
		
		        <div class='form-group'>
		        <label class='control-label col-md-6 col-sm-6 col-xs-12'>Final Fees Due Reminder:<br><a href="index.php?page=sections_user_mgmt_edit_text&b=10213&l=1" class="small_font">Edit Text</a><br><span class="label-button"><a href="{$send_final_fees_reminder_link}" target="_new" class="mini_button">Manually Send E-Mails Now</a><span></label>
		          <div class='col-md-6 col-sm-6 col-xs-12 input-group'>
				<div class="form-control col-md-7 col-xs-12 input-group-width40" style="background-color: #EEE;">Every</div>
				<input type="number" name="final_fees_due_reminder" value="{$final_fees_due_reminder}" size="3" min="0" max="500" class="form-control col-md-7 col-xs-12 input-group-width60" style="border-right:1px solid #DDD;" /> 
				<div class='input-group-addon'>Days (0 to disable)</div>
		          </div>
		          
       		 	</div>

			<div class='form-group'>
			<label class='control-label col-md-6 col-sm-6 col-xs-12'>Seller Notification: Auction Unsuccessful: <input type="checkbox" name="notify_seller_unsuccessful_auction" value="1" {if $notify_seller_unsuccessful_auction}checked="checked"{/if} /></label>  
			</div>

		{/if}
		
	</div>
</fieldset>
<fieldset>
	<legend>Prevent e-mail Flooding</legend>
	<div class="x_content">
		<div class='form-group'>
		<label class='control-label col-md-6 col-sm-6 col-xs-12'>Contact Seller: {$tooltips.flood_contact_seller}</label>
		  <div class='col-md-6 col-sm-6 col-xs-12 input-group'><input type='text' name='contact_seller_limit' size='4' value='{$contact_seller_limit|string_format:"%d"}' class='form-control col-md-7 col-xs-12 input-group' /> 
		  <div class='input-group-addon'>messages per sender, per hour (0 to disable)</div>
		  </div>
		</div>
	</div>
</fieldset>
<div style="text-align:center"><input type="submit" name="auto_save" value="Save" /></div>
</form>