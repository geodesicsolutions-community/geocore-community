<?php

//addons/attention_getters/setup.php

# Attention getters Addon

require_once ADDON_DIR . 'attention_getters/info.php';

class addon_attention_getters_setup extends addon_attention_getters_info
{
    public function install()
    {
        $util = geoAddon::getUtil($this->name, true);

        if (!$util->autoAdd('addons/attention_getters/images/banners/')) {
            geoAdmin::m('Error automatically adding new images from directory "addons/attention_getters/images/banners/" - you may need to add manually.', geoAdmin::NOTICE);
        }
        return true;
    }

    public function uninstall()
    {
        $db = 1;
        include GEO_BASE_DIR . 'get_common_vars.php';
        $db->Execute("DELETE FROM " . geoTables::choices_table . " WHERE `type_of_choice`=10");
        return true;
    }
}
