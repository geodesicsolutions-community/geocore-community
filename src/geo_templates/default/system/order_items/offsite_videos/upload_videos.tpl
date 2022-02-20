{* 7.4.4-41-ga8d7c9f *}
{if !$in_ajax}
	<h2 class="title">
		{$offsite_videos.section_title}
		{if $messages.500916 && $offsite_videos.description}
			<a href="#" class="show_instructions_button" id="offsite_video_instructions">{$messages.500916}</a>
		{/if}
	</h2>
	<div id="offsite_video_instructions_box"><p class="page_note">{$offsite_videos.description}</p></div>
	{if $error_msgs.offsite_videos}
		<div class="field_error_box">
			{$error_msgs.offsite_videos}
		</div>
	{/if}
	<div class="clr"></div>
{/if}
{if !$in_ajax}<div id="offsite_videos_outer" class="clearfix">{/if}
	{foreach from=$offsite_videos.slots item='slot' key='slotNum' name='offsite_video_slots'}
		<div id="offsite_video_slot_{$slotNum}" class="offsite_video_slot">
			{include file='system/order_items/offsite_videos/upload_slot.tpl'}
		</div>
	{/foreach}
	<br />
{if !$in_ajax}</div>{/if}

{if $steps_combined&&$is_ajax_combined}
	{* Loaded as part of combined steps, need to 're-initialize' stuff... *}
	<script type="text/javascript">
		//<![CDATA[
			//initialize external video js
			geoVidProcess.currentSlot = {$currentSlot};
			geoVidProcess.init();
		//]]>
	</script>
{/if}
