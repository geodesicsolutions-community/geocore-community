{* 17.07.0-11-g678b6a0 *}

{if $text.back_to_normal_link}
	<a href="{$classifieds_file_name}?a=5&amp;b={$category}" class="button">{$text.back_to_normal_link}</a>
	<br /><br />
{/if}

{if $streamlined}
	{* This is the new way to do category browsing. Instead of showing a big block of text on the page, add a button to the breadcrumb that opens a list of categories in a lightbox *}
	{include file='common/category_breadcrumb.tpl'}
	
	<div class="category_block" style="display: none;">
		<div class="cat-block-popout">
			{*<div class="closeBoxX"></div>*}
			{foreach from=$categories item=cats key=column}
				<div class="category_column">
					<ul class="categories">
						{foreach from=$cats item=cat}
							<a href="{$link}{$cat.category_id}">
								<li class="element category_{$cat.category_id} {cycle values="row_even,row_odd"}">
									<div class="main_cat_title">
										{if $cat.category_image}<img src="{external file=$cat.category_image}" alt="{$cat.category_image_alt}" />{/if}
										<span class="category_title">{$cat.category_name}</span> 
										{if $cat.category_count}<span class="listing_counts">{$cat.category_count}</span>{/if} 
										{if $cat.new_ad_icon}{$cat.new_ad_icon}{/if}
									</div>
								</li>
							</a>
						{/foreach}
					</ul>
				</div>
			{/foreach}
		</div>
	</div>
{else}
	
	{*
		$tree_display_mode:
			0: show tree below subcategories
			1: show tree above subcategories
			2: show tree below AND above subcategories
			3: do not show tree
	*}
	
	{if $tree_display_mode == 1 or $tree_display_mode == 2}
		{include file='common/category_breadcrumb.tpl'}
	{/if}
	
	{if $show_no_subcats}
		<div class="center sub_note">
			{$text.no_subcats} {$current_category_name}
		</div>
	{/if}
	
	{if $show_subcats}
		<div class="clearfix category_block">
			{foreach from=$categories item=cats key=column}
				<div class="category_column columns-{$column_count}">
					<ul class="categories">
						{foreach from=$cats item=cat}
							<li class="element category_{$cat.category_id}">
								<div class="main_cat_title">
									{if $cat.category_image}<a href="{$link}{$cat.category_id}"><img src="{external file=$cat.category_image}" alt="{$cat.category_image_alt}" /></a>{/if}
									<a href="{$link}{$cat.category_id}">{strip}
										<span class="category_title">
											{$cat.category_name}
										</span>
									{/strip}</a>
									{if $cat.category_count}<span class="listing_counts">{$cat.category_count}</span>{/if}
									{if $cat.new_ad_icon}{$cat.new_ad_icon}{/if}
								</div>
								{if $show_descriptions}
									<p class="category_description">{$cat.category_description}</p>
								{/if}
								{if $cat.sub_categories}
									<ul class="sub_categories">
										{foreach from=$cat.sub_categories item=sub_cat}
											<li class="element subcategory_{$sub_cat.category_id}">
												<a href="{$link}{$sub_cat.category_id}">
													<span class="category_title">
														{$sub_cat.category_name|fromDB}
													</span>
												</a>
											</li>
										{/foreach}
									</ul>
								{/if}
							</li>
						{/foreach}
					</ul>
				</div>
			{/foreach}
		</div>
	{/if}
	
	{if $tree_display_mode == 0 or $tree_display_mode == 2}
		{include file='common/category_breadcrumb.tpl'}
	{/if}


{/if}