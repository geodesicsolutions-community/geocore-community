<?php

// admin_text_management_class.php
/**************************************************************************
Geodesic Classifieds & Auctions Platform 18.02
Copyright (c) 2001-2018 Geodesic Solutions, LLC
All rights reserved
http://geodesicsolutions.com
see license attached to distribution
**************************************************************************/
##########GIT Build Data##########
##
## File Changed In GIT Commit:
## ##    16.09.0-109-g68fca00
##
##################################

class Text_badwords_management extends Admin_site
{

    var $internal_error_message = "There was an internal error";
    var $data_error_message = "Not enough data to complete request";
    var $page_text_error_message = "No text connected to this page";
    var $no_pages_message = "No pages to list";

    var $text_management_title_message = "Site Setup > Badwords";
    var $text_management_instruction_message = "You can control the badwords the software searches for in user
		entered text through this administration tool.  Enter a new badword and what text to possibly replace it with
		in the space next to it.  If the badword replacement is left blank the badword will be removed from user entered
		text with no replacement. To remove a badword
		from the list just click delete next to it.";

    var $badword_error;

    function display_badword_list($db = 0)
    {
        $sql_query = "select * from " . $this->badwords_table . " order by badword ASC";
        $result = $this->db->Execute($sql_query);

        if (PHP5_DIR) {
            $menu_loader = geoAdmin::getInstance();
        } else {
            $menu_loader =& geoAdmin::getInstance();
        }

        if (!$result) {
            trigger_error("ERROR SQL: " . $this->db->ErrorMsg());
            $menu_loader->userError("Internal error. Please contact <a href='http://www.geodesicsolutions.com/support/index.htm'>support</a>.");
            $this->body .= $menu_loader->getUserMessages();
            return false;
        } else {
            if ($this->badword_error) {
                $menu_loader->userError($this->badword_error);
            }

            $this->body .= $menu_loader->getUserMessages();

            if (!$this->admin_demo()) {
                $this->body .= "<form action=index.php?mc=site_setup&page=main_badwords method=post class='form-horizontal form-label-left'>\n";
            } else {
                $this->body .= "<div class='form-horizontal'>";
            }

            $this->body .= "
			<fieldset id='BadwordForm'>
				<legend>Enter New Badword</legend><div class='table-responsive'><table cellpadding=3 cellspacing=1 border=0 class='table table-hover table-striped table-bordered'>\n";
            $this->body .= "<thead><tr class=col_hdr_top>\n\t<td class=col_hdr_left>\n\t<b>Badword</b>\n\t</td>\n\t";
            $this->body .= "<td class=col_hdr_left>\n\t<b>Replace With</b>\n\t</td>\n\t";
            $this->body .= "<td class=col_hdr>\n\t<b>Entire Word</b>\n\t</td>\n\t";
            $this->body .= "<td class=col_hdr>\n\t&nbsp;\n\t</td>\n</tr>\n";
            $this->body .= "<tr>\n\t<td>\n\t<input type=text name=b[badword] size=30 maxsize=30 class='form-control col-md-7 col-xs-12'>\n\t</td>\n\t";
            $this->body .= "<td>\n\t<input type=text name=b[badword_replacement] size=30 maxsize=30 class='form-control col-md-7 col-xs-12'>\n\t</td>\n\t";
            $this->body .= "<td align=center>\n\t<input type=checkbox name=b[entire_word] value=1>\n\t</td>\n\t";
            if (!$this->admin_demo()) {
                $this->body .= "<td class='center'>\n\t<input type=submit name=\"auto_save\" value=\"Save\">\n\t</td>\n";
            }
            $this->body .= "</tr></thead>\n";

            $this->body .= "</table>
			<div class='page_note'><strong>NOTE:</strong> \"Entire word\" signals for the software to look for the word as a whole.<br /><br /> <strong>EXAMPLE:</strong> If \"Entire word\" has been checked and the text 'abc' is to be replaced by 'xyz', then... the text 'abc' <strong>will be</strong> replaced. However, 'abcdefg' will remain <strong>unchanged</strong>.
			</div></fieldset>";

            $this->body .= "<fieldset id='BadwordList'>
				<legend>Current Badword List</legend>
				<div class='table-responsive'>
				<table cellpadding=3 cellspacing=1 border=0 class='table table-hover table-striped table-bordered'><thead>\n";
            $this->body .= "<tr class=col_hdr_top>\n\t<td class=col_hdr_left>\n\t<b>Badword</b>\n\t</td>\n\t";
            $this->body .= "<td class=col_hdr_left>\n\t<b>Replace With</b>\n\t</td>\n\t";
            $this->body .= "<td class=col_hdr>\n\t<b>Entire Word</b>\n\t</td>\n\t";
            $this->body .= "<td class=col_hdr>\n\t&nbsp;\n\t</td>\n</tr></thead>\n";

            if ($result->RecordCount() > 0) {
                $this->row_count = 0;
                while ($show = $result->FetchRow()) {
                    $this->display_this_badword($show);
                    $this->row_count++;
                }
            } else {
                $this->body .= "<tbody><tr>\n\t<td colspan=4><div class=page_note_error>There are currently no badwords.</div></td>\n</tr>\n";
            }
            $this->body .= "</tbody></table></div></fieldset>";

            $this->body .= ($this->admin_demo()) ? '</div>' : "</form>\n";
            return true;
        }
    } //end of function display_page_messages

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    function display_this_badword($text)
    {

        $this->body .= "<tr class=" . $this->get_row_color() . ">\n\t<td class=medium_font>\n\t<b>";
        $this->body .= htmlspecialchars($text["badword"]) . "</b> \n\t</td>\n\t";
        $this->body .= "<td class=medium_font>\n\t" . htmlspecialchars($text["badword_replacement"]) . " \n\t</td>\n\t";
        $this->body .= "<td class=medium_font align=center>\n\t" . (($text["entire_word"]) ? "Yes" : "No") . "\n\t</td>\n\t";

        $delete_button = geoHTML::addButton('Delete', 'index.php?mc=site_setup&page=main_badwords&c=' . $text["badword_id"] . '&auto_save=1', false, '', 'lightUpLink mini_cancel');
        $this->body .= "<td width=100 class='center'>" . $delete_button . "</td>\n\t";
        $this->body .= "</tr>\n";
    } //end of function display_text_message

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    function insert_badword($db = 0, $badword_info = 0)
    {
        if ($badword_info) {
            $entire_word = ($badword_info['entire_word'] == 1) ? 1 : 0;
            $sql_query = "select * from " . $this->badwords_table . " where badword = ? AND entire_word = ?";
            $result = $this->db->Execute($sql_query, array ($badword_info["badword"], $entire_word));
            if (!$result) {
                $this->error_message = $this->internal_error_message;
                $this->site_error($db->ErrorMsg());
                return false;
            } elseif ($result->RecordCount() == 0) {
                $sql_query = "insert into " . $this->badwords_table . "
					(badword,badword_replacement,entire_word)
					values
					(?,?,?)";

                $result = $db->Execute($sql_query, array ($badword_info["badword"], $badword_info["badword_replacement"], $entire_word));
                if (!$result) {
                    $this->error_message = $this->internal_error_message;
                    return false;
                } else {
                    return true;
                }
            } else {
                $this->badword_error = "That word already exists in the badword list.";
                return false;
            }
        } else {
            $this->error_message = $this->internal_error_message;
            return false;
        }
    } //end of function insert_badword

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    function delete_badword($db = 0, $badword_id = 0)
    {
        if ($badword_id) {
            $sql_query = "delete from " . $this->badwords_table . "
				where badword_id = " . $badword_id;
            $result = $this->db->Execute($sql_query);
            if (!$result) {
                $this->error_message = $this->internal_error_message;
                return false;
            } else {
                return true;
            }
        } else {
            $this->error_message = $this->internal_error_message;
            return false;
        }
    } //end of function delete_badword

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    function badword_management_error()
    {
        $this->body .= "<table cellpadding=5 cellspacing=1 border=0>\n";
        $this->body .= "<tr>\n\t<td>There was an error</td>\n</tr>\n";
        if ($this->error_message) {
            $this->body .= "<tr>\n\t<td>" . $this->error_message . "</td>\n</tr>\n";
        }
        $this->body .= "</table>\n";
    } //end of function badword_management_error

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
    function display_main_badwords()
    {
        $this->display_badword_list($this->db);
        $this->display_page();
    }
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
    function update_main_badwords()
    {

        if (isset($_REQUEST["b"])) {
            return $this->insert_badword($this->db, $_REQUEST["b"]);
        } elseif (isset($_REQUEST['c'])) {
            return $this->delete_badword($this->db, $_REQUEST["c"]);
        } else {
            return false;
        }
    }
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
} //end of class Text_management
