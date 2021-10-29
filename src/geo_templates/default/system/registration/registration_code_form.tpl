{* 7.5.3-125-gf5f0a9a *}

<div class="content_box">
	<h1 class="title">{$messages.613}</h1>
	{$addons_top}
	<h3 class="subtitle">{$messages.232}</h3>
	<p class="page_instructions">{$messages.233}</p>
	
	{if $error_msg}
		<div class="field_error_box">{$error_msg}</div>
	{/if}
	<br />
	<form action="{$registration_url}" method="post">
		<div class="row_even center">
			<label for="registration_code" class="bold">{$messages.235}</label>
			<input type="text" id="registration_code" name="c[registration_code]" {if $badCode}value="{$badCode}"{/if} size="30" maxlength="30" class="field" />		
		</div>
		<div class="center">
			<input type="submit" name="c[submit_registration_code]" value="{$messages.236}" class="button" tabindex="0" />
			
			<input type="submit" name="c[bypass_registration_code]" value="{$messages.237}" class="button" />			
			
			<div class="clr"><br /></div>
			
			<a href="{$registration_url}?b=4" class="cancel">{$messages.887}</a>
		</div>
	</form>
</div>