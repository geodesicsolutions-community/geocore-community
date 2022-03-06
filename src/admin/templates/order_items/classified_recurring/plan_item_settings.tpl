{* 16.02.1-34-g43e3b3b *}
<div class="{cycle values='row_even,row_odd'}">
	<div class="page_note_error">
		Recurring Classifieds are currently in a Beta / Experimental state.<br />
		The functionality is available for testing, but is considered unstable and not yet recommended for use on a production site! Please submit feedback/bugs to Geo Support.
	</div>
</div>
<div class="{cycle values='row_even,row_odd'}">
	<div class="leftColumn">Enable</div>
	<div class="rightColumn">
		{if $recurring_billing_available}
			<input type="checkbox" name="classified_recurring[enabled]" value="1" {if $enabled}checked="checked"{/if} onclick="jQuery('#recurring_classifieds_settings').toggle(this.checked);" />
		{else}
			(Enable Recurring Billing for at least one Payment gateway to activate)
		{/if}
	</div>
	<div class="clearColumn"></div>
</div>
<div {if !$enabled}style="display: none;"{/if} id="recurring_classifieds_settings">
	<div class="leftColumn">Subscription Periods</div>
	<div class="rightColumn">
		<table border="1" cellpadding="1" style="text-align: center;">
			<tr>
				<th>Displayed as</th>
				<th>Period Length (days)</th>
				<th>Fee Assessed Per Period</th>
			</tr>
			{foreach $lengths as $l}
				<tr class="{cycle values='row_color2,row_color1'}">
					<td>{$l.period_display}</td>
					<td>{$l.period}</td>
					<td>{$l.price|displayPrice}</td>
				</tr>
			{foreachelse}
				<tr><td colspan="3">You have not yet entered any Subscription Lengths</td></tr>
			{/foreach}
		</table>
		<div class="center">
			<a href="index.php?mc=pricing&page=listing_subscription_periods&price_plan_id={$price_plan_id}&category_id={$category_id}">
				Add/Edit Subscription Length Choices
			</a>
		</div>
	</div>
	<div class="clearColumn"></div>
</div>