<?php

//addons/signs_flyers/setup.php

# Signs and flyers Addon

require_once ADDON_DIR . 'signs_flyers/info.php';

class addon_signs_flyers_setup extends addon_signs_flyers_info
{

    public function install()
    {
        //add some default stuff
        $db = 1;
        include GEO_BASE_DIR . 'get_common_vars.php';

        $sql = "INSERT INTO `geodesic_choices` (`type_of_choice`, `display_value`, `value`, `numeric_value`, `display_order`, `language_id`) VALUES
(14, 'No Photo Available', 'images/sign_flyer/sign_flyer_no_photo.gif', 0, 0, 1),
(13, 'No Photo Available', 'images/sign_flyer/sign_flyer_no_photo.gif', 0, 0, 1),
(14, 'No Photo Available - Automobiles', 'images/sign_flyer/sign_flyer_auto.gif', 0, 0, 1),
(13, 'No Photo Available - Automobiles', 'images/sign_flyer/sign_flyer_auto.gif', 0, 0, 1),
(14, 'No Photo Available - Real Estate', 'images/sign_flyer/sign_flyer_realestate.gif', 0, 0, 1),
(13, 'No Photo Available - Real Estate', 'images/sign_flyer/sign_flyer_realestate.gif', 0, 0, 1),
(14, 'No Photo Available - Equine', 'images/sign_flyer/sign_flyer_equine.gif', 0, 0, 1),
(13, 'No Photo Available -  Equine', 'images/sign_flyer/sign_flyer_equine.gif', 0, 0, 1)";

        $db->Execute($sql);
        return true;
    }

    public function uninstall()
    {
        $db = 1;
        include GEO_BASE_DIR . 'get_common_vars.php';

        $sql = "DELETE FROM `geodesic_choices` WHERE `type_of_choice` IN (13,14)";

        $db->Execute($sql);
        return true;
    }
}
