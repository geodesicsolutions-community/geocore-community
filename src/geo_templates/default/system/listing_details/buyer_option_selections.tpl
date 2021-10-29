{* 7.4.6-10-g3d46c25 *}
{foreach $groups as $group}
	<div style="display: inline-block;">
		<strong>{$group.label|fromDB}</strong><br />
		
		<div class="buyer-option-image-box"></div>
		<select name="cost_options[{$group.id}]" id="cost-option_{$group.id}" class="buyer_options_selection{$listing_id}{if $group.quantity_type=='combined'} costOptionCombined{$listing_id}{/if}">
			{if $group.quantity_type=='combined'}
				<option value="0">-</option>
			{/if}
			{foreach $group.options as $option}
				<option data-add-cost="{$option.cost_added}" data-file-slot="{$option.file_slot}" value="{$option.id}"{if $group.quantity_type=='individual' && $option.ind_quantity_remaining<1} disabled="disabled"{/if}>{strip}
					{$option.label|fromDB}
					{if $option.cost_added!=0}
						&nbsp;[{if $option.cost_added>0}+{/if}
						{$option.cost_added|displayPrice:$precurrency:$postcurrency}]
					{/if}
					{if $group.quantity_type=='individual' && $option.ind_quantity_remaining<1}{$messages.502277|escape}{/if}
				{/strip}</option>
			{/foreach}
		</select>
	</div>
{/foreach}

{add_footer_html}
	<script>
	jQuery(function () {
		var buyer_options{$listing_id} = { };
		
		buyer_options{$listing_id}.out_of_stock = '{$messages.502277|escape_js}';
		
		{if $hasCombined}
			buyer_options{$listing_id}.combined = {$combined_json};
			
			buyer_options{$listing_id}.updateCombined = function () {
				jQuery('.costOptionCombined{$listing_id}').each(function () {
					var values = [];
					//get the "other" values...
					var thisId = jQuery(this).attr('id');
					
					jQuery('.costOptionCombined{$listing_id}:not(#'+thisId+')').each(function () {
						if (jQuery(this).val()>0) {
							values[values.length] = jQuery(this).val();
						}
					});
					
					//the value before we do this.. we'll try to set it to this if possible
					var preValue = jQuery(this).val();
					//disable all options
					jQuery(this).find('option:not([value='+preValue+'])').prop({ disabled: true }).each (function () {
						//add "out of stock" text to each option
						if (jQuery(this).val()==0) {
							//don't add it to this one
							return;
						}
						if (!jQuery(this).data('originalLabel')) {
							jQuery(this).data('originalLabel',jQuery(this).text());
						}
						jQuery(this).text(jQuery(this).data('originalLabel')+buyer_options{$listing_id}.out_of_stock);
					});
					if (preValue!='0') {
						//enable 0 value
						jQuery(this).find('option[value=0]').prop({ disabled:false });
					}
					//now enable the options that are good...
					for (var a=0; a<buyer_options{$listing_id}.combined.length; a++) {
						//loop through the combined options...  Find the ones that the "other" values
						//match the entries in there
						
						var thisOptions = buyer_options{$listing_id}.combined[a].options;
						//loop through the "other" options, see if this has those in it
						var isGood = true;
						for (var b=0; b<values.length && isGood; b++) {
							if (thisOptions.indexOf(values[b]) == -1) {
								//not good
								isGood = false;
							}
						}
						if (isGood) {
							//enable the options in this one
							for (var c=0; c<thisOptions.length; c++) {
								jQuery(this).find('option[value='+thisOptions[c]+']').prop({ disabled:false}).each(function () {
									if (jQuery(this).data('originalLabel')) {
										//remove the "out of stock" message
										jQuery(this).text(jQuery(this).data('originalLabel'));
									}
								});
							}
						}
					}
				});
			};
		{/if}
		buyer_options{$listing_id}.updateBuyNowCost = function () {
			if (!jQuery('#listing-buy-now-price-{$listing_id}').length) {
				return;
			}
			//update the buy now price displayed
			var cost = jQuery('#listing-buy-now-price-{$listing_id}').data('baseCost');
			
			if (typeof cost === 'number') {
				jQuery('.buyer_options_selection{$listing_id}').each(function () {
					var currentOption = jQuery(this).find('option:selected');
					if (currentOption.val()>0 && currentOption.data('addCost')) {
						var addCost = currentOption.data('addCost');
						if (typeof addCost === 'number') {
							//safe to add them up, they are both numbers
							cost = cost+addCost;
						} else {
							//one of the values not a number, not able to "safely" update cost
							return;
						}
					}
				});
				jQuery('#listing-buy-now-price-{$listing_id}').text(cost);
			}
		};
		buyer_options{$listing_id}.updateBuyNowUrl = function () {
			//get the option values (that are actually set)
			var formInfo = jQuery('.buyer_options_selection{$listing_id}').not(function () {
				return (jQuery(this).val()==0);
			});
			formInfo = (formInfo.length)? '&'+formInfo.serialize() : '';
			
			//send the selections to the buy now button
			jQuery('a.buy_now_link').each(function () {
				//see if URL matches this URL in case being used on browsing page
				if (!jQuery(this).data('baseHref')) {
					//keep track of "original" URL
					jQuery(this).data('baseHref',jQuery(this).attr('href'));
				}
				var baseHref = jQuery(this).data('baseHref');
				if (baseHref.indexOf("b={$listing_id}&")>-1) {
					jQuery(this).attr('href', baseHref+formInfo);
				} else if (baseHref.indexOf("%2Aand%2Ab%2Ais%2A{$listing_id}%2A")>-1) {
					//right link, but sending info to login so have to do things different
					jQuery(this).attr('href', baseHref+formInfo.replace('&','%2Aand%2A').replace('=','%2Ais%2A'));
				}
			});
		};
		buyer_options{$listing_id}.updateFileSlot = function () {
			jQuery('.buyer_options_selection{$listing_id}').each(function () {
				var fileSlot = jQuery(this).find('option:selected').data('fileSlot');
				var imageBox = jQuery(this).prev('.buyer-option-image-box');
				if (fileSlot) {
					var image = jQuery('.galleryThumbs li:nth-child('+fileSlot+') img');
				}
				
				if (!imageBox.length) {
					//nothing can be done young one...
					return;
				}
				
				if (fileSlot && image.length) {
					imageBox.html(image.clone().width(50))
						.show('fast');
				} else {
					imageBox.hide();
				}
			});
		};
		buyer_options{$listing_id}.optionChanged = function () {
			buyer_options{$listing_id}.updateBuyNowCost();
			{if $hasCombined}
				if (jQuery(this).hasClass('costOptionCombined{$listing_id}')) {
					buyer_options{$listing_id}.updateCombined();
				}
			{/if}
			buyer_options{$listing_id}.updateBuyNowUrl();
			buyer_options{$listing_id}.updateFileSlot();
		};
		{if $hasCombined}
			//initialize the combined quantities
			buyer_options{$listing_id}.updateCombined();
		{/if}
		//go ahead and adjust the cost & link starting out
		buyer_options{$listing_id}.updateBuyNowCost();
		buyer_options{$listing_id}.updateBuyNowUrl();
		jQuery('.buyer-option-image-box').hide();
		buyer_options{$listing_id}.updateFileSlot();
		
		jQuery('.buyer_options_selection{$listing_id}').change(buyer_options{$listing_id}.optionChanged);
	});
	</script>
{/add_footer_html}