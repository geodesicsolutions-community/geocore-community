<?php

/**
 * How this file works:
 *
 *
 *
 * return true:  Process this update.
 * Return false: do not process this update, the update will be listed as "in
 *   progress".
 *
 * Anything that is echoed out will be displayed in a box on the update screen.
 *
 * This file can be used to notify the user of something, but it's primary use
 * is to allow some interactive step in the update process.  See the working
 * example at the end of this file.
 *
 *
 */

//return true;
$newPages = array(10201 => array('name' => 'Seller to Buyer Transaction Page', 'description' => 'This page displays the results of transactions between sellers and buyers, and is also used to display applicable error messages.'),
                 10202 => array('name' => 'Cart Main', 'description' => 'This is the main page for the Cart system'),
                 10203 => array('name' => 'Cart Checkout', 'description' => 'This is the page for collecting billing data and payment information'),
                 10204 => array('name' => 'Cart Success/Failure', 'description' => 'This is the page shown after collecting payment data, confirming success or failure of the gateway process'),
                 10205 => array('name' => 'Cart Listing Extras', 'description' => 'This is the page that presents choices of listing extras to the user, and shows the current subtotal'),
                 10209 => array('name' => 'User Account Information', 'description' => 'Replaces the User Management Home page, for use with the User Account Link Module')
                 );
$newPages_keys = array_keys($newPages);
$newPages_in = implode(',', $newPages_keys);
if (!isset($_POST['tpl_assign'])) {
    //haven't submitted this form yet

    //first, check to see if they're already assigned
    $sql = "select * from geodesic_pages_templates where page_id in (" . $newPages_in . ")";
    $existing = $this->_db->Execute($sql);

    //get all languages
    $sql = "select language_id, language from geodesic_pages_languages";
    $language_result = $this->_db->Execute($sql);
    $language_count = $language_result->RecordCount();

    if ($existing->RecordCount() == count($newPages) * $language_count) {
        //assignments have already been made -- move along
        return true;
    }

    $languages = array();
    while ($line = $language_result->FetchRow()) {
        $languages[$line['language_id']] = $line['language'];
    }

    //still here, so we need to assign stuff -- show the form

    //get available templates
    $sql = "select template_id, name from geodesic_templates order by name";
    $tpl_result = $this->_db->Execute($sql);
    $templates = array();
    while ($line = $tpl_result->FetchRow()) {
        $templates[$line['template_id']] = $line['name'];
    }



    //array of all possible page-language combos
    $pages = array();
    foreach ($newPages_keys as $key) {
        foreach ($languages as $l_key => $l) {
            $pages[$key][$l_key] = $l;
        }
    }

    //remove already-assigned page-language combos, so we don't have to show them
    while ($line = $existing->FetchRow()) {
        unset($pages[$line['page_id']][$line['language_id']]);
        if (count($pages[$line['page_id']]) < 1) {
            //if no more languages on this page, don't show page
            unset($pages[$line['page_id']]);
        }
    }

    //get admin's chosen default template
    $sql = "select `value` from geodesic_site_settings where setting = 'default_template' LIMIT 1";
    $default_template = $this->_db->GetOne($sql);
    if (!$default_template) {
        //setting not set -- use default setting for Basic Page Template 1
        $default_template = 25;
    }

    //now show the form
    ?>
    <form action="" method="post">
    <p>The following are new pages included in this update. Please select templates to attach to these new pages. 
    If you're unsure which template to attach, most pages will work with BASIC PAGE TEMPLATE - 1. You can always 
    change this setting later through the Pages Management section of the Admin.</p>
    <?php

    foreach ($pages as $page_key => $page_val) {
        //get page name
        $page_name = $newPages[$page_key]['name'];
        $page_description = $newPages[$page_key]['description'];

        echo '<fieldset><legend>' . $page_name . '</legend>';
        echo '<p>' . $page_description . '</p>';
        foreach ($page_val as $lang_key => $lang_val) {
            echo $lang_val . ': ';
            echo '<select name="tpl_assign[' . $page_key . '][' . $lang_key . ']">';
            echo '<option value="0">None</option>';
            foreach ($templates as $tpl_id => $tpl_name) {
                $selected = ($tpl_id == $default_template) ? ' selected="selected"' : '';
                echo '<option value="' . $tpl_id . '"' . $selected . '>' . $tpl_name . '</option>';
            }
            echo '</select><br />';
        }
        echo '</fieldset>';
    }
    ?> 
    <input type="submit" value="Save and Continue" />
    <!-- If submitting, remember to have these hidden fields
        so that the update knows they have already gotten past
        the "requirement checking" stage.
     -->
    <input type="hidden" name="license" value="on" />
    <input type="hidden" name="backup_agree" value="on" />
    <input type="hidden" name="licenseKey" value="<?php echo htmlspecialchars($_POST['licenseKey']); ?>" />
    </form>
    <?php

    //return false to stop upgrade process and show form
    return false;
} else {
    //process the form here
    $input = $_POST['tpl_assign'];

    //this is potentially open to being run by anyone, so make extra-special sure to clean/validate input

    //get available templates
    $sql = "select template_id, name from geodesic_templates order by name";
    $tpl_result = $this->_db->Execute($sql);
    $templates = array();
    while ($line = $tpl_result->FetchRow()) {
        $valid_templates[] = $line['template_id'];
    }

    //check input array for validity
    //remember: $this->criticalError causes the upgrade process to halt
    if (!is_array($input)) {
        $this->criticalError('Invalid input specified! (i01)');
    }
    foreach ($input as $input_page) {
        if (!is_array($input_page)) {
            $this->criticalError('Invalid input specified! (i02)');
        }
        foreach ($input_page as $input_tpl) {
            if (!in_array($input_tpl, $valid_templates)) {
                $this->criticalError('Invalid input specified! (i03)');
            }
        }
    }

    //input OK -- proceeed with template assignment

    //get valid module (!TAGS!) here, so we don't loop this query
    $sql = "select page_id, module_replace_tag from geodesic_pages where module = 1";
    $tags = array();
    $tag_result = $this->_db->Execute($sql);
    while ($tag = $tag_result->FetchRow()) {
        $tags[$tag['page_id']] = $tag['module_replace_tag'];
    }

    //get text from sub-modules, so we can check for modules embedded in HTML logged in/out and similar
    $sql = "select page_id, module_replace_tag, module_logged_in_html, module_logged_out_html from geodesic_pages where module_type = 4";
    $sub_result = $this->_db->Execute($sql);
    $subpages = array();
    while ($sub = $sub_result->FetchRow()) {
        $subpages[$sub['page_id']] = array( 'in' => $sub['module_logged_in_html'],
                                            'out' => $sub['module_logged_out_html']);
    }

    foreach ($input as $page_key => $input_page) {
        $parsed_templates = array();
        foreach ($input_page as $lang_key => $tpl_choice) {
            $sql = "INSERT IGNORE INTO `geodesic_pages_templates` (`page_id`, `language_id`, `template_id`) VALUES 
					('" . $page_key . "','" . $lang_key . "','" . $tpl_choice . "')";
            $insert_result = $this->_db->Execute($sql);
            if (!$insert_result) {
                return false;
            }

            if (in_array($tpl_choice, $parsed_templates)) {
                //we've already seen this template on this page (probably in another language)
                //page-module attachements are independent of language, so there's
                //no need to do them again
                continue;
            }

            //parse chosen templates for (!TAGS!) and assign to pages

            //first, get template text
            $sql = "select template_code from geodesic_templates where template_id = " . $tpl_choice;
            $tpl_row = $this->_db->GetRow($sql);
            $tpl_code = urldecode($tpl_row['template_code']);

            //recursively find and attach modules
            if (!parseForTags($tpl_code, $tags, $page_key, $subpages, $this->_db)) {
                return false;
            }

            $parsed_templates[] = $tpl_choice;
        }
    }
    return true; //continue with upgrade
}

/**
 * recursively checks for and attaches modules in a template
 *
 * initial input is decoded text of a specific page/language template
 *
 * @param String $input code to parse for (!TAGS!). urldecoding/stripslashing/fromDBing is the responsibility of the CALLER!!
 * @param Array $validTags array of (!TAGS!) to look for. format: [page_id => replace_tag]
 * @param int $page_id id of page to attach any found modules to
 * @param Array $weHaveSubText contains subtext of all modules that might need to be recursed into
 * @param DataAccess $db data accessor, for passing to attach function since DataAccess::getInstance() doesn't work here
 */
function parseForTags($input, $validTags, $page_id, $weHaveSubText, $db)
{
    //find tags

    //find everything that might be a module tag
    $pattern = "/\(!\w+!\)/";
    $matches = array();
    $modulesFound = array();
    $found = preg_match_all($pattern, $input, $matches);

    //only care about complete matches
    $matches = $matches[0];
    if ($found > 0) {
        foreach ($matches as $match) {
            $match_key = array_search($match, $validTags);
            if ($match_key !== false) {
                //attach module to this page, if it hasn't been done already
                if (!attachModuleToPage($match_key, $page_id, $db)) {
                    return false;
                }

                //remove this tag from valid tags array for this input
                //since it's already attached -- that way we don't over-recurse
                unset($validTags[$match_key]);

                //if the module we just attached has subtext, recurse!
                if (array_key_exists($match_key, $weHaveSubText)) {
                    $in = urldecode($weHaveSubText[$match_key]['in']); //logged in html
                    $out = urldecode($weHaveSubText[$match_key]['out']); //logged out html
                    parseForTags($in, $validTags, $page_id, $weHaveSubText, $db);
                    parseForTags($out, $validTags, $page_id, $weHaveSubText, $db);
                }
            }
        }
    }
    return true;
}

/**
 * Check to make sure a given module and page aren't already attached, then make the attachment
 *
 * @param int $module_id id of the module to attach
 * @param int $page_id is of the page to attach the module to
 * @param DataAccess $db data accessor, passed in from caller since DataAccess::getInstance() doesn't work here
 */
function attachModuleToPage($module_id, $page_id, $db)
{
    //make sure it's not already attached
    $sql = "select * from geodesic_pages_modules where module_id = '$module_id' AND page_id = '$page_id' LIMIT 1";
    $result = $db->Execute($sql);
    if (!$result) {
        return false;
    }
    if ($result->RecordCount() > 0) {
        //this attachment already exists -- move along
        return true;
    }
    //do the attachment
    $module_sql = "INSERT INTO geodesic_pages_modules (module_id, page_id, time) VALUES 
					('" . $module_id . "','" . $page_id . "','" . time() . "')";
    $mod_result = $db->Execute($module_sql);
    if (!$mod_result) {
        return false;
    }
    return true;
}



/**
 * ########## WORKING EXAMPLE ############
 * ######### DO NOT REMOVE!!!! ###########
 *
 * Always keep the below working example at the bottom of the interactive
 * file, to use as a template for future updates.  Just return before it
 * reaches this section of the code, or you can see this in action by temporarily
 * commenting out everything above this part of the file.
 */

if (!isset($_POST['favColor'])) {
    //We have not yet displayed the selection, so display it and let them
    //choose what their favorite color is.
    ?>
Hello!!!  This is an interactive portion of the update process.  Normally we would
ask some important question we need the answer to in order to do the update, but
this is just an example, so we're going to ask what is your favorite color.
<br /><br />
<form action="" method="post">
    <!-- If submitting, remember to have these hidden fields
        so that the update knows they have already gotten past
        the "requirement checking" stage.
     -->
    <input type="hidden" name="license" value="on" />
    <input type="hidden" name="backup_agree" value="on" />
    <input type="hidden" name="licenseKey" value="<?php echo htmlspecialchars($_POST['licenseKey']); ?>" />
    <label>
        What is your favorite color in the rainbow? 
        <select name="favColor">
            <option>Red</option>
            <option>Orange</option>
            <option>Yellow</option>
            <option>Green</option>
            <option>Blue</option>
            <option>Indigo</option>
            <option>Violet</option>
        </select>
    </label>
    <input type="submit" value="Continue" />
</form>
    <?php
    //Now we return false, to tell the update script to not process
    //the update yet.
    return false;
} else {
    /**
     * VERY IMPORTANT:  Check inputs!  Until the admin deletes the update
     * directory, this update process is publically accessible, so do NOT
     * trust any inputs from the user!
     */
    $allowed_colors = array (
        'Red','Orange','Yellow','Green','Blue','Indigo','Violet'
    );
    if (!in_array($_POST['favColor'], $allowed_colors)) {
        //Show critical error, which does not allow to proceed.
        $this->criticalError('Invalid input specified!');
    }

    //They have selected their favorite color.  Display a message to them
    // (this message is optional of course)
    ?>
Thank you for selecting your favorite color, <?php echo $_POST['favColor'];?>, 
that is my favorite color too!!  Proceeding with the update.
    <?php

    /**
     * Note: You would do any queries based on the POST data provided during
     * the normal conditional_sql.php or even arrays.php files.
     *
     * If you do, remember to never trust user input since the update is public
     * accessible.  NEVER use un-cleaned input as part of an SQL query, but I
     * don't have to tell you that :)
     *
     */

    //Return true to indicate to proceed with this update.
    return true;
}