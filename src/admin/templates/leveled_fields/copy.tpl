{* 7.1beta4-5-g3b68d86 *}


<div class="closeBoxX"></div>
<div class="lightUpTitle" id="newConfirmTitle">Copy Value(s)</div>

<form style="display:block; margin: 15px; width: 450px;" action="index.php?page=leveled_field_value_copy&amp;leveled_field={$leveled_field}&amp;parent={$parent}&amp;p={$page}" method="post">
	{foreach $values as $value_id}
		<input type="hidden" name="values[]" value="{$value_id}" />
	{/foreach}
	<p class="page_note">
		<strong>Warning:</strong> This can take some time if you are copying a lot of values,
		or are copying values that have a lot of sub-values.  Select where to copy the values to below:
	</p>
	<div class="{cycle values='row_color1,row_color2'}">
		<div class="leftColumn">FROM Multi-Level Field Group</div>
		<div class="rightColumn">{$leveled_field_label}</div>
		<div class="clearColumn"></div>
	</div>
	<div class="{cycle values='row_color1,row_color2'}">
		<div class="leftColumn">FROM Parent Value</div>
		<div class="rightColumn">
			{if $parents}
				Top Level &gt;
				{foreach $parents as $p}
					{$p.name}
					{if !$p@last} &gt;{/if}
				{/foreach}
			{else}
				Top Level
			{/if}
		</div>
		<div class="clearColumn"></div>
	</div>
	<div class="{cycle values='row_color1,row_color2'}">
		<div class="leftColumn">Copy TO Subvalues Of</div>
		<div class="rightColumn">
			{if $parent}<label><input type="radio" name="to_type" value="top" checked="checked" class="move_to_type" /> Top Level of <em>{$leveled_field_label}</em></label><br />{/if}
			<label><input type="radio" name="to_type" value="id" class="move_to_type" {if !$parent} checked="checked"{/if}/> Enter Value ID#</label><br />
			<div id="moveToIdBox" style="margin-left: 20px;{if $parent} display: none;{/if}">				
				<input type="text" name="new_parent" size="3" title="ID#" />
			</div>
			
			<label><input type="radio" name="to_type" value="browse" class="move_to_type" /> Select Value</label>
		</div>
		<div class="clearColumn"></div>
	</div>
	
	<div id="moveToBrowseBox" style="display: none;">
		{include file='leveled_fields/copyBrowse.tpl'}
	</div>
	<br /><br />
	<div style="float: right;">
		<input type="submit" name="auto_save" value="Copy" class="mini_button" />
		<input type="button" class="closeLightUpBox mini_cancel" value="Cancel" />
	</div>
	<br />
</form>