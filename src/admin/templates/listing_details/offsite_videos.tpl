{* 6.0.7-3-gce41f93 *}

<div class="col_hdr_top">Listing Videos</div>
<br /><br />
<div class="center">
{foreach from=$offsite_videos item='video'}
	<div style="display: inline-block; padding: 10px; border: 1px solid #666666; margin: 5px;">
		<p class="page_note">
			<strong>Slot:</strong> {$video.slot}<br />
			<strong>Youtube ID:</strong> {$video.video_id}
		</p>
		<object width="480" height="390">
			<param name="movie" value="{$video.media_content_url}"></param>
			<embed src="{$video.media_content_url}" type="{$video.media_content_type}" width="480" height="390"></embed>
		</object>
	</div>
{/foreach}
</div>

<div class="clr"></div>
