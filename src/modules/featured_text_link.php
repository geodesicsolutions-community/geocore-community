<?php

if (geoPC::is_ent() || geoPC::is_premier() || geoPC::is_basic()) {
    $tpl_vars = array (
        'href' => $page->configuration_data['classifieds_file_name'] . "?a=9&amp;b=" . $page->site_category,
        'class' => 'featured_text_link_text',
        'label' => $page->messages[1061]
    );

    $view->setModuleTpl($show_module['module_replace_tag'], 'index')
        ->setModuleVar($show_module['module_replace_tag'], $tpl_vars);
}
