{* 7.4beta1-362-g6f5c580 *}
{if $edit eq 1}
	<label>
		{$messages.500313}: {$precurrency}<input type='text' name='account_balance_add' value='{$price}' size="4" class="field" />{$postcurrency}
		{* (Current Balance: <em>{$current_balance|displayPrice}</em>) *}
	</label>
	
	<input type="submit" value="{$messages.500589}" class="button" onclick="jQuery('#checkout_clicked').val('click');" />
{else}
	{$messages.500313}
{/if}