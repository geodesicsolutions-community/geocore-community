{* 7.5.3-125-gf5f0a9a *}

{include file="cart_steps.tpl" g_resource="cart"}

<div class="content_box">
	<h1 class="title">{$title1}</h1>
	<h3 class="subtitle">{$title2} {$help_link}</h3>
	<p class="page_instructions">
		{$desc1}
		{if $parent_cat_id ne 0}
			<strong class="text_highlight">{$parent_cat_name}</strong> {$text1} {$num_cats}{$text2}
		{/if}
	</p>
	
	{if $error_msgs.cart_error}
		<div class="field_error_box">
			{$error_msgs.cart_error}
		</div>
	{/if}
	
	<p class="page_instructions">{$desc2}</p>
	
	{* capture each category into the correct column *}
	{foreach from=$cat_data key=i item=cat}
		{capture append="column_{$i % $colspan}"}		
				<li class="element category_{$cat.category_id}">
					<div class="main_cat_title">
					<a href="{$process_form_url}&amp;b={$cat.category_id}">
						{if $display_cat_image ne 0 AND $cat.category_image ne ""}
							<img src="{external file=$cat.category_image|fromDB}" alt="" /> &nbsp;
						{/if}
						<span class="category_title">{$cat.category_name|fromDB}</span>
						{if $display_cat_description && $cat.description}
							<p class="category_description">{$cat.description|fromDB}</p>
						{/if}
					</a>
					</div>
				</li>
		{/capture}
	{/foreach}
	
	{* print all the columns in order, and add in the captured data from above *}
	<div class="clearfix">
		{section name=colLoop loop=$colspan}
			<div class="category_column columns-{$colspan}">
				<ul class="categories">
					{foreach $column_{$smarty.section.colLoop.index} as $columnData}
						{$columnData}
					{/foreach}
				</ul>
			</div>
		{/section}
	</div>
	
	{if $listings_only_in_terminal ne 1}
		<div class="center">
			{$text3}
			<a href="{$process_form_url}&amp;b={$parent_cat_id}&amp;c=terminal" class="button">
				{$parent_cat_name}
			</a>
		</div>
	{/if}
</div>

<br />
{if !$steps_combined}
	<div class="center">
		<a href="{$cart_url}&amp;action=cancel" class="cancel">{$cancel_txt}</a>
	</div>
{/if}
