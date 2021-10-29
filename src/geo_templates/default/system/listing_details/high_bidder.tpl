{* 7.0.3-306-g69f3c8a *}
{if $high_bidder_username}
	{$high_bidder_username}
{else}
	{* Show "no high bidder" message *}
	{$messages.103002}
{/if}