<?php

//addons/debugger_log/info.php

# Log Debug Messages to file Addon


class addon_debugger_log_info
{
    //The following are required variables
    var $name = 'debugger_log';
    var $version = '1.0.1';
    var $title = 'Debugging Logger';
    var $author = "Geodesic Solutions LLC.";
    var $description = 'This addon logs any debugging messages to the file
log.php.  Once installed & enabled, the settings for what is logged are found in <strong>Admin Tools & Settings > Debug Log Settings</strong><br />
<br />
Like any logging addon, keeping this addon enabled will add some overhead to
every page load.  Once you no longer need to log debugging messages, you should disable this addon to increase site performance,
especially on a high traffic site.';
    //used in referencing tags, and maybe other uses in the future.
    var $auth_tag = 'geo_addons';


    ##Optional Vars##
    //if these vars are included, they will be used.

    var $icon_image = 'menu_debug.gif'; //located in addons/example/icon.gif

    //URL's.  If any of these exist, they will be linked to where appropriate in the
    //admin page.  Note that you can link to your own site, or to a relative page.
    //Keep in mind, if using a relative link, the link will not work when
    //the addon is disabled.
    var $core_events = array (
    'errorhandle'
    );

    public function enableCheck()
    {
        //init stuff here so it isn't a problem if it generates a message
        include_once ADDON_DIR . 'debugger_log/util.php';
        $util = Singleton::getInstance('addon_debugger_log_util');

        if ($util->_init) {
            return true;
        }
        if (!is_writable(ADDON_DIR . 'debugger_log/log.php')) {
            $util->tags = false;
            return true;
        }
        $db = true;
        include(GEO_BASE_DIR . 'get_common_vars.php');

        if ($db->get_site_setting('addon_debugger_log_require_cookie') && !isset($_COOKIE['debug_log'])) {
            //required to have cookie set, but cookie is not set
            $util->tags = false;
            return true;
        }

        $util->tags['debug'] = explode('|', $db->get_site_setting('addon_debugger_log_debug_tags'));
        $util->tags['error'] = explode('|', $db->get_site_setting('addon_debugger_log_error_tags'));
        $util->_init = true;

        return true;
    }
}
