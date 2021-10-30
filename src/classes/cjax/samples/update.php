<?php

/**
#   Date: 9/21/2007
#   Author: Carlos Galindo
#   Last modified:  05/03/2008
**/

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');

//define IN_SAMPLES so that CJAX knows how to find the .js file since cjax scripts are not supposed to be called from
//inside CJAX directory, when you use it in your real script no need define it, if you are calling from outside CJAX directory
define('IN_SAMPLES', 1);

/**
 * include cjax.php which is inside the CJAX main directory
 */
include '../cjax.php';


$CJAX->update('element', 'This text has been updated dynamically, take a look at the source!');
$CJAX->wait(3);
$CJAX->updateContent('code', __file__, true);

?>
<html>
<head>
<?php echo $CJAX->init() ?>
</head>
<body>
<span id='element'></span><br />
<span id='code'></span>

</body>
</html>