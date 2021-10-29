<!DOCTYPE HTML>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Validating Login Credentials</title>
	{* 3rd Party CSS -- Loaded separately here because our stuff needs to override some of it later *}
	<!-- Bootstrap -->
	<link href="css/bootstrap.css" rel="stylesheet">
	<!-- Font Awesome -->
	<link href="css/font-awesome.css" rel="stylesheet">
<link rel="stylesheet" type="text/css" href="css/body_html.css" />
<script type="text/javascript">
	{literal}
	window.onload = function () {
		setTimeout(	
			function(){
				myForm = document.getElementById('validate_login');
				if (myForm){
					window.location.replace('index.php');
					myForm.submit();
				}
			} , 2000);
		}
	{/literal}
</script>
</head>
<body>
	<div style="margin: auto; text-align: center; top: 50%; position:absolute; width: 100%">
		<form action="index.php{if $smarty.post.page}?page={$smarty.post.page}{/if}" method="post" id="validate_login">
			<input type="hidden" name="b[username]" value="{$username|escape}" />
			<input type="hidden" name="b[pvalidate]" value="{$password|escape}" />
			{if $license_key}
				<input type="hidden" name="b[license_key]" value="{$license_key|escape}" />
				{if $agreed}
					<input type="hidden" name="agreed" value="1" />
				{/if}
			{/if}
			<input type="hidden" name="b[sessionId]" value="{$session_id|escape}" />
			<i class="fa fa-key fa-spin" style="font-size: 48pt; color: #172D44;"></i><br><br>
			<div style="font-size: 12pt; color: #172D44;">Validating Login Credentials...</div>
		</form>
	</div>

</body>
</html>