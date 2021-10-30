<?php

if (geoPC::is_ent() || geoPC::is_premier() || geoPC::is_basic()) {
    $tpl_vars = array (
        'href' => $page->configuration_data['classifieds_file_name'] . "?a=8&amp;b=" . $page->site_category,
        'class' => 'featured_pic_link_text',
        'label' => $page->messages[1059]
    );

    $view->setModuleTpl($show_module['module_replace_tag'], 'index')
        ->setModuleVar($show_module['module_replace_tag'], $tpl_vars);
}
