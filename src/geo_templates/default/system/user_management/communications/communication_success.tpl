{* 7.5.3-125-gf5f0a9a *}

<div class="content_box">
	{if $uid}
		<h1 class="title my_account">{$messages.623}</h1>
		<h3 class="subtitle">{$messages.399}</h3>
	{/if}
	
	<div class="success_box">{$messages.407}</div>
</div>

{if $uid}
	<div class="center">
		<a href="{$userManagementHomeLink}" class="button">{$messages.408}</a>
	</div>
{/if}