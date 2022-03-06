<?php

function smarty_outputfilter_add_basetag($output, Smarty_Internal_Template $smarty)
{
    if ($smarty->source->type == 'geo_tset' && $smarty->gType() == 'main_page') {
        //this is a geo tset, so do the filter
        return geoFilter::baseHref($output);
    }
    //not a main_page template, don't bother filtering
    return $output;
}
