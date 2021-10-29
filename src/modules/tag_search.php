<?php

//tag_search.php
/**************************************************************************
Geodesic Classifieds & Auctions Platform 18.02
Copyright (c) 2001-2018 Geodesic Solutions, LLC
All rights reserved
http://geodesicsolutions.com
see license attached to distribution
**************************************************************************/
##########GIT Build Data##########
##
## File Changed In GIT Commit:
## ##    6.0.7-2-gc953682
##
##################################

//tag search module, displays auto-complete tag search box

if (geoPC::is_print() && $this->get_site_setting('disableAllBrowsing')) {
    //browsing disabled, do not show module contents
    return;
}

$tpl_vars = array();

$tpl_vars['helpLink'] = $page->display_help_link(500872);

$tpl_vars['current_tag'] = (isset($_GET['tag'])) ? geoFilter::cleanListingTag($_GET['tag']) : '';

$view->setModuleTpl($show_module['module_replace_tag'], 'index')
    ->setModuleVar($show_module['module_replace_tag'], $tpl_vars);
