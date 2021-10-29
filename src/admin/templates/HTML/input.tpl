{if $type eq 'text'}
	<input type='text' id='{$id}' name='{$id}' value='{$default_value}' /> 
{elseif $type eq 'large_text'}
	<input type='text' id='{$id}' name='{$id}' value='{$default_value}' style='width:300px'/> 
{elseif $type eq 'file'}
	<input type='file' id='{$id}' name='{$id}' style='width:300px'/> 
{/if}