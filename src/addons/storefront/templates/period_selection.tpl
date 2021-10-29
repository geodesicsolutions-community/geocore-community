{* 7.4.4-10-g8576128 *}
<div class="listing_extra_cost">
	{foreach from=$choices item="choice" key="id"}
		<div>
			<input type='radio' id='radio{$id}' name='c[subscription_choice]' value='{$choice.period_id}' {if $selected == $choice.period_id}checked="checked" {/if}/>
			<label for='radio{$id}'>
				{$choice.display_value}
			</label>
			{if !$allFree}
				<label for='radio{$id}' class="price">
					{$choice.amount|displayPrice}
				</label>
			{/if}
		</div>
	{/foreach}
</div>
