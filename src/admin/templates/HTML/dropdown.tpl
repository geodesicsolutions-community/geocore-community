{* 6.0.7-3-gce41f93 *}
<select{if $style} {$style}{/if}{if $id} id="{$id}" name="{$id}"{/if}>
	{foreach from=$options item=label key=value}
		<option value="{$value}"{if $selected == $value} selected="selected"{/if}>{$label}</option>
	{/foreach}
</select>