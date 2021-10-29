{* 6.0.7-3-gce41f93 *}

{foreach from=$listing_tags_array item=tag name=tagList}{strip}
	<a href="{$classifieds_file_name}?a=tag&amp;tag={$tag|escape}" class="listing_tag">
		{$tag|replace:'-':' '|capitalize|escape}
	</a>
	{/strip}{if !$smarty.foreach.tagList.last}, {/if}
{/foreach}