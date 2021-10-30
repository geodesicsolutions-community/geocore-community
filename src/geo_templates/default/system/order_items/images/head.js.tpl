{* 7.3.1-82-gadacb26 *}

<script type="text/javascript">
//<![CDATA[
	jQuery(function () {
		gjUtil.imageUpload.init = function (combinedReload) {
			if (!combinedReload) {
				gjUtil.imageUpload._adminId = {$adminId};
				gjUtil.imageUpload._userId = {$userId};
				gjUtil.imageUpload._maxImages = {$images.max};
				gjUtil.imageUpload._maxUploadSize = {$maximum_upload_size};
				
				{if $in_admin}
					gjUtil.imageUpload._ajaxUrl = '../AJAX.php';
				{/if}
				
				//populate text
				gjUtil.imageUpload._msgs = {
					m500667 : '{$messages.500667|escape_js}',
					m500677 : '{$messages.500677|escape_js}',
					m500678 : '{$messages.500678|escape_js}',
					m500689 : '{$messages.500689|escape_js}',
					m500682 : '{$messages.500682|escape_js}',
					m500818 : '{$messages.500818|escape_js}',
					tooManyFiles : '{$messages.502146|escape_js}'
				};
			}
			
			//initialize things
			gjUtil.imageUpload.observers();
			
			//also need to hide the upload button if already at the max number
			//figure out how many images are being used
			var currentCount = jQuery('#imagesUploaded > .media-preview').length;
			
			if (currentCount >= gjUtil.imageUpload._maxImages) {
				//max images reached, hide the button for uploads
				jQuery('#imagesPickfiles').hide('fast');
			} else {
				jQuery('#imagesPickfiles').show('fast');
			}
			
			gjUtil.imageUpload._pl = new plupload.Uploader({
				runtimes : 'html5,flash,silverlight,html4',
				browse_button : 'imagesPickfiles',
				drop_element : 'imagesFileList',
				container: 'imagesContainer',
				max_file_size : {$maximum_upload_size},
				url : '{if $in_admin}../{/if}AJAX.php?controller=UploadImage&action=upload&adminId={$adminId}&userId={$userId}&ua={$ua}',
				{if $fullWidth && $fullHeight}
					resize : { width : {$fullWidth}, height : {$fullHeight}, quality : 90 },
				{/if}
				flash_swf_url : '{if $in_admin}../{/if}js/plupload/Moxie.swf',
				silverlight_xap_url : '{if $in_admin}../{/if}js/plupload/Moxie.xap',
				filters : [
					{ title : '{$messages.502147|escape_js}', extensions : '{$fileTypes|escape_js}' }
				]
			});
			gjUtil.imageUpload._pl.bind('Init', function (up, params) {
				//create a way to show runtime for easier debugging when people
				//send screenshots
				var rt = params.runtime || '?';
				jQuery('#imagesFilelist').append('<div class="media-runtime">'+rt+'</div>');
				if (rt=='?') {
					//no upload supported?
					jQuery('#imagesFilelist').append('<p class="error">Error: Browser does not support file uploads.</p>');
				}
			});
			
			gjUtil.imageUpload._pl.init();
			
			gjUtil.imageUpload._pl.bind('FilesAdded', gjUtil.imageUpload.plFilesAdded);
			gjUtil.imageUpload._pl.bind('UploadProgress', gjUtil.imageUpload.plUploadProgress);
			gjUtil.imageUpload._pl.bind('BeforeUpload', gjUtil.imageUpload.plBeforeUpload);		
			gjUtil.imageUpload._pl.bind('Error', gjUtil.imageUpload.plError);
			gjUtil.imageUpload._pl.bind('FileUploaded', gjUtil.imageUpload.plFileUploaded);
			
			gjUtil.imageUpload._init();
		};
		//initalize it for first time...  passing in false re-initializes everything
		//including text and such that doesn't change
		gjUtil.imageUpload.init(false);
	});
//]]>
</script>