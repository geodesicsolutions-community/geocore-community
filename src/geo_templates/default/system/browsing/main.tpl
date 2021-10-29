{* 7.6.3-149-g881827a *}

{$category_cache}

{addon tag_type='core' tag='browsing_before_listings_column'}

<div id="content_column_wide">
	{addon tag_type='core' tag='browsing_before_listings'}
	
	{include file='common/browse_mode_buttons.tpl'}
	
	{if $show_classifieds&&$show_auctions&&!$disable_browsing_tabs}
		<ul class="tabList">
			{if $display_all_tab_browsing}<li id="allTab" class="activeTab">{$messages.501620}</li>{/if}
			<li id="classifiedsTab"{if !$display_all_tab_browsing} class="activeTab"{/if}>{$messages.500975}</li>
			<li id="auctionsTab">{$messages.500976}</li>
		</ul>
		
		<div class="tabContents">
			<div id="allTabContents"></div>
	{else}
		<div class="clear"></div>
	{/if}

	{if $show_classifieds}
		{if $show_auctions&&!$disable_browsing_tabs}<div id="classifiedsTabContents">{/if}
			{if $show_featured_classifieds}
				<div class="content_box">
					<h3 class="title">
						{$messages.28} {$current_category_name}
						{if $featured_links}
							<a class="featured_ads_links" href="{$classifieds_file_name}?a=9&amp;b={$category_id}">{$messages.873}</a>
							<a class="featured_ads_links" href="{$classifieds_file_name}?a=8&amp;b={$category_id}">{$messages.872}</a>
						{/if}
					</h3>
					{include file=$browse_tpl
						listings=$featured_classifieds.listings
						no_listings=$featured_classifieds.no_listings
						addonHeaders=$featured_classifieds.addonHeaders
						cfg=$featured_classifieds.cfg
						headers=$featured_classifieds.headers}
				</div>
				<br />
			{/if}
			<div class="content_box">
				<h2 class="title"><span class="category-intro">{$messages.200109}</span>&nbsp;{$current_category_name}</h2>
				{include file=$browse_tpl
					listings=$classified_browse_result.listings
					no_listings=$classified_browse_result.no_listings
					addonHeaders=$classified_browse_result.addonHeaders
					cfg=$classified_browse_result.cfg
					headers=$classified_browse_result.headers}
			</div>

		{if $show_auctions&&!$disable_browsing_tabs}</div>{/if}
	{/if}
	
	{if $show_auctions}
		{if $show_classifieds&&!$disable_browsing_tabs}<div id="auctionsTabContents">{/if}
			{if $show_featured_auctions}
				<div class="content_box">
					<h3 class="title">
						{$messages.100028} {$current_category_name}
						{if $featured_links}
							<a class="featured_ads_links" href="{$classifieds_file_name}?a=9&amp;b={$category_id}">{$messages.873}</a>
							<a class="featured_ads_links" href="{$classifieds_file_name}?a=8&amp;b={$category_id}">{$messages.872}</a>
						{/if}
					</h3>
					{include file=$browse_tpl
						listings=$featured_auctions.listings
						no_listings=$featured_auctions.no_listings
						addonHeaders=$featured_auctions.addonHeaders
						cfg=$featured_auctions.cfg
						headers=$featured_auctions.headers}
				</div>
				<br />
			{/if}
		
			<div class="content_box">
				<h2 class="title"><span class="category-intro">{$messages.200110}</span>&nbsp;{$current_category_name}</h2>
				{include file=$browse_tpl
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
</div>
