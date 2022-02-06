{* 7.4beta1-348-g7437410 *}
<!DOCTYPE html>
<html>
<head>
<title>Geodesic Update Routine</title>
<link rel="stylesheet" href="css/install.css" type="text/css" />
<script src="../js/jquery.min.js"></script>
<script type='text/javascript' src='../js/jquery.min.js'></script>
<script type='text/javascript' src='../geo_templates/default/external/js/gjmain.js'></script>

{$head}

<script>
var moveMiddle = function () {
	var divH = jQuery('#outerBox').innerHeight()/2;
	var pageH = jQuery(window).innerHeight()/2;
	jQuery('#outerBox').css({ top: Math.max(0, (pageH-divH))});
}

jQuery(window).on('resize', moveMiddle);

jQuery(function () {
	jQuery('#req_form').submit(function (e) {
		if (!jQuery('#license').length || !jQuery('#license').prop('checked')) {
			e.preventDefault();
			alert('You must agree to the License Agreement to proceed.');
		} else if (!jQuery('#backup_agree').length || !jQuery('#backup_agree').prop('checked')) {
			e.preventDefault();
			alert('You must back up the entire database and files to proceed.');
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
						<li><a href="versions/changelogs.php" target="_blank">Changelog</a></li>
						<li><a href="https://geodesicsolutions.org/wiki/" target="_blank">User Manual</a></li>
						<li><a href="https://github.com/geodesicsolutions-community/geocore-community/discussions" target="_blank">Community Discussion</a></li>
						<li><a href="https://geodesicsolutions.org" target="_blank">Website</a></li>
					</ul>
				</div>
				<div id="login_right">
					<h1 id="login_product_name">&nbsp;</h1>
					<h2 id="login_software_type">&nbsp;</h2>
					<div id="login_form_fields">
					{if $body_tpl}{include file=$body_tpl}{/if}
{$body}
					</div>
					<div id="login_copyright">
                        Distributed freely under <a href="https://github.com/geodesicsolutions-community/geocore-community/blob/40dda8b846a236688efcbd87fcfb7fa9280c4255/LICENSE" target="_blank">MIT License</a>
                    </div>
				</div>
				<div style="clear: both;"></div>
			</div>
		</div>
	</div>

</body>
</html>
