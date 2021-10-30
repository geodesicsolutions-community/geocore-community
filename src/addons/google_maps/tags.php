<?php

//addons/example/tags.php


# Example Addon
require_once ADDON_DIR . 'google_maps/info.php';
class addon_google_maps_tags extends addon_google_maps_info
{
    /**
     * This is the "old" tag name, here so that people that had google maps before
     * don't have to do anything when they update.
     */
    public function user_map()
    {
        return $this->listing_map();
    }
    public function user_map_auto_add_head()
    {
        $this->listing_map_auto_add_head();
    }

    public function listing_map_auto_add_head()
    {
        //add needed head stuff
        if (geoSession::isSSL()) {
            //gmaps are non-https only. leave an error message here to make diagnosis easier if somebody tries doing it anyway
            trigger_error('ERROR SSL GOOGLE_MAPS: Trying to use GMaps over SSL/HTTPS! This will likely trigger a mixed-type browser error.');
        }

        $util = geoAddon::getUtil($this->name);
        $util->initHead();
    }

    public function listing_map($params, $smarty)
    {
        $util = geoAddon::getUtil($this->name);
        return $util->getMap($params, $smarty);
    }
}
