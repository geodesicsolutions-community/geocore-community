{* 6.0.7-3-gce41f93 *}

<div class="{$current_color}">
	<div class="leftColumn">
		Videos
	</div>
	<div class="rightColumn">
		<div class="offsite_videos_container">
			{foreach from=$videos item=video key=slot}
				<div class="offsite_video">
					<object width="480" height="390">
						<param name="movie" value="{$video.media_content_url}"></param>
						<embed src="{$video.media_content_url}" type="{$video.media_content_type}" width="480" height="390"></embed>
					</object>
					<br />
					Video Slot # {$slot}
				</div>
			{/foreach}
		</div>
	</div>
	<div class="clearColumn"></div>
</div>