<!DOCTYPE html>
<html class="design2016">
<head>
	<title>{$title|strip_tags:false}</title>
	
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta name="Keywords" content="KEYWORDS GO HERE" />
	<meta name="Description" content="DESCRIPTION GOES HERE" />
	
	{head_html}
	
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
		
		<h1 class="listing_title" style="color: #000;">{$title}
			<span class="id">{$classified_id_label} {$classified_id}</span>
		</h1>
		
		<div id="print_photo_column">
			{listing tag='lead_picture'}
			<span style="font-size: 2.0em; font-weight: bold;">{$price}</span><br />
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
			<br />
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
			<div class="print_title">{$description_label}</div>	
			<div id="print_description" style="margin: 5px 0;">	
				<div>
					{$description}
				</div>
			</div>
			{listing tag='multi_level_field_ul' assign='multi_level'}
			{if $multi_level}
				<div id="print_multi_level_fields">
					<br /><br />
					{$multi_level}
					<div class="clr"></div>
				</div>
			{/if}
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
