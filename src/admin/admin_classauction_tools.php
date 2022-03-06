<?php

class Admin_classauction_tools extends Admin_site
{
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
    function display_admin_tools_view_ads()
    {
        // Variables
        $content = "";
        $usr_cache = array();
        $sql_query = "";

        // Begin the table
        $content .= "<form method='post' action='' class='form-horizontal'>
			<fieldset id='SearchListings'><legend>Search Expired Listings</legend>
			<div class='x_content'>";

        // Days Dropdown list
        $content .= '
				<div class="form-group">
					<label class="control-label col-xs-12 col-sm-5">Expired For: </label>
					<div class="col-xs-12 col-sm-6 input-group">
						<select id="age" name="age" class="form-control col-md-7 col-xs-12">';

        for ($x = 1; $x <= 45; $x++) {
            $content .= "
							<option value='" . $x . "'" . (($_REQUEST['age'] == $x) ? " selected" : "") . ">" . $x . "</option>";
        }

        $content .= '
						</select>
						<div class="input-group-addon">Days</div>
					</div>
				</div>';

        $content .= "
				<div class='center'><input type='submit' name='auto_save' value='Go'></div>";

        $content .= "
			</div></fieldset></form>";

        // Set the page for pagination
        if ($_REQUEST['show']) {
            $page = $_REQUEST['show'];
            $page_start = (($page - 1) * 25);
            $limit = $page_start . ",25";
        } else {
            $page = 1;
            $limit = "0,25";
        }

        // Expired Classifieds SQL
        $sql_query = "SELECT * FROM " . $this->classifieds_table . " WHERE ends < " . time() . " AND ends > " . (time() - ($_REQUEST['age'] * 24 * 60 * 60)) . " AND live = 0";
        $sql_query .= " ORDER BY ends DESC LIMIT " . $limit;
        $rs = $this->db->Execute($sql_query);
        if (!$rs) {
            return false;
        }

        // Count the expired classifides
        $sql_query = "SELECT count(*) as total FROM " . $this->classifieds_table . " WHERE ends < " . time() . " AND ends > " . (time() - ($_REQUEST['age'] * 24 * 60 * 60)) . " AND live = 0";
        $total_rs  = $this->db->Execute($sql_query);
        if (!$total_rs) {
            return false;
        }

        $total = $total_rs->FetchRow();
        $number_of_pages = ceil($total['total'] / 25);

        // Begin the table
        $content .= "
			<fieldset id='ExpiredListings'><legend>Search Results</legend><table class='table table-responsive table-hover table-striped'>";


        // Loop through the Ads
        if ($rs->RecordCount()) {
            $content .= "
				<thead>
					<tr class='col_hdr'>
						<th>ID</th>
						<th>Title</th>
						<th>Seller</th>
						<th>Description</th>
						<th>Ended</th>
						<th>Details</th>
					</tr>
				</thead>";
            while ($ad = $rs->FetchRow()) {
                if (!$usr_cache[$ad['seller']]) {
                    $sql_query = "SELECT id,username FROM " . $this->userdata_table . " WHERE id = " . $ad['seller'];
                    $usr_rs = $this->db->Execute($sql_query);

                    if ($usr_rs->RecordCount() == 1) {
                        $usr = $usr_rs->FetchRow();
                        $usr_cache[$usr['id']] = urldecode($usr['username']);
                    }
                }


                $content .= "
					<tr class=" . $this->get_row_color() . ">
						<td class=medium_font valign=top align=center>" . $ad['id'] . "</td>
						<td class=medium_font><a href='index.php?mc=users&page=users_view_ad&b=" . $ad['id'] . "'><span class=medium_font>" . urldecode($ad['title']) . "</span></a></td>
						<td class=medium_font valign=top align=center><a href=index.php?mc=users&page=users_view&b=" . $ad['seller'] . "><span class=medium_font>" . $usr_cache[$ad['seller']] . " (" . $ad['seller'] . ")</span></a></td>
						<td class=medium_font>" . geoString::specialChars(substr(strip_tags(geoString::fromDB($ad['description'])), 0, 50)) . "...</td>
						<td class=medium_font valign=top align=center>" . date('m-d-y h:i', $ad['ends']) . "</td>
						<td class=medium_font valign=top align=center><a href='index.php?mc=users&page=users_restart_ad&b=" . $ad['id'] . "'><span class=medium_font>Restart</span></a></td>
					</tr>";
                    $this->row_count++;
            }
            $content .= "</table>";
            // Begin Pagination
            if ($number_of_pages > 1) {
                $content .= "<div class='center'>";
                $content .= geoPagination::getHTML($number_of_pages, $page, "index.php?mc=admin_tools_settings&page=admin_tools_view_ads&age={$_REQUEST['age']}&show=");
                $content .= "</div>";
            }
        } else {
            $content .= "
				<tr>
					<td><div class='page_note_error'>There are no expired listings to display for this time-frame.</div></td>
				</tr></table>";
        }
        $content .= "</fieldset>";

        // Apply to page
        $this->body .= $content;
        // Display page
        $this->display_page();
    }
    function update_admin_tools_view_ads()
    {
    }

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    /*
     * This function provides a way to clean up orphaned image files on a server. Usually, image files will be removed when listings are archived,
     * but sometimes we've seen a few sneak by, so this should clean those out
     */
    function display_admin_tools_clean_images()
    {
        if ($_GET['auto_save'] == 2) {
            $this->update_admin_tools_clean_images();
        }

        $html = geoAdmin::m();
        $html .= '<fieldset><legend>Remove Orphaned Images</legend>';
        $html .= '<div class="page_note">This tool will search the user images folder for any files that are no longer referenced by listings in the database. Any of these "orphaned" listings will be permanently deleted from the server.
		this will not affect your listings in any way, and is normally not needed, but it may help free up disk space on some server configurations, especially older sites or those with lots of uploaded images. If your server has a large number of uploaded files, this may take a while.
		Click the button below to proceed, but do so with caution, as this action cannot be undone.' . (defined('DEMO_MODE') ? '<br /><br /><strong>Disabled for this demo</strong>' : '') . '</div>';

        if (!defined('DEMO_MODE')) {
            $html .= '<div class="center"><a href="index.php?mc=admin_tools_settings&amp;page=admin_tools_clean_images&amp;auto_save=1" class="mini_button lightUpLink">Remove Orphaned Images</a></div></fieldset>';
        }
        geoAdmin::getInstance()->display_page($html);
    }

    function update_admin_tools_clean_images()
    {
        if (defined('DEMO_MODE')) {
            geoAdmin::m('Disabled for this demo');
            return true;
        }
        $db = DataAccess::getInstance();

        //pull array of filenames from images_urls table
        $sql = "SELECT full_filename, thumb_filename FROM " . geoTables::images_urls_table;
        $result = $db->Execute($sql);
        $filenames = array();
        while ($row = $result->FetchRow()) {
            $filenames[] = $row['full_filename'];
            $filenames[] = $row['thumb_filename'];
        }

        $folder = $db->GetOne("SELECT image_upload_path FROM " . $this->ad_configuration_table);

        $dir = dir($folder);
        $count = 0;
        while (false !== ($filename = $dir->read())) {
            //loop through user_images dir

            if ($filename == '.' || $filename == '..' || substr($filename, 0, 1) == '_' || is_dir($folder . $filename)) {
                //skip filenames we don't care about
                continue;
            }

            //compare filename to array
            if (!in_array($filename, $filenames) && is_file($folder . $filename)) {
                unlink($folder . $filename); //delete file if not found
                $count++; //count number of files deleted
            }
        }

        if ($count) {
            geoAdmin::m("Removed $count orphaned images.", geoAdmin::NOTICE);
        } else {
            geoAdmin::m("No orphaned images were found.", geoAdmin::NOTICE);
        }
    }
}
