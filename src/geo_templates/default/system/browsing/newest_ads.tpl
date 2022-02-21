{* 7.6.3-139-ga22e995 *}

{$category_cache}

{include file='system/browsing/common/browse_mode_buttons.tpl'}

{if $show_classifieds&&$show_auctions&&!$disable_browsing_tabs}
	<ul class="tabList">
		{if $display_all_tab_browsing}<li id="allTab" class="activeTab">{$messages.501674}</li>{/if}
		<li id="classifiedsTab"{if !$display_all_tab_browsing} class="activeTab"{/if}>{$messages.501672}</li>
		<li id="auctionsTab">{$messages.501673}</li>
	</ul>

	<div class="tabContents">
		<div id="allTabContents"></div>
{else}
	<div class="clear"></div>
{/if}

{if $show_classifieds}
	{if $show_auctions&&!$disable_browsing_tabs}<div id="classifiedsTabContents">{/if}
		<div class="content_box">
			<h2 class="title">{$messages.899} {$current_category_name}</h2>
			{include file="system/browsing/$browse_tpl"
				listings=$classified_browse_result.listings
				no_listings=$classified_browse_result.no_listings
				addonHeaders=$classified_browse_result.addonHeaders
				cfg=$classified_browse_result.cfg
				headers=$classified_browse_result.headers}
		</div>
		<br />
	{if $show_auctions&&!$disable_browsing_tabs}</div>{/if}
{/if}

{if $show_auctions}
	{if $show_classifieds&&!$disable_browsing_tabs}<div id="auctionsTabContents">{/if}
	<div class="content_box">
		<h2 class="title">{$messages.100899} {$current_category_name}</h2>
		{include file="system/browsing/$browse_tpl"
			listings=$auction_browse_result.listings
			no_listings=$auction_browse_result.no_listings
			addonHeaders=$auction_browse_result.addonHeaders
			cfg=$auction_browse_result.cfg
			headers=$auction_browse_result.headers}
	</div>
	{if $show_classifieds&&!$disable_browsing_tabs}</div>{/if}
{/if}
{if $show_classifieds&&$show_auctions&&!$disable_browsing_tabs}
	</div>
{/if}

{if $pagination}
	{$messages.757} {$pagination}
{/if}
