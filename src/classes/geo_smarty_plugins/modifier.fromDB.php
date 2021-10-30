<?php
//modifier.fromDB.php


//this smarty plugin is for fromDB modifier

function smarty_modifier_fromDB ($value)
{
	return geoString::fromDB($value);
}
