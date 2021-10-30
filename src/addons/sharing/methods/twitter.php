<?php

//addons/sharing/methods/twitter.php

class addon_sharing_method_twitter
{

    public $name = 'twitter';

    /**
     * Gets the name of any methods that want to be used for this listing id.
     * Note that this function being called in the first place implies that the listing in question is live and belongs to the current user
     * @param int $listingId
     * @return String the name of any available method, sans any formatting
     */
    public function getMethodsForListing($listingId)
    {

        //we want this to be available for all listings, so simply return the name to show
        $msgs = geoAddon::getText('geo_addons', 'sharing');
        return $msgs['method_btn_twitter'];
    }

    /**
     * Gets the full HTML to show in the "options" block of the main addon page.
     * This function is responsible for any needed templatization to generate that HTML.
     * @return String HTML
     */
    public function displayOptions()
    {
        $data = $_POST;

        $msgs = geoAddon::getText('geo_addons', 'sharing');

        $tpl = new geoTemplate('addon', 'sharing');
        $tpl->assign('urlToListing', urlencode(geoListing::getListing($data['listing'])->getFullUrl()));
        $html = $tpl->fetch('methods/twitter_options.tpl');
        return $html;
    }

    public function getShortLink($listingId, $iconOnly = false)
    {
        $tpl = new geoTemplate('addon', 'sharing');
        $tpl->assign('iconUrl', geoTemplate::getUrl('images', 'addon/sharing/icon_twitter.png'));
        $msgs = geoAddon::getText('geo_addons', 'sharing');
        $tpl->assign('text', $msgs['shortlink_twitter']);

        $urlToListing = urlencode(geoListing::getListing($listingId)->getFullUrl());
        $tpl->assign('link', 'http://twitter.com/share?url=' . $urlToListing);
        $tpl->assign('iconOnly', $iconOnly);

        return $tpl->fetch('shortLink.tpl');
    }
}
