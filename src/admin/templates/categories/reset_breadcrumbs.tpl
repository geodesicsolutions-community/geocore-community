{* 7.5.3-36-gea36ae7 *}

<div class="closeBoxX"></div>
<div class="lightUpTitle">Reset Listing's Category Breadcrumbs</div>


<form style="display:block; margin: 15px; width: 600px;" id="breadcrumbForm" action="index.php?page=category_rescan_listings" method="post">

	<p class="page_note">This tool will reset (fix) the category breadcrumbs set for all listings
		on the site.  For sites with a lot of listings, as you can imagine this could
		take a long time, so it is broken up into "small batches", controlled below.
		<br /><br />
		You would typically use this tool after making any changes to the category structure,
		where existing categories that contain listings in them are moved.  See the
		user manual for more details about when this should be used and how to use it.
	</p>
	
	<div class="{cycle values='row_color1,row_color2'}">
		<div class="leftColumn">Listing batch size:</div>
		<div class="rightColumn">
			<label><input type="text" name="batch_size" id="batch_size" value="200" size="6" /> Listings per batch</label><br />
		</div>
		<div class="clearColumn"></div>
	</div>
	
	<div class="{cycle values='row_color1,row_color2'}">
		<div class="leftColumn">Start with</div>
		<div class="rightColumn">
			<input type="text" name="batch_run" id="batch_run" value="1" size="6"/>
		</div>
		<div class="clearColumn"></div>
	</div>
	
	<br />
	<div class="center">
		<a href="#" class="mini_button breadcrumb-start">Start</a>
		<a href="#" class="mini_button breadcrumb-stop" style="display: none;">Stop</a>
		<input type="hidden" name="auto_save" value="1" />
	</div>
	<br />
	<div class="breadcrumb-results" style="white-space: pre-wrap; padding: 5px; height: 75px; overflow-y: auto; border: thin solid green; display: none;"></div>
	<br /><br />
	<div style="float: right;">
		<input type="button" class="closeLightUpBox mini_cancel" value="Cancel" />
	</div>
	<br />
</form>

<script>
	var myBread = {
		echo : function (msg) {
			jQuery('.breadcrumb-results').show().append(msg+'<br />');
		},
		autoStart : true,
		processBatch : function () {
			jQuery('.breadcrumb-start').hide();
			jQuery('.breadcrumb-stop').show();
			myBread.echo('starting next batch...');
			jQuery('#breadcrumbForm input').prop({ disabled: false, readonly: false });
			var fData = jQuery('#breadcrumbForm').serialize();
			//stop changes
			jQuery('#breadcrumbForm input').prop({ disabled: true, readonly: true });
			jQuery.ajax({
					url : 'index.php?page=category_rescan_listings&runBatch=1',
					data : fData,
					dataType: 'json',
					type: 'POST'
				})
				.done(function (data) {
					jQuery('.breadcrumb-results').empty();
					if (!data) {
						myBread.echo('Unknown Server Error, please try again.');
						return;
					}
					if (data.error) {
						myBread.echo('Error: '+data.error);
					}
					if (data.msg) {
						myBread.echo(data.msg);
					}
					if (data.batch_run) {
						//there is a next batch to run...
						jQuery('#batch_run').val(data.batch_run);
						if (data.complete) {
							jQuery('.breadcrumb-stop').click();
						} else if (myBread.autoStart) {
							myBread.processBatch();
						}
					}
				});
		}
	};
	
	jQuery('.breadcrumb-start').click(function () {
		jQuery('.breadcrumb-start').hide();
		jQuery('.breadcrumb-stop').show();
		myBread.autoStart=true;
		myBread.processBatch();
		return false;
	});
	
	jQuery('.breadcrumb-stop').click(function () {
		jQuery('.breadcrumb-start').show();
		jQuery('.breadcrumb-stop').hide();
		jQuery('#breadcrumbForm input').prop({ disabled: false, readonly: false });
		myBread.autoStart=false;
		return false;
	});
</script>


