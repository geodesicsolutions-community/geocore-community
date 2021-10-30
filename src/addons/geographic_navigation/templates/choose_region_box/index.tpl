{* 7.6.3-149-g881827a *}

<strong>{$msgs.selectLocationTitle}</strong>
<br />
{$lastSelected=0}
{$link=0}
{foreach $levels as $level}
	<div class="narrowRegionLevel{if $level.count == 1 && !$level.emptyHidden} onlyRegionOnLevel{/if}" style="float: left;">
		{if count($level.regions)<$dropdownThreshold}
			<ul class="narrowRegionSelect">
				{foreach $level.regions as $region}
					{if $currentRegionId==$region.id}{$link=$region.link}{/if}
					<li>
						<span class="narrowRegionLink{if $level.selected&&$region.id==$level.selected} selectedRegion{/if}">
							{$region.label}
							{if !$level@last&&$level.selected&&$region.id==$level.selected}&gt;{/if}
						</span>
						<input type="hidden" value="{$region.id}" />
						<input type="hidden" value="{$region.link|escape}" />
					</li>
				{/foreach}
			</ul>
		{else}
			<select class="narrowRegionSelect">
				<option value="{$lastSelected}"></option>
				{foreach $level.regions as $region}
					{if $currentRegionId==$region.id}{$link=$region.link}{/if}
					<option value="{$region.id}"{if $level.selected&&$region.id==$level.selected} selected="selected"{/if}>{$region.label}</option>
				{/foreach}
			</select>
		{/if}
	</div>
	{$lastSelected=$level.selected}
{/foreach}
<div class="clear"><br /></div>
<div class="center">
	{if $link}
		<a href="{$link}" class="mini_button">{$msgs.selectButton}</a>
	{/if}
	{if $currentRegionId}
		<a href="{$resetLink}" class="mini_cancel">{$msgs.clearSelectionButton}</a>
	{/if}
	<input type="submit" value="{$msgs.cancelButton}" class="mini_cancel chooseNavCancel" />
	
</div>
