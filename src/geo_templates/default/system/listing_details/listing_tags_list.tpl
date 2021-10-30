{* 6.0.7-3-gce41f93 *}

{foreach from=$listing_tags_array item=tag name=tagList}{$tag|replace:'-':' '|capitalize|escape}{if !$smarty.foreach.tagList.last}, {/if}{/foreach}
