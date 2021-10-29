{* 7.1beta4-5-g3b68d86 *}


<div class="closeBoxX"></div>
<div class="lightUpTitle" id="newConfirmTitle">Delete Value(s)</div>

<form style="display:block; margin: 15px; width: 450px;" action="index.php?page=leveled_field_value_delete&amp;leveled_field={$leveled_field}&amp;parent={$parent}&amp;p={$page}" method="post">
	{foreach $values as $value_id}
		<input type="hidden" name="values[]" value="{$value_id}" />
	{/foreach}
	<p class="page_note_error">
		<strong>Warning:</strong> You are about to DELETE all of the selected values,
		and there is <strong>no undo!</strong>  Are you sure you want to do this?
	</p>
	<div class="{cycle values='row_color1,row_color2'}">
		<div class="leftColumn">Values Affected:</div>
		<div class="rightColumn">
			{foreach $levels_removed as $level => $count}
				<strong class="text_blue">{$count}</strong> level {$level} {if !$count@first}sub-{/if}value{if $count!=1}s{/if} will be removed{if !$count@last},<br />{/if}
			{/foreach}
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
		<div class="leftColumn">Really Delete?</div>
		<div class="rightColumn">
			<label><input type="radio" name="really" value="no" checked="checked" /> No, do not delete, I am not paying attention to what I click. :-)</label><br />
			<label><input type="radio" name="really" value="yes" /> Yes, delete now</label>
		</div>
		<div class="clearColumn"></div>
	</div>
	
	<br /><br />
	<div style="float: right;">
		<input type="submit" name="auto_save" value="Delete Now" class="mini_cancel" />
		<input type="button" class="closeLightUpBox mini_cancel" value="Cancel" />
	</div>
	<br />
</form>