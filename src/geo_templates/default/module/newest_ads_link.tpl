{* 6.0.7-3-gce41f93 *}
{if $buttonStyle}
	<a href="{$href}" class="button">
{else}
	<div class="row_even"><strong><a href="{$href}">
{/if}
{$label}
{if !$buttonStyle}
	</a></strong></div>
{else}
	</a>
{/if}