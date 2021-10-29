{* 16.09.0-79-gb63e5d8 *}

{$admin_msgs}

<form action="" method="post" class='form-horizontal' onsubmit="if(document.getElementById('tmce').checked)return confirm('WARNING!\nThe editor attempts to correct HTML that is invalid. This could cause problems with your templates depending on your design. Contact support for details.\n\nDo you want to activate the editor?');">
	<fieldset>
		<legend>WYSIWYG Editor Settings</legend>
		<div class="x_content">
			<div class="form-group">
				<label class="control-label col-xs-12 col-sm-5">Admin textareas{$tooltip|replace:'EGAD':'1'}</label>
				<div class="col-xs-12 col-sm-6">
					<input type="radio" name="use_admin_wysiwyg" value="0"{if !$use_admin_wysiwyg} checked="checked"{/if} /> None<br />
					<input type="radio" name="use_admin_wysiwyg" value="TinyMCE"{if $use_admin_wysiwyg=='TinyMCE'} checked="checked"{/if} id="tmce" /> TinyMCE
				</div>
			</div>
			
			<div class="form-group">
				<label class="control-label col-xs-12 col-sm-5">CSS stylesheets{$tooltip|replace:'EGAD':'2'}<br />(comma separated)</label>
				<div class="col-xs-12 col-sm-6">
					<textarea name="wysiwyg_css_uri" class="form-control" cols="50">{$wysiwyg_css_uri}</textarea>
				</div>
			</div>
			
			<div class="center">
				<input type="submit" name="auto_save" value="Save" />
			</div>
	
		</div>
	</fieldset>
	<fieldset>
		<legend>Template Code Editor Codemirror Settings</legend>
		<div class="x_content">
			<p class="page_note">When editing a template, the <strong><i class="fa fa-code"></i> Source Code Editor</strong> uses a 3rd party library called CodeMirror
				to make the contents easier to edit, providing line numbers and syntax highlighting and such.  Below are a few different
				settings to allow you to change the look and behavior of CodeMirror.</p>
			<div class="form-group">
				<label class="control-label col-xs-12 col-sm-5">Editor Theme</label>
				<div class="col-xs-12 col-sm-6">
					<select name="codemirrorTheme" class="form-control col-md-7 col-xs-12">
						<option value="0">Default</option>
						{foreach $codemirrorThemes as $theme}
							<option{if $codemirrorTheme==$theme} selected="selected"{/if}>{$theme}</option>
						{/foreach}
					</select>
				</div>
			</div>
			<div class="form-group">
				<label class="control-label col-xs-12 col-sm-5">Enable Auto-Tab</label>
				<div class="col-xs-12 col-sm-6">
					<input type="checkbox" name="codemirrorAutotab" value="1"{if $codemirrorAutotab} checked="checked"{/if} />
				</div>
			</div>
			<div class="form-group">
				<label class="control-label col-xs-12 col-sm-5">Enable Simple Search/Replace within editor</label>
				<div class="col-xs-12 col-sm-6">
					<input type="checkbox" name="codemirrorSearch" value="1"{if $codemirrorSearch} checked="checked"{/if} />
				</div>
			</div>
			<div class="center">
				<input type="submit" name="auto_save" value="Save" />
			</div>
		</div>
	</fieldset>
</form>