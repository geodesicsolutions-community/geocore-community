{* 7.4beta1-52-g0f55a7d *}
<script>
	var changeSecurityImage = function() {
		{if $imageType=='recaptcha'}
			Recaptcha.reload();
		{else}
			var a = new Date();
			jQuery('#addon_security_image img').attr('src', '{$classifieds_file_name}?a=ap&addon=security_image&page=image&no_ssl_force=1&time='+a.getTime());
		{/if}
	}
</script>