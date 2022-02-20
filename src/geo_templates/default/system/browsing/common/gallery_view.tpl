{* 16.03.0-16-g2293afa *}
{if !$gallery_columns}
	{$gallery_columns=1}
{/if}
{if $no_listings}
	<div class="no_results_box">
		{$no_listings}
	</div>
{else}
	<div class="listing_set gallery">
		<div class="gallery_inner">
			{foreach $listings as $id=>$listing}
				{if $gallery_columns&&$listing@index%$gallery_columns == 0}<div class="gallery_row">{/if}

				{if $main_page_gallery_sub_template}
					{include file="main_page/$main_page_gallery_sub_template"}
				{else}
					<article class="listing columns-{$gallery_columns}">
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
							{if $cfg.cols.type&&$listing.type}
								<p class="type">
									{if $headers.type.label}<em>{$headers.type.label}</em>{/if}
									{$listing.type}
								</p>
							{/if}

							{if $cfg.cols.title}
								<div class="title">
									{if $headers.title.label}<em>{$headers.title.label}</em>{/if}
									{if $listing.icons.sold && $cfg.icons.sold}<img src="{$cfg.icons.sold}" alt="" />{/if}
									<h3><a href="{$cfg.listing_url}{$id}"{include file='system/browsing/common/popup_link.tpl'}>{$listing.title}</a></h3>
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

								{if $listing.icons.attention_getter}<div><img src="{$listing.attention_getter_url}" class="attention_getter_icon attention-getter-inline" alt="" /></div>{/if}

								{if $listing.icons.addon_icons}
									{foreach $listing.icons.addon_icons as $addon => $icon}
										<div>{$icon}</div>
									{/foreach}
								{/if}
							{/if}

							{if $cfg.cols.business_type&&$listing.business_type}
								<p class="business_type">
									{if $headers.business_type.label}<em>{$headers.business_type.label}</em>{/if}
									{$listing.business_type}
								</p>
							{/if}

							{if $cfg.cols.description||$cfg.description_under_title}
							 	<p class="description">
									{if $headers.description.label}<em>{$headers.description.label}</em>{/if}
									{$listing.description}
								</p>
							{/if}

							{if $cfg.cols.tags && $listing.tags}
								<p class="tag_list_data">
									{if $headers.tags.label}<em>{$headers.tags.label}</em>{/if}
									{listing tag='listing_tags_links'}
								</p>
							{/if}

							{section name=optionals start=1 loop=21}
								{assign var='field' value=$smarty.section.optionals.index}
								{if $cfg.cols.optionals.$field&&$listing.optionals.$field}
									<p class="optional_{$field}">
										{if $headers.optionals.$field.label}<em>{$headers.optionals.$field.label}</em>{/if}
										{$listing.optionals.$field}
									</p>
								{/if}
							{/section}

							{if $cfg.cols.address&&$listing.address}
								{* TODO:  use <address></address> for address elements? *}
								<p class="address">
									{if $headers.address.label}<em>{$headers.address.label}</em>{/if}
									{$listing.address}
								</p>
							{/if}

							{if $cfg.cols.city&&$listing.city}
								<p class="city">
									{if $headers.city.label}<em>{$headers.city.label}</em>{/if}
									{$listing.city}
								</p>
							{/if}

							{for $level=1 to $cfg.maxLocationDepth}
								{$col = "region_level_$level"}
								{if $cfg.cols.$col&&$listing.$col}
									<p class="{$col}">
										{if $headers.$col.label}<em>{$headers.$col.label}</em>{/if}
										{$listing.$col}
									</p>
								{/if}
							{/for}

							{if $cfg.cols.zip&&$listing.zip}
								<p class="zip">
									{if $headers.zip.label}<em>{$headers.zip.label}</em>{/if}
									{$listing.zip}
								</p>
							{/if}

							{if $cfg.cols.location_breadcrumb&&$listing.location_breadcrumb}
								<p class="region_breadcrumb">
									{if $headers.location_breadcrumb.label}<em>{$headers.location_breadcrumb.label}</em>{/if}
									{$listing.location_breadcrumb}
								</p>
							{/if}

							{if $cfg.cols.num_bids&&$listing.num_bids}
								<p class="number_bids">
									{if $headers.num_bids.label}<em>{$headers.num_bids.label}</em>{/if}
									{$listing.num_bids}
								</p>
							{/if}

							{if $cfg.cols.price&&$listing.price}
								<p class="price">
									{if $headers.price.label}<em>{$headers.price.label}</em>{/if}
									{$listing.price}
								</p>
							{/if}

							{if $cfg.cols.entry_date&&$listing.entry_date}
								<p class="entry_date">
									{if $headers.entry_date.label}<em>{$headers.entry_date.label}</em>{/if}
									{$listing.entry_date}
								</p>
							{/if}

							{if $cfg.cols.time_left&&$listing.time_left}
								<p class="time_left">
									{if $headers.time_left.label}<em>{$headers.time_left.label}</em>{/if}
									{$listing.time_left}
								</p>
							{/if}

							{foreach $headers.leveled as $lev_id => $levels}
								{foreach $levels as $level => $levelHeader}
									<p class="{$levelHeader.css}">
										{if $levelHeader.label}<em>{$levelHeader.label}:</em>{/if}
										{$listing.leveled.$lev_id.$level.name}
									</p>
								{/foreach}
							{/foreach}

							{if $listing.addonData}
								{* let addons add columns if they want to *}
								{foreach $listing.addonData as $addonRows}
									{foreach $addonRows as $addonText}
										<p class="addon_data {$addonHeaders[$addon][$aKey].css}">
											{if $addonHeaders[$addon][$aKey].label}<em>{$addonHeaders[$addon][$aKey].label}:</em>{/if}
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
							<div class="clear"></div>
						</div>
					</article>
				{/if}

				{if $gallery_columns && ($listing@iteration%$gallery_columns==0||$listing@last)}</div>{/if}
			{/foreach}
			<div class="clear"></div>
		</div>
		<div class="galleryScroll" style="display: none;">
			<img src="{external file='images/buttons/left_arrow.png'}" alt="" class="leftScroll" />
			<img src="{external file='images/buttons/right_arrow.png'}" alt="" class="rightScroll" />
		</div>
	</div>
{/if}

