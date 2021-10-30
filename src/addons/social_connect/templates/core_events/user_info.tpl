{* 67d0e9c *}
{if $facebook_id}
	{include file='facebook/profile_picture.tpl'}
{else}
	<div class="fb_button fb_button_small"><a href="{$loginUrl|replace:'&':'&amp;'}" class="fb_button_small"><span class="fb_button_text">{$msgs.fb_usr_info_link_button}</span></a></div>
{/if}