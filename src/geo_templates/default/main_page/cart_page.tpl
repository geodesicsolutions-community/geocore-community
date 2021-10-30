<!DOCTYPE html>
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
		{*
		NOTE: This is a "special case" - the next line will use a "one-column"
		layout if the user is not logged in (anonymous listing) or a "two-column"
		layout if they ARE logged in
		
		*}
		<div class="wrapper {if $logged_in}two-column{else}one-column{/if}">
			{include file='header.tpl'}
			<!--[if lt IE 9]>
				<p class="browsehappy">You are using an <strong>outdated</strong> browser. Please <a href="http://browsehappy.com/">upgrade your browser</a> to improve your experience.</p>
			<![endif]-->
			<div class="main">
				<!-- MAIN CONTENTS OF PAGE -->
				{body_html}
			</div>
			<div class="sidebar">
				<!-- LEFT SIDEBAR CONTENTS -->
				<nav class="my-account rwd-hide">
					{module tag='my_account_links'}
				</nav>
			</div>
			
			{include file='footer.tpl'}
		</div>
	</body>
</html>