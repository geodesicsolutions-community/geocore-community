{* 4eb7314 *}

{*  This is the tree to use if the number of generations is more than 4, as it
	will work for an unlimited number of generations. *}


{if $currentGen == 1}
	{assign var=width value=100}
	{if $gender==sire}
		{assign var=origData value=$data}
	{/if}
	{assign var=data value=$data.$gender}
	{if $data.maxGen}{assign var=maxGen value=$data.maxGen}{/if}
{else}
	{assign var=depth value=$maxGen-$currentGen+1}
	{assign var=under value=$depth+1}
	{assign var=width value=$depth/$under}
	{assign var=width value=$width*100}
{/if}

<div class="{$gender}" style="width: {$width}%;{if $currentGen == 1} min-width: 200px; border: 1px solid black;{/if}">
	{if $currentGen < $maxGen}
		{include file="listing_details/tree_unlimited.tpl" currentGen=$currentGen+1 gender="sire" notTop=1 data=$data.sire}
		{include file="listing_details/tree_unlimited.tpl" currentGen=$currentGen+1 gender="dam" notTop=1 data=$data.dam}
	{/if}
	<div class="horseData{if $currentGen != 0} {$gender}_{/if}">
		{if $gender==dam&&$icon_dam}
			<img src="{external file=$icon_dam}" alt="" />
		{elseif $gender==sire&&$icon_sire}
			<img src="{external file=$icon_sire}" alt="" />
		{/if}
		{$data.name|capitalize}
	</div>
</div>
{if $currentGen==1 && $gender==sire}
	{include file="listing_details/tree_unlimited.tpl" gender="dam" notTop=1 data=$origData}
{/if}

{if !$notTop}
<div class="clear"><br /></div>
{/if}
