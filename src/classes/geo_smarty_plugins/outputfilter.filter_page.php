<?php
//outputfilter.filter_page.php


//this smarty plugin is nice

function smarty_outputfilter_filter_page ($output, Smarty_Internal_Template $smarty)
{
	return geoAddon::triggerDisplay('filter_display_page',$output, geoAddon::FILTER);
}
