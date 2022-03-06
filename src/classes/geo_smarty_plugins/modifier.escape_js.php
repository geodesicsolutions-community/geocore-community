<?php

//modifier.escape_js.php


//this is custom smarty plugin modifier

function smarty_modifier_escape_js($string)
{
    $string = preg_replace('/[\r\n\t]+/', ' ', $string);
    $string = addslashes($string);
    return $string;
}
