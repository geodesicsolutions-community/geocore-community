{* 16.09.0-79-gb63e5d8 *}
{if $category.title}
	{* Special case: Don't show categories without a title, specifically made
		for "top level" pages such as home or release notes *}
	<li{if $category.current} class="active"{/if}>
		<a>{if $level==1 && $category.image}{if $category.image_fa}<i class="fa {$category.image}"></i>{else}<img src="{$category.image}" />{/if} {/if}{$category.title}<span class="fa fa-chevron-down"></span></a>
		<ul class="nav child_menu"{if $category.current} style="display: block;"{/if}>


		{if $category.children_pages && count($category.children_pages) > 0}
			{foreach from=$category.children_pages item="_page"}
				{include file="side_menu/page" page=$_page mc=$category.index}
			{/foreach}
		{/if}
		{if $category.children_categories && count($category.children_categories) > 0}
			{foreach from=$category.children_categories item="sub_category"}
				{include file="side_menu/category" category=$sub_category level={$level+1}}
			{/foreach}
		{/if}

		</ul>

	</li>
{/if}
