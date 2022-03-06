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
		<div class="wrapper one-column">
			{include file='header.tpl'}
			<!--[if lt IE 9]>
				<p class="browsehappy">You are using an <strong>outdated</strong> browser. Please <a href="http://browsehappy.com/">upgrade your browser</a> to improve your experience.</p>
			<![endif]-->
			
			<div class="login">
				<!-- START LOGIN FORM -->
				<div class="half_column_left">
					<h1 class="title">Existing User? Login Now</h1>
					<div class="center main_text" style="overflow: hidden;">
						{body_html}
					</div>
				</div>
				<!-- END LOGIN FORM -->
				
				<!-- START REGISTER BLURB -->
				<div class="half_column_right">
					<h2 class="title">Not Registered? Register Now</h2>
					
					<p class="page_instructions">
						Registration only takes a minute and it's completely FREE! Sellers
						and buyers alike enjoy many great benefits including:
					</p>
					
					<ul class="checklist">
						<li>Global Exposure</li>
						<li>Personal Admin Pages</li>
						<li>Auto-Alert Feature</li>
						<li>Add-to-Favorites</li>
						<li>Signs &amp; Flyers Feature</li>
						<li>Personal Messaging System</li>
						<li>Advanced Search Features</li>
						<li>Attention Getters!!</li>
						<li>...and so much more!</li>
					</ul>
					
					<div class="center clear">
						<a href="register.php" class="button">Register Now!</a>
					</div>
				</div>
				<!-- END REGISTER BLURB -->
			</div>
			{include file='footer.tpl'}
		</div>
	</body>
</html>