{* LISTING DETAILS TOP BEGIN *}

<div class="clearfix">
	{listing tag='category_breadcrumb'}
</div>

<div style="padding: 0 0.5rem;">
	<!-- NEXT-PREV LINKS BEGIN -->
	<div style="clear:both; text-align:center;">
		{listing tag='previous_ad_link'}&nbsp;
		{listing tag='next_ad_link'}
	</div>
	<!-- NEXT-PREV LINKS END -->
	<div class="listing_title" style="display: inline;">
	{$title}&nbsp;
	</div>
	<div style="display: inline; font-size: 0.6em; text-transform: uppercase; white-space:nowrap;">
		{$classified_id_label}&nbsp;{$classified_id}
	</div>
	<div class="action_buttons" style="display: inline;">
	{if $can_edit}
		<a href="{$classifieds_file_name}?a=cart&amp;action=new&amp;main_type=listing_edit&amp;listing_id={$classified_id}"><img src="{external file='images/buttons/listing_edit.gif'}" alt="" /></a>
	{/if}
	{if $can_delete}
		<a onclick="if (!confirm('Are you sure you want to delete this?')) return false;" href="{$classifieds_file_name}?a=99&amp;b={$classified_id}"><img src="{external file='images/buttons/listing_delete.gif'}" alt="" /></a>
	{/if}
	{listing tag='listing_action_buttons' addon='core'}
	</div>
	<div class="clear"> </div>
	{if $minimum_bid}
		<div class="value price" style="font-weight:bold; font-size: 1.25em; display:inline;">{$minimum_bid}
		</div>
	{/if}
	<div style="position:relative; display:inline; font-size: .8em;">[ <span class="glyphicon glyphicon-user"></span>&nbsp;<a onclick="jQuery('html,body').animate({ 'scrollTop': jQuery('#seller').offset().top }, 1000); return false;" href="#">{$seller_label}</a> ]</div>
</div>

{* LISTING DETAILS TOP END *}

{* CENTER COLUMN BEGIN *}

<div class="main listing_maincol">
	<h2 class="title" style="margin-bottom: .5em;"><img src="{external file='images/icons/Auction-24_FFFFFF.png'}" alt="" />&nbsp;&nbsp;{$additional_text_12}
		<span style="float:right;">
			{listing tag='print_friendly_link'}&nbsp;
			{listing tag='favorites_link'}&nbsp;
			{listing tag='notify_friend_link'}&nbsp;
			{if $enabledAddons.contact_us}
				<a href="{$classifieds_file_name}?a=ap&amp;addon=contact_us&amp;page=main&amp;reportAbuse={$classified_id}" class="lightUpLink">{$additional_text_10}</a>&nbsp;
			{/if}

			<span class="times-viewed">{$viewed_count_label} {$viewed_count}</span>
		</span>
	</h2>

	<div class="content_box_3 clearfix">
		{listing tag='image_block'}

		{listing tag='offsite_videos_block' assign='offsite_videos_block'}
		{if $offsite_videos_block}
			<div class="clear"></div>
			<h3 class="title"><span class="glyphicon glyphicon-film"></span>&nbsp;{$offsite_videos_title}</h3>
			{$offsite_videos_block}
			<div class="clear"></div>
		{/if}
	</div>

	{* Assign social buttons to $social so we can check if there are any before
		showing the section *}
	{listing tag='listing_social_simple_icons' addon='core' assign='social'}

	{if $social}
		<div class="faded_top">
			{$social}
		</div>
	{/if}

	<!-- SELLER PROFILE BEGIN -->
	<div class="user-profile-cntnr" id="seller">
		<div class="member-since-cntnr">
			{addon author='geo_addons' addon='social_connect' tag='facebook_listing_profile_picture' width=86 assign='fb_pic'}
			{if $fb_pic}
				<div class="member-fb">
					{$fb_pic}
				</div>
				<div class="member"><span class="date" style="color: #FFF; text-shadow: 0px 0px 5px #000000">{listing field='member_since'}</span></div>
			{else}
				<div class="member">{$additional_text_17}<br><span class="date">{listing field='member_since'}</span></div>
			{/if}
		</div>
		<div class="seller_username">{listing tag='seller'}</div>
		<div style="display:inline;">{$seller_rating_label}{listing tag='seller_rating'}{$seller_rating_scale_explanation}</div>
		{listing field='seller_phone' assign='seller_phone'}
		{if $seller_phone}
		<div>
			<span class="glyphicon glyphicon-phone-alt"></span>&nbsp; {$seller_phone}
		</div>
		{/if}
		{listing tag='sellers_other_ads_link' assign='other_listings_link'}
		{if $other_listings_link}
		<div>
			<span class="glyphicon glyphicon-list"></span>&nbsp; {$other_listings_link}
		</div>
		{/if}
		{if $payment_options}
		<div class="content_section" style="clear:both;">
			<strong>{$payment_options_label}</strong> {$payment_options}
		</div>
		{/if}

		{* Assign the storefront link to $storefront_link so we can check if it is
		"empty" or not before showing it...  To prevent an "empty" item in the list
		if there is no storefront link. *}
		{listing tag='storefront_link' addon='storefront' assign='storefront_link'}
		{if $storefront_link}
		{* The storefront link exists so show it! *}
		<div class="seller-profile-links">
			<span class="glyphicon glyphicon-tags"></span>&nbsp; {$storefront_link}
		</div>
		{/if}

		{* "Assign" contents of each url link to a smarty variable, so we can see
		if the link exists before adding the section *}
		{listing tag='url_link_1' assign='url_link_1'}
		{listing tag='url_link_2' assign='url_link_2'}
		{listing tag='url_link_3' assign='url_link_3'}
		{if $url_link_1 or $url_link_2 or $url_link_3 or $public_email}

			{* Only show section if there is at least one URL link or if there
			is public e-mail to show *}
			{if $url_link_1}
			<div class="seller-profile-links">
				<span class="glyphicon glyphicon-globe"></span>&nbsp;{$url_link_1}
			</div>
			{/if}
			{if $url_link_2}
			<div class="seller-profile-links">
				<span class="glyphicon glyphicon-globe"></span>&nbsp;{$url_link_2}
			</div>
			{/if}
			{if $url_link_3}
			<div class="seller-profile-links">
				<span class="glyphicon glyphicon-globe"></span>&nbsp;{$url_link_3}
			</div>
			{/if}
			{if $public_email}
			<div class="seller-profile-links">
				<span class="glyphicon glyphicon-envelope"></span>&nbsp; <a href="mailto:{$public_email}">{$public_email}</a>
			</div>
			{/if}
		{/if}
		<div style="clear:both;"> </div>
	</div>
	<!-- SELLER PROFILE END -->

	<!-- AUCTION BID INFO BEGIN -->
	<div class="content_box_1 clearfix" style="margin-bottom:1px;">
		<div>
			<ul class="info auction">
				{if $minimum_bid}
					<li class="label">{$auction_type_label}</li>
					<li class="value">{$auction_type_data} {$auction_type_help}</li>
				{/if}
				<li class="label">{$quantity_label}</li>
				<li class="value">{$quantity}</li>
				<!-- FIELDS BELOW GET HIDDEN IF BUY NOW ONLY AUCTION -->
				{if $minimum_bid}
					<li class="label">{$high_bidder_label}</li>
					<li class="value">{listing tag='high_bidder'}</li>
					<li class="label">{$num_bids_label}</li>
					<li class="value highlight_links">{$num_bids} {listing tag='bid_history_link'}</li>
				{/if}
				<!-- FIELDS ABOVE GET HIDDEN IF BUY NOW ONLY AUCTION -->
				{if $date_started_label and !$in_bidding_preview}
					<li class="label">{$date_started_label}</li>
					<li class="value">{listing field='date_started'}</li>
				{/if}

				{if $time_remaining_label and !$in_bidding_preview}
					<li class="label">{$time_remaining_label}</li>
					<li class="value">{listing tag='time_remaining'}<span class="sub_note">({$date_ended_label} {$date_ended})</span></li>
				{/if}


				{if $bid_start_date_label and $bid_start_date and $in_bidding_preview}
					<li class="label">{$bid_start_date_label}</li>
					<li class="value">{$bid_start_date}</li>
				{/if}
			</ul>
			{listing tag='multi_level_field_ul'}
		</div>
		<div class="clear"> </div>
		<div>
			<div class="buyer-options">
				{listing tag='buyer_option_selections'}
			</div>

			{if $bid_start_date and $in_bidding_preview}
			<div class="content_box_1 center" style="font-size: 1em; color:#FF0000;">{$additional_text_8}</div>
			{/if}

			<div class="bid_box">
				{if $minimum_bid}
					<div class="bid_amount">
						{$minimum_label}<br />
						<div class="value price" style="font-size: 1.6em;">{$minimum_bid}
						</div>
						<div>{listing tag='price_for_how_many'}</div>
						<div>{$reserve}</div>
					</div>
					<div class="bid_button">
						{listing tag='make_bid_link'}
					</div>
					<!-- <p class="bid_box_note">{$additional_text_15}</p> -->
				{/if}
				{if $minimum_bid and $buy_now_data}
				<div style="height: 10px; border-bottom: 1px solid #fff; width: 100%;">&nbsp; </div>
				{/if}
				{if $buy_now_data}
					<div class="buy_now_cntnr">
						<div class="bid_amount">
							{$buy_now_label}<br />
							<span class="value price" style="font-size: 1.6em;">{listing tag='buy_now_price_adjusted'}</span><br />
							{listing tag='price_for_how_many'}
						</div>
						<div class="bid_button">
							{listing tag='buy_now_link'}<span style="max-width: 50px;">{listing tag='on_site_purchase_link'}</span>
						</div>
					</div>
				{/if}
			</div>
		</div>
	</div>
	<!-- AUCTION BID INFO END -->

	<!-- TABBED DATA BEGIN -->
	<div class="row" style="margin: 1rem 0;">
		<div class="listing-content">
			<ul class="nav nav-tabs">
				<li class="active"><a href="#tab-summary" data-toggle="tab"><span class="glyphicon glyphicon-align-justify"></span> <span class="tab-title">{$messages.502424}<!-- Summary --></span></a></li>
				<li><a href="#tab-details" data-toggle="tab"><span class="glyphicon glyphicon-list"></span> <span class="tab-title">{$messages.502425}<!-- Details --></span></a></li>

				{listing tag='contact_seller_form' assign='contact_form'}
				{if $contact_form}
					<li><a href="#tab-contact" data-toggle="tab"><span class="glyphicon glyphicon-envelope"></span> <span class="tab-title">{$messages.502426}<!-- Contact --></span></a></li>
				{/if}

				{addon author='geo_addons' addon='google_maps' tag='listing_map' assign='map'}
				{if $map}
					<li id="map-tab-handle"><a href="#tab-map" id="tab_map_link" data-toggle="tab"><span class="glyphicon glyphicon-map-marker"></span> <span class="tab-title">{$messages.502427}<!-- Map --></span></a></li>
					{add_footer_html}
						<script>
							jQuery('#tab_map_link').on('shown.bs.tab', function() {
								var center = addon_google_maps.mapHook.getCenter();
								google.maps.event.trigger(addon_google_maps.mapHook, 'resize');
								addon_google_maps.mapHook.setCenter(center);
							});
						</script>
					{/add_footer_html}
				{/if}
			</ul>
			<div class="tab-content">

				<!-- SUMMARY TAB BEGIN -->
				<div class="tab-pane fade in active" id="tab-summary">
					<div class="table table-condensed table-hover">
							{$description}
					</div>
				</div>
				<!-- SUMMARY TAB END -->

				<!-- DETAILS TAB BEGIN -->
				<div class="tab-pane fade" id="tab-details">
					<table class="table table-condensed table-hover">
						<tbody>
							<div style="clear:both; margin-bottom: 10px; font-size: 1.2em;">
								<strong>{$classified_id_label}</strong> {$classified_id}
							</div>
							{listing tag='multi_level_field_ul' assign='multi_level'}
							{listing tag='extra_questions' assign='extra_questions'}
							{if $extra_questions or $multi_level}
								{if $extra_questions}
									{$extra_questions}
								{/if}
								{if $multi_level}
									{$multi_level}
								{/if}
							{/if}

							{* NOTE: The fields below can be turned on/off under LISTING SETUP > FIELDS TO USE >
							OPTIONAL FIELDS on a site-wide basis, and then further turned on/off on a category by
							category basis under CATEGORIES > [MANAGE] a Category > [FIELDS TO USE] > OPTIONAL FIELDS *}
							<div class="clearfix">
								<ul class="info">
									{if $optional_field_1}
									<li class="label">{$optional_field_1_label}</li>
									<li class="value">{$optional_field_1}</li>
									{/if}
									{if $optional_field_2}
									<li class="label">{$optional_field_2_label}</li>
									<li class="value">{$optional_field_2}</li>
									{/if}
									{if $optional_field_3}
									<li class="label">{$optional_field_3_label}</li>
									<li class="value">{$optional_field_3}</li>
									{/if}
									{if $optional_field_4}
									<li class="label">{$optional_field_4_label}</li>
									<li class="value">{$optional_field_4}</li>
									{/if}
									{if $optional_field_5}
									<li class="label">{$optional_field_5_label}</li>
									<li class="value">{$optional_field_5}</li>
									{/if}
									{if $optional_field_6}
									<li class="label">{$optional_field_6_label}</li>
									<li class="value">{$optional_field_6}</li>
									{/if}
									{if $optional_field_7}
									<li class="label">{$optional_field_7_label}</li>
									<li class="value">{$optional_field_7}</li>
									{/if}
									{if $optional_field_8}
									<li class="label">{$optional_field_8_label}</li>
									<li class="value">{$optional_field_8}</li>
									{/if}
									{if $optional_field_9}
									<li class="label">{$optional_field_9_label}</li>
									<li class="value">{$optional_field_9}</li>
									{/if}
									{if $optional_field_10}
									<li class="label">{$optional_field_10_label}</li>
									<li class="value">{$optional_field_10}</li>
									{/if}
									{if $optional_field_11}
									<li class="label">{$optional_field_11_label}</li>
									<li class="value">{$optional_field_11}</li>
									{/if}
									{if $optional_field_12}
									<li class="label">{$optional_field_12_label}</li>
									<li class="value">{$optional_field_12}</li>
									{/if}
									{if $optional_field_13}
									<li class="label">{$optional_field_13_label}</li>
									<li class="value">{$optional_field_13}</li>
									{/if}
									{if $optional_field_14}
									<li class="label">{$optional_field_14_label}</li>
									<li class="value">{$optional_field_14}</li>
									{/if}
									{if $optional_field_15}
									<li class="label">{$optional_field_15_label}</li>
									<li class="value">{$optional_field_15}</li>
									{/if}
									{if $optional_field_16}
									<li class="label">{$optional_field_16_label}</li>
									<li class="value">{$optional_field_16}</li>
									{/if}
									{if $optional_field_17}
									<li class="label">{$optional_field_17_label}</li>
									<li class="value">{$optional_field_17}</li>
									{/if}
									{if $optional_field_18}
									<li class="label">{$optional_field_18_label}</li>
									<li class="value">{$optional_field_18}</li>
									{/if}
									{if $optional_field_19}
									<li class="label">{$optional_field_19_label}</li>
									<li class="value">{$optional_field_19}</li>
									{/if}
									{if $optional_field_20}
									<li class="label">{$optional_field_20_label}</li>
									<li class="value">{$optional_field_20}</li>
									{/if}
								</ul>
							</div>

							{listing tag='extra_checkbox_name' assign='extra_checkbox_name'}
							{if $extra_checkbox_name}
							<div class="clr" style="margin: 10px; border-top: 1px solid #EEE; clear:both;">
								<div id="checkbox">
										{$extra_checkbox_name}
								</div>
							</div>
							<div class="clear"> </div>
							{/if}

						</tbody>
					</table>
				</div>
				<!-- DETAILS TAB END -->

				<!-- CONTACT SELLER TAB BEGIN -->
				{if $contact_form}
					<div class="tab-pane fade" id="tab-contact">
						<div class="table table-condensed table-hover">
							<div style="padding: 0; border: 1px solid #EEE;">
								{$contact_form}
							</div>
						</div>
					</div>
				{/if}
				<!-- CONTACT SELLER TAB END -->

				<!-- MAP TAB BEGIN -->
				{if $map}
					<div class="tab-pane fade" id="tab-map">
						<div class="table table-condensed table-hover">
							<!-- START GOOGLE MAPS -->
							<div class="clearfix">{$map}</div>
						</div>
					</div>
				{/if}
				<!-- MAP TAB END -->

			</div>
		</div>
	</div>
	<!-- TABBED DATA END -->

	<div class="clear"> </div>

	<!-- PUBLIC QUESTIONS BEGIN -->
	{if $usePublicQuestions}
		<h3 class="title">
			<span class="glyphicon glyphicon-question-sign"></span>&nbsp;{$publicQuestionsLabel}{if not $logged_in} - <a href="index.php?a=10&login_trackback=1" style="font-size: 0.8em; margin-bottom:3px; color: #FFF;">{$messages.502428}<!-- Login Required --></a>{/if}{if $logged_in} - <a href="{$classifieds_file_name}?a=13&amp;b={$classified_id}" style="font-size: 0.8em; margin-bottom:3px; color: #FFF;">{$askAQuestionText}</a>{/if}
		</h3>
		<div class="content_box_1">
			{if $publicQuestions}
				{foreach from=$publicQuestions key='question_id' item='q'}
					{if $q.answer !== false}
						<div class="publicQuestions {cycle values='row_odd,row_even'}">
							<div class="question">
								<span class="public_question_asker_username"><a href="{$classifieds_file_name}?a=6&amp;b={$q.asker_id}">{$q.asker}</a></span>
								<span class="public_question_asker_timestamp">({$q.time})</span>
								{if $can_delete}<a onclick="if (!confirm('Are you sure you want to remove this question and its answer?')) return false;" href="{$classifieds_file_name}?a=4&amp;b=8&amp;c=2&amp;d={$question_id}&amp;public=1"><img src="{external file='images/buttons/listing_delete.gif'}" alt="" /></a> {/if}
								<br />
								{$q.question}
							</div>
							<div class="answer">
								{$q.answer}
							</div>
						</div>
					{/if}
				{/foreach}
			{else}
				<div class="box_pad" style="font-size: 0.8em; text-align:center; font-weight: bold;">{$noPublicQuestions}</div>
			{/if}
		</div>
	{/if}
	<!-- PUBLIC QUESTIONS END -->

</div>

{* CENTER COLUMN END *}

{* LEFT SIDEBAR BEGIN *}

<div class="sidebar listing_leftcol">
	<!-- LEAD PIC AND PRICE BEGIN -->
	<div class="lead_pic_1">
		<div class="content_box_1 cntr">
			{listing tag='lead_picture'}
			{if $minimum_label}
				<p>
					{$minimum_label}<br />
					<span class="value price" style="font-size:1.4em;">{$minimum_bid}</span>
				</p>
				<p>{$reserve}</p>
			{/if}
		</div>
	</div>
	<!-- LEAD PIC AND PRICE END -->

	<!-- LISTING POPULARITY BEGIN -->
	<h2 class="title">
		<span class="glyphicon glyphicon-thumbs-up"></span> &nbsp;{$additional_text_6}
	</h2>
	<div class="content_box_2">
		{* Assign vote total to $vote_total so can use it to determine whether
			to show the current vote info section *}
		{listing tag='voteSummary_total' assign='vote_total'}

			{* only show if there are already votes on the listing *}
			<div class="cntr" style="margin:5px 0 0 0;">
				<span style="font-weight: bold; white-space:nowrap;">{listing tag='voteSummary_text'}&nbsp;{listing tag='voteSummary_percent'}%</span>
			</div>

		<div class="cntr" style="font-size: 0.8em;">
			<!-- Space the links apart -->
			<div style="display: inline-block; padding: 8px;">
				{listing tag='vote_on_ad_link'}
			</div>
			<div style="display: inline-block; padding: 8px;">
				{listing tag='show_ad_vote_comments_link'}
			</div>
		</div>
	</div>
	<!-- LISTING POPULARITY END -->

	<!-- TAGS BEGIN -->
	{if $listing_tags_array}
		{* only show section if there are listing tags on this listing *}
		<h3 class="title">
			<span class="glyphicon glyphicon-tags"></span> &nbsp;{$listing_tags_label}
		</h3>
		<div class="content_box_2" style="font-size: 0.8em;">
			<div class="content_section" style="text-align:center; padding: 0.5em 0;">
				{listing tag='listing_tags_links'}
			</div>
		</div>
	{/if}
	<!-- TAGS END -->

	<!-- FEATURED LISTINGS BEGIN -->
	<h2 class="title">
		<span class="glyphicon glyphicon-star" style="font-size: .8em;"></span>&nbsp;{$additional_text_2}
	</h2>
	<div class="content_box_1">
		{*
			NOTE: In order to show featured listings in a single column, the {module} tag
			below includes a number of parameters that over-write the
			module settings set in the admin.  You must change those
			settings "in-line" below to change them.

			Or, you can remove the parameter(s) from the {module}
			tag completely, and it will use the module settings
			as set in the admin panel.

			See the user manual entry for the {module} tag for
			a list of all parameters that can be over-written in
			this way.
		 *}
		{module tag='module_featured_pic_1' gallery_columns=1 module_thumb_width=168 module_thumb_height=200}
	</div>
	<!-- FEATURED LISTINGS END -->
</div>

{* LEFT SIDEBAR END *}

{* RIGHT SIDEBAR BEGIN *}

<div class="sidebar2">
	<!-- ADVERTISEMENT BEGIN -->
	<h4 class="title">{$additional_text_3}</h4>
	<div class="content_box_3 cntr banner">
		<a href="https://geodesicsolutions.org/" target="_blank"><img src="{external file='images/banners/sample_300x100.jpg'}" alt="Sample Ad Banner" title="Sample Ad Banner" /></a>
		<a href="https://geodesicsolutions.org/" target="_blank"><img src="{external file='images/banners/sample_300x100.jpg'}" alt="Sample Ad Banner" title="Sample Ad Banner" /></a>
		<a href="https://geodesicsolutions.org/" target="_blank"><img src="{external file='images/banners/sample_300x100.jpg'}" alt="Sample Ad Banner" title="Sample Ad Banner" /></a>
	</div>
    <!-- ADVERTISEMENT END -->
</div>

{* RIGHT SIDEBAR END *}

{* NEXT-PREV LINKS BEGIN *}

<div class="clr"></div>
<div style="clear:both; text-align:center;">
	{listing tag='previous_ad_link'}&nbsp;
	{listing tag='next_ad_link'}
</div>

{* NEXT-PREV LINKS END *}
