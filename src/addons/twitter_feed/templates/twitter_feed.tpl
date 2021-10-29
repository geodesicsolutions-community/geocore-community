{* 16.05.0-17-g5113ae1 *}
<div class="twitter_feed_show_timeline">
	<a class="twitter-timeline" href="{$href}" {if $data_id}data-widget-id="{$data_id}"{/if}
	{if $config.tweet_limit}data-tweet-limit="{$config.tweet_limit}"{/if}
	{if $config.width}width="{$config.width}"{/if}
	{if $config.width}height="{$config.height}"{/if}
	{if $config.theme}data-theme="{$config.theme}"{/if}
	{if $config.link_color}data-link-color="#{$config.link_color}"{/if}
	{if $config.border_color}data-border-color="#{$config.border_color}"{/if}
	{if $config.chrome_string}data-chrome="{$config.chrome_string}"{/if}>
		{$msgs.default_link_label}
	</a>
	
	{* contents of this script tag should be the same for every timeline *}
	{add_footer_html}{literal}
		<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?'http':'https';if(!d.getElementById(id)){
		js=d.createElement(s);js.id=id;js.src=p+"://platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}
		(document,"script","twitter-wjs");</script>
	{/literal}{/add_footer_html}
</div>