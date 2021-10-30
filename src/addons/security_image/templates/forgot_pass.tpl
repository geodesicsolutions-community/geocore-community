{* 6.0.7-3-gce41f93 *}

<div class="row_odd" style="margin: 0 auto; text-align: center;">
	<label for="b[securityCode]" class="login_label">{$label}</label>
	{if $imageType=='recaptcha'}
		{include file='recaptcha.tpl'}
	{else}
		<input type="text" name="b[securityCode]" id="b[securityCode]" size="12" class="field" />
		{* note: showing any error messages is handled by the main lost-password page -- don't double it here *}
		<div class="center" style="width: 100%;">
			<div id="addon_security_image" style="width: {$w}px; height: {$h}px; margin: 0px auto;">
				<a href="javascript:void(0)" onclick="changeSecurityImage();">
					<img src="{$classifieds_file_name}?a=ap&amp;addon=security_image&amp;page=image&amp;no_ssl_force=1" alt="Security Image" />
				</a>
			</div>
		</div>
	{/if}
</div>
