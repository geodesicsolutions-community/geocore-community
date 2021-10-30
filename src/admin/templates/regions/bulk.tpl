{* 6.0.7-82-g583709d *}


<div class="closeBoxX"></div>
<div class="lightUpTitle" id="newConfirmTitle">Bulk Add Regions</div>


<form id="bulk_add_form" style="display:block; margin: 15px; width: 450px;" action="index.php?page=region_create_bulk&amp;parent={$parent}" method="post">
	<p class="page_note">
		This will add a lot of regions at once, starting out using the same name
		for all languages.  You can also set the starting values for each of the fields below.
	</p>
	
	<div class="{cycle values='row_color1,row_color2'}">
		<div class="leftColumn"><input type="checkbox" name="enabled" value="1" checked="checked" /></div>
		<div class="rightColumn">All Enabled?</div>
		<div class="clearColumn"></div>
	</div>
	<div class="{cycle values='row_color1,row_color2'}">
		<div class="leftColumn">Region Parent</div>
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
		<div class="leftColumn">Region Level</div>
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
		<div class="leftColumn">Unique Name<br /><span class="small_font">(Optional, used for subdomain)</span></div>
		<div class="rightColumn">
			<label><input type="checkbox" name="unique_use" value="1" onclick="$('unique_name_box')[(this.checked)? 'show':'hide']();" /> Attempt to use name</label>
			<div id="unique_name_box" style="display: none;">
				<br />
				Add to front/end of unique name(optional):<br />
				<input type="text" name="unique_pre" size="5" />name<input type="text" name="unique_post" value="{if $parent_info.unique_name}.{$parent_info.unique_name}{/if}" size="5" />
			</div>
		</div>
		<div class="clearColumn"></div>
	</div>
	<div class="{cycle values='row_color1,row_color2'}">
		<div class="leftColumn">Billing % Tax</div>
		<div class="rightColumn"><input type="text" name="tax_percent" value="0" size="3" />%</div>
		<div class="clearColumn"></div>
	</div>
	<div class="{cycle values='row_color1,row_color2'}">
		<div class="leftColumn">Billing Flat Tax</div>
		<div class="rightColumn">{$precurrency}<input type="text" name="tax_flat" value="0.00" size="3" />{$postcurrency}</div>
		<div class="clearColumn"></div>
	</div>
	
	<br />
	<div class="col_hdr">Regions</div>
	<br />
	<div class="page_note">
		Add all the regions in the box below, the name entered will be used for
		all languages to start.  You can try to add as many as you want but the more
		you add, the longer it will take.<br />
		Example: <em>Alabama, Alaska, Arizona</em>
		<br />
		<strong>Notes:</strong><br />
		<ul>
			<li>Regions must be seperated by either <strong>TAB</strong>, <strong>COMMA</strong>, or <strong>Newline / Linebreak</strong></li>
			<li>Extra spaces around regions are automatically removed.</li>
			<li>Double quotes before or after each region will be removed, to allow easier copy/paste from sources that surround regions with quotes.</li>
		</ul>
	</div>
	<br />
	<textarea name="names" rows="30" cols="4" style="width: 100%; height: 100px;"></textarea>
	<br /><br />
	<div style="float: right;">
		<input type="hidden" name="auto_save" value="1" />
		<input type="submit" value="Bulk Add Regions" class="mini_button" onclick="$('bulk_add_form').submit(); this.disable=true; this.value='Adding... This can take a while...'; return false;" />
		<input type="button" class="closeLightUpBox mini_cancel" value="Cancel" />
	</div>
</form>