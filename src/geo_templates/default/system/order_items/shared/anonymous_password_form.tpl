{* 6.0.7-3-gce41f93 *}

{*  NOTE:  This template will need to be moved to the anon addon sooner or later.  *}

{include file="system/cart/cart_steps.tpl"}
<div class="content_box">
	<h1 class="title">{$msgs.passPageTitle}</h1>
	<form action="{$nextPage}" method="post">
		{if $error}
			<div class="field_error_box">{$error}</div>
		{/if}
		<div class="row_even">
			<label class="field_label" for="anonPass">{$msgs.passwordLabel}</label> <input type="text" name="anonPass" id="anonPass" value="{$passFromURL}" />
		</div>
		<div class="center">
			<input type="submit" value="{$msgs.passwordButtonText}" class="button" />
		</div>
	</form>
</div>

<br />

<div class="center">
	<a href="?a=cart&amp;action=cancel" class="cancel">{$msgs.passwordCancelLink}</a>
</div>
