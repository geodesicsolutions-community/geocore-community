{* 7.5.3-125-gf5f0a9a *}

{if $json}
	<div class="success_box">{$instructions}</div>
{else}
	<div class="content_box">
		<h1 class="title">{$section_title}</h1>
		<h3 class="subtitle">{$page_title}</h3>	
		<div class="success_box">{$instructions}</div>
	</div>

	<div class="center">
		<a href="{$link}" class="button">{$link_text}</a>
	</div>
{/if}