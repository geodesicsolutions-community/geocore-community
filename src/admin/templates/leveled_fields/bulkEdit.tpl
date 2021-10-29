{* 7.1beta4-5-g3b68d86 *}


<div class="closeBoxX"></div>
<div class="lightUpTitle" id="newConfirmTitle">Mass Edit</div>

<form style="display:block; margin: 15px; width: 450px;" action="index.php?page=leveled_field_value_edit_bulk&amp;leveled_field={$leveled_field}&amp;parent={$parent}&amp;p={$page}" method="post">
	{foreach $values as $value_id}
		<input type="hidden" name="values[]" value="{$value_id}" />
	{/foreach}
	<p class="page_note">
		<strong>Warning:</strong> This will affect all selected values,
		and there is <strong>no quick undo!</strong>
	</p>
	<div class="{cycle values='row_color1,row_color2'}">
		<div class="leftColumn">Values</div>
		<div class="rightColumn">
			<strong class="text_blue">{$value_count}</strong> Values Selected
		</div>
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
		<div class="rightColumn">{$level.level} {if $level.label}({$level.label}){/if}</div>
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
	
	<br /><br />
	<div style="float: right;">
		<input type="submit" name="auto_save" value="Apply Changes" class="mini_button" />
		<input type="button" class="closeLightUpBox mini_cancel" value="Cancel" />
	</div>
	<br />
</form>