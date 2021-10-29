{* 6.0.7-3-gce41f93 *}

<div class="closeBoxX"></div>
<div class="lightUpTitle">
	Create Main Template Set
</div>
<div class="templateToolContents" style="width: 450px;">
	<form action='index.php?page=design_sets_create_main' method='post'>
		<div class="page_note">
			This will create a main template set for you to edit, based off of 
			the default templates and files.
		</div>
		<div class="{cycle values="row_color1,row_color2"}">
			<div class="leftColumn">
				<label for="new_t_set">Template Set Name</label>
			</div>
			<div class="rightColumn">
				<input name="new_t_set" id="new_t_set" type="text" value="my_templates" />
			</div>
			<div class="clearColumn"></div>
		</div>
		<br />
		<div style="text-align: right;">
			<input type='submit' name='auto_save' class="mini_button" value='Create Main Template Set' />
			<input type="button" value="Cancel" class="closeLightUpBox mini_cancel" />
		</div>
	</form>
</div>