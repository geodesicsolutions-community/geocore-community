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

//return true; //short-circuit the example below, comment this line for a working example.
$t_set = 'geo_exported_pre_5.0';

if (!$this->tableExists('geodesic_templates') || file_exists(GEO_TEMPLATE_DIR . $t_set)) {
    //they don't need to export
    return true;
}
/**
 * We need to export DB to file-based templates...
 *
 */
$error = '';
if (isset($_POST['exportTemplates']) && isset($_POST['doExport']) && $_POST['doExport']) {
    //will need a lot more memory than normal
    require_once GEO_BASE_DIR . 'ini_tools.php';
    geoRaiseMemoryLimit('256M');

    if (file_exists(ADMIN_DIR . 'templatesExport.php')) {
        require_once ADMIN_DIR . 'templatesExport.php';
    } else {
        //for people using alternate admin folder, but have not copied new files yet
        require_once GEO_BASE_DIR . 'admin/templatesExport.php';
    }
    $fObj = geoFile::getInstance(geoFile::TEMPLATES, false);
    $fObj->setChmod(true);

    $tplExport = geoTemplatesExport::getInstance($this->_db);
    $result = $tplExport->exportTo($t_set);
    if ($result) {
        //automatically start using it
        if ($tplExport->useTSets(array($t_set))) {
            echo 'Your templates and attachments have been successfully exported to template set <strong>' . $t_set . '</strong>!';
            return true;
        }
    }
    $error = $tplExport->errorMsg();
}
if ($error || !isset($_POST['exportTemplates'])) {
    if ($error) {
        echo "<div class='errorMsg'>" . $error . "</div>";
    }

    ?>

    <form method="post" action="">
        <input type="hidden" name="license" value="on" />
        <input type="hidden" name="backup_agree" value="on" />
        <input type="hidden" name="licenseKey" value="<?php echo htmlspecialchars($_POST['licenseKey']); ?>" />
        <input type="hidden" name="exportTemplates" value="1" />
        <p>
            In order to keep your existing design, your templates and page
            attachments will need to be exported to a file-based <em>template set</em>
            <sup style="font-size: 8px;">1</sup>.
        </p>
        <label>
            <input type="checkbox" name="doExport" value="1" checked="checked" />Perform the design export now?
        </label>

        <br /><br />
        <input type="submit" value="Continue" />
        <p>
            <strong>Note:</strong> If you do not perform the design export now, your site will use the <strong>default templates</strong>.
            The design export will be available in the admin panel after the update is complete.
        </p>
        <p style="font-size: 10px;">
            <sup id="1" style="font-size: 8px;">1</sup> A <strong>Template
            Set</strong>  is a sub-folder under <em>geo_templates/</em> folder,
            with files in it that contain information relating to the design of the
            site.  See <a href="https://geodesicsolutions.org/wiki/admin_menu/design/template_sets/start#what_is_a_template_set" onclick="window.open(this.href);return false;">this page</a>
            for more information on what a template set is.
        </p>
    </form>
    <?php

    return false;
} else {
    return true;
}


//for example of adding new pages (or modules), see interactive.php from 3.1_to_4.0

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
