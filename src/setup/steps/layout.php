<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<title>GeoInstaller</title>
<link rel="stylesheet" href="css/install.css" type="text/css">
<script src='../js/jquery.min.js'></script>
<script src='../geo_templates/default/external/js/gjmain.js'></script>

<?= $header ?>

<script>
var moveMiddle = function () {
	var divH = jQuery('#outerBox').innerHeight()/2;
	var pageH = jQuery(window).innerHeight()/2;
	jQuery('#outerBox').css({top: Math.max(0, (pageH-divH))});
}

jQuery(window).on('resize', moveMiddle);

jQuery(function () {
	jQuery('#req_form').submit(function (e) {
		if (!jQuery('#license').length || !jQuery('#license').prop('checked')) {
			e.preventDefault();
			alert('You must agree to the License Agreement to proceed.');
		}
	});
	moveMiddle();
});
</script>

</head>
<body>
	<div id="outerBox">
		<div id="login_box">
			<div id="login_sub">
				<div id="login_left">
					<div id="login_left_list"></div>
					<ul>
						<li style="list-style-image: none; list-style: none;">&nbsp;</li>
						<li><a href="http://geodesicsolutions.com/support/wiki/" onclick="window.open(this.href); return false;">User Manual</a></li>
						<li><a href="http://geodesicsolutions.com/geo_user_forum/index.php" onclick="window.open(this.href); return false;">User Forum</a></li>
						<li><a href="http://geodesicsolutions.com/support/helpdesk/kb" onclick="window.open(this.href); return false;">Knowledgebase</a></li>
						<li><a href="https://geodesicsolutions.com/geo_store/customers" onclick="window.open(this.href); return false;">Client Area</a></li>
						<li><a href="http://geodesicsolutions.com/resources.html" onclick="window.open(this.href); return false;">Resources</a></li>
					</ul>
				</div>
				<div id="login_right">
					<h1 id="login_product_name">&nbsp;</h1>
					<h2 id="login_software_type">&nbsp;</h2>
					<div id="login_form_fields">
                        <?php require($step . '.php'); ?>
					</div>
				</div>
				<div style="clear: both;"></div>
			</div>
		</div>
	</div>
</body>
</html>
