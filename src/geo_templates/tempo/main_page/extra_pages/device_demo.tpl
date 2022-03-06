<div>
	<style scoped>
		.phone {
			text-align: center;
			padding: 111px 28px 108px 31px;
			background: url('{external file="images/backgrounds/phone.png"}') no-repeat;
			background-size: 100% 100%;
			width: 298px;
			height: 529px;
			margin: 0 auto;
			overflow: hidden;
			resize: both;
		}
		.frame {
			width: 100%;
			height: 100%;
			border: none;
			resize: none;
		}
		.old-ie {
			display: none;
		}
	</style>
	{add_footer_html}

	{/add_footer_html}
	<h1>Mobile Phone Demonstration*</h1>
	<div class="old-ie browsehappy">
		<strong>This will not work on your browser:</strong>  You are using an <strong>outdated</strong> browser that is not capable of properly showing the mobile demonstration. Please <a href="http://browsehappy.com/">upgrade your browser</a> to improve your experience.
	</div>
	
	<p><strong>Responsive Web Design (RWD)</strong> is a name for a set of technologies that enable web pages to dynamically adjust 
	to the size of the screen they're being viewed on. GeoCore 7.3 leverages these techniques in order to make your site viewable and fully functional 
	on any computer, phone, or tablet. Below, we've taken <a href="http://geodesicsolutions.com/demo">this demo GeoCore installation</a> and loaded
	it into a phone-sized window, so you can see for yourself how the design automatically adjusts to the size and functionality of the smaller touchscreen.</p>
	
	
	<div class="phone">
		<iframe class="frame" src="index.php"></iframe>
	</div>
	<p>
		<strong>* Note:</strong> Screen resolutions and sizes vary widely.  In fact, this is part of the
		reason Geocore uses <strong>Responsive Web Design (RWD)</strong> - it allows the layout and design
		of the site to <em>respond</em> to the screen size or size of the browser window!
		You can try this yourself, when viewing the demo, just change the window size of your
		browser.  You don't even need to refresh, when you change the window size
		the entire page will automatically respond.
	</p>
	<p>
		This is just a simple demonstration of what the page looks like on a smaller
		screen, it is <strong>not meant for testing</strong> purposes and is not meant to be
		an accurate rendering of what the site will look like on a mobile phone.
		Things will look similar but depending on the device's resolution, screen
		size, and even what browser being used on the device, there can be
		variations.
	</p>
	<p class="center">
		{$classifieds_url|qr_code:125}
		Try the demo on your own mobile device or tablet, just scan this QR code!
	</p>
</div>