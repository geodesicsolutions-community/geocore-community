{* 16.09.0-106-ge989d1f *}
{$adminMsg}
<form action=index.php?mc=site_setup&page=security_image_config method=post class="form-horizontal">
	<fieldset>
		<legend>Security Image Type</legend>
		<div>
			<div class="form-group">
				<label class='control-label col-xs-12 col-sm-5'>Security Image Type</label>
				<div class="col-xs-12 col-sm-6">
					<label><input type="radio" id="imageType_system" name="security_image[imageType]" value="system" {if $reg->get('imageType','system')=='system'} checked="checked"{/if} /> System Generated</label><br />
					<label><input type="radio" id="imageType_recaptcha" name="security_image[imageType]" value="recaptcha"{if $reg->get('imageType','system')=='recaptcha'} checked="checked"{/if} /> reCAPTCHA&trade;</label> (3rd Party, <a href="http://www.google.com/recaptcha" onclick="window.open(this.href); return false;">see website</a>)
				</div>
			</div>
		</div>
	</fieldset>
	
	<fieldset class="built_in_images">
		<legend>System-Generated Image Abilities</legend>
		<div>
			{if !($abilities.imagecreatetruecolor||$abilities.imagecreate)}
				<p class="page_note_error">reCAPTCHA&trade; Only - Minimum requirement (GD Library) for System Generated Security Image is not met.</p>
				<div class="medium_font" style="text-align: left;">
					<span class="medium_error_font">Minimum requirement for System Generated Security Image is not met.</span>
					<br /><br />
					System Generated Security image requires <strong>GD library</strong> in order to be able to create the security image.
					However, it appears that this host does not have the GD library installed and enabled. 
					Technically speaking, neither of the functions imagecreate() or imagecreatetruecolor() can be used, which
					indicates that GD library is not installed.  
					<br /><br />
					Using <em>System Generated</em> security image will <strong>not display anything until GD libraries are installed and enabled on this host</strong>.
				</div>
			{else}
				{if !($abilities.imagepng || $abilities.imagegif || $abilities.imagejpeg || $abilities.imagewbmp)}
					<p class="page_note_error">reCAPTCHA&trade; Only - No library support found for GIF, JPEG, PNG, or WBMP.  The system generated security image requires GD library with at least one of those image types installed in able to generate the security image "on the fly".</p>
					<div class="medium_font" style="text-align: left;">
						<span class="medium_error_font">Minimum requirement for System Generated Security Image is not met.</span>
						<br /><br />
						The System Generated Security image requires <strong>GD library</strong> in order to be able to create the security image.
						It appears that GD library is installed, however there is no support for GIF, JPEG, PNG, or WBMP found, which
						indicates that the GD library may be mis-configured or not installed correctly. 
						Technically speaking, none of the functions imagepng(), imagegif(), imagejpeg(), or imagewbmp() can be used,
						and the System Generated Security Image needs at least one of those to be able to work.  
						<br /><br />
						The security image will <strong>not display until support for the image types listed above are installed and 
						enabled on this host</strong>.
					</div>
				{else}
					<div class='col_hdr'>General Capabilities</div>
					<div class="form-group">
						<label class='control-label col-xs-12 col-sm-5'>Blur Filter</label>
						<div class='col-xs-12 col-sm-6 vertical-form-fix'>{if !$abilities.imagefilter}Not {/if}Supported</div>
					</div>
					<div class="form-group">
						<label class='control-label col-xs-12 col-sm-5'>Emboss Filter</label>
						<div class='col-xs-12 col-sm-6 vertical-form-fix'>{if !$abilities.imagefilter}Not {/if}Supported</div>
					</div>
					<div class="form-group">
						<label class='control-label col-xs-12 col-sm-5'>"Sketchy" Filter</label>
						<div class='col-xs-12 col-sm-6 vertical-form-fix'>{if !$abilities.imagefilter}Not {/if}Supported</div>
					</div>
					<div class="form-group">
						<label class='control-label col-xs-12 col-sm-5'>Negative Effect</label>
						<div class='col-xs-12 col-sm-6 vertical-form-fix'>{if !$abilities.imagefilter}Not {/if}Supported</div>
					</div>
					<div class='col_hdr'>Character/Font Capabilities</div>
					
					<div class="form-group">
						<label class='control-label col-xs-12 col-sm-5'>GD True Type Fonts (TTF)</label>
						<div class='col-xs-12 col-sm-6 vertical-form-fix'>
							{if $abilities.imagettftext}Installed &amp; Enabled on Host{else}TTF not supported{/if}
						</div>
					</div>
					{if $abilities.imagettftext}
						<div class="form-group">
							<label class='control-label col-xs-12 col-sm-5'>Fonts Directory</label>
							<div class='col-xs-12 col-sm-6 vertical-form-fix'>
								{$fonts_dir}
							</div>
						</div>
						<div class="form-group">
							<label class='control-label col-xs-12 col-sm-5'>Font Files</label>
							<div class='col-xs-12 col-sm-6 vertical-form-fix'>
								{foreach from=$fonts item=font}{$font}<br />{/foreach}
							</div>
						</div>
						<div class='page_note' style='text-align: left;'>
							The system generated security image will randomly select which font to use on a per-character basis.
							<br />
							You can upload additional TTF font files if you wish, just be sure they are uploaded in <strong>BINARY mode</strong> to prevent font corruption.
						</div>
					{else}
						<div class="form-group">
							<label class='control-label col-xs-12 col-sm-5'>
								Font Used
							</label>
							<div class='col-xs-12 col-sm-6 vertical-form-fix'>
								Built-in default font for this host<br />
								(Without TTF support, font abilities are limited)
							</div>
						</div>
					{/if}
					<div class='col_hdr'>Security Image Preview</div>
					<br />
					<div id='addon_security_image' style='text-align: center; border:0px; margin:0px; padding:0px;'>
						<a href='javascript:void(0)' onclick='changeSecurityImage();'>
							<img src="../{$classifieds_file_name}?a=ap&addon=security_image&page=image" alt='Security Image' />
						</a>
					</div>
				{/if}
			{/if}
		</div>
	</fieldset>
	
	<fieldset class="reCAPTCHA_images">
		<legend>reCAPTCHA&trade; Preview</legend>
		<div>
			{if $reg->imageType=='recaptcha'}
				<div class="page_note">If you do not see the reCAPTCHA&trade; preview below, check that the public and private keys are set correctly in the settings below.</div>
				{include file='recaptcha.tpl' recaptcha_error=$error.recaptcha}
			{else}
				<div class="page_note">Save Changes to view reCAPTCHA&trade; preview.</div>
			{/if}
		</div>
	</fieldset>
	
	<fieldset>
		<legend>Locations</legend>
		<div>
			<div class="form-group">
				<label class='control-label col-xs-12 col-sm-5'>
					Registration
				</label>
				<div class='col-xs-12 col-sm-6'>
					<input type='hidden' name='security_image[secure_registration]' value='0' />
					<input type='checkbox' name='security_image[secure_registration]' id='secure_registration' value='1'{if $reg->secure_registration} checked="checked"{/if} />
				</div>
			</div>
			<div class="form-group">
				<label class='control-label col-xs-12 col-sm-5'>
					User Login
				</label>
				<div class='col-xs-12 col-sm-6'>
					<input type='hidden' name='security_image[secure_login]' value='0' />
					<input type='checkbox' name='security_image[secure_login]' id='secure_login' value='1'{if $reg->secure_login} checked="checked"{/if} />
				</div>
			</div>
			<div class="form-group">
				<label class='control-label col-xs-12 col-sm-5'>
					Messaging
				</label>
				<div class='col-xs-12 col-sm-6'>
					<input type='hidden' name='security_image[secure_messaging]' value='0' />
					<input type='checkbox' name='security_image[secure_messaging]' id='secure_messaging' value='1'{if $reg->secure_messaging} checked="checked"{/if} />
				
					<br />
					<input type="hidden" name="security_image[login_override]" value="0" /><input type="checkbox" name="security_image[login_override]" value="1" {if $reg->login_override}checked="checked"{/if} />
					Bypass Messaging images for logged-in users
				</div>
			</div>
			<div class="form-group">
				<label class='control-label col-xs-12 col-sm-5'>
					Listing Placement
				</label>
				<div class='col-xs-12 col-sm-6'>
					<input type='hidden' name='security_image[secure_listing]' value='0' />
					<input type='checkbox' name='security_image[secure_listing]' id='secure_listing' value='1'{if $reg->secure_listing} checked="checked"{/if} />
				</div>
			</div>
			{if $anonEnabled}
				<div class="form-group">
					<label class='control-label col-xs-12 col-sm-5'>
						Anonymous Listing Placement
					</label>
					<div class='col-xs-12 col-sm-6'>
						<input type='hidden' name='security_image[secure_listing_anon]' value='0' />
						<input type='checkbox' name='security_image[secure_listing_anon]' id='secure_listing_anon' value='1'{if $reg->secure_listing_anon} checked="checked"{/if} />
					</div>
				</div>
			{/if}
			<div class="form-group">
				<label class='control-label col-xs-12 col-sm-5'>
					Forgot Password
				</label>
				<div class='col-xs-12 col-sm-6'>
					<input type='hidden' name='security_image[secure_forgot_pass]' value='0' />
					<input type='checkbox' name='security_image[secure_forgot_pass]' id='forgot_pass' value='1'{if $reg->secure_forgot_pass} checked="checked"{/if} />
				</div>
			</div>
		</div>
	</fieldset>
	<fieldset class="reCAPTCHA_images">
		<legend>reCAPTCHA&trade; Settings</legend>
		<div>
			<div class="form-group">
				<label class='control-label col-xs-12 col-sm-5'>
					API Public/Private Key Sign-Up (Its FREE)
				</label>
				<div class='col-xs-12 col-sm-6 vertical-form-fix'>
					<a href="https://www.google.com/recaptcha/admin" onclick="window.open(this.href); return false;">
						Sign-Up Page
					</a>
				</div>
			</div>
			<div class="form-group">
				<label class='control-label col-xs-12 col-sm-5'>
					Site Key (public)
				</label>
				<div class='col-xs-12 col-sm-6'>
					<input type='text' name='security_image[recaptcha_pub_key]' value='{$recaptcha_pub_key}' class='form-control' />{$error.recaptcha_pub_key}
				</div>
			</div>
			<div class="form-group">
				<label class='control-label col-xs-12 col-sm-5'>
					Secret Key (private)
				</label>
				<div class='col-xs-12 col-sm-6'>
					{* Note: We don't actually show private key to browser since it is secret. *}
					<input type='password' name='security_image[recaptcha_private_key]' class='form-control' value='xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx' />{$error.recaptcha_private_key}
				</div>
			</div>
			<div class="form-group">
				<label class='control-label col-xs-12 col-sm-5'>
					Theme
				</label>
				<div class='col-xs-12 col-sm-6'>
					<input type="radio" name="security_image[recaptcha_theme]" value="light"{if $recaptcha_theme=='light'} checked="checked"{/if} /> Light (default theme)<br />
					<input type="radio" name="security_image[recaptcha_theme]" value="dark"{if $recaptcha_theme=='dark'} checked="checked"{/if} /> Dark
				</div>
			</div>
		</div>
	</fieldset>
	
	<fieldset class="built_in_images">
		<legend>Image Size</legend>
		<div>
			<div class="form-group">
				<label class='control-label col-xs-12 col-sm-5'>
					Width
				</label>
				<div class='col-xs-12 col-sm-6'>
					{$error.width}
					<div class="input-group">
						<input type='text' id='width' name='security_image[width]' value='{$reg->width}' class="form-control" />
						<div class="input-group-addon">pixels</div>
					</div>
				</div>
			</div>
			<div class="form-group">
				<label class='control-label col-xs-12 col-sm-5'>
					Height
				</label>
				<div class='col-xs-12 col-sm-6'>
					{$error.height}
					<div class="input-group">
						<input type='text' id='height' name='security_image[height]' value='{$reg->height}' class="form-control" />
						<div class="input-group-addon">pixels</div>
					</div>
				</div>
			</div>
		</div>
	</fieldset>
	<fieldset class="built_in_images">
		<legend>Character Settings</legend>
		<div>
			<div class="form-group">
				<label class='control-label col-xs-12 col-sm-5'>
					Number of Characters
				</label>
				<div class='col-xs-12 col-sm-6'>
					{$error.numChars}
					<input type='text' id='numChars' name='security_image[numChars]' value='{$reg->numChars}' class="form-control" />
				</div>
			</div>
			<div class="form-group">
				<label class='control-label col-xs-12 col-sm-5'>
					Font Size {if !$abilities.imagettftext}(1-5){/if}
				</label>
				<div class='col-xs-12 col-sm-6'>
					{$error.fontSize}
					<div class="input-group">
						<input type='text' id='fontSize' name='security_image[fontSize]' value='{$reg->fontSize}' class="form-control" />
						{if $abilities.imagettftext}
							<div class="input-group-addon">
								{if $abilities.gd_version.1==2}
									points
								{else}
									pixels
								{/if}
							</div>
						{else}
							<input type="hidden" id="use_small_font_size" />
						{/if}
					</div>
				</div>
			</div>
			<div class="form-group">
				<label class='control-label col-xs-12 col-sm-5'>
					Use Random Colors
				</label>
				<div class='col-xs-12 col-sm-6'>
					<input type='hidden' name='security_image[useRandomColors]' value='0' />
					<input type='checkbox' name='security_image[useRandomColors]' id='useRandomColors' value='1'{if $reg->useRandomColors} checked="checked"{/if} />
					<a class='btn btn-xs btn-info' id="secure_font_color_adv_link" onclick="jQuery('#secure_font_color_adv').show(); jQuery('#secure_font_color_adv_link').hide(); return false;">Advanced &rsaquo;</a>
					<div id='secure_font_color_adv' style='display: none;'>
						RGB Color Ranges (0-255):
						<br />
						<span style='color: red;'>Red:</span> <input type='text' name='security_image[rmin]' id='rmin' value='{$reg->rmin}' size='2' /> - <input type='text' name='security_image[rmax]' id='rmax' value='{$reg->rmax}' size='2' /><br />
						<span style='color: green;'>Green:</span> <input type='text' name='security_image[gmin]' id='gmin' value='{$reg->gmin}' size='2' /> - <input type='text' name='security_image[gmax]' id='gmax' value='{$reg->gmax}' size='2' /><br />
						<span style='color: blue;'>Blue:</span> <input type='text' name='security_image[bmin]' id='bmin' value='{$reg->bmin}' size='2' /> - <input type='text' name='security_image[bmax]' id='bmax' value='{$reg->bmax}' size='2' /><br />
						{$error.secure_font_color}
					</div>
				</div>
			</div>
			<div class="form-group">
				<label class='control-label col-xs-12 col-sm-5'>
					Characters Allowed (Case-insensitive matching)
				</label>
				<div class='col-xs-12 col-sm-6'>
					{$error.allowedChars}
					<input type='text' id='allowedChars' name='security_image[allowedChars]' value="{$reg->allowedChars|escape}" class="form-control" />
				</div>
				
			</div>
		</div>
	</fieldset>
	<fieldset class="built_in_images">
		<legend>Overall Image Effects</legend>
		<div>
		<div class="form-group">
			<label class='control-label col-xs-12 col-sm-5'>
				Distort
			</label>
			<div class='col-xs-12 col-sm-6'>
				<input type='hidden' name='security_image[useDistort]' value='0' />
				<input type='checkbox' name='security_image[useDistort]' id='useDistort' value='1'{if $reg->useDistort} checked="checked"{/if} />
				<a href="#" class="btn btn-xs btn-info" id="distort_adv_link" onclick="jQuery('#distort_adv').show(); jQuery('#distort_adv_link').hide(); return false;">Advanced &rsaquo;</a>
			</div>
		</div>
		<div class="form-group" id='distort_adv' style='display: none;'>
			<label class='control-label col-xs-12 col-sm-5'>Distort Amount (0.0 - 1.0):</label>
			<div class='col-xs-12 col-sm-6'>
				{$error.distort}
				<input type='text' id='distort' name='security_image[distort]' value='{$reg->distort}' class="form-control" />
			</div>
		</div>
		<div class="form-group">
			<label class='control-label col-xs-12 col-sm-5'>
				Blur Filter
			</label>
			<div class='col-xs-12 col-sm-6'>
				<input type='hidden' name='security_image[useBlur]' value='0' />
				<input type='checkbox' name='security_image[useBlur]' id='useBlur' value='1'{if $reg->useBlur} checked="checked"{/if} />
			</div>
			
		</div>
		<div class="form-group">
			<label class='control-label col-xs-12 col-sm-5'>
				Emboss Filter
			</label>
			<div class='col-xs-12 col-sm-6'>
				<input type='hidden' name='security_image[useEmboss]' value='0' />
				<input type='checkbox' name='security_image[useEmboss]' id='useEmboss' value='1'{if $reg->useEmboss} checked="checked"{/if} />
			</div>
			
		</div>
		<div class="form-group">
			<label class='control-label col-xs-12 col-sm-5'>
				Sketchy Filter
			</label>
			<div class='col-xs-12 col-sm-6'>
				<input type='hidden' name='security_image[useSketchy]' value='0' />
				<input type='checkbox' name='security_image[useSketchy]' id='useSketchy' value='1'{if $reg->useSketchy} checked="checked"{/if} />
			</div>
			
		</div>
		<div class="form-group">
			<label class='control-label col-xs-12 col-sm-5'>
				Photo Negative
			</label>
			<div class='col-xs-12 col-sm-6'>
				<input type='hidden' name='security_image[useNegative]' value='0' />
				<input type='checkbox' name='security_image[useNegative]' id='useNegative' value='1'{if $reg->useNegative} checked="checked"{/if} />
			</div>
			
		</div>
		</div>
	</fieldset>
	<fieldset class="built_in_images">
		<legend>Add to Image</legend>
		<div>
			<div class="form-group">
				<label class='control-label col-xs-12 col-sm-5'>
					Refresh Image Overlay
				</label>
				<div class='col-xs-12 col-sm-6'>
					<input type='hidden' name='security_image[useRefresh]' value='0' />
					<input type='checkbox' name='security_image[useRefresh]' id='useRefresh' value='1'{if $reg->useRefresh} checked="checked"{/if} />
					<a href="#" class="btn btn-xs btn-info" id="secure_refresh_adv_link" onclick="jQuery('#secure_refresh_adv').show(); jQuery('#secure_refresh_adv_link').hide(); return false;">Advanced &rsaquo;</a>
				</div>
			</div>
			<div class="form-group" style='display: none;' id='secure_refresh_adv'>
				<label class='control-label col-xs-12 col-sm-5'>Image URL:</label>
				<div class='col-xs-12 col-sm-6'>
					{$error.refreshUrl}
					<input type='text' id='refreshUrl' name='security_image[refreshUrl]' value="{$reg->refreshUrl|escape}" class='form-control' />
				</div>
			</div>
			
			<div class="form-group">
				<label class='control-label col-xs-12 col-sm-5'>
					Grid
				</label>
				<div class='col-xs-12 col-sm-6'>
					<input type='hidden' name='security_image[useGrid]' value='0' />
					<input type='checkbox' name='security_image[useGrid]' id='useGrid' value='1'{if $reg->useGrid} checked="checked"{/if} />
					<a href="#" class="btn btn-xs btn-info" id="numGrid_adv_link" onclick="jQuery('#numGrid_adv').show(); jQuery('#numGrid_adv_link').hide(); return false;">Advanced &rsaquo;</a>
				</div>
			</div>
			<div class="form-group" style='display: none;' id='numGrid_adv'>
				<label class='control-label col-xs-12 col-sm-5'># Grid Lines:</label>
				<div class='col-xs-12 col-sm-6'>
					{$error.numGrid}
					<input type='text' id='numGrid' name='security_image[numGrid]' value="{$reg->numGrid}" class='form-control' />
				</div>
			</div>
			
			
			<div class="form-group">
				<label class='control-label col-xs-12 col-sm-5'>
					Lines
				</label>
				<div class='col-xs-12 col-sm-6'>
					<input type='hidden' name='security_image[useLines]' value='0' />
					<input type='checkbox' name='security_image[useLines]' id='useLines' value='1'{if $reg->useLines} checked="checked"{/if} />
					<a href="#" class="btn btn-xs btn-info" id="lines_adv_link" onclick="jQuery('#lines_adv').show(); jQuery('#lines_adv_link').hide(); return false;">Advanced &rsaquo;</a>
				</div>
			</div>
			<div class="form-group" style='display: none;' id='lines_adv'>
				<label class='control-label col-xs-12 col-sm-5'># Lines:</label>
				<div class='col-xs-12 col-sm-6'>
					{$error.lines}
					<input type='text' id='lines' name='security_image[lines]' value="{$reg->lines}" class='form-control' />
				</div>
			</div>
			
			<div class="form-group">
				<label class='control-label col-xs-12 col-sm-5'>
					Noise
				</label>
				<div class='col-xs-12 col-sm-6'>
					<input type='hidden' name='security_image[useNoise]' value='0' />
					<input type='checkbox' name='security_image[useNoise]' id='useNoise' value='1'{if $reg->useNoise} checked="checked"{/if} />
					<a href="#" class="btn btn-xs btn-info" id="numNoise_adv_link" onclick="jQuery('#numNoise_adv').show(); jQuery('#numNoise_adv_link').hide(); return false;">Advanced &rsaquo;</a>
				</div>
			</div>
			<div class="form-group" style='display: none;' id='numNoise_adv'>
				<label class='control-label col-xs-12 col-sm-5'>Noise Amount:</label>
				<div class='col-xs-12 col-sm-6'>
					{$error.numNoise}
					<input type='text' id='numNoise' name='security_image[numNoise]' value="{$reg->numNoise}" class='form-control' />
				</div>
			</div>
		</div>
	</fieldset>
	<fieldset class="built_in_images">
		<legend>Load Preset Settings</legend>
		<div>
			<span class='medium_font'>
				Note: presets may look differently from host to host, depending on what GD 
				libraries are supported, and the version of PHP.
			</span><br />
			<!-- PRESET BUTTONS - ADD HERE - PICK BEST SECTION TO ADD TO -->
			<div class='col_hdr'>
				Overall Look
			</div>
			
			<div style='text-align: center; margin-top: 10px; margin-bottom: 10px;'>
				<a href="#" class="mini_button" onclick="loadInstallDefaults(); return false;">Fresh Install Default</a>
				<a href="#" class="mini_button" onclick="loadCleanLook(); return false;">Clean &amp; Crisp</a>
				<a href="#" class="mini_button" onclick="loadIceyBlackLook(); return false;">Icey Black</a>
				<a href="#" class="mini_button" onclick="loadPlaidLook(); return false;">Plaid</a>
				<a href="#" class="mini_button" onclick="loadAsphaltLook(); return false;">Chalk On Asphalt</a>
				<a href="#" class="mini_button" onclick="loadGrainyLook(); return false;">Grainy</a>
			</div>
			
			<div class='col_hdr'>
				Color Range Presets
			</div>
			
			<div style='text-align: center; margin-top: 10px; margin-bottom: 10px;'>
				<a href="#" class="mini_button" onclick="loadFontColorRed(); return false;">Reds</a>
				<a href="#" class="mini_button" onclick="loadFontColorGreen(); return false;">Greens</a>
				<a href="#" class="mini_button" onclick="loadFontColorBlue(); return false;">Blues</a>
				<a href="#" class="mini_button" onclick="loadFontColorLight(); return false;">Light Colors</a>
				<a href="#" class="mini_button" onclick="loadFontColorDark(); return false;">Dark Colors</a>
				<a href="#" class="mini_button" onclick="loadFontColorBright(); return false;">Bright Colors</a>
			</div>
			
			<div class='col_hdr'>
				Misc
			</div>
			
			<div style='text-align: center; margin-top: 10px; margin-bottom: 10px;'>
				<a href="#" class="mini_button" onclick="advFormDefault(); return false;">Reset Advanced Settings to Defaults</a>
				<a href="#" class="mini_button" onclick="loadAlphaLowercase(); return false;">Lowercase Letters</a>
			</div>
		
		</div>
	</fieldset>

	<div class="center"><input type='submit' name="auto_save" value='Save'></div>
</form>