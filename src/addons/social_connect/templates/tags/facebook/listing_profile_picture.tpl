{* 7.5.3-36-gea36ae7 *}{strip}
	{if $facebook_id&&$facebook_reveal=='Yes'}
		{* Facebook id is set for this seller and has it set to reveal the profile pic, so show it *}
		{include file='facebook/profile_picture.tpl'}
	{/if}
{/strip}