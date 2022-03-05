<div{if !$slot.empty} class="offsite_video_is_sortable"{/if}>
	<div class="offsite_video_box_title_buttons"{if $slot.empty} style="display: none;"{/if}>
		<span class="delete_offsite_video" id="deleteYoutube_{$slotNum}">
			{if $in_admin}
				<img src="../{external file='images/buttons/delete.png'}" alt="Delete Video" />
			{else}
				{$messages.500918}
			{/if}
		</span>
	</div>

	<div class="offsite_video_box_title{if $slot.editing}_editing{elseif $slot.empty}_empty{/if}">
		{if $slot.editing}
			{$messages.500919}
		{elseif !$slot.empty}
			{$messages.500920}
		{else}
			{$messages.500921}
		{/if}
	</div>

	<div class="preview_offsite_video_box">
		{if $slot.error}
			<div class="error">{$slot.error}</div>
		{/if}
		{if !$slot.empty}

            <iframe src="https://www.youtube.com/embed/{$slot.media_content_url|escape}"
            width="240" title="YouTube video player" frameborder="0" allow="encrypted-media;"></iframe>

		{/if}
	</div>

	<span class="offsite_video_slot_label">{$messages.500922}</span> <span class="offsite_video_slot_value">{$slotNum}</span><br />
	{if $slot.cost}
		<span class="offsite_video_cost_label">{$messages.500923}</span> <span class="offsite_video_cost_value">{$slot.cost}</span><br />
	{/if}
	<span class="offsite_video_id_label{if $slot.required} required{/if}">{$messages.500924}{if $slot.required} *{/if}</span><br />
	<input type="text" name="offsite_video_slots[{$slotNum}]" class="field offsite_video_id_input" value="{$slot.video_id}" /><br />
	<div class="offsite_video_loading_container" style="display: none;">
		<img src="{if $in_admin}../{/if}{external file='images/loading.gif'}" alt="Loading..." />
	</div>
	<div class="offsite_video_action_buttons" id="offsite_videoButtons_{$slotNum}"></div>
</div>
