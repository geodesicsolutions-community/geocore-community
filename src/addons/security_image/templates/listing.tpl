{* 6.0.7-3-gce41f93 *}
<label for="b[securityCode]" class="field_label">{$label}</label>
{if $imageType=='recaptcha'}
	{include file='recaptcha.tpl'}
{else}
	<input type="text" name="b[securityCode]" id="b[securityCode]" size="12" class="field" />
	{if $error}<span class="error_message">{$error}</span>{/if}
	<div>
		<div id="addon_security_image" style="width: {$w}px; height: {$h}px;">
			<a href="javascript:void(0)" onclick="changeSecurityImage();">
				<img src="{$classifieds_file_name}?a=ap&amp;addon=security_image&amp;page=image&amp;no_ssl_force=1" alt="Security Image" />
			</a>
		</div>
	</div>
{/if}