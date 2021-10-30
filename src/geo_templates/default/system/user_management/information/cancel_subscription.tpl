{* 7.3beta3 *}

{* This is the mini-template used for the yes/no page. *}

{if $showConfirm}
	<div style="text-align: center;">
		<h2>{$messages.500732}</h2>
		<p>{$messages.500733}</p>
		<br />
		<a href="{$confirmCancelUrl}" class="lightUpLink" onclick="return false;">{$messages.500734}</a> &nbsp; <a href="{$accountInfoUrl}" class="closeLightUpBox" onclick="return false;">{$messages.500735}</a>
	</div>
{elseif $failed}
	<div style="text-align: center;">
		{$failedMessage}
		<br />
		<a href="{$accountInfoUrl}" class="closeLightUpBox" onclick="return false;">{$messages.500756}</a>
	</div>
{else}
	{$messages.500736}
	{add_footer_html}
	{literal}
		<script type="text/javascript">
			jQuery(document).gjLightbox('close');
			location.reload(true);
		</script>
	{/literal}
	{/add_footer_html}
{/if}