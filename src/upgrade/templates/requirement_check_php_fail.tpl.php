<?php
//requirement_check_php_fail.tpl.php
##########GIT Build Data##########
## 
## File Changed In GIT Commit:
## 
##    16.03.0-3-gb5f334d
## 
##################################

/*
 * used when PHP requirement check failes.
 * 
 * To re-generate the page, on a dev install that does meet min requirements:
 * 
 * 1. In index.php find: "var $regen_php_failed = false;" and change to true.
 * 2. View upgrade requirement check page in the browser. (Note that parts might 
 *    look missing, if you view source you'll see those parts are echoed PHP)
 * 3. View source, and copy entire source contents.
 * 4. In this file, remove everything AFTER the first end PHP tag, and replace
 *    with contents you copied by view source.
 * 5. Remember to change $regen_php_failed back to false before committing changes.
 *   
 */


?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>Geodesic Update Routine</title>
<link rel="stylesheet" href="css/install.css" type="text/css" />
<script src='../js/jquery.min.js'></script>
<script src='../geo_templates/default/external/js/gjmain.js'></script>

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
					
<form action="index.php?run=show_upgrades" method="POST" id="req_form">
<div style="border: 2px solid #1382B7; padding: 3px; background-color:#FFF;"> 
<table cellpadding="2" cellspacing="2">
	<thead>
		<tr>

			<th class="heading1" colspan="3">Server Minimum Requirements Check</th>
		</tr>
		<tr>
			<th width="12%" class="heading2">Req&nbsp;Met?</th>
			<th width="30%" class="heading2">Requirement</th>
			<th class="heading2a">Your Server's Settings</th>

		</tr>
	</thead>
	<tbody>
		<tr style="background-color: #FFF;">
			<td class="result"><span class="failed"><img src="images/no.gif" alt="no" title="no"></span></td>
			<td class="req">PHP Version 5.4.0+</td>
			<td class="setting">PHP <?php echo phpversion(); ?></td>
		</tr>

		<tr style="background-color: #FFF;">
			<td class="result">---</td>

			<td class="req">MySQL Version 4.1.0+</td>
			<td class="setting">Not Tested, since PHP version check failed above.</td>
		</tr>
		<tr style="background-color: #FFF;">

			<td class="result">---</td>
			<td class="req">ionCube Loader</td>
			<td class="setting">Not Tested, since PHP version check failed above.</td>
		</tr>
	</tbody>
</table>
</div>
<br />
<p class="body_txt1"><div style="text-align: left; background-color: #FFF; padding: 5px; border: 1px solid #EA1D25;"><span class="failed">IMPORTANT: As shown above, one or more of your server's minimum requirements have not been met.  These requirements must be met in order to continue with this installation.
	<br /><br />NOTE: <?php if ($package=='both') { ?>Zend Optimizer and IonCube are<?php } else { ?>Ioncube is<?php } ?> FREELY available for your host to download and install on your server. There is NO COST to your host, since the version that needs to be installed is the
	"decryption" version.
	<br /><br />Hosting Trouble? Find our recommended hosting solutions by <a href="http://geodesicsolutions.com/resources.html" class="login_link" target="_blank">CLICKING HERE</a>.</span></div></p>
	<p>Please refer to the <a href="http://geodesicsolutions.com/support/wiki/update/start" class="login_link" target="_blank">Geodesic Solutions User Manual</a>.</p>

</form>
<form action="index.php" method="GET">

</form>
					</div>
					<div id="login_copyright">Copyright 2001-2011. <a class="login_link" href="http://geodesicsolutions.com" onclick="window.open(this.href); return false;">Geodesic Solutions, LLC.</a><br />All Rights Reserved.</div>
				</div>
				<div style="clear: both;"></div>
			</div>

		</div>
	</div>

</body>
</html>