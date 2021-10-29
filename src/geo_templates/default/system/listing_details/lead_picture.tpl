{* 7.3.1-55-g8f5596d *}
{if $image.icon}
	<img src="{external file=$image.icon}" alt="" />
{else}
	<img src="{if $image.url}{$image.url}{elseif $image.thumb_url}{$image.thumb_url}{/if}" alt=""{if $image.scaled.lead} style="width: {$image.scaled.lead.width}px;"{/if} />
{/if}