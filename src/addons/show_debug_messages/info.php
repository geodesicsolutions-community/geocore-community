<?php

//addons/show_debug_messages/info.php

# Show Filtered Debug Messages addon

class addon_show_debug_messages_info
{
    //The following are required variables
    var $name = 'show_debug_messages';
    var $version = '1.0.0';
    var $title = 'Show Debug Messages';
    var $author = "Geodesic Solutions LLC.";
    var $icon_image = 'menu_debug2.gif';
    var $info_url = 'http://geodesicsolutions.com/component/content/article/49-programming-trouble-shooting/71-debug-message-display.html?directory=64';
    var $description = 'After this is enabled, you must also specify in the URL the keywords to filter debug messages by.';
    var $auth_tag = 'geo_addons';

    var $core_events = array ( 'errorhandle',
    //'filter_display_page', //do NOT filter display page, or output will be cached!
    'filter_display_page_nocache', //instead, filter the nocache version.
    'app_bottom');
}
