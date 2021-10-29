{* 7.5.3-36-gea36ae7 *}

{* JavaScript needed by the Sharing addon, done in a .tpl file so we can use things like {$classifieds_file_name} *}

{add_footer_html}
	<script type="text/javascript">

		chosenListing = '';
		chosenMethod = '';
		
		var getMethodsForListing = function (listingId) {
			
			jQuery('#share_methods_box').hide(); //hide method selection box until its new data has been populated
			jQuery('#share_options_box').hide(); //also hide the options box
			
			if(listingId == '') {
				//top (blank) option selected -- clear everything and exit
				jQuery('#share_methods').empty();
				jQuery('#share_options').empty();
				return true;
			}
			jQuery.ajax({
				url : '{$classifieds_file_name}?a=ap&addon=sharing&page=ajax&function=getMethodsForListing',
				'type' : 'POST',
				data : { listing : listingId }
			}).done (function (returned) {
				//returned.responseText is the returned value
				shareMethodsHtml = returned;
				if(shareMethodsHtml != '') {
					jQuery('#share_methods').html(shareMethodsHtml);
					jQuery('#share_methods_box').show();
					
					//if the "options" box is showing, hide it.
					//user has selected a new listing and must re-select from valid methods
					jQuery('#share_options_box').hide();
					jQuery('#share_options').empty();
					chosenListing = listingId;
				}
			});
		}

		var getOptionsForMethod = function (methodName) {
			jQuery('#share_options_box').hide(); //hide method selection box until its new data has been populated
			jQuery.ajax({
				url : '{$classifieds_file_name}?a=ap&addon=sharing&page=ajax&function=getOptionsForMethod',
				type : 'POST',
				data : {
					method : methodName,
					listing : chosenListing
				}
			}).done (function (returned) {
				//returned.responseText is the returned value
				shareOptionsHtml = returned;
				chosenMethod = methodName;
				if(shareOptionsHtml != '') {
					jQuery('#share_options').html(shareOptionsHtml);
					jQuery('#share_options_box').show();
				}
			});
		}

		jQuery (function () {
			jQuery('#options_form').submit(function (event) {
				 event.preventDefault();
				 var $this = jQuery(this);
				 var params = $this.serialize();
				 params += "&chosenMethod="+chosenMethod+"&listing="+chosenListing;
				 jQuery.ajax({
					 url : '{$classifieds_file_name}?a=ap&addon=sharing&page=ajax&function=processOptionsForm',
					 type : 'POST',
					 data : params
				 }).done(function (returned) {
					 optionsResult = returned;
					 console.log(optionsResult);
					 jQuery(document).gjLightbox('open',optionsResult);
				 });
			});
			if (jQuery('#listing_select').length) {
				jQuery('#listing_select').change(function () {
					getMethodsForListing(jQuery('#listing_select').val());
				});
			} else {
				getMethodsForListing(jQuery('#listingToShare').val());
			}
		});

	</script>

{/add_footer_html}