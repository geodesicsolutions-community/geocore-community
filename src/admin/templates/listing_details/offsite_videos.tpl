<div class="col_hdr_top">Listing Videos</div>
<br /><br />
<div class="center">
{foreach from=$offsite_videos item='video'}
	<div style="display: inline-block; padding: 10px; border: 1px solid #666666; margin: 5px;">
		<p class="page_note">
			<strong>Slot:</strong> {$video.slot}<br />
			<strong>Youtube ID:</strong> {$video.video_id}
		</p>
        <iframe src="https://www.youtube.com/embed/{$video.media_content_url|escape}" width="240" title="YouTube video player" frameborder="0"
        allow="encrypted-media"></iframe>
	</div>
{/foreach}
</div>

<div class="clr"></div>
