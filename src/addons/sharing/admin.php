<?php

//addons/sharing/admin.php

# sharing Addon

class addon_sharing_admin extends addon_sharing_info
{

    public function init_text($language_id)
    {
        $return_var = array (
            'listing_box_label' => array(
                    'name' => 'Listing Box Label',
                    'desc' => '',
                    'type' => 'input',
                    'default' => 'Share a Listing'
            ),
            'listing_ddl_label' => array(
                    'name' => 'Listing Dropdown Label',
                    'desc' => '',
                    'type' => 'input',
                    'default' => 'Your Listings:'
            ),
            'method_box_label' => array(
                    'name' => 'Method Box Label',
                    'desc' => '',
                    'type' => 'input',
                    'default' => 'Select a Share Method'
            ),
            'options_box_label' => array(
                    'name' => 'Options Box Label',
                    'desc' => '',
                    'type' => 'input',
                    'default' => 'Select Sharing Options'
            ),
            'no_listings' => array(
                    'name' => 'No Listings message',
                    'desc' => 'Shown when a user visits the Sharing page but has no shareable listings',
                    'type' => 'input',
                    'default' => 'You have no listings to share. Place a listing first.'
            ),
            'my_account_links_label' => array(
                    'name' => 'My Account Links label',
                    'desc' => 'Text of the navigation link to the sharing page',
                    'type' => 'input',
                    'default' => 'Sharing'
            ),
            'my_account_links_icon' => array(
                    'name' => 'My Account Links icon',
                    'desc' => 'optional image appearing beside the navigation link to the sharing page',
                    'type' => 'input',
                    'default' => ''
            ),
            'method_btn_craigslist' => array(
                    'name' => 'Select a Method Button - Craigslist',
                    'desc' => '',
                    'type' => 'input',
                    'default' => 'Craigslist'
            ),
            'method_btn_facebook' => array(
                    'name' => 'Select a Method Button - Facebook',
                    'desc' => '',
                    'type' => 'input',
                    'default' => 'Facebook'
            ),
            'method_btn_twitter' => array(
                    'name' => 'Select a Method Button - Twitter',
                    'desc' => '',
                    'type' => 'input',
                    'default' => 'Twitter'
            ),
            //removed with the death of digg
//          'method_btn_digg' => array(
//                  'name' => 'Select a Method Button - Digg',
//                  'desc' => '',
//                  'type' => 'input',
//                  'default' => 'Digg'
//          ),
            'method_btn_reddit' => array(
                    'name' => 'Select a Method Button - Reddit',
                    'desc' => '',
                    'type' => 'input',
                    'default' => 'Reddit'
            ),
            //removed with the death of myspace
//          'method_btn_myspace' => array(
//                  'name' => 'Select a Method Button - Myspace',
//                  'desc' => '',
//                  'type' => 'input',
//                  'default' => 'Myspace'
//          ),
            'method_btn_linkedin' => array(
                    'name' => 'Select a Method Button - LinkedIn',
                    'desc' => '',
                    'type' => 'input',
                    'default' => 'LinkedIn'
            ),
            'craigslist_options_tpl_choice' => array(
                    'name' => 'Craigslist - Label for template selection',
                    'desc' => '',
                    'type' => 'input',
                    'default' => 'Select a template to use'
            ),
            'craigslist_preview_btn' => array(
                    'name' => 'Craigslist - Preview button',
                    'desc' => '',
                    'type' => 'input',
                    'default' => 'Preview'
            ),
            'craigslist_html_btn' => array(
                    'name' => 'Craigslist - Show HTML button',
                    'desc' => '',
                    'type' => 'input',
                    'default' => 'Get Craigslist HTML Code'
            ),
            'craigslist_price_label' => array(
                    'name' => 'Craigslist - Price Label',
                    'desc' => 'labels the Price field in the default Craigslist output',
                    'type' => 'input',
                    'default' => 'Price'
            ),
            'craigslist_desc_label' => array(
                    'name' => 'Craigslist - Price Label',
                    'desc' => 'labels the Description field in the default Craigslist output',
                    'type' => 'input',
                    'default' => 'Description'
            ),
            'craigslist_img_label' => array(
                    'name' => 'Craigslist - Price Label',
                    'desc' => 'labels the Images field in the default Craigslist output',
                    'type' => 'input',
                    'default' => 'Images'
            ),
            'craigslist_html_instructions' => array(
                    'name' => 'Craigslist - Show HTML page - page instructions',
                    'desc' => '',
                    'type' => 'input',
                    'default' => '<strong>Instructions:</strong> Copy and paste these values into the corresponding fields of the craigslist listing placement process.'
            ),
            'craigslist_html_boxTitle' => array(
                    'name' => 'Craigslist - Show HTML page - popup box title',
                    'desc' => '',
                    'type' => 'input',
                    'default' => 'HTML for Craigslist'
            ),
            'craigslist_html_previewTitle' => array(
                    'name' => 'Craigslist - Show HTML page - preview box title',
                    'desc' => '',
                    'type' => 'input',
                    'default' => 'Craigslist Preview'
            ),
            'craigslist_html_title' => array(
                    'name' => 'Craigslist - Show HTML page - title label',
                    'desc' => '',
                    'type' => 'input',
                    'default' => 'Posting Title:'
            ),
            'craigslist_html_price' => array(
                    'name' => 'Craigslist - Show HTML page - price label',
                    'desc' => '',
                    'type' => 'input',
                    'default' => 'Price:'
            ),
            'craigslist_html_desc' => array(
                    'name' => 'Craigslist - Show HTML page - description label',
                    'desc' => '',
                    'type' => 'input',
                    'default' => 'Posting Description:'
            ),
            'sharing_link_text' => array(
                    'name' => 'Sharing Link Title',
                    'desc' => 'hover text shown when the cursor is over the sharing link',
                    'type' => 'input',
                    'default' => 'Share'
            ),
            'shortlink_facebook' => array(
                    'name' => 'Shortlink text - Facebook',
                    'desc' => 'text of a shortlink in the listing display popup',
                    'type' => 'input',
                    'default' => 'facebook'
            ),
            'shortlink_twitter' => array(
                    'name' => 'Shortlink text - Twitter',
                    'desc' => 'text of a shortlink in the listing display popup',
                    'type' => 'input',
                    'default' => 'twitter'
            ),
//          'shortlink_digg' => array(
//                  'name' => 'Shortlink text - Digg',
//                  'desc' => 'text of a shortlink in the listing display popup',
//                  'type' => 'input',
//                  'default' => 'digg'
//          ),
            'shortlink_reddit' => array(
                    'name' => 'Shortlink text - Reddit',
                    'desc' => 'text of a shortlink in the listing display popup',
                    'type' => 'input',
                    'default' => 'reddit'
            ),
//          'shortlink_myspace' => array(
//                  'name' => 'Shortlink text - Myspace',
//                  'desc' => 'text of a shortlink in the listing display popup',
//                  'type' => 'input',
//                  'default' => 'myspace'
//          ),
            'shortlink_linkedin' => array(
                    'name' => 'Shortlink text - LinkedIn',
                    'desc' => 'text of a shortlink in the listing display popup',
                    'type' => 'input',
                    'default' => 'linkedin'
            ),
            'shortlink_more' => array(
                    'name' => 'Shortlink text - More',
                    'desc' => 'text of a shortlink in the listing display popup',
                    'type' => 'input',
                    'default' => 'more'
            ),
            'popup_title' => array(
                    'name' => 'Popup title',
                    'desc' => '',
                    'type' => 'input',
                    'default' => 'Share This'
            ),
        );
        return $return_var;
    }

    public function init_pages()
    {
        menu_page::addonAddPage('sharing_settings', '', 'Settings', $this->name, $this->icon_image);
    }

    public function display_sharing_settings()
    {
        $view = geoView::getInstance();
        $util = geoAddon::getUtil($this->name);
        $reg = geoAddon::getRegistry($this->name);
        $tpl_vars = array();

        $methods = $util->getAllMethods();
        foreach ($methods as $method) {
            $tpl_vars['methods'][$method->name] = $reg->get("method_{$method->name}_is_enabled");
        }
        $tpl_vars['fb_app_id'] = $reg->fb_app_id;
        $tpl_vars['adminMessages'] = geoAdmin::m();

        $view->setBodyTpl('admin/settings.tpl', $this->name)
        ->setBodyVar($tpl_vars);
    }

    public function update_sharing_settings()
    {
        $reg = geoAddon::getRegistry($this->name);
        $enabled = $_POST['enabled'];
        $util = geoAddon::getUtil($this->name);
        $methods = $util->getAllMethods();
        foreach ($methods as $name => $obj) {
            $reg->set("method_{$name}_is_enabled", ($enabled[$name] ? 1 : 0));
        }
        $reg->fb_app_id = (int)$_POST['fb_app_id'];
        $reg->save();
        return true;
    }
}
