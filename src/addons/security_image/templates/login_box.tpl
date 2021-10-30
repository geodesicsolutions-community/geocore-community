{* 7.4beta1-361-g791e99c *}

{add_footer_html}
{include file='js.tpl'}
{/add_footer_html}

<div class="row_even">
	<label for="b[securityCode]" class="login_label">{$label}</label>
	{if $imageType=='recaptcha'}
		{include file='recaptcha.tpl'}
	{else}
		<input name="b[securityCode]" id="b[securityCode]" size="4" class="field" type="text" />
	
		<div id="addon_security_image" class="center" style="width: {$w}px; height: {$h}px;">
			<a href="javascript:void(0)" onclick="changeSecurityImage();">
				<img src="{$classifieds_file_name}?a=ap&amp;addon=security_image&amp;page=image&amp;no_ssl_force=1" alt="Security Image" style="width: {$w}px; height: {$h}px;" />
			</a>
		</div>
	{/if}
</div>