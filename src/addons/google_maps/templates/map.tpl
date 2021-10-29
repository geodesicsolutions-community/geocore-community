{* 7.5.3-36-gea36ae7 *}

<div style='position:relative; padding: 8px;'>
	{if $msgs.map_label}
		<div class="googleMapsLabel">
			{$msgs.map_label}
		</div>
	{/if}
	<div id='map_canvas{$listing_id|escape}' class="map-container"></div>
</div>
{add_footer_html}
<script type='text/javascript'>
//<![CDATA[
	jQuery(function () {
  		addon_google_maps.init({$coords}, '{$location|escape_js}', 'map_canvas{$listing_id|escape_js}');
	});
//]]>
</script>
{/add_footer_html}