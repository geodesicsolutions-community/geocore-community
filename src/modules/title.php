<?php

$display = "";

$debug_title = 0;

trigger_error("DEBUG MODULES: top of title module");

$tpl_vars = array();
$tpl_vars['page_id'] = $page->page_id;

$tpl_vars['addonText'] = geoAddon::triggerDisplay('module_title_add_text');

$tpl_vars['addonTextPre'] = geoAddon::triggerDisplay('module_title_prepend_text');

$page->get_ad_configuration(0, 1);

if ($page->ad_configuration_data["title_module_language_display"] == 1) {
    $language_id = $this->getLanguage();
    if ($page->page_id == 1) {
        //get the language id from the listing itself as that is language of the important content irrespective of browsing language
        $listing_data = geoListing::getListing($_REQUEST['b']);
        $language_id = $listing_data->language_id;
    }
    //if not listing display page use the language id of the user browsing the site as they are seeing pages in that language
    $sql = "SELECT * FROM " . $page->pages_languages_table . " WHERE language_id=?";
    $language_result = $db->GetRow($sql, array($language_id));
    if ($language_result) {
        $tpl_vars["language_text"] = $page->messages[502382] . geoString::fromDB($language_result['language']) . $page->messages[502383];
    }
} else {
    $tpl_vars["language_text"] = "";
}

//pages that have their own functionality to discover title
$exception_array = array(1,2,3,84,10210);
if ((in_array($page->page_id, $exception_array)) && (!strlen(trim($tpl_vars['addonText'])))) {
    //default behavior, get title text to use
    $default_text = geoString::fromDB($page->ad_configuration_data['title_module_text']);
}

$get_username_array = array(22,23,24,25,26,27,28,29,30,37,38,43,10209,10157,10159,10175,10183,10184);
if (in_array($page->page_id, $get_username_array)) {
    //get the current logged in user's username to personalize the page title
    $user = geoUser::getUser(geoSession::getInstance()->getUserID());
    $username = trim($user->username);
}

$get_category_name_array = array(62,63,64);
if (in_array($page->page_id, $get_category_name_array)) {
    $category_name = geoCategory::getName(intval($_REQUEST['b']));
    $category_name = is_object($category_name) ? $category_name->CATEGORY_NAME : $category_name;
}
//it can be customized, like changing order of info and stuff.
switch ($page->page_id) {
    case 1:
        //listing details


        //find category name
        $tpl_vars['category_name'] = geoCategory::getName($page->site_category, true);

        //if this is a classified marked as sold, the "title" view var has the HTML
        //for the sold graphic prepended. Grab title fresh from the DB here to make sure
        //that doesn't happen
        $tpl_vars['titleOnly'] = geoListing::getTitle($view->classified_id);

        break;

    case 2:
        //front page of site : use text in the template
        //nothing specific to do in PHP portion...
        break;

    case 3:
        //category browsing
        $name = geoCategory::getName($page->site_category);
        if (is_object($name)) {
            //set the title module value for the category if set
            $name = ((strlen(trim($name->TITLE_MODULE)) > 0) ? $name->TITLE_MODULE : $name->CATEGORY_NAME);
        }
        $tpl_vars['category_title'] = geoString::fromDB($name);
        break;

    case 4: //notify friend form
        $text = ((strlen(trim($page->messages[502303])) > 0) ? ($page->messages[502303] . " '" . geoListing::getTitle($_REQUEST["b"]) . "'") : $default_text);
        break;
    case 6: //contact seller form
        $text = ((strlen(trim($page->messages[502304])) > 0) ? ($page->messages[502304] . " '" . geoListing::getTitle($_REQUEST["b"]) . "'") : $default_text);
        break;
    case 15: //registration information collection
        $text = ((strlen(trim($page->messages[502305])) > 0) ? $page->messages[502305] : $default_text);
        break;
    case 17: //registration information collection
        $text = ((strlen(trim($page->messages[502380])) > 0) ? $page->messages[502380] : $default_text);
        break;
    case 18: //registration success page
        $text = ((strlen(trim($page->messages[502307])) > 0) ? $page->messages[502307] : $default_text);
        break;
    case 19: //registration code form page
        $text = ((strlen(trim($page->messages[502306])) > 0) ? $page->messages[502306] : $default_text);
        break;
    case 22: //user current listings page
        $text = ((strlen(trim($page->messages[502308])) > 0) ? ($page->messages[502308] . " " . $username) : $default_text);
        break;
    case 23: //user expired listings page
        $text = ((strlen(trim($page->messages[502309])) > 0) ? ($page->messages[502309] . " " . $username) : $default_text);
        break;
    case 24: //user current communications page
        $text = ((strlen(trim($page->messages[502310])) > 0) ? ($page->messages[502310] . " " . $username) : $default_text);
        break;
    case 25: //view specific communication page
        $text = ((strlen(trim($page->messages[502311])) > 0) ? ($page->messages[502311] . " " . $username) : $default_text);
        break;
    case 26: //communication configuration page
        $text = ((strlen(trim($page->messages[502312])) > 0) ? ($page->messages[502312] . " " . $username) : $default_text);
        break;
    case 27: //listing filters/saved search page
        $text = ((strlen(trim($page->messages[502313])) > 0) ? ($page->messages[502313] . " " . $username) : $default_text);
        break;
    case 28: //add new listing filter/saved search form page
        $text = ((strlen(trim($page->messages[502314])) > 0) ? ($page->messages[502314] . " " . $username) : $default_text);
        break;
    case 30: //user favorites page
        $text = ((strlen(trim($page->messages[502315])) > 0) ? ($page->messages[502315] . " " . $username) : $default_text);
        break;
    case 31: //edit listing home
        $text = ((strlen(trim($page->messages[502316])) > 0) ? ($page->messages[502316]) : $default_text);
        break;
    case 35: //view expired listings detail page
        $listing_title = ((is_numeric($_REQUEST['c']) && ($_REQUEST['c'] != 0)) ? geoListing::getTitle($_REQUEST['c']) : '');
        $text = ((strlen(trim($listing_title)) > 0 ) ? ($page->messages[502317] . " '" . $listing_title . "'") : $default_text);
        break;
    case 36: //verify listing removal page
        $listing_title = ((is_numeric($_REQUEST['c']) && ($_REQUEST['c'] != 0)) ? geoListing::getTitle($_REQUEST['c']) : '');
        $text = ((strlen(trim($listing_title)) > 0 ) ? ($page->messages[502318] . " (" . $listing_title . ")") : $default_text);
        break;
    case 37: //view user personal information page
        $text = ((strlen(trim($page->messages[502319])) > 0) ? ($page->messages[502319] . " " . $username) : $default_text);
        break;
    case 38: //edit user personal information page
        $text = ((strlen(trim($page->messages[502320])) > 0) ? ($page->messages[502320] . " " . $username) : $default_text);
        break;
    case 39: //login page
        $text = ((strlen(trim($page->messages[502321])) > 0) ? $page->messages[502321] : $default_text);
        break;
    case 40: //lost password form page
        $text = ((strlen(trim($page->messages[502322])) > 0) ? $page->messages[502322] : $default_text);
        break;
    case 42: //language choice page
        $text = ((strlen(trim($page->messages[502323])) > 0) ? $page->messages[502323] : $default_text);
        break;
    case 43: //user management home page
        $text = ((strlen(trim($page->messages[502324])) > 0) ? ($page->messages[502324] . " " . $username) : $default_text);
        break;
    case 44: //search and search page results page
        if (isset($_REQUEST["b"]["search_text"]) && (strlen(trim($_REQUEST["b"]["search_text"])) > 0)) {
            $text = ((strlen(trim($page->messages[502324])) > 0) ? ($page->messages[502324] . " '" . urldecode($_REQUEST["b"]["search_text"]) . "'") : $default_text);
        } else {
            $text = ((strlen(trim($page->messages[502325])) > 0) ? $page->messages[502325] : $default_text);
        }
        break;
    case 55: //sellers other listings page
        //need sellers username
        $username = geoUser::userName((int)$_REQUEST['b']);
        $text = ((strlen(trim($page->messages[502326])) > 0) ? ($page->messages[502326] . " " . $username) : $default_text);
        break;
    case 56: //renew/upgrade listings page
        $text = ((strlen(trim($page->messages[502327])) > 0) ? $page->messages[502327] : $default_text);
        break;
    case 59: //site error page
        $text = ((strlen(trim($page->messages[502328])) > 0) ? $page->messages[502328] : $default_text);
        break;
    case 62: //browse featured pic listings page
        $text = ((strlen(trim($page->messages[502329])) > 0) ? ($page->messages[502329] . " " . $category_name) : $default_text);
        break;
    case 63: //browse featured listing text only page
        $text = ((strlen(trim($page->messages[502330])) > 0) ? ($page->messages[502330] . " " . $category_name) : $default_text);
        break;
    case 64: //browse newest listings page
        $text = ((strlen(trim($page->messages[502331])) > 0) ? ($page->messages[502331] . " " . $category_name) : $default_text);
        break;
    case 70: //flyer form page
        $text = ((strlen(trim($page->messages[502332])) > 0) ? $page->messages[502332] : $default_text);
        break;
    case 71: //sign form page
        $text = ((strlen(trim($page->messages[502333])) > 0) ? $page->messages[502333] : $default_text);
        break;
    case 72: //signs and flyers home list
        $text = ((strlen(trim($page->messages[502334])) > 0) ? $page->messages[502334] : $default_text);
        break;
    case 73: //flyer page
        $text = ((strlen(trim($page->messages[502335])) > 0) ? $page->messages[502335] : $default_text);
        break;
    case 74: //sign page
        $text = ((strlen(trim($page->messages[502336])) > 0) ? $page->messages[502336] : $default_text);
        break;
    case 84:
        //full size image display
        $name = geoCategory::getName($page->site_category);
        $name = is_object($name) ? $name->CATEGORY_NAME : $name;
        $tpl_vars['category_name'] = geoString::fromDB($name);
        break;
    case 115: //voting comments view
        $listing_title = ((is_numeric($_REQUEST['b']) && ($_REQUEST['b'] != 0)) ? geoListing::getTitle($_REQUEST['b']) : '');
        $text = ((strlen(trim($listing_title)) > 0 ) ? ($page->messages[502337] . " '" . $listing_title . "'") : $default_text);
        break;
    case 116: //voting form
        $listing_title = ((is_numeric($_REQUEST['b']) && ($_REQUEST['b'] != 0)) ? geoListing::getTitle($_REQUEST['b']) : '');
        $text = ((strlen(trim($listing_title)) > 0 ) ? ($page->messages[502338] . " '" . $listing_title . "'") : $default_text);
        break;
    case 135: //extra page 1
        $text = ((strlen(trim($page->messages[502339])) > 0) ? $page->messages[502339] : $default_text);
        break;
    case 136: //extra page 2
        $text = ((strlen(trim($page->messages[502341])) > 0) ? $page->messages[502341] : $default_text);
        break;
    case 137: //extra page 3
        $text = ((strlen(trim($page->messages[502340])) > 0) ? $page->messages[502340] : $default_text);
        break;
    case 138: //extra page 4
        $text = ((strlen(trim($page->messages[502342])) > 0) ? $page->messages[502342] : $default_text);
        break;
    case 139: //extra page 5
        $text = ((strlen(trim($page->messages[502343])) > 0) ? $page->messages[502343] : $default_text);
        break;
    case 140: //extra page 6
        $text = ((strlen(trim($page->messages[502344])) > 0) ? $page->messages[502344] : $default_text);
        break;
    case 141: //extra page 7
        $text = ((strlen(trim($page->messages[502345])) > 0) ? $page->messages[502345] : $default_text);
        break;
    case 142: //extra page 8
        $text = ((strlen(trim($page->messages[502346])) > 0) ? $page->messages[502346] : $default_text);
        break;
    case 143: //extra page 9
        $text = ((strlen(trim($page->messages[502347])) > 0) ? $page->messages[502347] : $default_text);
        break;
    case 144: //extra page 10
        $text = ((strlen(trim($page->messages[502348])) > 0) ? $page->messages[502348] : $default_text);
        break;
    case 145: //extra page 11
        $text = ((strlen(trim($page->messages[502349])) > 0) ? $page->messages[502349] : $default_text);
        break;
    case 146: //extra page 12
        $text = ((strlen(trim($page->messages[502350])) > 0) ? $page->messages[502350] : $default_text);
        break;
    case 147: //extra page 13
        $text = ((strlen(trim($page->messages[502351])) > 0) ? $page->messages[502351] : $default_text);
        break;
    case 148: //extra page 14
        $text = ((strlen(trim($page->messages[502352])) > 0) ? $page->messages[502352] : $default_text);
        break;
    case 149: //extra page 15
        $text = ((strlen(trim($page->messages[502353])) > 0) ? $page->messages[502353] : $default_text);
        break;
    case 150: //extra page 16
        $text = ((strlen(trim($page->messages[502354])) > 0) ? $page->messages[502354] : $default_text);
        break;
    case 151: //extra page 17
        $text = ((strlen(trim($page->messages[502355])) > 0) ? $page->messages[502355] : $default_text);
        break;
    case 152: //extra page 18
        $text = ((strlen(trim($page->messages[502356])) > 0) ? $page->messages[502356] : $default_text);
        break;
    case 153: //extra page 19
        $text = ((strlen(trim($page->messages[502357])) > 0) ? $page->messages[502357] : $default_text);
        break;
    case 154: //extra page 20
        $text = ((strlen(trim($page->messages[502358])) > 0) ? $page->messages[502358] : $default_text);
        break;

    case 180: //pay invoice success/failure
        $text = ((strlen(trim($page->messages[502359])) > 0) ? $page->messages[502359] : $default_text);
        break;
    case 183: //invoice detail
        $text = ((strlen(trim($page->messages[502360])) > 0) ? $page->messages[502360] : $default_text);
        break;

    case 184: //balance transaction list display
        $text = ((strlen(trim($page->messages[502361])) > 0) ? $page->messages[502361] : $default_text);
        break;

    case 10157: //Feedback Home
        $text = ((strlen(trim($page->messages[502362])) > 0) ? ($page->messages[502362] . " " . $view->username) : $default_text);
        break;

    case 10158: //feedback about specific client
        $user = geoUser::getUser($_REQUEST["d"]);
        $text = ((strlen(trim($page->messages[502363])) > 0) ? ($page->messages[502363] . " " . trim($user->username)) : $default_text);
        break;
        //get userid of feedback displayed
    case 10159: //open feedbase for current client
        $text = ((strlen(trim($page->messages[502364])) > 0) ? ($page->messages[502364] . " " . $view->username) : $default_text);
        break;
    case 10160: //leave feedback
        $title = geoListing::getTitle($_REQUEST["d"]);
        $text = ((strlen(trim($page->messages[502365])) > 0) ? ($page->messages[502365] . " " . $title . "(" . $_REQUEST["d"] . ")") : $default_text);
        break;
    case 10161: //feedback thank you
        $title = geoListing::getTitle($_REQUEST["d"]);
        $text = ((strlen(trim($page->messages[502366])) > 0) ? ($page->messages[502366] . " " . $title . "(" . $_REQUEST["d"] . ")") : $default_text);
        break;
    case 10162: //feedback error page
        $title = geoListing::getTitle($_REQUEST["d"]);
        $text = ((strlen(trim($page->messages[502367])) > 0) ? ($page->messages[502367] . " " . $title . "(" . $_REQUEST["d"] . ")") : $default_text);
        break;
    case 10163: //bid setup page
        $title = geoListing::getTitle($_REQUEST["b"]);
        $text = ((strlen(trim($page->messages[502368])) > 0) ? ($page->messages[502368] . " " . $title . "(" . $_REQUEST["b"] . ")") : $default_text);
        break;
    case 10164: //bid error page
        $title = geoListing::getTitle($_REQUEST["b"]);
        $text = ((strlen(trim($page->messages[502369])) > 0) ? ($page->messages[502369] . " " . $title . "(" . $_REQUEST["b"] . ")") : $default_text);
        break;
    case 10165: //bid successful page
        $title = geoListing::getTitle($_REQUEST["b"]);
        $text = ((strlen(trim($page->messages[502370])) > 0) ? ($page->messages[502370] . " " . $title . "(" . $_REQUEST["b"] . ")") : $default_text);
        break;
    case 10171: //bid history page
        $title = geoListing::getTitle($_REQUEST["b"]);
        $text = ((strlen(trim($page->messages[502371])) > 0) ? ($page->messages[502371] . " " . $title . "(" . $_REQUEST["b"] . ")") : $default_text);
        break;
    case 10175: //Users Current Bids Page
        $text = ((strlen(trim($page->messages[502372])) > 0) ? ($page->messages[502372] . " " . $username) : $default_text);
        break;
    case 10183: //sellers blacklist page
        $text = ((strlen(trim($page->messages[502373])) > 0) ? ($page->messages[502372] . " " . $username) : $default_text);
        break;
    case 10184: //sellers invited list page
        $text = ((strlen(trim($page->messages[502374])) > 0) ? ($page->messages[502374] . " " . $username) : $default_text);
        break;
    case 10201: //seller to buyer transaction page
        $text = ((strlen(trim($page->messages[502375])) > 0) ? $page->messages[502375] : $default_text);
        break;
    case 10202: //main cart page
        $text = ((strlen(trim($page->messages[502376])) > 0) ? $page->messages[502376] : $default_text);
        break;
    case 10203: //cart checkout
        $text = ((strlen(trim($page->messages[502377])) > 0) ? $page->messages[502377] : $default_text);
        break;
    case 10204: //cart success/failure page
        $text = ((strlen(trim($page->messages[502378])) > 0) ? $page->messages[502378] : $default_text);
        break;
    case 10205: //cart listing extras
        $text = ((strlen(trim($page->messages[502379])) > 0) ? $page->messages[502379] : $default_text);
        break;
    case 10209: //my account home page
        $text = ((strlen(trim($page->messages[502324])) > 0) ? ($page->messages[502324] . " " . $username) : $default_text);
        break;


    case 10210:
        //listing tags browsing
        $tpl_vars['listing_tag'] = (isset($_GET['tag'])) ? geoFilter::cleanListingTag($_GET['tag']) : '';

        break;

    default:
        //check with addons to see if they have a title for this page
        if (!strlen(trim($tpl_vars['addonText']))) {
            //default behavior, get title text to use
            $text = geoString::fromDB($page->ad_configuration_data['title_module_text']);
        }
        break;
}

$tpl_vars['text'] = $text;

//if something has specified a page number, then use it.  Primarily for category results.
$tpl_vars['page_number'] = ($page->page_result) ? $page->page_result : 1;

$view->setModuleTpl($show_module['module_replace_tag'], 'index')
    ->setModuleVar($show_module['module_replace_tag'], $tpl_vars);
