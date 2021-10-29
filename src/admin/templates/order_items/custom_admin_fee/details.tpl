{* 7.0.2-319-g05e6409 *}

{include file="cart_steps.tpl" g_type="system" g_resource="cart"}

<form action="{$process_form_url}" method="post">
	<div class="content_box">
		<h1 class="title">Custom Fee</h1>
		<h1 class="subtitle">Set Custom Fee Details</h1>
		<p class="page_instructions">
			Set the label, price, and other options for the custom fee.
		</p>
		
		{if $error_msgs.cart_error}
			<div class="field_error_box">
				{$error_msgs.cart_error}
			</div>
		{/if}
		
		<div class="{if $error_msgs.label}field_error_row {/if}{cycle values='row_odd,row_even'}">
			<label for="custom_admin_fee_label" class="field_label required">Item Label</label>
			<input type="text" id="custom_admin_fee_label" class="field" name="custom_admin_fee[label]" value="{$label}" size="40" />
			
			{if $error_msgs.label}
				<span class="error_message">{$error_msgs.label}</span>
			{/if}
		</div>
		<div class="{if $error_msgs.cost}field_error_row {/if}{cycle values='row_odd,row_even'}">
			<label for="custom_admin_fee_cost" class="field_label required">Cost</label>
			<span class="precurrency">{$precurrency}</span>
			<input type="text" id="custom_admin_fee_cost" class="field" name="custom_admin_fee[cost]" value="{$cost|displayPrice:'':''}" size="6" />
			<span class="postcurrency">{$postcurrency}</span>
			
			{if $error_msgs.cost}
				<span class="error_message">{$error_msgs.cost}</span>
			{/if}
		</div>
		
		<div class="{if $error_msgs.notify}field_error_row {/if}{cycle values='row_odd,row_even'}">
			<label for="custom_admin_fee_notify" class="field_label">
				Notify E-mail(s) when Payment Received
				<br />
				<span class="small_font">(Comma-seperated for multiple, or leave blank for no notifications)</span>
			</label>
			<input type="text" class="field" id="custom_admin_fee_notify" name="custom_admin_fee[notify]" value="{$notify|escape}" size="40" />
			{if $error_msgs.notify}
				<span class="error_message">{$error_msgs.notify}</span>
			{/if}
		</div>
		
		<div class="{if $error_msgs.cost}field_error_row {/if}{cycle values='row_odd,row_even'}">
			<label for="custom_admin_fee_removable" class="field_label">Removable by client?</label>
			<input type="checkbox" id="custom_admin_fee_removable" name="custom_admin_fee[removable]" value="1"{if $removable} checked="checked"{/if} />
		</div>
	</div>
	<div class="clr"><br /></div>
	<div class="center">
		<input type="submit" name="submit" value="Continue" class="button" />
		<br /><br />
		<a href="{$cart_url}&amp;action=cancel" class="cancel">Cancel/Remove from Cart</a>
	</div>
</form>