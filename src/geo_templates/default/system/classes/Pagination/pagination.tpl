{* 16.02.1-31-ga7044db *}
<ul class="pagination{if $css} {$css}{/if}">
	{if $previousPage}
		<li>
			{if $moduleTag}
				<a href="#" onclick="LoadModulePage_{$moduleTag}({$previousPage}); return false;">
			{else}
				<a href="{$url}{$previousPage}{$postUrl}">
			{/if}
			{if $skip_glyphs}&lsaquo;{else}<span class="glyphicon glyphicon-chevron-left"></span>{/if}
			</a>
		</li>
	{elseif $moduleTag}
		{* always show "previous" button for module navigation, so it doesn't "jump around" *}
		<li>
			<a href="#" onclick="return false;" style="opacity:0.5; cursor: default;">{if $skip_glyphs}&lsaquo;{else}<span class="glyphicon glyphicon-chevron-left"></span>{/if}</a>
		</li>
	{/if}
	
	{foreach from=$links item=page}
		{if $page == $currentPage}
			<li class="current">{$page}</li>
		{else}
			<li>
				{if $moduleTag}
					{* Note: LoadModulePage_{tag} is defined in module/common/browsing.tpl *}
					<a href="#" onclick="LoadModulePage_{$moduleTag}({$page}); return false;">
				{else}
					<a href="{$url}{$page}{$postUrl}">
				{/if}
				{$page}
				</a>
			</li>
		{/if}
	{/foreach}
	
	{if $nextPage}
		<li>
			{if $moduleTag}
				<a href="#" onclick="LoadModulePage_{$moduleTag}({$nextPage}); return false;">
			{else}
				<a href="{$url}{$nextPage}{$postUrl}">
			{/if}
			{if $skip_glyphs}&rsaquo;{else}<span class="glyphicon glyphicon-chevron-right"></span>{/if}
			</a>
		</li>
	{elseif $moduleTag}
		{* always show "next" button for module navigation, so it doesn't "jump around" *}
		<li>
			<a href="#" onclick="return false;" style="opacity:0.5; cursor: default;">{if $skip_glyphs}&rsaquo;{else}<span class="glyphicon glyphicon-chevron-right"></span>{/if}</a>
		</li>
	{/if}
</ul>
<div class="clr"></div>