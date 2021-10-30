{* 6.0.7-3-gce41f93 *}
<div>
	<div class="{cycle values='row_color1,row_color2'}">
		<div class="leftColumn">Minimum # Videos Required</div>
		<div class="rightColumn">
			<input type="text" name="offsite_videos[minVideos]" value="{$minVideos}" size="4" />
		</div>
		<div class="clearColumn"></div>
	</div>
	
	<div class="{cycle values='row_color1,row_color2'}">
		<div class="leftColumn">Maximum # Videos Allowed</div>
		<div class="rightColumn">
			<input type="text" name="offsite_videos[maxVideos]" value="{$maxVideos}" size="4" />
		</div>
		<div class="clearColumn"></div>
	</div>
	{if $is_ent}
		<div class="{cycle values='row_color1,row_color2'}">
			<div class="leftColumn"># Free Videos</div>
			<div class="rightColumn">
				<input type="text" name="offsite_videos[freeVideos]" value="{$freeVideos}" size="4" />
			</div>
			<div class="clearColumn"></div>
		</div>
	{/if}
	<div class="{cycle values='row_color1,row_color2'}">
		<div class="leftColumn">Cost per video</div>
		<div class="rightColumn">
			{$precurrency}<input type="text" name="offsite_videos[costPerVideo]" value="{$costPerVideo|displayPrice:'':''}" size="11" />{$postcurrency}
		</div>
		<div class="clearColumn"></div>
	</div>
</div>