{* 7.0.2-183-gfa134f3 *}

{include file='common/browse_mode_buttons.tpl'}

<div class="clear"></div>

<div class="content_box">
	<h1 class="title">{$messages.593}</h1>
	{include file=$browse_tpl}
	<div class="center">
		<br />
		<a href="{$classifieds_file_name}?a=19" class="button">{$messages.591}</a>
	</div>
</div>
{if $pagination}
	{$pagination}
{/if}