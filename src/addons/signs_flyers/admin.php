<?php

//addons/signs_flyers/admin.php

# Signs & Flyers Addon

class addon_signs_flyers_admin extends addon_signs_flyers_info
{
    var $db;
    var $admin_site;

    function addon_signs_flyers_admin()
    {
        if (!Singleton::isInstance('Admin_site')) {
            return false;
        }
        if (PHP5_DIR) {
            $this->db = DataAccess::getInstance();
            $this->admin_site = Singleton::getInstance('Admin_site');
        } else {
            $this->db =& DataAccess::getInstance();
            $this->admin_site =& Singleton::getInstance('Admin_site');
        }
    }

    function init_pages()
    {

            menu_page::addonAddPage('addon_signs', '', 'Signs Setup', 'signs_flyers', 'fa-copy');
            menu_page::addonAddPage('addon_flyers', '', 'Flyers Setup', 'signs_flyers', 'fa-copy');
    }

    function display_addon_signs()
    {
        $admin = geoAdmin::getInstance();

        if (isset($_REQUEST["c"])) {
            $choice_id = $_REQUEST["c"];
            if ($choice_id) {
                $sql_query = "delete from " . $this->admin_site->choices_table . "
					where choice_id = " . $choice_id;
                $result = $this->db->Execute($sql_query);
                if (!$result) {
                    return false;
                }
            }
        }


        $sql_query = "select * from " . $this->admin_site->ad_configuration_table;
        $result = $this->db->Execute($sql_query);
        if (!$result) {
            $this->admin_site->site_error($this->db->ErrorMsg());
            return false;
        } elseif ($result->RecordCount() == 1) {
            $this->admin_site->body .= $admin->getUserMessages();
            $show = $result->FetchRow();
            if (!$this->admin_site->admin_demo()) {
                $this->admin_site->body .= "<form action=index.php?mc=listing_setup&page=addon_signs method=post>\n";
            }
            $this->admin_site->body .= "
<fieldset>
<legend>Current Image Choices for Seller</legend>
<div class='table-responsive'>
	<table class='table table-hover table-striped table-bordered'>";

                //."Add a sales flyer template
                //displaying the information you select in it through the form below.  Add your template in the space below using the
                //  field replacement labels listed below.  This template only controls the placement of label and data fields within the table you provide in the
                //  box below.  All data placed within the template is pulled from the users and listing information.<br><br>

                $this->admin_site->body .= "<thead><tr class=col_hdr_top>\n\t<td class=col_hdr_left><b>Image Title (displayed to seller)</b></td><td class=col_hdr_left colspan=\"2\"><b>Image location</b></td></tr></thead>\n";
                $this->admin_site->body .= "<tr class=" . $this->admin_site->get_row_color() . " >\n\t<td class=medium_font>First Image in Listing</td><td class=medium_font>[ within listing ]</td><td></td>";
                $sql_query = "select * from " . $this->admin_site->choices_table . " where type_of_choice = 14";
                $choices_result = $this->db->Execute($sql_query);
            if (!$choices_result) {
                return false;
            } elseif ($choices_result->RecordCount() > 0) {
                while ($show_choice = $choices_result->FetchRow()) {
                    $this->admin_site->body .= "<tr class=" . $this->admin_site->get_row_color() . " >\n\t<td align=left class=medium_font>\n\t";
                    $this->admin_site->body .= $show_choice["display_value"] . "</td>\n\t";
                    $this->admin_site->body .= "<td class=medium_font align=left>" . $admin->geo_templatesDir() . "<em>[Template Set]</em>/external/" . $show_choice["value"] . "</td><td>" . geoHTML::addButton('delete', "index.php?mc=listing_setup&page=addon_signs&c=" . $show_choice["choice_id"] . "&auto_save=1", false, '', 'lightUpLink mini_cancel') . "</td>\n</tr>\n";
                }
            }
                $this->admin_site->body .= "
	</table></div>
</fieldset>
<fieldset>
<legend>Add a New Image Choice</legend>
<div class='table-responsive'>
	<table class='table table-hover table-striped table-bordered'>";

                $this->admin_site->body .= "<thead><tr class=col_hdr_top>\n\t<td class=col_hdr_left><b>Image Title</b></td><td class=col_hdr_left><b>Image location (URL)</b></td></tr></thead>\n";
                $this->admin_site->body .= "<tr class=" . $this->admin_site->get_row_color() . " >\n\t<td class=medium_font><input type=text name=b[new_image_name] size=30></td><td class=medium_font>" . $admin->geo_templatesDir() . "<em>[Template Set]</em>/external/<input type=text name=b[new_image_value] size=60></td></tr>\n";
                $this->admin_site->body .= "
	</table>
</div>";

            if (!$this->admin_site->admin_demo()) {
                $this->admin_site->body .= "<div class='center'><input type=submit name=\"auto_save\" value=\"Add Image Choice\"></div>";
            }

            $this->admin_site->body .= "
</fieldset>
<fieldset>
<legend>Available Signs Template Tags</legend>
<div class='table-responsive'>
	<table class='table table-hover table-striped table-bordered'>";

            $this->admin_site->body .= "<thead><tr class=col_hdr_top>\n\t<th class=col_hdr_right>Tag</td><th class=col_hdr_left>Description</td></tr></thead>\n";
            $this->admin_site->body .= "<tr class=" . $this->admin_site->get_row_color() . " >\n\t<td valign=top align=right class=medium_font>\n\t
				{\$image}</td>\n\t";
            $this->admin_site->row_count++;
            $this->admin_site->body .= "<td class=medium_font>\n\tPlacement of image chosen for sign.\n\t</td>\n</tr>\n";
            $this->admin_site->body .= "<tr class=" . $this->admin_site->get_row_color() . " >\n\t<td valign=top align=right class=medium_font>\n\t
				{\$title}</td>\n\t";
            $this->admin_site->row_count++;
            $this->admin_site->body .= "<td class=medium_font>\n\tThe title of the listing.\n\t</td>\n</tr>\n";
            $this->admin_site->body .= "<tr class=" . $this->admin_site->get_row_color() . " >\n\t<td valign=top align=right class=medium_font>\n\t
				{\$address}</td>\n\t";
            $this->admin_site->row_count++;
            $this->admin_site->body .= "<td class=medium_font>\n\tThe address of the user placing the listing.\n\t</td>\n</tr>\n";
            $this->admin_site->body .= "<tr class=" . $this->admin_site->get_row_color() . " >\n\t<td valign=top align=right class=medium_font>\n\t
				{\$city}</td>\n\t";
            $this->admin_site->row_count++;
            $this->admin_site->body .= "<td class=medium_font>\n\tThe city of the user placing the listing.\n\t</td>\n</tr>\n";
            $this->admin_site->body .= "<tr class=" . $this->admin_site->get_row_color() . " >\n\t<td valign=top align=right class=medium_font>\n\t
				{\$state}</td>\n\t";
            $this->admin_site->row_count++;
            $this->admin_site->body .= "<td class=medium_font>\n\tThe state of the user placing the listing.\n\t</td>\n</tr>\n";
            $this->admin_site->body .= "<tr class=" . $this->admin_site->get_row_color() . " >\n\t<td valign=top align=right class=medium_font>\n\t
				{\$zip}</td>\n\t";
            $this->admin_site->row_count++;
            $this->admin_site->body .= "<td class=medium_font>\n\tThe zip of the user placing the listing.\n\t</td>\n</tr>\n";
            $this->admin_site->body .= "<tr class=" . $this->admin_site->get_row_color() . " >\n\t<td valign=top align=right class=medium_font>\n\t
				{\$price}</td>\n\t";
            $this->admin_site->row_count++;
            $this->admin_site->body .= "<td class=medium_font>\n\tThe price of the listing.\n\t</td>\n</tr>\n";
            $this->admin_site->body .= "<tr class=" . $this->admin_site->get_row_color() . " >\n\t<td valign=top align=right class=medium_font>\n\t
				{\$phone_1}</td>\n\t";
            $this->admin_site->row_count++;
            $this->admin_site->body .= "<td class=medium_font>\n\tphone number 1\n\t</td>\n</tr>\n";
            $this->admin_site->body .= "<tr class=" . $this->admin_site->get_row_color() . " >\n\t<td valign=top align=right class=medium_font>\n\t
				{\$phone_2}</td>\n\t";
            $this->admin_site->row_count++;
            $this->admin_site->body .= "<td class=medium_font>\n\tphone number 2\n\t</td>\n</tr>\n";
            if (geoPC::is_ent()) {
                $this->admin_site->body .= "<tr class=" . $this->admin_site->get_row_color() . " >\n\t<td valign=top align=right class=medium_font>\n\t
					{\$optional_field_1}</td>\n\t";
                $this->admin_site->row_count++;
                $this->admin_site->body .= "<td class=medium_font>\n\tthe optional field 1 if you use it\n\t</td>\n</tr>\n";
                $this->admin_site->body .= "<tr class=" . $this->admin_site->get_row_color() . " >\n\t<td valign=top align=right class=medium_font>\n\t
					{\$optional_field_2}</td>\n\t";
                $this->admin_site->row_count++;
                $this->admin_site->body .= "<td class=medium_font>\n\tthe optional field 2 if you use it\n\t</td>\n</tr>\n";
                $this->admin_site->body .= "<tr class=" . $this->admin_site->get_row_color() . " >\n\t<td valign=top align=right class=medium_font>\n\t
					{\$optional_field_3}</td>\n\t";
                $this->admin_site->row_count++;
                $this->admin_site->body .= "<td class=medium_font>\n\tthe optional field 3 if you use it\n\t</td>\n</tr>\n";
                $this->admin_site->body .= "<tr class=" . $this->admin_site->get_row_color() . " >\n\t<td valign=top align=right class=medium_font>\n\t
					{\$optional_field_4}</td>\n\t";
                $this->admin_site->row_count++;
                $this->admin_site->body .= "<td class=medium_font>\n\tthe optional field 4 if you use it\n\t</td>\n</tr>\n";
                $this->admin_site->body .= "<tr class=" . $this->admin_site->get_row_color() . " >\n\t<td valign=top align=right class=medium_font>\n\t
					{\$optional_field_5}</td>\n\t";
                $this->admin_site->row_count++;
                $this->admin_site->body .= "<td class=medium_font>\n\tthe optional field 5 if you use it\n\t</td>\n</tr>\n";
                $this->admin_site->body .= "<tr class=" . $this->admin_site->get_row_color() . " >\n\t<td valign=top align=right class=medium_font>\n\t
					{\$optional_field_6}</td>\n\t";
                $this->admin_site->row_count++;
                $this->admin_site->body .= "<td class=medium_font>\n\tthe optional field 6 if you use it\n\t</td>\n</tr>\n";
                $this->admin_site->body .= "<tr class=" . $this->admin_site->get_row_color() . " >\n\t<td valign=top align=right class=medium_font>\n\t
					{\$optional_field_7}</td>\n\t";
                $this->admin_site->row_count++;
                $this->admin_site->body .= "<td class=medium_font>\n\tthe optional field 7 if you use it\n\t</td>\n</tr>\n";
                $this->admin_site->body .= "<tr class=" . $this->admin_site->get_row_color() . " >\n\t<td valign=top align=right class=medium_font>\n\t
					{\$optional_field_8}</td>\n\t";
                $this->admin_site->row_count++;
                $this->admin_site->body .= "<td class=medium_font>\n\tthe optional field 8 if you use it\n\t</td>\n</tr>\n";
                $this->admin_site->body .= "<tr class=" . $this->admin_site->get_row_color() . " >\n\t<td valign=top align=right class=medium_font>\n\t
					{\$optional_field_9}</td>\n\t";
                $this->admin_site->row_count++;
                $this->admin_site->body .= "<td class=medium_font>\n\tthe optional field 9 if you use it\n\t</td>\n</tr>\n";
                $this->admin_site->body .= "<tr class=" . $this->admin_site->get_row_color() . " >\n\t<td valign=top align=right class=medium_font>\n\t
					{\$optional_field_10}</td>\n\t";
                $this->admin_site->row_count++;
                $this->admin_site->body .= "<td class=medium_font>\n\tthe optional field 10 if you use it\n\t</td>\n</tr>\n";
                $this->admin_site->body .= "<tr class=" . $this->admin_site->get_row_color() . " >\n\t<td valign=top align=right class=medium_font>\n\t
					{\$optional_field_11}</td>\n\t";
                $this->admin_site->row_count++;
                $this->admin_site->body .= "<td class=medium_font>\n\tthe optional field 11 if you use it\n\t</td>\n</tr>\n";
                $this->admin_site->body .= "<tr class=" . $this->admin_site->get_row_color() . " >\n\t<td valign=top align=right class=medium_font>\n\t
					{\$optional_field_12}</td>\n\t";
                $this->admin_site->row_count++;
                $this->admin_site->body .= "<td class=medium_font>\n\tthe optional field 12 if you use it\n\t</td>\n</tr>\n";
                $this->admin_site->body .= "<tr class=" . $this->admin_site->get_row_color() . " >\n\t<td valign=top align=right class=medium_font>\n\t
					{\$optional_field_13}</td>\n\t";
                $this->admin_site->row_count++;
                $this->admin_site->body .= "<td class=medium_font>\n\tthe optional field 13 if you use it\n\t</td>\n</tr>\n";
                $this->admin_site->body .= "<tr class=" . $this->admin_site->get_row_color() . " >\n\t<td valign=top align=right class=medium_font>\n\t
					{\$optional_field_14}</td>\n\t";
                $this->admin_site->row_count++;
                $this->admin_site->body .= "<td class=medium_font>\n\tthe optional field 14 if you use it\n\t</td>\n</tr>\n";
                $this->admin_site->body .= "<tr class=" . $this->admin_site->get_row_color() . " >\n\t<td valign=top align=right class=medium_font>\n\t
					{\$optional_field_15}</td>\n\t";
                $this->admin_site->row_count++;
                $this->admin_site->body .= "<td class=medium_font>\n\tthe optional field 15 if you use it\n\t</td>\n</tr>\n";
                $this->admin_site->body .= "<tr class=" . $this->admin_site->get_row_color() . " >\n\t<td valign=top align=right class=medium_font>\n\t
					{\$optional_field_16}</td>\n\t";
                $this->admin_site->row_count++;
                $this->admin_site->body .= "<td class=medium_font>\n\tthe optional field 16 if you use it\n\t</td>\n</tr>\n";
                $this->admin_site->body .= "<tr class=" . $this->admin_site->get_row_color() . " >\n\t<td valign=top align=right class=medium_font>\n\t
					{\$optional_field_17}</td>\n\t";
                $this->admin_site->row_count++;
                $this->admin_site->body .= "<td class=medium_font>\n\tthe optional field 17 if you use it\n\t</td>\n</tr>\n";
                $this->admin_site->body .= "<tr class=" . $this->admin_site->get_row_color() . " >\n\t<td valign=top align=right class=medium_font>\n\t
					{\$optional_field_18}</td>\n\t";
                $this->admin_site->row_count++;
                $this->admin_site->body .= "<td class=medium_font>\n\tthe optional field 18 if you use it\n\t</td>\n</tr>\n";
                $this->admin_site->body .= "<tr class=" . $this->admin_site->get_row_color() . " >\n\t<td valign=top align=right class=medium_font>\n\t
					{\$optional_field_19}</td>\n\t";
                $this->admin_site->row_count++;
                $this->admin_site->body .= "<td class=medium_font>\n\tthe optional field 19 if you use it\n\t</td>\n</tr>\n";
                $this->admin_site->body .= "<tr class=" . $this->admin_site->get_row_color() . " >\n\t<td valign=top align=right class=medium_font>\n\t
					{\$optional_field_20}</td>\n\t";
                $this->admin_site->row_count++;
                $this->admin_site->body .= "<td class=medium_font>\n\tthe optional field 20 if you use it\n\t</td>\n</tr>\n";
            }
            $this->admin_site->body .= "<tr class=" . $this->admin_site->get_row_color() . " >\n\t<td valign=top align=right class=medium_font>\n\t
				{\$classified_id}</td>\n\t";
            $this->admin_site->row_count++;
            $this->admin_site->body .= "<td class=medium_font>\n\tThe listing id data.\n\t</td>\n</tr>\n";
            $this->admin_site->body .= "<tr class=" . $this->admin_site->get_row_color() . " >\n\t<td valign=top align=right class=medium_font>\n\t
				{\$description}</td>\n\t";
            $this->admin_site->row_count++;
            $this->admin_site->body .= "<td class=medium_font>\n\tDescription data for this listing.\n\t</td>\n</tr>\n";

            $this->admin_site->body .= "<tr class=" . $this->admin_site->get_row_color() . " >\n\t<td valign=top align=right class=medium_font>\n\t
			{\$url_1}</td>\n\t";
            $this->admin_site->row_count++;
            $this->admin_site->body .= "<td class=medium_font>URL 1, as entered for this listing.\n\t</td>\n</tr>\n";
            $this->admin_site->body .= "<tr class=" . $this->admin_site->get_row_color() . " >\n\t<td valign=top align=right class=medium_font>\n\t
			{\$url_2}</td>\n\t";
            $this->admin_site->row_count++;
            $this->admin_site->body .= "<td class=medium_font>URL 2, as entered for this listing.\n\t</td>\n</tr>\n";
            $this->admin_site->body .= "<tr class=" . $this->admin_site->get_row_color() . " >\n\t<td valign=top align=right class=medium_font>\n\t
			{\$url_3}</td>\n\t";
            $this->admin_site->row_count++;
            $this->admin_site->body .= "<td class=medium_font>URL 3, as entered for this listing.\n\t</td>\n</tr>\n";

            $this->admin_site->body .= "<tr class=" . $this->admin_site->get_row_color() . " >\n\t<td valign=top align=right class=medium_font>\n\t
				{\$contact}</td>\n\t";
            $this->admin_site->row_count++;
            $this->admin_site->body .= "<td class=medium_font>\n\tName of user who placed the listing.\n\t</td>\n</tr>\n";
            $this->admin_site->body .= "
	</table>
</div>
</fieldset>\n";
            $this->admin_site->body .= "</form>\n";
        }



        $this->admin_site->display_page();
    }

    function display_addon_flyers()
    {
        $admin = 1;
        include GEO_BASE_DIR . 'get_common_vars.php';

        if (isset($_REQUEST["c"])) {
            $choice_id = $_REQUEST["c"];
            if ($choice_id) {
                $sql_query = "delete from " . $this->admin_site->choices_table . "
					where choice_id = " . $choice_id;
                $result = $this->db->Execute($sql_query);
                //echo $sql_query."<br>\n";
                if (!$result) {
                    return false;
                }
            }
        }



        $sql_query = "select * from " . $this->admin_site->ad_configuration_table;
        $result = $this->db->Execute($sql_query);
        if (!$result) {
            $this->admin_site->site_error($this->db->ErrorMsg());
            return false;
        } elseif ($result->RecordCount() == 1) {
            $this->admin_site->body .= $admin->getUserMessages();
            $show = $result->FetchRow();
            if (!$this->admin_site->admin_demo()) {
                $this->admin_site->body .= "<form action=index.php?mc=listing_setup&page=addon_flyers method=post>\n";
            }
            $this->admin_site->body .= "
<fieldset>
<legend>Current Image Choices for Seller</legend>
<div class='table-responsive'>
	<table class='table table-hover table-striped table-bordered'>";
                //$this->admin_site->title = "Listing Setup > Flyers Setup";

                //Add a sales flyer template
                //displaying the information you select in it through the form below.  Add your template in the space below using the
                //  field replacement labels listed below.  This template only controls the placement of data fields within the table you provide
                //  in the box below.  All data placed within the template is pulled from the users and listing information.<br><br>

                $this->admin_site->body .= "<thead><tr class=col_hdr_top>\n\t<td class=col_hdr_left><b>Image Title (displayed to seller)</b></td><td class=col_hdr_left colspan=\"2\"><b>Image location</b></td></tr></thead>\n";
                $this->admin_site->body .= "<tr class=" . $this->admin_site->get_row_color() . " >\n\t<td align=left class=medium_font>\n\tFirst Image in Listing</td><td align=left class=medium_font>\n\t[ within listing ]</td><td></td>";
                $sql_query = "select * from " . $this->admin_site->choices_table . " where type_of_choice = 13";
                $choices_result = $this->db->Execute($sql_query);
            if (!$choices_result) {
                //  return false;
            } elseif ($choices_result->RecordCount() > 0) {
                while ($show_choice = $choices_result->FetchRow()) {
                    $this->admin_site->body .= "<tr class=" . $this->admin_site->get_row_color() . ">\n\t<td align=left class=medium_font>\n\t";
                    $this->admin_site->body .= $show_choice["display_value"] . "</td>\n\t";
                    $this->admin_site->body .= "<td align=left class=medium_font>" . $admin->geo_templatesDir() . "<em>[Template Set]</em>/external/" . $show_choice["value"] . "</td><td>" . geoHTML::addButton('delete', "index.php?mc=listing_setup&page=addon_flyers&c=" . $show_choice["choice_id"] . "&auto_save=1", false, '', 'lightUpLink mini_cancel') . "</td>\n</tr>\n";
                }
            }
                $this->admin_site->body .= "
	</table>
</div>
</fieldset>
<fieldset>
<legend>Add a New Image Choice</legend>
<div class='table-responsive'>
	<table class='table table-hover table-striped table-bordered'>";
                $this->admin_site->body .= "<thead><tr class=col_hdr_top>\n\t<td class=col_hdr_left><b>Image Title</b></td><td class=col_hdr_left colspan=\"2\"><b>Image location (URL)</b></td></tr></thead>\n";
                $this->admin_site->body .= "<tr class=" . $this->admin_site->get_row_color() . " >\n\t<td align=left class=medium_font>\n\t<input type=text name=b[new_image_name] size=30></td><td align=left class=medium_font>\n\t" . $admin->geo_templatesDir() . "<em>[Template Set]</em>/external/<input type=text name=b[new_image_value] size=60></td></tr>\n";
                $this->admin_site->body .= "
	</table>
	</div>";

            if (!$this->admin_site->admin_demo()) {
                $this->admin_site->body .= "<div class='center'><input type=submit name=\"auto_save\" value=\"Add Image Choice\"></div>";
            }

            $this->admin_site->body .= "
</fieldset>
<fieldset>
<legend>Available Flyer Template Tags</legend>
<div class='table-responsive'>
	<table class='table table-hover table-striped table-bordered'>";

            $this->admin_site->body .= "<thead><tr class=col_hdr_top>\n\t<th class=col_hdr_right>Tag</td><th class=col_hdr_left>Description</td></tr></thead>\n";

            $this->admin_site->body .= "<tr class=" . $this->admin_site->get_row_color() . " >\n\t<td valign=top align=right class=medium_font>\n\t
				{\$image}</td>\n\t";
            $this->admin_site->row_count++;
            $this->admin_site->body .= "<td class=medium_font>\n\tPlacement of image chosen for flyer.\n\t</td>\n</tr>\n";
            $this->admin_site->body .= "<tr class=" . $this->admin_site->get_row_color() . " >\n\t<td valign=top align=right class=medium_font>\n\t
				{\$title}</td>\n\t";
            $this->admin_site->row_count++;
            $this->admin_site->body .= "<td class=medium_font>\n\tThe title of the listing.\n\t</td>\n</tr>\n";
            $this->admin_site->body .= "<tr class=" . $this->admin_site->get_row_color() . " >\n\t<td valign=top align=right class=medium_font>\n\t
				{\$address}</td>\n\t";
            $this->admin_site->row_count++;
            $this->admin_site->body .= "<td class=medium_font>\n\tThe address of the user placing listing.\n\t</td>\n</tr>\n";
            $this->admin_site->body .= "<tr class=" . $this->admin_site->get_row_color() . " >\n\t<td valign=top align=right class=medium_font>\n\t
				{\$city}</td>\n\t";
            $this->admin_site->row_count++;
            $this->admin_site->body .= "<td class=medium_font>\n\tThe city of the user placing listing.\n\t</td>\n</tr>\n";
            $this->admin_site->body .= "<tr class=" . $this->admin_site->get_row_color() . " >\n\t<td valign=top align=right class=medium_font>\n\t
				{\$state}</td>\n\t";
            $this->admin_site->row_count++;
            $this->admin_site->body .= "<td class=medium_font>\n\tThe state of the user placing listing.\n\t</td>\n</tr>\n";
            $this->admin_site->body .= "<tr class=" . $this->admin_site->get_row_color() . " >\n\t<td valign=top align=right class=medium_font>\n\t
				{\$zip}</td>\n\t";
            $this->admin_site->row_count++;
            $this->admin_site->body .= "<td class=medium_font>\n\tThe zip of the user placing listing.\n\t</td>\n</tr>\n";
            $this->admin_site->body .= "<tr class=" . $this->admin_site->get_row_color() . " >\n\t<td valign=top align=right class=medium_font>\n\t
				{\$price}</td>\n\t";
            $this->admin_site->row_count++;
            $this->admin_site->body .= "<td class=medium_font>\n\tThe price of the listing item.\n\t</td>\n</tr>\n";
            $this->admin_site->body .= "<tr class=" . $this->admin_site->get_row_color() . " >\n\t<td valign=top align=right class=medium_font>\n\t
				{\$phone_1}</td>\n\t";
            $this->admin_site->row_count++;
            $this->admin_site->body .= "<td class=medium_font>\n\tphone number 1\n\t</td>\n</tr>\n";
            $this->admin_site->body .= "<tr class=" . $this->admin_site->get_row_color() . " >\n\t<td valign=top align=right class=medium_font>\n\t
				{\$phone_2}</td>\n\t";
            $this->admin_site->row_count++;
            $this->admin_site->body .= "<td class=medium_font>\n\tphone number 2\n\t</td>\n</tr>\n";
            if (geoPC::is_ent()) {
                $this->admin_site->body .= "<tr class=" . $this->admin_site->get_row_color() . " >\n\t<td valign=top align=right class=medium_font>\n\t
					{\$optional_field_1}</td>\n\t";
                $this->admin_site->row_count++;
                $this->admin_site->body .= "<td class=medium_font>\n\tthe optional field 1 if you use it\n\t</td>\n</tr>\n";
                $this->admin_site->body .= "<tr class=" . $this->admin_site->get_row_color() . " >\n\t<td valign=top align=right class=medium_font>\n\t
					{\$optional_field_2}</td>\n\t";
                $this->admin_site->row_count++;
                $this->admin_site->body .= "<td class=medium_font>\n\tthe optional field 2 if you use it\n\t</td>\n</tr>\n";
                $this->admin_site->body .= "<tr class=" . $this->admin_site->get_row_color() . " >\n\t<td valign=top align=right class=medium_font>\n\t
					{\$optional_field_3}</td>\n\t";
                $this->admin_site->row_count++;
                $this->admin_site->body .= "<td class=medium_font>\n\tthe optional field 3 if you use it\n\t</td>\n</tr>\n";
                $this->admin_site->body .= "<tr class=" . $this->admin_site->get_row_color() . " >\n\t<td valign=top align=right class=medium_font>\n\t
					{\$optional_field_4}</td>\n\t";
                $this->admin_site->row_count++;
                $this->admin_site->body .= "<td class=medium_font>\n\tthe optional field 4 if you use it\n\t</td>\n</tr>\n";
                $this->admin_site->body .= "<tr class=" . $this->admin_site->get_row_color() . " >\n\t<td valign=top align=right class=medium_font>\n\t
					{\$optional_field_5}</td>\n\t";
                $this->admin_site->row_count++;
                $this->admin_site->body .= "<td class=medium_font>\n\tthe optional field 5 if you use it\n\t</td>\n</tr>\n";
                $this->admin_site->body .= "<tr class=" . $this->admin_site->get_row_color() . " >\n\t<td valign=top align=right class=medium_font>\n\t
					{\$optional_field_6}</td>\n\t";
                $this->admin_site->row_count++;
                $this->admin_site->body .= "<td class=medium_font>\n\tthe optional field 6 if you use it\n\t</td>\n</tr>\n";
                $this->admin_site->body .= "<tr class=" . $this->admin_site->get_row_color() . " >\n\t<td valign=top align=right class=medium_font>\n\t
					{\$optional_field_7}</td>\n\t";
                $this->admin_site->row_count++;
                $this->admin_site->body .= "<td class=medium_font>\n\tthe optional field 7 if you use it\n\t</td>\n</tr>\n";
                $this->admin_site->body .= "<tr class=" . $this->admin_site->get_row_color() . " >\n\t<td valign=top align=right class=medium_font>\n\t
					{\$optional_field_8}</td>\n\t";
                $this->admin_site->row_count++;
                $this->admin_site->body .= "<td class=medium_font>\n\tthe optional field 8 if you use it\n\t</td>\n</tr>\n";
                $this->admin_site->body .= "<tr class=" . $this->admin_site->get_row_color() . " >\n\t<td valign=top align=right class=medium_font>\n\t
					{\$optional_field_9}</td>\n\t";
                $this->admin_site->row_count++;
                $this->admin_site->body .= "<td class=medium_font>\n\tthe optional field 9 if you use it\n\t</td>\n</tr>\n";
                $this->admin_site->body .= "<tr class=" . $this->admin_site->get_row_color() . " >\n\t<td valign=top align=right class=medium_font>\n\t
					{\$optional_field_10}</td>\n\t";
                $this->admin_site->row_count++;
                $this->admin_site->body .= "<td class=medium_font>\n\tthe optional field 10 if you use it\n\t</td>\n</tr>\n";
                $this->admin_site->body .= "<tr class=" . $this->admin_site->get_row_color() . " >\n\t<td valign=top align=right class=medium_font>\n\t
					{\$optional_field_11}</td>\n\t";
                $this->admin_site->row_count++;
                $this->admin_site->body .= "<td class=medium_font>\n\tthe optional field 11 if you use it\n\t</td>\n</tr>\n";
                $this->admin_site->body .= "<tr class=" . $this->admin_site->get_row_color() . " >\n\t<td valign=top align=right class=medium_font>\n\t
					{\$optional_field_12}</td>\n\t";
                $this->admin_site->row_count++;
                $this->admin_site->body .= "<td class=medium_font>\n\tthe optional field 12 if you use it\n\t</td>\n</tr>\n";
                $this->admin_site->body .= "<tr class=" . $this->admin_site->get_row_color() . " >\n\t<td valign=top align=right class=medium_font>\n\t
					{\$optional_field_13}</td>\n\t";
                $this->admin_site->row_count++;
                $this->admin_site->body .= "<td class=medium_font>\n\tthe optional field 13 if you use it\n\t</td>\n</tr>\n";
                $this->admin_site->body .= "<tr class=" . $this->admin_site->get_row_color() . " >\n\t<td valign=top align=right class=medium_font>\n\t
					{\$optional_field_14}</td>\n\t";
                $this->admin_site->row_count++;
                $this->admin_site->body .= "<td class=medium_font>\n\tthe optional field 14 if you use it\n\t</td>\n</tr>\n";
                $this->admin_site->body .= "<tr class=" . $this->admin_site->get_row_color() . " >\n\t<td valign=top align=right class=medium_font>\n\t
					{\$optional_field_15}</td>\n\t";
                $this->admin_site->row_count++;
                $this->admin_site->body .= "<td class=medium_font>\n\tthe optional field 15 if you use it\n\t</td>\n</tr>\n";
                $this->admin_site->body .= "<tr class=" . $this->admin_site->get_row_color() . " >\n\t<td valign=top align=right class=medium_font>\n\t
					{\$optional_field_16}</td>\n\t";
                $this->admin_site->row_count++;
                $this->admin_site->body .= "<td class=medium_font>\n\tthe optional field 16 if you use it\n\t</td>\n</tr>\n";
                $this->admin_site->body .= "<tr class=" . $this->admin_site->get_row_color() . " >\n\t<td valign=top align=right class=medium_font>\n\t
					{\$optional_field_17}</td>\n\t";
                $this->admin_site->row_count++;
                $this->admin_site->body .= "<td class=medium_font>\n\tthe optional field 17 if you use it\n\t</td>\n</tr>\n";
                $this->admin_site->body .= "<tr class=" . $this->admin_site->get_row_color() . " >\n\t<td valign=top align=right class=medium_font>\n\t
					{\$optional_field_18}</td>\n\t";
                $this->admin_site->row_count++;
                $this->admin_site->body .= "<td class=medium_font>\n\tthe optional field 18 if you use it\n\t</td>\n</tr>\n";
                $this->admin_site->body .= "<tr class=" . $this->admin_site->get_row_color() . " >\n\t<td valign=top align=right class=medium_font>\n\t
					{\$optional_field_19}</td>\n\t";
                $this->admin_site->row_count++;
                $this->admin_site->body .= "<td class=medium_font>\n\tthe optional field 19 if you use it\n\t</td>\n</tr>\n";
                $this->admin_site->body .= "<tr class=" . $this->admin_site->get_row_color() . " >\n\t<td valign=top align=right class=medium_font>\n\t
					{\$optional_field_20}</td>\n\t";
                $this->admin_site->row_count++;
                $this->admin_site->body .= "<td class=medium_font>\n\tthe optional field 20 if you use it\n\t</td>\n</tr>\n";
            }
            $this->admin_site->body .= "<tr class=" . $this->admin_site->get_row_color() . " >\n\t<td valign=top align=right class=medium_font>\n\t
				{\$classified_id}</td>\n\t";
            $this->admin_site->row_count++;
            $this->admin_site->body .= "<td class=medium_font>\n\tThe listing id data.\n\t</td>\n</tr>\n";
            $this->admin_site->body .= "<tr class=" . $this->admin_site->get_row_color() . " >\n\t<td valign=top align=right class=medium_font>\n\t
				{\$description}</td>\n\t";
            $this->admin_site->row_count++;
            $this->admin_site->body .= "<td class=medium_font>\n\tDescription data for this listing.\n\t</td>\n</tr>\n";

            $this->admin_site->body .= "<tr class=" . $this->admin_site->get_row_color() . " >\n\t<td valign=top align=right class=medium_font>\n\t
			{\$url_1}</td>\n\t";
            $this->admin_site->row_count++;
            $this->admin_site->body .= "<td class=medium_font>URL 1, as entered for this listing.\n\t</td>\n</tr>\n";
            $this->admin_site->body .= "<tr class=" . $this->admin_site->get_row_color() . " >\n\t<td valign=top align=right class=medium_font>\n\t
			{\$url_2}</td>\n\t";
            $this->admin_site->row_count++;
            $this->admin_site->body .= "<td class=medium_font>URL 2, as entered for this listing.\n\t</td>\n</tr>\n";
            $this->admin_site->body .= "<tr class=" . $this->admin_site->get_row_color() . " >\n\t<td valign=top align=right class=medium_font>\n\t
			{\$url_3}</td>\n\t";
            $this->admin_site->row_count++;
            $this->admin_site->body .= "<td class=medium_font>URL 3, as entered for this listing.\n\t</td>\n</tr>\n";

            $this->admin_site->body .= "<tr class=" . $this->admin_site->get_row_color() . " >\n\t<td valign=top align=right class=medium_font>\n\t
				{\$contact}</td>\n\t";
            $this->admin_site->row_count++;
            $this->admin_site->body .= "<td class=medium_font>\n\tName of user who placed the listing.\n\t</td>\n</tr>\n";

            $this->admin_site->body .= "</table>
	</div>
</fieldset>";
            $this->admin_site->body .= "</form>\n";
        }

        $this->admin_site->display_page();
    }

    function update_addon_signs()
    {
        $admin = 1;
        include GEO_BASE_DIR . 'get_common_vars.php';

        //print_r($_REQUEST["b"]);
        $info = $_REQUEST["b"];
        if (isset($info)) {
            if (strlen($info["new_image_name"]) > 0) {
                //enter new image
                $sql_query = "insert into " . $this->admin_site->choices_table . "
					(type_of_choice,display_value,value)
					values
					(14,\"" . $info["new_image_name"] . "\",\"" . $info["new_image_value"] . "\")";
                $result = $this->db->Execute($sql_query);
                //echo $sql_query."<br>\n";
                if (!$result) {
                    $admin->userError("Settings NOT saved, DB error.  Debug: " . $this->db->ErrorMsg());
                    return false;
                }
            } else {
                $admin->userError("Settings NOT saved, please enter an image title.");
                return false;
            }
            return true;
        } elseif ($_REQUEST['c']) {
            //deleting an image choice
            return true;
        }
        return false;
    }

    function update_addon_flyers()
    {
        $admin = 1;
        include GEO_BASE_DIR . 'get_common_vars.php';

        $info = $_REQUEST["b"];
        if ($info) {
            if (strlen($info["new_image_name"]) > 0) {
                //enter new image
                $this->sql_query = "insert into " . $this->admin_site->choices_table . "
					(type_of_choice,display_value,value)
					values
					(13,\"" . $info["new_image_name"] . "\",\"" . $info["new_image_value"] . "\")";
                $result = $this->db->Execute($this->sql_query);
                //echo $sql_query."<br>\n";
                if (!$result) {
                    $admin->userError("Settings NOT saved, DB Error.  Debug: " . $this->db->ErrorMsg());
                    return false;
                }
            } else {
                $admin->userError('Image title required.');
                return false;
            }
            return true;
        } elseif ($_REQUEST['c']) {
            //deleting an image choice
            return true;
        }
        return false;
    }

    function init_text($language_id)
    {
        $return_var['my_account_links_label'] = array (
            'name' => 'My Account Links Label',
            'desc' => 'Text for Signs/Flyers link in the My Account Links module',
            'type' => 'input',
            'default' => 'Print Signs and Flyers'
        );
        $return_var['my_account_links_icon'] = array (
            'name' => 'My Account Links Icon',
            'desc' => 'Icon for Signs/Flyers link in the My Account Links module (complete img tag)',
            'type' => 'textarea',
            'default' => ''
        );

        $return_var['tpl_search_listing_id'] = array (
                'name' => 'Listing ID label',
                'desc' => '',
                'type' => 'input',
                'default' => 'Search for Listing ID:',
                'section' => 'Template Text'
        );

        $return_var['tpl_for_sale'] = array (
                'name' => 'For Sale header',
                'desc' => '',
                'type' => 'input',
                'default' => 'FOR SALE',
                'section' => 'Template Text'
        );

        return $return_var;
    }
}
