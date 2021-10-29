<?php
//modifier.phoneFormat.php
##########GIT Build Data##########
## 
## File Changed In GIT Commit:
## 
##    7.4beta3-22-g5653c10
## 
##################################

//this smarty plugin is for phoneFormat modifier

function smarty_modifier_phoneFormat ($value)
{
	return geoNumber::phoneFormat($value);
}
