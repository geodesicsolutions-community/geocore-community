{* 7.6.3-149-g881827a *}

<div class="content_box">
	<h1 class="title my_account">{$messages.629}</h1>
	<h3 class="subtitle">{$messages.447}</h3>
	<p class="page_instructions">{$messages.458}</p>
	
	{if $category_tree}
		<div class="note_box">
				{$messages.711}: <a href="{$categoriesLink}" class="category_tree">{$messages.448}</a> &gt;
				{if is_array($tree)} 
					{foreach from=$tree item=t name=catTree}
						<a href="{$t.link}" class="category_tree">{$t.name}</a>{if !$smarty.foreach.catTree.last} &gt; {/if}
					{/foreach}
				{else}
					{$tree}
				{/if}
		</div>
	{/if}
	
	<h2 class="title">{$ad->TITLE|fromDB}</h2>
	<div class="{cycle values='row_odd,row_even'}">
		<label class="field_label">{$messages.449}</label>
		{$ad->ID}
	</div>
	
	{if $fields->price->is_enabled}
		<div class="{cycle values='row_odd,row_even'}">
			<label class="field_label">
				{if $ad->ITEM_TYPE == 1}
					{$messages.706}
				{else}
					{$messages.100706}
				{/if}
			</label>
		
			{$ad->PRECURRENCY|fromDB}
			{if $ad->ITEM_TYPE == 1}
				{$ad->PRICE|fromDB}
			{else}
				{$ad->FINAL_PRICE|fromDB}
			{/if}
			{$ad->POSTCURRENCY|fromDB}
		</div>
	{/if}
	{if $ad->PRICE_APPLIES == 'item' && $ad->QUANTITY}
		<div class="{cycle values='row_odd,row_even'}">
			<label class="field_label">{$messages.502136}</label>
			{$ad->QUANTITY}
		</div>
		<div class="{cycle values='row_odd,row_even'}">
			<label class="field_label">{$messages.502137}</label>
			{$ad->QUANTITY_REMAINING}
		</div>
	{/if}
	
	<div class="{cycle values='row_odd,row_even'}">
		<label class="field_label">{$messages.450}</label>
		{$ad->VIEWED + 1}
	</div>
	<div class="{cycle values='row_odd,row_even'}">
		<label class="field_label">{$messages.451}</label>
		{$start_date}
	</div>
	<div class="{cycle values='row_odd,row_even'}">
		<label class="field_label">{$messages.452}</label>
		{$end_date}
	</div>
	<div class="{cycle values='row_odd,row_even'}">
		<label class="field_label">{$messages.454}</label>
		{$ad->LOCATION_ZIP|fromDB}
	</div>
	<div class="{cycle values='row_odd,row_even'}">
		<label class="field_label">{$messages.453}</label>
		{$ad->REASON_AD_ENDED|fromDB}
	</div>
	<div class="{cycle values='row_odd,row_even'}">
		<label class="field_label">{$messages.455}</label>
		<div style="margin: 0 5px;">{$ad->DESCRIPTION|fromDB}</div>
	</div>
	
	{if $ad->URL_LINK_1}
		<div class="{cycle values='row_odd,row_even'}">
			<label class="field_label">{$messages.500992}</label>
			{$ad->URL_LINK_1|fromDB}
		</div>
	{/if}
	{if $ad->URL_LINK_2}
		<div class="{cycle values='row_odd,row_even'}">
			<label class="field_label">{$messages.500993}</label>
			{$ad->URL_LINK_2|fromDB}
		</div>
	{/if}
	{if $ad->URL_LINK_3}
		<div class="{cycle values='row_odd,row_even'}">
			<label class="field_label">{$messages.500994}</label>
			{$ad->URL_LINK_3|fromDB}
		</div>
	{/if}
	
	{if $ad->ITEM_TYPE == 2}
		{* show auction bids *}
		<h3 class="title">{$messages.103307}</h3>
		{if $ad->PRICE_APPLIES == 'item' && count($bids) > 0}
			<p class="page_instructions">
				{if $user.id==$ad->SELLER}
					{$messages.502133}
				{/if}
				{$messages.502134}
			</p>
		{/if}
		<table style="width: 100%;">
			{if count($bids) > 0}
				<tr class="results_column_header">
					<td class="nowrap" style="text-align: left;">{$messages.103311}</td>
					<td class="nowrap" style="text-align: left;">
						{$messages.103309}
						{if $ad->PRICE_APPLIES=='item'}
							{$messages.502135}
						{/if}
					</td>
					{if $ad->AUCTION_TYPE == 2 || $ad->PRICE_APPLIES == 'item'}<td class="nowrap" style="text-align: left;">{$messages.103310}</td>{/if}							
					<td class="title" style="text-align: left;">{$messages.103308}</td>
				</tr>
				{foreach from=$bids item=bid}
					<tr class="{cycle values="row_even,row_odd"}">
						<td class="nowrap">{$bid.username}{if $bid.email} ({$bid.email}){/if}</td>
						<td class="nowrap price">{$bid.amount}</td>
						{if $ad->AUCTION_TYPE == 2 || $ad->PRICE_APPLIES == 'item'}<td class="nowrap center">{$bid.quantity}</td>{/if}
						<td>{$bid.time}</td>
					</tr>
				{/foreach}
					
			{else}
				<tr class="data_values">
					<td colspan="4">{$messages.103312}</td>
				</tr>
			{/if}
		</table>
	{/if}
</div>
<br />
<div class="center">
	<a href="{$expiredAdsLink}" class="button">{$messages.456}</a> &nbsp; <a href="{$userManagementHomeLink}" class="button">{$messages.457}</a>
</div>
