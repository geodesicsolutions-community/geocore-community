<?php
/**
#	Date: 9/21/2007
#	Author:	Carlos Galindo
#	Last modified:	05/03/2008
**/

//define IN_SAMPLES so that CJAX knows how to find the .js file since cjax scripts are not supposed to be called from
//inside CJAX directory, when you use it in your real script DO NOT need define it, if you are calling from outside CJAX directory
define('IN_SAMPLES',1);

include '../cjax.php';

$CJAX->wait(2);
$CJAX->update('element','Ohhh wait...');
$CJAX->wait(4);
$CJAX->update('element','I know...');
$CJAX->wait(7);
$CJAX->update('element',"It is: &nbsp;");
$CJAX->wait(8);
$CJAX->set_value('ip',$_SERVER['REMOTE_ADDR']);
$CJAX->wait(10);
$CJAX->update('element',"<br /> this is a sample to illustrate how to set values in different elements");
$CJAX->wait(11);
$CJAX->set_value('dummy',"Show Source");

$CJAX->wait(13);
$CJAX->update('element',"<br /> <b> Please wait... Getting source code..");
$CJAX->wait(14);
$CJAX->update('element',"<br /> <b> Please wait... Getting source code...");
$CJAX->wait(16);
$CJAX->update('element',"<br /> <b> Please wait... Getting source code....");

$CJAX->wait(17);
$CJAX->updateContent('code',__file__,true);
$CJAX->wait(14);
$CJAX->hide('dummy');
$CJAX->wait(14);
$CJAX->hide('ip');
$CJAX->wait(17);
$CJAX->update('element',"<H3>SET_VALUE ELEMENTS SOURCE CODE</H3>");
?>
<html>
<head>
<?php echo $CJAX->init() ?>
</head>
What is your ip address? <span id='element'></span><input type='text' id='ip' value=''>
<br /> 
<input type='button' id='dummy' value='Dummy'><br /><br />
<span id='code'></span>
</body>
</html>