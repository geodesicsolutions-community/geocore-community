<?php
//modifier.fromDB.php
##########GIT Build Data##########
## 
## File Changed In GIT Commit:
## 
##    6.0.7-2-gc953682
## 
##################################

//this smarty plugin is for fromDB modifier

function smarty_modifier_fromDB ($value)
{
	return geoString::fromDB($value);
}
