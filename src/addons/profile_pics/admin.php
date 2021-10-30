<?php

//addons/profile_pics/admin.php


require_once ADDON_DIR . 'profile_pics/info.php';

class addon_profile_pics_admin extends addon_profile_pics_info
{

    public function init_pages()
    {
        menu_page::addonAddPage('profile_pic_config', '', 'Configuration', $this->name);
    }

    public function init_text($languageId)
    {
        $return = array
        (
            'my_account_uploader_label' => array (
                'name' => 'My Account Info Uploader Label',
                'desc' => '',
                'type' => 'input',
                'default' => 'Profile Picture',
                'section' => 'Uploader'
            ),
            'btn_upload_new' => array (
                    'name' => 'Upload New button',
                    'desc' => '',
                    'type' => 'input',
                    'default' => 'Upload New',
                    'section' => 'Uploader'
            ),
            'btn_save' => array (
                    'name' => 'Save button',
                    'desc' => '',
                    'type' => 'input',
                    'default' => 'Save',
                    'section' => 'Uploader'
            ),
        );

        return $return;
    }

    public function display_profile_pic_config()
    {
        $tpl_vars = array();
        $tpl_vars['admin_msgs'] = geoAdmin::m();

        $reg = geoAddon::getRegistry($this->name);
        $tpl_vars['viewport_width'] = $reg->viewport_width;
        $tpl_vars['viewport_height'] = $reg->viewport_height;
        $tpl_vars['boundary_width'] = $reg->boundary_width;
        $tpl_vars['boundary_height'] = $reg->boundary_height;


        geoView::getInstance()->setBodyTpl('admin/config.tpl', $this->name)->setBodyVar($tpl_vars);
    }

    public function update_profile_pic_config()
    {
        $reg = geoAddon::getRegistry($this->name);

        $reg->viewport_width = (int)$_POST['viewport_width'];
        $reg->viewport_height = (int)$_POST['viewport_height'];
        $reg->boundary_width = (int)$_POST['boundary_width'];
        $reg->boundary_height = (int)$_POST['boundary_height'];
        $reg->save();
        return true;
    }
}
