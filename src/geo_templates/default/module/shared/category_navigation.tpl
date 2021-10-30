{* 7.5.3-39-g2a9b929 *}
{if $error_message||$error}
	<span class="error_message">{$error_message}{$error}</span>
{/if}

{foreach from=$categories item=cats key=column name=catlist}
	<div class="category_column{if $column_css} {$column_css}{/if} columns-{$col_count}">
		<ul>
			{foreach from=$cats item=cat}
				<li class="element  category_{$cat.category_id}">
					<a href="{$link}{$cat.category_id}">
						{if $module.display_category_image && $cat.category_image}<img src="{external file=$cat.category_image}" alt="{$cat.category_image_alt}" />{/if}
						
						{$cat.category_name}
						{if $cat.category_counts}{$cat.category_counts}{/if}
						{if $cat.new_ad_icon}{$cat.new_ad_icon}{/if}
					</a>
					{if $module.display_category_description}
						<p>{$cat.category_description}</p>
					{/if}
					{if $cat.sub_categories}
						{include file='subcategories.tpl' g_resource='shared' sub_categories=$cat.sub_categories}
					{/if}
				</li>
			{/foreach}
			{if $parent_category_text && $smarty.foreach.catlist.last}
				<li class="element_highlight">
					<a href="{$parent_category_url}">{$parent_category_text}</a>
				</li>
			{/if}
		</ul>
	</div>
{foreachelse}
	{if $module.display_no_subcategory_message}
		<div class="category_column columns-{$col_count}">
			<ul>
				<li class="element">
					<p>
						{$no_subcategory_text}
					</p>
				</li>
				{if $parent_category_text}
					<li class="element_highlight">
						<a href="{$parent_category_url}">{$parent_category_text}</a>
					</li>
				{/if}
			</ul>
		</div>
	{/if}
{/foreach}
<div class="clear"></div>