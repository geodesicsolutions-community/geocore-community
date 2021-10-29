{* 7.0.3-298-g9dc5675 *}
<div id="largeImageBlock_{$listing_id}">
	{foreach $images as $image}
		<div class="largeImageContainer">
			<div class="largeImageImage">
				{if !$image.icon}
					<img src="{$image.image_url}" alt="{$image.image_text}"{if $image.scaled.full} style="width: {$image.scaled.full.width}px; height: {$image.scaled.full.height}px;"{/if} />
				{else}
					<a href="{$image.image_url}"><img src="{$image.icon}" alt="{$image.image_text}" /></a>
				{/if}
			</div>
			<div class="largeImageTitle">
				{$image.image_text}{if !$image.image_text}&nbsp;{/if}
			</div>
		</div>
	{/foreach}
</div>