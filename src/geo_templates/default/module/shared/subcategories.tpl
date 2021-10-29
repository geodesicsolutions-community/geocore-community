{* 6.0.7-3-gce41f93 *}

<ul class="sub_categories">
	{foreach from=$sub_categories item=sub_cat}
		<li class="element subcategory_{$sub_cat.category_id}">
			<a href="{$link}{$sub_cat.category_id}">
				{$sub_cat.category_name|fromDB}
			</a>
		</li>
	{/foreach}
</ul>