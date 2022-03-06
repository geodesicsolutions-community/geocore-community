<!DOCTYPE html>
{*
     IMPORTANT - TEXT ENTRIES ARE NO LONGER EDITED IN THIS TEMPLATE, UNLESS YOU ARE ADDING YOUR OWN TEXT. SYSTEM
     TEXT IS NOW LOCATED ENTIRELY IN THE PAGES MANAGEMENT MENU. ANY TEXT REFERENCES YOU SEE BELOW ARE SIMPLY
     "NOTES" AND ARE FOR INFORMATION PURPOSES ONLY TO HELP YOU IDENTIFY THE TEXT'S TAG IF YOU WANT TO MOVE IT.
     MOST SYSTEM TEXT FOR THIS PARTICULAR TEMPLATE CAN BE FOUND AND EDITED AT THE FOLLOWING LOCATION: 
     PAGES MANAGEMENT > GENERAL TEMPLATE TEXT > COMMON TEMPLATE TEXT 
*}
<html class="no-js design2016">
	<head>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<title>{module tag='module_title'}</title>
		<meta name="description" content="">
		<meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no">

		{head_html}
		
		<!--  This loads the RSS feed  -->
		<link rel="alternate" type="application/rss+xml" title="Newest Listing Feed" href="rss_listings.php" />
		
		{include file='head_common.tpl'}
		<script src="js/modernizr-2.6.2.min.js"></script>
		<script>Modernizr.load({ test: Modernizr.mq('only all'),nope:'{external file="css/old_ie.css"}'});</script>
	</head>
	<body>
		<div class="clean-categories">
			<div class="wrapper one-column">
				{include file='header.tpl'}
				<!--[if lt IE 9]>
					<p class="browsehappy">You are using an <strong>outdated</strong> browser. Please <a href="http://browsehappy.com/">upgrade your browser</a> to improve your experience.</p>
				<![endif]-->
				<div class="main">
										
					<!-- LEFT COLUMN BEGIN -->
					<div class="sidebar rwd-categories">
						<div id="category_column">
							<div id="left_categories">
								<!-- FANCY CATEGORY DISPLAY BEGIN -->
								{category_list}
								<!-- FANCY CATEGORY DISPLAY END -->
								
								<!-- SIMPLE LINK CATEGORY DISPLAY BEGIN 
								{module tag='classified_navigation_1'}
								SIMPLE LINK CATEGORY DISPLAY END -->
							</div>
				
							
						</div>
					</div>	
					<!-- LEFT COLUMN END -->
					
					<!-- MAIN COLUMN BEGIN -->	
					<div class="newest-ads-links" style="text-align: right;">
						<strong>{$common_text.502418}:<!-- Recent --></strong> &nbsp;
						{module tag='newest_ads_link' buttonStyle=1}
						{module tag='newest_ads_link_1' buttonStyle=1}
						{module tag='newest_ads_link_2' buttonStyle=1}
						{module tag='newest_ads_link_3' buttonStyle=1}
					</div>
					<div class="clearfix rwd-hide">
						{body_html}
					</div>
					
					<!-- FEATURED CAROUSEL BEGIN -->
					<div class="content_box gj_simple_carousel rwd-hide">
						{* 
							NOTE: In order to show the module in a way that will fit in
							the layout for this page, the {module} tag
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
						{module tag='module_featured_pic_1' gallery_columns=4 dynamic_image_dims=1}
					</div>
					<!-- FEATURED CAROUSEL END -->

					<div class="clearfix">
						<div class="half_column_left">
							<div class="content_box">
								<h2 class="title">{$common_text.502421}<!-- Hottest Listings --></h2>
								{module tag='module_hottest_ads'}
							</div>
						</div>
						<div class="half_column_right">
							<div class="content_box">
								<h3 class="title">{$common_text.502422}<!-- Recent Listings --></h3>
								{module tag='newest_ads_1'}
							</div>
						</div>
					</div>
					
					<!-- START REGIONS -->
					<div class="rwd-hide">
						{if $enabledAddons.geographic_navigation}
						{* Only show this section if the geographic navigation addon is set up *}
						<div class="content_box clearfix region-link">
							<h3 class="title">
								<span class="glyphicon glyphicon-map-marker"></span>&nbsp;{$common_text.502402}<!-- Location -->:&nbsp;<span style="font-size: .8em; font-weight: normal;">{addon author='geo_addons' addon='geographic_navigation' tag='current_region'}&nbsp;
								{if $geographic_navigation_region}&nbsp;[&nbsp;<a href="{$classifieds_url}?region=0" style="color: #FFF;">{$common_text.502401}<!-- clear --></a>&nbsp;]{/if}</span>
							</h3>
							{addon author='geo_addons' addon='geographic_navigation' tag='navigation'} 
						</div>
						{/if}
					</div>
					<!-- END REGIONS -->
					
					<!-- MAIN COLUMN END -->	
					
				</div>
				{include file="footer.tpl"}
			</div>
		</div>
	</body>
</html>