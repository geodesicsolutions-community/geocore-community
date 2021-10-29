{* 17.05.0-22-g4435795 *}
{$adminMessages}
<form action="" method="post" class="form-horizontal form-label-left">
	<fieldset>
		<legend>Server Settings</legend>
		<div class='x_content'>
			<div class='form-group'>
				<label class='control-label col-md-5 col-sm-5 col-xs-12'>GD Library 2.0: </label>
			        <div class='col-md-6 col-sm-6 col-xs-12'>
			              <input type="radio" name="imagecreatetruecolor_switch" value="0" {if !$imagecreatetruecolor_switch}checked="checked" {/if}/>
				      	Use imagecreatetruecolor<br />
				      <input type="radio" name="imagecreatetruecolor_switch" value="1" {if $imagecreatetruecolor_switch}checked="checked" {/if}/>
					Use older methods
			        </div>
          		</div>

			<div class='form-group'>
				<label class='control-label col-md-5 col-sm-5 col-xs-12'>Process Image Directory (default): </label>
			        <div class='col-md-6 col-sm-6 col-xs-12'>
			              <input type="radio" name="image_upload_type" value="0" {if !$image_upload_type}checked="checked" {/if}/>
				      		From Starting Temp Directory<br />
				      <input type="radio" name="image_upload_type" value="1" {if $image_upload_type}checked="checked" {/if}/>
						Copy First
			        </div>
          		</div>

			<div class='form-group'>
				<label class='control-label col-md-5 col-sm-5 col-xs-12'>Photo Directory URL: </label>
			  	<div class='col-md-6 col-sm-6 col-xs-12'>
			  		<input type="text" name="url_image_directory" class='form-control col-md-7 col-xs-12' value="{$url_image_directory|escape}" />
			  	</div>
			</div>

			<div class='form-group'>
				<label class='control-label col-md-5 col-sm-5 col-xs-12'>Server Path to Root of Photos Directory: <br />
					<span class="small_font">Path to this document: <span style="color: #26B99A;">{$server_dir}</span></span></label>
			  	<div class='col-md-6 col-sm-6 col-xs-12'>
			  		<input type="text" name="image_upload_path" class='form-control col-md-7 col-xs-12' value="{$image_upload_path|escape}" />
			  	</div>
			</div>
			
		</div>
	</fieldset>
	<fieldset>
		<legend>Upload Settings</legend>
		<div class='x_content'>
			<div class='form-group'>
				<label class='control-label col-md-5 col-sm-5 col-xs-12'>Max Size per Photo / File: </label>
				<div class='col-md-6 col-sm-6 col-xs-12 input-group'>
					<input type="text" name="maximum_upload_size" class='form-control col-md-7 col-xs-12' value="{$maximum_upload_size}" /><div class='input-group-addon'>Bytes each</div>
				</div>
			</div>
			<div class='form-group'>
				<label class='control-label col-md-5 col-sm-5 col-xs-12'>Image Resize Quality: </label>
				<div class='col-md-6 col-sm-6 col-xs-12 input-group'>
					<input type="text" name="photo_quality" class='form-control col-md-7 col-xs-12' value="{$photo_quality}" /><div class='input-group-addon'>percent</div>
				</div>
			</div>		
			<div class='form-group'>
				<label class='control-label col-md-5 col-sm-5 col-xs-12'>Max Length of Photo Title/Caption: </label>
				<div class='col-md-6 col-sm-6 col-xs-12 input-group'>
					<input type="number" name="maximum_image_description" class='form-control col-md-7 col-xs-12' value="{$maximum_image_description}" min="0" max="10000" size="3" /><div class='input-group-addon'>(0 to disable)</div>
				</div>
			</div>	
			<div class='form-group'>
				<label class='control-label col-md-5 col-sm-5 col-xs-12'>Starting Title: </label>
			        <div class='col-md-6 col-sm-6 col-xs-12'>
			              	<input type="radio" name="starting_image_title" value="filename"{if $starting_image_title=='filename'} checked="checked"{/if} /> Filename<br />
					<input type="radio" name="starting_image_title" value="blank"{if $starting_image_title=='blank'} checked="checked"{/if} /> Blank
			        </div>
          		</div>			

		</div>
	</fieldset>
	
	<fieldset>
		<legend>Image Block Settings (On Listing Details page)</legend>
		<div class='x_content'>
			<div class='form-group'>
				<label class='control-label col-md-5 col-sm-5 col-xs-12'>Image Block Layout: </label>
				<div class='col-md-6 col-sm-6 col-xs-12'>
					<select name="gallery_style" id='gallery_style' class='form-control col-md-7 col-xs-12'>
						<option value="photoswipe" {if $gallery_style==='photoswipe'}selected="selected"{/if}>PhotoSwipe (Recommended)</option>
						<option value="filmstrip" {if $gallery_style==='filmstrip'}selected="selected"{/if}>Filmstrip</option>
						<option value="gallery2" {if $gallery_style==='gallery2'}selected="selected"{/if}>Optimized Gallery</option>
						<option value="gallery" {if $gallery_style==='gallery'}selected="selected"{/if}>Alternate Gallery</option>
						<option value="classic" {if $gallery_style==='classic'}selected="selected"{/if}>Classic</option>
					</select>
				</div>
				<script>
					jQuery('#gallery_style').change(function() {
						var classic = jQuery(this).val() == 'classic' ? true : false;
						jQuery('.gallery-style-classic').toggle(classic);
						var photoswipe = jQuery(this).val() == 'photoswipe' ? true : false;
						jQuery('.gallery-style-not-photoswipe').toggle(!photoswipe); 
					});
					jQuery(document).ready(function() {
						jQuery('#gallery_style').change();
					});
				</script>
			</div>
			<div class='form-group gallery-style-classic'>
				<label class='control-label col-md-5 col-sm-5 col-xs-12'>Number of Columns:<br /><span class='small_font'>(Classic View only)</span> </label>
				<div class='col-md-6 col-sm-6 col-xs-12'>
					<input type="text" name="photo_columns" class='form-control col-md-7 col-xs-12' value="{$photo_columns}" size="2" />
				</div>
			</div>	

			<div class='gallery-style-not-photoswipe'>
				{if $is_ent}
					<div class='form-group'>
						<label class='control-label col-md-5 col-sm-5 col-xs-12'>Enlarge Photo View: </label>
						<div class='col-md-6 col-sm-6 col-xs-12'>
						      <input type="radio" name="image_link_destination_type" value="1" {if $image_link_destination_type}checked="checked" {/if}/>
						      	Full Size Image Display Page</label><br />
						      <input type="radio" name="image_link_destination_type" value="0" {if !$image_link_destination_type}checked="checked" {/if}/>
							Lightbox Slideshow
						</div>
	          			</div>
				{/if}
				<div class='form-group'>
					<label class='control-label col-md-5 col-sm-5 col-xs-12'>Number of Displayed Images: <br /><span class='small_font'>(Full Size Image Display Page only)</span> </label>
					<div class='col-md-6 col-sm-6 col-xs-12 input-group'>
						<input type="text" name="number_of_photos_in_detail" class='form-control col-md-7 col-xs-12' value="{$number_of_photos_in_detail}" size="2" /><div class='input-group-addon'>(0 to display all)</div>
					</div>
				</div>
			</div>

		</div>
	</fieldset>
	<fieldset class='gallery-style-not-photoswipe'>
		<legend>Lightbox Slideshow Settings</legend>
		<div class='x_content'>
			<p class="page_note" style="text-align: center;">Text Used: <a href="index.php?page=sections_browsing_edit_text&b=157&l=1">Pages Management &gt; Browsing Listings &gt; Image Lightbox Slideshow &gt; Edit Text</a></p>
			<div class='form-group'>
				<label class='control-label col-md-5 col-sm-5 col-xs-12'>Slideshow Enabled: </label>
				<div class='col-md-6 col-sm-6 col-xs-12'>
					<input type="checkbox" name="useSlideshow" value="1"{if $useSlideshow} checked="checked"{/if} />
				</div>
			</div>
			<div class='form-group'>
				<label class='control-label col-md-5 col-sm-5 col-xs-12'>Slideshow Starts Automatically: </label>
				<div class='col-md-6 col-sm-6 col-xs-12'>
					<input type="checkbox" name="startSlideshow" value="1"{if $startSlideshow} checked="checked"{/if} />
				</div>
			</div>
			<div class='form-group'>
				<label class='control-label col-md-5 col-sm-5 col-xs-12'>Use Lightbox Animations: </label>
				<div class='col-md-6 col-sm-6 col-xs-12'>
					<input type="checkbox" name="useLightboxAnimations" value="1"{if $useLightboxAnimations} checked="checked"{/if} />
				</div>
			</div>
		</div>
	</fieldset>
	<fieldset>
		<legend>Photo Max Dimension Settings</legend>
		<div>
			{foreach from=$dimensionSettings item="settings"}
				<div class="form-group">
					<label class="control-label col-xs-12 col-sm-5">{$settings.label}</label>
					<div class="col-xs-12 col-sm-6">
						<div class="input-group">
							<div class="input-group-addon">max width</div>
							<input class='form-control' type='number' min='0' name='dim[{$settings.name}_width]' value='{$settings.width}' />
							<div class="input-group-addon">pixels</div>
						</div>
						<div class="input-group">
							<div class="input-group-addon">max height</div>
							<input class='form-control' type='number' min='0' name='dim[{$settings.name}_height]' value='{$settings.height}' />
							<div class="input-group-addon">pixels</div>
						</div>
					</div>
				</div>
			{/foreach}
		</div>
	</fieldset>
	
	<div style="text-align: center;">
		<input type="submit" name="auto_save" value="Save" />
	</div>
</form>