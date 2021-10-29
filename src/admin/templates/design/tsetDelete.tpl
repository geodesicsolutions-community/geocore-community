{* 6.0.7-3-gce41f93 *}

<div class="closeBoxX"></div>
<div class="lightUpTitle">
	DELETE Template Set
</div>
<div class="templateToolContents" style="width: 550px;">
	<form action='index.php?page=design_sets_delete' method='post'>
		<div class="page_note_error">
			This will DELETE all files and folders in the template set!
			<br /><br />
			This action <strong>cannot be un-done</strong>.
		</div>
		
		<div class="{cycle values="row_color1,row_color2"}">
			<div class="leftColumn">
				Delete Template Set
			</div>
			<div class="rightColumn">
				<span class="text_blue">{$t_set}</span>
			</div>
			<div class="clearColumn"></div>
		</div>
		
		<div class="{cycle values="row_color1,row_color2"}">
			<div class="leftColumn">
				Confirm Deletion
			</div>
			<div class="rightColumn">
				<label><input type="radio" name="verify" value="0" checked="checked" /> NO, do NOT delete template set</label><br />
				<label><input type="radio" name="verify" value="1" /> Yes, delete template set</label>
				<input type="hidden" name="t_set" value="{$t_set}" />
			</div>
			<div class="clearColumn"></div>
		</div>
		
		<br />
		<div style="text-align: right;">
			<input type='submit' class="mini_button" name='auto_save' value='DELETE Now' />
			<input type="button" value="Cancel" class="closeLightUpBox mini_cancel" />
		</div>
	</form>
</div>