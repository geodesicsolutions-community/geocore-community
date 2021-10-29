{* 67d0e9c *}
{foreach $friends as $name}
	<a href="http://www.facebook.com/profile.php?id={$name@key}" onclick="window.open(this.href); return false;">
		<img src="https://graph.facebook.com/{$name@key}/picture" alt="{$name|escape}" style="height: 20px;" /> {$name}{if !$name@last}, {/if}
	</a>
{/foreach}
uses this site
