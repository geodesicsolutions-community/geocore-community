{* 7.3beta2-67-g64b986f *}

<div class="closeBoxX"></div>
<div class="lightUpTitle">Clear Combined CSS &amp; JS</div>

<div style="width: 400px;">
	<p class="page_note">
		When using the Combine, Minify, and Compress feature, the combined 
		output for CSS and JS is generated and stored in static files. Click below
		to clear that combined output if you have made changes to any CSS or JS
		in your template sets, and need the system to re-generate the combined output.
		<br /><br />
		If you are in the process of making a lot of changes to the design,
		consider turning off the combine feature until your changes are complete. 
	</p>
	
	<p class="page_note_error">Warning:  Any un-saved changes on the page will be lost!</p>
	<form action="index.php?page=design_clear_combined&json=1" method="post" id="switchModeForm">
		<div style="text-align: right;">
			<input type="hidden" name="auto_save" value="1" />
			<input type="submit" value="Clear All Combined CSS &amp; JS" class="mini_button" />
			<input type="button" value="Cancel" class="mini_cancel closeLightUpBox" />
		</div>
	</form>
</div>
