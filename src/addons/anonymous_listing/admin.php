<?php

//addons/anonymous_listing/admin.php

# Anonymous Listing Addon

class addon_anonymous_listing_admin extends addon_anonymous_listing_info
{

    function addon_anonymous_listing_admin()
    {
        if (Singleton::isInstance('Admin_site')) {
            if (strlen(PHP5_DIR)) {
                $this->admin_site = Singleton::getInstance('Admin_site');
                $this->db = DataAccess::getInstance();
            } else {
                $this->admin_site =& Singleton::getInstance('Admin_site');
                $this->db =& DataAccess::getInstance();
            }
        } else {
            //if the admin site does not exist yet, something weird is going on,
            //since the admin site should have been the class to initialize this.
            return false;
        }
    }

    function init_text($language_id)
    {
        $return_var = array();
        $return_var['passwordLabel'] = array (
            'name' => 'Password prompt',
            'desc' => 'Labels the text entry field that allows a user to input the anonymous edit password for a listing',
            'type' => 'textarea',
            'default' => 'Input the password to edit this listing: '
        );
        $return_var['passwordButtonText'] = array (
            'name' => 'Password Button Label',
            'desc' => 'Labels the submit button on the anonymous edit pasword input form',
            'type' => 'textarea',
            'default' => 'Submit'
        );
        $return_var['passwordError'] = array (
            'name' => 'Password Error',
            'desc' => 'Error message displayed if the wrong anonymous edit password is given',
            'type' => 'textarea',
            'default' => 'Incorrect password. Please try again.'
        );
        $return_var['passwordCancelLink'] = array (
            'name' => 'Password Cancel Link',
            'desc' => 'Text of link that cancels the edit attempt, on anonymous edit password form',
            'type' => 'textarea',
            'default' => 'Cancel Edit'
        );

        $return_var['placementText1'] = array (
            'name' => 'Placement Text 1',
            'desc' => 'Text on anonymous page during listing placement process',
            'type' => 'textarea',
            'default' => 'You are placing this listing anonymously.'
        );

        $return_var['placementText2'] = array (
            'name' => 'Placement Text 2',
            'desc' => 'Text on anonymous page during listing placement process',
            'type' => 'textarea',
            'default' => 'You will be able to edit it later by using the following password:'
        );

        $return_var['placementContinueLink'] = array (
            'name' => 'Placement Continue Link',
            'desc' => 'Text of link that continues past anonymous page in placement process',
            'type' => 'textarea',
            'default' => 'Continue placing this listing'
        );
        $return_var['placementCancelLink'] = array (
            'name' => 'Placement Cancel Link',
            'desc' => 'Text of link that cancels the edit attempt, during placement process',
            'type' => 'textarea',
            'default' => 'Cancel Listing'
        );
        $return_var['emailText'] = array (
            'name' => 'Email Text',
            'desc' => 'Text of line added to listing confirmation email prefacing edit password (for anonymous listings only)',
            'type' => 'textarea',
            'default' => 'You have placed this listing anonymously. To edit it in the future, you will need to input this password: '
        );

        $return_var['emailEditLinkLabel'] = array (
            'name' => 'Listing Complete Email: Edit Link Label',
            'desc' => 'Used in the email sent when an anonymous listing is placed successfully, to label the Edit link',
            'type' => 'textarea',
            'default' => 'Use the link below to edit this listing.'
        );

        $return_var['browseHeader'] = array (
            'name' => 'Browse Edit Header',
            'desc' => 'Header for the (anonymous) Edit Button column on category browsing pages',
            'type' => 'textarea',
            'default' => 'Edit Anonymous Listing'
        );

        $return_var['passPageTitle'] = array (
            'name' => 'Enter Password page title',
            'desc' => 'Title used on the page the user enters the anonymous password.',
            'type' => 'input',
            'default' => 'Edit Anonymous Listing'
        );

        $return_var['stepEditLabel'] = array (
            'name' => 'CART STEP: Anonymous Password',
            'desc' => 'Label used for the step where user is required to enter the passwod (listing edit).',
            'type' => 'input',
            'default' => 'Password'
        );
        $return_var['stepLabel'] = array (
            'name' => 'CART STEP: Anonymous Password Display',
            'desc' => 'Label used for the step where password is generated and shown to user (new listing).',
            'type' => 'input',
            'default' => 'Generate Pass'
        );
        $return_var['eulaLabel'] = array (
                'name' => 'User Agreement Label',
                'desc' => 'Labels the User Agreement optionally shown on the Anonymous step of the listing process',
                'type' => 'input',
                'default' => 'I have read and agree to the <a href="index.php?a=28&b=140" onclick="window.open(this.href); return false;">Terms of Use</a>.'
        );
        $return_var['eulaError'] = array (
                'name' => 'User Agreement Error',
                'desc' => 'Shown when someone doesn\'t agree to the User Agreement',
                'type' => 'input',
                'default' => 'You must agree to the Terms of Use to continue.'
        );

        return $return_var;
    }

    function init_pages()
    {
        //menu_page::addonAddPage($index, $parent, $title, $addon_name, $image, $type);
        menu_page::addonAddPage('anonymous_listing_options', '', 'Settings', 'anonymous_listing', $this->icon_image);
    }

    function display_anonymous_listing_options()
    {
        $menu_loader = geoAdmin::getInstance();

        $this->admin_site->body = $menu_loader->getUserMessages();

        $this->admin_site->body .= '<form action="" method="post" class="form-horizontal">';

        $registry = geoAddon::getRegistry('anonymous_listing');
        $anonymous_username = $registry->get('anon_user_name', 'Anonymous');

        $label = "Anonymous \"user\" name ";
        $label .= geoHTML::showTooltip('Anonymous "user" name', 'This is the name that will be used to replace the {$seller} tag in classified templates when viewing a listing that has been posted anonymously');
        $field = '<input type="text" class="form-control" value="' . $anonymous_username . '" name="anon_info[username]" />';
        $this->admin_site->body .= '<div class="form-group">
										<label class="control-label col-xs-12 col-sm-5">' . $label . '</label>
										<div class="col-xs-12 col-sm-6">' . $field . '</div>
									</div>';

        $label = "Require User Agreement ";
        $label .= geoHTML::showTooltip('Require User Agreement', 'If checked, requires anonymous listers to agree to the same User Agreement shown during normal registration before they can complete listing placement.');
        $field = '<input type="checkbox" value="1" ' . ($registry->get('use_eula') == 1 ? 'checked="checked"' : '') . ' name="anon_info[use_eula]" />';
        $this->admin_site->body .= '<div class="form-group">
										<label class="control-label col-xs-12 col-sm-5">' . $label . '</label>
										<div class="col-xs-12 col-sm-6">' . $field . '</div>
									</div>';

        $submit = '<input type="submit" value="Save" name="auto_save" />';
        $this->admin_site->body .= "<div class='center'>$submit</div>";
        $this->admin_site->body .= '</form>';


        $this->admin_site->display_page();
    }

    function update_anonymous_listing_options()
    {
        $registry = geoAddon::getRegistry('anonymous_listing');
        $data = $_POST['anon_info'];
        $username = $data['username'];
        $registry->set('anon_user_name', $username);
        $registry->set('use_eula', ($data['use_eula'] == 1 ? 1 : 0));
        $registry->save();
        return true;
    }
}
