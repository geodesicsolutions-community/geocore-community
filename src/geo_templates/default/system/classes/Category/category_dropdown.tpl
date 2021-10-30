{* 6.0.7-3-gce41f93 *}
<select name="{$name}" class="field"{if $id} id="{$id}"{/if}>
	{foreach from=$options item=opt}
 	<option value="{$opt.value}"{if $opt.selected} selected="selected"{/if}>{$opt.label}</option>
 	{/foreach}
 </select>
