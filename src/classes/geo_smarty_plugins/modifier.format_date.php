<?php
//modifier.format_date.php
##########GIT Build Data##########
## 
## File Changed In GIT Commit:
## 
##    6.0.7-2-gc953682
## 
##################################

//this smarty plugin is for displayPrice modifier

function smarty_modifier_format_date ($date, $format = null)
{
	if ($format === null) {
		$format = DataAccess::getInstance()->get_site_setting('entry_date_configuration');
	}
	return date($format,$date);
}
