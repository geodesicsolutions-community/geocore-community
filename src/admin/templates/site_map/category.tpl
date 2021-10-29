{* 16.09.0-79-gb63e5d8 *}
{if $category.title}
	<li>
		<div>
			{if $category.image_fa}<i class="fa {$category.image}"></i>{else}<img src="{$category.image}" />{/if}
			&nbsp;{$category.title}
		</div>
	{if count($category.children_pages) > 0}
		<ul>
			{foreach from=$category.children_pages item="_page"}
				{include file="site_map/page" page=$_page mc=$category.index}
			{/foreach}
		</ul>
	{/if}
	{if count($category.children_categories) > 0}
		<ul>
			{foreach from=$category.children_categories item="sub_category"}
				{include file="site_map/category" category=$sub_category}
			{/foreach}
		</ul>
	{/if}
		
	</li>
{/if}