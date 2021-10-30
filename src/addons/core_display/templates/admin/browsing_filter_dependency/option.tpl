{* 7.1beta4-3-gdc2fedd *}
{if $name!==$field}
	<option value="{$name}" {if $settings.$field.dependency === $name}selected="selected"{/if}>{$label}</option>
{/if}