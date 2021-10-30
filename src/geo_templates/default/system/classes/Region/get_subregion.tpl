{* 6.0.7-3-gce41f93 *}
<select name="{$stateName}" class="field" id="{$stateName}">
	<option value="none">{$noneLabel}</option>
	{foreach from=$states item=state}
		{if $fancy}
			{if $parent != $state.parent_id}
				<optgroup label="{$state.label}">
				{if $opt_group}
					</optgroup>
				{/if}
				{assign var='opt_group' value=true}
				{assign var='parent' value=$state.parent_id}
			{/if}
		{/if} 
		
		<option value="{$state.value}"{if $state.selected} selected="selected"{/if}>{$state.text}</option>
	{/foreach}
	
	{if $opt_group}</optgroup>{/if}
</select>