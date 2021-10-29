{* 16.09.0-79-gb63e5d8 *}
{if $page.type != 'sub_page'}
<li{if $page.current} class="current-page"{/if}>
	<a href="index.php?page={$page.index}&amp;mc={$mc}">{$page.title}</a>
</li>
{/if}