{* 7.6.3-93-g8353c8d *}
<div class="lightUpBox_navigation">
	{if $previous_image_id}
		<a href="get_image.php?id={$previous_image_id}" class='lightUpLink' onclick="return false;">{$messages.2411}</a>
	{else}
		<span class="disabledLink">{$messages.2411}</span>
	{/if}
	{if $useSlideshow}
		{*
			NOTE: The order of these is important, must be playLink then pauseLink,
			if the order is changed it will break the javascript that shows/hides
			the play and pause buttons.
			
			The in-line display: none; is needed to start out with those buttons hidden,
			and must be left in-line, if they were in a CSS class then the JS
			used to show/hide the links would not work.  (see documentation on Prototype's
			show() at http://www.prototypejs.org/api/element/show )
		*}{strip}
		<a href="#play" class="playLink" style="display: none;" onclick="return false;">{$messages.500760}</a>
		<a href="#pause" class="pauseLink" style="display: none;" onclick="return false;">{$messages.500761}</a>
		<span class="disabledLink noplayLink">{$messages.500760}</span>{/strip}
	{/if}
	{if $next_image_id}
		<a href="get_image.php?id={$next_image_id}" class='lightUpLink' onclick="return false;">{$messages.2412}</a>
	{else}
		<span class="disabledLink">{$messages.2412}</span>
	{/if}
	<a href="#close" class="closeLightUpBox"><img alt="" src="{external file='images/buttons/btn_pop_close.gif'}" /></a>
	{if $messages.500762 || $messages.500763}
		{* Only display if one of the message entries are not blank. *}
		<br /><span class="lightUpBox_imageCountText">{$messages.500762} {$imageNum} {$messages.500763} {$imageCount}</span>
	{/if}
</div>
<div class="lightUpBox_imageBox" style="width: {$maxWidth}px; max-width: 100%;">
	{if $is_icon}<a href="{$url}" class="lightUpBox_link">{/if}
		{$display_image}
	{if $is_icon}</a>{/if}
</div>
<div class="lightUpBox_description medium_font">
	{if $display_image_text}
		{$display_image_text}
	{else}
		<br />
	{/if}
</div>
{if $useSlideshow}
	<script type="text/javascript">
		//set JS value for next image ID
		gjUtil.lightbox.setNextImgId({if $first_image_id}{$first_image_id}{else}{$next_image_id}{/if});
		{if $playing && $startSlideshow}jQuery(document).gjLightbox('startSlideshow');{/if}
	</script>
{/if}

