{* 7.6.3-149-g881827a *}
{if $main_type == 'listing_edit'}
	{* Template designers: if you want to use a totally different template file for edit
	   listings, you would surround it with these if smarty tags.  The same goes for classified,
	   auction, and reverse_auction, just change 'listing_edit' as appropriate in the if stmt *}
	
{/if}

<h2 class="title">
	{$images.section_title}
	{if $images.description && $messages.500916}
		<a href="#" class="show_instructions_button" id="image_upload_instructions">{$messages.500916}</a>
	{/if}
</h2>
<p id="image_upload_instructions_box" class="page_note">{$images.description}</p>

<div id="imagesContainer" class="media-container">
	<div id="imagesFilelist" class="media-upload-dropbox clearfix">
		{* This next div used to capture "styles" to use for progress circle
			- leave this intact *}
		<div class="media-progress-circle" id="imagesProgressBarCss"></div>
		{$messages.502148} <strong id="imagesCurrentCount">{$images.current_count}</strong> {$messages.502149} <strong id="imagesMaxCount">{$images.max}</strong> {$messages.502150}
		<span id="imagePriceInfo">
			{if $images.pricing}
				<br /><strong class="price">{$images.pricing.cost_per_image|displayPrice}</strong> {$messages.502151}
				{if $images.pricing.number_free_images>0}
					({$images.pricing.number_free_images} {$messages.502152})
				{/if}
			{/if}
		</span>
		<br />
		<a id="imagesPickfiles" href="#select-files" class="button media-select-files" onclick="return false;">{$messages.502153}</a>
	</div>
</div>
<div id="imagesUploaded" class="media-preview-container clearfix">
	{include file='images/preview.tpl'}
</div>
{if $steps_combined&&$is_ajax_combined}
	{* Loaded as part of combined steps, need to 're-initialize' stuff... *}
	<script type="text/javascript">
		//<![CDATA[
			//initialize external video js
			gjUtil.imageUpload._maxImages = {$images.max};
			//pass in true to skip the stuff that doesn't need to happen again
			gjUtil.imageUpload.init(true);
		//]]>
	</script>
{/if}