{* 6.0.7-3-gce41f93 *}

<div class="row_even{if $error} field_error_row{/if}">
	<label for="c_securityCode" class="required">{$label}</label>
	{if $imageType=='recaptcha'}
		{include file='recaptcha.tpl'}
	{else}
		<input type="text" name="c[securityCode]" id="c_securityCode" class="field" />
		{if $error}<span class="error_message">{$error}</span>{/if}
		<div id="addon_security_image" style="width: {$w}px; height: {$h}px;" class="clear">
			<a href="javascript:void(0)" onclick="changeSecurityImage();">
				<img src="{$classifieds_file_name}?a=ap&amp;addon=security_image&amp;page=image&amp;no_ssl_force=1" alt="Security Image" />
			</a>
		</div>
	{/if}
</div>