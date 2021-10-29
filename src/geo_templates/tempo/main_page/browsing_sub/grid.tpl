{*CODE_ONLY*}
{*
NOTE:  This template is used to display the contents of each listing "row" when
browsing the site using grid view, during normal browsing.  Since this template
has a lot more "Smarty Tags" than normal, you can only edit using the code view.

If you try to use WYSIWYG editor to edit this template, there is very high chance
that it will corrupt the smarty tags!

NOTE: Grid view sub-template is a little different than the other views, as it
is used to show the header as well.
*}
{if $thead}
	{* The header section.. This sub-template is called for both the header section
		and for the main body section. *}
	<tr class="results_column_header">
		{* in hopes of making things easy on anyone who would like to modify this template, 
			this first column is intentionally over-commented *}

		{if $cfg.cols.type} 
			{* $cfg.cols array holds whether or not a column is being used *}
			
			<td class="browse_table_column_header_type">
				{* $headers.business_type.css holds the default css class for this header *}
				
				{* 
					$cfg.browse_url points to the current page, and ends in "c="
					$headers.business_type.reorder holds the value of "c", which tells the system how to re-sort the listings if this link is clicked
					$headers.business_type.text is the text of this header, changeable in the Geodesic admin controls
				*}
				{if $cfg.sort_links}<a class="{$headers.type.css}" href="{$cfg.browse_url}{$headers.type.reorder}">{/if}
					{$headers.type.text}
				{if $cfg.sort_links}</a>{/if}
			</td>
		{/if}
		
		{if $cfg.cols.business_type}
			<td class="browse_table_column_header_business_type{if $cfg.sort_links && $headers.business_type.reorder != 43} sorted_by{/if}">
				{if $cfg.sort_links}<a class="{$headers.business_type.css}" href="{$cfg.browse_url}{$headers.business_type.reorder}">{/if}
					{$headers.business_type.text}
				{if $cfg.sort_links}</a>{/if}
			</td>
		{/if}
		
		{if $cfg.cols.image}
			{* can't sort by image, so no link here *}
			<td class="browse_table_column_image">{$headers.image.text}</td>
		{/if}
		
		{if $cfg.cols.title}
			<td class="{if $cfg.sort_links && $headers.title.reorder != 5}sorted_by {/if}{if $cfg.cols.description}half{else}title{/if}">
				{if $cfg.sort_links}<a class="{$headers.title.css}" href="{$cfg.browse_url}{$headers.title.reorder}">{/if}
					{$headers.title.text}
				{if $cfg.sort_links}</a>{/if}

			</td>
		{elseif $cfg.cols.icons}
			<td class="browse_table_column_header_icons"></td>
		{/if}
		
		{if $cfg.cols.description}
			{* can't order by description, so no link here *}
			<td class="half">{$headers.description.text}</td>
		{/if}
		
		{if $cfg.cols.tags}
			<td class="browse_table_column_header_tags">
				{$headers.tags.text}
			</td>
		{/if}
		
		{if count($headers.optionals) > 0}
			{foreach from=$headers.optionals item=optional_header key="fieldNum"}
				{* $headers.optionals is indexed 1-20, so you could split this out of the loop if you really wanted to...
					e.g.: $headers.optionals.1.text to get the header text for "optional field 1" *}
					
				<td class="browse_table_column_header_{$fieldNum}{if $cfg.sort_links && ($fieldNum <= 10 && (($optional_header.reorder-15)/2)+1 != $fieldNum) || ($fieldNum > 10 && (($optional_header.reorder-45)/2)+11 != $fieldNum)} sorted_by{/if}">
					{if $cfg.sort_links}<a class="{$optional_header.css}" href="{$cfg.browse_url}{$optional_header.reorder}">{/if}
						{$optional_header.text}
					{if $cfg.sort_links}</a>{/if}
				</td>
			{/foreach}
		{/if}
		
		{if $cfg.cols.address}
			<td class="browse_table_column_header_address">
				{if $cfg.sort_links}<a class="{$headers.address.css}" href="{$cfg.browse_url}{$headers.address.reorder}">{/if}
					{$headers.address.text}
				{if $cfg.sort_links}</a>{/if}
			</td>
		{/if}
		
		{if $cfg.cols.city}
			<td class="browse_table_column_header_city{if $cfg.sort_links && $headers.city.reorder != 7} sorted_by{/if}">
				{if $cfg.sort_links}<a class="{$headers.city.css}" href="{$cfg.browse_url}{$headers.city.reorder}">{/if}
					{$headers.city.text}
				{if $cfg.sort_links}</a>{/if}
			</td>
		{/if}
					
		{if $cfg.cols.zip}
			<td class="browse_table_column_header_zip{if $cfg.sort_links && $headers.zip.reorder != 13} sorted_by{/if}">
				{if $cfg.sort_links}<a class="{$headers.zip.css}" href="{$cfg.browse_url}{$headers.zip.reorder}">{/if}
					{$headers.zip.text}
				{if $cfg.sort_links}</a>{/if}
			</td>
		{/if}
		
		
		{for $level=1 to $cfg.maxLocationDepth}
			{$col = "region_level_$level"}
			{if $cfg.cols.$col}
				<td class="browse_table_column_header_{$col}">
						{$headers.{$col}.text}
				</td>
			{/if}
		{/for}
		
		{if $cfg.cols.location_breadcrumb}
			<td class="browse_table_column_header_location_breadcrumb">
					{$headers.location_breadcrumb.text}
			</td>
		{/if}
		
		{if $cfg.cols.num_bids}
			<td class="browse_table_column_header_num_bids">
				{if $cfg.sort_links}<a class="{$headers.num_bids.css}" href="{$cfg.browse_url}{$headers.num_bids.reorder}">{/if}
					{$headers.num_bids.text}
				{if $cfg.sort_links}</a>{/if}
			</td>
		{/if}
		
		{if $cfg.cols.price}
			<td class="browse_table_column_header_price{if $cfg.sort_links && $headers.price.reorder != 1} sorted_by{/if}">
				{if $cfg.sort_links}<a class="{$headers.price.css}" href="{$cfg.browse_url}{$headers.price.reorder}">{/if}
					{$headers.price.text}
				{if $cfg.sort_links}</a>{/if}
			</td>
		{/if}
		
		{if $cfg.cols.entry_date}
			<td class="browse_table_column_header_entry_date{if $cfg.sort_links && $headers.entry_date.reorder != 4} sorted_by{/if}">
				{if $cfg.sort_links}<a class="{$headers.entry_date.css}" href="{$cfg.browse_url}{$headers.entry_date.reorder}">{/if}
					{$headers.entry_date.text}
				{if $cfg.sort_links}</a>{/if}
			</td>
		{/if}
		
		{if $cfg.cols.time_left}
			<td class="browse_table_column_header_time_left{if $cfg.sort_links && $headers.time_left.reorder != 70} sorted_by{/if}">
				{if $cfg.sort_links}<a class="{$headers.time_left.css}" href="{$cfg.browse_url}{$headers.time_left.reorder}">{/if}
					{$headers.time_left.text}
				{if $cfg.sort_links}</a>{/if}
			</td>
		{/if}
		{foreach $headers.leveled as $levels}
			{foreach $levels as $levelHeader}
				<td class="{$levelHeader.css}">
					{$levelHeader.text}
				</td>
			{/foreach}
		{/foreach}
		{* allow Addons to add their own column headers here *}
		{if $addonHeaders}
			{foreach from=$addonHeaders item=addon}
				{foreach from=$addon item=header}
					<td{if $header.css} class="{$header.css}"{/if}>
						{$header.text}
					</td>
				{/foreach}
			{/foreach}
		{/if}
		
		{if $cfg.cols.edit}
			<td>{$headers.edit.text}</td>
		{/if}
		
		{if $cfg.cols.delete}
			<td>{$headers.delete.text}</td>
		{/if}
	</tr>
{else}
	{* The main part, this is where each listing row is displayed. *}
	<tr class="{$listing.css}">
		{if $cfg.cols.type}
			<td class="center">
				{$listing.type}
			</td>
		{/if}
		
		{if $cfg.cols.business_type}
			<td class="center">
				{$listing.business_type}
			</td>
		{/if}
		
		{if $cfg.cols.image}
			<td class="center">
				<div class="attention-getter-wrapper">
					{if $listing.full_image_tag}
						<span class="rwd-image" style="position: relative;">{$listing.image}</span>
					{else}
						<a href="{$cfg.listing_url}{$id}" style="position: relative;" {if $cfg.popup}onclick="window.open(this.href,'_blank','width={$cfg.popup_width},height={$cfg.popup_height},scrollbars=1,location=0,menubar=0,resizable=1,status=0'); return false;"{/if}>
							<img src="{$listing.image}" class="rwd-image" alt="" />
						</a>
					{/if}
					{if $listing.icons.attention_getter}
					<a href="{$cfg.listing_url}{$id}" {if $cfg.popup}onclick="window.open(this.href,'_blank','width={$cfg.popup_width},height={$cfg.popup_height},scrollbars=1,location=0,menubar=0,resizable=1,status=0'); return false;"{/if}>
						<div class="attention-getter-position image rwd-image">
							<img src="{$listing.attention_getter_url}" class="attention_getter_icon" alt="" />
						</div>
					</a>
					{/if}
				</div>
			</td>
		{/if}
		
		{if $cfg.cols.title}
			<td style="width: {if $cfg.cols.description}50{else}100{/if}%">
				{* if description column is active, this and it share half width (class "half"). if not, this column gets max width (class "title") *}
				
				{if $listing.icons.sold && $cfg.icons.sold}<img src="{$cfg.icons.sold}" alt="" />{/if}
				
				<a href="{$cfg.listing_url}{$id}" {if $cfg.popup}onclick="window.open(this.href,'_blank','width={$cfg.popup_width},height={$cfg.popup_height},scrollbars=1,location=0,menubar=0,resizable=1,status=0'); return false;"{/if}>
					{$listing.title}
				</a>
				{if $listing.icons.verified && $cfg.icons.verified}<img src="{$cfg.icons.verified}" alt="" />{/if}
				{if $listing.icons.buy_now && $cfg.icons.buy_now}<img src="{$cfg.icons.buy_now}" alt="" />{/if}
				{if $listing.icons.reserve_met && $cfg.icons.reserve_met}<img src="{$cfg.icons.reserve_met}" alt="" />{/if}
				{if $listing.icons.reserve_not_met && $cfg.icons.reserve_not_met}<img src="{$cfg.icons.reserve_not_met}" alt="" />{/if}
				{if $listing.icons.no_reserve && $cfg.icons.no_reserve}<img src="{$cfg.icons.no_reserve}" alt="" />{/if}
				
				{if $listing.icons.attention_getter && $listing.attention_getter_url}<img src="{$listing.attention_getter_url}" class="attention-getter-inline" alt="" />{/if}
				
				{if $listing.icons.addon_icons}
					{foreach $listing.icons.addon_icons as $addon => $icon}
						{* it's up to the addons themselves to create the img tag, as some addons may need to add more than one icon *}
						{$icon}
					{/foreach}
				{/if}
				
				{if $cfg.description_under_title}<p class="listing_results_description">{$listing.description}</p>{/if}
			</td>
		{elseif $cfg.cols.icons}
			<td>
				{if $listing.icons.sold && $cfg.icons.sold}<img src="{$cfg.icons.sold}" alt="" />{/if}
				{if $listing.icons.verified && $cfg.icons.verified}<img src="{$cfg.icons.verified}" class="verified_icon" alt="" />{/if}
				{if $listing.icons.buy_now && $cfg.icons.buy_now}<img src="{$cfg.icons.buy_now}" class="buy_now_icon" alt="" />{/if}
				{if $listing.icons.reserve_met && $cfg.icons.reserve_met}<img src="{$cfg.icons.reserve_met}" class="reserve_met_icon" alt="" />{/if}
				{if $listing.icons.reserve_not_met && $cfg.icons.reserve_not_met}<img src="{$cfg.icons.reserve_not_met}" class="reserve_not_met_icon" alt="" />{/if}
				{if $listing.icons.no_reserve && $cfg.icons.no_reserve}<img src="{$cfg.icons.no_reserve}" class="no_reserve_icon" alt="" />{/if}
				
				{if $listing.icons.attention_getter}<img src="{$listing.attention_getter_url}" class="attention_getter_icon" alt="" />{/if}
				
				{if $listing.icons.addon_icons}
					{foreach $listing.icons.addon_icons as $addon => $icon}
						{* it's up to the addons themselves to create the img tag, as some addons may need to add more than one icon *}
						{$icon}
					{/foreach}
				{/if}
			</td>
		{/if}
		
		{if $cfg.cols.description}
		 	{* this column is only shown alongside the title column, never by itself (but is sometimes not shown at all), so it gets a hard width of 50% *}
			<td style="width: 50%;">
				{$listing.description}
			</td>
		{/if}
		
		{if $cfg.cols.tags}
			<td class="tag_list_data">
				{if $listing.tags}
					{listing tag='listing_tags_links'}
				{/if}
			</td>
		{/if}
		
		{section name=optionals start=1 loop=21}
			{assign var='field' value=$smarty.section.optionals.index}
			{if $cfg.cols.optionals.$field}
				<td class="center">
					{if $listing.optionals.$field}{$listing.optionals.$field}{else}-{/if}
				</td>
			{/if}
		{/section}
		
		{if $cfg.cols.address}
			<td class="center">
				{if $listing.address}{$listing.address}{else}{$cfg.empty}{/if}
			</td>
		{/if}
		
		{if $cfg.cols.city}
			<td class="center">
				{if $listing.city}{$listing.city}{else}{$cfg.empty}{/if}
			</td>
		{/if}
		
		{if $cfg.cols.zip}
			<td class="center">
				{if $listing.zip}{$listing.zip}{else}{$cfg.empty}{/if}
			</td>
		{/if}
		
		{for $level=1 to $cfg.maxLocationDepth}
			{$col = "region_level_$level"}
			{if $cfg.cols.$col}
				<td class="center nowrap">
						{if $listing.$col}{$listing.$col}{else}{$cfg.empty}{/if}
				</td>
			{/if}
		{/for}
		
		
		{if $cfg.cols.location_breadcrumb}
			<td class="center nowrap">
				{if $listing.location_breadcrumb}{$listing.location_breadcrumb}{else}{$cfg.empty}{/if}
			</td>
		{/if}
		
		{if $cfg.cols.num_bids}
			<td class="center nowrap">
				{if $listing.num_bids}{$listing.num_bids}{else}{$cfg.empty}{/if}
			</td>
		{/if}
		
		{if $cfg.cols.price}
			<td class="center nowrap price">
				{if $listing.price}<span class="price">{$listing.price}</span>{else}{$cfg.empty}{/if}
			</td>
		{/if}
		
		{if $cfg.cols.entry_date}
			<td class="center nowrap">
				{if $listing.entry_date}{$listing.entry_date}{else}{$cfg.empty}{/if}
			</td>
		{/if}
		
		{if $cfg.cols.time_left}
			<td class="center nowrap">
				{if $listing.time_left}{$listing.time_left}{else}{$cfg.empty}{/if}
			</td>
		{/if}
		
		{foreach $headers.leveled as $lev_id => $levels}
			{foreach $levels as $level => $levelHeader}
				<td class="center nowrap">
					{$listing.leveled.$lev_id.$level.name}
				</td>
			{/foreach}
		{/foreach}
		
		{if $listing.addonData}
			{* let addons add columns if they want to *}
			{foreach from=$listing.addonData item=addonRows}
				{foreach from=$addonRows item=addonText}
					<td class="center nowrap">
						{$addonText}
					</td>
				{/foreach}
			{/foreach}
		{/if}
		
		{if $cfg.cols.edit}
			<td class="center">
				<a href="{$classifieds_file_name}?a=cart&amp;action=new&amp;main_type=listing_edit&amp;listing_id={$id}"><img src="{external file='images/buttons/listing_edit.gif'}" alt="" /></a>
			</td>
		{/if}
		
		{if $cfg.cols.delete}
			<td class="center">
				<a onclick="if (!confirm('Are you sure you want to delete this?')) return false;" href="{$classifieds_file_name}?a=99&amp;b={$id}"><img src="{external file='images/buttons/listing_delete.gif'}" alt="" /></a>
			</td>
		{/if}
	</tr>
{/if}