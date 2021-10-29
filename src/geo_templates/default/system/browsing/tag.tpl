{* 7.0.2-89-g637a04a *}

{include file='common/browse_mode_buttons.tpl'}

<div class="content_box clear">
	<h1 class="title">{$messages.500870} {$tag|replace:'-':' '|capitalize|escape}</h1>
	{include file=$browse_tpl}
</div>
{if $pagination}
	{$pagination}
{/if}