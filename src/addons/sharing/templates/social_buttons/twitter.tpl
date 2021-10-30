{* 7.3beta3 *}
{* Twitter "Tweet" Link
	See http://twitter.com/about/resources/tweetbutton for options *}
{if $activeMethods.twitter == 1}
	<a href="http://twitter.com/share" class="twitter-share-button" data-count="none">Tweet</a>{add_footer_html}<script type="text/javascript" src="http://platform.twitter.com/widgets.js"></script>{/add_footer_html}
{/if}