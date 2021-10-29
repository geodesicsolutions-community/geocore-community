<?php
//function.module.php
##########GIT Build Data##########
## 
## File Changed In GIT Commit:
## 
##    6.0.7-2-gc953682
## 
##################################

//This fella takes care of {module ...}

function smarty_function_module ($params, Smarty_Internal_Template $smarty)
{
	//check to make sure all the parts are there
	if (!isset($params['tag'])) {
		//tag not specified
		return '{module tag syntax error}';
	}
	$tag = $params['tag'];
	//Use DataAccess to process as modules expect $this to be instance of DB
	return DataAccess::getInstance()->moduleTag($tag, $params, $smarty);
}
