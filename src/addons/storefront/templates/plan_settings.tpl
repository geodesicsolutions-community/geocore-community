{* 16.02.0-8-g674a411 *}
<div class="{cycle values="row_color1,row_color2"}">
	<div class="leftColumn">Enabled{$tooltip_allowed}</div>
	<div class="rightColumn">
		<input type="checkbox" name="storefront_subscription[enabled]"
			id="storefront_subscription_enabled" value="1"
			{if $enabled}checked="checked"{/if}
			{if !$choices OR count($choices) == 0}disabled="disabled"{/if}
			onclick="jQuery('#storefrontSubData').toggle(this.checked);" />
		{if $choices && !$periodCheck}
			<span class='medium_error_font' id="needsPeriodError">Please attach subscription periods</span>
		{elseif !$choices}
			<a class='medium_error_font' href="?page=storefront_subscription_choices">Please Add Subscription Periods</a>
		{/if}
	</div>
	<div class="clearColumn"></div>
</div>
<div id="storefrontSubData" {if !$enabled}style="display: none;"{/if}>
	<div class="{cycle values="row_color1,row_color2"}">
		<div class="leftColumn">Storefronts Free For Users in this price plan{$tooltip_free}</div>
		<div class="rightColumn">
			<input type="checkbox" name="storefront_subscription[free_storefronts]" value="1" {if $free_storefronts}checked="checked"{/if} onclick="jQuery('#storefrontChoices').toggle(!this.checked);" />
		</div>
		<div class="clearColumn"></div>
	</div>
	<div class="{cycle values='row_color1,row_color2'}" id="storefrontChoices" {if $free_storefronts}style="display: none;"{/if}>
		<div class="leftColumn">Attach Storefront Subscription Periods{$tooltip_subscriptions}</div>
		<div class="rightColumn">
			{foreach from=$choices item="choice"}
			<label><input type="checkbox" class="storefrontPeriod" 
				name="storefront_subscription[storefront_periods][{$choice.period_id}]"
				id="storefront_subscription[storefront_periods][{$choice.period_id}]" value="{$choice.period_id}"
				{if $periods[$choice.period_id]}checked="checked" {/if}
				onclick="jQuery('#needsPeriodError').toggle(jQuery('.storefrontPeriod:checked').length==0);" />{$choice.display_value} - {$choice.amount|displayPrice}</label>
				<br />
			{/foreach}
		</div>
		<div class="clearColumn"></div>
	</div>
</div>
