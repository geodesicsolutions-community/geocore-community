{* 6.0.7-3-gce41f93 *}
<h1 class="subtitle">{$title}</h1>
{if $error}
	<div class="field_error_box">
		{if $link}<a href="{$link}">{/if}
			{$error}
		{if $link}</a>{/if}
	</div>
{/if}