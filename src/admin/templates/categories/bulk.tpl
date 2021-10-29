{* 7.3.1-83-gc642c41 *}


<div class="closeBoxX"></div>
<div class="lightUpTitle" id="newConfirmTitle">Bulk Add Categories</div>


<form id="bulk_add_form" style="display:block; margin: 15px; width: 600px;" action="index.php?page=category_create_bulk&amp;parent={$parent}" method="post">
	<p class="page_note">
		This will add a lot of categories at once, starting out using the same name
		for all languages.  You can also set the starting values for each of the fields below.
	</p>
	
	<div class="{cycle values='row_color1,row_color2'}">
		<div class="leftColumn"><input type="checkbox" name="enabled" value="1" checked="checked" /></div>
		<div class="rightColumn">All Enabled?</div>
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
		<div class="leftColumn">Display Order #</div>
		<div class="rightColumn">
			Start at: <input type="text" name="display_order" value="{$display_order}" size="2" /><br />
			<label><input type="radio" name="display_order_type" value="inc" checked="checked" /> Increment Automatically</label><br />
			<label><input type="radio" name="display_order_type" value="same" /> All use same number</label>
		</div>
		<div class="clearColumn"></div>
	</div>
	
	<div class="{cycle values='row_color1,row_color2'}">
		<div class="leftColumn">Starting Listing Types Allowed</div>
		<div class="rightColumn">
			{foreach $listing_types as $type => $type_info}
				<label><input name="listing_types_allowed[{$type}]" value="1" type="checkbox"{if !$category.excluded_list_types.$type} checked="checked"{/if}> {$type_info.label}</label><br />
			{/foreach}
		</div>
		<div class="clearColumn"></div>
	</div>
	
	<br />
	<div class="col_hdr">Category Names</div>
	<br />
	<div class="page_note">
		Add all the categories in the box below, the name entered will be used for
		all languages to start.  You can try to add as many as you want but the more
		you add, the longer it will take.<br />
		Example: <em>Autos, Personals, Misc.</em>
		<br />
		<strong>Notes:</strong><br />
		<ul>
			<li>Categories must be seperated by either <strong>TAB</strong>, <strong>COMMA</strong>, or <strong>Newline / Linebreak</strong></li>
			<li>Extra spaces around categories are automatically removed.</li>
			<li>Double quotes before or after each category will be removed, to allow easier copy/paste from sources that surround values with quotes.</li>
		</ul>
	</div>
	<br />
	<textarea name="names" rows="30" cols="4" style="width: 100%; height: 100px;"></textarea>
	<br /><br />
	<div style="float: right;">
		<input type="hidden" name="auto_save" value="1" />
		<input type="submit" value="Bulk Add Categories" class="mini_button" onclick="jQuery('#bulk_add_form').submit(); this.disable=true; this.value='Adding... This can take a while...'; return false;" />
		<input type="button" class="closeLightUpBox mini_cancel" value="Cancel" />
	</div>
</form>