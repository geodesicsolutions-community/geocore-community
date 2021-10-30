<?php

$sql = "SELECT count(*) counter FROM geodesic_sessions";
$logged_result = $this->GetRow($sql);
$count = (isset($logged_result['counter'])) ? $logged_result['counter'] : 0;

$view->setModuleTpl($show_module['module_replace_tag'], 'index')
    ->setModuleVar($show_module['module_replace_tag'], 'live_users', $count);
