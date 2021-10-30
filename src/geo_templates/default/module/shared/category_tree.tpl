{* 7.3beta4-112-g53e6ee9 *}
{* Use same template for all 3 different category tree modules *}

<nav class="breadcrumb">
	{$category_tree_pre}
	{if !$fallback_tree_display && $categories}
		<div class="highlight">{$link_label}</div>
		<a href="{$base_url}">{$link_text}</a>
		{foreach from=$categories item=c}
			{if $c.id}<a href="{$base_url}&amp;b={$c.id}">{else}<div class="active">{/if}
				{$c.label}
			{if !$c.id}</div>{else}</a>{/if}
		{/foreach}
	{elseif $fallback_tree_display}
		<div>{$fallback_tree_display}</div>
	{/if}
</nav>