<?php

//function.listing.php


//This fella takes care of {module ...}

function smarty_function_listing($params, Smarty_Internal_Template $smarty)
{
    //check to make sure all the parts are there
    if (!isset($params['tag']) && !isset($params['field'])) {
        //One of the required parts is not specified
        return '{listing tag syntax error}';
    }

    //figure out the listing ID
    $listing_id = geoListing::smartyGetListingId($params, $smarty);

    if (!$listing_id) {
        //could not figure out listing ID!  Perhaps used on a generic location, don't
        //throw any errors or something, just be blank.
        return '';
    }

    $listing = geoListing::getListing($listing_id);
    if (!$listing) {
        //invalid listing or something?
        return '';
    }

    if (isset($params['tag'])) {
        //this is most common...  show contents of one of the built-in tags

        $tag = $params['tag'];

        unset($params['tag']);

        if (isset($params['addon'])) {
            //Let addon take care of it!
            $addonName = $params['addon'];
            unset($params['addon']);

            //set the listing_id in the params, this is the "special" thing listing
            //addon tags do, so the addon tag doesn't have to do work to figure out
            //which one to use
            $params['listing_id'] = $listing_id;
            return geoAddon::getInstance()->smartyDisplayTag($params, $smarty, $addonName, $tag, 'listing');
        }

        //OK we have the tag, the listing_id and the listing object...
        return $listing->smartyDisplayTag($tag, $params, $smarty);
    } elseif (isset($params['field'])) {
        //Show pre-formatted field...  or possibly something else, but let
        //listing class take care of things from here.

        $field = $params['field'];

        unset($params['field']);
        //note:  smartyDisplayField accounts for assign=... in params.
        return $listing->smartyDisplayField($field, $params, $smarty);
    }
}
