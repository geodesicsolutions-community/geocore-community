<?php

$user_id = $page->userid;
$tpl_vars = array();
//to allow easier customizing at the template level, let template know what
//format to display username with
$tpl_vars['username_format'] = $show_module['module_display_username'];

if ($user_id) {
    $user = geoUser::getUser($user_id);
    if ($user) {
        //give template all the user's data to give more options to displaying
        //user
        $tpl_vars['userData'] = $user->toArray();
        switch ($show_module['module_display_username']) {
            case 1:
                //display username only
                $display = $user->username;
                break;

            case 2:
                //display firstname only
                $display = stripslashes($user->firstname);
                break;

            case 3:
                //display lastname only
                $display = stripslashes($user->lastname);
                break;

            case 4:
                //display firstname lastname
                $display = stripslashes($user->firstname . " " . $user->lastname);
                break;

            case 5:
                //display lastname, firstname
                $display =  stripslashes($user->lastname . " " . $user->firstname);
                break;

            case 6:
                //display email address
                $display = geoString::fromDB($user->email);
                break;
            default:
                //default display username only
                $display = $user->username;
                break;
        }
        if (strlen(trim($display)) == 0) {
            //selected display name is blank for this user. fall back on username
            $display = $user->username;
        }
        $tpl_vars['userLabel'] = $display;
    }
}

$view->setModuleTpl($show_module['module_replace_tag'], 'index')
    ->setModuleVar($show_module['module_replace_tag'], $tpl_vars);
