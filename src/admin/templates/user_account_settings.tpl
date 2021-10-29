{* 16.09.0-79-gb63e5d8 *}
{$admin_messages}

<script type="text/javascript">
//<![CDATA[
	Event.observe(window, 'load', function () {
		$('verify_accounts').observe('click', function () {
			$('verify_settings')[(this.checked? 'show':'hide')]();
		});
	});
//]]>
</script>

<form action="" method="post" class="form-horizontal form-label-left">
	<fieldset>
		<legend>Main Settings</legend>
		<div>
			<div class="form-group">
				<label class="control-label col-md-5 col-sm-5 col-xs-12">Post-Login Landing Page {$tooltips.post_login_page}</label>
				<div class="col-md-6 col-sm-6 col-xs-12">
					<input type="radio" name="b[post_login_page]" {if $settings.post_login_page == 0}checked="checked"{/if} value="0" /> My Account Home<br />
					<input type="radio" name="b[post_login_page]" {if $settings.post_login_page == 1}checked="checked"{/if} value="1" /> Site Home<br />
					<input type="radio" name="b[post_login_page]" {if $settings.post_login_page == 2}checked="checked"{/if} value="2" /> Other:
						<input type="text" name="b[post_login_url]" value="{$settings.post_login_url}" placeholder="http://example.com/home.html" size="30" />
				</div>
			</div>
			
			<div class="form-group">
				<label class="control-label col-md-5 col-sm-5 col-xs-12">Number of rows per table {$tooltips.my_account_table_rows}</label>
				<div class="col-md-6 col-sm-6 col-xs-12">
					<input class="form-control col-md-7 col-xs-12" type="text" name="b[my_account_table_rows]" value="{$settings.my_account_table_rows}" />
				</div>
			</div>
			
			<div class="form-group">
				<label class="control-label col-md-5 col-sm-5 col-xs-12">Show icons for addons in "My Account Links" {$tooltips.show_addon_icons}</label>
				<div class="col-md-6 col-sm-6 col-xs-12">
					<input type="radio" name="b[show_addon_icons]" {if $settings.show_addon_icons != 1}checked="checked"{/if} value="0" /> Off<br />
					<input type="radio" name="b[show_addon_icons]" {if $settings.show_addon_icons == 1}checked="checked"{/if} value="1" /> On<br />
					
				</div>
			</div>
		</div>
	</fieldset>
	<fieldset>
		<legend>Verify Account Settings</legend>
		<div>
			<p class="page_note">
				This software offers a "Verify Account" order item feature that charges $1 (by default) to verify a user's account.  That order item has
				additional price plan specific settings within <strong>Pricing > Price Plans Home > [edit button] > Cost Specifics</strong>,
				such as turning the item off or changing the price.
				<br /><br />
				Those price plan item settings will only be available if <strong>Enable Account Verification System</strong> is enabled (checked)
				below.
			</p>
			
			<div class='form-group'>
			<label class='control-label col-md-4 col-sm-4 col-xs-12'></label>
			  <div class='col-md-7 col-sm-7 col-xs-12'>
			    <input type="checkbox" name="verify_accounts" id="verify_accounts" value="1" {if $verify_accounts}checked="checked"{/if} />&nbsp;
			    Enable Account Verification System (anti-SPAM measure designed for "mostly free" sites)
			  </div>
			</div>	
			
			<div id="verify_settings"{if !$verify_accounts} style="display: none;"{/if}>
			
				<div class='form-group'>
				<label class='control-label col-md-4 col-sm-4 col-xs-12'></label>
				  <div class='col-md-7 col-sm-7 col-xs-12'>
				    <input type="checkbox" name="nonverified_require_approval" value="1" {if $nonverified_require_approval}checked="checked"{/if} />&nbsp;
				    All order items "Require Admin Approval" for Non-verified Accounts (including Anonymous Listings)
				  </div>
				</div>	
			
				<div class='form-group'>
				<label class='control-label col-md-4 col-sm-4 col-xs-12'></label>
				  <div class='col-md-7 col-sm-7 col-xs-12'>
				    <input type="checkbox" name="auto_verify_with_payment" value="1" {if $auto_verify_with_payment}checked="checked"{/if} />&nbsp;
				    Verify account when user pays for anything.
				  </div>
				</div>	

			</div>
		</div>
	</fieldset>
	<fieldset>
		<legend>User Ratings</legend>
		<div>
			<div class="page_note">Activate User Ratings by adding the listing tag: <b>{literal}{listing tag='user_rating'}{/literal}</b></div>
			<div class='form-group'>
			<label class='control-label col-md-5 col-sm-5 col-xs-12'>Low Rating Threshold: </label>
			  <div class='col-md-6 col-sm-6 col-xs-12'>
			    If a user's average rating from 10 or more users drops below <input type="text" name="b[user_rating_low_threshold]" value="{$user_rating_low_threshold}" size="4" /> (0 to disable), send an email notification to (next setting below):
			  </div>
        		</div> 
    
			<div class='form-group'>
			<label class='control-label col-md-5 col-sm-5 col-xs-12'>Send Low Rating Email Notification to: </label>
			  <div class='col-md-6 col-sm-6 col-xs-12'>
				<input type="checkbox" name="b[user_rating_low_notify_user]" {if $user_rating_low_notify_user==1}checked="checked"{/if} value="1" /> The low-rated user<br />
				<input type="checkbox" name="b[user_rating_low_notify_admin]" {if $user_rating_low_notify_admin==1}checked="checked"{/if} value="1" /> The site admin
			  </div>
        		</div> 
        		
		</div>
	</fieldset>
	<fieldset>
		<legend>Boxes</legend>
		<div>
			<div class="page_note">
				Below is the list of all the information "boxes" that can be shown on the My Account Home Page. Select the ones you want to show.
			</div>
			<div>
				{foreach $boxes as $id => $box}
					<div class="form-group">
						<label class="control-label col-md-5 col-sm-5 col-xs-12">{$box.name}</label>
						<div class="col-md-6 col-sm-6 col-xs-12">
							<input type="checkbox" name="b[my_account_show_{$id}]" {if $box.setting}checked="checked"{/if} value="1" />
						</div>
					</div>
					{if $id == 'recently_sold'}
						<div class="form-group">
							<label class="control-label col-md-5 col-sm-5 col-xs-12">Number of days since ending that a listing counts as "recently sold"</label>
							<div class="col-md-6 col-sm-6 col-xs-12">
								<input class="form-control col-md-7 col-xs-12" type="text" name="b[my_account_recently_sold_time]" value="{$settings.my_account_recently_sold_time}" />
							</div>
						</div>
					{/if}
				{/foreach}
			</div>
		</div>
	</fieldset>
	
	<div style="width: 100%; margin: 10px auto; text-align: center;"><input type="submit" name="auto_save" value="Save" /></div>
</form>