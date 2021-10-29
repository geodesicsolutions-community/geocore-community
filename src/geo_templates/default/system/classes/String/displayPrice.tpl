{* 6.0.7-3-gce41f93 *}{strip}
{if $replace}
	{$replaceTxt}
{elseif $onlyPost}
	{$post}
{elseif $number === false}
	-
{else}
	{$pre}{$number}{if $post} {$post}{/if}
{/if}
{/strip}