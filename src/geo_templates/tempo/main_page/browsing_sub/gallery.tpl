{*CODE_ONLY*}
{*
NOTE:  This template is used to display the contents of each listing "box" when
browsing the site using gallery view, during normal browsing.  Since this template
has a lot more "Smarty Tags" than normal, you can only edit using the code view.

If you try to use WYSIWYG editor to edit this template, there is very high chance
that it will corrupt the smarty tags!
*}
<article class="listing clearfix {if $gallery_columns} columns-{$gallery_columns}{/if} gallery-rwd">
	{* 
		NOTE: The simple carousel works "with" this gallery view template to
		turn a normal gallery view into a sliding carousel using Javascript.  In
		order for the simple gallery carousel to work properly, it needs
		to use div with "article_inner" class otherwise things don't line up
		(or may not work at all) in the carousel.
		
		In other words, leave the div HTML tag below with the article_inner class 
		intact, or it can break the carousel (featured image slider).
	 *}
	<div class="article_inner {$listing.css}">
		{if $cfg.cols.image}
			<div class="image attention-getter-wrapper">
				{if $listing.full_image_tag}
					<span style="position: relative;">{$listing.image}</span>
				{else}
					<a href="{$cfg.listing_url}{$id}" style="position: relative;">
						<img src="{$listing.image}" alt="{$listing.title|escape}" />
					</a>
				{/if}
				{if $listing.icons.attention_getter}<a href="{$cfg.listing_url}{$id}"><div class="attention-getter-position"><img src="{$listing.attention_getter_url}" class="attention_getter_icon" alt="" /></div></a>{/if}
			</div>
		{/if}
		<div style="margin-top: 3px;">
			<span class="glyphicon glyphicon-camera"></span>&nbsp;:&nbsp;<a href="{$cfg.listing_url}{$id}">{listing tag='number_images'}</a>&nbsp;|&nbsp;
			<span class="glyphicon glyphicon-facetime-video"></span>&nbsp;:&nbsp;<a href="{$cfg.listing_url}{$id}">{listing tag='number_videos'}</a>
			<!-- &nbsp;|&nbsp;
			<img src="{external file='images/icons/heart_sml.png'}" alt="" />&nbsp;:&nbsp;{listing tag='favorites_link'} -->
		</div>
		{if $cfg.cols.type&&$listing.type}
			<p class="type">
				{if $headers.type.label}<em>{$headers.type.label}</em>{/if}
				{$listing.type}
			</p>
		{/if}
		
		{if $cfg.cols.title}
			<div class="title">
				{if $listing.icons.sold && $cfg.icons.sold}<img src="{$cfg.icons.sold}" alt="" />{/if}
				<h3><a href="{$cfg.listing_url}{$id}">{$listing.title}</a></h3>
				{if $listing.icons.verified && $cfg.icons.verified}<div><img src="{$cfg.icons.verified}" class="verified_icon" alt="" /></div>{/if}
				{if $listing.icons.buy_now && $cfg.icons.buy_now}<div><img src="{$cfg.icons.buy_now}" class="buy_now_icon" alt="" /></div>{/if}
				{if $listing.icons.reserve_met && $cfg.icons.reserve_met}<div><img src="{$cfg.icons.reserve_met}" class="reserve_met_icon" alt="" /></div>{/if}
				{if $listing.icons.reserve_not_met && $cfg.icons.reserve_not_met}<div><img src="{$cfg.icons.reserve_not_met}" class="reserve_not_met_icon" alt="" /></div>{/if}
				{if $listing.icons.no_reserve && $cfg.icons.no_reserve}<div><img src="{$cfg.icons.no_reserve}" class="no_reserve_icon" alt="" /></div>{/if}
				
				{if $listing.icons.attention_getter}<div><img src="{$listing.attention_getter_url}" class="attention_getter_icon attention-getter-inline" alt="" /></div>{/if}
				
				{if $listing.icons.addon_icons}
					{foreach $listing.icons.addon_icons as $addon => $icon}
						<div>{$icon}</div>
					{/foreach}
				{/if}
			</div>
		{elseif $cfg.cols.icons && $listing.icons}
			{if $listing.icons.sold && $cfg.icons.sold}<div><img src="{$cfg.icons.sold}" alt="" /></div>{/if}
			{if $listing.icons.verified && $cfg.icons.verified}<div><img src="{$cfg.icons.verified}" class="verified_icon" alt="" /></div>{/if}
			{if $listing.icons.buy_now && $cfg.icons.buy_now}<div><img src="{$cfg.icons.buy_now}" class="buy_now_icon" alt="" /></div>{/if}
			{if $listing.icons.reserve_met && $cfg.icons.reserve_met}<div><img src="{$cfg.icons.reserve_met}" class="reserve_met_icon" alt="" /></div>{/if}
			{if $listing.icons.reserve_not_met && $cfg.icons.reserve_not_met}<div><img src="{$cfg.icons.reserve_not_met}" class="reserve_not_met_icon" alt="" /></div>{/if}
			{if $listing.icons.no_reserve && $cfg.icons.no_reserve}<div><img src="{$cfg.icons.no_reserve}" class="no_reserve_icon" alt="" /></div>{/if}
			
			{if $listing.icons.attention_getter}<div><img src="{$listing.attention_getter_url}" class="attention_getter_icon" alt="" /></div>{/if}
			
			{if $listing.icons.addon_icons}
				{foreach $listing.icons.addon_icons as $addon => $icon}
					<div>{$icon}</div>
				{/foreach}
			{/if}
		{/if}
		{if $cfg.cols.price&&$listing.price}
			<p class="price">
				{if $headers.price.label}<em>{$headers.price.label}</em>{/if}
				<span class="price">{$listing.price}</span>
			</p>
		{/if}	
		
		{if $listing.addonData}
			{* let addons add columns if they want to *}
			{foreach $listing.addonData as $addonRows}
				{foreach $addonRows as $addonText}
					<p class="addon_data {$addonHeaders[$addon][$aKey].css}">
						{if $addonHeaders[$addon][$aKey].label}<em>{$addonHeaders[$addon][$aKey].label}</em>{/if}
						{$addonText}
					</p>
				{/foreach}
			{/foreach}
		{/if}
		
		{if $cfg.cols.edit}
			<p class="edit">
				<a href="{$classifieds_file_name}?a=cart&amp;action=new&amp;main_type=listing_edit&amp;listing_id={$id}"><img src="{external file='images/buttons/listing_edit.gif'}" alt="" /></a>
			</p>
		{/if}
		
		{if $cfg.cols.delete}
			<p class="delete">
				<a onclick="if (!confirm('Are you sure you want to delete this?')) return false;" href="{$classifieds_file_name}?a=99&amp;b={$id}"><img src="{external file='images/buttons/listing_delete.gif'}" alt="" /></a>
			</p>
		{/if}

		<div class="clr"> </div>
		
		{if $listing.city or $listing.time_left or $listing.entry_date}
		<div class="clr"> </div>		
		<div class="legend-abs-space clr"> </div>
		<div class="legend clr">
			{if $cfg.cols.city && $listing.city}
			<div class="legend-location">
					<p>
						<span class="glyphicon glyphicon-map-marker"></span> {$listing.city} 
					</p>
				</div>
			{/if}
	
			<div class="legend-vote">
				<p>
					 &nbsp;&nbsp;<span style="white-space: nowrap;">{listing tag='voteSummary_text'} {listing tag='voteSummary_percent'}%</span>
				</p>
			</div>
	
			{if $cfg.cols.time_left&&$listing.time_left}
				<div class="legend-time">
					<p>
						<span class="glyphicon glyphicon-hourglass"></span> {$listing.time_left}
					</p>
				</div>
			{/if}
			{if $cfg.cols.entry_date && $listing.entry_date}
				<div class="legend-time">
					<p>
						{$listing.entry_date}
					</p>
				</div>
			{/if}
		</div>
		{/if}
		<div class="clr"> </div>
	</div>
</article>