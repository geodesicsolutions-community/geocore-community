<!DOCTYPE HTML>
<html class="design2016">
<head>
	<title>{$title}</title>
	
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta http-equiv="Content-Style-Type" content="text/css" />
	<meta name="Keywords" content="KEYWORDS GO HERE" />
	<meta name="Description" content="DESCRIPTION GOES HERE" />
	
	{head_html}
	
	<link href="{external file='css/signs_flyers.css'}" rel="stylesheet" type="text/css" />
</head>
<body class="print_body">
	<div class="print_shell">
		<!-- START HEADER -->
		<div id="header">
			<img src="{external file='images/logo.jpg'}" alt="" />
			
		</div>
		<!-- END HEADER -->
		
		<div class="clr"></div>
		
		<nav class="breadcrumb">
			<div>{$site_name}</div>
			<div>{$addon_text.tpl_search_listing_id} {$classified_id}</div>
		</nav>
		
		<h1 class="listing_title">
			{$title}
		</h1>
		
		<div id="print_photo_column">
			{$image}
		</div>
		
		<div id="print_listing_info_column">
			<ul class="info">
				<li class="label price">{$price}</li>
				<li class="label">{$address}</li>
				<li class="label">{$city}, {$state} {$zip}</li>
				<li class="label">{$phone_1}</li>
				<li class="label">{$contact}</li>	
			</ul>
			<div class="clr"><br /></div>
		</div>

		<div id="print_listing_info_column">
			<ul class="info">
                                {if $optional_field_1}
				<li class="label">{$optional_field_1}</li>
                                {/if}
                                {if $optional_field_2}
				<li class="label">{$optional_field_2}</li>
                                {/if}
                                {if $optional_field_3}
				<li class="label">{$optional_field_3}</li>
                                {/if}
                                {if $optional_field_4}
				<li class="label">{$optional_field_4}</li>
                                {/if}
                                {if $optional_field_5}
				<li class="label">{$optional_field_5}</li>
                                {/if}
                                {if $optional_field_6}
				<li class="label">{$optional_field_6}</li>
                                {/if}
                                {if $optional_field_7}
				<li class="label">{$optional_field_7}</li>
                                {/if}
                                {if $optional_field_8}
				<li class="label">{$optional_field_8}</li>
                                {/if}
                                {if $optional_field_9}
				<li class="label">{$optional_field_9}</li>
                                {/if}
                                {if $optional_field_10}
				<li class="label">{$optional_field_10}</li>
                                {/if}
                                 {if $optional_field_11}
				<li class="label">{$optional_field_11}</li>
                                {/if}
                                {if $optional_field_12}
				<li class="label">{$optional_field_12}</li>
                                {/if}
                                {if $optional_field_13}
				<li class="label">{$optional_field_13}</li>
                                {/if}
                                {if $optional_field_14}
				<li class="label">{$optional_field_14}</li>
                                {/if}
                                {if $optional_field_15}
				<li class="label">{$optional_field_15}</li>
                                {/if}
                                {if $optional_field_16}
				<li class="label">{$optional_field_16}</li>
                                {/if}
                                {if $optional_field_17}
				<li class="label">{$optional_field_17}</li>
                                {/if}
                                {if $optional_field_18}
				<li class="label">{$optional_field_18}</li>
                                {/if}
                                {if $optional_field_19}
				<li class="label">{$optional_field_19}</li>
                                {/if}
                                {if $optional_field_20}
				<li class="label">{$optional_field_20}</li>
                                {/if}
			</ul>
			<div class="clr"><br /></div>
		</div>
		<div class="clr"><br /></div>		

		<div id="print_description" style="margin-bottom: 10px;">		
			<div class="main_text">
				{$description}
			</div>
		</div>

		<!-- START LEFT TABS -->
		
		<div class="print_half_column_left">
			
			<ul id="print_optional_fields">
			<li class="rows left"><strong>{$title}</strong> :: {$phone_1} <br>
                        {$site_name} :: # {$classified_id}</li>
			<li class="rows left"><strong>{$title}</strong> :: {$phone_1} <br>
                        {$site_name} :: # {$classified_id}</li>
			<li class="rows left"><strong>{$title}</strong> :: {$phone_1} <br>
                        {$site_name} :: # {$classified_id}</li>
			<li class="rows left"><strong>{$title}</strong> :: {$phone_1} <br>
                        {$site_name} :: # {$classified_id}</li>
			<li class="rows left"><strong>{$title}</strong> :: {$phone_1} <br>
                        {$site_name} :: # {$classified_id}</li>
			<li class="rows left"><strong>{$title}</strong> :: {$phone_1} <br>
                        {$site_name} :: # {$classified_id}</li>
			<li class="rows left"><strong>{$title}</strong> :: {$phone_1} <br>
                        {$site_name} :: # {$classified_id}</li>
			<li class="rows left"><strong>{$title}</strong> :: {$phone_1} <br>
                        {$site_name} :: # {$classified_id}</li>
			<li class="rows left"><strong>{$title}</strong> :: {$phone_1} <br>
                        {$site_name} :: # {$classified_id}</li>
			</ul>

		</div>
		<!-- END LEFT TABS -->
		
		<!-- START RIGHT TABS -->
		
		<div class="print_half_column_right">
			
			<ul id="print_optional_fields">
			<li class="rows right"><strong>{$title}</strong> :: {$phone_1} <br>
                        {$site_name} :: # {$classified_id}</li>
			<li class="rows right"><strong>{$title}</strong> :: {$phone_1} <br>
                        {$site_name} :: # {$classified_id}</li>
			<li class="rows right"><strong>{$title}</strong> :: {$phone_1} <br>
                        {$site_name} :: # {$classified_id}</li>
			<li class="rows right"><strong>{$title}</strong> :: {$phone_1} <br>
                        {$site_name} :: # {$classified_id}</li>
			<li class="rows right"><strong>{$title}</strong> :: {$phone_1} <br>
                        {$site_name} :: # {$classified_id}</li>
			<li class="rows right"><strong>{$title}</strong> :: {$phone_1} <br>
                        {$site_name} :: # {$classified_id}</li>
			<li class="rows right"><strong>{$title}</strong> :: {$phone_1} <br>
                        {$site_name} :: # {$classified_id}</li>
			<li class="rows right"><strong>{$title}</strong> :: {$phone_1} <br>
                        {$site_name} :: # {$classified_id}</li>
			<li class="rows right"><strong>{$title}</strong> :: {$phone_1} <br>
                        {$site_name} :: # {$classified_id}</li>

			</ul>

		</div>
		
		<!-- END LEFT TABS -->
		
		<div class="clr"></div>
	</div>
	{footer_html}
</body>
</html>
