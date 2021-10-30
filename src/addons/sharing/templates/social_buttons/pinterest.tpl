{* 17.10.0-10-g338411a *}
{* Pinterest "Pin it" Button
	See http://business.pinterest.com/widget-builder/#builder for options
	--NOTE: Requires at least one image. *}
{if $lead_image && $activeMethods.pinterest == 1}
		<a data-pin-config="none" data-pin-do="buttonPin" href="//www.pinterest.com/pin/create/button/?url={$listing_url}&amp;media={$lead_image|escape:'url'}&amp;description={$listing_data.description|truncate:450|escape:'url'}{if $price}%20{$price|escape:'url'}{/if}"><img src="//assets.pinterest.com/images/pidgets/pin_it_button.png" alt="" /></a>
		{add_footer_html}<script type="text/javascript" src="//assets.pinterest.com/js/pinit.js"></script>{/add_footer_html}
{/if}