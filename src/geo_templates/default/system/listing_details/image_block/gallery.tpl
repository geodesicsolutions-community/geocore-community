{* 7.6.3-95-g5b65615 *}


<div class="galleryContainer">
	<div class="galleryBigImage">
		<div class="bigLeadImage">
			{foreach $images as $image}
				{if $image.icon}
					<a href="{$image.url}" onclick="window.open(this.href); return false;" class="big_link_{$classified_id}_{$image.id}"{if !$image@first} style="display: none;"{/if}>
						<img src="{external file=$image.icon}" alt="" />
					</a>
				{else}
					<a href="get_image.php?id={$image.id}" class="big_link_{$classified_id}_{$image.id} lightUpLink mobile-lightbox-disable" onclick="return false;"{if !$image@first} style="display: none;"{/if}>
						<img src="{if $image.thumb_url}{$image.thumb_url}{else}{$image.url}{/if}"{if $image.scaled.image.width} style="width: {$image.scaled.image.width}px;"{/if} alt="{$image.image_text}" />
					</a>
				{/if}
			{/foreach}
		</div>
		{if $image.image_text && $ad_configuration_data.maximum_image_description}
			<p class="imageTitle">{$images.1.image_text|truncate:$ad_configuration_data.maximum_image_description}</p>
		{/if}
	</div>
	{if $image_count>1}
	<div class="galleryThumbs">
		<ul>
			{foreach $images as $image}
				<li>
					{if $image.icon}
						<img class="thumb" src="{external file=$image.icon}" alt="" />
					{else}
						<img class="thumb" src="{if $image.thumb_url}{$image.thumb_url}{else}{$image.url}{/if}"{if $image.scaled.thumb} style="width: {$image.scaled.thumb.width}px;"{/if} alt="{$image.image_text}" />
					{/if}
					<label style="display: none;" id="big_link_{$classified_id}_{$image.id}">{$image.image_text}</label>
				</li>
			{/foreach}
			
		</ul>
	</div>
	{/if}
	
	<div class="clr"></div>
</div>