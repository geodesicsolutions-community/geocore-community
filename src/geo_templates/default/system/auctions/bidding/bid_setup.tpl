{* @git-info@ *}
<div class="content_box">
	<h1 class="title">{$messages.102437}</h1>
	<h3 class="subtitle">{$title}</h3>
	<form action="{$formTarget}" method="post" id="bid_data_form">
		{if $auction_type == 'buy_now'}
			<p class="page_instructions">{$messages.102442}</p>
			<div class="bid-form-container">
				<div class="bid-form-spacer">
					<div class="cntr">
						<label class="field_label uppercase">{if $verify}{$messages.500237}{else}{$messages.102443}{/if}</label>
						<div class="bid-price">{$price}</div>
						{if $price_applies=='item' && $max_quantity>1}
							{$messages.502105}
						{elseif $price_applies=='lot' && $max_quantity>1}
							{$messages.502106}
						{/if}
					</div>
					{if $additional_fees}
						<div class="{cycle values='row_even,row_odd'} cntr">
							<div for="fees" class="bid-label">{$messages.502169}</div>
							<div class="price bid-value">{$additional_fees.formatted.total}</div>
							{if $price_applies=='item' && (!{$verify} && $max_quantity > 1) || ({$verify} && $quantity > 1)}{$messages.502105}{/if}
						</div>
					{/if}
					{if $cost_options.groups}
						<div class="cntr">
							<label class="field_label">{$messages.502271}</label>
							{include file='system/auctions/bidding/cost_options'}
						</div>
						{if $verify && $cost_options_cost}
							<div class="{cycle values='row_even,row_odd'} cntr">
								<label class="field_label">{$messages.502272}</label>
								<span class="price">{$cost_options_cost|displayPrice}</span>
								{if $price_applies=='item' && $quantity > 1}{$messages.502105}{/if}
							</div>
						{/if}
					{/if}
					<div class="cntr" style="padding: 0.6em 0.4em;">
						<div class="bid-label">
							{if $verify}{$messages.502108}{else}{$messages.502107}{/if}
						</div>
						<div class="bid-value">
							{if !$verify && $price_applies=='item'&&$max_quantity > 1}
								<input type="text" name="c[bid_quantity]" value="{$quantity}" size="7" class="field" />
							{else}
								{if $price_applies=='lot' && $quantity>1}{$messages.502109}{/if}
								{$quantity}
								<input type="hidden" name="c[bid_quantity]" value="{$quantity}" />
							{/if}
						</div>
					</div>
					{if $additional_fees||($price_applies=='item' && $quantity>1)}
						<div class="cntr">
							<label for="grandTotal" class="field_label uppercase">{$messages.502170}</label>
							<div class="bid-price price">
								{if $verify}
									{$grandTotal}
								{else}
									{strip}
										{$precurrency}
										<span data-base-cost="{$baseTotal}" id="listing-buy-now-price-{$listing_id}">
											{$grandTotalRaw|displayPrice:'':''}
										</span>
										{if !$hide_postcurrency} {$postcurrency}{/if}
									{/strip}
								{/if}
							</div>
							{if !$verify && $price_applies == 'item' && $max_quantity > 1}{$messages.502105}{/if}
						</div>
					{/if}
					<div class="center">
						<input type="hidden" name="c[bid_amount]" value="{$hidden_price}" />
						<input type="hidden" name="d" value="1" />
						<div class="addon_bid_extra">{foreach $addon_bid_extra as $addon}{$addon}{/foreach}</div>
						<input type="submit" name="c[buy_now_bid]" class="button buy_now_link" value="{if $verify}{$messages.500238}{else}{$messages.102444}{/if}" />
					</div>
				</div>
			</div>
		{elseif $auction_type == 'dutch'}
			<p class="page_instructions">{$messages.102446}</p>
			<div class="bid-form-container">
				<div class="{cycle values='row_even,row_odd'}">
					<label for="c[bid_quantity]" class="field_label">{if $verify}{$messages.500240}{else}{$messages.102445}{/if}</label>
					{if $verify}
						{$quantity}<input type="hidden" name="c[bid_quantity]" value="{$quantity}" />
					{else}
						<input type="text" size="7" maxsize="7" name="c[bid_quantity]" id="c[bid_quantity]" value="1" class="field" />
					{/if}
				</div>
				<div class="{cycle values='row_even,row_odd'}">
					<label for="c[bid_amount]" class="field_label">{if $verify}{$messages.500239}{else}{$messages.102440}{/if}</label>
					{if $verify}
						{$price}<input type="hidden" name="c[bid_amount]" value="{$hidden_price}" />
					{else}
					<span style="white-space: nowrap;">{$precurrency} <input type="text" name="c[bid_amount]" id="c[bid_amount]" value="{$bid_to_show}" class="field" /> {if !$hide_postcurrency}{$postcurrency}{/if}</span>
					{/if}
				</div>
				{if $additional_fees}
					<div class="{cycle values='row_even,row_odd'}">
						<label for="fees" class="field_label">{$messages.502169}</label> {$additional_fees.formatted.total}
					</div>
					<div class="{cycle values='row_even,row_odd'}">
						<label for="grandTotal" class="field_label">{$messages.502170}</label> {$additional_fees.grandTotal}
					</div>
				{/if}
			</div>
			<br />
			<div class="center">
				<div class="addon_bid_extra">{foreach $addon_bid_extra as $addon}{$addon}{/foreach}</div>
				<input type="submit" value="{if $verify}{$messages.500241}{else}{$messages.102439}{/if}" class="button" />
			</div>
		{elseif $auction_type == 'reverse'}
			<p class="page_instructions">{$messages.500987}</p>
			<div class="{cycle values='row_even,row_odd'} cntr">
				<label for="c[bid_amount]" class="field_label">{if $verify}{$messages.500989}{else}{$messages.500988}{/if}</label>
				{if $verify}
					{$price}<input type="hidden" name="c[bid_amount]" value="{$hidden_price}" />
				{else}
					{$precurrency} <input type="text" name="c[bid_amount]" id="c[bid_amount]" value="{$bid_to_show}" class="field" /> {if !$hide_postcurrency}{$postcurrency}{/if}
				{/if}
			</div>
			{if $additional_fees}
				<div class="{cycle values='row_even,row_odd'} cntr">
					<label for="fees" class="field_label">{$messages.502169}</label> {$additional_fees.formatted.total}
				</div>
				<div class="{cycle values='row_even,row_odd'} cntr">
					<label for="grandTotal" class="field_label">{$messages.502170}</label> {$additional_fees.grandTotal}
				</div>
			{/if}
			{if $cost_options.groups}
				<div class="{cycle values='row_even,row_odd'}">
					<label class="field_label">{$messages.502271}</label>
					{include file='system/auctions/bidding/cost_options'}
				</div>
				{if $verify && $cost_options_cost}
					<div class="{cycle values='row_even,row_odd'} cntr">
						<label class="field_label">{$messages.502272}</label>
						<span class="price">{$cost_options_cost|displayPrice}</span>
						{if $price_applies=='item' && $quantity > 1}{$messages.502105}{/if}
					</div>
				{/if}
			{/if}
			<br />
			<div class="center">
				<div class="addon_bid_extra">{foreach $addon_bid_extra as $addon}{$addon}{/foreach}</div>
				<input type="submit" value="{if $verify}{$messages.500991}{else}{$messages.500990}{/if}" class="button buy_now_link" />
			</div>
		{elseif $auction_type == 'standard'}
			<p class="page_instructions">{$messages.102438}</p>
			<div class="bid-form-container">
				<div class="cntr">
					<label for="c[bid_amount]" class="field_label">{if $verify}{$messages.500242}{else}{$messages.102440}{/if}</label>
					{if $verify}
					<span style="white-space: nowrap;">{$price}<input type="hidden" name="c[bid_amount]" value="{$hidden_price}" /></span>
					{else}
					<span style="white-space: nowrap; font-size: .75em;">{$precurrency} <input type="text" name="c[bid_amount]" id="c[bid_amount]" value="{$bid_to_show}" class="field" /> {if !$hide_postcurrency}{$postcurrency}{/if}</span>
					{/if}
				</div>
				{if $additional_fees}
					<div class="{cycle values='row_even,row_odd'} cntr">
						<label for="fees" class="field_label">{$messages.502169}</label> {$additional_fees.formatted.total}
					</div>
					{if $verify}
						<div class="{cycle values='row_even,row_odd'} cntr">
							<label for="grandTotal" class="field_label">{$messages.502170}</label> {$grandTotal}
						</div>
					{/if}
				{/if}
				{if $cost_options.groups}
					<div class="{cycle values='row_even,row_odd'} cntr">
						<label class="field_label">{$messages.502271}</label>
						{include file='system/auctions/bidding/cost_options'}
					</div>
					{if $verify && $cost_options_cost}
						<div class="{cycle values='row_even,row_odd'} cntr">
							<label class="field_label">{$messages.502272}</label>
							<span class="price">{$cost_options_cost|displayPrice}</span>
						</div>
					{/if}
				{/if}
			</div>
			<div class="center">
				<div class="addon_bid_extra">{foreach $addon_bid_extra as $addon}{$addon}{/foreach}</div>
				<input type="submit" value="{if $verify}{$messages.500236}{else}{$messages.102439}{/if}" class="button buy_now_link" />
			</div>
		{/if}
	</form>
</div>
<div class="center"><a href="{$auctionLink}" class="button">{$messages.103055}</a></div>
