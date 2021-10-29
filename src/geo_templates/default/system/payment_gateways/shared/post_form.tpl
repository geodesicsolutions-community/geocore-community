{* 7.5.3-36-gea36ae7 *}
<div style="width: 100%;">
	<div style="width: 50px; margin: 10px auto;">
		<img src="{external file='images/loading.gif'}" alt="" />
	</div>
	<form action="{$post_url}" method="post" id="gateway_post">
		{foreach from=$post_fields item=field key=index}
			<input type="hidden" name="{$index}" value="{$field}" />
		{/foreach}
		<input type="submit" value="continue to gateway" style="display: none;" />
	</form>
	{add_footer_html}
		<script type="text/javascript">
			//<![CDATA[
			gjUtil.autoSubmitForm("gateway_post");
			//]]>
		</script>
	{/add_footer_html}
</div>