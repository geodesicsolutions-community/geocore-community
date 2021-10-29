{* 6.0.7-3-gce41f93 *}
{if $checkbox_hidden ne ''}
	{$checkbox_hidden}
{else}
	<input name='{$checkbox_name}' value='0' type='hidden' />
{/if}
<label>
	{if $checkbox ne ''}
		{$checkbox}
	{else}
		<input name='{$checkbox_name}' value='1' type='checkbox' {$checked} />
	{/if}
	{$title}
</label>

{$display_help_link}
