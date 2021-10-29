<?php
/**
#	Date: 9/21/2007
#	Author:	Carlos Galindo
#	Last modified:	05/03/2008
**/

ini_set('display_errors','1');
ini_set('display_startup_errors','1');

//defined so that CJAX knows how to find the .js file since cjax scripts are not supposed to be called from
//inside CJAX directory
define('IN_SAMPLES',1);

/**
 * include cjax.php which is inside the CJAX main directory
 */
include '../cjax.php';

/**
 * Will look for the element that has the id of "wait",  and will update it.
 */
$CJAX->update('wait','Please wait 5 seconds...');

/**
 * Alert message
 */
$CJAX->alert('Please wait 5 seconds...');

/**
 *  Sets an interval of 5 seconds for the next event
 */
$CJAX->wait(5);

#if the script doesn't work, then un-comment next line and make sure it matches your CJAX directory
# e.g  http://yoursite.com/cjax

$CJAX->alert('just waited 5 seconds... take a look at the source..!');
/**
 * Sets an interval of 5 seconds for the next event
 */
$CJAX->wait(5);
/**
 * Will look for the element that has the id of "wait",  and will update it.
 */
$CJAX->update('wait','Your just waited 5 seconds!.. can you believe it..!, <br /> <br /><b>This is a sample demostration of how CJAX can be used to delay events on your applications</b>');
$CJAX->wait(10);
$CJAX->updateContent('code',__file__,true);

?>
<html>
<head>
<?php echo $CJAX->init() ?>
</head>
<body>
<span id='wait'></span><br />
<span id='code'></span>
</body>
</html>