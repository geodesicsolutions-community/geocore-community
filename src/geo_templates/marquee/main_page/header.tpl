{*
     IMPORTANT - TEXT ENTRIES ARE NO LONGER EDITED IN THIS TEMPLATE, UNLESS YOU ARE ADDING YOUR OWN TEXT. SYSTEM
     TEXT IS NOW LOCATED ENTIRELY IN THE PAGES MANAGEMENT MENU. ANY TEXT REFERENCES YOU SEE BELOW ARE SIMPLY
     "NOTES" AND ARE FOR INFORMATION PURPOSES ONLY TO HELP YOU IDENTIFY THE TEXT'S TAG IF YOU WANT TO MOVE IT.
     MOST SYSTEM TEXT FOR THIS PARTICULAR TEMPLATE CAN BE FOUND AND EDITED AT THE FOLLOWING LOCATION: 
     PAGES MANAGEMENT > GENERAL TEMPLATE TEXT > COMMON TEMPLATE TEXT 
*}


{* The HTML at the top of each page used for menu, logo, top navigation, and user bar *}

<header class="page clearfix">

	<!-- START SUBMENU -->
	{if ($smarty.get.a == 4 and $logged_in) || $smarty.get.a == 'cart'}
		{* in My Account section or Cart process -- show mini-cart *}
		<div class="content_box clearfix regions my-account">
			{module tag='my_account_links' mini_cart_only=1}
		</div>
		<div class="submenu-spacer">&nbsp;</div>
	{elseif $enabledAddons.geographic_navigation and $smarty.get.addon != 'storefront'}
		{* On any other page (EXCEPT STOREFRONT PAGES), and if the GeoNav addon is set up, show region selection as the submenu *}
		<div class="content_box clearfix regions">
			<h2 class="title section-collapser">
				<span class="glyphicon glyphicon-map-marker"></span>&nbsp;{$common_text.502402}<!-- Location -->&nbsp;<span style="font-size: .8em; font-weight: normal;"><span style="font-size: .8em;" class="glyphicon glyphicon-chevron-right"></span> {addon author='geo_addons' addon='geographic_navigation' tag='current_region'}&nbsp;
				{if $geographic_navigation_region}&nbsp;[&nbsp;<a href="{$classifieds_url}?region=0">{$common_text.502401}<!-- clear --></a>&nbsp;]{/if}</span>
			</h2>
			{addon author='geo_addons' addon='geographic_navigation' tag='navigation'} 
		</div>
		<div class="submenu-spacer">&nbsp;</div>
	{/if}
	<!-- END SUBMENU -->
	
	<!-- START SEARCH BOX -->
	<div class="search-box-hdr">
		<form method="get" action="index.php" class="searchbox clearfix">
			<div class="cntr" style="display:inline;">
				<div class="search-form" style="display:inline;">
					<input type="hidden" name="a" value="19" />
					<input type="hidden" name="b[subcategories_also]" value="1" />
					<input class="keyword" type="text" placeholder="{$common_text.502412}" name="b[search_text]" /><!-- Search Keywords -->
				</div>
			</div>
			{if $enabledAddons.zipsearch}
			<div class="cntr" style="display:inline;">
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
					</select>
					{$common_text.502434}<!-- of -->
					<input id="by_zip_code" class="field" type="text" size="8" name="b[by_zip_code]" value=""  placeholder="{$common_text.502417}" /><!-- Postal Code -->
				</div>
			</div>
			{/if}
			<div class="cntr search-button" style="display:inline; text-transform:uppercase;">
				<input class="search-button" type="submit" value="{$common_text.502429}" /><!-- Search -->
			</div>
		</form>	
	</div>	
	<!-- END SEARCH BOX -->	
	
	<!-- START LOGO -->
	<div class="logo-box{if $smarty.get.a} rwd-hide{/if}" title="{$site_name}">
		<a href="index.php" class="logo" title="{$site_name}">
			<!-- Logo image OR Logo text goes here!  To use text, remove the
				image tag below and replace it with text -->
			<img src="{external file='images/logo.jpg'}" alt="{$site_name}" title="{$site_name}" />
		</a>
		<a href="index.php" class="slogan" title="{$site_name}">
			<!-- Slogan text goes here, if you want to add a slogan that shows
				under the logo text or logo image. -->
		</a>
	</div>
	<!-- END LOGO -->
	
	<!-- START NAVIGATION -->
	<nav class="fixed-nav">

		<a href="#page-bar" class="fixed-link menu"></a>
		
		<div class="header-links-rwd"> 
			<a href="index.php" class="search"><span class="glyphicon glyphicon-home"> </span></a> 
			<a href="index.php?a=19" class="search"><span class="glyphicon glyphicon-search"> </span></a> 
			<a href="index.php?a=1" class="list"><span class="glyphicon glyphicon-tag"> </span>&nbsp;{$common_text.502391}<!-- Sell --></a>
		</div>

		<a href="#user-bar" class="fixed-link user">{addon addon='social_connect' tag='facebook_session_profile_picture'}</a>
		
			<!-- START USER BAR -->

			<div class="fixed-menu clearfix user-bar">
			
				<div class="language-links">
					
					{* SET TWO LANGUAGES TO ACTIVE FOR LANGUAGE SELECT DROPDOWN TO DISPLAY *}
					<div class="language-select">
						{language_select}
					</div>

					{* START LANGUAGE FLAGS - REPLACE lang = 1 WITH LANGUAGE ID # FROM LANGUAGES MENU AND DELETE THE NOTED LINES FOR THAT LANGUAGE BELOW. REPLACE FLAG IMAGE WITH ONE FROM images/icons/flags/ FOLDER OR WITH ONE OF YOUR OWN. *}
					
					{* DELETE THIS LINE TO USE LANGUAGE BELOW
					{$lang = 1}
					<a href="{$smarty.server.SCRIPT_NAME}?switchLang=yes{foreach $smarty.get as $key => $value}&{if $key === 'set_language_cookie'}{$key}={$lang}{else}{$key}={$value}{/if}{/foreach}{if !$smarty.get.set_language_cookie}&set_language_cookie={$lang}{/if}"><span class="selected"><img src="{external file='images/icons/flags/UK.png'}" alt="English" title="English" /></span></a>
					DELETE THIS LINE TO USE LANGUAGE ABOVE *}
		
					{* DELETE THIS LINE TO USE LANGUAGE BELOW
					{$lang = 24}
					<a href="{$smarty.server.SCRIPT_NAME}?switchLang=yes{foreach $smarty.get as $key => $value}&{if $key === 'set_language_cookie'}{$key}={$lang}{else}{$key}={$value}{/if}{/foreach}{if !$smarty.get.set_language_cookie}&set_language_cookie={$lang}{/if}"><span class="unselected"><img src="{external file='images/icons/flags/Spain.png'}" alt="Spanish" title="Spanish" /></span></a>
					DELETE THIS LINE TO USE LANGUAGE ABOVE *}
					
					{* END LANGUAGE FLAGS *}

				</div>
								
				{if $enabledAddons.social_connect}<div class="fb-profile-pic">{addon addon='social_connect' tag='facebook_session_profile_picture'}</div>{/if}
				<div class="user-welcome">{$common_text.502397}<!-- Welcome -->, {module tag='display_username'}</div>
				<span class="user-links">
					{if not $logged_in}
						{*Logged out code*}
						<a href="register.php" class="menu-link">{$common_text.502398}<!-- Register --><span class="glyphicon glyphicon-pencil pc-hide" style="padding-left:10px;"> </span></a>
						<a href="index.php?a=10" class="menu-link">{$common_text.502399}<!-- Login --><span class="glyphicon glyphicon-log-in pc-hide" style="padding-left:10px;"> </span></a>
					{else}
						{*Logged in code*}
						{module tag='my_account_links' mobile_header=1}
					    <a href="index.php?a=17" class="menu-link">{$common_text.502400}<!-- Logout --><span class="glyphicon glyphicon-log-out pc-hide" style="padding-left:10px;"> </span></a>
					{/if}
				</span>
				{addon author='geo_addons' addon='social_connect' tag='facebook_login_button'}
			</div>
		
			<div class="abs-space"></div>	
		
			<!-- END USER BAR -->
			
		<nav class="page-bar fixed-menu">
			<div class="nav-wrapper">
				<a href="index.php" class="menu-link"><span class="glyphicon glyphicon-home pc-hide"> </span>&nbsp;&nbsp;{$common_text.502390}<!-- Home -->&nbsp;&nbsp;</a>
				<a href="index.php?a=1" class="menu-link sell"><span class="glyphicon glyphicon-tag pc-hide"> </span>&nbsp;&nbsp;{$common_text.502391}<!-- Sell -->&nbsp;&nbsp;</a>
				<a href="index.php?a=19" class="menu-link"><span class="glyphicon glyphicon-search pc-hide"> </span>&nbsp;&nbsp;{$common_text.502392}<!-- Search -->&nbsp;&nbsp;</a>
				{addon addon='storefront' tag='list_stores_link' no_li=1}
				<a href="index.php?a=28&amp;b=143" class="menu-link"><span class="glyphicon glyphicon-bookmark pc-hide"> </span>&nbsp;&nbsp;{$common_text.502393}<!-- Pricing -->&nbsp;&nbsp;</a>
				<a href="index.php?a=28&amp;b=141" class="menu-link"><span class="glyphicon glyphicon-question-sign pc-hide"> </span>&nbsp;&nbsp;{$common_text.502394}<!-- Help -->&nbsp;&nbsp;</a>
				<a href="index.php?a=28&amp;b=142" class="menu-link extra"><span class="glyphicon glyphicon-info-sign pc-hide"> </span>&nbsp;&nbsp;{$common_text.502395}<!-- About Us -->&nbsp;&nbsp;</a>
				<a href="index.php?a=28&amp;b=136" class="menu-link extra"><span class="glyphicon glyphicon-phone pc-hide"> </span>&nbsp;&nbsp;{$common_text.502396}<!-- Contact Us -->&nbsp;&nbsp;</a>
			</div>
		</nav>
		
	</nav>
	
	<!-- END NAVIGATION -->
	
</header>