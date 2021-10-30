{* 7.4beta1-359-g8dc9d39 *}
{if $inCart}
	<label>
		{$msgs.inputLabel}
		&nbsp;<input type='text' size='10' name='discount_code' value='{$discount_code}' onclick="jQuery('#checkout_clicked').val('click');" />
	</label>
	<input type='submit' value='{$msgs.updateLabel}' />
	<div class="clearfix"></div>
{elseif $discount_code}
	{$msgs.inputLabelAlt} ({$discount_code})
{/if}
{if $error}
<span class="error">
	{$msgs.errorMsg}
</span>
{/if}