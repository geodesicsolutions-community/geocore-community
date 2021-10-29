{* 16.09.0-79-gb63e5d8 *}


{foreach from=$page_structure item="top_category"}
	{foreach from=$top_category.children_categories item="category"}
		{include file="side_menu/category" level='1'}
	{/foreach}
{/foreach}
