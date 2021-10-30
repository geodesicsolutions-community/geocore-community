<form {if $id}  id='{$id}' name='{$id}' {/if}{if $action} action='{$action}'{/if}{if $method} method='{$method}'{else} method='POST'{/if} {if $enctype} enctype='{$enctype}'{/if}>
	{$html}
</form>