{* 7.6.3-149-g881827a *}
{if $string_tree or $array_tree or $category_tree_pre or $category_tree_post}
	<nav class="breadcrumb">
		{if $category_tree_pre}
			{* Allow outside sources add to category tree *}
			{$category_tree_pre}
		{/if}
		{if $array_tree}
			<div class="highlight">{$text.tree_label}</div>
			<a href="{$link_top}">{$text.main_category}</a>
			{foreach from=$array_tree item=cat name=tree}
				{if $smarty.foreach.tree.last}<div class="active">{else}<a href="{$link}{$cat.category_id}">{/if}
					{$cat.category_name}
				{if not $smarty.foreach.tree.last}</a>{else}</div>{/if}
			{/foreach}
			{if $streamlined && !$in_terminal_category}
				<div class="subcategory-nav-open" style="cursor: pointer;"><span class="glyphicon glyphicon-plus"></span></div>
			{/if}
		{elseif $string_tree}
			<div>{$string_tree}</div>
			{* is that anything like string cheese? "string_treese," perhaps? *}
		{/if}
		{if $category_tree_post}
			{* Allow outside sources add to category tree *}
			{$category_tree_post}
		{/if}
	</nav>
{/if}