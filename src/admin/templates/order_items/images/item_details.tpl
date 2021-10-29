{* 6.0.7-3-gce41f93 *}
<div class="{$current_color}">
	<div class="leftColumn">
		Images
	</div>
	<div class="rightColumn">
		<div style="border: 2px solid #88AACC; padding: 15px;">
{foreach from=$images item="image" key="id"}
			<div style="float:left; border: 1px solid #88AACC; margin: 5px;">
				<a href="{$image.full}" target="_blank">
					<img src="{$image.thumb}" alt="{$image.caption}"
						style="{if $image.width}width: {$image.width}px;{/if}
						{if $image.height}height: {$image.height}px;{/if}" />
	{if $image.caption}
					<br />{$image.caption}
	{/if}<br />Slot: {$image.slot}
				</a>
			</div>
{foreachelse}
No Attached Images.
{/foreach}
			<div class="clearColumn"></div>
		</div>
	</div>
	<div class="clearColumn"></div>
</div>