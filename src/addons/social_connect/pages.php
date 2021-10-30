<?php

//FILE_LOCATION/FILE_NAME.php

# social connect

require_once ADDON_DIR . 'social_connect/info.php';

class addon_social_connect_pages extends addon_social_connect_info
{
    public function merge_accounts()
    {
        //this is used for internal user only
        return '<h1>Page used internally only.</h1><!-- Nice try though!  -->';
    }
}
