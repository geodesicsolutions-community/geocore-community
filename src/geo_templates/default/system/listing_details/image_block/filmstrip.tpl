{* 7.6.3-95-g5b65615 *}


<div class="filmstrip_outer">
	<div class="filmstrip_container">
		<div class="filmstrip_main">
			<div class="filmstrip_main_img">
				{foreach $images as $image}
					{if $image.icon}
						<a href="{$image.url}" onclick="window.open(this.href); return false;" class="film_big_link_{$classified_id}_{$image.id}"{if !$image@first} style="display: none;"{/if}>
							<img src="{external file=$image.icon}" alt="" />
						</a>
					{else}
						<a href="get_image.php?id={$image.id}" class="film_big_link_{$classified_id}_{$image.id} lightUpLink mobile-lightbox-disable" onclick="return false;"{if !$image@first} style="display: none;"{/if}>
							<img src="{if $image.thumb_url}{$image.thumb_url}{else}{$image.url}{/if}"{if $image.scaled.image} style="width: {$image.scaled.image.width}px;"{/if} alt="{$image.image_text}" />
						</a>
					{/if}
				{/foreach}
			</div>
			<p class="imageTitle">
				{foreach $images as $image}{$image.image_text}{break}{/foreach}
				&nbsp;<br />{$messages.500881} 1 {$messages.500882} {$image_count}
			</p>
		</div>
		<div class="clr"><br /></div>
		<div class="filmstripLeftScrollButton"></div>
		<div class="filmstripRightScrollButton"></div>
		<div class="filmstrip_strip_container">
			<div class="filmstrip_strip">
				{foreach from=$images item=image}
					<div class="filmstrip_entry">
						{if $image.icon}
							<img class="thumb" src="{external file=$image.icon}" alt="" />
						{else}
							<img class="thumb" src="{if $image.thumb_url}{$image.thumb_url}{else}{$image.url}{/if}"{if $image.scaled.thumb} style="width: {$image.scaled.thumb.width}px;"{/if} alt="{$image.image_text}" />
						{/if}
						<label style="display: none;" id="film_big_link_{$classified_id}_{$image.id}">
							{$image.image_text}
							&nbsp;<br />{$messages.500881} {$image@iteration} {$messages.500882} {$image_count}
						</label>
					</div>
				{/foreach}
				<div class="clr"></div>
			</div>
		</div>
		
	</div>
</div>