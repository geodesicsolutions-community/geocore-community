{* 67d0e9c *}

<div class="fb_button fb_button_small">
	<a href="{$loginUrl|replace:'&':'&amp;'}" class="fb_button_small">
		<span class="fb_button_text">
			{if $login_user && $login_user.facebook_id}
				{$msgs.fb_tag_login_reconnect}
			{elseif $login_user}
				{$msgs.fb_tag_login_link}
			{else}
				{$msgs.fb_tag_login_login}
			{/if}
		</span>
	</a>
</div>