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
		<div class="wrapper three-column">
			{include file='header.tpl'}
			<!--[if lt IE 9]>
				<p class="browsehappy">You are using an <strong>outdated</strong> browser. Please <a href="http://browsehappy.com/">upgrade your browser</a> to improve your experience.</p>
			<![endif]-->

			<div class="front-page">
				<!-- LEFT COLUMN BEGIN -->
				<div class="sidebar rwd-categories">
					<div id="category_column">
						<div id="left_categories">
							<h2 class="title categories"><span class="glyphicon glyphicon-list-alt"></span>&nbsp;&nbsp;{$common_text.502435}<!-- Categories --></h2>
							<!-- FANCY CATEGORY DISPLAY BEGIN -->
							{category_list}
							<!-- FANCY CATEGORY DISPLAY END -->

							<!-- SIMPLE LINK CATEGORY DISPLAY BEGIN
							{module tag='classified_navigation_1'}
							SIMPLE LINK CATEGORY DISPLAY END -->
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

					</div>
				</div>
				<!-- LEFT COLUMN END -->

				<!-- MAIN COLUMN BEGIN -->
				<div class="main">

					<!-- FEATURED CAROUSEL BEGIN -->
					<div class="clr"> </div>
					<div class="content_box gj_simple_carousel border">
						<h3 class="title"><span class="glyphicon glyphicon-star"></span>&nbsp;&nbsp;{$common_text.502419}<!-- Featured Listings --></h3>
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
						{module tag='module_featured_pic_1' dynamic_image_dims=1 gallery_columns=4 module_thumb_width=160}
					</div>
					<!-- FEATURED CAROUSEL END -->
					<div style="width: 100%;">
						<div class="clearfix">
							<div class="half_column_left hottest_ads normal-whitespace">
								<div class="content_box border2">
									<h2 class="title"><span class="glyphicon glyphicon-fire"></span>&nbsp;&nbsp;{$common_text.502421}<!-- Hottest Listings --></h2>
									{module tag='module_hottest_ads' browse_view='list' use_pagination=1}
								</div>
							</div>

							<div class="half_column_right recent_ads normal-whitespace">
								<div class="content_box border">
									<h3 class="title"><span class="glyphicon glyphicon-calendar"></span>&nbsp;&nbsp;{$common_text.502422}<!-- Recent Listings --></h3>
									{module tag='newest_ads_1' browse_view='list' use_pagination=1}
								</div>
							</div>
						</div>

						<div class="content_box rwd-hide border">
							{module tag='featured_ads_1'}
						</div>
					</div>

				</div>
				<!-- MAIN COLUMN END -->

				<!-- RIGHT COLUMN BEGIN -->
				<div class="sidebar2 cntr">
					<!--
					<div class="content_box">
						<a href="index.php?a=1"><img src="{external file='images/buttons/place_listing.gif'}" alt="Place A Listing" title="Place A Listing" /></a>
					</div>
					<br />
					-->
					<div class="content_box stats">
						<h2 class="title">{$common_text.502437}<!-- Site Stats --></h2>
						{module tag='module_total_live_users'}
						<div style="font-size: 0.75em; font-weight: bold; display:inline-block;">{$common_text.502436}<!-- Registered Users -->: </div><div style="display:inline-block;">{module tag='module_total_registered_users'}</div>
					</div>
					<h4 class="title">{$common_text.502438}<!-- Advertisement --></h4>
					<div class="content_box_3 cntr banner">
                		<a href="https://geodesicsolutions.org/" target="_blank"><img src="{external file='images/banners/sample_300x100.jpg'}" alt="Sample Ad Banner" title="Sample Ad Banner" /></a>
		                <a href="https://geodesicsolutions.org/" target="_blank"><img src="{external file='images/banners/sample_300x100.jpg'}" alt="Sample Ad Banner" title="Sample Ad Banner" /></a>
                		<a href="https://geodesicsolutions.org/" target="_blank"><img src="{external file='images/banners/sample_300x100.jpg'}" alt="Sample Ad Banner" title="Sample Ad Banner" /></a>
					</div>
					<br />
					<div>
						<!-- EDIT THE FOLLOWING LINE OF CODE WITH YOUR BANNER OR ADSENSE CODE
						<a href="https://geodesicsolutions.org/wiki/tutorials/using_a_banner_system/adsense/" target="_blank" rel="nofollow"><img src="{external file='images/banners/banner1_adsense_200x200.jpg'}" alt="Banner Example" title="Banner Example" width="200" height="200" /></a>
						EDIT THE ABOVE LINE OF CODE WITH YOUR BANNER OR ADSENSE CODE -->
					</div>
				</div>
				<!-- RIGHT COLUMN END -->
			</div>

			{include file="footer.tpl"}

		</div>
	</body>
</html>
