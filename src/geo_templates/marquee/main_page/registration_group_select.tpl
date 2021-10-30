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
			<div class="main">
				<!-- MAIN CONTENTS OF PAGE -->

			<div class="content_box">
				<h1 class="title">Registration</h1>
			
				<div class="title2" style="width: 100%; margin: 20px auto 0 auto; text-align: center;">
					Please choose how you will be using our site:
					<div style="margin: 50px 0"><a href="register.php?registration_code=choiceA">Choice A</a>
					</div>
					<div style="margin: 0 0 50px 0"><a href="register.php?registration_code=choiceB">Choice B</a>
					</div>
				</div>
			
				<div style="clear:both;">
				</div>
			</div>				
				
			</div>
			{include file='footer.tpl'}
		</div>
	</body>
</html>