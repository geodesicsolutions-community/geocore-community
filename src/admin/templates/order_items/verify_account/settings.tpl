{* 7.1beta2-132-g786eac4 *}

<div>
	<div class="{cycle values='row_color1,row_color2'}">
		<div class="leftColumn"><input type="checkbox" name="verify_account[enabled]" value="1" {if $enabled}checked="checked"{/if} /></div>
		<div class="rightColumn">Enable Item</div>
		<div class="clearColumn"></div>
	</div>
	<div class="{cycle values='row_color1,row_color2'}">
		<div class="leftColumn"><input type="checkbox" name="verify_account[require_for_listing]" value="1" {if $require_for_listing}checked="checked"{/if} /></div>
		<div class="rightColumn">Require Verified Account to place listings</div>
		<div class="clearColumn"></div>
	</div>
	{if $is_auctions}
		<div class="{cycle values='row_color1,row_color2'}">
			<div class="leftColumn"><input type="checkbox" name="verify_account[require_for_bid]" value="1" {if $require_for_bid}checked="checked"{/if} /></div>
			<div class="rightColumn">Require Verified Account to Bid On an Auction<br />(Bidding includes "buy now")</div>
			<div class="clearColumn"></div>
		</div>
	{/if}
	{if $account_balance_possible}
		<div class="{cycle values='row_color1,row_color2'}">
			<div class="leftColumn"><input type="checkbox" name="verify_account[apply_to_balance]" value="1" {if $apply_to_balance}checked="checked"{/if} /></div>
			<div class="rightColumn">Apply Charge to User's Account Balance</div>
			<div class="clearColumn"></div>
		</div>
	{/if}
	<div class="{cycle values='row_color1,row_color2'}">
		<div class="leftColumn">Amount to Charge</div>
		<div class="rightColumn">
			<label>{$precurrency}<input type="text" name="verify_account[amount]" value="{$amount|displayPrice:'':''}" size="4" /> {$postcurrency}</label>
		</div>
		<div class="clearColumn"></div>
	</div>
</div>