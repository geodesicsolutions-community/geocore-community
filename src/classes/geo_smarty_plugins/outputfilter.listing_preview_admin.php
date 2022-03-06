<?php

//outputfilter.strip_forms.php


//this smarty plugin is nice

function smarty_outputfilter_listing_preview_admin($output, Smarty_Internal_Template $smarty)
{
    return geoFilter::baseHref($output, true);
}
