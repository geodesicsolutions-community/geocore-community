{* 7.6.3-149-g881827a *}
{$error}
{if !$only_show_error}

	<h1 class="subtitle">{$messages.332}</h1>
	<form action="{$form_target}" method="post">
		<p class="page_instructions">
			{$messages.333}
		
			{if $encode}<input type="hidden" name="c" value="{$encode}" />{/if}
			{if $auth_messages.login}
				<br /><span class="error_message">{$auth_messages.login|fromDB}</span>
			{/if}
			{if $must_login}
				<br /><span class="error_message">{$must_login}</span>
			{/if}
		</p>
		<div class="{if $error_messages.username}field_error_row{else}row_even{/if}">
			<label for="username" class="login_label">{$messages.334}</label>
			<input type="text" id="username" name="b[username]" size="20" {if $username} value="{$username}"{/if}  class="field login_field" />
			{if $error_messages.username}<br />
				<span class="error_message">{$error_messages.username|fromDB}</span>
			{/if}
		</div>
		<div class="{if $error_messages.password}field_error_row{else}row_odd{/if}">
			<label for="password" class="login_label">{$messages.335}</label>
			<input id="password" type="password" name="b[password]" size="20" class="field login_field" />
			{if $error_messages.password}<br />
				<span class="error_message">{$error_messages.password|fromDB}</span>
			{/if}
		</div>
		
		<span class="login-bold">{$securityImageHTML}</span>
		
		<div class="center">
			<input type="submit" name="submit" value="{$messages.336}" class="button" />
		</div>
		
		{if $forgotPasswordLink}
			<div class="cntr link">
				<a href="{$forgotPasswordLink}">{$messages.1316}</a>
			</div>
		{/if}
		<div class="cntr link" style="margin: 0 0 10px 0;">	
			<a href="{$registrationLink}">{$messages.1317}</a>
		</div>
		{$addons_bottom}
	</form>
{/if}