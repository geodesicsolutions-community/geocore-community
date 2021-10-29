{* 7.6.3-96-g66f0347 *}
<div class="content_box">
	<h1 class="title">{$interrupted_action|capitalize} {$messages.500386}</h1>
	
	<p class="page_instructions">
		{if $interrupted_action==$new_action}
			{$messages.500812} {$interrupted_action}{$messages.500813}
		{else}
			{if $allFree}{$messages.500408}{else}{$messages.500387}{/if}
		{/if}
	</p>
	
	<div class="center">
		<strong>{$interrupted_action|capitalize}</strong><br />
		<a href="{$cart_url}" class="button">{$messages.500814}</a>
		<a href="{$new_action_url}" class="cancel">{if $interrupted_action==$new_action}{$messages.500815}{else}{$messages.500389}{/if}</a>
		
	</div>
</div>
