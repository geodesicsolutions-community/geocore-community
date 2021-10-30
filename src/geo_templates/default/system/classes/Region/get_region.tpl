{* 6.0.7-3-gce41f93 *}
{if is_array($one)}
	{$one.name}<input type="hidden" name="{$fieldName}" id="{$fieldName}" value="{$one.value}" />
{else}
	<select name="{$fieldName}" id="{$fieldName}" class="field" style="width: 136px;">
		{foreach from=$options item=opt}
			<option value="{$opt.value}"{if $opt.selected} selected="selected"{/if}>{$opt.label}</option>
		{/foreach}
	</select>
	
	{if $error}<span class="error_message">{$error}</span>{/if}
{/if}