<?php

//addons/bulk_uploader/admin.php

//setting this to 1 causes the image processor to use cURL to make a local copy of
//the images before attempting to process them
define('FORCE_CURL', 0);

//change this to use a different filename for revolving source files
define('REVOLVING_FILENAME', 'data.csv');

require_once("session_handler.php");
require_once('tokenizer.php');
require_once(ADMIN_DIR . 'admin_site_class.php');
class addon_bulk_uploader_admin extends Admin_site
{
    var $table_prefix = 'geodesic_addon_bulk_uploader';
    var $addon_url;
    var $skip_first_listing;
    var $image_types;
    var $ad_configuration_data;
    var $db;
    var $session;
    var $uploads;
    var $top_dropdown = array();
    var $bottom_dropdown = array();
    var $self_path;
    var $addon_dir;
    var $userid;
    var $uploads_dir_name = "uploads";
    var $_category_tree_array;
    var $critical_error = false;

    var $url;

    function init_pages()
    {
        //add main config
        $this->self_path = '?page=addon_bulk_uploader_main_config';
        menu_page::addonAddPage('addon_bulk_uploader_main_config', '', 'Bulk Upload', 'bulk_uploader', 'fa-truck'); //image path hardcoded since this extends Admin_site and not the info file
        menu_page::addonAddPage('addon_bulk_uploader_manage_uploads', '', 'Manage Uploads', 'bulk_uploader', 'fa-truck'); //image path hardcoded since this extends Admin_site and not the info file
        menu_page::addonAddPage('addon_bulk_uploader_tokenize_images', '', 'Pre-Load Remote Images', 'bulk_uploader', 'fa-truck'); //image path hardcoded since this extends Admin_site and not the info file
    }

    public function display_addon_bulk_uploader_manage_uploads()
    {
        $db = DataAccess::getInstance();


        if ($_REQUEST['deleteLog'] && is_numeric($_REQUEST['deleteLog'])) {
            $del = $this->deleteLog($_REQUEST['deleteLog']);
            if (!$del) {
                geoAdmin::m('Error deleting listings.', geoAdmin::ERROR);
            } else {
                geoAdmin::m('Listings deleted.', geoAdmin::SUCCESS);
            }
        }

        if ($_REQUEST['cancelMultipart'] && is_numeric($_REQUEST['cancelMultipart'])) {
            $del = $this->cancelMultipart($_REQUEST['cancelMultipart']);
            if (!$del) {
                geoAdmin::m('Error cancelling multipart upload.', geoAdmin::ERROR);
            } else {
                geoAdmin::m('Cancelled multipart upload, but did not remove already-uploaded listings.', geoAdmin::SUCCESS);
            }
        }

        if ($_REQUEST['clearSession'] == 1) {
            $sql = "TRUNCATE TABLE `geodesic_addon_bulk_uploader_session`";
            if ($db->Execute($sql)) {
                geoAdmin::m('Succesfully cleared session data.', geoAdmin::SUCCESS);
            } else {
                geoAdmin::m('Error clearing session data.', geoAdmin::ERROR);
            }
        }

        //show revolving uploads that exist in the system, to allow deletion

        $this->cleanLog(); //remove log entries where their listings have expired naturally

        $html = geoAdmin::m();
        $html .= "<form action='' method='post' class='form-horizontal form-label-left'>";

        $sql = "SELECT * FROM " . $this->table_prefix . "_log ORDER BY `insert_time` DESC";
        $result = $db->Execute($sql);
        $countQuery = $db->Prepare("SELECT count(`listing_id`) FROM `geodesic_addon_bulk_uploader_listings` WHERE `upload_id` = ?");

            $tooltip = geoHTML::showTooltip('Custom Label', 'This label is only visible here in the admin. You can set it to whatever you like, to help you keep track of bulk uploads.');
            $html .= "<fieldset><legend>Manage Previous Uploads</legend>";

        if ($result) {
                $html .= "
				<div class='table-responsive'>
				<table class='table table-hover table-striped table-bordered'>
					<thead>
						<tr class='col_hdr_top'>
							<th class='col_hdr'>Date</th>
							<th class='col_hdr'>Number of Listings</th>
							<th class='col_hdr'>Custom Label $tooltip</th>
							<th class='col_hdr'>Undo</th>
						</tr>
					</thead>
					<tbody id='orders_parent'>";
            $i = 0;
            foreach ($result as $element) {
                $date = date("F j, Y g:i:s", $element['insert_time']);
                $row = ($row == 'odd_row') ?  'even_row' : 'odd_row';
                $numListings = $db->GetOne($countQuery, array($element['log_id']));
                if ($numListings > 0) {
                    $html .= "
					<tr class='$row'>
						<td style='text-align: center;'>$date</td>
						<td style='text-align: center;'>$numListings</td>
						<td style='text-align: center;'>
							<input type='text' size='35' name='labels[" . $element['log_id'] . "]' value='" . $element['user_label'] . "' />
						</td>
						<td style='text-align: center;'>
							<a href='index.php?page=addon_bulk_uploader_manage_uploads&mc=addon_cat_bulk_upload&deleteLog=" . $element['log_id'] . "&auto_save=1' class='mini_cancel lightUpLink'>Delete Listings</a>
						</td>
					</tr>";
                }
            }
            $html .= "</tbody></table></div>";
        } else {
            $html .= "<div class='row_color2 center'>You haven't bulk uploaded anything yet!</div>";
        }
        $html .= '</fieldset>';

        //get any active (incomplete) multipart upload sets
        $result = $db->Execute("SELECT * FROM `geodesic_addon_bulk_uploader_multipart`");
        if ($result && $result->RecordCount() > 0) {
            $html .= "<fieldset><legend>Pending Multi-part Uploads</legend><div>";
            foreach ($result as $multi) {
                $html .= '<div class="row_color' . ($r++ % 2 ? 1 : 2) . '">';

                $html .= "(#{$multi['id']}) Upload at most <strong>{$multi['count']}</strong> listing(s) every <strong>" . ($multi['gap'] / 3600) . " hour(s)</strong> [Already Completed: {$multi['completed']} listing(s)]";
                $html .= " <a href='index.php?page=addon_bulk_uploader_manage_uploads&mc=addon_cat_bulk_upload&cancelMultipart={$multi['id']}&auto_save=1' class='mini_cancel lightUpLink'>Cancel</a>";
                $html .= "</div>";
            }
            $html .= "</div></fieldset>";
        }


        $html .= "<fieldset><legend>Maintenance</legend>";
        $html .= "
		<div class='form-group'>
		<label class='control-label col-md-5 col-sm-5 col-xs-12'>Clear Session Data: " . geoHTML::showTooltip('Clear Session Data', 'This button will clear saved session data in the Bulk Uploader. Normally, you shouldn\'t need this, but you can use it if the Uploader becomes "stuck" for any reason, or when instructed to by Support.') . " </label>
		  <div class='col-md-6 col-sm-6 col-xs-12'>
		  <a href='index.php?page=addon_bulk_uploader_manage_uploads&mc=addon_cat_bulk_upload&clearSession=1&auto_save=1' class='mini_cancel lightUpLink'>Clear Session Data</a>
		  </div>
        </div>
		";
        $html .= "</fieldset>";

        $r = 0;
        $html .= '<fieldset><legend>Manage Revolving Inventory</legend><div>';

        $input = '<input type="text" name="revolving_period" size="2" value="' . $db->get_site_setting('bulk_revolve_period') . '" >';
        $html .= '
			<div class="form-group">
			<label class="control-label col-md-5 col-sm-5 col-xs-12">Update Frequency: </label>
			  <div class="col-md-6 col-sm-6 col-xs-12">
			  <span class="vertical-form-fix">Check each file for updates every ' . $input . ' days</span>
			  </div>
			</div>
		';

        $reg = geoAddon::getRegistry('bulk_uploader');

        $html .= '
		<div class="form-group">
			<label class="control-label col-md-5 col-sm-5 col-xs-12">Process a single file at once: ' . geoHTML::showTooltip(
            'Process a single file at once',
            'With this option selected, the cron update will only execute a single Revolving Inventory update each run, regardless of how many are due. This can help
					ease the burden on the server when dealing with several update files that would otherwise update at the same time.'
        ) . '</label>
			<div class="col-md-6 col-sm-6 col-xs-12">
				<input type="checkbox" name="revolving_single_run_only" ' . ($reg->revolving_single_run_only == 1 ? 'checked="checked"' : '') . ' value="1" />
			</div>
		</div>
		';

        $sql = "SELECT * FROM `geodesic_addon_bulk_uploader_revolving` ORDER BY `label` ASC";
        $result = $db->Execute($sql);

        if ($result && $result->RecordCount() > 0) {
            while ($upload = $result->FetchRow()) {
                $html .= '<div class="row_color' . ($r++ % 2 ? 1 : 2) . '">';
                $html .= '<div class="leftColumn">' . $upload['label'] . '<br />Next run: ' . date('m/d/y H:i', $upload['next_run']) . '</div>';
                $html .= '<div class="rightColumn">
							<input type="checkbox" name="delete[' . $upload['id'] . ']" value="1"><strong>Remove</strong> from Revolving Inventory<br />
							<input type="checkbox" name="reset[' . $upload['id'] . ']" value="1">Reset "next run" to current time
							</div>';
                $html .= '<div class="clearColumn"></div></div>';
            }
        }
        $html .= '</div></fieldset>';

        $html .= "<div style='width: 115px; margin: 2px auto;'><input type='submit' value='Save' name='auto_save' /></div></form>";

        $this->body = $html;
        $this->display_page();
    }

    public function update_addon_bulk_uploader_manage_uploads()
    {
        $db = DataAccess::getInstance();

        if (isset($_POST['labels'])) {
            //changing one or more bulk upload labels
            foreach ($_POST['labels'] as $logId => $userLabel) {
                $sql = "UPDATE `geodesic_addon_bulk_uploader_log` SET `user_label` = ? WHERE `log_id` = ?";
                $result = $db->Execute($sql, array($userLabel, $logId));
                if (!$result) {
                    geoAdmin::m('Database error while setting user label.', geoAdmin::ERROR);
                    return false;
                }
            }
        }

        if ($_POST['revolving_period'] && is_numeric($_POST['revolving_period'])) {
            $db->set_site_setting('bulk_revolve_period', $_POST['revolving_period']);
        }

        $reg = geoAddon::getRegistry('bulk_uploader');
        $reg->revolving_single_run_only = $_POST['revolving_single_run_only'] ? 1 : 0;
        $reg->save();

        $reset = $_POST['reset'];
        $delete = $_POST['delete'];

        foreach ($reset as $id => $confirm) {
            if ($confirm == 1) {
                $sql = "UPDATE `geodesic_addon_bulk_uploader_revolving` SET `next_run` = ? WHERE `id` = ?";
                $result = $db->Execute($sql, array(geoUtil::time(), $id));
                if (!$result) {
                    return false;
                }
            }
        }

        $count = 0;
        require_once(ADDONS_DIR . 'bulk_uploader/registry.php');
        $reg = new geoBulkUploaderRegistry();
        foreach ($delete as $id => $confirm) {
            if ($confirm == 1) {
                //get label before removing
                $sql = "SELECT `label` FROM `geodesic_addon_bulk_uploader_revolving` WHERE `id` = ?";
                $label = $db->GetOne($sql, array($id));
                //remove from database
                $sql = "DELETE FROM `geodesic_addon_bulk_uploader_revolving` WHERE `id` = ?";
                $result = $db->Execute($sql, array($id));
                if (!$result) {
                    return false;
                }
                //also remove from map table
                if (!$db->Execute("DELETE FROM geodesic_addon_bulk_uploader_revolving_map WHERE `revolving_id` = ?", array($label))) {
                    return false;
                }

                //remove file
                $uploadPath = ADDON_DIR . 'bulk_uploader' . DIRECTORY_SEPARATOR . $this->uploads_dir_name . DIRECTORY_SEPARATOR . 'revolving' . DIRECTORY_SEPARATOR . $label;
                if (is_file($uploadPath . DIRECTORY_SEPARATOR . REVOLVING_FILENAME)) {
                    unlink($uploadPath . DIRECTORY_SEPARATOR . REVOLVING_FILENAME);
                    rmdir($uploadPath);
                } elseif (is_file(ADDON_DIR . 'bulk_uploader' . DIRECTORY_SEPARATOR . $this->uploads_dir_name . DIRECTORY_SEPARATOR . '_R_' . $label . '.csv')) {
                    //old-style filename (pre-2.7.0)
                    unlink(ADDON_DIR . 'bulk_uploader' . DIRECTORY_SEPARATOR . $this->uploads_dir_name . DIRECTORY_SEPARATOR . '_R_' . $label . '.csv');
                }

                //get settings to find the attached bulk upload log: upload_log_id
                $settings = $reg->$label;
                if ($settings['upload_log_id']) {
                    //also remove the associated log and its listings
                    $this->deleteLog($settings['upload_log_id']);
                }
                //remove registry entry
                $reg->set($label, false);
                $count++;
            }
        }
        if ($count > 0) {
            geoAdmin::m('Removed ' . $count . ' Revolving Inventory session(s)');
        }
        return true;
    }


    public function cancelMultipart($id)
    {
        if (!$id) {
            return false;
        }
        return (bool)DataAccess::getInstance()->Execute("DELETE FROM `geodesic_addon_bulk_uploader_multipart` WHERE `id` = ?", array($id));
    }

    public function display_addon_bulk_uploader_tokenize_images()
    {
        $tpl_vars['adminMsgs'] = geoAdmin::m();
        geoView::getInstance()->setBodyVar($tpl_vars)
            ->setBodyTpl('admin/preload.tpl', 'bulk_uploader');
    }

    public function update_addon_bulk_uploader_tokenize_images()
    {
        $upload = $_FILES['csvfile']['tmp_name'];
        do {
            $newFileName = $this->uploads . md5(rand(0, 99999));
        } while (file_exists($newFileName));

        if (move_uploaded_file($upload, $newFileName)) {
            $h = fopen($newFileName, 'r');
            $skipped = $created = $notReadable = $rowsDone = 0;
            $delimiter = ($_POST['delimiter']) ? $_POST['delimiter'] : ',';
            $encapsulation = ($_POST['encapsulation']) ? $_POST['encapsulation'] : '"';

            //this is a string of characters that can potentially terminate a URL string, for use in strcspn() below
            $terminators = "\"',\r\n\t " . $delimeter . $encapsulation;

            $contents = file_get_contents($newFileName);
            $urls = array();
            $offset = 0;
            while (($offset = stripos($contents, 'http', $offset)) !== false) { //remember that 0 is a valid posistion here, so check against !== false
                //found the start of something that looks like a URL at posistion $offset

                //this finds the length of the URL string by looking for something that could terminate it
                $end = strcspn($contents, $terminators, $offset);

                $urls[] = substr($contents, $offset, $end);

                //add $end to $offset so that we start the next iteration looking past what we've already seen
                $offset += $end;
            }

            foreach ($urls as $url) {
                $r = geoBulkUploaderImageTokenizer::getToken($url);
            }
            $stats = geoBulkUploaderImageTokenizer::getStats();

            geoAdmin::m('Processed ' . count($urls) . ' URLs', geoAdmin::SUCCESS);
            if ($stats['extant']) {
                geoAdmin::m("Found {$stats['extant']} images with pre-existing tokens", geoAdmin::NOTICE);
            }
            if ($stats['recreate']) {
                geoAdmin::m("Remade {$stats['recreate']} tokens with missing local images", geoAdmin::NOTICE);
            }
            if ($stats['new']) {
                geoAdmin::m("Created {$stats['new']} new tokens", geoAdmin::NOTICE);
            }
            if ($stats['not_image']) {
                geoAdmin::m("Detected {$stats['not_image']} URLs that were not valid image references", geoAdmin::NOTICE);
            }
            if ($stats['write_fail']) {
                geoAdmin::m("Failed to write {$stats['write_fail']} images to local disk", geoAdmin::ERROR);
            }

            return true;
        }
        geoAdmin::m('Error reading uploaded file. Is directory ' . $this->uploads . ' write-enabled (CHMOD 777)?', geoAdmin::ERROR);
        return false;
    }

    function display_addon_bulk_uploader_main_config()
    {
        $this->session = singleton::getInstance('geoBulkUploaderSessionHandler');
        $this->StartUpTasks();

        if (!is_writable(ADDON_DIR . 'bulk_uploader/tokens/')) {
            geoAdmin::m('Directory permissions error: ' . ADDON_DIR . 'bulk_uploader/tokens/ is not writable.', geoAdmin::NOTICE);
        }
        if (!is_writable(ADDON_DIR . 'bulk_uploader/uploads/')) {
            geoAdmin::m('Directory permissions error: ' . ADDON_DIR . 'bulk_uploader/uploads/ is not writable.', geoAdmin::NOTICE);
        }

        if (isset($_POST["data"])) {
            $this->session->freeRowById('config');

            //if "multiple categories" option is selected, remove any single category that might be selected
            if ($_POST['data']['multiple_categories'] == 1) {
                $_POST['data']['category'] = 0;
            }

            foreach ($_POST["data"] as $key => $value) {
                $this->session->config($key, $value);
            }
        }

        if (isset($_FILES['csvfile'])) {
            $allowed_size = ini_get('post_max_size');
            $file = $_FILES["csvfile"];
            $file = $this->setCSVData($file["tmp_name"]);

            if (!$file) {
                geoAdmin::m("Cannot write to folder: " . $this->uploads . "<br /> Most commonly, this error means you did not include a CSV file in the last step, or you selected the wrong \"compression method\" for the uploaded file. It is also possible that you need to CHMOD 777 the directory shown above.", geoAdmin::ERROR);
                $this->critical_error = true;
            }

            if (isset($_POST['ri']) && $_POST['ri']['use'] == 1) {
                //using Revolving Inventory -- initialize the stuff to hold things for future use
                $this->initializeRevolvingInventory($file);
            } else {
                //grab just the name of the file for use as the label later
                $shortName = substr($_FILES["csvfile"]['name'], 0, strrpos($_FILES["csvfile"]['name'], '.'));
                $this->session->config('fileNameShort', $shortName);
            }

            $this->session->config("fileName", $file);



            $this->session->free("bulkColumn");
        }

        $this->addHeader($this->addDynamicDropDownValues());
        $this->db = DataAccess::getInstance();
        $body = $this->displayBulkUploader();
        $this->body = geoAdmin::m() . $body;

        $this->display_page();

        $this->get_configuration_data($this->db);
        $this->get_ad_configuration($this->db);
        $this->cleanLog();
    }

    /**
     * Try to remove the server time/memory limits while uploading
     */
    public function increaseServerLimits()
    {
        if (defined('GEO_DID_INCREASE_SERVER_LIMITS') && GEO_DID_INCREASE_SERVER_LIMITS) {
            //already did this
            return;
        }
        if (stripos('set_time_limit', ini_get('disable_functions')) !== false) {
            //changing the time limit is disallowed on this server
            geoAdmin::m('Notice: server permissions disallow removing max_execution_time on the fly', geoAdmin::NOTICE);
        } else {
            //remove time limit
            set_time_limit(0);
        }
        if (ini_set('memory_limit', -1) === false) {
            geoAdmin::m('Notice: server permissions disallow removing memory_limit on the fly', geoAdmin::NOTICE);
        }
        define('GEO_DID_INCREASE_SERVER_LIMITS', 1);
    }

    /**
     * step 4...
     * Inserts the data from the csv file
     *
     * Parameters $forceSettings, $forceData are used by the revolving inventory cron
     * to directly insert the settings and data to be used into this function
     * instead of pulling that info from the Session data.
     *
     * They could, ostensibly, be used elsewhere for a similar purpose.
     * For proper formatting, see the $save variable wherever it appears in this file, and/or this addon's renew_revolving_inventory cron file
     *
     */
    public function insertCSV($forceSettings = null, $forceHandle = null, $existingUids = null)
    {
        $this->increaseServerLimits();
        if ($forceSettings) {
            $this->session = singleton::getInstance('geoBulkUploaderSessionHandler');

            //since this is running from a cron, the class autoloader doesn't work
            //on files in admin folder...
            require_once(ADMIN_DIR . 'php5_classes/Admin.class.php');
            require_once(ADMIN_DIR . 'php5_classes/Notifications.class.php');

            $this->StartUpTasks(true);
            $settings = $forceSettings;
            $isCron = true;
            $cron = geoCron::getInstance();
            $logAppend = ' - bulk_admin::insertCSV()';
        } else {
            $settings['config'] = $this->session->configArray();
            $settings['columns'] = $this->session->getArray("bulkColumn");
            $settings['defaultColumns'] = $this->session->getArray("bulkDefaultColumn");
            $settings['defaultData'] = $this->session->getArray("bulkDefaultData");
            $settings['customTitle'] = $this->session->getArray("bulkCustomTitle");

            $settings['duration']['method'] = $this->session->config('duration_method');
            $settings['duration']['start'] = $this->session->get("startTime");
            $settings['duration']['end'] = $this->session->get("endTime");

            $settings['checkUserLimits'] = $this->session->get('checkUserLimits');
            $settings['useDefaultUserData'] = $this->session->get('useDefaultUserData');

            $settings['upgrades']['bolding'] = $this->session->get("bolding");
            $settings['upgrades']['better_placement'] = $this->session->get("better_placement");
            $settings['upgrades']['featured_ad'] = $this->session->get("featured_ad");
            $settings['upgrades']['attention_getter'] = $this->session->get("attention_getter");
            $settings['upgrades']['attention_getter_url'] = $this->session->get("attention_getter_url");

            $isCron = false;
        }

        //note: $multipart_completed is the number of listings of a multipart upload ALREADY FINISHED before this time began
        //      $multipart_count is the number of listings to process in any given multipart upload session
        if ($_POST['multipart']['count'] > 0 && $_POST['multipart']['gap'] > 0) {
            //configure a new mutipart upload
            $multipart_count = intval($_POST['multipart']['count']);

            $settings['seller'] = trim($_POST['seller']); //need to save the default seller explicitly when doing things this way

            $sql = "INSERT INTO `geodesic_addon_bulk_uploader_multipart` (count, gap, settings) VALUES (?,?,?)";
            $this->db->Execute($sql, array($multipart_count, intval($_POST['multipart']['gap'] * 3600), serialize($settings)));
            $multipartId = $this->db->Insert_Id();
            $multipart_completed = 0;
        } elseif ($settings['multipart_id'] > 0) {
            //continuing a multipart upload
            $multipartId = $settings['multipart_id'];
            $sql = "SELECT * FROM `geodesic_addon_bulk_uploader_multipart` WHERE `id` = ?";
            $multipart_result = $this->db->GetRow($sql, array($multipartId));
            $multipart_count = $multipart_result['count'];
            $multipart_completed = $multipart_result['completed'];
            $settings = unserialize($multipart_result['settings']);
            if ($isCron) {
                $cron->log('settings are: <pre>' . print_r($settings, 1) . '</pre>', __LINE__);
                if (!$settings) {
                    //something is very wrong
                    $cron->log('failed to get settings. cannot proceed.', __LINE__);
                    return;
                }
            }
        }

        //ids of inserted listings
        $insertedIDs = array();
        $failedRows = array();

        //addon object, for interfacing with other addons
        $geoAddon = geoAddon::getInstance();

        //set up the default seller ID
        $seller = (isset($settings['seller'])) ? $settings['seller'] : trim($_POST['seller']);
        $defaultSeller = $this->userid = $this->lookupUserId($seller);

        //get configuration
        $columnFields = $settings['columns'];
        $fieldLegend = array_flip($columnFields);
        $columnDefaultColumns = $settings['defaultColumns'];
        $columnDefaultData = $settings['defaultData'];
        $customTitle = $settings['customTitle'];

        //counter variables, to count things!
        $insertCount = $updateCount = $skipCount = 0;

        //set default values
        $listing_defaults = array();
        $listing_defaults["live"] = 1;

        $itemType = $settings['config']['type'];
        $listing_defaults["item_type"] = ($itemType == "classified") ? 1 : 2;

        $listing_defaults["date"] = $settings['duration']['start'];
        $listing_defaults["ends"] = $settings['duration']['end'];
        $listing_defaults["bolding"] = $settings['upgrades']['bolding'];
        $listing_defaults["better_placement"] = $settings['upgrades']['better_placement'];
        $listing_defaults["featured_ad"] = $settings['upgrades']['featured_ad'];
        $listing_defaults["attention_getter"] = $settings['upgrades']['attention_getter'];
        $listing_defaults["attention_getter_url"] = $settings['upgrades']['attention_getter_url'];
        $listing_defaults["auction_type"] = "1";
        $listing_defaults['minimum_bid'] = '0.01';
        $listing_defaults['quantity'] = '1';
        $listing_defaults['price_applies'] = 'lot';
        $listing_defaults['conversion_rate'] = 1;
        $listing_defaults['language_id'] = 1;

        $costOptions = array();

        if ($isCron && ($settings['duration']['method'] == 1)) {
            //these are part of the revolving inventory system and have a set duration that must renew with each refresh
            $listing_defaults["start_time"] = $listing_defaults['date'] = geoUtil::time();
            if ($listing_defaults['ends'] > 0) {
                $duration = $settings['duration']['end'] - $settings['duration']['start'];
                $listing_defaults["end_time"] = $listing_defaults['ends'] = $listing_defaults['date'] + $duration;
            } else {
                //unlimited duration listings; keep them that way.
                $listing_defaults["end_time"] = $listing_defaults['ends'] = 0;
            }
        } elseif ($settings['duration']['method'] == 2) {
            //these listings start and end at a specific date
            $listing_defaults["start_time"] = $listing_defaults["date"];
            $listing_defaults["end_time"] = $listing_defaults["ends"];
        }


        if (!$settings['config']['multiple_categories']) {
            $listing_defaults["category"] = $settings['config']['category'];
        } else {
            $multipleCategories = true;
            $categories = array();
        }

        //set custom defaults from step 3
        foreach ($columnDefaultColumns as $key => $fieldName) {
            $listing_defaults[$fieldName] = $columnDefaultData[$key];
        }

        //sort out category specific and image columns
        $categorySpecific = array();
        //get column names from the classifieds table
        $listing_field = array();
        $listing_fields = $this->listFieldNames($this->classifieds_table, array('id'));

        //******* NOTE FOR WHEN MAKING CHANGES PAST THIS POINT ********
        // $listing_field is the master list of fields that will get uploaded
        // Whatever you do should ultimately make changes to that variable

        $base = '';
        foreach ($listing_fields as $key => $value) {
            if (isset($listing_field[$key])) {
                //already did this field, somehow (TODO: is this even needed?)
                continue;
            }
            if (isset($listing_defaults[$key])) {
                //there is a default value set for this field -- set it, and let it be overwritten later if applicable
                $listing_field[$key] = $listing_defaults[$key];
            } else {
                //no default for this field, but go ahead and add the field into the array
                $listing_field[$key] = null;
            }

            if ($key == 'seller') {
                //make sure every listing has a seller by setting the default now.
                //later, we'll assign specific sellers from the actual data, where applicable.
                $listing_field[$key] = $defaultSeller;
            }

            if ($key == 'delayed_start') {
                if ($itemType == 'auction') {
                    if ($settings['duration']['method'] == 2) {
                        $listing_field[$key] = 0;
                        $listing_field['start_time'] = $settings['duration']['start'];
                        $listing_field['end_time'] = $settings['duration']['end'];
                    } else {
                        $listing_field[$key] = $this->getDelayedSetting($this->userid);
                    }
                }
            }

            if ($key == 'start_date' && $settings['duration']['method'] == 2) {
                $listing_field[$key] = $settings['duration']['start'];
            }

            if ($key == 'starting_bid' && !$listing_field[$key]) {
                $listing_field['starting_bid'] = ($listing_field['minimum_bid']) ? $listing_field['minimum_bid'] : $listing_defaults['minimum_bid'];
            }
        }

        //get data sheet
        if ($forceHandle) {
            $handle =& $forceHandle;
        } else {
            $file = $settings['config']['fileName'];
            $handle = fopen($file, "r");
        }

        $count =  $i = 0;

        if ($revolvingLabel = $settings['config']['revolving_label']) {
            $uids_mapped = array();
            $uniqueDuplicates = 0;
            require_once(ADDONS_DIR . 'bulk_uploader/registry.php');
            $reg = new geoBulkUploaderRegistry();
            $revolving = $reg->$revolvingLabel;
        }

        $action = 'insert'; //revolving uploader will make use of different actions, such as updating existing listings

        $delimiter = ($settings['config']['delimiter']) ? $settings['config']['delimiter'] : ',';
        $encapsulation = ($settings['config']['encapsulation']) ? $settings['config']['encapsulation'] : '"';

        if ($settings['config']['skipfirstrow']) {
            //skip first row
            $throwAway = fgetcsv($handle, 0, $delimiter, $encapsulation);
        }

        if ($multipart_completed > 0) {
            //skip rows of this multipart upload that have already been done
            for ($c = 0; $c < $multipart_completed; $c++) {
                $throwAway = fgetcsv($handle, 0, $delimiter, $encapsulation);
            }
        }

        //set up log for this upload

        if ($revolving && $revolving['upload_log_id']) {
            //this is a revolving upload with a saved log id
            $logId = $revolving['upload_log_id'];
            //make sure the log exists
            $check = $this->db->Execute('SELECT `log_id` FROM `geodesic_addon_bulk_uploader_log` WHERE `log_id` = ?', array($logId));
            if ($check && $check->RecordCount() == 1) {
                //update log time
                $this->db->Execute("UPDATE `geodesic_addon_bulk_uploader_log` SET `insert_time` = ? WHERE log_id = ?", array(geoUtil::time(), $logId));
            } else {
                //something's wrong with the log. better make a new one -- clear $logId and let the bit below do its thing
                $logId = false;
            }
        }
        if (!$logId) {
            //no extant log -- make a new one!
            $sql = "INSERT INTO `geodesic_addon_bulk_uploader_log` (`insert_time`, `user_label`) VALUES (?, ?)";
            $uploadLabel = $revolvingLabel ? $revolvingLabel : $this->session->config('fileNameShort');
            $this->db->Execute($sql, array($listing_defaults["date"], $uploadLabel));
            $logId = $this->db->Insert_ID();
            if ($revolving) {
                //save log id for use in future updates
                $revolving['upload_log_id'] = $logId;
                $usingNewLog = true; //set this so that even updated listings can be put on the new log
                $purgeLogQuery = $this->db->Prepare("DELETE FROM `geodesic_addon_bulk_uploader_listings` WHERE `listing_id` = ?");
            }
        }
        $logQuery = $this->db->Prepare("INSERT INTO `geodesic_addon_bulk_uploader_listings` (`upload_id`, `listing_id`) VALUES ('" . $logId . "', ?)");

        //prepare db query to find category IDs from category names
        $getCategoryId = $this->db->Prepare("SELECT `category_id` FROM `geodesic_categories_languages` WHERE `category_name` = ?");

        $listingsUploaded = 0;

        while (($values = fgetcsv($handle, 0, $delimiter, $encapsulation)) !== false) {
            /*
             * IMPORTANT NOTE TO DEVELOPERS: read this comment block, and things below will make a whole lot more sense (I hope...)
             *
             * This explains the variables used in this loop, which aren't particularly semantic in their naming
             * (yay for legacy code, eh?) ;)
             *
             * $values holds the specific values entered in each row (i.e. the result of fgetcsv)
             *
             * $columnFields is the list of fields actually in use (i.e. selected in step 2), keyed by column number
             *
             * $fieldLegend is an array_flip()'d version of $columnFields. use it to get specific values out of a row.
             * For example: $thisSeller = $values[$fieldLegend['seller']];
             */

            if (!$this->_isValidRow($values)) {
                if ($isCron) {
                    $cron->log('Values not good, not able to process row! Row is: ' . print_r($values, 1), __line__ . $logAppend);
                }
                continue;
            }

            if ($listingsUploaded >= $multipart_count && $multipartId) {
                //have already completed all uploads for this multipart upload
                //break out of loop
                break;
            }

            $uniqueValue = null;

            //base URL to use
            $base = '';
            if ($fieldLegend['image_base_url'] && isset($values[$fieldLegend['image_base_url']])) {
                $base = $values[$fieldLegend['image_base_url']];
            }

            if (isset($fieldLegend['seller'])) {
                //if this listing has a seller set, use it. otherwise, use the default.
                $listingSeller = $values[$fieldLegend['seller']] ? $values[$fieldLegend['seller']] : $defaultSeller;
            } else {
                //not using the seller column -- do checks below with the default user
                $listingSeller = $defaultSeller;
            }

            //set price plan to use for this user
            $sql = "select * from geodesic_user_groups_price_plans where id = " . $listingSeller;
            $priceplan_row = $this->db->GetRow($sql);
            if (!$priceplan_row) {
                //user doesn't have a priceplan for some reason -- use the default
                $sql = "select * from geodesic_groups where default_group = 1";
                $priceplan_row = $this->db->GetRow($sql);
            }
            if ($listing_defaults["item_type"] == 1) {
                $pricePlanToUse = $priceplan_row['price_plan_id'];
            } elseif ($listing_defaults["item_type"] == 2) {
                $pricePlanToUse = $priceplan_row['auction_price_plan_id'];
            }
            $listing_field['price_plan_id'] = $pricePlanToUse;

            $failedUserCheck = false;
            if ($settings['checkUserLimits'] && !$this->_checkUserLimits($listingSeller, $pricePlanToUse)) {
                //check for ability of this user to place a listing returned false -- do not upload this listing
                if ($revolving) {
                    //even though user can't place new listings,
                    //this might be an update of an existing listing,
                    //but we won't know that till later
                    //for now, make a note of it, and handle it a little lower down
                    if ($isCron) {
                        $cron->log('failed user check for user ' . $listingSeller . '. Possibly skipping this listing later', __line__ . $logAppend);
                        $reg->_failedUserCheck = 1;
                    }
                    $failedUserCheck = true;
                } else {
                    //not a revolving session, so definitely trying to place a new listing, but can't.
                    //skip this listing now instead of later, to save on a bit of process time
                    if ($isCron) {
                        $cron->log('checkUserLimits returned false for user ' . $listingSeller . '. Skipping this listing', __line__ . $logAppend);
                    }
                    continue;
                }
            }

            if ($settings['useDefaultUserData']) {
                //we may populate some listing defaults with data from this user -- get that data now.
                $defaultUserData = $this->_getDefaultUserData($listingSeller);
                foreach ($defaultUserData as $fieldName => $data) {
                    if (!isset($fieldLegend[$fieldName]) || !$values[$fieldLegend[$fieldName]]) {
                        //this field either isn't in use, or is blank for this listing
                        $listing_field[$fieldName] = $data;
                    }
                }
            }

            //set base image url from step1 setting here. overwrite it below with setting from step2, if applicable
            $imageBase = $settings['config']['base_image_path'] ? $settings['config']['base_image_path'] : '';

            //set some defaults that get reset with each listing
            $images = array();
            $imageOrder = 1;
            $imageCaptions = array();
            $checkboxList = ($listing_defaults['checkbox_list']) ? explode(',', $listing_defaults['checkbox_list']) : array();
            $youtubeIds = ($listing_defaults['youtube_list']) ? explode(',', $listing_defaults['youtube_list']) : array();
            $locationStr = ($listing_defaults['location']) ? $listing_defaults['location'] : '';
            $twUser = ($listing_defaults['twitter_username']) ? $listing_defaults['twitter_username'] : '';
            $listingTags = array();
            if (($listing_defaults['tags_list'])) {
                $rawTags = explode(',', $listing_defaults['tags_list']);
                foreach ($rawTags as $tag) {
                    $listingTags[] = str_replace(' ', '-', trim($tag));
                }
            }
            $leveledFields = array();

            foreach ($columnFields as $k => $field) {
                $v = $values[$k];

                if ($revolving && ($field == $revolving['unique_id'])) {
                    //this is the unique id column of the data -- store its value for later use
                    if (in_array($v, $uids_mapped)) {
                        //check for uniqueness failed, skip this listing
                        $uniqueDuplicates++;
                        //continue to the next row, don't add this one
                        continue 2;
                    }
                    $uniqueValue = $v;
                }

                if (strpos($field, 'leveled_') === 0) {
                    $fieldId = substr($field, 8); //everything after "leveled_" i.e. the ID number of this field
                    $givenValue = $v;
                    $leveledId = $this->_getLeveledIdByName($fieldId, $givenValue);
                    if ($leveledId) {
                        $leveledFields[$fieldId] = $leveledId;
                    }
                    unset($listing_field[$field]);
                    continue;
                }

                if ($field == 'category') {
                    if (!is_numeric($v)) {
                        //this is a category name -- try to match it to a category ID
                        $categoryName = $v;
                        $idResult = $this->db->GetRow($getCategoryId, array(geoString::toDB($categoryName)));
                        $v = $idResult['category_id'];
                        if (!$v) {
                            //die('invalid category name ('.$categoryName.') detected. aborting upload.');
                        }
                    }
                    $v = (int)$v;
                    if ($v) {
                        //log the categories that are used, so we can recalculate their counts later
                        $categories[$v] = $v;
                        $categoryId = $v;
                    }
                }

                if (array_key_exists($field, $listing_field)) {
                    //this is a field whose name directly matches a database column

                    if ($field == 'mapping_state') {
                        $sql = "SELECT abbreviation FROM geodesic_states WHERE name =? OR abbreviation=?";
                        $r = $this->db->getrow($sql, array($v,$v));
                        $v = (isset($r["abbreviation"])) ? $r["abbreviation"] : $v;

                        //default state data sent with some versions of the software (around 5.0.0) has trailing spaces in the abbreviations
                        //the main software takes care of this by trim()'ing. need to do it here, too. at least for now.
                        $v = trim($v);
                    }

                    if ($field === 'show_contact_seller' || $field === 'show_other_ads') {
                        //these fields store as enum('yes','no') - parse logical values into those
                        if (strtolower($v) == 'no' || $v == 0 || $v == '0' || $v == 'false') {
                            $v = 'no';
                        } else {
                            $v = 'yes';
                        }
                    }

                    if ($field === 'quantity') {
                        $v = $v < 1 ? 1 : $v; //make sure quantity is at least 1
                        $listing_field['quantity_remaining'] = $v; //set the "quantity remaining" to be the full quantity
                    }

                    if ($field === 'price_applies') {
                        $v = ($v !== 'item') ? 'lot' : 'item';
                    }

                    if ($field == 'minimum_bid') {
                        $v = (!$v) ? $listing_defaults['minimum_bid'] : str_replace("$", "", $v);
                    }

                    if ($field == 'reserve_price') {
                        $v = (!$v) ? null : str_replace("$", "", $v);
                    }

                    if ($field == 'seller') {
                        $sellerAsId = $this->lookupUserId($listingSeller); //lookupUserId gets the ID# from a username
                        $v = ($sellerAsId) ? $sellerAsId : $defaultSeller;
                    }

                    $listing_field[$field] = $v;
                    continue;
                }

                //seller-buyer
                if ($field == 'use_seller_buyer') {
                    if (!isset($sbExists)) {
                        //check to make sure the needed field is in the db
                        //also do some mini caching on the result, so we only check once
                        $sql = 'select seller_buyer_data from geodesic_classifieds';
                        $sbExists = ($this->db->Execute($sql)) ? true : false;
                    }

                    if ($sbExists) {
                        //create an array with the data
                        //it gets automagically serialized and toDB'd later on, so don't do that here
                        $listing_field['seller_buyer_data'] = array('paypal_allow_sb' => $v);
                    }

                    //unset the toggle, because there's no place to put it in the db
                    unset($listing_field['use_seller_buyer']);
                }

                //category specific fields (skip if using multiple categories)
                //key of $categorySpecific is same as key of this column loop
                //value is the question_id of the question in question (yeah, I know... :) )
                if (!$multipleCategories && strpos($field, 'categoryQuestion') !== false) {
                    $categorySpecific[$k] = substr($field, strpos($field, '_') + 1);
                }

                if ($field == 'checkbox_list') {
                    $checkboxList = explode(',', $v);
                    unset($listing_field['checkbox_list']);
                }

                if (strpos($field, "image_") !== false) {
                    if ($field === 'image_base_url') {
                        $imageBase = $v;
                        continue;
                    } elseif ($field === 'image_list') {
                        if ($v) { //only process this if data actually exists
                            $imageList = explode(",", $v);
                            $i = 1; //images start at number 1
                            foreach ($imageList as $imagePath) {
                                $image_info = pathinfo($imagePath);
                                $images[$i++] = array(
                                    'name' => $imagePath,
                                    'extension' => $image_info['extension'],
                                    'order' => $imageOrder++
                                );
                            }
                        }
                        continue;
                    } elseif ($field === 'image_caption_list') {
                        if ($v) {
                            $imageCaptions = explode(',', $v);
                            //increment array keys (so they start at 1)
                            $i = 1;
                            $increment = array();
                            foreach ($imageCaptions as $cap) {
                                $increment[$i++] = $cap;
                            }
                            $imageCaptions = $increment;
                        }
                        continue;
                    } elseif (strpos($field, 'image_caption') === 0) {
                        //numbered image caption field (image_caption_1)
                        $imageCaptions[substr($field, strrpos($field, '_') + 1)] = $v;
                        continue;
                    } elseif ($field === 'image_update') {
                        $useImageUpdateSwitch = true;
                        $updateImages = $v;
                        unset($listing_field['image_update']);
                        continue;
                    }

                    if ($v) {
                        //this is one of the numbered image fields, e.g. $field == 'image_1'
                        $image_info = pathinfo($v);
                        $images[substr($field, strrpos($field, '_') + 1)] =   array(
                            'name' => $v,
                            'extension' => $image_info['extension'],
                            'order' => $imageOrder++
                        );
                    }
                }


                if ($field == 'youtube_list') {
                    $youtubeIds = explode(",", $v);
                    unset($listing_field['youtube_list']);
                    if (!count($youtubeIds)) {
                        unset($youtubeIds);
                    }
                }


                if ($field == 'location') {
                    $locationStr = $v;
                    unset($listing_field['location']);
                }


                if ($field == 'twitter_username' && $geoAddon->isEnabled('twitter_feed')) {
                    $twUser = $v;
                    unset($listing_field['twitter_username']);
                }

                if ($field == 'tags_list') {
                    //listing tags
                    //convert spaces to dashes, and break into an array
                    $listingTags = array(); //clear any defaults first
                    $rawTags = explode(',', $v);
                    foreach ($rawTags as $tag) {
                        $listingTags[] = str_replace(' ', '-', trim($tag));
                    }
                    //remove from $listing_field, because these go into their own, separate db table
                    unset($listing_field['tags_list']);
                }

                if (substr($field, 0, 3) === 'co_') {
                    //this is something to do with "cost options"

                    if ($field === 'co_combinedquantities') {
                        $costOptions['combinedquantities'] = $v;
                        unset($listing_field['co_combinedquantities']);
                    } else {
                        $groupNumber = substr($field, strpos($field, '_') + 1, (strrpos($field, '_') - strpos($field, '_') - 1)); //get the number out of, e.g. "co_14_groupname" (between the two underscores)
                        $groupFieldType = substr($field, strrpos($field, '_') + 1); //everything after the last underscore
                        $costOptions['groups'][$groupNumber][$groupFieldType] = $v;
                        unset($listing_field[$field]);
                    }
                }
            }
            if ($revolving && $uniqueValue === null) {
                //unique value not found?  This shouldn't happen, weird...
                if ($isCron) {
                    $cron->log('Unique value not set???', __line__ . $logAppend);
                }
                continue;
            }

            //figure out if doing update or insert
            if ($isCron && in_array($uniqueValue, $existingUids)) {
                //this is an 'update' of classified# $update_id being done by the revolving uploader
                if ($isCron) {
                    $cron->log('Processing update for uniqueValue: ' . $uniqueValue, __line__ . $logAppend);
                }
                $action = 'update';
                $update_id = array_search($uniqueValue, $existingUids);
                $listing = geoListing::getListing($update_id);
                if (!$listing || !$listing->live) {
                    if ($isCron) {
                        $cron->log('listing appears to be expired. will attempt to insert as new.', __line__ . $logAppend);
                    }
                    //this listing has expired. insert the data as a new listing instead of an update
                    $action = 'insert';
                    $update_id = null;
                }
            } else {
                if ($isCron) {
                    $cron->log('trying to insert as a new listing', __line__ . $logAppend);
                }
                $action = 'insert';
            }

            //now that we know what action is being taken, we can check to see if we need to skip this listing or not
            if ($action !== 'update' && $failedUserCheck == true) {
                if ($isCron) {
                    $cron->log('This is NOT an update. Skipping it, since the user check was failed, above.', __line__ . $logAppend);
                }
                continue;
            }

            $planItem = geoPlanItem::getPlanItem('images', $pricePlanToUse, $listing_field['category']);
            if (count($images) > 0) {
                if ($settings['checkUserLimits']) {
                    $maxImages = $planItem->get('max_uploads', 8);
                    $imageCount = min($maxImages, count($images));
                } else {
                    $imageCount = count($images);
                }
            } else {
                $imageCount = 0;
            }
            $listing_field['image'] = $imageCount;

            $planItem = geoPlanItem::getPlanItem('offsite_videos', $pricePlanToUse, $listing_field['category']);
            if (count($youtubeIds) > 0) {
                if ($settings['checkUserLimits']) {
                    $maxYoutubes = $planItem->get('maxVideos');
                    $youtubeCount = min($maxYoutubes, count($youtubeIds));
                } else {
                    $youtubeCount = count($youtubeIds);
                }
            } else {
                $youtubeCount = 0;
            }
            $listing_field['offsite_videos_purchased'] = $youtubeCount;


            if (!$listing_field['precurrency'] && $listing_field['price']) {
                //price is set, but precurrency is not -- default to site precurrency
                //(if price not set, don't do this, so that the postcurrency-only label style can be used, e.g., "CALL US")
                $listing_field['precurrency'] = $this->db->get_site_setting('precurrency');
            }
            if (!$listing_field['postcurrency']) {
                //postcurrency not set -- default to site postcurrency
                $listing_field['postcurrency'] = $this->db->get_site_setting('postcurrency');
            }

            //if admin set a custom title on step 3, override existing title with it
            if (count($customTitle) > 0) {
                $title = array();
                $categoryKeys = array_flip($categorySpecific);
                foreach ($customTitle as $column) {
                    if (isset($listing_field[$column])) {
                        $title[] = $listing_field[$column];
                    } elseif (strpos($column, 'categoryQuestion') !== false) {
                        //this title piece comes from a category question
                        $question_id = substr($column, strpos($column, '_') + 1);
                        //question_id is the database-id of the question we're interested in
                        //$categorySpecific is an array of colNumber => db-id
                        //$categoryKeys is an array_flip() of $categorySpecific
                        //so $categoryKeys[$question_id] is the column number to look in for this data
                        $title[] = $values[$categoryKeys[$question_id]];
                    }
                }
                if (!empty($title)) {
                    $listing_field['title'] = implode(" ", $title);
                }
            }

            if (!$listing_field['title'] || !$listing_field['seller']) {
                //if the listing doesn't have a title or seller at this point, we've probably got corrupt data,
                //such as admin refreshing a page or returning to the admin after logging out on Step 4
                //don't place this listing.
                if ($isCron) {
                    $cron->log('listing data not found or corrupted. skipping listing.', __line__ . $logAppend);
                }
                $skipCount++;
                continue;
            }

            $listing = array();

            //get array that says how to encode each field for the DB
            require_once(CLASSES_DIR . '/order_items/_listing_placement_common.php');
            $encoding_types = _listing_placement_commonOrderItem::getListingVarsToUpdate();

            //list of fields to not change when doing an update
            $noUpdateFields = array('id','duration',
            'one_votes','two_votes','three_votes','vote_total',
            'minimum_bid','current_bid','final_price',
            'viewed','responded','forwarded');

            if ($settings['duration']['method'] == 2) {
                //static start/end times never update!
                $noUpdateFields[] = 'date';
                $noUpdateFields[] = 'start_time';
                $noUpdateFields[] = 'ends';
                $noUpdateFields[] = 'end_time';
            } elseif ($settings['duration']['adjustTimes'] == 1) {
                //don't update start time, but set ends to now + initial duration
                $noUpdateFields[] = 'date';
                $noUpdateFields[] = 'start_time';
            }

            foreach ($listing_field as $k => $v) {
                if ($k) {
                    if ($action == 'update' && in_array($k, $noUpdateFields)) {
                        //this is an update of an existing listing, and this field should not be changed
                        //continuing here keeps it from being added to the query
                        continue;
                    }

                    switch ($encoding_types[$k]) {
                        case 'toDB':
                            if (is_array($v) && $k == 'seller_buyer_data' && geoPC::is_ent()) {
                                //special case
                                $v = serialize($v);
                            }
                            $v = trim(geoString::toDB($v));
                            break;
                        case 'int':
                            $v = intval($v);
                            break;
                        case 'float':
                            $v = floatval($v);
                            break;
                        case 'bool':
                            $v = (($v) ? 1 : 0);
                            break;
                        default:
                            //not altered, for fields like "date"
                            break;
                    }
                    $listing[] =  "`$k` = '$v'";
                }
            }

            $listing = implode(",\n", $listing);

            if ($action == 'insert') {
                $sql = "INSERT INTO $this->classifieds_table SET \n$listing";
                $r = $this->db->Execute($sql);
                if (!$r) {
                    $failedRows[$row] = $values;
                    die($this->db->ErrorMsg() . "<br /> $sql");
                }
                $insertCount++;
                $insertedID = $this->db->Insert_ID();

                //add this listing to the log for this upload session (query is prepared way up at the top of this function)
                $this->db->Execute($logQuery, array($insertedID));

                if ($isCron) {
                    $cron->log("Just inserted new listing, new ID is $insertedID", __line__ . $logAppend);
                }
            } elseif ($action == 'update') {
                $sql = "UPDATE " . geoTables::classifieds_table . " SET $listing WHERE `id` = " . (int)$update_id;
                $r = $this->db->Execute($sql);
                if (!$r) {
                    $failedRows[$row] = $values;
                    die($this->db->ErrorMsg() . "<br /> $sql");
                }
                $updateCount++;

                if ($usingNewLog) {
                    //for whatever reason, we've created a new log to use for this set of listings
                    //remove any references to this listing from old logs
                    $this->db->Execute($purgeLogQuery, array($update_id));
                    //and then add it to the new one
                    $this->db->Execute($logQuery, array($update_id));
                    //note: both queries above are prepared way up outside this loop
                }

                //set ID so that pics and cat-fields can be loaded, then delete the old versions of those to make room for the new
                $insertedID = $update_id;

                if ($isCron) {
                    $cron->log("Just updated existing listing, ID is $insertedID", __line__ . $logAppend);
                }
                //reset extra questions for this listing and re-add them below
                //(a similar reset of images takes place in the insertImages function)
                $sql = "DELETE FROM " . geoTables::classified_extra_table . " WHERE classified_id = ?";
                $this->db->Execute($sql, array($insertedID));
            } elseif ($isCron) {
                $cron->log('Weird, action not set?', __line__ . $logAppend);
            }

            if ($revolving) {
                $uids_mapped [$insertedID] = $uniqueValue;
            }
            $categoryId = ($categoryId) ? $categoryId : $listing_defaults["category"];
            geoCategory::setListingCategory($insertedID, $categoryId);
            unset($categoryId);
            //now that we have the listing ID, if useDefaultUserData is set, pull over the Regions
            if ($settings['useDefaultUserData'] && $listingSeller) {
                $defaultRegions = geoRegion::getRegionsForUser($listingSeller);
                geoRegion::setListingRegions($insertedID, $defaultRegions);
            }

            //handle images
            /*
             * $images: array of image data to be processed
             * $insertedID: ID of listing to add images to; from mysql auto increment value
             * if either missing, can't process images.
             *
             * $useImageUpdateSwitch: true if the image_update column switch is present in upload data
             * $updateImages: true if we want to update the images on this listing
             * process images if switch not present, or if switch present and turned on
             *
             * $imageBase: base path for image urls
             * $imageCount: maximum number of images to add to listing (usually per checkUserLimits)
             * fast_image_proc: if true, skip fancy image processing in favor of speed
             */
            if ($images && $insertedID) {
                //there are images to process
                if ($action == 'update') {
                    //this is an update -- see if the switch is on
                    $processImages = ($useImageUpdateSwitch && !$updateImages) ? false : true;
                } else {
                    //always add images on an insert
                    $processImages = true;
                }
                if ($isCron) {
                    $cron->log('processImages is ' . (($processImages) ? 'true' : 'false'), __line__ . $logAppend);
                }
                if ($processImages) {
                    $this->insertImages($images, $insertedID, $imageBase, $imageCount, $settings['config']['fast_image_proc'], $imageCaptions);
                }
            }

            if ($youtubeIds && $insertedID) {
                if ($isCron) {
                    $cron->log('about to process youtube videos', __line__ . $logAppend);
                }
                if (!($action == 'update' && $useImageUpdateSwitch && !$updateImages)) {
                    //skip this if it's an update and the imageUpdate switch is off
                    $this->insertYoutube($youtubeIds, $insertedID, $youtubeCount);
                }
            }

            if ($locationStr && $insertedID) {
                $locations = explode(',', $locationStr);
                if (count($locations) == 1) {
                    //only one region for this listing -- do it the fast way
                    $regionId = $this->_getTerminalRegionId(trim($locations[0]));
                    if ($regionId) {
                        $regionsForListing = geoRegion::getRegionWithParents($regionId);
                        geoRegion::setListingRegions($insertedID, $regionsForListing);
                    }
                } else {
                    $regionsForListing = array();
                    foreach ($locations as $location) {
                        $regionId = $this->_getTerminalRegionId(trim($location));
                        if ($regionId) {
                            $regionsForListing[] = $regionId;
                        }
                    }
                    geoRegion::setListingEndRegions($insertedID, $regionsForListing);
                }
            } elseif ($settings['useDefaultUserData'] && $insertedID && $listingSeller) {
                //copy regions from user
                geoRegion::setListingRegions($insertedID, geoRegion::getRegionsForUser($listingSeller));
            }

            if ($twUser && $geoAddon->isEnabled('twitter_feed') && $insertedID) {
                $sql = "REPLACE INTO `geodesic_addon_twitter_feed_usernames` (`listing_id`, `twitter_name`, `active`) VALUES (?,?,?)";
                $r = $this->db->Execute($sql, array($insertedID, $twUser, 1));
            }

            if ($leveledFields && $insertedID) {
                geoLeveledField::setListingValues($insertedID, $leveledFields);
            }

            //build array of search_text values
            //IMPORTANT: clear this for every listing
            $searchTexts = array();

            // handle category specific questions
            $search_text = $searchTexts = array();
            if ($checkboxList) {
                foreach ($checkboxList as $checkboxID) {
                    $sql = "SELECT * FROM " . $this->classified_questions_table . " WHERE question_id='$checkboxID'";
                    $checkboxInfo = $this->db->GetRow($sql);
                    $sql = "INSERT INTO geodesic_classifieds_ads_extra (classified_id, name, question_id, value, checkbox, group_id, display_order) values ";
                    $sql .= "(?,?,?,?,?,?,?)";
                    $queryData = array($insertedID, $checkboxInfo['name'], $checkboxID, $checkboxInfo['name'], 1, $checkboxInfo['group_id'], $checkboxInfo['display_order']);
                    $r = $this->db->Execute($sql, $queryData);
                    $searchTexts[] = $checkboxInfo['name'];
                }
            }
            if (!empty($categorySpecific)) {
                foreach ($categorySpecific as $key => $question_id) {
                    $checkbox = 0;
                    if (!$question_id) {
                        continue;
                    }
                    $categoryValue = ($values[$key]) ? $values[$key] : '';

                    $sql_query = "SELECT * FROM " . $this->classified_questions_table . " WHERE question_id='$question_id' LIMIT 1";
                    $result = $this->db->Execute($sql_query);
                    $resultInfo = $result->FetchRow();
                    if ($resultInfo["choices"] == "check") {
                        /*
                           to mark a checkbox as "on," use one of $textResponses, or a non-zero integer (probably 1)
                           to mark it as "off," use anything PHP evals to boolean false (such as 0), or any string not in $textResponses (such as "off")
                        */
                        $textResponses = array('true', 'yes', 'on');
                        if (!$categoryValue && !in_array(strtolower($categoryValue), $textResponses)) {
                            //this checkbox shouldn't be checked -- skip the insert query
                            continue;
                        }
                        $categoryValue = $resultInfo["name"];
                        $checkbox = 1;
                    }

                    $sql = "INSERT INTO geodesic_classifieds_ads_extra (classified_id, name, question_id, value, checkbox, group_id, display_order) values ";
                    $sql .= "(?,?,?,?,?,?,?)";
                    $queryData = array($insertedID, geoString::toDB($resultInfo['name']), $question_id, geoString::toDB($categoryValue), $checkbox, $resultInfo['group_id'], $resultInfo['display_order']);
                    $r = $this->db->Execute($sql, $queryData);
                    if ($r === false) {
                        $err = 'Failed to insert category specific question # ' . $question_id . ' for this reason: ' . $this->db->ErrorMsg() . '<br />';
                        $err .= 'The query was: ' . $sql . '<br />';
                        $err .= 'query data: <pre>' . print_r($queryData, 1) . '</pre>';
                        die($err);
                    }
                    if ($categoryValue) {
                        $searchTexts[] = $categoryValue;
                    }
                }
                $userid = '';
            }

            //handle listing tags
            if ($this->_addTags($insertedID, $listingTags)) {
                //added one or more tags. also add them to the searchTexts array
                $searchTexts = array_merge($searchTexts, $listingTags);
            }

            if ($costOptions && $insertedID) {
                $this->_processCostOptions($costOptions, $insertedID);
            }

            if (count($searchTexts)) {
                $search_text = geoString::toDB(implode(' - ', $searchTexts));
                $sql = "update geodesic_classifieds set search_text = ? where id = ?";
                $result = $this->db->Execute($sql, array($search_text, $insertedID));
            }
            $listingsUploaded++;
        }

        if ($multipartId) {
            //if uploaded fewer than 'count' listings, this is EOF, and we're done. delete the multipart session
            if ($listingsUploaded < $multipart_count) {
                $sql = "DELETE FROM `geodesic_addon_bulk_uploader_multipart` WHERE `id` = ?";
                $this->db->Execute($sql, array($multipartId));
            } else {
                //we have completed part of a multipart upload
                //update the db so we know where and when to pick up next time
                $sql = "UPDATE `geodesic_addon_bulk_uploader_multipart` SET `last_run` = ?, `completed` = ? WHERE `id` = ?";
                $this->db->Execute($sql, array(geoUtil::time(), ($multipart_completed + $listingsUploaded), $multipartId));
            }
        }

        if (!$isCron) {
            //close file handler
            fclose($handle);
            //report stats
            if ($insertCount) {
                geoAdmin::m('Inserted ' . $insertCount . ' new listings');
            }
            if ($skipCount) {
                geoAdmin::m('Skipped ' . $skipCount . ' listings. Either the "title" or "seller" data was missing, or you refreshed this page.', geoAdmin::NOTICE);
            }
        } else {
            $cron->log('Main upload file complete. Inserted: ' . $insertCount . ' | Updated: ' . $updateCount . ' | Skipped: ' . $skipCount, __LINE__);
        }

        if ($revolving) {
            //log revolving unique_ids to the database...somewhere
            //old way: $revolving['uids_mapped'] = $uids_mapped;

            $uidLog = $this->db->Prepare("INSERT INTO `geodesic_addon_bulk_uploader_revolving_map` (revolving_id, listing_id, uid) VALUES (?,?,?)");
            foreach ($uids_mapped as $listingId => $uniqueVal) {
                $r = $this->db->Execute($uidLog, array($revolvingLabel, $listingId, $uniqueVal));
                if (!$r && $isCron) {
                    $cron->log('map insert fail: ' . $this->db->ErrorMsg());
                }
            }

            $reg->$revolvingLabel = $revolving;

            if ($uniqueDuplicates > 0) {
                $message = "Note:  Found $uniqueDuplicates rows with the same value in the unique field.  The duplicate rows were skipped.";
                if (!$isCron) {
                    geoAdmin::m($message, geoAdmin::NOTICE);
                } else {
                    $cron->log($message, __line__ . $logAppend);
                }
            }
        }

        if ($multipleCategories) {
            //loop through categories used and recalculate their listing counts
            //this process is computationally expensive, so make sure we only do each category once
            $catsToUpdate = array_unique($categories);

            foreach ($catsToUpdate as $update) {
                geoCategory::updateListingCount($update);
            }
        } else {
            //just one category to update
            geoCategory::updateListingCount($settings['config']['category']);
        }
        if (!$isCron) {
            $this->session->clear('config');
            $this->session->free('fileName');
            $this->session->free('page');
        }
    }


     /*
     * check an array of data created by fgetcsv to determine whether it contains valid data
     */
    private function _isValidRow($row)
    {
        if (!$row) {
            return false;
        }

        //Excel will sometimes leave blank rows at the end of files that users don't detect, such as:
        //,,,,,,,,,,,,,,,,,
        //these count as otherwise-valid entries for fgetcsv, but can cause erroneous behavior
        //so let's check for them, and pass over any that are found
        $strlens = array_map('strlen', $row);
        foreach ($strlens as $strlen) {
            //if at least one column has a valid value, this is not junk
            if ($strlen) {
                return true;
            }
        }
        return false;
    }


    /*
     * determine whether this user has the needed rights to add a new listing.
     * returns false if user's subscription is expired or user has exceeding max number of listings
     */
    public $limitFails = array(); //caching var, so we only have to check this once for each user/type
    private function _checkUserLimits($userId, $pricePlanId)
    {
        if (!$userId || $item_type) {
            //how did that happen?
            return false;
        }
        if ($this->limitFails[$userId][$item_type] == 1) {
            //this result is cached as failure. don't need to check again
            return false;
        }
        $db = DataAccess::getInstance();
        $sql = "SELECT `type_of_billing`, `max_ads_allowed` FROM " . geoTables::price_plans_table . " WHERE `price_plan_id` = ?";
        $pricePlan = $db->GetRow($sql, array($pricePlanId));

        if ($pricePlan['type_of_billing'] == 2) {
            //this user is on a subscription plan. see if the subscription is expired or not
            $sql = "SELECT `subscription_expire` FROM " . geoTables::user_subscriptions_table . " WHERE user_id = ?";
            $expirationTime = $db->GetOne($sql, array($userId));
            if ($expirationTime <= geoUtil::time()) {
                //user's subscription is expired. don't place listing
                $this->limitFails[$userId][$item_type] = false;
                return false;
            }
        }

        $sql = "SELECT count(id) FROM " . geoTables::classifieds_table . " WHERE seller = ? AND live = 1";
        $currentListings = $db->GetOne($sql, array($userId));

        if ($currentListings >= $pricePlan['max_ads_allowed']) {
            //this user can't place any more listings
            $this->limitFails[$userId][$item_type] = false;
            return false;
        }
        //everything's good -- allow this listing to be placed
        return true;
    }


    private function _getDefaultUserData($userId)
    {
        $user = geoUser::getUser($userId);
        if (!$user) {
            return false;
        }

        $address = ($user->address_2) ? $user->address . ' ' . $user->address_2 : $user->address;
        $mapping_location = array();
        if ($user->address) {
            $mapping_location[] = $user->address;
        }
        if ($user->city) {
            $mapping_location[] = $user->city;
        }
        if ($user->state) {
            $mapping_location[] = $user->state;
        }
        if ($user->zip) {
            $mapping_location[] = $user->zip;
        }
        if ($user->country) {
            $mapping_location[] = $user->country;
        }
        $mapping_location = implode(" ", $mapping_location);
        $defaults = array(
            'email' => $user->email,
            'location_address' => $address,
            'location_zip' => $user->zip,
            'location_city' => $user->city,
            'mapping_location' => $mapping_location,
            'phone' => $user->phone,
            'phone2' => $user->phone2,
            'fax' => $user->fax,
            'url_link_1' => $user->url,
            'business_type' => $user->business_type,
        );
        return $defaults;
    }



    public function get_questions($category, $group = 0)
    {
        if (!$group) {
            $group = (geoPC::is_ent()) ? intval($group) : 0;
        }
        $category = intval($category);
        if (!$group && !$category) {
            //can't get questions w/o category or group
            return false;
        }

        $where = array();
        while ($category != 0) {
            //get all the parent categories.
            $where[] = "`category_id` = $category";
            $sql = "SELECT `parent_id` FROM " . geoTables::categories_table . " WHERE `category_id` = ?";
            $row = $this->db->GetRow($sql, array($category));
            $category = $row['parent_id'];
        }

        if ($group) {
            $where[] = "`group_id` = $group";
        }

        //get the questions for this group/category
        $sql = "SELECT * FROM geodesic_classifieds_sell_questions WHERE " . implode(' OR ', $where) . " ORDER BY `display_order`";
        $questions = $this->db->GetAll($sql);
        if ($questions === false) {
            $this->site_error($this->db->ErrorMsg());
            return false;
        }
        $this->questions = array();
        if (count($questions) == 0) {
            return;
        }
        foreach ($questions as $key => $row) {
            $sql = "SELECT * FROM " . geoTables::questions_languages . " `question_id` = ? AND `language_id` = ?";
            $lang_row = $this->db->GetRow($sql, array($row['question_id'],$this->language_id));
            if ($lang_row) {
                $questions[$key] = array_merge($row, $lang_row);
            }
        }
        return $questions;
    }

    /**
     * builds presentation layer
     *
     * @param int $page
     * @return void
     */
    function displayBulkUploader($page = 0)
    {
        $page = (isset($_GET["p"]) ? intval($_GET["p"]) : $this->session->config('page'));

        $this->setHeadInformation();

        $highlight = false;

        if (!$page || !$this->session->config("fileName")) {
            $page = 1;
            $highlight = true;
        }

        //show progress breadcrumb
        $steps = array(
            1 => 'File Information',
            2 => 'Assign Fields',
            3 => 'Listing Options',
            4 => 'Upload Complete'
        );

        if ($this->critical_error) {
            //encountered a stop error. go back to the step before this one and show the error so it can be fixed.
            $page--;
        }

        switch ($page) {
            default:
            case 1:
                {
                    $f_compression = false;
                    $zip = (function_exists("zip_read")) ? 1 : 0;
                    $gzopen = (function_exists("gzopen")) ? 1 : 0;
                    $bzopen = (function_exists("bzopen")) ? 1 : 0;
                if ($zip || $gzopen || $bzopen) {
                    $f_compression = true;
                }
                    $tooltip[0] = $this->show_tooltip(1, 1);
                    $tooltip[1] = $this->show_tooltip(2, 1);
                    $delimiter = (strlen($this->session->config('delimiter'))) ? $this->session->config('delimiter') : ",";
                    $images_delimiter =  (strlen($this->session->config('images_delimiter'))) ? $this->session->config('images_delimiter') : ",";
                    $encapsulation = (strlen($this->session->config('encapsulation'))) ? $this->session->config('encapsulation') : "\"";


                    $body .= "
					<form action='$this->self_path&p=2' method='POST' enctype='multipart/form-data' class='form-horizontal form-label-left' >
					<fieldset>
					<legend>Bulk Uploader</legend>
					<div class='x_content'>

						<div class='form-group'>
						<label class='control-label col-md-5 col-sm-5 col-xs-12'>File Type Allowed: </label>
						  <div class='col-md-6 col-sm-6 col-xs-12'>
						  <span class='vertical-form-fix'>csv (<em><a href='$this->url/docs/example_with_title.txt' target='_blank'>see sample</a></em>)</span>
						  </div>
						</div>

						<div class='form-group'>
						<label class='control-label col-md-5 col-sm-5 col-xs-12'>Select A File: </label>
						  <div class='col-md-6 col-sm-6 col-xs-12'>
							 <span class='vertical-form-fix'><input type='file' id='file_name' name='csvfile' class='col-md-7 col-xs-12' style='min-width: 200px; padding: 0;'/></span>
						</div>
						</div>

						<div class='form-group'>
						<label class='control-label col-md-5 col-sm-5 col-xs-12'>Skip First Row: " . geoHTML::showTooltip('Skip First Row', 'Select <strong>Yes</strong> to skip the first row in your file and keep it from being uploaded.
								For instance, if the top row of your file contains the definition for what each column represents, and not actual listing data, then you would not want to include it in the upload. You would want to "skip it".') . "</label>
						  <div class='col-md-6 col-sm-6 col-xs-12'>
								<select id='data[skipfirstrow]' name='data[skipfirstrow]' class='form-control col-md-7 col-xs-12'>
								<option value='0'>No</option>
								<option value='1'" . ($this->session->config('skipfirstrow') ? ' selected' : '') . ">Yes</option>
								</select>
						  </div>
						</div>

						<div class='form-group'>
						<label class='control-label col-md-5 col-sm-5 col-xs-12'>Preview Rows: </label>
						  <div class='col-md-6 col-sm-6 col-xs-12'>
								<select id='data[previewLength]' name='data[previewLength]' class='form-control col-md-7 col-xs-12'>
								<option value='5'" . (($this->session->config('previewLength') == 5) ? 'selected' : '') . ">5</option>
								<option value='10'" . (($this->session->config('previewLength') == 10) ? 'selected' : '') . ">10</option>
								<option value='15'" . (($this->session->config('previewLength') == 15) ? 'selected' : '') . ">15</option>
								</select>
						  </div>
						</div>
						";

                if ($f_compression) {
                    $body .= "

						<div class='form-group'>
						<label class='control-label col-md-5 col-sm-5 col-xs-12'>File Compression: </label>
						  <div class='col-md-6 col-sm-6 col-xs-12'>
								<select id='data[compression]' name='data[compression]' class='form-control col-md-7 col-xs-12'>
								<option value='0' selected>None
								";
                    if ($zip) {
                        $body .= "<option value='zip'" . (($this->session->config('compression') == 'zip') ? 'selected' : '') . ">zip";
                    }
                    if ($gzopen) {
                        $body .= "<option value='gzip'" . (($this->session->config('compression') == 'gzip') ? 'selected' : '') . ">gzip";
                    };
                    if ($bzopen) {
                        $body .= "<option value='bz2'" . (($this->session->config('compression') == 'bz2') ? 'selected' : '') . ">bz2";
                    }
                            $body .= "
								</select>
						  </div>
						</div>
						";
                }

                    $body .= "
					<div class='form-group'>
					<label class='control-label col-md-5 col-sm-5 col-xs-12'>Delimiter: <br>
					<span class='small_font'>Example: (comma) <span class='color-primary-one' style='font-size: 1.4em;'><b>,</b></span> or
													  (dot) <span class='color-primary-one' style='font-size: 1.4em;'><b>.</b></span> or
													  (pipe) <span class='color-primary-one' style='font-size: 1.4em;'><b>|</b></span> ...etc</span></label>
					  <div class='col-md-6 col-sm-6 col-xs-12'>
					  <input type='text' id='data[delimiter]' name='data[delimiter]' size='5' class='form-control col-md-7 col-xs-12' value='" . $delimiter . "'>
					  </div>
					</div>

					<div class='form-group'>
					<label class='control-label col-md-5 col-sm-5 col-xs-12'>Encapsulation: <br>
					<span class='small_font'>Example: (comma) <span class='color-primary-one' style='font-size: 1.4em;'><b>,</b></span> or
													  (dot) <span class='color-primary-one' style='font-size: 1.4em;'><b>.</b></span> or
													  (pipe) <span class='color-primary-one' style='font-size: 1.4em;'><b>|</b></span> ...etc</span></label>
					  <div class='col-md-6 col-sm-6 col-xs-12'>
					  <input type='text' id='data[encapsulation]' name='data[encapsulation]' size='5' class='form-control col-md-7 col-xs-12' value='" . $encapsulation . "'>
					  </div>
					</div>

					<div class='form-group'>
					<label class='control-label col-md-5 col-sm-5 col-xs-12'>Category: </label>
					  <div class='col-md-6 col-sm-6 col-xs-12'>
						" . $this->categoryDropdown($this->session->config('category')) . "
						<br />";

                        $checked = ($this->session->config('multiple_categories') == 1) ? 'checked="checked"' : '';

                        $body .= "<input type='checkbox' name='data[multiple_categories]' value='1' onclick='$(\"categoryDropdown\").disabled = (this.checked) ? true : false;' " . $checked . " /> Multiple Categories<br>
						<span class='small_font' style='margin-left: 15px;'>(Selected on Next Page)</span>
					  </div>
					</div>
					";
                                    // Step label
                if (geoMaster::is('classifieds') && geoMaster::is('auctions')) {
                    $body .= "
						<div class='form-group'>
						<label class='control-label col-md-5 col-sm-5 col-xs-12'>Listings Data Type: </label>
						  <div class='col-md-6 col-sm-6 col-xs-12'>
							<select id='data[type]' name='data[type]' class='form-control col-md-7 col-xs-12'>
							<option value='classified'" . (($this->session->config('type') == 'classified') ? ' selected' : '') . ">Classified</option>
							<option value='auction'" . (($this->session->config('type') == 'auction') ? ' selected' : '') . ">Auction</option>
							</select>
						  </div>
						</div>
						";
                } else {
                    $body .= "<input type='hidden' name='data[type]' value='" . (geoMaster::is('classifieds') ? "classified" : "auction") . "'>";
                }

                    $body .= "</div></fieldset>";

                    $body .= "<fieldset><legend>Image Upload Options</legend>
					<div class='x_content'>
						<div class='form-group'>
						<label class='control-label col-md-5 col-sm-5 col-xs-12'>Image Upload Type: </label>
						  <div class='col-md-6 col-sm-6 col-xs-12'>
							<select name='data[fast_image_proc]' class='form-control col-md-7 col-xs-12'>
								<option value='0'>Better Quality</option>
								<option value='1'>Faster</option>
							</select>
						  </div>
						</div>

						<div class='form-group'>
						<label class='control-label col-md-5 col-sm-5 col-xs-12'>Base Image Path: </label>
						  <div class='col-md-6 col-sm-6 col-xs-12'>
						  <input name='data[base_image_path]' type='text' class='form-control col-md-7 col-xs-12' value='" . $this->session->config('base_image_path') . "' />
						  </div>
						</div>
					</div>

					</fieldset>";


                    $body .= $this->RevolvingInventoryForm();

                    $body .= "
							<div class='centerColumn'>
							<input type=submit id=formSubmit value='Continue'>&nbsp;";

                            $body .= "
						</div>
					</form>
					";
            }
            break;
            case 2:
                {
                    $this->addJs(2);

                    $skip = $this->session->config('skipfirstrow');
                    $diplay_total = $this->getCSVLength($this->session->config('fileName'));
                    $len  = $preview_number = $this->session->config('previewLength');

                if ($skip) {
                    $diplay_total--;
                }
                    $body .= "
					<form action='$this->self_path&p=3' method='POST'  class='form-horizontal form-label-left'>
					<div style='position:relative;overflow:scroll;width:100%;height:auto;border-style:solid;border-width:1px;border:1px solid #cfcfcf;'>
						<fieldset>
						<legend>Assign Columns <span class='color-primary-one' style='font-size: 0.8em;'>(Previewing First $len of $diplay_total)</span></legend>
						<div>
							" . $this->getCSVColumnBlock($highlight) . "
						</div>
						</fieldset>
					</div>
					";

                    $body .= "<div class='centerColumn'>";
                    $body .= (($page > 1 && $page != 4) ? "<input type=button value='Back' onClick='javascript: window.location = \"$this->self_path&p=" . ($page - 1) . "\";'>&nbsp;" : '');
                    $body .= "<input type=submit id=formSubmit value='Continue'>&nbsp;";
                    $body .= "</div></form>";

            }
            break;
            case 3:
                {
                    $this->addJs(3);

                    $columns = $this->session->getArray("bulkColumn");
                if ($this->session->config('multiple_categories')) {
                    //using multiple categories -- make sure category column has been chosen, and show a warning if not
                    if (!in_array('category', $columns)) {
                        geoAdmin::m('You have chosen to upload to multiple categories, but did not include a category column in the upload data. You MUST go back and assign a category column, or this upload will not complete.', geoAdmin::ERROR);
                    }
                }
                if (!in_array('title', $columns)) {
                    geoAdmin::m('You did not select a column of data as the listing title. You MUST either go back and choose a title column, or use the "Combine Fields to Create the Title" setting below. Listings that do not contain a title will fail to upload.', geoAdmin::NOTICE);
                }

                    $body .= "
					<form action='$this->self_path&p=4' method='post'  onsubmit='return validateSettings()' class='form-horizontal form-label-left'>";
                    $body .= $this->alterUploadForm();
                    $body .= ($page > 1 && $page != 4) ? "<div class='center'><input type=button value='Back' onClick='javascript: window.location = \"$this->self_path&p=" . ($page - 1) . "\";'>&nbsp;" : "";

                    $body .= "<input type=reset value='Reset' title='This only resets the current page'></div>";
                    $body .= "<div class='center'><input type=submit id=formSubmit value='Upload Listings'></div>";
                    $body .= "</form>";
            }
            break;
            case 4:
                {
                    $this->insertCSV();

                if ($revolving = $this->session->config('revolving_label')) {
                    $this->initializeRevolvingCron($revolving);
                    require_once(ADDONS_DIR . 'bulk_uploader/registry.php');
                    $reg = new geoBulkUploaderRegistry();
                    $data = $reg->get($revolving);
                    $updatefile = $data['config']['updatefile'];
                    $body .= '<fieldset><legend>Revolving Inventory Update File</legend>
									<div class="page_info">Revolving Inventory added successfully.
									The update filename is: <strong>' . $updatefile . '</strong></div></fieldset>';
                }

                    $body .= "<form action='$this->self_path&p=1' method='POST' class='form-horizontal form-label-left'>";
                    $body .= $this->getUploadForm("Status", "New Upload", $page, $this->getFinishedBlock());
                    $body .= "</form>";
                    $this->session->free();
                    //session->free doesn't always work as expected, so brute-force remove the "page" variable, so the uploader resets next time
                    $sql = "delete from geodesic_addon_bulk_uploader_session where name='page'";
                    DataAccess::getInstance()->Execute($sql);
            }
            break;
        }

        return $body;
    }


    /**
     * body of the form for Step 3
     *
     */
    function alterUploadForm()
    {
        $db = DataAccess::getInstance();
        require_once(ADDONS_DIR . 'bulk_uploader/registry.php');
        $reg = new geoBulkUploaderRegistry();

        $sql = 'SELECT id, username FROM ' . geoTables::userdata_table . ' ORDER BY `id` ASC';
        $result = $db->Execute($sql);
        $users = array();
        $defaultSeller = $this->session->config('seller') ? $this->session->config('seller') : $reg->_savedSeller;
        if (!$result || $result->RecordCount() > 500) {
            //can't find users, or lots of users. show text input instead of trying to use a dropdown
            $default_seller_input = "<input type='text' id='seller' name='seller' value='" . $defaultSeller . "' size='20' " . ($highlight ? "class='highlight'" : "") . ">";
        } else {
            $default_seller_input = '<select id="seller" name="seller" ' . ($highlight ? 'class="highlight"' : '') . ' class="form-control col-md-7 col-xs-12">';
            while ($line = $result->FetchRow()) {
                $default_seller_input .= '<option value="' . $line['id'] . '" ' . (($line['id'] == $defaultSeller) ? 'selected="selected"' : '') . '>' . $line['id'] . ' - ' . $line['username'] . '</option>';
            }
            $default_seller_input .= '</select>';
        }

        $body = "
		<fieldset>
		<legend>Alter Listing Data</legend>
		<div class='x_content'>

        <div class='form-group'>
        <label class='control-label col-md-5 col-sm-5 col-xs-12'>Default Seller: " . geoHTML::showTooltip('Default Seller', '<strong>This is a required field.</strong><br />
This is to ensure that a <strong>seller</strong> is specified for each listing in your upload. The <strong>seller</strong>
selected here will only be used if you do not have a <strong>seller column</strong> specified in your uploaded file, or
if there are any listings in your file that did not include a <strong>seller</strong>.') . "</label>
          <div class='col-md-6 col-sm-6 col-xs-12'>
          $default_seller_input
          </div>
        </div>

		<div class='form-group'>
		<label class='control-label col-md-5 col-sm-5 col-xs-12'></label>
		  <div class='col-md-7 col-sm-7 col-xs-12'>
			<input type='checkbox' name='checkUserLimits' value='1' " . (($reg->_savedUserCheck == 1) ? "checked='checked'" : "") . " />&nbsp;
			Check User Limits
		  </div>
		</div>

        <div class='form-group'>
        <label class='control-label col-md-5 col-sm-5 col-xs-12'>Set Default Field Values: </label>
          <div class='col-md-6 col-sm-6 col-xs-12'>
				<div id='set_default_field_values'>";
                $savedDefaults = $reg->_savedDefaults;
                $savedData = $savedDefaults['data'];
                $defaultColumns = array_merge($savedDefaults['column'], $this->session->getArray("bulkDefaultColumn"));

        foreach ($defaultColumns as $key => $colVal) {
            if (!$colVal) {
                //no actual data to show here, just garbage from merge
                continue;
            }
            $defaultValue = $this->session->get("bulkDefaultData", $key) ? $this->session->get("bulkDefaultData", $key) : $savedData[$key];

            $body .= "<br /><span class='selectBoxHeader'>$key</span>";
            $body .= "<select id='bulkDefaultColumn$key' name='bulkDefaultColumn[$key]'>";
            $body .= "<option value='null'>Select a field</option>";
            foreach ($this->bottom_dropdown as $catKey => $catValue) {
                foreach ($catValue as $key => $value) {
                    $body .= "<option id='$catKey' value='" . $value[1] . "' " . (($value[1] == $colVal) ? "selected='selected'" : "") . ">" . $value[0] . "</option>";
                }
            }
            $body .= "</select>";

            $body .= "<br /><textarea name=bulkDefaultData[$key]>" . $defaultValue . "</textarea>";
        }
        if (count($defaultColumns) > 0) {
            //let the "add new field" script know about fields we've already added
            $body .= "<script type='text/javascript'>defaultDataFieldsCount = " . count($defaultColumns) . "</script>";
        }
                $body .= "
				</div>
				<input value='New Default Field' type=button onclick=\"createDefaultFieldRow('set_default_field_values');\">
				<input value='Clear Default Fields' type='button' " . ((count($defaultColumns) > 0) ? "" : "style='display: none;'") . " id='default_clear_btn' onclick=\"removeDefaultFields();\" />
          </div>
        </div>

		<div class='form-group'>
		<label class='control-label col-md-5 col-sm-5 col-xs-12'></label>
		  <div class='col-md-7 col-sm-7 col-xs-12'>
			<input type='checkbox' name='useDefaultUserData' value='1' " . (($reg->_savedUseDefaults == 1) ? "checked='checked'" : "") . " />&nbsp;
			" . geoHTML::showTooltip('Use Default User Data', 'If checked, this will populate the "user data" fields of a listing according
				 to that listing\'s seller\'s registration data. Note that even still, those are treated as "defaults" and will be overwritten by any data directly present in the upload file') . "&nbsp;Use Default User Data
		  </div>
		</div>
		";

        $body .= "

        <div class='form-group'>
        <label class='control-label col-md-5 col-sm-5 col-xs-12'>Combine Fields to Create the Title: </label>
          <div class='col-md-6 col-sm-6 col-xs-12'>
			<div id='customTitleTable'>";
            $defaultTitle = array_merge($reg->_savedTitle, $this->session->getArray("bulkCustomTitle"));
        foreach ($defaultTitle as $key => $colVal) {
            if (!$colVal || $colVal == 'null') {
                //no actual data to show here, just garbage from merge
                //yes, that's a string that says 'null' -- I'm not really sure why, but it's used here sometimes
                continue;
            }

            $body .= "<br /><span class='selectBoxHeader'>$key</span>";
            $body .= "<select id='bulkCustomTitle$key' name='bulkCustomTitle[$key]'>";
            $body .= "<option value='null'>Select a field</option>";
            foreach ($this->bottom_dropdown as $catKey => $catValue) {
                foreach ($catValue as $key => $value) {
                    $body .= "<option value='" . $value[1] . "' " . (($value[1] == $colVal) ? "selected='selected'" : "") . ">" . $value[0] . "</option>";
                }
            }
            $body .= "</select>";
        }
        if (count($defaultTitle) > 0) {
            //let the "add new field" script know about fields we've already added
            $body .= "<script type='text/javascript'>defaultTitleFieldsCount = " . count($defaultTitle) . "</script>";
        }
            $body .= "
			</div>
			<input value='Add a Field' type=button onClick=\"createCustomTitleRow('customTitleTable');\">
			<input value='Clear Title Fields' type='button' " . ((count($defaultTitle) > 0) ? "" : "style='display: none;'") . " id='title_clear_btn' onclick=\"removeTitleFields();\" />
          </div>
        </div>

		";

        $body .= $this->getDurationBlock();
        $body .= $this->getUpgradeBlock();

        if ($revolving = $this->session->config('revolving_label')) {
            $body .= "<div class='header-color-primary-mute'>Revolving Inventory Key Column </div>
					<div class='row_color2'>";

            $t = geoHTML::showTooltip('Unique Row Identifier Column', 'Select a column of your upload data whose value uniquely identifies each row. This should be something like a VIN number that is different for every item in your inventory');
            $body .= "
			<div class='form-group'>";
            $body .= "<label class='control-label col-md-5 col-sm-5 col-xs-12'>Unique Row Identifier Column: $t </label>";
              $body .= "<div class='col-md-6 col-sm-6 col-xs-12'>
			  <select name='ri[unique_id]' class='form-control col-md-7 col-xs-12'>";
                $body .= "<option value='null'>Select a field</option>";
            foreach ($this->bottom_dropdown as $catKey => $catValue) {
                foreach ($catValue as $key => $value) {
                    $body .= "<option id='$catKey' value='" . $value[1] . "'>" . $value[0] . "</option>";
                }
            }
            $body .= "</select>
			  </div>
			</div>
			";
        } elseif (!$revolving) {
            $body .= '<div class="header-color-primary-mute">Multi-part Upload</div>

						<div class="form-group">
						<label class="control-label col-md-5 col-sm-5 col-xs-12">Multi-part Upload: <br /><span class="small_font">(leave blank to upload all at once)</span> </label>
						  <div class="col-md-6 col-sm-6 col-xs-12">
						  <span class="vertical-form-fix">Upload at most <input type="text" name="multipart[count]" size="2"> listings every <input type="text" name="multipart[gap]" size="2"> hours.</span>
						  </div>
						</div>
						';
        }

        $body .= "
		</div>
		</fieldset>";

        return $body;
    }


    private function RevolvingInventoryForm()
    {
        $html = '<fieldset><legend>Revolving Inventory Upload</legend>';
        $html .= '<div class="x_content"><div class="page_note"><strong>Important:</strong> Be absolutely sure you have read the documentation and understand what this feature does before using it!</div>';

        $html .= '
				<div class="form-group switcher">
				<label class="control-label col-md-4 col-sm-4 col-xs-12"></label>
				  <div class="col-md-7 col-sm-7 col-xs-12">
					<input type="checkbox" value="1" name="ri[use]" onclick="if(this.checked)$(\'ri_hide\').show(); else $(\'ri_hide\').hide();" />&nbsp;
					Save this upload as Revolving Inventory?
				  </div>
				</div>
		';

        $html .= '<div id="ri_hide" style="display: none;">';

        $html .= '
				<div class="form-group">
				<label class="control-label col-md-5 col-sm-5 col-xs-12">Specify a label for this upload: </label>
				  <div class="col-md-6 col-sm-6 col-xs-12">
				  <input type="text" name="ri[label]" maxlength="255" class="form-control col-md-7 col-xs-12"/>
				  </div>
				</div>
		';

        $html .= '</div></div></fieldset>';
        return $html;
    }

    private function initializeRevolvingInventory($filename)
    {
        $data = $_POST['data'];
        $ri = $_POST['ri'];

        //if this function has already been run for this upload, this value will be set
        //if it is, allow changing the labels/filenames, since this is a revision of the current upload session
        $editing = ($this->session->config('revolving_label')) ? true : false;

        $label = $ri['label'];
        if (preg_match('/[^a-zA-Z0-9]+/', $label)) {
            //found non-alphanumeric characters
            geoAdmin::m('Revolving Inventory Label invalid: please use only latin, alphanumeric characters', geoAdmin::ERROR);
            $this->critical_error = true;
            return false;
        }

        $db = DataAccess::getInstance();
        require_once(ADDONS_DIR . 'bulk_uploader/registry.php');
        $reg = new geoBulkUploaderRegistry();

        //new logic for here: check to see if label we're trying to use is in the revolving db table.
        //if not, clear any data associated with it, because it was started but never completed

        $sql = "SELECT `label` FROM `geodesic_addon_bulk_uploader_revolving` WHERE `label` = ?";
        $labelExists = ($db->GetOne($sql, array($label)) === $label) ? true : false;

        if ($labelExists) {
            //this label is already in use on an active revolving session -- throw an error
            geoAdmin::m('Revolving Inventory Label invalid: already in use.', geoAdmin::ERROR);
            $this->critical_error = true;
            return false;
        }

        $registryData = $reg->$label;
        if ($registryData) {
            //there is a pre-existing entry for this label in the registry, but since it wasn't in the revolving table, the input was never finished
            //clear the registry data so that it may be entered anew
            $reg->$label = false;
        }

        //delete any previous map entries in the db associated with this label
        $db->Execute("DELETE FROM geodesic_addon_bulk_uploader_revolving_map WHERE `revolving_id` = ?", array($label));

        //make a user-readable version of the upload file
        $uploadPath = ADDON_DIR . 'bulk_uploader' . DIRECTORY_SEPARATOR . $this->uploads_dir_name . DIRECTORY_SEPARATOR . 'revolving' . DIRECTORY_SEPARATOR . $label;
        if (!is_dir($uploadPath)) {
            mkdir($uploadPath, 0777, true);
        }
        $newName = $uploadPath . DIRECTORY_SEPARATOR . REVOLVING_FILENAME;
        if (file_exists($newName)) {
            //filename already exists, but like above, the input was never completed
            //delete the file and start over
            unlink($newName);
        }
        if (!copy($filename, $newName)) {
            geoAdmin::m('Failed to create Revolving Inventory source file. Ensure the uploads directory is CHMOD 777, or contact GeoSupport if this problem persists.', geoAdmin::ERROR);
            $this->critical_error = true;
            return false;
        }

        //try to chmod the new file
        chmod($newName, 0777);

        //save the interesting data
        $save['config'] = array(
            'skipfirstrow' => $data['skipfirstrow'],
            'compression' => $data['compression'],
            'delimiter' => $data['delimiter'],
            'encapsulation' => $data['encapsulation'],
            'category' => $data['category'],
            'multiple_categories' => $data['multiple_categories'],
            'fast_image_proc' => $data['fast_image_proc'],
            'base_image_path' => $data['base_image_path'],
            'type' => $data['type'],
            'csvdata' => $filename,
            'updatefile' => $newName
        );

        $reg->$label = $save;

        //let the rest of the bulk upload session know that we're setting up a revolving upload
        $this->session->config('revolving_label', $label);


        return true;
    }

    private function initializeRevolvingCron($label)
    {
        //add this to the database, set it to upload again in a week
        $db = DataAccess::getInstance();
        $next_run = geoUtil::time() + ($db->get_site_setting('bulk_revolve_period') * 86400);
        $sql = "INSERT INTO `geodesic_addon_bulk_uploader_revolving` (label, next_run) VALUES (?, ?)";
        $result = $db->Execute($sql, array($label, ($next_run)));
        return $result;
    }




    function StartUpTasks($minimal = false)
    {
        if (!function_exists('getimagesize') && !function_exists('curl_init')) {
            $message = "<span style='color: #FF0000;'>getimagesize() and cURL function are not enabled on this host: <br /> ask your hosting provider to enable one of this two! </span><br /><a href=\"index.php?page=home\">Admin Home</a>";
            return geoAdmin::display_page($message);
        }

        $this->addon_dir = dirname(__file__);
        $this->uploads = $this->addon_dir . DIRECTORY_SEPARATOR . $this->uploads_dir_name . DIRECTORY_SEPARATOR;

        if ($minimal) {
            if (!is_writable($this->uploads)) {
                geoCron::getInstance()->log("Error:  cannot write to folder {$this->uploads}, cannot continue!", __line__ . ' - ' . __file__);
                return false;
            }
            return true;
        }

        $this->initAdminMessages();
        $this->propagateDropdownArrays();

        if (!is_writable($this->uploads)) {
            die(geoAdmin::display_page("<div class='page_note_error'>
				Cannot write to folder: <span style='font-weight:bold'>" . $this->uploads . "</span>
				<div style='font-weight:bold;color: #FF0000;'>Don't forget to CHMOD " . $this->uploads_dir_name . " directory to 777!<div>
			</div>
			"));
        }
        $indexfile = $this->get_site_setting('classifieds_file_name');
        $site =  str_replace($indexfile, '', $this->get_site_setting('classifieds_url'));
        $this->addon_url = $this->url = $site . 'addons/bulk_uploader';

        //getting reports of users of non-Win O/S's having their uploaded data strung onto one line
        //setting auto_detect_line_endings makes the upload marginally slower
        //but should also make it platform-independant
        ini_set("auto_detect_line_endings", "1");

        //determine whether this is a revolving inventory upload
        //if it is, save all options to registry as well as session
        $revolving = $this->session->config('revolving_label');

        require_once(ADDONS_DIR . 'bulk_uploader/registry.php');
        $reg = new geoBulkUploaderRegistry();
        if ($revolving || (isset($_POST['ri']) && $_POST['ri']['use'] == 1)) {
            //it's possible to run this without the revolving inventory being saved in the session yet, but to still want the image option
            $save = $revolving ? $reg->get($revolving) : null;

            //add image update column
            $this->bottom_dropdown['images'][] = array("Images Updated (1 - yes, 0 - no)", "image_update");
        }

        if (isset($_REQUEST["bulkColumn"])) {
            $this->session->free("bulkColumn");
            foreach ($_POST["bulkColumn"] as $key => $value) {
                $this->session->set("bulkColumn", $value, $key);
                $columns[$key] = $value;
            }
            if ($revolving) {
                //save column setup to revolving data
                $save['columns'] = $columns;
            }
            //also save column setup to its own place in the registry, regardless of $revolving
            //this will be used to load up the column arrangement the next time the bulk uploader is used
            //preface the registry key with a underscore so it doesn't conflict with revolver data
            $reg->set('_savedColumns', $columns);
        }

        //save default field values
        //defaultColumn is the column to use
        //defaultData is the actual data to put in the column
        if (isset($_REQUEST["bulkDefaultColumn"]) && isset($_REQUEST["bulkDefaultData"])) {
            $this->session->free("bulkDefaultColumn");
            foreach ($_REQUEST["bulkDefaultColumn"] as $key => $value) {
                $this->session->set("bulkDefaultColumn", $value, $key);
                if ($revolving) {
                    $save['defaultColumns'][$key] = $value;
                }
                $defaults['column'][$key] = $value;
            }

            $this->session->free("bulkDefaultData");
            foreach ($_REQUEST["bulkDefaultData"] as $key => $value) {
                $this->session->set("bulkDefaultData", $value, $key);
                if ($revolving) {
                    $save['defaultData'][$key] = $value;
                }
                $defaults['data'][$key] = $value;
            }

            //as above, save to registry for use in "restoring" step 3 on a subsequent bulk uploader run
            $reg->set('_savedDefaults', $defaults);
        }
        if (isset($_REQUEST["bulkCustomTitle"])) {
            $this->session->free("bulkCustomTitle");
            foreach ($_REQUEST["bulkCustomTitle"] as $key => $value) {
                $this->session->set("bulkCustomTitle", $value, $key);
                if ($revolving) {
                    $save['customTitle'][$key] = $value;
                }
                $title[$key] = $value;
            }
            //as above, save to registry for use in "restoring" step 3 on a subsequent bulk uploader run
            $reg->_savedTitle = $title;
        }
        if (isset($_REQUEST["bulkDuration"]) && $_REQUEST["bulkDuration"]["method"] === "0") {
            $startTime = geoUtil::time();
            $endTime = ($_REQUEST["bulkDuration"]["fixed"]) ? geoUtil::time() + $_REQUEST["bulkDuration"]["fixed"] : 0;
            $adjustTimes = $_REQUEST['bulkDuration']['adjustTimes'];
            $this->session->set("startTime", $startTime);
            $this->session->set("endTime", $endTime);
            $this->session->config("duration_method", 1);
            if ($revolving) {
                $save['duration'] = array(
                    'method' => 1,
                    'start' => $startTime,
                    'end' => $endTime,
                    'adjustTimes' => $adjustTimes
                );
            }
            $reg->_savedDuration = array('method' => 0, 'duration' => $_REQUEST["bulkDuration"]["fixed"], 'adjustTimes' => $adjustTimes);
        } elseif (isset($_REQUEST["bulkDuration"]) && $_REQUEST["bulkDuration"]["method"] === "1") {
            $start = $_REQUEST["bulkDuration"]["start"];
            $end = $_REQUEST["bulkDuration"]["end"];
            $adjustTimes = $_REQUEST['bulkDuration']['adjustTimes'];
            $startTime = mktime($start["hour"], $start["minute"], 0, $start["month"], $start["day"], $start["year"]);
            $endTime = mktime($end["hour"], $end["minute"], 0, $end["month"], $end["day"], $end["year"]);
            $this->session->config("duration_method", 2);
            $this->session->set("startTime", $startTime);
            $this->session->set("endTime", $endTime);
            if ($revolving) {
                $save['duration'] = array(
                    'method' => 2,
                    'start' => $startTime,
                    'end' => $endTime,
                    'adjustTimes' => $adjustTimes
                );
            }
            $reg->_savedDuration = array('method' => 1, 'start' => $start, 'end' => $end, 'adjustTimes' => $adjustTimes);
        }


        if (isset($_REQUEST["bulkUpgrades"])) {
            foreach ($_REQUEST["bulkUpgrades"] as $key => $value) {
                $this->session->set($key, $value);
                if ($revolving) {
                    $save['upgrades'][$key] = $value;
                }
                $upgrades[$key] = $value;
            }
            $reg->_savedUpgrades = $upgrades;
        }

        if (isset($_REQUEST['ri']['unique_id'])) {
            $save['unique_id'] = $_REQUEST['ri']['unique_id'];
        }

        if (isset($_REQUEST['seller'])) {
            $this->session->set('default_seller', $_REQUEST['seller']);
            if ($revolving) {
                $save['seller'] = $_REQUEST['seller'];
            }
            $reg->_savedSeller = $_REQUEST['seller'];
        }

        if (isset($_REQUEST['checkUserLimits'])) {
            $this->session->set('checkUserLimits', $_REQUEST['checkUserLimits']);
            if ($revolving) {
                $save['checkUserLimits'] = $_REQUEST['checkUserLimits'];
            }
            $reg->_savedUserCheck = $_REQUEST['checkUserLimits'] ? 1 : 0;
        }
        if (isset($_REQUEST['useDefaultUserData'])) {
            $this->session->set('useDefaultUserData', $_REQUEST['useDefaultUserData']);
            if ($revolving) {
                $save['useDefaultUserData'] = $_REQUEST['useDefaultUserData'];
            }
            $reg->_savedUseDefaults = $_REQUEST['useDefaultUserData'] ? 1 : 0;
        }

        if ($revolving) {
            //save values to registry as settings for this revolving set
            $reg->set($revolving, $save);
        }

        if (isset($_GET['p'])) {
            $this->session->config('page', $_GET['p']);
        }
        return true;
    }

    function addHeader($add)
    {
        if ($add) {
            geoView::getInstance()->addTop($add);
        }
    }

    function addJs($page)
    {

        switch ($page) {
            case 2:
                $this->addHeader("
					<script type='text/javascript'>
					function swapBottomSelect(selectElement, selectNumericId) {
						bottomDropDown = $('bottomSelect'+selectNumericId)
						if(bottomDropDown.disabled){bottomDropDown.disabled=false;}
						if(selectElement.selectedIndex==0) {
							bottomDropDown.options[0].style.display='';
							bottomDropDown.selectedIndex=0;
							bottomDropDown.disabled=true;
							return;
						}
						selectedNewIndex=false;
						if(selectElement.selectedIndex==1) {
							bottomDropDown.options[0].style.display='none';
							for(i=0;i<bottomDropDown.options.length;i++) {
								if(!selectedNewIndex&&i!=0){bottomDropDown.selectedIndex=i;selectedNewIndex=true;}
								bottomDropDown.options[i].style.display='';
							}
							return
						}
						for(i=0;i<bottomDropDown.options.length;i++) {
							if(bottomDropDown.options[i].id!=selectElement.options[selectElement.selectedIndex].value){
								bottomDropDown.options[i].style.display='none';
							}else{
								if(!selectedNewIndex){
									bottomDropDown.selectedIndex=i;
									selectedNewIndex=true;
								}
								bottomDropDown.options[i].style.display='';
							}
						}
					}

					function alterBottomSelect(selectElement,selectId) {
						k = 0;
						disabled = false;
						while(otherSelectElements = $('bottomSelect'+k)) {
							if(k != selectId) {
								if(selectElement.selectedIndex!=0) {
									if(selectElement.selectedIndex==otherSelectElements.selectedIndex){
										alert('This field is already being used by column number: '+k);
									}
								}
							}
							k++;
						}
					}
					</script>
					");

                break;
            case 3:
                $this->addHeader("
				<script type='text/javascript'>

				defaultDataFieldsCount = 0;
				defaultTitleFieldsCount = 0;
				function createDefaultFieldRow(divID) {
					container = $(divID);
					var numericId = defaultDataFieldsCount++;
					container.insert(new Element('br'));
					" . $this->getJavascriptDropDownBlock('bulkDefaultColumn') . "
					container.insert(new Element('br'));
					container.insert(new Element('textarea', {
						'name': 'bulkDefaultData[' + numericId + ']',
						'id': 'bulkDefaultData' + numericId
					}));

					$('default_clear_btn').show();
				}

				function removeDefaultFields() {
					$('set_default_field_values').update('');
					$('default_clear_btn').hide();
					defaultDataFieldsCount = 0;
				}

				function removeTitleFields() {
					$('customTitleTable').update('');
					$('title_clear_btn').hide();
					defaultTitleFieldsCount = 0;
				}

				function createCustomTitleRow(divID) {
					var numericId = defaultTitleFieldsCount++;
					container = $(divID);
					container.insert(new Element('br'));
					" . $this->getJavascriptDropDownBlock('bulkCustomTitle') . "
					container.insert(new Element('br'));

					$('title_clear_btn').show();
				}

				function validateSettings() {
					if(!$('seller').value) {
						alert('Please specify a default seller ID');
						$('seller').focus();
						return false;
					}
					return true;
				}

				</script>
				");

                break;

            default:
        }
    }



    /**
     * Removes log entries whose listings have expired naturally.
     * Called when Manage Uploads page displays
     *
     * @return void
     */
    function cleanLog()
    {
        //get all expired listing IDs
        $sql = "SELECT log.`listing_id` as listing_id, log.`upload_id` as upload_id FROM `geodesic_addon_bulk_uploader_listings` as log, `geodesic_classifieds` as class WHERE class.id=log.listing_id AND class.live = 0 AND class.ends < ?";
        $expired = $this->db->GetAll($sql, array(geoUtil::time()));

        //also look for any listing IDs that are in the bulk uploader log but not in the classifieds table at all (manually removed?)
        $sql = "SELECT `listing_id`, `upload_id` FROM `geodesic_addon_bulk_uploader_listings` as addon WHERE NOT EXISTS (SELECT `id` FROM `geodesic_classifieds` as main WHERE addon.listing_id = main.id)";
        $removed = $this->db->GetAll($sql);
        foreach ($removed as $rem) {
            $expired[] = $rem;
        }

        //delete from listings table
        $logsModified = array();
        $remove = $this->db->Prepare("DELETE FROM `geodesic_addon_bulk_uploader_listings` WHERE `listing_id` = ?");
        foreach ($expired as $exp) {
            $this->db->Execute($remove, array($exp['listing_id']));
            $logsModified[$exp['upload_id']]++;
        }
        //if a log is now completely empty, delete it, too
        $modQuery = $this->db->Prepare("SELECT * FROM `geodesic_addon_bulk_uploader_listings` WHERE `upload_id` = ?");
        foreach ($logsModified as $logId => $notImportant) {
            $remaining = $this->db->Execute($modQuery, array($logId));
            if ($remaining->RecordCount() == 0) {
                $this->db->Execute("DELETE FROM `geodesic_addon_bulk_uploader_listings` WHERE `upload_id` = ?", array($logId));
                $this->db->Execute("DELETE FROM `geodesic_addon_bulk_uploader_log` WHERE `log_id` = ?", array($logId));
            }
        }

        //also look for logs that were blank to begin with, and remove those
        $sql = "DELETE FROM `geodesic_addon_bulk_uploader_log` WHERE log_id NOT IN (SELECT upload_id FROM `geodesic_addon_bulk_uploader_listings`)";
        $this->db->Execute($sql);
    }

    /**
     * builds html block for display
     *
     * @return string
     */
    function getCSVColumnBlock($highlight = false)
    {
        $skip = $this->session->config('skipfirstrow');
        $diplay_total = $this->getCSVLength($this->session->config('fileName'));
        $len  = $preview_number = $this->session->config('previewLength');

        if ($skip) {
            $diplay_total--;
        }
        $data = $this->getCSVData($preview_number);
        if (!count($data)) {
            $msg = "The uploaded file contains no data. Please check the formatting of your data, and make sure you are specifying the correct delimitating and encapsulating characters.";
            return $msg;
        }

        $body .= "<table style='position:relative;width:700px'>
				  	<tr class='form_row'>
				";

        //get the column arrangement from the last time the bulk uploader was run
        require_once(ADDONS_DIR . 'bulk_uploader/registry.php');
        $reg = new geoBulkUploaderRegistry();
        $savedColumns = $reg->get('_savedColumns');

        for ($i = 0; $i < count($data[0]); $i++) {
            if ($savedColumns[$i]) {
                //default value from last run
                $default = $savedColumns[$i];
            } elseif ($this->session->get('bulkColumn', $i)) {
                //default value from this run, saved in session (probably returning to this page from later in the process)
                $default = $this->session->get('bulkColumn', $i);
            } else {
                //no default for this field number
                $default = '';
            }

            $body .= "<td style='margin-right:2px;padding:0px 10px;border-left:2px solid #999;border-bottom:2px solid #353535;'>";
            $body .= $this->getDropDownBlock($i, "bulkColumn", $default, 0, $highlight);
            $body .= "</td>";
        }
        $body .= "</tr>";
        $i = 0;
        foreach ($data as $csvRow) {
            $i++;
            $body .= "<tr class='form_row'>";
            foreach ($csvRow as $csvColumn) {
                $row = ($i % 2) ? 'even_row' : 'odd_row';
                if (strlen($csvColumn) > 60 && !($i == 1 && $skip)) {
                    $row = 'mix_row';
                    $title = "<span class='overtip'> " . $csvColumn . " </span></span>";
                    $csvColumn = substr($csvColumn, 0, 30);
                    $span = "<span class='ToolText' onMouseOver=\"javascript:this.className='ToolTextHover'\" onMouseOut=\"javascript:this.className='ToolText'\">";
                }
                if ($i === 1 && $skip) {
                    $row = 'title_row';
                }

                $body .= "
				<td class='$row'>
				" . $span . $csvColumn . $title . "
				</td>
				";

                $title = $row = $span = '';
            }
            $body .= "</tr>";
        }
        $body .= "</table>";

        return $body;
    }

    /**
     * adds optional fields and more image fields
     *
     * @return void
     */
    function addDynamicDropDownValues()
    {
        for ($i = 1; $i <= 20; $i++) {
            array_push($this->bottom_dropdown["images"], array("Image URL " . $i, "image_" . $i));
            array_push($this->bottom_dropdown["images"], array("Image Caption " . $i, "image_caption_" . $i));
        }
        for ($i = 1; $i <= 20; $i++) {
            $optional_field_name = $this->configuration_data['optional_field_' . $i . '_name'];
            array_push($this->bottom_dropdown["optional"], array($optional_field_name, "optional_field_" . $i));
        }

        //add in leveled fields
        $leveled = geoLeveledField::getInstance();
        $leveled_ids = $leveled->getLeveledFieldIds();
        foreach ($leveled_ids as $level_id) {
            $this->bottom_dropdown["leveled"][] = array($leveled->getLeveledFieldLabel($level_id), "leveled_" . $level_id);
        }


        if (!$this->session->config('multiple_categories') && $categoryId = $this->session->config("category")) {
            $this->top_dropdown["category"] = "Category Questions";
            $this->bottom_dropdown["category"] = array();

            //get questions for this category, recursively
            $this->addCategoryDropDownValues($categoryId);

            if (count($this->bottom_dropdown["category"]) == 0) {
                //there are no category specific questions for this category
                //remove the Category Questions selection from the top dropdown
                array_pop($this->top_dropdown);
            } else {
                $this->bottom_dropdown['category'][] = array('Checkbox List', 'checkbox_list');
            }
        } else {
            //using "multiple categories" mode -- show a choice for 'category' instead of category questions
            $this->top_dropdown['category_select'] = "Category";
            $this->bottom_dropdown['category_select'][] = array('Category ID# or Name','category');

            $this->top_dropdown['category'] = 'Category Checkboxes';
            $this->bottom_dropdown['category'][] = array('Checkbox List', 'checkbox_list');
        }

        if ($this->session->config("type") == "classified" && geoMaster::is('classifieds')) {
            $this->top_dropdown["classified"] = "Classified Fields";

            $this->bottom_dropdown["classified"] = array();
            array_push($this->bottom_dropdown["classified"], array("Price", "price"));
            array_push($this->bottom_dropdown["classified"], array("Sold (1 - yes, 0 - no)", "sold_displayed"));
        } elseif ($this->session->config("type") == "auction" && geoMaster::is('auctions')) {
            $this->top_dropdown["auction"] = "Auction Fields";

            $this->bottom_dropdown["auction"] = array();
            array_push($this->bottom_dropdown["auction"], array("Bid Start Date", "start_time"));
            array_push($this->bottom_dropdown["auction"], array("Buy Now", "buy_now"));
            array_push($this->bottom_dropdown["auction"], array("Final Fee", "final_fee"));
            array_push($this->bottom_dropdown["auction"], array("Reserve Price", "reserve_price"));
            array_push($this->bottom_dropdown["auction"], array("Starting Bid", "starting_bid"));
            array_push($this->bottom_dropdown["auction"], array("Minimum Bid", "minimum_bid"));
            array_push($this->bottom_dropdown["auction"], array("Quantity of Items", "quantity"));
            array_push($this->bottom_dropdown["auction"], array('Price Applies To ["lot"|"item"]', "price_applies"));
            array_push($this->bottom_dropdown["auction"], array("Buy Now Only (1 - yes, 0 - no)", "buy_now_only"));
            array_push($this->bottom_dropdown["auction"], array("Use Seller Buyer (1 - yes, 0 - no)", "use_seller_buyer"));

            $this->top_dropdown['cost_options'] = "Cost Option Groups";
            for ($i = 1; $i <= 20; $i++) {
                $this->bottom_dropdown['cost_options'][] = array('Option Group ' . $i . ': Group Name', 'co_' . $i . '_groupname');
                $this->bottom_dropdown['cost_options'][] = array('Option Group ' . $i . ': Option Names', 'co_' . $i . '_optnames');
                $this->bottom_dropdown['cost_options'][] = array('Option Group ' . $i . ': Option Costs', 'co_' . $i . '_optcosts');
                $this->bottom_dropdown['cost_options'][] = array('Option Group ' . $i . ': Option File Slots', 'co_' . $i . '_optfiles');
                $this->bottom_dropdown['cost_options'][] = array('Option Group ' . $i . ': Option INDIVIDUAL Quantities', 'co_' . $i . '_optquantities');
            }
            $this->bottom_dropdown['cost_options'][] = array('COMBINED Quantity Sets', 'co_combinedquantities');
        }

        $addons = array();
        $geoAddon = geoAddon::getInstance();

        if ($geoAddon->isEnabled('storefront')) {
            $addons[] = array('Storefront: Category ID#', 'storefront_category');
        }

        if ($geoAddon->isEnabled('twitter_feed')) {
            $addons[] = array('Twitter Feed: Username', 'twitter_username');
        }



        if (count($addons) > 0) {
            $this->top_dropdown['addons'] = "Addons";
            $this->bottom_dropdown['addons'] = $addons;
        }
    }

    function addCategoryDropDownValues($categoryId)
    {
        $this->sql_query = "SELECT question_id, name FROM " . $this->questions_table . " where category_id = " . $categoryId . " ORDER BY `display_order`";
        $result = $this->db->Execute($this->sql_query);
        if ($result->RecordCount() > 0) {
            while ($question = $result->FetchRow()) {
                array_push($this->bottom_dropdown["category"], array($question["name"], "categoryQuestion_" . $question["question_id"]));
            }
        }
        $this->sql_query = "select parent_id from " . $this->classified_categories_table . " where category_id = " . $categoryId;
        $result = $this->db->Execute($this->sql_query);
        if ($result->RecordCount() > 0) {
            $parent = $result->FetchRow();
            $this->addCategoryDropDownValues($parent["parent_id"]);
        }
    }

    /**
     * builds html block for
     * both top and bottom dropdowns
     *
     * @return string
     */
    function getDropDownBlock($id, $name, $selected = '', $jsInc = 0, $highlight = false)
    {
        $body = "<select onchange='javascript: swapBottomSelect(this,\"" . ($id + $jsInc) . "\");' " . ($highlight ? "class='highlight'" : "class='selector'") . " style='position:relative;width:auto;max-width:200px'>\n";
        $k = 0;
        foreach ($this->top_dropdown as $key => $value) {
            //if something is "$selected," be sure to set the top dropdown to "all fields" instead of the usual default of "not used"
            $body .= "\t<option value='$key' " . ((($selected !== '') && ($key == "all")) ? "selected" : "") . ">$value</option>\n";
            $k++;
        }
        $body .= "</select><br>\n";
        //bottom
        $body .= "<select class='selector' id=bottomSelect" . ($id + $jsInc) . " name='" . $name . "[" . $id . "]' " . ((($selected !== '')) ? "" : "disabled ") . "onChange='javascript: alterBottomSelect(this,\"" . ($id + $jsInc) . "\");' style='position:relative;width:auto;max-width:200px'>\n";
        $body .= "\t<option value='null'>Select above</option>\n";
        $k = 0;

        foreach ($this->bottom_dropdown as $catKey => $catValue) {
            foreach ($catValue as $key => $value) {
                $body .= "\t<option id='$catKey' value='" . $value[1] . "' " . (($value[1] == $selected) ? "selected" : "") . ">" . $value[0] . "</option>\n";
            }
            $k++;
        }
        $body .= "</select>\n";
        $body .= "<span class='selectBoxHeader'>$id</span>";

        return $body;
    }



    /**
     * builds html block for
     * choosing the listing's duration
     *
     * @return string
     */
    function getDurationBlock()
    {
        //get settings saved from last time
        require_once(ADDONS_DIR . 'bulk_uploader/registry.php');
        $reg = new geoBulkUploaderRegistry();
        $savedDuration = $reg->_savedDuration;
        $method = $savedDuration['method'];
        $isRevolving = $this->session->config('revolving_label') ? true : false;
        if ($savedDuration) {
            if ($method == 0) {
                $duration = $savedDuration['duration'];
            } else {
                $start = $savedDuration['start'];
                $end = $savedDuration['end'];
            }
        }

        if (!$start && !$end) {
            //nothing saved -- set default start/end to now/tomorrow
            $today = date("n|d|Y|G|i", geoUtil::time());
            $today = explode('|', $today);
            $start = array(
                'month' => $today[0],
                'day' => $today[1],
                'year' => $today[2],
                'hour' => $today[3],
                'minute' => $today[4],
            );
            $tomorrow = date("n|d|Y|G|i", geoUtil::time() + 86400);
            $tomorrow = explode('|', $tomorrow);
            $end = array(
                'month' => $tomorrow[0],
                'day' => $tomorrow[1],
                'year' => $tomorrow[2],
                'hour' => $tomorrow[3],
                'minute' => $tomorrow[4],
            );
        }

        $body .= "

			<div class='form-group'>
			<label class='control-label col-md-5 col-sm-5 col-xs-12'>
					Start Now:&nbsp;<input id='immediate' type='radio' name=bulkDuration[method] value='0' checked onclick=\"hideFixedDuration(); \"> </label>
			  <div class='col-md-6 col-sm-6 col-xs-12 input-group'>
			  <div class='input-group-addon'>End in:</div>
				<select name=bulkDuration[fixed] class='form-control col-md-7 col-xs-12 input-group'>";
                        $body .= "<option value='0' " . ($duration == 0 ? 'selected="selected"' : '') . ">Unlimited</option>";
        for ($i = 1; $i <= 365; $i++) {
            $body .= "<option value='" . (86400 * $i) . "' " . ($duration == (86400 * $i) ? 'selected="selected"' : '') . ">";
            $body .= "$i day" . ($i != 1 ? 's' : '') . "</option>";
        }
                $body .= "</select>";

        if ($isRevolving) {
            $body .= '<input type="checkbox" name="bulkDuration[adjustTimes]" value="1" ' . ($savedDuration['adjustTimes'] == 1 ? 'checked="checked"' : '') . '/>
					Start Date Does not Change on Refresh ' . (geoHTML::showTooltip('Start date does not change on refresh', 'If this option is checked, all listings involved in the Revolving Inventory process will retain their own initial placement times, rather than being updated with each refresh.'));
        }
              $body .= "</div>
			</div>

			";

        $body .= "

			<div class='form-group'>
			<label class='control-label col-md-5 col-sm-5 col-xs-12'>
					Fixed Duration:&nbsp;<input id='fixed' type='radio' name=bulkDuration[method] value='1' onclick=\"showFixedDuration()\"> </label>
			  <div class='col-md-6 col-sm-6 col-xs-12 input-group' id='fixed_duration_div' style='display: none;'>";

                $body .= 'Start Time<br />';
                    $body .= "<select name=bulkDuration[start][month]>\n";
                    $body .= "<option value='" . date('n') . "'>month</option>";
        for ($i = 1; $i <= 12; $i++) {
            $body .= "<option value='" . $i . "'" . (($start['month'] == $i) ? " selected='selected'" : '') . ">$i</option>";
        }
                    $body .= "</select>";
                    $body .= "/<select name=bulkDuration[start][day]>\n";
                    $body .= "<option value='" . date('j') . "'>day</option>";
        for ($i = 1; $i <= 31; $i++) {
            $body .= "<option value='" . $i . "'" . (($start['day'] == $i) ? " selected='selected'" : '') . ">$i</option>";
        }
                    $body .= "</select>";
                    $body .= "/<select name=bulkDuration[start][year]>\n";
                    $body .= "<option value='" . date('y') . "'>year</option>";
        for ($i = date("Y"); $i <= date("Y") + 10; $i++) {
            $body .= "<option value='" . $i . "'" . (($start['year'] == $i) ? " selected='selected'" : '') . ">$i</option>";
        }
                    $body .= "</select>";
                    $body .= ":<select name=bulkDuration[start][hour]>\n";
                    $body .= "<option value='" . date('n') . "'>hour</option>";
        for ($i = 0; $i <= 23; $i++) {
            $body .= "<option value='" . $i . "'" . (($start['hour'] == $i) ? " selected='selected'" : '') . ">$i</option>";
        }
                    $body .= "</select>";
                    $body .= ":<select name=bulkDuration[start][minute]>\n";
                    $body .= "<option value='" . date('i') . "'>minute</option>";
        for ($i = 0; $i <= 59; $i++) {
            $body .= "<option value='" . $i . "'" . (($start['minute'] == $i) ? " selected='selected'" : '') . ">$i</option>";
        }
                    $body .= "</select>
					<br />
					";

                    $body .= '<br />End Time<br />';
                    $body .= "<select name=bulkDuration[end][month]>\n";
                    $body .= "<option value='" . date('n') . "'>month</option>";
        for ($i = 1; $i <= 12; $i++) {
            $body .= "<option value='" . $i . "'" . (($end['month'] == $i) ? " selected='selected'" : '') . ">$i</option>";
        }
                    $body .= "</select>";
                    $body .= "/<select name=bulkDuration[end][day]>\n";
                    $body .= "<option value='" . date('j') . "'>day</option>";
        for ($i = 1; $i <= 31; $i++) {
            $body .= "<option value='" . $i . "'" . (($end['day'] == $i) ? " selected='selected'" : '') . ">$i</option>";
        }
                    $body .= "</select>";
                    $body .= "/<select name=bulkDuration[end][year]>\n";
                    $body .= "<option value='" . date('y') . "'>year</option>";
        for ($i = date("Y"); $i <= date("Y") + 10; $i++) {
            $body .= "<option value='" . $i . "'" . (($end['year'] == $i) ? " selected='selected'" : '') . ">$i</option>";
        }
                    $body .= "</select>";
                    $body .= ":<select name=bulkDuration[end][hour]>\n";
                    $body .= "<option value='" . date('n') . "'>hour</option>";
        for ($i = 0; $i <= 23; $i++) {
            $body .= "<option value='" . $i . "'" . (($end['hour'] == $i) ? " selected='selected'" : '') . ">$i</option>";
        }
                    $body .= "</select>";
                    $body .= ":<select name=bulkDuration[end][minute]>\n";
                    $body .= "<option value='" . date('i') . "'>minute</option>";
        for ($i = 0; $i <= 59; $i++) {
            $body .= "<option value='" . $i . "'" . (($end['minute'] == $i) ? " selected='selected'" : '') . ">$i</option>\n";
        }
                    $body .= "
					</select>";

                  $body .= "</div>
			</div>
				";


            $this->addHeader("
				<script type='text/javascript'>
				Event.observe(window,'load',function() {
					" . ($method == 1 ? "showFixedDuration(); $('fixed').checked=true;" : "hideFixedDuration(); $('immediate').checked = true;") . "
				});

				function showFixedDuration()
				{
					$('fixed_duration_div').show();
					$('start_now_div').hide();
				}

				function hideFixedDuration()
				{
					$('fixed_duration_div').hide();
					$('start_now_div').show();
				}

				</script>
				");

        return $body;
    }

    /**
     * builds html block for
     * selecting a category
     *
     * @return string
     */
    function getUpgradeBlock()
    {
        $body = "<div class='header-color-primary-mute'>Listing Upgrades " . geoHTML::showTooltip('Listing Upgrades', 'The choices you select below will apply to all listings in this upload.') . "</div>";

        //get the state of upgrades from the last upload
        require_once(ADDONS_DIR . 'bulk_uploader/registry.php');
        $reg = new geoBulkUploaderRegistry();
        $saved = $reg->_savedUpgrades;

        $body .= "
					<div class='form-group'>
					<label class='control-label col-md-5 col-sm-5 col-xs-12'></label>
					  <div class='col-md-7 col-sm-7 col-xs-12'>
						<input type=\"checkbox\" id=\"bolding\" name='bulkUpgrades[bolding]' value='1' " . (($saved['bolding']) ? "checked='checked'" : "") . " />&nbsp;
						Bolding
					  </div>
					</div>

					<div class='form-group'>
					<label class='control-label col-md-5 col-sm-5 col-xs-12'></label>
					  <div class='col-md-7 col-sm-7 col-xs-12'>
						<input type=\"checkbox\" id=\"better_placement\" name='bulkUpgrades[better_placement]' value='1' " . (($saved['better_placement']) ? "checked='checked'" : "") . " />&nbsp;
						Better Placement
					  </div>
					</div>

					<div class='form-group'>
					<label class='control-label col-md-5 col-sm-5 col-xs-12'></label>
					  <div class='col-md-7 col-sm-7 col-xs-12'>
						<input type=\"checkbox\" id=\"featured_ad\" name='bulkUpgrades[featured_ad]' value='1' " . (($saved['featured_ad']) ? "checked='checked'" : "") . " />&nbsp;
						Featured Listing
					  </div>
					</div>
				";

        $attentionGetters = $this->getAttentionGetters();
        if (count($attentionGetters) > 0) {
            $select = "<select id=\"attention_getter_url\" name='bulkUpgrades[attention_getter_url]' onchange=\"$('attention_getter').checked = true;\"  class=\"form-control col-md-7 col-xs-12\">";
            foreach ($attentionGetters as $key => $value) {
                $select .= "<option value=\"$value\" " . (($saved['attention_getter_url'] == $value) ? "selected='selected'" : "") . " >$key</option>";
            }
            $select .= "</select>";
            $body .= "
					<div class='form-group'>
					<label class='control-label col-md-5 col-sm-5 col-xs-12'></label>
					  <div class='col-md-7 col-sm-7 col-xs-12'>
						<input type=\"checkbox\" id=\"attention_getter\" name='bulkUpgrades[attention_getter]' onclick=\"$('attention_getter_url').focus();\" value='1' " . (($saved['attention_getter']) ? "checked='checked'" : "") . " /> $select&nbsp;
						Attention Getter:
					  </div>
					</div>
					";
        }
        return $body;
    }

    function getFinishedBlock()
    {
        $body = "<table><tr><td align=center style=\"font-weight: bold; font-size:1.4em;\">Upload Complete!</td></tr></table>\n";
        $body .= "<table width=100% class=\"form_table\" id=\"defaultTable\">\n";
        $body .= "<tr class=\"form_row\">\n\t";

        $failedFile = $this->session->config("failedFile");
        if ($failedFile) {
            $body .= "<div><strong><a href='bulk_uploader/uploads/$failedFile'>Failed Data</a></strong></div>\n";
        } else {
            $body .= "<center><div style='position:relative;'>Your upload was performed without any errors.</div></center>";
        }
        $body .= "</tr>\n";
        $body .= "</table>";
        return $body;
    }

    function getAttentionGetters()
    {
        $sql_query = "select * from " . $this->choices_table . " where type_of_choice = 10";
        $result = $this->db->Execute($sql_query);
        $attentionGetters = array();
        while ($resultRow = $result->FetchRow()) {
            $attentionGetters[$resultRow["display_value"]] = $resultRow["value"];
        }
        return $attentionGetters;
    }

    function categoryDropdown($selected = 0)
    {

        $this->get_subcategories_for_dropdown($this->db, 0, $this->db->get_site_setting('levels_of_categories_displayed_admin'));
        if (!isset($categoryId)) {
            $categoryId = 0;
        }

        $disabled = ($this->session->config('multiple_categories') == 1) ? 'disabled="disabled"' : '';

        $body = "<select id='categoryDropdown' name='data[category]' class='category_select' $disabled>";
        foreach ($this->category_dropdown_name_array as $key => $value) {
            $body .= "<option ";
            if ($this->category_dropdown_id_array[$key] == $categoryId || $this->category_dropdown_id_array[$key] == $selected) {
                $body .= "selected";
            }
            $body .= " value=\"" . $this->category_dropdown_id_array[$key] . "\">" . urldecode($this->category_dropdown_name_array[$key]) . "</option>\n\t\t";
        }
        $body .= "</select>";

        return $body;
    } //end of function get_category_dropdown

    /**
     * adds scripts and styles to the <head> of the document
     *
     * @return void
     */
    function setHeadInformation()
    {
        $path = dirname($this->get_site_setting('classifieds_url')) . '/addons/bulk_uploader';
        $this->addHeader("<link rel='stylesheet' type='text/css' href='$path/bulk.css' />");
    }


    /**
     * parses CSV file into an array
     *
     * @param int $rows number of rows to be parsed
     * @return array
     */
    function getCSVData($rows = 0)
    {
        $csvReturn = array();
        $file = $this->session->config('fileName');
        $handle = fopen($file, "r");

        $delimiter = $this->session->config('delimiter');
        if (!$delimiter) {
            $delimiter = ',';
        }
        $encapsulation = $this->session->config('encapsulation');
        if (!$encapsulation) {
            $encapsulation = '"';
        }
        $pass = $this->session->config('skipfirstrow');

        if (!$rows) {
            while ($csvData = fgetcsv($handle, 300000, $delimiter, $encapsulation)) {
                if ($pass) {
                    $pass = false;
                    continue;
                }
                foreach ($csvData as $key => $val) {
                    $csvData[$key] = str_replace("'", "&#039;", $val);
                }
                if (count($csvData) > 1) {
                    $csvReturn[] = $csvData;
                }
            }
        } else {
            if ($pass) {
                $rows++;
            }
            for ($i = 0; $i < $rows; $i++) {
                $csvData = fgetcsv($handle, 300000, $delimiter, $encapsulation);
                foreach ($csvData as $key => $val) {
                    $csvData[$key] = str_replace("'", "&#039;", $val);
                }
                $csvReturn[] = $csvData;
            }
        }
        fclose($handle);
        return $csvReturn;
    }

    /**
     * counts the rows in the csv file
     *
     * @param int $rows number of rows to be parsed
     * @return int
     */
    function getCSVLength($fileName)
    {
        $csvReturn = 0;
        $handle = fopen($fileName, "r");
        while ($csvData = fgetcsv($handle, 300000)) {
            $csvReturn++;
        }
        return $csvReturn;
    }

    /**
     * turns an array into a CSV data sheet
     *
     * @param string $fileName file to insert data
     * @param array $csvData two-dimensional array
     * @param
     * @return boolean
     */
    function putCSVData($fileName, $csvData, $delimiter = ',', $encapsulation = '')
    {
        $handle = fopen($fileName, "wb");
        $CSVString = '';
        foreach ($csvData as $values) {
            foreach ($values as $value) {
                $CSVString .= $encapsulation . $value . $encapsulation . $delimiter;
            }
            $CSVString = rtrim($CSVString, $delimiter);
            $CSVString = $CSVString . "\n";
        }
        fwrite($handle, $CSVString);
        fclose($handle);
    }

    /**
     * sets the CSV file to be referenced later
     *
     * @param string $fileName location of csvdata, usually in $_FILES
     * @return boolean
     */
    function setCSVData($fileName)
    {
        //clean file system
        $oldFile = $this->session->config('fileName');
        if (file_exists($oldFile)) {
            unlink($oldFile);
        }

        $compression = $this->session->config('compression');
        if ($compression) {
            // Compressed
            switch ($compression) {
                case 'gzip':
                    if (function_exists('readgzfile')) {
                        $text = readgzfile($fileName);
                    } else {
                        return false;
                    }
                    break;

                case 'bz2':
                    if (
                        function_exists('bzopen') && function_exists('bzread') &&
                        function_exists('bzdecompress') && function_exists('bzclose')
                    ) {
                            $bz_file = bzopen($fileName, 'r');
                            $bz_string = bzread($bz_file);
                            $text = bzdecompress($bz_string);
                            bzclose($bz_file);
                    } else {
                        return false;
                    }
                    break;

                case 'zip':
                    if (
                        function_exists('zip_open') && function_exists('zip_read') &&
                        function_exists('zip_entry_read') && function_exists('zip_entry_open') &&
                        function_exists('zip_entry_close') && function_exists('zip_close')
                    ) {
                            $zip_file = zip_open($fileName);
                            $zip_entry = zip_read($zip_file);
                        if (!zip_entry_open($zip_file, $zip_entry)) {
                            return false;
                        }
                            $text = zip_entry_read($zip_entry);
                            zip_entry_close($zip_entry);
                            zip_close($zip_file);
                    } else {
                        return false;
                    }
                    break;

                default:
                    // Some unknown file type
                    return false;
            }
            do {
                $newFileName = $this->uploads . md5(rand(0, 99999));
            } while (file_exists($newFileName));
            $handle = fopen($newFileName, "wb");
            if (fwrite($handle, $text)) {
                return $newFileName;
            } else {
                return false;
            }
            fclose($handle);
        } else {
            do {
                $newFileName = $this->uploads . md5(rand(0, 99999));
            } while (file_exists($newFileName));
            if (move_uploaded_file($fileName, $newFileName)) {
                return $newFileName;
            } else {
                return false;
            }
        }
    }



    /**
     * This function contains javascript that is used in-line in the addJs() function above.
     * 'container' is a containing div already set up with $() in prototype
     *
     * @return string
     */
    function getJavascriptDropDownBlock($name)
    {
        $header = "
		container.insert(new Element('span', {'class':'selectBoxHeader'}).update(numericId));";

        $fieldSelect = "
			selectBox = new Element('select', {
				'id': '" . $name . "'+numericId,
				'name': '" . $name . "['+numericId+']',
			});

			selectBox.insert(new Element('option', {'value':'null'}).update('Select a field'));";
        $k = 0;
        foreach ($this->bottom_dropdown as $catKey => $catValue) {
            foreach ($catValue as $key => $value) {
                $fieldSelect .= "
				selectBox.insert(new Element('option', {
					'value': '" . $value[1] . "',
					'id': '$catKey'
				}).update('" . geoString::specialChars($value[0]) . "'));
				";
            }
        }
        $fieldSelect .= "
		container.insert(selectBox);";
        return $header . $fieldSelect;
    }

    /**
     * builds html block for display
     *
     * @param string $title
     * @param string $buttonText
     * @param int $page
     * @return string
     */
    function getUploadForm($title, $buttonText, $page)
    {
        $arguments = func_get_args();

        $body = "<script type='text/javascript/'>
			Text[1] = ['Bulk Upload Data', 'Choose the file you would like to import.  You may upload either a text file or a compressed file that your server can handle.  If you are using a text file leave the compression set to None.']\n
			Text[2] = ['Delimiter & Encapsulation', 'Choose the characters that separate and enclose your fields. Most CSV files use a comma as the delimiter and quotes for the encapsulator.']\n
			Text[3] = ['Choose the Type of Data', 'Choose which type of data. User imports are coming soon!']\n
			Text[4] = ['Choose the Category', 'Choose which category you would like your listings to be imported to.']\n
			Text[5] = ['Map uploaded columns to the database', 'Use the drop down menus to change which uploaded column is mapped to which database column.']\n
			Text[6] = ['Set default field values', 'Default fields allow you add static data to every listing you import.']\n
			Text[7] = ['Combine fields to create the title', 'This feature allows you to append other fields together to create your title field.']\n
			Text[8] = ['Listing\'s Duration', 'Control the start and end times.']\n
			Text[9] = ['Listing\'s Upgrades', 'Give your newly imported listings extra upgrades.']\n
			Text[10] = ['Set the default Seller', 'If you do not have a User ID column in your data you MUST assign the user id here.  If you have a User ID column set but some rows are missing ID data you can set a default value to fill in those gaps.']\n";


        // Set style for tooltip
        //$body .= "Style[0] = ['white','','','','',,'black','#ffffcc','','','',,,,2,'#b22222',2,24,0.5,0,2,'gray',,2,,13]\n";
        $body .= "Style[1]=['white','#000099','','','',,'black','#e8e8ff','','','',,,,2,'#000099',2,,,,,'',3,,,]\n";
        $body .= "var TipId = 'tiplayer'\n";
        $body .= "var FiltersEnabled = 1\n";
        $body .= "mig_clay()\n";
        $body .= "</script>";


        $body .= "
		<fieldset>
		<legend>$title</legend>
		<div>
		";
        for ($i = 3; $i < count($arguments); $i++) {
            $body .= $arguments[$i];
        }
        $body .= "
		</div>
		</fieldset>";

        // Submit button
        $body .= "<input type=submit id=formSubmit value='$buttonText'>&nbsp;";
        if ($page > 1 && $page != 4) {
            $body .= "<input type=button value='Back' onClick='javascript: window.location = \"$this->self_path&p=" . ($page - 1) . "\";'>&nbsp;";
        }
        $body .= "<input type=reset value='Reset' title='This only resets the current page'>";

        return $body;
    }

    /**
     * Deletes a log
     *
     * @param integer $log_id
     * @return boolean
     */
    public function deleteLog($log_id)
    {
        $sql = "SELECT `listing_id` FROM `geodesic_addon_bulk_uploader_listings` WHERE `upload_id` = ?";
        $result = $this->db->Execute($sql, array($log_id));
        while ($log = $result->FetchRow()) {
            $listings[] = $log['listing_id'];
        }
        $listing_ids = implode(', ', $listings);

        if (empty($listing_ids)) {
            trigger_error('DEBUG STATS: error!: $listing_ids is not set');
            $sql = "DELETE FROM " . $this->table_prefix . "_log WHERE `log_id`='$log_id' LIMIT 1";
            $r = $this->db->Execute($sql);
            if (!$r) {
                trigger_error('DEBUG SQL: Sql error! sql: ' . $sql . ' Error: ' . $this->db->ErrorMsg());
            }
            return true;
        }

        $sql = "SELECT category FROM $this->classifieds_table WHERE id IN($listing_ids) GROUP BY category";
        $categoryResult = $this->db->Execute($sql);

        foreach ($listings as $deleteMe) {
            geoListing::remove($deleteMe); //use the built-in way to remove listings, to make sure we get all the extraneous data
        }

        while ($row = $categoryResult->FetchRow()) {
            geoCategory::updateListingCount($row['category']);
        }

        // Delete from log tables
        $sql = "DELETE FROM " . $this->table_prefix . "_log WHERE log_id = '$log_id'";
        $r = $this->db->Execute($sql);
        if (!$r) {
            trigger_error('DEBUG SQL: Sql error! sql: ' . $sql . ' Error: ' . $this->db->ErrorMsg());
            return false;
        }
        $sql = "DELETE FROM " . $this->table_prefix . "_listings WHERE upload_id = '$log_id'";
        $r = $this->db->Execute($sql);
        if (!$r) {
            trigger_error('DEBUG SQL: Sql error! sql: ' . $sql . ' Error: ' . $this->db->ErrorMsg());
            return false;
        }
        return true;
    }

    var $user_ids = array();
    function lookupUserId($username)
    {
        if (is_numeric($username)) {
            return $username;
        }
        if (isset($this->user_ids[$username])) {
            return $this->user_ids[$username];
        }

        $db = DataAccess::getInstance();
        $sql = "SELECT id from geodesic_logins WHERE username=?";
        $user_id = $db->GetOne($sql, array($username));
        $this->user_ids[$username] = $user_id;
        return $user_id;
    }


    public function insertImages($images, $classified_id, $imageBase, $totalImages, $fastMode, $imageCaptions)
    {
        if (!$classified_id) {
            die("classified ID is not set");
        }
        if (!is_array($images) || empty($images)) {
            die('images is not an array, or is empty. ' . print_r($images, 1));
            return false;
        }

        $numImages = 0;

        $db = DataAccess::getInstance();
        //get image settings out of the old-school table
        $sql = "SELECT lead_picture_width as thumb_w, lead_picture_height as thumb_h,
				maximum_full_image_width as full_w, maximum_full_image_height as full_h,
				url_image_directory as remote_path, image_upload_path as local_path, photo_quality as quality
				FROM " . geoTables::ad_configuration_table;
        $settings = $db->GetRow($sql);

        /* NOTE: using lead_picture_* (not maximum_image_*) for thumbnail sizes.
         * That doesn't make much sense to me, but seems to be how the base software is doing it, so we'll go with it for now
         */

        //remove any existing images on the listing
        $sql = "SELECT image_id FROM " . geoTables::images_urls_table . " WHERE classified_id = ?";
        $preExisting = $db->GetAll($sql, array($classified_id));
        foreach ($preExisting as $deleteMe) {
            geoImage::remove($deleteMe['image_id']);
        }

        $imagesAdded = 0;
        foreach ($images as $key => $image) {
            if ($numImages++ >= $totalImages) {
                //can't add any more images
                break;
            }

            $filename = ($imageBase) ? $imageBase . $image['name'] : $image['name'];

            //getimagesize doesn't like spaces in filenames
            $filename = str_replace(' ', '%20', $filename);

            $data = false;
            if (FORCE_CURL && function_exists('curl_init')) {
                //try to transfer the image to a local temp file using curl first
                //FORCE_CURL must be set manually at the top of this file, to enable this
                //most servers will not need this
                $ch = curl_init($filename);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1);
                $data = curl_exec($ch);
                curl_close($ch);
                $temp = $this->uploads . 'image_' . time() . rand(1, 1000) . "." . $image['extension'];
                $fp = fopen($temp, "w+");
                if (fwrite($fp, $data) === false) {
                    fclose($fp);
                    unlink($fp);
                }
                fclose($fp);
                $data = $temp;
                unlink($temp);
            } else {
                //this is the default case -- just pass in whatever is given as the filename
                //this can be a fully-qualified URL (e.g. http://example.com/image1.jpg)
                //or a local path (e.g. /var/www/images/image1.jpg)
                //a path relative to the folder this script is running from would also probably work (e.g. uploads/image1.jpg)


                if ($fastMode) {
                    //using fastMode, we first look for an extant token, but don't create one if it's missing
                    $token = geoBulkUploaderImageTokenizer::getToken($filename, true);
                    if (!$token) {
                        //there's no extant token for this fastMode image, so just use the given string as a URL
                        $data = $filename;
                    }
                } else {
                    //if not using fastMode, either get an extant token or take the time to make a new one
                    $data = geoBulkUploaderImageTokenizer::getToken($filename);
                }
            }

            if (function_exists('imagejpeg') && !$fastMode) {
                //save resized images -- make the geoImage class do most of the legwork.

                //NOTE: all processed images will be converted to be jpegs
                $imageType = 'image/jpeg';

                //geoImage::resize() returns an array in the form array('image'=>$imageResource, 'width'=>int, 'height'=>int)
                $fullImage = geoImage::resize($data, $settings['full_w'], $settings['full_h']);
                $thumbImage = geoImage::resize($data, $settings['thumb_w'], $settings['thumb_h']);

                //sanity check to make sure these are actual images
                if (!$fullImage['image'] || !$thumbImage['image']) {
                    continue;
                }

                //find filenames to use for the new images
                $fullName = geoImage::generateFilename($settings['local_path']);
                $thumbName = geoImage::generateFilename($settings['local_path']);

                //store some data that's useful for putting these in the db in a bit
                $fullData = array(
                    'width' => $fullImage['width'],
                    'height' => $fullImage['height'],
                    'filepath' => $settings['remote_path'] . $fullName,
                    'filename' => $fullName
                );
                $thumbData = array(
                    'width' => $thumbImage['width'],
                    'height' => $thumbImage['height'],
                    'filepath' => $settings['remote_path'] . $thumbName,
                    'filename' => $thumbName
                );

                //now write the data to an actual image file on disk
                $fullCreate = imagejpeg($fullImage['image'], $settings['local_path'] . $fullName, $settings['quality']);
                $thumbCreate = imagejpeg($thumbImage['image'], $settings['local_path'] . $thumbName, $settings['quality']);

                //kill the temp images to free memory
                imagedestroy($fullImage);
                imagedestroy($thumbImage);
            } elseif (substr($filename, 0, 4) === 'http') {
                //either don't have imagejpeg, or user wants to do this the fast way and skip all the fancy processing
                //in this case, this MUST be a fully-qualified URL, and we'll try to simply toss it into the database

                /*
                 * The old way: used to run getimagesize() here, which can be really slow.
                 *  instead, we now set the dims to 0 and wait to do actually check them until each individual image is requested on the front end for the first time
                 *
                 */

                $imageType = '';
                $width = $height = 0;


                $fullData = array(
                    'width' => $width,
                    'height' => $height,
                    'filepath' => $filename,
                    'filename' => $filename
                );
                //not making a thumbnail for this, but fill it with dummy data to make the insert query less messy
                $thumbData = array(
                    'width' => $width,
                    'height' => $height,
                    'filepath' => '',
                    'filename' => ''
                );
            }


            $sql = "INSERT INTO " . geoTables::images_urls_table . " SET
				classified_id = ?,

				image_url = ?,
				full_filename = ?,
				thumb_url = ?,
				thumb_filename = ?,
				file_path = ?,

				image_width = ?,
				image_height = ?,
				original_image_width = ?,
				original_image_height = ?,

				date_entered = ?,
				display_order = ?,
				mime_type = ?,

				image_text = ?";

            $queryData = array(
                $classified_id,

                $fullData['filepath'],
                $fullData['filename'],
                $thumbData['filepath'],
                $thumbData['filename'],
                $settings['local_path'],

                $thumbData['width'],
                $thumbData['height'],
                $fullData['width'],
                $fullData['height'],

                geoUtil::time(),
                $image['order'],
                $imageType,

                $imageCaptions[$key] . ''
            );

            $r = $db->Execute($sql, $queryData);
            if ($r === false) {
                //image insertion failed (bad image?)
            } else {
                $imagesAdded++;
            }
        }

        if (!$imagesAdded) {
            //we have not successfully added any images, likely due to bad URLs or lack of access
            //set the classifieds table back to 0 images, so that the "no images" icon appears
            $db->Execute("UPDATE " . geoTables::classifieds_table . " SET `image` = 0 WHERE `id` = ?", array($classified_id));
        }
    }


    function insertYoutube($youtubeIds, $listingId, $totalVideos)
    {
        require_once(CLASSES_DIR . 'order_items/offsite_videos.php');
        $numVideos = 0;
        $slot = 1;
        $db = DataAccess::getInstance();

        //clear any pre-existing videos
        $db->Execute("DELETE FROM " . geoTables::offsite_videos . " WHERE `listing_id`=$listingId");

        //set up the insert query, for speed and awesome
        $sql = "INSERT INTO " . geoTables::offsite_videos . " (`listing_id`, `slot`, `video_type`, `video_id`, `media_content_url`, `media_content_type`) VALUES ('$listingId', ?, 'youtube', ?, ?, ?)";
        $insertQuery = $db->Prepare($sql);

        foreach ($youtubeIds as $id) {
            if (++$numVideos > $totalVideos) {
                //too many videos!
                break;
            }
            //make the order item do all the hard work -- it will take a youtube id or url in most any format and spit back the needed data
            $videoData = offsite_videosOrderItem::getYoutubeDataForVideoId($id);
            $db->Execute($insertQuery, array($slot++, $videoData['video_id'], $videoData['media_content_url'], $videoData['media_content_type']));
        }
    }

    function getDelayedSetting($seller_id)
    {
        $sql = "SELECT auction_price_plan_id plan FROM `geodesic_user_groups_price_plans` WHERE id='$seller_id'";
        $r = $this->db->getrow($sql);
        if ($r === false) {
            die($this->db->ErrorMsg() . "<br /> $sql");
        }
        if (empty($r)) {
            return false;
        }

        $sql = "SELECT delayed_start_auction start_late FROM geodesic_classifieds_price_plans WHERE price_plan_id=?";
        $r = $this->db->getrow($sql, array($r['plan']));
        if ($r === false) {
            die($this->db->ErrorMsg() . "<br /> $sql");
        }
        return isset($r['start_late']) ? $r['start_late'] : 2;
    }

     /**
      * gets column names from a table
      *
      * @var int $table
      */
    function listFieldNames($table, $skip = array())
    {
        $sql = "SHOW columns FROM $table";
        $r = $this->db->getassoc($sql);
        if (!empty($r)) {
            foreach ($r as $key => $column) {
                $columnNames[$key] = $column['Field'];
            }
            if (!empty($skip)) {
                foreach ($skip as $k => $v) {
                    if (isset($columnNames[$k])) {
                        unset($columnNames[$k]);
                    }
                }
            }
        }
        return $columnNames;
    }
    var $adminMessage;

    function initAdminMessages()
    {
        $this->adminMessage = geoAdmin::getInstance();
    }

    function propagateDropdownArrays()
    {
        $this->bottom_dropdown = array(
            "general" => array(
                array("Title","title"),
                array("User ID (Seller)","seller"),
                array("Description","description"),
                array("Locations (Terminal Regions) [List]","location"),
                array("Address","location_address"),
                array("City","location_city"),
                array("Zip","location_zip"),
                array("Phone 1","phone"),
                array("Phone 2","phone2"),
                array("Fax","fax"),
                array("URL Link 1","url_link_1"),
                array("URL Link 2","url_link_2"),
                array("URL Link 3","url_link_3"),
                array("Email","email"),
                array("Business Type","business_type"),
                array("Payment Types","payment_options"),
                array("Precurrency Symbol","precurrency"),
                array("Postcurrency Symbol","postcurrency"),
                array("Language ID#","language_id"),
                array("Show Contact Seller","show_contact_seller"),
                array("Show Seller's Other Listings","show_other_ads"),
            ),
            "mapping" => array(
                array("Mapping Location","mapping_location"),
            ),
            "images" => array(
                array("Base Image URL", "image_base_url"),
                array("Images [List]", "image_list"),
                array("Captions [List]", "image_caption_list"),
                array("Youtube Videos [List]", "youtube_list")
            ),
            "optional" => array(
                array('Listing Tags [List]', 'tags_list')
            )
        );

        $this->top_dropdown = array(
            "null" => "Field Not Used",
            "all" => "All Fields",
            "general" => "General Fields",
            "mapping" => "Mapping Fields",
            "optional" => "Optional Fields",
            "leveled" => "Multi-Level Fields",
            "images" => "Images"
        );
    }

    private function _addTags($listingId, $tagArray)
    {
        if (!$tagArray || count($tagArray) < 1 || !$listingId) {
            //not enough info to process tags
            return false;
        }
        $db = DataAccess::getInstance();
        //first, remove any existing tags
        $sql = "DELETE FROM `geodesic_listing_tags` WHERE `listing_id` = ?";
        $db->Execute($sql, array($listingId));

        //now loop through the array and add the new values
        $sql = "INSERT INTO `geodesic_listing_tags` (`listing_id`, `tag`) VALUES (?,?)";
        $prep = $db->Prepare($sql);
        foreach ($tagArray as $tag) {
            $db->Execute($prep, array($listingId, geoString::toDB($tag)));
        }
        return true;
    }

    private $_regionCache;
    private function _getTerminalRegionId($location)
    {
        if (!$location) {
            //nothing to find!
            return false;
        }

        if (isset($this->_regionCache[$location])) {
            return $this->_regionCache[$location];
        }

        if (is_numeric($location)) {
            //this is already a region id -- nothing to do here!
            return $location;
        }

        //first, check the "unique name" field
        //special case: also replace spaces with hyphens and check that
        $id = (int)$this->db->GetOne("SELECT `id` FROM " . geoTables::region . " WHERE unique_name=? OR unique_name=?", array(geoString::toDB($location), geoString::toDB(str_replace(' ', '-', $location))));
        if ($id) {
            $this->_regionCache[$location] = $id;
            return $id;
        }

        //now check abbreviations (since the pre-GeoCore State field required uploading by abbreviation)
        //since this is legacy and abbreviations are non-unique, only look in the "State" level
        $levels = geoRegion::getLevelsForOverrides();
        if ($levels['state']) {
            $id = (int)$this->db->GetOne("SELECT `id` FROM " . geoTables::region . " WHERE billing_abbreviation=? AND level=?", array(geoString::toDB($location), $levels['state']));
            if ($id) {
                $this->_regionCache[$location] = $id;
                return $id;
            }
        }

        //if nothing found there, try looking for the name directly
        $id = (int)$this->db->GetOne("SELECT `id` FROM " . geoTables::region_languages . " WHERE name=?", array(geoString::toDB($location)));
        if ($id) {
            $this->_regionCache[$location] = $id;
            return $id;
        }

        //found nothing
        return false;
    }

    private function _getLeveledIdByName($fieldId, $givenValue)
    {
        $db = DataAccess::getInstance();
        $value = geoString::toDB($givenValue);
        $sql = "SELECT v.`id` FROM " . geoTables::leveled_field_value . " as v, " . geoTables::leveled_field_value_languages . " as l
				WHERE v.id=l.id AND `leveled_field` = ? AND `name` = ?";
        $id = $db->GetOne($sql, array($fieldId, $value));
        return $id ? $id : false;
    }

    private function _processCostOptions($options, $listingId)
    {
        $db = DataAccess::getInstance();
        //first, some table name shortcuts, just to make my life easier...
        $tables = array(
            'optgroup' => 'geodesic_listing_cost_option_group',
            'option' => 'geodesic_listing_cost_option',
            'combined_quantities' => 'geodesic_listing_cost_options_quantity',
            'combined_quantity_sets' => 'geodesic_listing_cost_options_q_option'
        );

        //now, wipe any existing options for this listing, so they may be added fresh

        //to do that, first get the group IDs to wipe
        $sql = "SELECT `id` FROM " . $tables['optgroup'] . " WHERE `listing` = ?";
        $result = $db->Execute($sql, array($listingId));
        foreach ($result as $optiongroup) {
            $deleteMe[] = (int)$optiongroup['id'];
        }
        if (count($deleteMe) > 0) {
            $delstr = implode(',', $deleteMe);
            //found some stuff to delete, so actually delete it
            $db->Execute("DELETE FROM " . $tables['optgroup'] . " WHERE `id` IN ($delstr)");
            $db->Execute("DELETE FROM " . $tables['option'] . " WHERE `group` IN ($delstr)");
        }

        //similar thing once more, but for the combined quantity tables this time
        $sql = "SELECT `id` FROM " . $tables['combined_quantities'] . " WHERE `listing` = ?";
        $result = $db->Execute($sql, array($listingId));
        foreach ($result as $qgroup) {
            $deleteMe[] = (int)$qgroup['id'];
        }
        if (count($deleteMe) > 0) {
            $delstr = implode(',', $deleteMe);
            //found some stuff to delete, so actually delete it
            $db->Execute("DELETE FROM " . $tables['combined_quantities'] . " WHERE `id` IN ($delstr)");
            $db->Execute("DELETE FROM " . $tables['combined_quantity_sets'] . " WHERE `combo_id` IN ($delstr)");
        }


        //now that any old data is gone, build new data from the input array, and add it to the db

        $seller = geoListing::getListing($listingId, false)->seller;

        foreach ($options['groups'] as $order => $data) {
            if ($data['optquantities']) {
                $quantity_type = 'individual';
            } elseif ($options['combinedquantities']) {
                $quantity_type = 'combined';
                //known issue: this will set quantity type to 'combined' for options that have "no" quantity if combined quantities are in use elsewhere for this listing
                // -- I don't think that should hurt anything, but it's worth noting.
            } else {
                $quantity_type = 'none';
            }
            $sql = "INSERT INTO " . $tables['optgroup'] . " (`listing`, `label`, `seller`, `quantity_type`, `display_order`) VALUES (?,?,?,?,?)";
            $result = $db->Execute($sql, array($listingId, geoString::toDB($data['groupname']), $seller, $quantity_type, $order - 1));
            $optgroup_id = $db->Insert_Id();
            if ($optgroup_id) {
                $labels = explode(',', $data['optnames']);
                $costs = explode(',', $data['optcosts']);
                $files = explode(',', $data['optfiles']);
                $quantities = explode(',', $data['optquantities']);
                for ($i = 0; $i < count($labels); $i++) {
                    $sql = "INSERT INTO " . $tables['option'] . " (`group`,`label`,`cost_added`,`file_slot`,`ind_quantity_remaining`,`display_order`) VALUES (?,?,?,?,?,?)";
                    $db->Execute($sql, array($optgroup_id, geoString::toDB($labels[$i]), ($costs[$i] ? $costs[$i] : 0), (int)$files[$i], (int)$quantities[$i], $i));
                    $mapNamesIds[$labels[$i]] = $db->Insert_Id();
                }
            }
        }

        if ($options['combinedquantities']) {
            //format e.g.: red+medium=4,red+large=5,black+medium=2
            $quantityExpressions = explode(',', $options['combinedquantities']);
            foreach ($quantityExpressions as $order => $exp) {
                $parts = explode('+', $exp);
                $quantity = substr($exp, strpos($exp, '=') + 1);
                //figure out the internal option IDs for these parts
                $used_ids = array(); //be sure to blank this out every time
                foreach ($parts as $optname) {
                    $used_ids[] = $mapNamesIds[$optname];
                }
                $sql = "INSERT INTO " . $tables['combined_quantities'] . " (`listing`, `quantity_remaining`) VALUES (?,?)";
                $db->Execute($sql, array($listingId, (int)$quantity));
                $combo_id = $db->Insert_Id();
                if ($combo_id) {
                    foreach ($used_ids as $opt_id) {
                        $sql = "INSERT INTO " . $tables['combined_quantity_sets'] . " (`combo_id`,`option_id`) VALUES (?,?)";
                        $db->Execute($sql, array($combo_id, $opt_id));
                    }
                }
            }
        }
    }
}
