{* 16.09.0-79-gb63e5d8 *}

{if !$insideBox && $needsDefaultCopy}
	{include file="design/parts/copyTsetWarning.tpl"}
{/if}

<div style="float: right; vertical-align: middle;{if $insideBox} margin-top: 10px;{/if}">
	Design Mode: <span class="primaryTwo">{if $advMode}Advanced{else}Standard{/if}</span>&nbsp;
	<a href="index.php?page=design_change_mode" class="lightUpLink">
		<i class="fa fa-pencil edit-pencil"></i>
	</a>  
</div>
{if !$insideBox}
	<div class="clearColumn"></div>
{/if}