<?php

//addons/sharing/methods/myspace.php

class addon_sharing_method_myspace
{

    //myspace is no longer a social network or something that can be "shared to"

    public $name = 'myspace';

    /**
     * Gets the name of any methods that want to be used for this listing id.
     * Note that this function being called in the first place implies that the listing in question is live and belongs to the current user
     * @param int $listingId
     * @return String the name of any available method, sans any formatting
     */
    public function getMethodsForListing($listingId)
    {
        return '';
    }

    /**
     * Gets the full HTML to show in the "options" block of the main addon page.
     * This function is responsible for any needed templatization to generate that HTML.
     * @return String HTML
     */
    public function displayOptions()
    {
        return '';
    }

    public function getShortLink($listingId, $iconOnly = false)
    {
        return '';
    }
}
