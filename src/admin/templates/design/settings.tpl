{* 16.09.0-79-gb63e5d8 *}
{$adminMsgs}
{include file='admin/design/parts/designModeBox.tpl'}

<script>
jQuery(function () {
	var googleLibCheck = function () {
		if (jQuery('#minifyEnabled').prop('checked') && jQuery('#minifyLibs').prop('checked')) {
			//minify libs enabled, hide option to use google API libs
			jQuery('#googleApiBox').hide('fast');
		} else {
			//minify libs not enabled, show option to use google API libs
			jQuery('#googleApiBox').show('fast');
		}
	};

	var minifyShowHide = function () {
		if (jQuery('#minifyEnabled').prop('checked')) {
			jQuery('.minifyOn').show('fast');
			jQuery('.minifyOff').hide('fast');
		} else {
			jQuery('.minifyOn').hide('fast');
			jQuery('.minifyOff').show('fast');
		}
		googleLibCheck();
	};
	minifyShowHide();
	jQuery('#minifyEnabled').click(minifyShowHide);
	//same for htaccess
	var minifyHtShowHide = function () {
		if (jQuery('#tplHtaccess').prop('checked')) {
			jQuery('.htaccessOn').show('fast');
			jQuery('.htaccessOff').hide('fast');
			if (!jQuery('.htaccessOn input[type=checkbox]:checked').length) {
				//if none are checked, check them all
				jQuery('.htaccessOn input[type=checkbox]').prop('checked',true);
			}
		} else {
			jQuery('.htaccessOn').hide('fast');
			jQuery('.htaccessOff').show('fast');
		}
	};
	minifyHtShowHide();
	jQuery('#tplHtaccess').click(minifyHtShowHide);

	var changeExtBase = function () {
		var ext_url = jQuery('#external_url_base').val();
		jQuery('.external_url_base').text(ext_url);
	}
	changeExtBase();

	jQuery('#external_url_base').change(changeExtBase);

	googleLibCheck();
	jQuery('#minifyLibs').click(googleLibCheck);
});
</script>

<form action="index.php?page=design_settings" class="form-horizontal form-label-left" method="post">
	<fieldset>
		<legend>Media Location Settings</legend>
		<div class='x_content'>
			<p class="page_note">
				<strong>Caution:</strong> Changing these settings incorrectly can result
				in a non-working website, make sure you fully understand what each
				setting does before changing these.  Check the user manual for a
				full explanation of each setting.
				<br /><br />
				Most sites should leave these settings as they are.
			</p>
			<div class='form-group'>
			<label class='control-label col-md-5 col-sm-5 col-xs-12 pad-top-0'>Alternate External Media Base URL: <br><span class="small_font">(Leave BLANK in most cases)</span></label>
			  <div class='col-md-6 col-sm-6 col-xs-12'>
			  <input type="text" name="external_url_base" id="external_url_base" class='form-control col-md-7 col-xs-12' value="{$external_url_base|escape}" />
			  </div>
        		</div>

			<div class='form-group'>
			<label class='control-label col-md-5 col-sm-5 col-xs-12 pad-top-0'>JS Library Folder: </label>
			  <div class='col-md-6 col-sm-6 col-xs-12'>
			  	<span class="external_url_base">{$external_url_base}</span>{$GEO_TEMPLATE_LOCAL_DIR|escape}
			  </div>
        		</div>

			<div class='form-group'>
			<label class='control-label col-md-5 col-sm-5 col-xs-12 pad-top-0'>Template Set Folder: </label>
			  <div class='col-md-6 col-sm-6 col-xs-12'>
			  	<span class="external_url_base">{$external_url_base}</span>{$GEO_JS_LIB_LOCAL_DIR|escape}
			  </div>
        		</div>

			<div class="center">
				<br />
				<input type="submit" name="auto_save" value="Save Settings" />
			</div>
		</div>
	</fieldset>

	<fieldset>
		<legend>Optimization Settings</legend>
		<div class='x_content'>

			<div class='form-group'>
			<label class='control-label col-md-4 col-sm-4 col-xs-12'></label>
			  <div class='col-md-7 col-sm-7 col-xs-12'>
			  	<input type="checkbox" name="minifyEnabled" id="minifyEnabled" value="1" {if $minifyEnabled}checked="checked"{/if}/>&nbsp;
			  	Combine, Minify, and Compress CSS and JS <br><span class="small_font">(Recommended for Live Sites)</span>
			  </div>
        		</div>

			<div class="minifyOn">
				{if $minifyEnabled}
					<div class="center">
						<a href="index.php?page=design_clear_combined" class="mini_cancel lightUpLink">Clear Combined CSS &amp; JS</a>
						<br /><br />
					</div>
				{/if}

				<div class='form-group'>
				<label class='control-label col-md-5 col-sm-5 col-xs-12'></label>
				  <div class='col-md-7 col-sm-7 col-xs-12'>
					<input type="checkbox" name="minifyLibs" id="minifyLibs" value="1" {if $minifyLibs}checked="checked"{/if}/>&nbsp;
					Also Combine CSS and JS libraries <br><span class="small_font">(such as jQuery)</span>
				  </div>
				</div>

				<div class='form-group'>
				<label class='control-label col-md-5 col-sm-5 col-xs-12'></label>
				  <div class='col-md-7 col-sm-7 col-xs-12'>
					<input type="checkbox" name="noMinifyJs" id="noMinifyJs" value="1" {if $noMinifyJs}checked="checked"{/if}/>&nbsp;
					Compatibility: do NOT minify JS <br><span class="small_font">(combine only -- useful for older designs or server configurations that prevent minification)</span>
				  </div>
				</div>

				<div class='form-group'>
				<label class='control-label col-md-5 col-sm-5 col-xs-12'></label>
				  <div class='col-md-7 col-sm-7 col-xs-12'>
					<input type="checkbox" name="noMinifyCss" id="noMinifyCss" value="1" {if $noMinifyCss}checked="checked"{/if}/>&nbsp;
					Compatibility: do NOT minify CSS <br><span class="small_font">(combine only -- useful for older designs or server configurations that prevent minification)</span>
				  </div>
				</div>

			</div>

			<div class='form-group'>
			<label class='control-label col-md-4 col-sm-4 col-xs-12'></label>
			  <div class='col-md-7 col-sm-7 col-xs-12'>
			  	<input type="checkbox" name="filter_trimwhitespace" value="1" {if $filter_trimwhitespace}checked="checked"{/if} />&nbsp;
			  	Trim repeated whitespace from final HTML output <br><span class="small_font">(Recommended for sites with a high percentage of mobile or low-bandwidth users)</span>
			  </div>
        		</div>

			<div class='form-group'>
			<label class='control-label col-md-4 col-sm-4 col-xs-12'></label>
			  <div class='col-md-7 col-sm-7 col-xs-12'>
			  	<input type="checkbox" name="useGoogleLibApi" value="1" id="useGoogleLibApi" {if $useGoogleLibApi}checked="checked"{/if} />&nbsp;
			  	Use Google Libraries API <br><span class="small_font">(Allows faster loading of available JS libraries - <a href="http://code.google.com/apis/libraries/devguide.html" onclick="window.open(this.href); return false;">Info Here</a>)</span>
			  </div>
        		</div>

			<div class='form-group'>
			<label class='control-label col-md-4 col-sm-4 col-xs-12'></label>
			  <div class='col-md-7 col-sm-7 col-xs-12'>
			  	<input type="checkbox" name="useFooterJs" id="useFooterJs" value="1" {if $useFooterJs}checked="checked"{/if}/>&nbsp;
			  	Use <strong>{ldelim}footer_html}</strong> to delay loading of certain javascript
			  </div>
        		</div>

			<div class='form-group'>
			<label class='control-label col-md-4 col-sm-4 col-xs-12'></label>
			  <div class='col-md-7 col-sm-7 col-xs-12'>
			  	<input type="checkbox" name="tplHtaccess" id="tplHtaccess" value="1" {if $tplHtaccess}checked="checked"{/if}/>&nbsp;
			  	Use <strong>.htaccess</strong> for <strong>{$GEO_TEMPLATE_LOCAL_DIR}</strong> (requires apache)
			  </div>
        		</div>

			<div class="htaccessOn">

				<div class='form-group'>
				<label class='control-label col-md-5 col-sm-5 col-xs-12'></label>
				  <div class='col-md-7 col-sm-7 col-xs-12'>
					<input type="checkbox" name="tplHtaccess_protect" value="1" {if $tplHtaccess_protect}checked="checked"{/if}/>&nbsp;
					.htaccess - <strong>Stop Prying Eyes</strong> <br><span class="small_font">(deny access to tpl files and folder contents)</span>
				  </div>
				</div>

				<div class='form-group'>
				<label class='control-label col-md-5 col-sm-5 col-xs-12'></label>
				  <div class='col-md-7 col-sm-7 col-xs-12'>
					<input type="checkbox" name="tplHtaccess_compress" value="1" {if $tplHtaccess_compress}checked="checked"{/if}/>&nbsp;
					.htaccess - <strong>Compress Files</strong> <br><span class="small_font">(requires mod_deflate Apache Module)</span>
				  </div>
				</div>

				<div class='form-group'>
				<label class='control-label col-md-5 col-sm-5 col-xs-12'></label>
				  <div class='col-md-7 col-sm-7 col-xs-12'>
					<input type="checkbox" name="tplHtaccess_expires" value="1" {if $tplHtaccess_expires}checked="checked"{/if}/>&nbsp;
					.htaccess - <strong>Cache Files Longer</strong> <br><span class="small_font">(requires mod_expires Apache Module)</span>
				  </div>
				</div>

				<div class='form-group'>
				<label class='control-label col-md-5 col-sm-5 col-xs-12'></label>
				  <div class='col-md-7 col-sm-7 col-xs-12'>
					<input type="checkbox" name="tplHtaccess_rewrite" value="1" {if $tplHtaccess_rewrite}checked="checked"{/if}/>&nbsp;
					.htaccess - Use mod_rewrite for Combined CSS/JS</strong>
				  </div>
				</div>

			</div>
			<div class="center">
				<br />
				<input type="submit" name="auto_save" value="Save Settings" />
			</div>
		</div>
	</fieldset>

	<fieldset>
		<legend>Advanced Settings</legend>
		<div class='x_content'>
			<div class='form-group'>
			<label class='control-label col-md-4 col-sm-4 col-xs-12'></label>
			  <div class='col-md-7 col-sm-7 col-xs-12'>
			  	<input type="checkbox" name="noDefaultCss" value="1" {if $noDefaultCss}checked="checked"{/if} />&nbsp;
				<span class="minifyOff">Do NOT Automatically Reference <strong>default.css</strong> in {literal}{head_html}{/literal}</span>
				<span class="minifyOn">Do NOT Automatically Include <strong>default.css</strong> in Combined CSS Contents</span>
				<br />
				<em><strong>Warning:</strong> May break site, and WILL require additional steps for software updates</em>
			  </div>
        		</div>

			<div class='form-group'>
			<label class='control-label col-md-4 col-sm-4 col-xs-12'></label>
			  <div class='col-md-7 col-sm-7 col-xs-12'>
			  	<input name="useCHMOD" id="chmod" type="checkbox" value="1" {if $useCHMOD}checked="checked"{/if} />&nbsp;
			  	CHMOD 777 Files <br><span class="small_font">(affects operations on files)</span>
			  </div>
        		</div>

			{if $canEditSystemTemplates && $advMode}
				{include file="design/parts/editSystemWarning.tpl"}
			{/if}

			<div class='form-group' {if !$advMode} style="display: none;"{/if}>
			<label class='control-label col-md-4 col-sm-4 col-xs-12'></label>
			  <div class='col-md-7 col-sm-7 col-xs-12'>
			  	<input name="canEditSystemTemplates" id="canEditSystemTemplates" type="checkbox" value="1" {if $canEditSystemTemplates}checked="checked"{/if} />&nbsp;
			  	Allow Edit of system, module, and addon Templates
			  </div>
        		</div>

			{if $iamdeveloper}
				<div class='form-group' {if !$advMode} style="display: none;"{/if}>
				<label class='control-label col-md-4 col-sm-4 col-xs-12'></label>
				  <div class='col-md-7 col-sm-7 col-xs-12'>
					<input name="allowDefaultTsetEdit" id="allowDefaultTsetEdit" type="checkbox" value="1" {if $allowDefaultTsetEdit}checked="checked"{/if} />&nbsp;
					Allow Edit of default template set<br><span class="small_font">(IAMDEVELOPER Setting)</span>
				  </div>
				</div>
			{/if}

			<div class="center">
				<br />
				<input type="submit" name="auto_save" value="Save Settings" />
			</div>
		</div>
	</fieldset>
</form>
