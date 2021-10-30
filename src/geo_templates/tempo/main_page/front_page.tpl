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

	</head>
	<body>
		{include file='header.tpl'}
		<div class="slideshow-container">
			<div class="home-contents-container">
				<div class="home-slogan">{$common_text.502430}<!-- Home Page Slogan -->
				</div>
				{if $enabledAddons.storefront}
				<div class="slideshow-button-container">
					<ul class="slideshow-button">
						<li id="active"><a href="index.php?a=ap&addon=storefront&page=list_stores" id="current"><span class="glyphicon glyphicon-tags"></span>&nbsp;&nbsp;{$common_text.502431}<!-- Browse Stores --></a></li>
					</ul>
				</div>
				{/if}
			</div>

			{* 
				NOTE: More preformatted images for the slideshow below are located in this template sets folder:
				external/ images/ showcase_slideshow/
			
				Or use your own images with a recommended image size of 1400px x 400px
			*}
			<div class="search_fade_box">
				
				<div class="gj_image_fade">
					<div style="display: none;"><img src="{external file='images/showcase_slideshow/home_tech.jpg'}" alt="" /></div>
					<div style="display: none;"><img src="{external file='images/showcase_slideshow/home_woman.jpg'}" alt="" /></div>
					<div style="display: none;"><img src="{external file='images/showcase_slideshow/home_coffee.jpg'}" alt="" /></div>
					<div style="display: none;"><img src="{external file='images/showcase_slideshow/home_dogs.jpg'}" alt="" /></div>
					<div style="display: none;"><img src="{external file='images/showcase_slideshow/home_couples.jpg'}" alt="" /></div>
					<div style="display: none;"><img src="{external file='images/showcase_slideshow/home_auto.jpg'}" alt="" /></div>
					<div style="display: none;"><img src="{external file='images/showcase_slideshow/home_realty.jpg'}" alt="" /></div>
					<div style="display: none;"><img src="{external file='images/showcase_slideshow/home_hat.jpg'}" alt="" /></div>
					<div style="display: none;"><img src="{external file='images/showcase_slideshow/home_horses.jpg'}" alt="" /></div>
					<div style="display: none;"><img src="{external file='images/showcase_slideshow/home_horseshoes.jpg'}" alt="" /></div>
					<div style="display: none;"><img src="{external file='images/showcase_slideshow/home_cats.jpg'}" alt="" /></div>
					<div style="display: none;"><img src="{external file='images/showcase_slideshow/home_guitar.jpg'}" alt="" /></div>
					<div style="display: none;"><img src="{external file='images/showcase_slideshow/home_jewelry.jpg'}" alt="" /></div>
					<div style="display: none;"><img src="{external file='images/showcase_slideshow/home_passport.jpg'}" alt="" /></div>
					<div style="display: none;"><img src="{external file='images/showcase_slideshow/home_food.jpg'}" alt="" /></div>					
					<div style="display: none;"><img src="{external file='images/showcase_slideshow/home_locks.jpg'}" alt="" /></div>
					<div style="display: none;"><img src="{external file='images/showcase_slideshow/home_skirts.jpg'}" alt="" /></div>
					<div style="display: none;"><img src="{external file='images/showcase_slideshow/home_camera.jpg'}" alt="" /></div>
					<div style="display: none;"><img src="{external file='images/showcase_slideshow/home_candy.jpg'}" alt="" /></div>
					<div style="display: none;"><img src="{external file='images/showcase_slideshow/home_concert.jpg'}" alt="" /></div>
					<div style="display: none;"><img src="{external file='images/showcase_slideshow/home_pencils.jpg'}" alt="" /></div>
				</div>

			</div>
		</div>
		<div class="rwd-hide">{addon author='geo_addons' addon='geographic_navigation' tag='breadcrumb'}</div>
		<div class="wrapper one-column">
			<!--[if lt IE 9]>
				<p class="browsehappy">You are using an <strong>outdated</strong> browser. Please <a href="http://browsehappy.com/">upgrade your browser</a> to improve your experience.</p>
			<![endif]-->
			<div class="main">
				<!-- MAIN CONTENTS OF PAGE -->

				<!-- FEATURED PC BEGIN -->
				<div class="content_box rwd-hide content_box_override">
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
					{module tag='module_featured_pic_1' module_number_of_columns=5 module_number_of_ads_to_display=25 dynamic_image_dims=1}
				</div>
				<!-- FEATURED PC END -->
				
				<!-- Hottest and Recent Half Columns Begin -->
				<div class="half_column_left normal-whitespace rwd-hide">
					<h2 class="title section-collapser"><span class="glyphicon glyphicon-fire"></span>&nbsp;{$common_text.502421}<!-- Hottest Listings --></h2>
					<div>
						{module tag='module_hottest_ads' browse_view='list' use_pagination=1}
					</div>
				</div>
				<div class="half_column_right rwd-hide">
					<h3 class="title section-collapser"><span class="glyphicon glyphicon-calendar"></span>&nbsp;{$common_text.502422}<!-- Recent Listings --></h3>
					<div>
						{module tag='newest_ads_1' browse_view='list' use_pagination=1}
					</div>
				</div>
				<!-- Hottest and Recent Half Columns END -->
			</div>
			<div class="sidebar">
				<!-- LEFT SIDEBAR CONTENTS BEGIN -->

				<div id="category_column cz-categories">
					<div id="left_categories">
						{category_list}
					</div>									
				</div>
				
				<!-- FEATURED MOBILE BEGIN -->
				<div class="content_box pc-hide content_box_override">
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
					{module tag='module_featured_pic_1' module_number_of_columns=3 module_number_of_ads_to_display=30}
					<div style="width: 100%; text-align:center; margin: 1em 0; text-transform: uppercase;"><a href="index.php?a=19&b[subcategories_also]=1">{$common_text.502432}<!-- More Listings --></a></div>
				</div>
				
				<!-- FEATURED MOBILE END -->
				
				<!-- START REGIONS -->
				<div class="rwd-hide button-hide" style="text-align:center;">
				{if $enabledAddons.geographic_navigation}
					{* Only show this section if the geographic navigation addon is set up *}
					<div class="content_box clearfix">
						<h2 class="title">
							<span class="glyphicon glyphicon-map-marker"></span>&nbsp;{$common_text.502402}<!-- Location -->:&nbsp;<span style="font-size: .8em; font-weight: normal;">{addon author='geo_addons' addon='geographic_navigation' tag='current_region'}&nbsp;
							{if $geographic_navigation_region}&nbsp;[&nbsp;<a href="{$classifieds_url}?region=0">{$common_text.502401}<!-- clear --></a>&nbsp;]{/if}</span>
						</h2>
						{addon author='geo_addons' addon='geographic_navigation' tag='navigation' columns=5} 
					</div>
				{/if}
				</div>
				<!-- END REGIONS -->				
								
				<!-- BANNER BEGIN -->
				{* Example place for 300x100 image banners *}
				<div class="content_box cntr banner content_box_override">	         
					<a href="index.php?a=28&b=136"><img src="{external file='images/banners/banner1_300x100.jpg'}" alt="Advertise" title="Advertise" /></a>
					<a href="http://www.hostmonster.com/track/geodesic/resources" target="_blank"><img src="{external file='images/banners/hostmonster_300x100.jpg'}" alt="HostMonster Hosting" title="HostMonster Hosting" /></a>
					<a href="http://lunarpages.com/id/geodesic/goto/basic" target="_blank"><img src="{external file='images/banners/lunarpages_300x100.jpg'}" alt="Lunarpages.com Hosting" title="Lunarpages.com Hosting" /></a>
				</div>
				<!-- BANNER END -->
			</div>
			
			{include file='footer.tpl'}
		</div>
	</body>
</html>