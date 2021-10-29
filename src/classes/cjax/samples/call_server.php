<?php
/**
#	Date: 9/21/2007
#	Author:	Carlos Galindo
#	Last modified:	05/03/2008
**/

define('IN_SAMPLES',1);
include '../cjax.php';

if(isset($_GET['calling']))
{
	$oldtext = $CJAX->get('texting');
	if($oldtext)
	{
		$stime = date('h:s ',time());
		$CJAX->alert('this is derived from the server:'.$oldtext. ' server time is :' .$stime);
	}
	echo 'You just made an Ajax call to this file itself, believe it or not!  this text is the output of an AJAX call!';
	echo '<br /><br />';
	echo 'Click the button to make another call!';
	$text = $CJAX->value('okk');
	$call= $CJAX->call("?calling=1&texting=$text");
	echo "<input type='text' id='okk' value='ok'><input type='button' value='Send' $call/ >";
	
	echo "<br />$oldtext";
	exit();
}

$CJAX->wait(7);
$CJAX->updateContent('code',__file__,true);

$CJAX->call_server('call_server.php?calling=1','element');

?>

<html>
<head>
<?php echo $CJAX->init() ?>
</head>
<body>
<div id='element'></div>
<br /> 
<span id='code'></span>
</body>
</html>