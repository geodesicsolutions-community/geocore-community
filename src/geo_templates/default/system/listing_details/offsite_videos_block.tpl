
<div class="offsite_videos_container">
	{foreach from=$offsite_videos item='video'}
		{if $video.video_type=='youtube'}
			<div class="embed-container">
        		<iframe src='//www.youtube.com/embed/{$video.video_id|escape}?wmode=transparent'
                frameborder="0" title="YouTube video player"
                allow="fullscreen; accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"></iframe>
			</div>
		{/if}
		{* If any more video types are ever added, they would be added here. *}
	{/foreach}
</div>
