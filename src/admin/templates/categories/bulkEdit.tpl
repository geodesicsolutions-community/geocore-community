{* 7.3.1-87-g39301ce *}


<div class="closeBoxX"></div>
<div class="lightUpTitle" id="newConfirmTitle">Mass Edit</div>

<form style="display:block; margin: 15px; width: 450px;" action="index.php?page=category_edit_bulk&amp;parent={$parent}&amp;p={$page}" method="post">
	{foreach $values as $value_id}
		<input type="hidden" name="values[]" value="{$value_id}" />
	{/foreach}
	<p class="page_note">
		<strong>Warning:</strong> This will affect all selected categories,
		and there is <strong>no quick undo!</strong>
	</p>
	<div class="{cycle values='row_color1,row_color2'}">
		<div class="leftColumn">Categories</div>
		<div class="rightColumn">
			<strong class="text_blue">{$value_count}</strong> Categories Selected
		</div>
		<div class="clearColumn"></div>
	</div>
	<div class="{cycle values='row_color1,row_color2'}">
		<div class="leftColumn">Category Parent</div>
		<div class="rightColumn">
			{if $parents}
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
		<div class="rightColumn">{$level}</div>
		<div class="clearColumn"></div>
	</div>
	<div class="{cycle values='row_color1,row_color2'}">
		<div class="leftColumn">Enabled</div>
		<div class="rightColumn">
			<label><input type="radio" name="enabled" value="0" checked="checked" /> No Change</label><br />
			<label><input type="radio" name="enabled" value="yes" /> Enable All</label><br />
			<label><input type="radio" name="enabled" value="no" /> Disable All</label>
		</div>
		<div class="clearColumn"></div>
	</div>
	
	<div class="{cycle values='row_color1,row_color2'}">
		<div class="leftColumn">Display Order #</div>
		<div class="rightColumn">
			<label><input type="radio" name="display_order_change" value="0" checked="checked" /> No Change</label><br />
			<label><input type="radio" name="display_order_change" value="inc" /> Reset, Increment starting from: <input type="text" name="display_order_inc_start" value="1" size="2" /></label><br />
			<label><input type="radio" name="display_order_change" value="same" /> Set all to same number: <input type="text" name="display_order_same" value="1" size="2" /></label>
		</div>
		<div class="clearColumn"></div>
	</div>
	
	<div class="{cycle values='row_color1,row_color2'}">
		<div class="leftColumn">Listing Types Allowed</div>
		<div class="rightColumn">
			<label><input type="radio" name="listing_types_allowed_change" value="0" checked="checked" /> No Change</label><br />
			<label><input type="radio" name="listing_types_allowed_change" value="1" /> Change To:</label><br />
			<div style="margin-left: 20px;">
				{foreach $listing_types as $type => $type_info}
					&nbsp;<label><input name="listing_types_allowed[{$type}]" value="1" type="checkbox" checked="checked"> {$type_info.label}</label><br />
				{/foreach}
			</div>
		</div>
		<div class="clearColumn"></div>
	</div>
	
	<div class="{cycle values='row_color1,row_color2'}">
		<div class="leftColumn">Category Image</div>
		<div class="rightColumn">
			<label><input type="radio" name="category_image_clear" value="0" checked="checked" /> No Change</label><br />
			<label><input type="radio" name="category_image_clear" value="1" /> Remove Image (All Languages)</label>
		</div>
		<div class="clearColumn"></div>
	</div>
	
	<div class="{cycle values='row_color1,row_color2'}">
		<div class="leftColumn">Category Description</div>
		<div class="rightColumn">
			<label><input type="radio" name="category_description_clear" value="0" checked="checked" /> No Change</label><br />
			<label><input type="radio" name="category_description_clear" value="1" /> Remove Description (All Languages)</label>
		</div>
		<div class="clearColumn"></div>
	</div>
	
	<div class="{cycle values='row_color1,row_color2'}">
		<div class="leftColumn">Add extra to {ldelim}head_html} from</div>
		<div class="rightColumn">
			<label><input type="radio" name="which_head_html_change" value="0" checked="checked" /> No Change</label><br />
			<label><input type="radio" name="which_head_html_change" value="1" /> Change To:</label><br />
			<div style="margin-left: 20px;">
				<select name="which_head_html" class="which_head_html">
					<option value="parent">Parent Category</option>
					<option value="default">Default Site-Wide</option>
				</select>
			</div>
		</div>
		<div class="clearColumn"></div>
	</div>
	{if $has_subcategory}
		<br /><br />
		<div class="{cycle values='row_color1,row_color2'}">
			<div class="leftColumn"><input type="checkbox" name="apply_subcategories" value="1" /></div>
			<div class="rightColumn">
				Apply Changes To ALL Sub-Categories:<br />
				{foreach $levels_count as $level => $count}
					<strong class="text_blue">{$count}</strong> level {$level} sub-categor{if $count>1}ies{else}y{/if}{if !$count@last},<br />{/if}
				{/foreach}
			</div>
			<div class="clearColumn"></div>
		</div>
	{/if}
	
	<br /><br />
	<div style="float: right;">
		<input type="submit" name="auto_save" value="Apply Changes" class="mini_button" />
		<input type="button" class="closeLightUpBox mini_cancel" value="Cancel" />
	</div>
	<br />
</form>