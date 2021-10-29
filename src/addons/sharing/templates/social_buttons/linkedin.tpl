{* 7.1beta4-99-g84389d8 *}
{* LinkedIn Share Button
	See http://developer.linkedin.com/plugins/share-plugin-generator for options
*}
{if $activeMethods.linkedin == 1}
	<script type="text/javascript" src="http://platform.linkedin.com/in.js"></script><script type="IN/Share" data-url="{$listing_url_unencoded}"></script>
{/if}