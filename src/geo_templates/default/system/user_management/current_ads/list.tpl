{* 7.6.3-149-g881827a *}

<div class="content_box">
	<h1 class="title my_account">{$messages.634}</h1>
	<h3 class="subtitle">{$messages.504}</h3>
	<p class="page_instructions">{$messages.505}</p>

	<div class="search_content_box">
		<div class="center">
			<div class="form-wrapper cf">
				<form method="get" action="" style="display: inline;">
					<input type="hidden" name="a" value="4" />
					<input type="hidden" name="b" value="1" />
					<input type='text' id='search_text' name='q' value="{$q}" size='60' maxlength='80' class="field" placeholder="{$messages.500660|escape}" />
					<button type="submit"><span class="glyphicon glyphicon-search"></span></button>
				</form>
			</div>
		</div>
	</div>


<!-- NEW BEFORE CHANGES
	<div class="search_content_box">
		<div class="center">
			<div class="form-wrapper cf">
				<input type="hidden" name="b[subcategories_also]" value="1" />
				<input type='text' id='search_text' name='b[search_text]' size='60' maxlength='80' class="field" placeholder="{$messages.573|escape}" />
				<button type="submit"><span class="glyphicon glyphicon-search"></span></button>

			</div>
		</div>
	</div>
NEW BEFORE CHANGES -->

<!-- OLD
	<div style="text-align: center;">
		<form method="get" action="" style="display: inline;">
			<input type="hidden" name="a" value="4" />
			<input type="hidden" name="b" value="1" />
			<input type="text" name="q" value="{$q}" size="45" class="field" />
			<input type="submit" value="{$messages.500660}" class="button" />
		</form>
	</div>
OLD -->

</div>

<div class="content_box">
	<table style="width: 100%;">
		<tr class="column_header">
			{if $is_ca && $bothListingTypes}
				<td class="nowrap">{$messages.200000}</td>
			{/if}
			<td></td>
			<td width="100%">{$messages.506}</td>
			{if $is_a}
				<td class="nowrap">{$messages.102865}</td>
			{/if}
			{if $sold_image and $is_e}
				<td class="nowrap">{$messages.715}</td>
			{/if}

			<td class="nowrap">{$messages.500899}</td>

		</tr>
		{foreach from=$listings key=k item=listing}
			<tr class="{cycle values='row_odd,row_even'}">
				{if $is_ca && $bothListingTypes}
					<td class="center">
						{if $listing.item_type eq 1}{$messages.200002}{else}{$messages.200001}{/if}
					</td>
				{/if}

				<td><span class="active-pic">{$listing.thumbnail}</span></td>

				<td width="100%">
					<div>
						<a href="{$file_name}?a=2&amp;b={$listing.id}">
							<strong>{$listing.title|fromDB}</strong></a>
						<span style="font-size: 0.8em; color: #666;">({$listing.id})</span>
						<br />
						<span style="font-size: 0.8em;">
							<span style="font-weight: bold;">{$messages.507}: </span>
							{if $listing.item_type eq 2 && $listing.delayed_start eq 1}
							{$messages.500228}
						{else}
						<span style="white-space: nowrap;">{$listing.date|format_date:$date_format}</span>
						{/if}

						{if $listing.ends}
							<br />
							<span style="font-weight: bold;">{$messages.508}: </span>
							{if $listing.item_type eq 2 && $listing.delayed_start eq 1}
								{$messages.500228}
							{else}
								<span style="white-space: nowrap;">{$listing.ends|format_date:$date_format}</span>
							{/if}
						{/if}

						{if $listing.item_type==2 && $listing.price_applies=='item'}
							<br />
							<span style="font-weight: bold;">{$messages.502122}: </span> {$listing.quantity}<br />
							<span style="font-weight: bold;">{$messages.502123}: </span> {$listing.quantity_remaining}
						{/if}
						</span>
					</div>


					<div id="button_{$k}">
						<input type="button" onclick="showActionsForRow({$k}); return false;" class="mini_button" value="{$messages.500898}" />
					</div>
					<div id="actions_{$k}" style="display: none;">
						{if $listing.addon_buttons}{$listing.addon_buttons}{/if}
						{if $listing.show_renew_link}<a href="{$ssl_url}?a=cart&amp;action=new&amp;main_type=listing_renew_upgrade&amp;listing_id={$listing.id}&amp;r=1" title="{$messages.834}" class="mini_button"><img src="{external file='images/buttons/listing_renew.png'}" alt="{$messages.834}" /></a>{/if}
						{if $listing.show_upgrade_link}<a href="{$ssl_url}?a=cart&amp;action=new&amp;main_type=listing_renew_upgrade&amp;listing_id={$listing.id}&amp;r=2" title="{$messages.835}" class="mini_button"><img src="{external file='images/buttons/listing_upgrade.png'}" alt="{$messages.835}" /></a>{/if}
						{if $listing.show_edit_link}<a title="{$messages.3205}" href="{$file_name}?a=cart&amp;action=new&amp;main_type=listing_edit&amp;listing_id={$listing.id}" title="{$messages.509}" class="mini_button"><img src="{external file='images/buttons/listing_edit.png'}" alt="{$messages.509}" /></a>{/if}
						{if $listing.show_delete_link}<a title="{$messages.3206}" href="{$file_name}?a=4&amp;b=6&amp;c={$listing.id}" title="{$messages.510}" class="mini_button"><img src="{external file='images/buttons/listing_delete.png'}" alt="{$messages.510}" /></a>{/if}
						{if $allow_copying_new_listing}<a title="{$messages.500180}" href="{$file_name}?a=cart&amp;action=new&amp;main_type={if $listing.item_type == 1}classified{else}auction{/if}&amp;copy_id={$listing.id}" title="{$messages.200176}" class="mini_button"><img src="{external file='images/buttons/listing_copy.png'}" alt="{$messages.200176}" /></a>{/if}
						{if $listing.bump_access}<a title="{$messages.502301}" href="{$file_name}?a=4&amp;b=1&amp;bump_id={$listing.id}" class="mini_button"><img src="{external file='images/buttons/listing_bump.png'}" alt="{$messages.200176}" /></a>{/if}
					</div>
				</td>

				{if $is_a}
					<td class="center nowrap">
						{if $listing.price_applies=='item'&&$listing.bids}
							{include file='system/user_management/current_ads/bids_list.tpl'}
						{elseif $listing.current_bid > 0}
							{$listing.current_bid}
						{elseif $listing.item_type eq 1}
							&nbsp;-&nbsp;
						{elseif $listing.auction_type == 2}
							<a href="{$file_name}?a=1031&amp;b={$listing_id}">{$messages.502171}</a>
						{else}
							{$messages.102864}
						{/if}
					</td>
				{/if}
				{if $sold_image and $is_e}
					<td class="center">
						{if $listing.item_type eq 1}
							<a href="{$file_name}?a=4&amp;b=11&amp;c={$listing.id}" class="mini_button">
								{if $listing.sold_displayed}{$messages.717}{else}{$messages.716}{/if}
							</a>
						{else if $listing.sold_displayed}
							{$messages.717}
						{else}
							&nbsp;-&nbsp;
						{/if}
					</td>
				{/if}

				<td class="nowrap" style="font-size: 0.8em;">
					<span style="font-weight: bold;">{$messages.783}:</span> {$listing.forwarded}<br />
					<span style="font-weight: bold;">{$messages.784}:</span> {$listing.responded}<br />
					<span style="font-weight: bold;">{$messages.785}:</span> {$listing.viewed}<br />
					<span style="font-weight: bold;">{$messages.502045}:</span> {$listing.favorited}
				</td>


			</tr>
		{foreachelse}
			<div class="field_error_box">
					{if $q}
						{$messages.500662}
					{else}
						{$messages.511}
					{/if}
			</div>
		{/foreach}
	</table>
</div>
{add_footer_html}
<script>
	showing = false;
	var showActionsForRow = function(row) {

		//hide previously chosen action bar
		if(showing !== false) {
			jQuery('#actions_'+showing).hide();
			jQuery('#button_'+showing).show();
		}

		//hide this row's manage button, and show its action bar
		jQuery('#button_'+row).hide();
		jQuery('#actions_'+row).show();
		showing = row;

	};

	jQuery(document).ready(function () {
		jQuery('.showAllBidsButton').click(function () {
			var contents = jQuery(this).next('.showAllBids');
			if (contents.length) {
				jQuery(document).gjLightbox('open',contents.html());
			}
			return false;
		});
	});
</script>
{/add_footer_html}

{if $show_pagination}
	{$messages.200173} {$pagination}
{/if}
<div class="center">
	<a href="{$file_name}?a=4" class="button">{$messages.512}</a>
</div>

{if $pending}
	<br />
	<div class="content_box">
		<h3 class="title">{$messages.1433}</h3>

		<table style="width: 100%;">
			<tr class="column_header">
				<td>{$messages.506}</td>
				<td>{$messages.1434}</td>
				<td></td>
			</tr>
			{foreach from=$pending item=listing}
				<tr class="{cycle values="row_even,row_odd"}">
					<td>{$listing.title|fromDB} ({$listing.id}) {if $listing.upgrade_icon}{$listing.upgrade_icon}{/if}</td>
					<td style="text-align: left;">{$listing.description}</td>
					<td>{$listing.amount}</td>
				</tr>
			{/foreach}
		</table>
	</div>
{/if}
