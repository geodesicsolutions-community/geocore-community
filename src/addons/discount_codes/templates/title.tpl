{* 6.0.7-3-gce41f93 *}
{if $inCart}
	<label>
		{$msgs.inputLabel}<br />
		&nbsp;<input type='text' size='10' name='discount_code' value='{$discount_code}' />
	</label>
	<input type='submit' value='{$msgs.updateLabel}' class="mini_button" />
{elseif $discount_code}
	{$msgs.inputLabelAlt} ({$discount_code})
{/if}
{if $error}
	<br />
	<span class="error_message">
		{$msgs.errorMsg}
	</span>
{/if}