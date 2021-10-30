{* 7.4.4-10-g8576128 *}
<br />

<div class="content_box">
	<h2 class="title">{$msgs.account_info_section_title}</h2>
	<p class="page_instructions">{$msgs.account_info_section_desc}</p>
	
	{if $recurringId}
		<div class="{cycle values='row_odd,row_even'}">
			<label class="field_label">
				{$msgs.recurring_sub_price_label}
			</label>
			{$cyclePrice} {$msgs.recurring_sub_price_every} {$cycleDuration} {$msgs.recurring_sub_price_days}
		</div>
		
		<div class="{cycle values='row_odd,row_even'}">
			<label class="field_label">
				{$msgs.recurring_sub_next_payment_label}
			</label>
			{$nextCycleDate}
		</div>
		
		<div class="{cycle values='row_odd,row_even'}">
			<label class="field_label">
				{$msgs.recurring_sub_cancel_label}
			</label>
			<a href="{$cancelRecurringLink}" class="mini_button lightUpLink">{$msgs.recurring_sub_cancel_link}</a>
		</div>
	{else}
		<div class="{cycle values='row_odd,row_even'}">
			<label class="field_label">
				{$msgs.sub_expires_label}
			</label>
			{$expiration_date}
		</div>
		
		{if $showRenewLink}
			<div class="{cycle values='row_odd,row_even'}">
				<label class="field_label">
					{$msgs.sub_renew_label}
				</label>
				<a href="{$classifieds_file_name}?a=cart&amp;action=new&amp;main_type=storefront_subscription" class="mini_button">{$msgs.sub_renew_link_txt}</a>
			</div>
		{/if}
	{/if}
</div>