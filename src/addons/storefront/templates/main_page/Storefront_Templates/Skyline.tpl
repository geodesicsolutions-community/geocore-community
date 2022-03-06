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
		
		<!-- Storefront-specific CSS -->
		<link rel="stylesheet" type="text/css" href="{external file='css/addon/storefront/default_style.css'}" />
		
		{include file='head_common.tpl'}
		<script src="js/modernizr-2.6.2.min.js"></script>
		<script>Modernizr.load({ test: Modernizr.mq('only all'),nope:'{external file="css/old_ie.css"}'});</script>
	</head>
	<body>
		<div class="wrapper {if $classified_id}three-column{else}two-column{/if} store skyline">
			{include file='header.tpl'}
			<!--[if lt IE 9]>
				<p class="browsehappy">You are using an <strong>outdated</strong> browser. Please <a href="http://browsehappy.com/">upgrade your browser</a> to improve your experience.</p>
			<![endif]-->
			
			{if $storefront_logo}
				<div class="store-logo-container">
					<div class="center clearfix store-logo">
						<a href="{$storefront_home_url}">{$storefront_logo}</a>
					</div>
					<div class="storefront-name-container">
						<div class="inner">
							{$storefront_name}
						</div>
					</div>
				</div>
			{/if}
			
			{if $classified_id}
				{* this is a listing details page! All three columns are included by the sub-template, so just call body_html and skip the rest  *}
				{if $storefront_welcome_note}
					<div class="content_box row_even clearfix store-welcome-note">
						{$storefront_welcome_note}
					</div>
				{/if}
				{body_html}
			{else}
				<div class="main">
					{if $updateResult}
						<div class="success_box">
							{$updateResult}
						</div>
					{/if}
				
					{if $storefront_welcome_note}
						<div class="content_box row_even clearfix store-welcome-note">
							{$storefront_welcome_note}
						</div>
					{/if}
					{body_html}
				</div>
				{if !$classified_id}
					<div class="sidebar">
						{if $is_owner}
						<div class="center">
							<a href='index.php?a=ap&amp;addon=storefront&amp;page=control_panel' class='button' style="font-size: 1em; margin: 5px 5px 10px 5px;"><span class="glyphicon glyphicon-cog"> </span>&nbsp;{$storefront_text.tpl_manage_link}</a>
						</div>
						{/if}
						
						<div class="content_box">
							<h2 class="title"><span class="glyphicon glyphicon-tasks"> </span>&nbsp;{$storefront_text.tpl_cat_header}</h2>
							<ul>
								<li>{$storefront_homelink}</li>
								{foreach from=$storefront_categories item='cat'}
									<li><a href='{$cat.url}'>{$cat.category_name}</a></li>
										{foreach from=$cat.subcategories key=sub_id item=sub}
											<li><a href='{$sub.url}' class="subcategory">{$sub.category_name}</a></li>
										{/foreach}
								{/foreach}
							</ul>
						</div>
						
						{if $storefront_pages}
							<div class="content_box">
								<h2 class="title"><span class="glyphicon glyphicon-duplicate"> </span>&nbsp;{$storefront_text.tpl_page_header}</h2>
								<ul>
									{foreach from=$storefront_pages item='page'}
										<li><a href='{$page.url}'>{$page.link_text}</a></li>
									{/foreach}
								</ul>
							</div>
						{/if}
						
						{if $display_newsletter}
							<div class="content_box">
								<form action='' id='newSubscriber' method='post'>
									<h2 class="title">{$storefront_text.tpl_newsletter_header}</h2>
									{if $storefront_email_added}
										<div class="success_box">{$storefront_text.tpl_newsletter_thanks}</div>
									{else}
										<div class="center">
											<input type='hidden' name='newSubscriber' value='1' />
											<input type='text' name='email' id='email' value='Email Address' onfocus='javascript: document.getElementById("subscribeSubmit").disabled = false;' class="field" />
											<input type='submit' name='subscribeSubmit' id='subscribeSubmit' value='subscribe' disabled='disabled' class="button" />
										</div>
									{/if}
								</form>
							</div>
						{/if}
					</div>
				{/if}
			{/if}
			
			{include file='footer.tpl'}
		</div>
	</body>
</html>
