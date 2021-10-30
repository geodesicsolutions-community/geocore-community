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
	<div class="print_shell_sign">
		<!-- START HEADER -->
		<div class="for_sale">
			{$addon_text.tpl_for_sale}
		</div>

		<!-- END HEADER -->
		
		<div class="clr"></div>
		
		<nav class="breadcrumb">
			<div>{$site_name}</div>
			<div>{$addon_text.tpl_search_listing_id} {$classified_id}</div>
		</nav>
		
		<h1 class="listing_title_sign">
			{$title}
		</h1>
		
		<div id="print_photo_column">
			{$image}
		</div>
		
		<div id="print_listing_info_column">
			<ul class="info">
				<li class="contact">{$price}<br>
                                {$phone_1}<br>
				{$contact}</li>
			</ul>

			<div class="clr"><br /></div>
		</div>
		<div id="print_listing_info_column">
                <div class="main_text">{$description}</div>
		</div>
		<div class="clr"><br /></div>		
	</div>
	{footer_html}
</body>
</html>
