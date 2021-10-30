{* 16.09.0-44-g833ba7b *}

<div id="profile-pic-display"><img src="{$original_profile_pic}" alt="" /></div>
<div id="uploader-wrapper" style="min-width: {$boundary_width}px; min-height: {$boundary_height}px; display: none;">
	<div id="profile-pic-workspace"></div>
	<div class="cntr"><button id="profile-pic-save" class="button">{$msgs.btn_save}</button></div>
</div>

{* hide the actual file input, because it's ugly...overload another button to click it *}
<button class="button" id="profile-pic-upload-btn" onclick="jQuery('#profile-pic-upload').click(); return false;">{$msgs.btn_upload_new}</button>
<img src='{$spinner}' alt='' style="display: none;" id="spinner" />
<input type="file" id="profile-pic-upload" accept="image/*" style="display: none;" />



{add_footer_html}
	<script>
		var $ppWorkspace;
		
		$ppWorkspace = jQuery('#profile-pic-workspace').croppie({		
			viewport: {
				width: {$viewport_width},
				height: {$viewport_height}
			},
			boundary: {
				width: {$boundary_width},
				height: {$boundary_height}
			},
			enableExif: true
		});
	
		function readFile(input) {
			if (input.files && input.files[0]) {
				
				jQuery('#profile-pic-upload-btn').hide(); //kill "upload new" button
				jQuery("#spinner").show(); //show a spinner while uploading
				
				{* this is a way to have javascript read the given file without actually POSTing it *}
				var reader = new FileReader();
				reader.onload = function (e) {
					//bind the read image to the cropper
					$ppWorkspace.croppie('bind', {
						url: e.target.result
					});
					//make the cropper appear, now that it's ready
					jQuery("#profile-pic-display").hide();
					jQuery('#uploader-wrapper').show();
					jQuery("#spinner").hide();
					
				}
				reader.readAsDataURL(input.files[0]);
			}
		}
		
		jQuery('#profile-pic-upload').on('change', function () { readFile(this); });
		
		jQuery('#profile-pic-save').on('click', function (ev) {
			$ppWorkspace.croppie('result', {
				type: 'base64',
				size: 'viewport'
			}).then(function (resp) {
				//resp is the cropped image encoded in base64
				
				//save via ajax
				jQuery.post("AJAX.php?controller=addon_profile_pics&action=savePic",
					{ 
						pic_data: resp,
						user_id: {$user_id} 
					},
					function(ret) { 
						if(ret.status == 'error') {
							//something went wrong
							gjUtil.addMessage('Error '+ret.message, 2000);
						} else {
							//upload OK
							//show the new image and remove the cropper
							jQuery("#profile-pic-display").html("<img src='"+resp+"' alt='' />").show();
							jQuery('#uploader-wrapper').hide();
							jQuery('#profile-pic-upload-btn').show();
						}
					},
					'json'
				);
			});
		});
	</script>
{/add_footer_html}