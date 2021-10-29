{* 7.6.3-95-g5b65615 *}
<table style="border: none; width: 100%;">
	{foreach $images as $image}
		{if $image@index is div by $columns}<tr>{/if}
		
		<td style="text-align: center; vertical-align: top; width: {$width_percentage};">
			{if $image.icon}
				<a href="{$image.url}" onclick="window.open(this.href); return false;">
					<img src="{external file=$image.icon}" alt="" />
				</a>
				
				{if $image.image_text && $ad_configuration_data.maximum_image_description}
					<br />
					<span class="zoom_link">{$image.image_text|truncate:$ad_configuration_data.maximum_image_description}</span>
				{/if}
			{else}
				{if $image.scaled.image.width!=$image.original_image_width}
					{if $image_link_destination_type}
						<a href="{$classifieds_file_name}?a=15&amp;b={$image.classified_id}" class="zoom_link">
					{else}
						<a href="get_image.php?id={$image.id}" class="lightUpLink mobile-lightbox-disable" onclick="return false;">
					{/if}
				{/if}
					<img src="{if $image.thumb_url}{$image.thumb_url}{else}{$image.url}{/if}"{if $image.scaled.image} style="width: {$image.scaled.image.width}px;"{/if} alt="{$image.image_text|truncate:$ad_configuration_data.maximum_image_description}" />
					
					{if $image.image_text && $ad_configuration_data.maximum_image_description}
						<br />
						<span class="zoom_link">{$image.image_text|truncate:$ad_configuration_data.maximum_image_description}</span>
					{/if}
				
				{if $image.scaled.image.width!=$image.original_image_width}
					<br />
					<span class="zoom_link">{$messages.339}</span>
					<span class="zoom_link">{$messages.12}</span>
					</a>
				{/if}
			{/if}
		</td>
		
		{if $image@last || $image@iteration is div by $columns}</tr>{/if}
	{/foreach} 
</table>