{* 17.03.0-17-g898e9d6 *}

<div class="content_box">
	<h1 class="title">{$messages.102447}</h1>
	
	<div class="{cycle values='row_even,row_odd'}">
		<label class="field_label">{$messages.102450}</label>
		{$title}
	</div>
	
	{if $is_dutch} 
		<div class="{cycle values='row_even,row_odd'}">
			<label class="field_label">{$messages.102455}</label>
			{$quantity}
		</div>
		
		<div class="{cycle values='row_even,row_odd'}">
			<label class="field_label">{if $reverse_auction}{$messages.500999}{else}{$messages.102441}{/if}</label>
			{$price}
		</div>
	{else} 
		<div class="{cycle values='row_even,row_odd'}">
			<label class="field_label">{$messages.102451}</label>
			{$price}
			{if $auction_type=='buy_now'&&$price_applies=='item'&&$quantity>1}{$messages.502110}{/if}
		</div>
		
		{if $auction_type == 'buy_now'}
			<div class="{cycle values='row_even,row_odd'}">
				<label class="field_label">
					{$messages.502111}
				</label>
				{if $price_applies=='lot' && $quantity>1}{$messages.502112}{/if}
				{$quantity}
			</div>
		{/if}
		
		{if $additional_fees}
			<div class="{cycle values='row_even,row_odd'}">
				<label class="field_label">{$messages.502169}</label>
				{$additional_fees}
			</div>
		{/if}
		
		<div class="success_box">
			{$successText}
		</div>
	{/if}
	
	{if $rebidLink} 
		<div class="center">
			<a href="{$rebidLink}" class="button">{if $reverse_auction}{$messages.501002}{else}{$messages.102452}{/if}</a>
		</div>
	{/if}

</div>

<br />

<div class="center">
	<a href="{$categoryLink}" class="button">{$messages.102454}</a>
	<a href="{$auctionLink}" class="button">{$messages.102453}</a>
</div>
