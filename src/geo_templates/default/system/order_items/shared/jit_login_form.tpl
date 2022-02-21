{* 17.03.0-30-g0210372 *}

{include file='system/cart/cart_steps.tpl'}

<div class="jit_login_form" style="text-align: center; width: 95%; margin: 0 auto;">

{if $didResend}<p style="color: #980000;">{$messages.502207}</p>{/if}

{if $emailExists}
	<p>{$messages.500769}</p>
	<p style="font-size: 18pt;"><a href="{$loginURL}">{$messages.500771}</a>{$messages.500772}<a href="{$backURL}">{$messages.500773}</a></p>
{elseif !$allow_user_pass}
	<p>{$messages.500770}</p>
	{if $require_email_confirmation}
		<p>{$messages.502204}<span style="color: #980000">{$email}</span>{$messages.502205}</p>
	{/if}
	<form action="{$continueURL}" id="continueForm" method="post">
		{if $errorMsg}<p class="error_message">{$errorMsg}</p>{/if}
		{$securityImageHTML}
		{if $require_email_confirmation}
			<p>{$messages.502446} <input type="text" name="confirmation_code" /> <input type="button" class="button" value="{$messages.502206}" onclick="window.location='{$continueURL}&resend=1'" /></p>
		{/if}
		<p style="font-size: 18pt;"><a href="{$loginURL}">{$messages.500771}</a>{$messages.500772}<a href="{$continueURL}" onclick="jQuery('#continueForm').submit(); return false;">{$messages.500774}</a></p>
	</form>
{else}
	<p>{$messages.500788}</p>
	{if $require_email_confirmation}
		<p>{$messages.502204}<br /><span style="color: #4174a6; font-weight: bold;">{$email}</span><br />{$messages.502205}</p>
	{/if}
	<p><a href="{$loginURL}" class="button" style="font-size: 1.2em;">{$messages.500771}</a></p>
	<p style="font-size: 18pt;">
		<form action="{$continueURL}" method="post">
			<table style="width: 100%; text-align: left; font-size: .9em; font-weight: bold;">
				<tr>
					<td colspan="2" style="text-align: center;" class="error_message">{$errorMsg}</td>
				</tr>
				{if $require_email_confirmation}
					<tr>
						<td style="text-align: right; width: 50%;">{$messages.502446}</td><td><input type="text" name="confirmation_code" class="field login_field" /> <input type="button" value="{$messages.502206}" onclick="window.location='{$continueURL}&resend=1'" /></td>
					</tr>
				{/if}
				<tr>
					<td style="text-align: right; width: 50%;">{$messages.500789}</td><td><input type="text" name="username" maxlength="{$max_user_length}" class="field login_field" /></td>
				</tr>
				<tr>
					<td style="text-align: right; width: 50%;">{$messages.500790}</td><td><input type="password" name="password" maxlength="{$max_pass_length}" class="field login_field" /></td>
				</tr>
				<tr>
					<td style="text-align: right; width: 50%;">{$messages.500791}</td><td><input type="password" name="confirm" maxlength="{$max_pass_length}" class="field login_field" /></td>
				</tr>
				{if $securityImageHTML}
					<tr>
						<td colspan="2" style="text-align: center;">{$securityImageHTML}</td>
					</tr>
				{/if}
				<tr>
					<td colspan="2" style="text-align: center;"><input type="submit" class="button" style="font-size: 1.2em;" value="{$messages.500792}" /></td>
				</tr>
			</table>
		</form>
	</p>
{/if}



</div>
