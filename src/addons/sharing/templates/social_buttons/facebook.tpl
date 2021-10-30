{* 7.4.4-114-g6ba2326 *}
{* Facebook "Like" Button
	See http://developers.facebook.com/docs/reference/plugins/like for options 
	https://developers.facebook.com/tools/debug can be used to debug the information FB "scrapes" from the site
	*}

{if $activeMethods.facebook == 1}
	{if true}
		<iframe src="http://www.facebook.com/plugins/like.php?href={$listing_url}&amp;layout=button_count&amp;show_faces=false&amp;width=90&amp;action=like&amp;colorscheme=light&amp;height=21" style="border:none; overflow:hidden; width:90px; height:21px;"></iframe>
	{else}
		{* HTML5 version -- doesn't align right against other buttons, but might be nice to use eventually *}
		<div style="display: inline-block;" class="fb-like" data-href="{$listing_url|escape}" data-send="false" data-layout="button_count" data-width="90" data-show-faces="false"></div>
	{/if}
{/if}

{* not entirely sure what this is for, but FB whines if it's not here *}
<div id="fb-root"></div>