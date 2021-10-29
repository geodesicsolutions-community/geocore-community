{* 16.09.0-87-g69e04de *}

{if !$is_ajax}<input type="hidden" value="{$value.id}" /><div class="enabledButton">{/if}
{if $value.enabled=='yes'}
	<img src="admin_images/bullet_success.png" alt="Enabled" style="width: 18px; height: 18px;" />
{else}
	<img src="admin_images/bullet_error.png" alt="Disabled" style="width: 18px; height: 18px;" />
{/if}
{if !$is_ajax}</div>{/if}

{if $is_ajax}

<script type="text/javascript">
//<![CDATA[
	{if $value.enabled=='yes'}
		$('row_{$value.id}').down().next().removeClassName('disabled')
			.select('.disabledSection').each(function(elem){ elem.hide(); });
	{else}
		$('row_{$value.id}').down().next().addClassName('disabled')
			.select('.disabledSection').each(function(elem){ elem.show(); });
	{/if}
//]]>
</script>

{/if}