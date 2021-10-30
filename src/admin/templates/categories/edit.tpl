{* 7.5.3-126-gef4a138 *}

<div class="closeBoxX"></div>
<div class="lightUpTitle" id="newConfirmTitle">{if $new}Add{else}Edit{/if} Category{if $category.name} {$category.name}{/if}</div>


<form style="display:block; margin: 15px; width: 600px;" action="index.php?page=category{if $new}_create{else}_edit{/if}&amp;parent={$parent}{if !$new}&amp;category={$category.id}{/if}&amp;p={$page|escape}" method="post">
	<div class="{cycle values='row_color1,row_color2'}">
		<div class="leftColumn"><input type="checkbox" name="enabled" value="1"{if $new||$category.enabled=='yes'} checked="checked"{/if} /></div>
		<div class="rightColumn">Enabled?</div>
		<div class="clearColumn"></div>
	</div>
	{if !$new}
		<div class="{cycle values='row_color1,row_color2'}">
			<div class="leftColumn">Category ID#</div>
			<div class="rightColumn">{$category.id}</div>
			<div class="clearColumn"></div>
		</div>
	{/if}
	<div class="{cycle values='row_color1,row_color2'}">
		<div class="leftColumn">Parent Category</div>
		<div class="rightColumn">
			{if $category.parent}
				{foreach $parents as $parent}
					{$parent.name}
					{if !$parent@last} &gt;{/if}
				{/foreach}
			{else}
				Top Level
			{/if}
		</div>
		<div class="clearColumn"></div>
	</div>
	<div class="{cycle values='row_color1,row_color2'}">
		<div class="leftColumn">Category Level</div>
		<div class="rightColumn">{$category.level}</div>
		<div class="clearColumn"></div>
	</div>

	<div class="{cycle values='row_color1,row_color2'}">
		<div class="leftColumn">Display Order #</div>
		<div class="rightColumn"><input type="text" name="display_order" value="{$category.display_order}" size="4" /></div>
		<div class="clearColumn"></div>
	</div>
	
	<div class="{cycle values='row_color1,row_color2'}">
		<div class="leftColumn">Listing Types Allowed</div>
		<div class="rightColumn">
			{foreach $listing_types as $type => $type_info}
				<label><input name="listing_types_allowed[{$type}]" value="1" type="checkbox"{if !$category.excluded_list_types.$type} checked="checked"{/if}> {$type_info.label}</label><br />
			{/foreach}
		</div>
		<div class="clearColumn"></div>
	</div>
	
	<div class="{cycle values='row_color1,row_color2'}">
		<div class="leftColumn">Add extra to {ldelim}head_html} from</div>
		<div class="rightColumn">
			<select name="which_head_html" class="which_head_html">
				<option value="parent"{if $category.which_head_html=='parent'} selected="selected"{/if}>Parent Category</option>
				<option value="default"{if $category.which_head_html=='default'} selected="selected"{/if}>Default Site-Wide</option>
				<option value="cat"{if $category.which_head_html=='cat'} selected="selected"{/if}>Category-Specific (Set Below)</option>
				<option value="cat+default"{if $category.which_head_html=='cat+default'} selected="selected"{/if}>Default AND Category-Specific (Set Below)</option>
			</select>
		</div>
		<div class="clearColumn"></div>
	</div>
	{if $count_of_parents <= 1}
	<div class="{cycle values='row_color1,row_color2'}">
		<div class="leftColumn"><input type="checkbox" name="front_page_display" value="1"{if $front_page_display=='yes'} checked="checked"{/if} /></div>
		<div class="rightColumn">Display within default front page category navigation?</div>
		<div class="clearColumn"></div>
	</div>
	{/if}
	
	<br />
	<div class="col_hdr">Category Name</div>
	<br />
	{foreach $languages as $lang}
		<div class="{cycle values='row_color1,row_color2'}">
			<div class="leftColumn">{$lang.language}</div>
			<div class="rightColumn"><input type="text" name="name[{$lang.language_id}]" size="20" value="{if $names[$lang.language_id]}{$names[$lang.language_id]}{else}{$category.name}{/if}" /></div>
			<div class="clearColumn"></div>
		</div>
	{/foreach}
	
	<br />
	<div class="col_hdr">
		Category HTML Title Value<br />
		<em>How this category name appears in page titles -- defaults to category name if left blank</em>
	</div>
	<br />
	{foreach $languages as $lang}
		<div class="{cycle values='row_color1,row_color2'}">
			<div class="leftColumn">{$lang.language}</div>
			<div class="rightColumn"><input type="text" name="title_module[{$lang.language_id}]" size="20" value="{$title_module[$lang.language_id]}" /></div>
			<div class="clearColumn"></div>
		</div>
	{/foreach}	
	
	<br />
	<div class="col_hdr">
		SEO URL Contents{if !$seo_enabled} - <a href="index.php?page=addon_tools&mc=addon_management">Enable SEO Addon</a> to use{/if}<br />
		<em>used in URLs rewritten by the SEO addon -- defaults to category name if left blank</em>
	</div>
	<br />
	{foreach $languages as $lang}
		<div class="{cycle values='row_color1,row_color2'}">
			<div class="leftColumn">{$lang.language}</div>
			<div class="rightColumn"><input type="text" name="seo_url_contents[{$lang.language_id}]" size="20" value="{$seo_url_contents[$lang.language_id]}" {if !$seo_enabled}disabled="disabled"{/if}/></div>
			<div class="clearColumn"></div>
		</div>
	{/foreach}		
	
	<br />
	<div class="col_hdr">Category Image</div>
	<br />
	{foreach $languages as $lang}
		<div class="{cycle values='row_color1,row_color2'}">
			<div class="leftColumn">{$lang.language}</div>
			<div class="rightColumn"><em>{$tpl_folder}[Template Set]/external/</em><input type="text" name="category_image[{$lang.language_id}]" size="20" value="{$category_images[$lang.language_id]|escape}" /></div>
			<div class="clearColumn"></div>
		</div>
	{/foreach}
	
	<br />
	<div class="col_hdr">Category Image Alt Tag Value</div>
	<br />
	{foreach $languages as $lang}
		<div class="{cycle values='row_color1,row_color2'}">
			<div class="leftColumn">{$lang.language}</div>
			<div class="rightColumn"><input type="text" name="category_image_alt[{$lang.language_id}]" size="20" value="{$category_image_alt[$lang.language_id]|escape}" /></div>
			<div class="clearColumn"></div>
		</div>
	{/foreach}	
	
	<br />
	<div class="col_hdr">Category Description</div>
	<br />
	{foreach $languages as $lang}
		<div class="{cycle values='row_color1,row_color2'}">
			<div class="leftColumn">{$lang.language}</div>
			<div class="rightColumn"><textarea name="description[{$lang.language_id}]">{$descriptions[$lang.language_id]|escape}</textarea></div>
			<div class="clearColumn"></div>
		</div>
	{/foreach}
	
	<div class="head_html">
		<br />
		<div class="col_hdr">{ldelim}head_html} added contents</div>
		<br />
		{foreach $languages as $lang}
			<div class="{cycle values='row_color1,row_color2'}">
				<div class="leftColumn">{$lang.language}</div>
				<div class="rightColumn"><textarea name="head_html[{$lang.language_id}]">{$head_html[$lang.language_id]|escape}</textarea></div>
				<div class="clearColumn"></div>
			</div>
		{/foreach}
	</div>
	<br /><br />
	<div style="float: right;">
		<input type="submit" name="auto_save" value="{if $new}Add Category{else}Apply Changes{/if}" class="mini_button" />
		<input type="button" class="closeLightUpBox mini_cancel" value="Cancel" />
	</div>
	<br />
</form>
