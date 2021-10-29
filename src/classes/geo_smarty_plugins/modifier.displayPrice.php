<?php
//modifier.displayPrice.php
##########GIT Build Data##########
## 
## File Changed In GIT Commit:
## 
##    6.0.7-2-gc953682
## 
##################################

//this smarty plugin is for displayPrice modifier

function smarty_modifier_displayPrice ($value, $pre = false, $post = false, $type = null)
{
	return geoString::displayPrice($value, $pre, $post, $type);
}
