{* 7.5.3-36-gea36ae7 *}
{if $salutation}{$salutation},<br />{/if}
{$messageBody}<br />
<br />
{if $price_applies=='item'}
	<strong>{$messages.502114}</strong> {$quantity} @ {$finalBid} {$messages.502115}
{else}
	<strong>{$finalBidLabel}</strong> {$finalBid}
{/if}
<br /><br />
{if $cost_options}
	<strong>{$messages.502274}</strong><br />
	{foreach $cost_options as $group_id => $option_id}
		{$group=$cost_option_group_details.$group_id}
		{$option=$cost_option_details.$option_id}
		<strong>{$group.label|fromDB}</strong>
		{$option.label|fromDB}
		{if $option.cost_added} [+{$option.cost_added_pretty}]{/if}
		<br />
	{/foreach}
	<br />
	{if $cost_options_cost}
		{if $price_applies=='item'}
			<strong>{$messages.502275}</strong>
		{else}
			<strong>{$messages.502276}</strong>
		{/if}
		{$cost_options_cost_pretty}
		<br /><br />
	{/if}
{/if}
{if $additionalFees}
	<strong>{if $price_applies=='item'}{$messages.502116}{else}{$messages.500033}{/if}</strong><br />
	{foreach $additionalFees.formatted as $key => $fee}
		{if $key!=='total'}
			{$fee}<br />
		{/if}
	{/foreach}
	{if $price_applies=='item'}
		<strong>{$messages.502117}</strong> {$additionalFees.formatted.total}
		<br /><br />
		<strong>{$messages.502118}</strong> {$additionalFees.grandTotal}
	{else}
		<strong>{$messages.500035}</strong> {$additionalFees.formatted.total}
	{/if}
	<br /><br />
	{$messages.500034}
	<br /><br />
{/if}
<strong>{$messages.500036}</strong> {$grandTotal}
<br /><br />
{$highBidderInfo.firstname} {$highBidderInfo.lastname}<br />
{$highBidderInfo.email}<br />
<br />
{$listingTitle}<br />
{if $cost_options}
	<strong>{$messages.502274}</strong><br />
	{foreach $cost_options as $group_id => $option_id}
		{$group=$cost_option_group_details.$group_id}
		{$option=$cost_option_details.$option_id}
		<strong>{$group.label|fromDB}</strong>
		
		{$option.label|fromDB}
		{if $option.cost_added} [+{$option.cost_added_pretty}]{/if}
		{if $option.image_url}
			<img src="{$option.image_url}" alt="{$option.label|fromDB}" style="width: 50px;">
		{/if}
		<br />
	{/foreach}
	<br />
{/if}

<a href="{$listingLink}">{$listingLink}</a>
{if $price_applies=='item'}
	<br /><br />
	{if $quantity_remaining}
		<strong>{$messages.502119}</strong> {$quantity_remaining} {$messages.502120} {$quantity_starting}
	{else}
		{$messages.502121}
	{/if}
{/if}