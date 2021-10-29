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
		<div class="wrapper two-column">
			{include file='header.tpl'}
			<!--[if lt IE 9]>
				<p class="browsehappy">You are using an <strong>outdated</strong> browser. Please <a href="http://browsehappy.com/">upgrade your browser</a> to improve your experience.</p>
			<![endif]-->

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
								{if $geographic_navigation_region}&nbsp;[&nbsp;<a href="index.php?region=0" style="color: #FFF;">{$common_text.502401}<!-- clear --></a>&nbsp;]{/if}</span>
							</h3>
							{addon author='geo_addons' addon='geographic_navigation' tag='navigation'} 
						</div>
					{/if}
					</div>
					<!-- END REGIONS -->
					
				</div>
				<div class="content_box stats border">
					<h3 class="title" style="padding-left: 0;"><span class="glyphicon glyphicon-stats"></span>&nbsp;&nbsp;{$common_text.502437}<!-- Site Stats --></h3>
					{module tag='module_total_live_users'}
					<div style="font-size: 0.75em; font-weight: bold; display:inline-block; line-height: 2.75em;">{$common_text.502436}<!-- Registered Users -->: </div><div style="display:inline-block;">{module tag='module_total_registered_users'}</div>
				</div>
			</div>	
			<!-- LEFT COLUMN END -->	
			
			<!-- MAIN COLUMN BEGIN -->				
			<div class="main">
	
				<!-- SEARCH FORM BOXES BEGIN -->
				<div class="search_column_left">

					<!-- form input field begin -->						
					<div class="search-box-container">
						<form>
							<div class="form-wrapper cf">
								<input type="hidden" name="a" value="19" />
								<input type="hidden" name="b[subcategories_also]" value="1" />
								<input class="keyword" type="text" placeholder="{$common_text.502412}" name="b[search_text]" /><!-- Search Keywords -->
								<button type="submit"><span class="glyphicon glyphicon-search"></span></button>
							</div>
							
							{if $enabledAddons.zipsearch}
							<div class="cntr" style="font-size:.8em; margin: 10px 0;">
								<div class="zipbox">
									{$common_text.502433}<!-- Within -->:
									<select class="field" name="b[by_zip_code_distance]">
										<option value="1">1 {$common_text.502413}<!-- mile --><!-- Use .502415 for kilometer --></option>
										<option value="5">5 {$common_text.502414}<!-- miles --><!-- Use .502416 for kilometers --></option>
										<option value="10">10 {$common_text.502414}<!-- miles --></option>
										<option value="15">15 {$common_text.502414}<!-- miles --></option>
										<option value="20">20 {$common_text.502414}<!-- miles --></option>
										<option value="25">25 {$common_text.502414}<!-- miles --></option>
										<option value="30">30 {$common_text.502414}<!-- miles --></option>
										<option value="40">40 {$common_text.502414}<!-- miles --></option>
										<option value="50">50 {$common_text.502414}<!-- miles --></option>
										<option value="75">75 {$common_text.502414}<!-- miles --></option>
										<option value="100">100 {$common_text.502414}<!-- miles --></option>
										<option value="200">200 {$common_text.502414}<!-- miles --></option>
										<option value="300">300 {$common_text.502414}<!-- miles --></option>
										<option value="400">400 {$common_text.502414}<!-- miles --></option>
										<option value="500">500 {$common_text.502414}<!-- miles --></option>
									</select>
									{$common_text.502434}<!-- of -->
									<input id="by_zip_code" class="field" type="text" size="10" name="b[by_zip_code]" value=""  placeholder="{$common_text.502417}" /><!-- Postal Code -->
								</div>
							</div>
							{else}
							<div class="spacer">&nbsp;</div>
							{/if}
							
						</form>
						
						<div class="recentButtons">
							<div class="recent-listing-links">
								<strong><span class="glyphicon glyphicon-calendar"></span>&nbsp;{$common_text.502418}<!-- Recent --></strong>
								<div style="white-space:nowrap; display:inline;">
									{module tag='newest_ads_link' buttonStyle=1}
									{module tag='newest_ads_link_1' buttonStyle=1}
									{module tag='newest_ads_link_2' buttonStyle=1}
									{module tag='newest_ads_link_3' buttonStyle=1}
								</div>
							</div>
						</div>
					</div>
					<!-- form input field end -->						

				</div>

				<div class="search_column_right">
					<div>
						<ul class="home_bullets">
							<li id="active"><a href="index.php?a=1" id="current"><span class="glyphicon glyphicon-tag"></span>&nbsp;&nbsp;{$common_text.502439}<!-- Post a Listing --></a></li>
							<li><a href="index.php?a=28&amp;b=141">{$common_text.502394}<!-- Help --></a></li>
							<li><a href="index.php?a=28&amp;b=135">{$common_text.502403}<!-- Features --></a></li>
						</ul>
					</div>
				</div>
				<!--  SEARCH FORM BOXES BEGIN END -->
				<div class="clr"> </div>				
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
								{module tag='module_hottest_ads'}
							</div>
						</div>
						
						<div class="half_column_right recent_ads normal-whitespace">
							<div class="content_box border">
								<h3 class="title"><span class="glyphicon glyphicon-calendar"></span>&nbsp;&nbsp;{$common_text.502422}<!-- Recent Listings --></h3>
								{module tag='newest_ads_1'}
							</div>
						</div>
					</div>
					
					<div class="content_box rwd-hide border">
						{module tag='featured_ads_1'}
					</div>
				</div>
				
			</div>
			<!-- MAIN COLUMN END -->						
			
		</div>
			{include file="footer.tpl"}
	</body>
</html>

