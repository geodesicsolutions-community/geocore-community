{* 7.6.3-149-g881827a *}

<div class="content_box clearfix">
	{if $msgs.featured_title}
	<h1 class="title"><span class="category-intro">{$msgs.featured_title}</span> {$current_category_name}</h1>
	{/if}
	<div class="featured_browsing{if $featured_carousel} gj_simple_carousel{/if}">
		{include file=$browse_tpl}
	</div>
</div>
