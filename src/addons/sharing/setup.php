<?php

//addons/sharing/setup.php

# sharing Addon
require_once ADDON_DIR . 'sharing/info.php';

class addon_sharing_setup extends addon_sharing_info
{
    public function install()
    {
        $this->enableStartingMethods();
        return true;
    }

    public function upgrade($old_version)
    {
        if (version_compare($old_version, '1.2.0', '<')) {
            $this->enableStartingMethods();
        }
        return true;
    }

    private function enableStartingMethods()
    {
        $reg = geoAddon::getRegistry($this->name, true);
        $startingMethods = array('craigslist','facebook','google_plus','linkedin','twitter','myspace','pinterest','reddit');
        foreach ($startingMethods as $m) {
            $reg->set("method_{$m}_is_enabled", 1);
        }
        $reg->save();
    }
}
