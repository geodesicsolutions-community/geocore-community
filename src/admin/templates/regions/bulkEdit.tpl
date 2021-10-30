{* 6.0.7-115-g9890342 *}


<div class="closeBoxX"></div>
<div class="lightUpTitle" id="newConfirmTitle">Mass Edit</div>

<form style="display:block; margin: 15px; width: 450px;" action="index.php?page=region_edit_bulk&amp;parent={$parent}&amp;p={$page}" method="post">
	{foreach $regions as $region_id}
		<input type="hidden" name="regions[]" value="{$region_id}" />
	{/foreach}
	<p class="page_note">
		<strong>Warning:</strong> This will affect all selected regions,
		and there is <strong>no quick undo!</strong>
	</p>
	<div class="{cycle values='row_color1,row_color2'}">
		<div class="leftColumn">Regions:</div>
		<div class="rightColumn">
			<strong class="text_blue">{$region_count}</strong> Regions Selected
		</div>
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
		<div class="rightColumn">{$level.level} ({$level.type_label})</div>
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
		<div class="leftColumn">Unique Name<br /><span class="small_font">(Optional, used for subdomain)</span></div>
		<div class="rightColumn">
			<label><input type="radio" name="unique_use" class="unique_use_bulk_edit" value="0" checked="checked" /> No Change</label><br />
			<label><input type="radio" name="unique_use" class="unique_use_bulk_edit" value="clear" /> Clear All</label><br />
			<label><input type="radio" name="unique_use" class="unique_use_bulk_edit showNameBox" value="abbreviation" /> Attempt to use Abbreviation</label><br />
			{foreach $languages as $lang}
				<label><input type="radio" name="unique_use" class="unique_use_bulk_edit showNameBox" value="{$lang.language_id}" /> Attempt to use {$lang.language} Name</label><br />
			{/foreach}
			<div id="unique_name_box" style="display: none;">
				<br />
				Add to front/end of unique name(optional):<br />
				<input type="text" name="unique_pre" size="5" />name<input type="text" name="unique_post" value="{if $parent_info.unique_name}-{$parent_info.unique_name}{/if}" size="5" />
			</div>
		</div>
		<div class="clearColumn"></div>
	</div>
	<div class="{cycle values='row_color1,row_color2'}">
		<div class="leftColumn">Billing % Tax</div>
		<div class="rightColumn">
			<label><input type="radio" name="tax_percent_change" value="0" checked="checked" /> No Change</label><br />
			<label><input type="radio" name="tax_percent_change" value="1" /> Change to <input type="text" name="tax_percent" value="{$region.tax_percent}" size="3" />%</label>
		</div>
		<div class="clearColumn"></div>
	</div>
	<div class="{cycle values='row_color1,row_color2'}">
		<div class="leftColumn">Billing Flat Tax</div>
		<div class="rightColumn">
			<label><input type="radio" name="tax_flat_change" value="0" checked="checked" /> No Change</label><br />
			<label><input type="radio" name="tax_flat_change" value="1" /> Change to {$precurrency}<input type="text" name="tax_flat" value="{$region.tax_flat}" size="3" />{$postcurrency}</label>
		</div>
		<div class="clearColumn"></div>
	</div>
	
	<br /><br />
	<div style="float: right;">
		<input type="submit" name="auto_save" value="{if $new}Add Region{else}Apply Changes{/if}" class="mini_button" />
		<input type="button" class="closeLightUpBox mini_cancel" value="Cancel" />
	</div>
</form>