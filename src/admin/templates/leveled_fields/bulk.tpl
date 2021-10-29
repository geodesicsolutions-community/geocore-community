{* 7.1beta4-5-g3b68d86 *}


<div class="closeBoxX"></div>
<div class="lightUpTitle" id="newConfirmTitle">Bulk Add Values</div>


<form id="bulk_add_form" style="display:block; margin: 15px; width: 450px;" action="index.php?page=leveled_field_value_create_bulk&amp;leveled_field={$leveled_field}&amp;parent={$parent}" method="post">
	<p class="page_note">
		This will add a lot of values at once, starting out using the same name
		for all languages.  You can also set the starting values for each of the fields below.
	</p>
	
	<div class="{cycle values='row_color1,row_color2'}">
		<div class="leftColumn"><input type="checkbox" name="enabled" value="1" checked="checked" /></div>
		<div class="rightColumn">All Enabled?</div>
		<div class="clearColumn"></div>
	</div>
	<div class="{cycle values='row_color1,row_color2'}">
		<div class="leftColumn">Multi-Level Field Group</div>
		<div class="rightColumn">{$leveled_field_label}</div>
		<div class="clearColumn"></div>
	</div>
	<div class="{cycle values='row_color1,row_color2'}">
		<div class="leftColumn">Value Parent</div>
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
		<div class="leftColumn">Value Level</div>
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
	
	<br />
	<div class="col_hdr">Values</div>
	<br />
	<div class="page_note">
		Add all the values in the box below, the name entered will be used for
		all languages to start.  You can try to add as many as you want but the more
		you add, the longer it will take.<br />
		Example: <em>Ford, GMC, Toyota</em>
		<br />
		<strong>Notes:</strong><br />
		<ul>
			<li>Values must be seperated by either <strong>TAB</strong>, <strong>COMMA</strong>, or <strong>Newline / Linebreak</strong></li>
			<li>Extra spaces around values are automatically removed.</li>
			<li>Double quotes before or after each value will be removed, to allow easier copy/paste from sources that surround values with quotes.</li>
		</ul>
	</div>
	<br />
	<textarea name="names" rows="30" cols="4" style="width: 100%; height: 100px;"></textarea>
	<br /><br />
	<div style="float: right;">
		<input type="hidden" name="auto_save" value="1" />
		<input type="submit" value="Bulk Add Values" class="mini_button" onclick="jQuery('#bulk_add_form').submit(); this.disable=true; this.value='Adding... This can take a while...'; return false;" />
		<input type="button" class="closeLightUpBox mini_cancel" value="Cancel" />
	</div>
</form>