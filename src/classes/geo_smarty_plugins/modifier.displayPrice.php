<?php
//modifier.displayPrice.php


//this smarty plugin is for displayPrice modifier

function smarty_modifier_displayPrice ($value, $pre = false, $post = false, $type = null)
{
	return geoString::displayPrice($value, $pre, $post, $type);
}
