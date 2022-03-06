<!DOCTYPE html>
<html class="design2016">
<head>
	<title>{$title|strip_tags:false}</title>
	
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta name="Keywords" content="KEYWORDS GO HERE" />
	<meta name="Description" content="DESCRIPTION GOES HERE" />
	
	{head_html}
	
	{* HTML5 compatibility for browsers before IE 9. *}
	<!--[if lt IE 9]>
		<meta http-equiv="X-UA-Compatible" content="IE=Edge">
	<![endif]-->
</head>
<body class="print_body">
	<div class="print_shell">
		<!-- START HEADER -->
		<div id="header" style="text-align:center;">
			<img src="{external file='images/logo.jpg'}" alt="" />
			<div class="print_top_text">{$site_name}</div>
		</div>
		<!-- END HEADER -->
		
		<div class="clr"></div>
		
		{listing tag='category_breadcrumb'}
		
		
		<h3 class="listing_title" style="color:#000000;">{$title}
			<span class="id">{$classified_id_label} {$classified_id}</span>
			
		</h3>
		
		<div id="print_photo_column">
			{listing tag='lead_picture'}
			<span style="font-size: 1.4em; font-weight: bold;">{listing tag='seller'}</span><br />
			{if $phone_data}
				{$phone_data}<br />
			{/if}
			{if $city_data}
				{$city_data}
			{/if}
			{if $state_data}
				{$state_data}
			{/if}
			{if $zip_data}
				{$zip_data}
			{/if}
			{if $payment_options_label}
				<br /><strong>{$payment_options_label}</strong>
				<br />{$payment_options}
			{/if}
			{if $public_email}
				<br />
				{$public_email}
			{/if}
			<br /><br />
			{$classifieds_url|cat:"?a=2&b={$classified_id}"|qr_code:190}
		</div>
	
		<div id="print_listing_info_column">
			<div id="print_description">
				<h3 class="print_title">{$description_label}</h3>		
				<div>
					{$description}
				</div>
			</div>
			
			<ul class="info">
				<li class="label">{$auction_type_label}</li>
				<li class="value">{$auction_type_data} {$auction_type_help}</li>
				<li class="label">{$quantity_label}</li>
				<li class="value">{$quantity}</li>
				
				<li class="label">{$high_bidder_label}</li>
				<li class="value">{$high_bidder}</li>
				
				<li class="label">{$num_bids_label}</li>
				<li class="value highlight_links">{$num_bids}</li>
				
				<li class="label">{$starting_label}</li>
				<li class="value">{$starting_bid}</li>
							
				{if $optional_field_20}
					<li class="label">{$optional_field_20_label}</li>
					<li class="value">{$optional_field_20}</li>
				{/if}
				
				<li class="label">{$time_remaining_label}</li>
				<li class="value">{$time_remaining}</li>
			</ul>
			{listing tag='multi_level_field_ul'}
			
			{listing tag='extra_question_value' assign='extra_question_value'}
			{if $extra_question_value}
				<div id="print_extra_questions">
					<div class="label">
						{listing tag='extra_question_name'}
					</div>
					<div class="data">
						{$extra_question_value}
					</div>
				</div>
			{/if}
			
			<ul id="print_optional_fields">
				{if $optional_field_1}
					<li class="row_odd"><label>{$optional_field_1_label}</label>{$optional_field_1}</li>
				{/if}
				{if $optional_field_2}
					<li class="row_odd"><label>{$optional_field_2_label}</label>{$optional_field_2}</li>
				{/if}
				{if $optional_field_3}
					<li class="row_odd"><label>{$optional_field_3_label}</label>{$optional_field_3}</li>
				{/if}
				{if $optional_field_4}
					<li class="row_odd"><label>{$optional_field_4_label}</label>{$optional_field_4}</li>
				{/if}
				{if $optional_field_5}
					<li class="row_odd"><label>{$optional_field_5_label}</label>{$optional_field_5}</li>
				{/if}
				{if $optional_field_6}
					<li class="row_odd"><label>{$optional_field_6_label}</label>{$optional_field_6}</li>
				{/if}
				{if $optional_field_7}
					<li class="row_odd"><label>{$optional_field_7_label}</label>{$optional_field_7}</li>
				{/if}
				{if $optional_field_8}
					<li class="row_odd"><label>{$optional_field_8_label}</label>{$optional_field_8}</li>
				{/if}
				{if $optional_field_9}
					<li class="row_odd"><label>{$optional_field_9_label}</label>{$optional_field_9}</li>
				{/if}
				{if $optional_field_10}
					<li class="row_odd"><label>{$optional_field_10_label}</label>{$optional_field_10}</li>
				{/if}
				{if $optional_field_11}
					<li class="row_odd"><label>{$optional_field_11_label}</label>{$optional_field_11}</li>
				{/if}
				{if $optional_field_12}
					<li class="row_odd"><label>{$optional_field_12_label}</label>{$optional_field_12}</li>
				{/if}
				{if $optional_field_13}
					<li class="row_odd"><label>{$optional_field_13_label}</label>{$optional_field_13}</li>
				{/if}
				{if $optional_field_14}
					<li class="row_odd"><label>{$optional_field_14_label}</label>{$optional_field_14}</li>
				{/if}
				{if $optional_field_15}
					<li class="row_odd"><label>{$optional_field_15_label}</label>{$optional_field_15}</li>
				{/if}
				{if $optional_field_16}
					<li class="row_odd"><label>{$optional_field_16_label}</label>{$optional_field_16}</li>
				{/if}
				{if $optional_field_17}
					<li class="row_odd"><label>{$optional_field_17_label}</label>{$optional_field_17}</li>
				{/if}
				{if $optional_field_18}
					<li class="row_odd"><label>{$optional_field_18_label}</label>{$optional_field_18}</li>
				{/if}
				{if $optional_field_19}
					<li class="row_odd"><label>{$optional_field_19_label}</label>{$optional_field_19}</li>
				{/if}
			</ul>	
			<div class="clr"><br /></div>
		</div>
		<div class="clr"></div>		
		<br />
		
		{* START CHECKBOXES *}
		{listing tag='extra_checkbox_name' assign='extra_checkbox_name'}
		{if $extra_checkbox_name}
			<div id="checkbox" style="margin: 0 0 0 30px;">
				{$extra_checkbox_name}
				<div class="clr"></div>
			</div>
			<br />
		{/if}
		
		{* END CHECKBOXES *}
		
		<div class="clr"></div>
	</div>
	{footer_html}
</body>
</html>
