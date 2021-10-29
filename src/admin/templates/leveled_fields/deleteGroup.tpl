{* 7.1beta2-156-gf00f365 *}


<div class="closeBoxX"></div>
<div class="lightUpTitle" id="newConfirmTitle">Delete Leveled Field Group</div>

<form style="display:block; margin: 15px; width: 450px;" action="index.php?page=leveled_fields_delete&amp;leveled_field={$leveled_field}" method="post">
	<p class="page_note_error">
		<strong>Warning:</strong> You are about to DELETE and entire group of
		leveled fields and all of the values,
		and there is <strong>no undo!</strong>  Are you sure you want to do this?
	</p>
	<div class="{cycle values='row_color1,row_color2'}">
		<div class="leftColumn">Values in Group:</div>
		<div class="rightColumn">
			{$value_count}
		</div>
		<div class="clearColumn"></div>
	</div>
	<div class="{cycle values='row_color1,row_color2'}">
		<div class="leftColumn">Leveled Field Group</div>
		<div class="rightColumn">{$leveled_field_label}</div>
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
</form>