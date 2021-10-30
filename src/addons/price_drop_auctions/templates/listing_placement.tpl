{* 16.07.0-73-g60bad20 *}
<div id="price_drop_wrapper" style="display: none;">
	<h3 class="subtitle">{$msgs.listing_placement_section_header} {if $msgs.section_header_tooltip}<a href="show_help.php?addon=price_drop_auctions&amp;auth=geo_addons&amp;textName=section_header_tooltip" class="lightUpLink" onclick="return false;"><img src="{external file=$helpIcon}" alt="" /></a>{/if}</h3>
	<div class="row_even">
		<span class="field_label">{$msgs.listing_placement_activate_label}</span><input type="checkbox" name="price_drop" id="price_drop_toggle" value="1" {if $session_variables.price_drop}checked="checked"{/if} /> {$msgs.listing_placement_activate_desc}
	</div>
	<div class="row_odd" id="price_drop_min_reveal" {if !$session_variables.price_drop}style="display: none;"{/if}>
		<span class="field_label">{$msgs.listing_placement_minimum_price_label}</span> <span class="main_text precurrency">{$session_variables.precurrency}</span> <input class="field number-field" type="text" value="{$session_variables.price_drop_minimum}" name="price_drop_minimum" />
	</div>
</div>
{add_footer_html}
	<script>
		jQuery(document).ready(function() {
			//show or hide price drop section based on Buy Now Only
			
			if(jQuery('#buy_now_only').val() == 1) {
				//buy-now-only is the only option
				jQuery('#price_drop_wrapper').show();
			} else {
				jQuery('#buy_now_only').change(function() {
					if(jQuery('#buy_now_only').prop('checked')) {
						jQuery('#price_drop_wrapper').show();
					} else {
						jQuery('#price_drop_wrapper').hide();
					}
				});
				//and fire the event once to make sure we're starting in the right state
				jQuery('#buy_now_only').change();
			}
			
			jQuery('#price_drop_toggle').change(function(){
				jQuery('#price_drop_min_reveal').toggle(jQuery('#price_drop_toggle').prop('checked'));
			});
			jQuery('#price_drop_toggle').change();
			
		}); 
	</script>
{/add_footer_html}