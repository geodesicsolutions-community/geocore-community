{* 7.5.3-36-gea36ae7 *}


<div class="closeBoxX"></div>
<div class="lightUpTitle" id="newConfirmTitle">Delete Categor{if $value_count>1}ies{else}y{/if}</div>

<form style="display:block; margin: 15px; width: 450px;" action="index.php?page=category_delete&amp;parent={$parent}&amp;p={$page}" method="post">
	{foreach $values as $value_id}
		<input type="hidden" name="values[]" value="{$value_id}" />
	{/foreach}
	<p class="page_note_error">
		<strong>Warning:</strong> You are about to DELETE all of the selected categories,
		and there is <strong>no undo!</strong>  Are you sure you want to do this?
	</p>
	<div class="{cycle values='row_color1,row_color2'}">
		<div class="leftColumn">Categories Affected:</div>
		<div class="rightColumn">
			{foreach $levels_removed as $level => $count}
				<strong class="text_blue">{$count}</strong> level {$level} {if !$count@first}sub-{/if}categor{if $count>1}ies{else}y{/if} will be removed{if !$count@last},<br />{/if}
			{/foreach}
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
				<p class="page_note_error">listings in the categor{if $value_count>1}ies{else}y{/if} deleted will be moved to the first undeleted parent</p>
			{else}
				Top Level <p class="page_note_error">listings in the categor{if $value_count>1}ies{else}y{/if} deleted will be deleted also</p>
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