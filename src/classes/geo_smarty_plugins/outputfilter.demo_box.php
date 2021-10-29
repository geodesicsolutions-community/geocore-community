<?php
//outputfilter.demo_box.php
##########GIT Build Data##########
## 
## File Changed In GIT Commit:
## 
##    6.0.7-2-gc953682
## 
##################################

//this smarty plugin is nice

function smarty_outputfilter_demo_box ($output, Smarty_Internal_Template $smarty)
{
	return preg_replace('/(\<body[^>]*\>)/','$1'.DEVELOPER_MODE,$output);
}
