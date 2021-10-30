{* 7.6.3-149-g881827a *}

{if !$notTop}
	<div class="{cycle values='row_odd,row_even'}">
{/if}
{if $currentGen == 1}
	{assign var=width value=100}
	{if $gender==sire}
		{assign var=origFieldName value=$fieldName}
		{assign var=origData value=$data}
	{/if}
	{assign var=data value=$data.$gender}
{else}
	{assign var=depth value=$maxGen-$currentGen+1}
	{assign var=under value=$depth+1}
	{assign var=width value=$depth/$under}
	{assign var=width value=$width*100}
{/if}
{capture assign=fieldName}{$fieldName}[{$gender}]{/capture}

<div class="{$gender}" style="width: {$width}%;{if $currentGen == 1} min-width: 200px; border: 1px solid black;{/if}">
	{if $currentGen < $maxGen}
		{include file="listing_placement/tree_unlimited.tpl" currentGen=$currentGen+1 gender="sire" notTop=1 data=$data.sire}
		{include file="listing_placement/tree_unlimited.tpl" currentGen=$currentGen+1 gender="dam" notTop=1 data=$data.dam}
	{/if}
	<div class="horseData{if $currentGen != 0} {$gender}_{/if}">
		{if $gender==dam&&$icon_dam}
			<img src="{external file=$icon_dam}" alt="" />
		{elseif $gender==sire&&$icon_sire}
			<img src="{external file=$icon_sire}" alt="" />
		{/if}
		<input type="text" name="{$fieldName}[name]" size="14" value="{$data.name|escape|capitalize}" />
		{if $errors.$fieldName}<span class="error_message">{$errors.$fieldName}</span>{/if}
	</div>
</div>
{if $currentGen==1 && $gender==sire}
	{include file="listing_placement/tree_unlimited.tpl" gender="dam" notTop=1 fieldName=$origFieldName data=$origData}
{/if}

{if !$notTop}<div class="clear"></div></div>{/if}
