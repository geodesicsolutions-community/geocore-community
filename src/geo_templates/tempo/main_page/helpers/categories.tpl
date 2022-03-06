{*

Custom template / smarty tag (cz_categories) to display category navigation matching
the design.  This goes with smarty/function.cz_categories.php smarty plugin in
this template set.

*}
<div class="list-group cz-categories">
	{foreach $categories as $category}
		<a href="{$link}{$category.category_id}" class="list-group-item{if $category.current} active{/if}">{$category.category_name} <span class="glyphicon glyphicon-chevron-right"></span></a>
		{if $category.sub_categories}
			<div class="list-subgroups">
				{foreach $category.sub_categories as $sub_cat}
					<a href="{$link}{$sub_cat.category_id}" class="list-subgroup-item{if $sub_cat.current} active{/if}">{$sub_cat.category_name}</a>
					{if $sub_cat.sub_categories}
						{foreach $sub_cat.sub_categories as $sub_sub_cat}
							<a href="{$link}{$sub_sub_cat.category_id}" class="list-sub-subgroup-item{if $sub_sub_cat.selected} active{/if}">{$sub_sub_cat.category_name}</a>
						{/foreach}
					{/if}
				{/foreach}
			</div>
		{/if}
	{/foreach}
</div>