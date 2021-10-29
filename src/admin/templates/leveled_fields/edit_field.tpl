{* 16.09.0-87-g69e04de *}


<div class="closeBoxX"></div>
<div class="lightUpTitle" id="newConfirmTitle">Edit Multi-Level Field Group Label</div>


<form style="display:block; margin: 15px; width: 450px;" action="index.php?page=leveled_field_edit&amp;leveled_field={$leveled_field}" method="post" class='form-horizontal'>
	<p class="page_note">Note that the field group label is only viewed in the admin.</p>
	<div class="form-group">
		<label class='control-label col-xs-12 col-sm-5'>Field Group Label</label>
		<div class="col-xs-12 col-sm-6">
			<input type="text" name="label" value="{$leveled_field_label|escape}" class='form-control' />
		</div>
		<div class="clearColumn"></div>
	</div>
	
	<div class='center'>
		<input type="submit" name="auto_save" value="Apply Changes" class="mini_button" />
		<input type="button" class="closeLightUpBox mini_cancel" value="Cancel" />
	</div>
</form>