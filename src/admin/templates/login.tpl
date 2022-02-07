{* 16.09.0-79-gb63e5d8 *}
<!DOCTYPE HTML>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<meta name="robots" content="NONE" />
	<meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
	<title>
		Admin Login
	</title>

	{* 3rd Party CSS -- Loaded separately here because our stuff needs to override some of it later *}
	<!-- Bootstrap -->
	<link href="css/bootstrap.css" rel="stylesheet">
	<!-- Font Awesome -->
	<link href="css/font-awesome.css" rel="stylesheet">


    <script type="text/javascript" src="../js/jquery.min.js"></script>
	<script type='text/javascript' src='../js/prototype.js'></script>
	<script type="text/javascript" src="../{external file='js/main.js' forceDefault=1}"></script>
	<script type="text/javascript" src="../{external file='js/gjmain.js' forceDefault=1}"></script>
	<script type="text/javascript" src="../{external file='js/plugins/simpleCarousel.js' forceDefault=1}"></script>
	<script type="text/javascript" src="../{external file='js/plugins/lightbox.js' forceDefault=1}"></script>
	<script type="text/javascript" src="../{external file='js/plugins/utility.js' forceDefault=1}"></script>



    <!-- This file has final overrides and most of the styles specific to the "new" admin design. Load it very last -->
	<link href="css/admin_theme.css" rel="stylesheet">
	<link rel="stylesheet" href="css/login.css" type="text/css" />

	{literal}

	<script type="text/javascript">
		//<![CDATA[

		var initLogin = function () {
			/* check for a cookie */
			if (document.cookie == "") {
				/* if a cookie is not found - alert user -
				 change cookieexists field value to false */
				alert("COOKIES need to be enabled!");

				/* If the user has Cookies disabled an alert will let him know
				  that cookies need to be enabled to log on.*/

				$('cookieexists').value = "false"
			} else {
				/* this sets the value to true and nothing else will happen,
				   the user will be able to log on*/
				$('cookieexists').value = "true"
			}
			//if the admin user field exists, focus on it.
			focusAdminUser('admin_username');
			//or focus on license field if that exists
			focusAdminUser('license_key_field');

		}

		/* Set a cookie to be sure that one exists.
		   Note that this is outside the function*/
		document.cookie = 'killme' + escape('nothing')

		var focusAdminUser = function (id_name) {
			if ($(id_name)) {
				$(id_name).focus();
			}
		}
		//run initLogin() when page loads.
		Event.observe(window, 'load', initLogin);
		{/literal}
		//]]>
	</script>
</head>
<body>

    <div class="login_wrapper">
      <div class="animate form login_form">
	 <section class="login_content">
		<form action="index.php" method="post" id="login_form">
			{if $error}<div class="login_error">{$error}</div>{/if}
			{if $cookie_error}<div class="login_error">{$cookie_error}</div>{/if}
			<div id="login_box">
				<div id="login_sub">
					<div id="login_right">
						<h1 id="login_product_name">Admin Panel Login <span style="font-size: 0.6em;">&nbsp;{$version}</span></h1>
						<h2 id="login_software_type">Authorized Access Only!</h2>
						<div id="login_form_fields">
                            <div id="login_username_block">{$username_label}{$username_field}</div>
                            <div id="login_password_block">{$password_label}{$password_field}</div>
                            {if $smarty.get.page}<input type="hidden" name="page" value="{$smarty.get.page}" />{/if}
						</div>
						<div id="submit_button">
							<input type="hidden" id="cookieexists" name="cookieexists" value="false" />
							<input type="submit" value="Login" class="btn btn-default submit" />
						</div>
						<div id="login_copyright">Copyright 2022. All Rights Reserved.</div>
					</div>
					<div id="login_bottom"></div>
				</div>
			</div>
		</form>
	</section>
      </div>
    </div>


</body>
</html>
