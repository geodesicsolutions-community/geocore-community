<?php

//addons/social_connect/tags.php

# Facebook Connect

require_once ADDON_DIR . 'social_connect/info.php';

class addon_social_connect_tags extends addon_social_connect_info
{
    public function facebook_login_button_auto_add_head()
    {
        geoView::getInstance()->addCssFile("addons/social_connect/facebook_button.css");
    }

    public function facebook_login_button($params, Smarty_Internal_Template $smarty)
    {
        $reg = geoAddon::getRegistry($this->name);

        $fb_app_id = $reg->get('fb_app_id');
        $fb_app_secret = $reg->get('fb_app_secret');
        if (!$fb_app_id || !$fb_app_secret) {
            //can't do it without app ID
            return '';
        }

        $tpl_vars = array();

        $util = geoAddon::getUtil($this->name);

        // Login or logout url will be needed depending on current user state.
        if ($util->user || defined('IN_ADMIN')) {
            //don't show login button
            return '';
        }
        $tpl_vars['loginUrl'] = $util->loginUrl();
        $tpl_vars['msgs'] = geoAddon::getText($this->auth_tag, $this->name);
        $tpl_vars['login_user'] = $util->login_user;

        return geoTemplate::loadInternalTemplate(
            $params,
            $smarty,
            'tags/facebook/login_button.tpl',
            geoTemplate::ADDON,
            $this->name,
            $tpl_vars
        );
    }

    public function facebook_session_profile_picture($params, Smarty_Internal_Template $smarty)
    {
        $util = geoAddon::getUtil($this->name);
        if (!$util->user) {
            //no profile pic to show
            return '';
        }
        $tpl_vars = array ('facebook_id' => $util->user);

        $tpl_vars['width'] = (int)$params['width'] ? (int)$params['width'] : false;

        return geoTemplate::loadInternalTemplate(
            $params,
            $smarty,
            'tags/facebook/session_profile_picture.tpl',
            geoTemplate::ADDON,
            $this->name,
            $tpl_vars
        );
    }

    public function facebook_listing_profile_picture($params, Smarty_Internal_Template $smarty)
    {
        $util = geoAddon::getUtil($this->name);

        $listingId = (isset($params['listing_id'])) ? (int)$params['listing_id'] : 0;

        if (!$listingId) {
            //allow working as a normal {addon} tag
            $view = geoView::getInstance();
            if (!$view->classified_id) {
                //id NOT set
                return '';
            }
            $listingId = (int)$view->classified_id;
        }
        $listing = geoListing::getListing($listingId);
        if (!$listing) {
            //something wrong
            return '';
        }
        $seller = (int)$listing->seller;
        if ($seller <= 1) {
            //seller was anon or admin or we don't know
            return '';
        }
        $tpl_vars = $util->getUserInfo($seller);

        if (!$tpl_vars) {
            //didn't get anything, nothing to base profile pic on
            return '';
        }

        $tpl_vars['width'] = (int)$params['width'] ? (int)$params['width'] : false;

        //even if user has set to not show profile pic, still let template deal with it
        //so people can do custom things when user doesn't want to show profile pic...
        return geoTemplate::loadInternalTemplate(
            $params,
            $smarty,
            'tags/facebook/listing_profile_picture.tpl',
            geoTemplate::ADDON,
            $this->name,
            $tpl_vars
        );
    }

    public function facebook_session_app_friends($params, Smarty_Internal_Template $smarty)
    {
        $util = geoAddon::getUtil($this->name);
        $friends = $util->getAppFriends();
        if (!$friends) {
            //no friends to display
            return '';
        }
        $tpl_vars = array ('friends' => $friends);
        return geoTemplate::loadInternalTemplate(
            $params,
            $smarty,
            'tags/facebook/session_app_friends.tpl',
            geoTemplate::ADDON,
            $this->name,
            $tpl_vars
        );
    }
}
