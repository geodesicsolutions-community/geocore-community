{* 7.5.3-36-gea36ae7 *}

{if $success}
	<div class="success_box">{$success}</div>
{/if}
{if $errors}
	{foreach from=$errors item=err}
		<div class="error_message">{$err}</div>
	{/foreach}
{/if}