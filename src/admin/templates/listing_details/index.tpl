{* 7.3.0-7-g4962ea6 *}

{$adminMsgs}
<fieldset>
	<legend>Listing Details</legend>
	<div>
		
		<div class='center'>
			<a class='mini_button' href='index.php?page=admin_cart&amp;userId={$userId}&amp;action=new&amp;main_type=listing_edit&amp;listing_id={$listingId}'>Edit Details</a>
			<a class='mini_button' href='index.php?page=users_restart_ad&amp;b={$listingId}'>Restart or Change Extras</a>
		</div>
		
		<br /><br />
		<div class="col_hdr_top">Listing Specifics</div>
		
		{if $listing.live}
			<div class="{cycle values='row_color1,row_color2'}">
				<div class="leftColumn">Listing Link</div>
				<div class="rightColumn">
					<a href="{$listing_link}" class="mini_button" onclick="window.open(this.href); return false;">View Listing</a>
				</div>
				<div class="clearColumn"></div>
			</div>
		{/if}
		
		<div class="{cycle values='row_color1,row_color2'}">
			<div class="leftColumn">Listing Type</div>
			<div class="rightColumn">
				{if $listing.item_type==2}
					{if $listing.auction_type==1}
						Standard Auction
					{elseif $listing.auction_type==2}
						Dutch Auction
					{elseif $listing.auction_type==3}
						Reverse Auction
					{/if}
				{elseif $listing.item_type==1}
					Classified Ad
				{/if}
			</div>
			<div class="clearColumn"></div>
		</div>
		<div class="{cycle values='row_color1,row_color2'}">
			<div class="leftColumn">Category</div>
			<div class="rightColumn">
				Main 
				{foreach from=$category_tree item=cat}
					&gt; {$cat.category_name}
				{/foreach}
			</div>
			<div class="clearColumn"></div>
		</div>
		<div class="{cycle values='row_color1,row_color2'}">
			<div class="leftColumn">Title</div>
			<div class="rightColumn">{$listing.title|fromDB}</div>
			<div class="clearColumn"></div>
		</div>
		<div class="{cycle values='row_color1,row_color2'}">
			<div class="leftColumn">Listing ID</div>
			<div class="rightColumn">{$listingId}</div>
			<div class="clearColumn"></div>
		</div>
		<div class="{cycle values='row_color1,row_color2'}">
			<div class="leftColumn">Seller</div>
			<div class="rightColumn">
				{$user.username} ( {$user.email|fromDB} )
				{if $verify_img}<img src="../{external file=$verify_img}" alt="" />{/if}
				<br />
				<a href="index.php?mc=users&amp;page=users_view&amp;b={$listing.seller}" class="mini_button">User Details</a>
				<a href="index.php?page=admin_messaging_send&amp;b[{$listing.seller}]={$user.username}" class="mini_button">Contact</a>
			</div>
			<div class="clearColumn"></div>
		</div>
		<div class="{cycle values='row_color1,row_color2'}">
			<div class="leftColumn">Listing Contact Email</div>
			<div class="rightColumn">{$listing.email|fromDB}</div>
			<div class="clearColumn"></div>
		</div>
		{if $listing.phone||$listing.phone2||$listing.fax}
			<div class="{cycle values='row_color1,row_color2'}">
				<div class="leftColumn">Phone</div>
				<div class="rightColumn">
					{$listing.phone|fromDB}
					{if $listing.phone2}
						<br />{$listing.phone2|fromDB}
					{/if}
					{if $listing.fax}
						<br />fax: {$listing.fax|fromDB}
					{/if}
				</div>
				<div class="clearColumn"></div>
			</div>
		{/if}
		{if $listing.url_link_1||$listing.url_link_2||$listing.url_link_3}
			<div class="{cycle values='row_color1,row_color2'}">
				<div class="leftColumn">URLs</div>
				<div class="rightColumn">
					{if $listing.url_link_1}
						<a href="{$listing.url_link_1|fromDB}">{$listing.url_link_1|fromDB}</a>
					{/if}
					{if $listing.url_link_2}
						<br /><a href="{$listing.url_link_2|fromDB}">{$listing.url_link_2|fromDB}</a>
					{/if}
					{if $listing.url_link_3}
						<br /><a href="{$listing.url_link_3|fromDB}">{$listing.url_link_3|fromDB}</a>
					{/if}
				</div>
				<div class="clearColumn"></div>
			</div>
		{/if}
		{if $listing.mapping_location}
			<div class="{cycle values='row_color1,row_color2'}">
				<div class="leftColumn">Mapping Location</div>
				<div class="rightColumn">
					{$listing.mapping_location|fromDB}
				</div>
				<div class="clearColumn"></div>
			</div>
		{/if}
		{if $listing.discount_id}
			<div class="{cycle values='row_color1,row_color2'}">
				<div class="leftColumn">Discount ID</div>
				<div class="rightColumn">{$listing.discount_id}</div>
				<div class="clearColumn"></div>
			</div>
			<div class="{cycle values='row_color1,row_color2'}">
				<div class="leftColumn">Discount Percentage</div>
				<div class="rightColumn">{$listing.discount_percentage} %</div>
				<div class="clearColumn"></div>
			</div>
			<div class="{cycle values='row_color1,row_color2'}">
				<div class="leftColumn">Discount Amount</div>
				<div class="rightColumn">{$listing.discount_amount|displayPrice}</div>
				<div class="clearColumn"></div>
			</div>
		{/if}
		{if $listing.duration}
			<div class="{cycle values='row_color1,row_color2'}">
				<div class="leftColumn">Duration</div>
				<div class="rightColumn">{$listing.duration} Days</div>
				<div class="clearColumn"></div>
			</div>
		{/if}
		{if $listing.date}
			<div class="{cycle values='row_color1,row_color2'}">
				<div class="leftColumn">Date Listing Placed<br />or Renewed</div>
				<div class="rightColumn">{$listing.date|format_date:'M d, Y H:i:s'}</div>
				<div class="clearColumn"></div>
			</div>
		{/if}
		{if $listing.start_time && $listing.date<$listing.start_time}
			<div class="{cycle values='row_color1,row_color2'}">
				<div class="leftColumn">Scheduled Listing Start Date</div>
				<div class="rightColumn">{$listing.start_time|format_date:'M d, Y H:i:s'}</div>
				<div class="clearColumn"></div>
			</div>
		{/if}
		{if $listing.item_type==2 && $listing.end_time && $listing.date<$listing.end_time}
			<div class="{cycle values='row_color1,row_color2'}">
				<div class="leftColumn">Scheduled Listing End Date</div>
				<div class="rightColumn">{$listing.end_time|format_date:'M d, Y H:i:s'}</div>
				<div class="clearColumn"></div>
			</div>
		{else}
			<div class="{cycle values='row_color1,row_color2'}">
				<div class="leftColumn">Date Listing Ends</div>
				<div class="rightColumn">
					{if $listing.live<2}
						{$listing.ends|format_date:'M d, Y H:i:s'}
					{else}
						{$listing.duration*86400+$smarty.now|format_date:'M d, Y H:i:s'}
					{/if}
				</div>
				<div class="clearColumn"></div>
			</div>
		{/if}
		<div class="{cycle values='row_color1,row_color2'}">
			<div class="leftColumn">Location</div>
			<div class="rightColumn">
				{if $listing.location}{$listing.location}{/if}<br />
				{if $listing.location_address}{$listing.location_address|fromDB}, {/if}
				{if $listing.location_city}{$listing.location_city|fromDB}, {/if}
				{if $listing.location_zip}{$listing.location_zip|fromDB}, {/if}
			</div>
			<div class="clearColumn"></div>
		</div>
		{if $listing.locations.1}
			<div class="{cycle values='row_color1,row_color2'}">
				<div class="leftColumn">Additional Regions</div>
				<div class="rightColumn">
					<div style="max-height: 120px; float: left; overflow: auto;">
						<ol>
							{foreach $listing.locations as $location}
								{if $location@first}{continue}{/if}
								<li>
									{foreach $location as $region}
										{$region.name}{if !$region@last} &gt;{/if}
									{/foreach}
								</li>
							{/foreach}
						</ol>
					</div>
					<div class="clr"></div>
				</div>
				<div class="clearColumn"></div>
			</div>
		{/if}
		{if $listing.item_type==1}
			<div class="{cycle values='row_color1,row_color2'}">
				<div class="leftColumn">Price</div>
				<div class="rightColumn">{$listing.price|displayPrice:$listing.precurrency:$listing.postcurrency}</div>
				<div class="clearColumn"></div>
			</div>
		{elseif $listing.item_type==2}
			<div class="{cycle values='row_color1,row_color2'}">
				<div class="leftColumn">Starting Price</div>
				<div class="rightColumn">{$listing.starting_bid|displayPrice:$listing.precurrency:$listing.postcurrency}</div>
				<div class="clearColumn"></div>
			</div>
			<div class="{cycle values='row_color1,row_color2'}">
				<div class="leftColumn">Reserve Price</div>
				<div class="rightColumn">{$listing.reserve_price|displayPrice:$listing.precurrency:$listing.postcurrency}</div>
				<div class="clearColumn"></div>
			</div>
			<div class="{cycle values='row_color1,row_color2'}">
				<div class="leftColumn">Buy Now Price</div>
				<div class="rightColumn">{$listing.buy_now|displayPrice:$listing.precurrency:$listing.postcurrency}</div>
				<div class="clearColumn"></div>
			</div>
			<div class="{cycle values='row_color1,row_color2'}">
				<div class="leftColumn">Final Price</div>
				<div class="rightColumn">{$listing.final_price|displayPrice:$listing.precurrency:$listing.postcurrency}</div>
				<div class="clearColumn"></div>
			</div>
			<div class="{cycle values='row_color1,row_color2'}">
				<div class="leftColumn">Quantity</div>
				<div class="rightColumn">{$listing.quantity}</div>
				<div class="clearColumn"></div>
			</div>
			<div class="{cycle values='row_color1,row_color2'}">
				<div class="leftColumn">Bid History</div>
				<div class="rightColumn">
					{if $bid_history}
						<table class="small_font" cellpadding="2" cellspacing="0" style="width: 300px;">
							<tr class='row_color_black small_font_light'>
								<td><b>Bid Date</b></td>
								<td><b>Bid Amount</b></td>
								<td><b>Bidder</b></td>
							</tr>
							{foreach from=$bid_history item='bid'}
								<tr class="{cycle values='row_color1,row_color2,row_color3'}">
									<td>{$bid.time_of_bid|format_date:$entry_date_configuration}</td>
									<td>{$bid.bid|displayPrice:$listing.precurrency:$listing.postcurrency}</td>
									<td>{$bid.username}</td>
								</tr>
							{/foreach}
						</table>
					{else}
						No Bids
					{/if}
				</div>
				<div class="clearColumn"></div>
			</div>
			<div class="{cycle values='row_color1,row_color2'}">
				<div class="leftColumn">Open Proxy Bids</div>
				<div class="rightColumn">
					{if $proxy_bid_history}
						<table class="small_font" cellpadding="2" cellspacing="0" style="width: 300px;">
							<tr class='row_color_black small_font_light'>
								<td><b>Bid Date</b></td>
								<td><b>Max Bid Amount</b></td>
								<td><b>Bidder</b></td>
							</tr>
							{foreach from=$proxy_bid_history item='bid'}
								<tr class="{cycle values='row_color1,row_color2,row_color3'}">
									<td>{$bid.time_of_bid|format_date:$entry_date_configuration}</td>
									<td>{$bid.maxbid|displayPrice:$listing.precurrency:$listing.postcurrency}</td>
									<td>{$bid.username}</td>
								</tr>
							{/foreach}
						</table>
					{else}
						No Open Proxy Bids
					{/if}
				</div>
				<div class="clearColumn"></div>
			</div>
		{/if}
		{if $listing.payment_options}
			<div class="{cycle values='row_color1,row_color2'}">
				<div class="leftColumn">Payment Types Accepted</div>
				<div class="rightColumn">
					{$listing.payment_options|fromDB|replace:'||':', '}
				</div>
				<div class="clearColumn"></div>
			</div>
		{/if}
		<div class="{cycle values='row_color1,row_color2'}">
			<div class="leftColumn">Price Plan</div>
			<div class="rightColumn">
				{if $price_plan_name}
					{$price_plan_name} ({$listing.price_plan_id})
				{else}
					Unknown
				{/if}
				{if $plan_choices}
					<br />
					Change to:
					<form action='' method='post' style="display: inline;">
						<select name="c">
							{foreach from=$plan_choices item='pp'}
								<option value="{$pp.price_plan_id}"{if $pp.price_plan_id==$listing.price_plan_id} selected="selected"{/if}>
									{$pp.name}
								</option>
							{/foreach}
						</select>
						<input type='submit' name='auto_save' value="Change" />
					</form>
				{/if}
			</div>
			<div class="clearColumn"></div>
		</div>
		<div class="{cycle values='row_color1,row_color2'}">
			<div class="leftColumn">Status</div>
			<div class="rightColumn">
				{if $listing.live}
					Live
				{else}
					Expired {$listing.reason_ad_ended|fromDB|escape}
				{/if}
			</div>
			<div class="clearColumn"></div>
		</div>
		<div class="{cycle values='row_color1,row_color2'}">
			<div class="leftColumn">Number of Times Viewed</div>
			<div class="rightColumn">
				{if $listing.viewed}
					{$listing.viewed}
				{else}
					zero
				{/if}
			</div>
			<div class="clearColumn"></div>
		</div>
		<div class="{cycle values='row_color1,row_color2'}">
			<div class="leftColumn">Number of Times Responded</div>
			<div class="rightColumn">
				{if $listing.responded}
					{$listing.responded}
				{else}
					zero
				{/if}
			</div>
			<div class="clearColumn"></div>
		</div>
		<div class="{cycle values='row_color1,row_color2'}">
			<div class="leftColumn">Number of Times Forwarded</div>
			<div class="rightColumn">
				{if $listing.forwarded}
					{$listing.forwarded}
				{else}
					zero
				{/if}
			</div>
			<div class="clearColumn"></div>
		</div>
		<div class="{cycle values='row_color1,row_color2'}">
			<div class="leftColumn">Expiration Notice Sent</div>
			<div class="rightColumn">{if $listing.expiration_notice}yes{else}no{/if}</div>
			<div class="clearColumn"></div>
		</div>
		<div class="{cycle values='row_color1,row_color2'}">
			<div class="leftColumn">Description</div>
			<div class="rightColumn">
				<textarea cols="50" rows="4" style="width: 100%;">{$listing.description|fromDB|escape}</textarea>
			</div>
			<div class="clearColumn"></div>
		</div>
		{if $tags}
			<div class="{cycle values='row_color1,row_color2'}">
				<div class="leftColumn">Listing Tags</div>
				<div class="rightColumn">
					{foreach from=$tags item='tag'}{$tag}, {/foreach}
				</div>
				<div class="clearColumn"></div>
			</div>
		{/if}
		{foreach from=$optionals item=optional}
			<div class="{cycle values='row_color1,row_color2'}">
				<div class="leftColumn">{$optional.label}</div>
				<div class="rightColumn">{$optional.value}</div>
				<div class="clearColumn"></div>
			</div>
		{/foreach}
		{if $extras}
			<div class="col_hdr_top">Extra Questions</div>
			{foreach from=$extras item='extra'}
				<div class="{cycle values='row_color1,row_color2'}">
					<div class="leftColumn">{$extra.name|fromDB}</div>
					<div class="rightColumn">{$extra.value|fromDB}</div>
					<div class="clearColumn"></div>
				</div>
			{/foreach}
		{/if}
		{if $images}
			{include file='listing_details/images.tpl'}
		{/if}
		
		{if $offsite_videos}
			{include file='listing_details/offsite_videos.tpl'}
		{/if}
		
	</div>
</fieldset>

{if $order_items}
	{include file='orders/list_items.tpl' items=$order_items itemLegend='Order Items affecting Listing'
		hideNarrow=1 hideStatChange=1 hidePages=1}
{/if}
