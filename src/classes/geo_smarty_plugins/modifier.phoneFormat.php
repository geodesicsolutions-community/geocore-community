<?php

//modifier.phoneFormat.php


//this smarty plugin is for phoneFormat modifier

function smarty_modifier_phoneFormat($value)
{
    return geoNumber::phoneFormat($value);
}
