{* 7.5.3-28-ga8a8eb4 *}
{foreach $images.images_data as $image}
	<div class="media-preview clearfix" id="imagesPreview_{$image.image_id}">
		<div class="media-editable-saved"><img src="{if $in_admin}../{/if}{external file='images/saved-check.png'}" alt="" /></div>
		{if $image.isIcon != 1 && ($messages.502295 || $in_admin)}
			<a href="#{$image.image_id}" class="rotateImage media-rotate">
				{if $in_admin}
					<img src="../{external file='images/buttons/rotate-cw.png'}" alt="Rotate Image" />
				{else}
					{$messages.502295}
				{/if}
			</a>
		{/if}
		{if !$skipDelete}
			<a href="#{$image.image_id}" class="deleteImage media-delete">
				{if $in_admin}
					<img src="../{external file='images/buttons/delete.png'}" alt="Delete Image" />
				{else}
					{$messages.500715}
				{/if}
			</a>
		{/if}
		<strong class="slot-label">{$messages.500698}</strong>
		<input type="number" class="media-editable-sorting editImageSort" value="{$image.display_order|escape}" size="2" min="1" max="{$images.max}" />
		<br />
		<div class="media-preview-image">
			{if $image.resized or $image.isIcon}<a href="{if $in_admin}../{/if}{$image.image_url}" class="lightUpImg">{/if}
				{$image.tag}
			{if $image.resized or $image.isIcon}</a>{/if}
		</div>
		{if $images.maximum_image_description>0}
			<input type="hidden" value="{$image.image_text|escape}" />
			<div class="media-editable-label editImgageTitle" contenteditable="true" title="Click to Edit">{$image.image_text}</div>
		{/if}
		{if $image.isIcon && $messages.502154}
			{* Download link for files *}
			<a href="{if $in_admin}../{/if}{$image.image_url}" onclick="window.open(this.href); return false" class="button">{$messages.502154}</a>
		{/if}
		{if $image.cost}
			<strong>{$messages.500702}</strong> <span class="price">{$image.cost}</span>
		{/if}
	</div>
{/foreach}