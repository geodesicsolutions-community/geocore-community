{* 16.09.0-90-g207ceeb *}
<!DOCTYPE HTML>
<html>
	<head>
		<title>GeoCore CE Admin Panel</title>
			{if $charset}<meta http-equiv="Content-Type" content="text/html; charset={$charset}" />{/if}

			{* 3rd Party CSS -- Loaded separately here because our stuff needs to override some of it later *}
			 <!-- Bootstrap -->
			<link href="css/bootstrap.css" rel="stylesheet">
			<!-- Font Awesome -->
			<link href="css/font-awesome.css" rel="stylesheet">

			{head_html}

			<meta http-equiv="X-UA-Compatible" content="IE=edge">
			<meta name="viewport" content="width=device-width, initial-scale=1">
			<script type="text/javascript">
				jQuery(document).ready(function () {
					gjUtil.inAdmin = true;
					gjUtil.ready();
				});
				//Wait for entire page to be done for this stuff to load
				jQuery(window).load(function () {
					gjUtil.load();
				});

				//Load the 'old' prototype-based stuff
				Event.observe(window, 'load', function () {
					geoUtil.init();
				});
			</script>

			<!-- This file has final overrides and most of the styles specific to the "new" admin design. Load it very last -->
			<link href="css/admin_theme.css" rel="stylesheet">
	</head>

	<body{if $body_tag_html} $body_tag_html{/if} class="nav-md">
		<div class="container body">
			<div class="main_container">
				<div class="col-md-3 left_col">
					<div class="left_col scroll-view">
						<div class="navbar nav_title" style="border: 0;">
							<div id="logo" {if !$white_label}class="{if $geoturbo_status}geoTurboLogo{else}geoCoreLogo{/if}"{/if}>
							<h1>
								<a href="index.php?page=home">
									{if !$white_label}<span>{$product_typeDisplay}</span>{/if}{if $is_beta} [BETA]{/if}{if $is_rc} [Release Candidate]{/if}
								</a>
							</h1>
							</div>
						</div>

						<div class="clearfix"></div>

						<!-- menu profile quick info -->
						<div class="profile">
							{if $enabledAddons.profile_pics}
								<div class="profile_pic">
									<div class="img-circle profile_img">
										<a href="index.php?mc=users&page=users_view&b={$admin_userid}">{addon author='geo_addons' addon='profile_pics' tag='show_pic' width=46}</a>
									</div>
								</div>
							{/if}
							<div class="profile_info" {if !$enabledAddons.profile_pics}style="width: 100%;"{/if}>
								<h2>{$admin_username}{if $admin_userid != 1} (#{$admin_userid}){/if}</h2>
								<a href="#" onclick="logout(this); return false;" data-toggle="tooltip" data-placement="top" title="Logout">
									<span class="glyphicon glyphicon-off" aria-hidden="true"></span> Logout
								</a>
							</div>
						</div>
						<!-- /menu profile quick info -->

						<br />

						<!-- sidebar menu -->
						{if !$hide_side_menu}
							<div id="sidebar-menu" class="main_menu_side hidden-print main_menu">
								<div class="menu_section">
									<h3> </h3>
									<ul class="nav side-menu" id="sideMenu">
										{include file="side_menu/index"}
									</ul>
								</div>
							</div>
						{/if}
						<!-- /sidebar menu -->
						</div>
					</div>

					<!-- top navigation -->
					<div class="top_nav">
						<div class="nav_menu">
							<nav>
								<div class="nav toggle">
									<a id="menu_toggle"><i class="fa fa-bars"></i></a>
								</div>
								<div class="nav toggle site-name">{$site_name}
									<a href="index.php?page=main_general_settings&mc=site_setup" title="Edit Site Name" alt="Edit Site Name"><i class="fa fa-pencil edit-pencil"></i></a>
								</div>
								<ul class="nav navbar-nav navbar-right">
									<li>
										<a href="index.php?page=home" title="Admin Home">
											<i class="fa fa-home"></i>
										</a>
										<a href="{$classifieds_url}" title="My Site" onclick="window.open(this.href); return false;">
											<i class="fa fa-laptop"></i>
										</a>
										<a href="index.php?page=site_map" title="Admin Map">
											<i class="fa fa-sitemap"></i>
										</a>
										<a href="#" onclick="logout(this); return false;" title="Logout">
											<i class="fa fa-power-off"></i>
										</a>
									</li>
								</ul>
							</nav>
						</div>

						<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 header-search">
							<form action="index.php?page=text_search" method="post" id="admin-top-search-form">
								<div class="input-group">
									<input type="text" class="form-control" aria-label="Admin Search" placeholder="Search Admin..." id="admin-top-search-field" />
									<div class="input-group-btn dropdown">
										<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" id='admin-top-search-select' aria-expanded="false" style="margin-right: 0px; border-radius: 0px; border-left: 0px;">Select <span class="caret"></span></button>
										<ul class="dropdown-menu dropdown-menu-right" role="menu">
											<li><a href="index.php?page=text_search" onclick="adminTopSearchUpdateForm(1); return false;">for Site Text</a></li>
											<li><a href="index.php?mc=users&page=users_search" onclick="adminTopSearchUpdateForm(2); return false;">for User</a></li>
											<li><a href="index.php?mc=users&page=users_view_ad" onclick="adminTopSearchUpdateForm(3); return false;">for Listing ID#</a></li>
											<li><a href="index.php?mc=users&page=orders_list_order_details" onclick="adminTopSearchUpdateForm(4); return false;">for Order#</a></li>
											<li><a href="index.php?mc=users&page=orders_list_order_details" onclick="adminTopSearchUpdateForm(5); return false;">for Invoice#</a></li>
											<li><a href="index.php?mc=users&page=users_search" onclick="adminTopSearchUpdateForm(6); return false;">for Transaction#</a></li>
										</ul>
									</div>
									<div class="input-group-btn">
										<button type="button" class="btn btn-primary" onclick="jQuery('#admin-top-search-form').submit();"><i class="fa fa-search"></i></button>
									</div>
								</div>

								<input type="hidden" name="b[search_group]" value="0" />
								<input type="hidden" name="b[search_type]" value="1" />
								<input type="hidden" name="show_first" value="1" />
								<script>
									function adminTopSearchUpdateForm(type) {
										if(type == 1) {
											jQuery('#admin-top-search-form').prop('action','index.php?page=text_search');
											jQuery('#admin-top-search-field').prop('name','text');
											jQuery('#admin-top-search-select').html("for Site Text <span class='caret'></span>");
										} else if (type == 2) {
											jQuery('#admin-top-search-form').prop('action','index.php?mc=users&page=users_search');
											jQuery('#admin-top-search-field').prop('name','b[text_to_search]');
											jQuery('#admin-top-search-select').html("for User <span class='caret'></span>");
										} else if (type == 3) {
											jQuery('#admin-top-search-form').prop('action','index.php?page=users_view_ad');
											jQuery('#admin-top-search-field').prop('name','search_top');
											jQuery('#admin-top-search-select').html("for Listing ID# <span class='caret'></span>");
										} else if (type == 4) {
											jQuery('#admin-top-search-form').prop('action','index.php?page=orders_list_order_details');
											jQuery('#admin-top-search-field').prop('name','orderId');
											jQuery('#admin-top-search-select').html("for Order# <span class='caret'></span>");
										} else if (type == 5) {
											jQuery('#admin-top-search-form').prop('action','index.php?page=orders_list_order_details');
											jQuery('#admin-top-search-field').prop('name','invoiceId');
											jQuery('#admin-top-search-select').html("for Invoice# <span class='caret'></span>");
										} else if (type == 6) {
											jQuery('#admin-top-search-form').prop('action','index.php?page=orders_list_order_details');
											jQuery('#admin-top-search-field').prop('name','transactionId');
											jQuery('#admin-top-search-select').html("for Transaction# <span class='caret'></span>");
										}
									}
								</script>
							</form>
						</div>
					</div>
					<!-- /top navigation -->

					<!-- page content -->
					<div class="right_col" role="main">

						<div style='clear:both'></div>
						{if !$hide_title}
							<div class='topIcons'>
								<div class="breadcrumb_title">
									<div>{if $image && $image_fa}<span class="fa {$image}"></span> {/if}{$breadcrumb_title}</div>
								</div>
								<div class="page_image">
									{if $image && !$image_fa}<img src="{$image}" alt="icon" />{/if}
									<div style='clear:both'></div>
									{if !$white_label}
										<a class="page_help_button" title="Wiki Help for this Topic"
                                            href="https://geodesicsolutions.org/wiki/admin_menu/{$wiki_uri}" target="_blank"><i class="fa fa-book"> </i></a>
									{/if}
								</div>
							</div>
							<div class="section-title">{$page_title}</div>
							{if $addon_title}<div class="page-title1">Addon: <span class="color-primary-two">{$addon_title}</span></div>{/if}
						{/if}

						{include file='lease_payment_due.tpl'}

						{if !$hide_notifications}
							<div id="notifications-box">
								{include file="notifications"}
							</div>
						{/if}

						{body_html}

					</div>
					<!-- /page content -->

					<!-- footer content -->
					<footer>
						<div class="pull-right">
							<div id="footer-top">
								<p>GeoCore CE (Community Edition) DB Ver. {$product_version}
								{if !$white_label}<span style="white-space:nowrap;">[ <a href="https://github.com/geodesicsolutions-community/geocore-community/releases" target="_blank">Changelog</a> ]</span>{/if} </p>
							</div>
							<div id="footer">
								<div id="footer-inside">
									<p>Copyright © 2022</p>
                                    <p>
                                        Licensed under <a href="https://github.com/geodesicsolutions-community/geocore-community/blob/42e315b06b57a3a42b1352713258866fc691be70/LICENSE" target="_blank">MIT License</a>
                                    </p>
								</div>
							</div>
						</div>
						<div class="clearfix"></div>
					</footer>
					<!-- /footer content -->
				</div>
			</div>
		</div>
		<!-- Scripts to run the admin menu, and the full copy of bootstrap.js, used throughout the admin -->
		<script src="js/admin_theme.js"></script>
		<script src="js/bootstrap.min.js"></script>
	</body>
</html>
