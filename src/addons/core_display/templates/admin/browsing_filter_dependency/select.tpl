{* 7.1beta4-3-gdc2fedd *}
<select name="settings[{$field}][dependency]">
	<option value="">-</option>
	{foreach $leveled_fields as $name => $dep_info}
		{if !$leveled_field||$leveled_field!=$dep_info.leveled_field}
			{* Do not show "child" levels as options to this one to be dependent on *}
			{$label = "{$dep_info.leveled_field_label} - Level {$dep_info.level}"}
			{include file='admin/browsing_filter_dependency/option.tpl'}
		{/if}
	{/foreach}
	{foreach $optionals as $name => $label}
		{include file='admin/browsing_filter_dependency/option.tpl'}
	{/foreach}
	{foreach $catSpec as $name => $label}
		{include file='admin/browsing_filter_dependency/option.tpl' name="cs_{$name}"}
	{/foreach}
	
	{* Price *}
	{include file='admin/browsing_filter_dependency/option.tpl' name='price' label='Price'}
</select>