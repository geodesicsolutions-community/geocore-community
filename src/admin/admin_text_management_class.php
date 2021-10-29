<?php

// admin_text_management_class.php
##########GIT Build Data##########
##
## File Changed In GIT Commit:
## 
##    17.12.0-5-g5a388b4
##
##################################

class Text_management extends Admin_site
{

    var $internal_error_message = "There was an internal error";
    var $data_error_message = "Not enough data to complete request";
    var $page_text_error_message = "No text connected to this page";
    var $no_pages_message = "No pages to list";
    var $no_messages_on_page_message = "There are no labels attached to this page.";
    var $text_management_title_message = "Site Text Management";
    var $text_management_instruction_message = "";
    var $debug_text = 0;
    var $debug_search = 0;
    var $debug_languages = 0;

    function display_page_messages($db, $page_id = 0, $language_id = 1, $section_index = '')
    {
        //echo $language_id." is language id<br>\n";

        if ($page_id) {
            $page_name = $this->get_page_name($db, $page_id);
            $language_name = $this->get_language_name($db, $language_id);
            $this->body .= "<div class=\"page-title1\"><i class=\"fa fa-file\"></i><span class=\"visible-lg-inline\"> Page:</span> <span class=\"color-primary-two\">" . $page_name . "</span>&nbsp;&nbsp;<i class=\"fa fa-language\"></i><span class=\"visible-lg-inline\"> Language:</span> <span class=\"color-primary-one\">{$language_name}</span></div>";
            $this->body .= "<div style=\"padding: 5px;\"><a href=\"index.php?mc=pages_sections&page=sections" . $section_index . "_page&b=" . $page_id . "\" class=\"back_to\"><i class=\"fa fa-backward\"></i> Back to " . $page_name . " Details</a></div>";
            $this->body .= "<fieldset id='TextDisplayed'><legend>Text Fields</legend><div class=\"table-responsive\"><table cellpadding=\"3\" cellspacing=\"1\" border=\"0\" width=\"100%\" class=\"table table-hover table-striped table-bordered\">\n";
//          $this->title = " ({$this->text_management_title_message} - for {$language_name} language)";
            //$this->description = "Labels attached to the <b>".$language_name."</b> language of the <b>".$page_name."</b> section";

            //get current pages messages
            $this->sql_query = "SELECT * FROM " . $this->pages_text_table . " WHERE
				`page_id` = " . $page_id . " " .
                (!geoMaster::is('classifieds') || !geoMaster::is('auctions') ? " and classauctions = 0 " : "") .
                " ORDER BY display_order > 0 DESC, display_order ASC, message_id ASC"; //order: first those that have a display_order, in order of it; then use ID order

            if ($this->debug_text) {
                echo $this->sql_query . "<br>\n";
            }
            $page_result = $this->db->Execute($this->sql_query);
            if (!$page_result) {
                $this->error_message = $this->internal_error_message;
                $this->site_error($this->db->ErrorMsg());
                return false;
            } elseif ($page_result->RecordCount() > 0) {
                $this->row_count = 0;
                $this->body .= "<thead><tr><td style='width: 25%; text-align: center;'><b><span>Text Field and Explanation</span></b></td>";
                $this->body .= "<td style='text-align: center;'><b>Text Displayed when <span class=\"color-primary-one\">\"" . $language_name . "\"</span> is Selected</b></td>";
                $this->body .= "<td class='hidden-xs' style='width: 10%;'>&nbsp;</td></tr></thead><tbody>";
                while ($show_page_messages = $page_result->FetchRow()) {
                    //get the current value of this message within this language
                    $this->sql_query = "SELECT * FROM " . $this->pages_text_languages_table . " where
						page_id = " . $page_id . " and text_id = " . $show_page_messages["message_id"] . " and language_id = " . $language_id;
                    //echo $this->sql_query."<br>\n";
                    $text_result = $this->db->Execute($this->sql_query);
                    if (!$text_result) {
                        $this->error_message = $this->internal_error_message;
                        $this->site_error($this->db->ErrorMsg());
                        return false;
                    } elseif ($text_result->RecordCount() == 1) {
                        $this->get_configuration_data($db);
                        $text_label_name = stripslashes(urldecode(urldecode($show_page_messages["name"])));
                        $text_label_desc = stripslashes(urldecode(urldecode($show_page_messages["description"])));
                        $show_text = $text_result->FetchRow();

                        $append = '';
                        $search_terms = array('error','label','header');
                        foreach ($search_terms as $name) {
                            //this will only be used for optional fields
                            if (preg_match('/' . $name . '/', $text_label_name)) {
                                $append = ' - ' . $name;
                            }
                        }
                        if (
                            !preg_match('/registration/', $text_label_name)
                            && preg_match('/optional field/', $text_label_name)
                        ) {
                            //Site Wide Optional Fields
                            for ($i = 1; $i < 21; $i++) {
                                if (preg_match('/ ' . $i . ' /', $text_label_name)) {
                                    $name = $this->configuration_data['optional_field_' . $i . '_name'] . $append;
                                    $message = $text_label_name . '<br>' . $text_label_desc;
                                }
                            }
                        } elseif (
                            preg_match('/registration/', $text_label_name)
                            && preg_match('/optional field/', $text_label_name)
                        ) {
                            //Registration Optional Fields
                            $sql_query = "select * from " . $this->registration_configuration_table;
                            $result = $this->db->Execute($sql_query);
                            if (!$result) {
                                $this->site_error($this->db->ErrorMsg());
                                return false;
                            } elseif ($result->RecordCount() == 1) {
                                $registration_configuration = $result->FetchRow();
                            }
                            for ($i = 1; $i < 11; $i++) {
                                if (preg_match('/ ' . $i . ' /', $text_label_name)) {
                                    $name = $registration_configuration['registration_optional_' . $i . '_field_name'] . $append;
                                    $message = $text_label_name . '<br>' . $text_label_desc;
                                }
                            }
                        } else {
                            $name = $text_label_name;
                            $message = $text_label_desc;
                        }
                        $dontShow = 0;
                        if (preg_match('/Optional Field [0-9]{1,2} - header/', $name) && !geoPC::is_ent()) {
                            $dontShow = 1;
                        }
                        if (defined('IAMDEVELOPER')) {
                            $name .= " ({$show_page_messages["message_id"]})";//show text ID for developers
                        }
                        if (!$dontShow) {
                            $edit = geoHTML::addButton('Edit', "index.php?mc=pages_sections&page=sections" . $section_index . "_edit_text&b=" . $page_id . "&c=" . $show_page_messages["message_id"] . "&l=" . $language_id);
                            $this->body .= "<tr class=\"" . $this->get_row_color() . "\">\n\t<td align=\"right\" valign=\"top\" class=\"medium_font\" style='white-space: normal;'>"; //explicitly override nowrap from .table-responsive
                            $this->body .= "<b>$name</b><br />
								<span class=\"small_font\">$message</span><br />
								<div class='visible-xs-block'>$edit</div>
								</td>\n\t";

                            $this->body .= "<td valign=\"top\" class=\"medium_font\">\n\t<textarea rows=\"2\" readonly disabled=\"disabled\" style=\"margin: 5px auto; width: 100%;\">" . geoString::specialChars(stripslashes(urldecode($show_text["text"]))) . "</textarea></td>\n\t";
                            $this->body .= "<td valign=\"top\" class='hidden-xs' style='text-align: center;'>" . $edit . "</td>\n</tr>\n";
                        }
                    } else {
                        $this->body .= "<tr class=\"" . $this->get_row_color() . "\">\n\t<td colspan=\"3\" class=medium_error_font" . $this->no_messages_on_page_message . " \n\t</td>\n</tr>\n";
                    }
                    $this->row_count++;
                    //remove before release
                }
            } else {
                $this->body .= "<tr>\n\t<td colspan=\"3\" class=\"medium_font\" align=\"center\"><br><br><br><b>There is no text attached to this page and language.</b><br><br><br><br></td>\n</tr>\n";
            }
            $this->body .= "</tbody></table></div></fieldset>\n";
            $this->body .= "<div style=\"padding: 5px;\"><a href=\"index.php?mc=pages_sections&page=sections" . $section_index . "_page&b=" . $page_id . "\" class=\"back_to\"><i class=\"fa fa-backward\"></i> Back to " . $page_name . " Details</a></div>";

            return true;
        } else {
            $this->error_message = $this->internal_error_message;
            return false;
        }
    } //end of function display_page_messages

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    function edit_text_message($db, $text_id = 0, $language_id = 1, $page_id = 0, $section_index = '')
    {
        $this->body .= geoAdmin::m();
        if ($text_id) {
            $this->sql_query = "SELECT * FROM " . $this->pages_text_table . " where
				message_id = " . $text_id;
            $result = $this->db->Execute($this->sql_query);
            if (!$result) {
                $this->error_message = $this->internal_error_message;
                $this->site_error($this->db->ErrorMsg());
                return false;
            } elseif ($result->RecordCount() == 1) {
                $text = $result->FetchRow();
                //get the text attached to this language
                $this->sql_query = "select text from " . $this->pages_text_languages_table . " where
					text_id = " . $text_id . " and language_id=" . $language_id;
                $language_result = &$this->db->Execute($this->sql_query);
                if (!$language_result) {
                    $this->error_message = $this->internal_error_message;
                    $this->site_error($this->db->ErrorMsg());
                    return false;
                } elseif ($language_result->RecordCount() == 1) {
                    $this->sql_query = "select charset from " . $this->site_configuration_table;
                    $result = $this->db->Execute($this->sql_query);
                    if (!$result) {
                        $charset = "none";
                    } elseif ($result->RecordCount() == 1) {
                        $show_configuration = $result->FetchRow();
                        $charset = $show_configuration['charset'];
                    }

                    require_once('admin_wysiwyg_config.php');
                    $this->head_html .= wysiwyg_configuration::getHeaderText('textManager');
                    $show_language_message = $language_result->FetchRow();
                    if (!$this->admin_demo()) {
                        $this->body .= "<form action=\"index.php?mc=pages_sections&page=sections" . $section_index . "_edit_text&b=" . $text["page_id"] . "&c=" . $text_id . "&l=" . $language_id . "\" method=\"post\" class=\"form-horizontal form-label-left\">";
                    } else {
                        $this->body .= "<div class='form-horizontal'>";
                    }
                    $language_name = $this->get_language_name($db, $language_id);
                    $page_name = $this->get_page_name($db, $page_id);
                    $this->body .= "<div class=\"page-title1\"><i class=\"fa fa-file\"></i><span class=\"visible-lg-inline\"> Page:</span> <span class=\"color-primary-two\">" . $page_name . "</span>&nbsp;&nbsp;<i class=\"fa fa-language\"></i><span class=\"visible-lg-inline\"> Language:</span> <span class=\"color-primary-one\">{$language_name}</span></div>";
                    $this->body .= "<div style=\"padding: 5px;\"><a href=\"index.php?mc=pages_sections&page=sections" . $section_index . "_edit_text&b=" . $text["page_id"] . "&l=" . $language_id . "\" class=\"back_to\"><i class=\"fa fa-backward\"></i> Back to " . $page_name . " Text Fields</a></div>";

                    $this->body .= "<fieldset id='TextField'><legend>Text Field</legend><div class=\"x_content\">";

                    $wysiwyg_on_off = ($this->db->get_site_setting('use_admin_wysiwyg')) ? '<br /><span class="small_font">(<a href="#" onclick="gjWysiwyg.toggleTinyEditors(); return false;">Show/Hide Editor</a>)</span>' : '';

                    $this->body .= "
					<div class='form-group'>
					<label class='control-label col-md-3 col-sm-3 col-xs-12'>Text Field: </label>
					  <div class='col-md-6 col-sm-6 col-xs-12'>
						<span class='vertical-form-fix'>" . urldecode($text["name"]) . "</span>
					  </div>
					</div>
					";

                    $this->body .= "
					<div class='form-group'>
					<label class='control-label col-md-3 col-sm-3 col-xs-12'>Attached To: </label>
					  <div class='col-md-6 col-sm-6 col-xs-12'>
					  <span class=\"color-primary-two vertical-form-fix\">" . $page_name . "</span>
					  </div>
					</div>
					";

                    $this->body .= "
					<div class='form-group'>
					<label class='control-label col-md-3 col-sm-3 col-xs-12'>Language: </label>
					  <div class='col-md-6 col-sm-6 col-xs-12'>
					  <span class=\"color-primary-one vertical-form-fix\">" . $language_name . "</span>
					  </div>
					</div>
					";

                    $this->body .= "
					<div class='form-group'>
					<label class='control-label col-md-3 col-sm-3 col-xs-12'>Charset: </label>
					  <div class='col-md-6 col-sm-6 col-xs-12'>
						<span class='vertical-form-fix'>" . $charset . " <a href=\"index.php?mc=site_setup&page=main_general_settings\"><i class=\"fa fa-pencil edit-pencil\"></i></a></span>
					  </div>
					</div>
					";

                    $this->body .= "
					<div class='form-group'>
					<label class='control-label col-md-3 col-sm-3 col-xs-12'>Description: </label>
					  <div class='col-md-6 col-sm-6 col-xs-12'>
						<span class='vertical-form-fix'>" . stripslashes(urldecode($text["description"])) . "</span>
					  </div>
					</div>
					";

                    $this->body .= "
					<div class='form-group'>
					<label class='control-label col-md-3 col-sm-3 col-xs-12'>Text Displayed: $wysiwyg_on_off</label>
					  <div class='col-md-8 col-sm-8 col-xs-12'>
					  <textarea name=\"z[text]\" cols=\"75\" rows=\"20\" class=\"textManager\">" . geoString::specialChars(urldecode($show_language_message["text"])) . "</textarea>
					  </div>
					</div>
					";

                    if (!$this->admin_demo()) {
                        $this->body .= "<div class=\"center\"><input type=\"submit\" name='auto_save' value=\"Save\"></div>";
                    }

                    $this->body .= "</div></fieldset>";
                    $this->body .= "<div style=\"padding: 5px;\"><a href=\"index.php?mc=pages_sections&page=sections" . $section_index . "_edit_text&b=" . $text["page_id"] . "&l=" . $language_id . "\" class=\"back_to\"><i class=\"fa fa-backward\"></i> Back to " . $page_name . " Text Fields</a></div>";
                    $this->body .= ($this->admin_demo()) ? '</div>' : "</form>";
                } else {
                    $this->body .= "<table cellpadding=\"3\" cellspacing=\"0\" border=\"0\">\n";
                    $this->body .= "<tr>\n\t<td class=\"very_large_font\"><b>" . $this->text_management_title_message . "</b> </td>\n</tr>\n";
                    $this->body .= "<tr>\n\t<td class=\"medium_font\">There are no messages for this language </td>\n</tr>\n";
                    $this->body .= "</table>\n";
                }
                return true;
            } else {
                $this->error_message = $this->internal_error_message;
                return false;
            }
        } else {
            $this->error_message = $this->internal_error_message;
            return false;
        }
    } //end of function edit_text_message

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    function update_text_message($db, $text_page_id = 0, $text_id = 0, $text_info = 0, $language_id)
    {
        //echo $text_id." is text_id<br>\n";
        //echo $text_page_id." is text_page_id<br>\n";
        //echo $text_info["text"]." is text_info<br>\n";
        if (($text_id) && ($text_page_id) && ($text_info)) {
            $this->sql_query = "update " . $this->pages_text_languages_table . " set text = \"" . urlencode(stripslashes($text_info["text"])) . "\"
				where text_id = " . $text_id . " and language_id = " . $language_id;
            $result = $this->db->Execute($this->sql_query);
            //clear text cache, since cache can be embeded, no way to
            //expire all appropriate pages accurately
            geoCache::clearCache('text');
            //also clear page output cache
            geoCache::clearCache('pages');
            //echo $this->sql_query."<br>\n";
            if (!$result) {
                $this->error_message = $this->internal_error_message;
                $this->site_error($this->db->ErrorMsg());
                return false;
            }

            if (defined('IAMDEVELOPER')) {
                //figure out line to add to CSV file
                $query = "select text_id,pages.page_id,language_id,name,description,langs.text from geodesic_pages_messages as pages, geodesic_pages_messages_languages as langs where pages.message_id = langs.text_id and language_id = '{$language_id}' and `text_id`=$text_id";
                $row = $this->db->GetRow($query);
                $line = geoArrayTools::toCSV($row, true);
                geoAdmin::m('Line for CSV file: <br /><input type="text" value="' . htmlspecialchars($line) . '" readonly="readonly" style="width: 500px;" />', geoAdmin::NOTICE);
                geoAdmin::m('Saved in DB as: <br /><input type="text" value="' . htmlspecialchars(geoString::toDB($text_info["text"])) . '" readonly="readonly" style="width: 500px;" />', geoAdmin::NOTICE);
            }

            return true;
        } else {
            $this->error_message = $this->internal_error_message;
            return false;
        }
    } //end of function update_text_message


    function list_sections($db, $language_id)
    {
        $this->sql_query = "SELECT * FROM " . $this->text_page_table;
        $result = $this->db->Execute($this->sql_query);
        if (!$result) {
            $this->error_message = $this->internal_error_message;
            $this->site_error($this->db->ErrorMsg());
            return false;
        } else {
            if ($result->RecordCount() > 0) {
                //$this->title = "Site Sections";
                $this->row_count = 0;
                while ($show = $result->FetchRow()) {
                    $this->body .= "<tr class=\"" . $this->get_row_color() . "\">\n\t<td>\n\t<a href=\"index.php?mc=pages_sections&page=sections" . $section_index . "_edit_text&b=" . $show["text_page_id"] . "&l=" . $language_id . "\">
						<span class=\"medium_font\">" . $show["page_name"] . "</span>\n\t</td>\n</tr>\n";
                    $this->row_count++;
                }
            } else {
                $this->body .= "<tr>\n\t<td class=\"medium_font\">\n\t" . $this->no_pages_message . " \n\t</td>\n</tr>\n";
            }
            $this->body .= "<tr class=\"row_color_black\">\n\t<td><a href=\"index.php?mc=pages_sections&page=sections" . $section_index . "_edit_text\"><span class=\"medium_font_light\">site message home</span></a>\n\t</td>\n</tr>\n";
        }

        return true;
    } //end of function list_sections

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
//remove before release
    function name_and_description_form($db, $text_id = 0)
    {
        if ($text_id) {
            $this->sql_query = "SELECT * FROM " . $this->pages_text_table . " WHERE message_id = " . $text_id;
            $result = $this->db->Execute($this->sql_query);
            if (!$result) {
                $this->error_message = $this->internal_error_message;
                $this->site_error($this->db->ErrorMsg());
                return false;
            } elseif ($result->RecordCount() == 1) {
                $text = $result->FetchRow();
                if (!$this->admin_demo()) {
                    $this->body .= "<form action=\"index.php?mc=pages_sections&page=text_dev_tools&d=" . $text_id . "\" method=\"post\">\n";
                }
                $this->body .= "<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\">\n";
                $this->body .= "<tr>\n\t<td class=\"medium_font\">\n\tname \n\t</td>\n\t";
                $this->body .= "<td class=\"medium_font\">\n\t<input type=\"text\" name=\"e[name]\" size=\"50\"
					value=\"" . urldecode($text["name"]) . "\"> \n\t</td>\n</tr>\n";
                $this->body .= "<tr>\n\t<td valign=\"top\" class=\"medium_font\">\n\tdescription \n\t</td>\n\t";
                $this->body .= "<td class=\"medium_font\">\n\t
					<textarea name=\"e[description]\" cols=\"50\" rows=\"20\">" . geoString::specialChars(stripslashes(urldecode($text["description"]))) . "</textarea>
					 \n\t</td>\n</tr>\n";

                $this->body .= "<tr>\n\t<td valign=\"top\" class=\"medium_font\">\n\tbelongs to " . $this->get_page_name($db, $text["text_page_id"]) . " \n\t</td>\n\t";
                $this->body .= "<td class=\"medium_font\">\n\t<select name=\"e[page_id]\">\n\t\t";
                $this->sql_query = "SELECT * FROM " . $this->pages_table;
                $page_result = $this->db->Execute($this->sql_query);
                if (!$page_result) {
                    $this->error_message = $this->internal_error_message;
                    $this->site_error($this->db->ErrorMsg());
                    return false;
                } elseif ($page_result->RecordCount() > 0) {
                    while ($show_page = $page_result->FetchRow()) {
                        $this->body .= "<option value=\"" . $show_page["page_id"] . "\"";
                        if ($show_page["page_id"] == $text["page_id"]) {
                            $this->body .= " selected";
                        }
                        $this->body .= ">" . $this->get_page_name($db, $show_page["page_id"]) . "</option>\n\t\t";
                    }
                }
                $this->body .= "</select> \n\t</td>\n</tr>\n";

                $this->body .= "<tr>\n\t<td valign=\"top\" class=\"medium_font\">\n\tdisplay order \n\t</td>\n\t";
                $this->body .= "<td class=\"medium_font\">\n\t<select name=\"e[display_order]\">\n\t\t";
                for ($i = 1; $i < 100; $i++) {
                    $this->body .= "<option ";
                    if ($text["display_order"] == $i) {
                        $this->body .= " selected";
                    }
                    $this->body .= ">" . $i . "</option>\n\t\t";
                }
                $this->body .= "</select> \n\t</td>\n</tr>\n";

                $this->body .= "</td>\n</tr>\n";
                if (!$this->admin_demo()) {
                    $this->body .= "<tr>\n\t<td align=\"center\" colspan=\"2\"><input type=\"submit\" name=\"auto_save\" value=\"Save\">\n\t</td>\n</tr>\n";
                }
                $this->body .= "<tr>\n\t<td align=\"center\" colspan=\"2\"><a href=\"index.php?mc=pages_sections&page=sections" . $section_index . "_page&b=" . $text["page_id"] . "\">back to " . $this->get_page_name($db, $text["page_id"]) . "</a>\n\t</td>\n</tr>\n";
                //$this->body .= "<tr>\n\t<td align=\"center\" colspan=\"2\"><a href=\"index.php?page=sections_page&z=4&b=".$text["page_id"]."&t=".$element_id."\">delete this text element</a>\n\t</td>\n</tr>\n";
                $this->body .= "</table>\n</form>\n";
            } else {
                $this->body .= "nothing returned<br>\n";
            }
                return true;
        } else {
            $this->body .= "no text id";
            return false;
        }
    } //end of function name_and_description_form

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
//remove before release
    function update_message_name_and_description($db, $text_id = 0, $information = 0)
    {
        //echo $text_id." in update_message_name_and_description<br>\n";
        if (($text_id) && ($information)) {
            $this->sql_query = "update " . $this->pages_text_table . " set
				name = \"" . $information["name"] . "\",
				page_id= " . $information["page_id"] . ",
				display_order= " . $information["display_order"] . ",
				description = \"" . $information["description"] . "\"
				where message_id = " . $text_id;
            //echo $this->sql_query."<bR>\n";
            $result = $this->db->Execute($this->sql_query);
            if (!$result) {
                $this->error_message = $this->internal_error_message;
                $this->site_error($this->db->ErrorMsg());
                return false;
            }
            return true;
        } else {
            $this->error_message = $this->internal_error_message;
            return false;
        }
    } //end of function update_message_name_and_description

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
    function delete_text_element($db, $element_id = 0)
    {
        //echo $text_id." in update_message_name_and_description<br>\n";
        if ($element_id) {
            $this->sql_query = "delete from " . $this->pages_text_table . "
				where message_id = " . $element_id;
            //echo $this->sql_query."<bR>\n";
            $result = $this->db->Execute($this->sql_query);
            if (!$result) {
                $this->error_message = $this->internal_error_message;
                $this->site_error($this->db->ErrorMsg());
                return false;
            }

            $this->sql_query = "delete from " . $this->pages_text_languages_table . "
				where text_id = " . $element_id;
            //echo $this->sql_query."<bR>\n";
            $result = $this->db->Execute($this->sql_query);
            //clear text cache, since cache can be embeded, no way to
            //expire all appropriate pages accurately
            geoCache::clearCache('text');
            //also clear page output cache
            geoCache::clearCache('pages');
            if (!$result) {
                $this->error_message = $this->internal_error_message;
                $this->site_error($this->db->ErrorMsg());
                return false;
            }

            return true;
        } else {
            $this->error_message = $this->internal_error_message;
            return false;
        }
    } //end of function update_message_name_and_description

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    function get_page_name($db, $page_id = 0)
    {
        if ($page_id) {
            $this->sql_query = "select name from " . $this->pages_table . " WHERE page_id = " . $page_id;
            //echo $this->sql_query."<br>\n";
            $result = $this->db->Execute($this->sql_query);
            if (!$result) {
                $this->error_message = $this->internal_error_message;
                $this->site_error($this->db->ErrorMsg());
            } elseif ($result->RecordCount() == 1) {
                $show = $result->FetchRow();
                return urldecode($show["name"]);
            } else {
                return "no name";
            }
        } else {
            return "no name";
        }
    } //end of function get_page_name

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    function get_sub_page_name($db, $sub_page_id = 0)
    {
        if ($sub_page_id) {
            $this->sql_query = "select name from " . $this->text_subpages_table . " WHERE sub_page_id = " . $sub_page_id;
            $result = $this->db->Execute($this->sql_query);
            if (!$result) {
                $this->error_message = $this->internal_error_message;
                $this->site_error($this->db->ErrorMsg());
            } elseif ($result->RecordCount() == 1) {
                $show = $result->FetchRow();
                return urldecode($show["name"]);
            } else {
                return "no name";
            }
        } else {
            return "no name";
        }
    } //end of function get_sub_page_name

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    function languages_home($db)
    {
        $this->sql_query = "SELECT * FROM " . $this->pages_languages_table;
        $result = $this->db->Execute($this->sql_query);
        if (!$result) {
            return false;
        }



        $this->body .= "
		  <fieldset id='CurrentLanguages'><legend>Current Languages</legend>
		  <div class=\"table-responsive\">
		    <table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" class=\"table table-hover table-striped table-bordered\">
		    ";

        if ($result->RecordCount() > 0) {
            $this->body .= "

            <thead>
              <tr class=\"col_hdr_top\">
				<td class=\"col_hdr_left\">
                  <b>Language</b>
                </td>
				<td class=\"col_hdr_left\">
                  <b>ID</b>
                </td>
                <td class=\"col_hdr\" align=\"center\">
                  <b>Active/Inactive</b>
                </td>
                <td class=\"col_hdr\" align=\"center\">
                  <b>Status</b>
                </td>
                <td class=\"col_hdr_left\" width=\"100\" colspan='2'>&nbsp;</td>
              </tr>
            </thead>";

            $this->row_count = 0;
            $num_default = 0;
            while ($show = $result->FetchRow()) {
                $num_default += ($show["default_language"]);
                $edit_button = geoHTML::addButton('Edit', 'index.php?mc=languages&page=languages_edit&l=' . $show["language_id"]);
                $this->body .= "
				      <tr class=\"" . $this->get_row_color() . "\">
						<td class=\"medium_font\">\n\t" . $show["language"] . "</td>
				        <td class=\"medium_font\" align=\"center\">\n\t" . $show["language_id"] . "</td>
						<td class=\"medium_font\" align=\"center\">
				          " . (($show["active"]) ? "Active" : "Inactive") . "
				        </td>
				        <td class=\"medium_font\" align=\"center\">
				          " . (($show["default_language"]) ? 'Site Default' : '&nbsp;') . "
				        </td>
				        <td align=\"center\" width=\"100\">" . $edit_button . "</td>";
                if ($show["language_id"] != 1) {
                    $confirm_msg = "Are you sure you want to delete the following language from your database?\\n\\n" . geoString::specialChars($show["language"]);
                    $this->body .= "
				        <td align=\"center\" width=\"100\">
				        	" . geoHTML::addButton('Delete', "index.php?mc=languages&page=languages_delete&l=" . $show["language_id"] . "&auto_save=1", false, '', 'lightUpLink mini_cancel') . "
				        </td>";
                } else {
                    //language_id=1 and therefore cannot delete base language
                    $this->body .= "
			         <td class=\"medium_font\" align=\"center\" width=\"100\">Base Language</td>";
                }

                $this->body .= "
				      </tr>";
                $this->row_count++;
            }
        }

        $this->body .= "
		    <tr>
		      <td colspan=\"100%\" align=\"center\">
		        <div style=\"margin-top: 20px;\">
					<a href=\"index.php?mc=languages&page=languages_new\" class=\"btn btn-success\"><i class=\"fa fa-plus-circle\"></i> Add a New Language</a><br />
					<a href=\"index.php?mc=languages&page=languages_export\" class=\"btn btn-primary btn-xs\"><i class=\"fa fa-upload\"></i> Export Language Data</a><br />
					<a href=\"index.php?mc=languages&page=languages_import\" class=\"btn btn-primary btn-xs\"><i class=\"fa fa-download\"></i> Import Language Data</a>
		        </div>
		      </td>
		    </tr>
		  </table></div></fieldset>";

        return true;
    }

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    function edit_language_form($db, $language_id = 0)
    {
        if (!$language_id) {
            return false;
        }

        $this->sql_query = "SELECT * FROM " . $this->pages_languages_table . " WHERE language_id = " . $language_id;
        $result = $this->db->Execute($this->sql_query);
        if (!$result) {
            $this->error_message = $this->internal_error_message;
            $this->site_error($this->db->ErrorMsg());
        }
        if ($result->RecordCount() != 1) {
            return false;
        } else {
            $show = $result->FetchRow();
        }

      //$this->title = "Languages > Edit Language Details";
        $this->description = "Make any necessary changes to language and click the \"save\" button below";

        $this->body .= "<SCRIPT language=\"JavaScript1.2\">";
        // Set title and text for tooltip
        $this->body .= "Text[1] = [\"Language Name\", \"Language name used throughout your site to identify it.\"]\n
			Text[2] = [\"Site Default Language\", \"Choosing \\\"yes\\\" will set the above language as the default if the user does not choose a language.\"]\n
			Text[3] = [\"Active Language\", \"This language will not be available as a choice on the site's front end until the language is set as active. This allows you to setup and edit the language before releasing it as a language choice for your site.\"]\n";

        //".$this->show_tooltip(3,1)."

        // Set style for tooltip
        //echo "Style[0] = [\"white\",\"\",\"\",\"\",\"\",,\"black\",\"#ffffcc\",\"\",\"\",\"\",,,,2,\"#b22222\",2,24,0.5,0,2,\"gray\",,2,,13]\n";

        $this->body .= "</script>";

        if (!$this->admin_demo()) {
            $this->body .= "<form action=\"index.php?mc=languages&page=languages_edit&l=" . $language_id . "\" class=\"form-horizontal form-label-left\" method=\"post\">\n";
        } else {
            $this->body .= "<div class='form-horizontal'>";
        }

        $this->body .= "<fieldset id='EditLanguage'><legend>Edit Language Settings</legend><div class='x_content'><table cellpadding=\"3\" cellspacing=\"0\" border=\"0\">\n";

        $this->body .= "<div class='form-group'>
		<label class='control-label col-md-4 col-sm-4 col-xs-12'>Language Name: " . $this->show_tooltip(1, 1) . "</label>
			<div class='col-md-6 col-sm-6 col-xs-12'><input type=\"text\" class=\"form-control col-md-7 col-xs-12\" name=\"c[language]\" value=\"" . $show["language"] . "\">
			</div>
		</div>";

        $this->body .= "<div class='form-group'>
		<label class='control-label col-md-4 col-sm-4 col-xs-12'>Site Default Language: " . $this->show_tooltip(2, 1) . "</label>
			<div class='col-md-6 col-sm-6 col-xs-12'><input type=\"radio\" name=\"c[default_language]\" value=\"1\" ";
        if ($show["default_language"] == 1) {
            $this->body .= "checked";
        }
            $this->body .= "> Yes<br><input type=\"radio\" name=\"c[default_language]\" value=\"0\" ";
        if ($show["default_language"] == 0) {
            $this->body .= "checked";
        }
            $this->body .= "> No ";
            $this->body .= "</div>
		</div>";

        $this->body .= "<div class='form-group'>
		<label class='control-label col-md-4 col-sm-4 col-xs-12'>Active Language: " . $this->show_tooltip(3, 1) . "</label>
			<div class='col-md-6 col-sm-6 col-xs-12'><input type=\"radio\" name=\"c[active]\" value=\"1\" ";
        if ($show["active"] == 1) {
            $this->body .= "checked";
        }
            $this->body .= "> Yes<br><input type=\"radio\" name=\"c[active]\" value=\"0\" ";
        if ($show["active"] == 0) {
            $this->body .= "checked";
        }
            $this->body .= "> No ";
            $this->body .= "</div>
		</div>";

        if (!$this->admin_demo()) {
            $this->body .= "<div class=\"center\"><input type=\"submit\" name=\"auto_save\" value=\"Save\"></div>";
        }

        $this->body .= "</table></div></fieldset>\n";

        $this->body .= "
		<div style='padding: 5px;'><a href=index.php?page=languages_home&mc=languages class='back_to'>
		<i class='fa fa-backward'> </i> Back to Current Languages</a></div>
		";

        $this->body .= ($this->admin_demo()) ? '</div>' : "</form>\n";
        return true;
    } //end of function edit_language_form

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    function update_language($db, $language_id = 0, $information = 0)
    {
        if (!$language_id || !$information) {
            return false;
        }

        if ($information['default_language'] == 1) {
            //setting this language to default, so un-set any other defaults
            $sql = "update " . $this->pages_languages_table . " set
				default_language = 0
				where language_id != " . $language_id;
            if (!$this->db->Execute($sql)) {
                return false;
            }
        }

        $sql = "update " . $this->pages_languages_table . " set
			language = \"" . $information["language"] . "\",
			active = " . $information["active"] . ",
			default_language = " . $information["default_language"] . "
			where language_id = " . $language_id;
        if (!$this->db->Execute($sql)) {
            return false;
        }
        return true;
    } //end of function update_language

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    function new_language_form()
    {
        if (!$this->admin_demo()) {
            $this->body .= "<form action=\"index.php?mc=languages&page=languages_new\" method=\"post\" class='form-horizontal form-label-left'>\n";
        } else {
            $this->body .= "<div class='form-horizontal'>";
        }
        $this->body .= "<fieldset id='NewLanguage'><legend>Add New Language</legend><div>";

        $this->body .= "
		<div class='page_note' style='text-align: center;'>
			Enter the name of your new language below. This will create the internal structure for your new language and copy the text of the English language as a starting point.<br />
			After that completes, you may wish to use the <a href='index.php?page=languages_new&mc=languages'>Import Language Data</a> tool to import a translation file,
			or you can manually adjust individual text fields using the Pages Management section of the admin.
		</div>
		<div class='form-group'>
		<label class='control-label col-md-3 col-sm-3 col-xs-12'>Language Name: </label>
			<div class='col-md-6 col-sm-6 col-xs-12' style=\"text-align:center;\">
			<input type=\"text\" name=\"c[name]\" class=\"form-control col-md-7 col-xs-12\" value=\"\"" . $show["language"] . "\"\">";
        if (!$this->admin_demo()) {
            $this->body .= "<input type=\"submit\" name=\"auto_save\" value=\"Save\">";
        }
        $this->body .= "</div>
		</div>";

        $this->body .= "</div></fieldset>\n";
        $this->body .= ($this->admin_demo()) ? '</div>' : "</form>\n";
        return true;
    } //end of function edit_language_form

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    function insert_new_language($db, $language = 0)
    {
        if ($language) {
            //set the time limit to be not set
            set_time_limit();
            //also make it so user aborts don't screw up
            ignore_user_abort(true);
            $this->sql_query = "insert into " . $this->pages_languages_table . "
				(language)
				values
				(\"" . $language["name"] . "\")";
            $new_language_result = $this->db->Execute($this->sql_query);
            //echo $this->sql_query."<br>\n";
            if (!$new_language_result) {
                $this->error_message = $this->internal_error_message;
                $this->site_error($this->db->ErrorMsg());
                return false;
            }

            $language_id = $this->db->Insert_ID();

            //Copy page text for new language
            $this->sql_query = "SELECT * FROM " . $this->pages_text_languages_table . " WHERE language_id = 1";
            $result = $this->db->Execute($this->sql_query);
            //echo $this->sql_query."<br>\n";
            if (!$result) {
                $this->error_message = $this->internal_error_message;
                $this->site_error($this->db->ErrorMsg());
                return false;
            } elseif ($result->RecordCount() > 0) {
                while ($show = $result->FetchRow()) {
                    $text = urldecode($show["text"]);
                    $text = urlencode($text);
                    $this->sql_query = "insert into " . $this->pages_text_languages_table . "
						(page_id,text_id,language_id,text)
						values
						(" . $show["page_id"] . "," . $show["text_id"] . "," . $language_id . ",\"" . $text . "\")";
                    $new_message_result = $this->db->Execute($this->sql_query);

                    //echo $this->sql_query."<br>\n";
                    if (!$new_message_result) {
                        $this->error_message = $this->internal_error_message;
                        $this->site_error($this->db->ErrorMsg());
                        return false;
                    }
                }
            } else {
                return false;
            }

            // Addon Text
            $addonController = geoAddon::getInstance();
            $installed_text_addons =& $addonController->getTextAddons(0, 1);
            //die('<pre>'.print_r($installed_text_addons,1).'</pre>');
            $text_addons_keys = array_keys($installed_text_addons);
            foreach ($text_addons_keys as $name) {
                //get a copy of the addon's admin class, and run the init_text function
                include_once(ADDON_DIR . '/' . $name . '/admin.php');
                $admin = "addon_{$name}_admin";
                if (!class_exists($admin)) {
                    continue;
                }
                $admin = new $admin();
                $text = $admin->init_text($language_id);

                foreach ($text as $text_id => $data) {
                    if (!$addonController->setText($admin->auth_tag, $name, $text_id, $data['default'], $language_id)) {
                        trigger_error('ERROR ADDON: could not set text. ' . $this->db->ErrorMsg());
                    }
                }
            }

            // Copy listing length choices
            $this->sql_query = "SELECT * FROM " . $this->choices_table . " WHERE language_id = 1 AND type_of_choice = 1";
            $choices_result = $this->db->Execute($this->sql_query);
            if (!$choices_result) {
                $this->error_message = $this->internal_error_message;
                $this->site_error($this->db->ErrorMsg());

                return false;
            } else {
                while ($choices = $choices_result->FetchRow()) {
                    $this->sql_query = "INSERT INTO " . $this->choices_table . "
							(type_of_choice, display_value, numeric_value, language_id)
							VALUES
							(1, '" . $choices["display_value"] . "', '" . $choices["numeric_value"] . "', '$language_id')";
                    //echo $this->sql_query.'<Br>';
                    $result = $this->db->Execute($this->sql_query) or die($this->sql_query);
                }
            }

            //category languages
            $this->sql_query = "SELECT * FROM " . $this->classified_categories_languages_table . " WHERE language_id = 1";
            $result = $this->db->Execute($this->sql_query);
            //echo $this->sql_query."<br>\n";
            if (!$result) {
                $this->error_message = $this->internal_error_message;
                $this->site_error($this->db->ErrorMsg());
                return false;
            } elseif ($result->RecordCount() > 0) {
                while ($show = $result->FetchRow()) {
                    $sql_query = "INSERT INTO " . $this->db->geoTables->categories_languages_table . "
						(`category_id`,`category_name`,`description`,`language_id`)
						values (?, ?, ?, ?)";
                    $query_data = array ($show["category_id"],$show["category_name"],$show["description"],$language_id);
                    $insert_result = $this->db->Execute($sql_query, $query_data);
                    //echo $sql_query." is the query<br>\n";
                    if (!$insert_result) {
                        //echo $sql_query." is the query<br>\n";
                        //echo "Error: ".$this->db->ErrorMsg()."<br />\n";
                        $this->error_message = $this->messages[3500];
                        return false;
                    }
                }
            } else {
                return false;
            }

            //category questions
            $sql = "SELECT * FROM " . geoTables::questions_languages . " WHERE language_id=1";
            $rows = $this->db->Execute($sql);

            foreach ($rows as $row) {
                $this->db->Execute(
                    "INSERT INTO " . geoTables::questions_languages . " SET
					question_id=?, language_id=?, name=?, explanation=?, choices=?",
                    array ($row['question_id'], $language_id, $row['name'], $row['explanation'], $row['choices'])
                );
            }

            //regions
            $sql = "SELECT * FROM " . geoTables::region_languages . " WHERE language_id=1";
            $rows = $this->db->Execute($sql);

            foreach ($rows as $row) {
                $this->db->Execute(
                    "INSERT INTO " . geoTables::region_languages . " SET
						`id`=?, `language_id`=?, `name`=?",
                    array($row['id'], $language_id, '' . $row['name'])
                );
            }

            //region level labels
            $sql = "SELECT * FROM " . geoTables::region_level_labels . " WHERE language_id=1";
            $rows = $this->db->Execute($sql);

            foreach ($rows as $row) {
                $this->db->Execute(
                    "INSERT INTO " . geoTables::region_level_labels . " SET
						`level`=?, `language_id`=?, `label`=?",
                    array($row['level'], $language_id, '' . $row['label'])
                );
            }

            return $language_id;
        } else {
            //no information to enter
            return false;
        }
        return true;
    } //end of function insert_new_language

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    function delete_language($db, $language_id)
    {
        if (!$language_id) {
            return false;
        }

        $this->sql_query = "delete from " . $this->pages_languages_table . " WHERE language_id = " . $language_id;
        $result = $this->db->Execute($this->sql_query);
        if ($this->debug_languages) {
            echo $this->sql_query . "<br>\n";
        }
        if (!$result) {
            return false;
        }

        $this->sql_query = "delete from " . $this->pages_text_languages_table . " WHERE language_id = " . $language_id;
        $result = $this->db->Execute($this->sql_query);
        //clear text cache, since cache can be embeded, no way to
        //expire all appropriate pages accurately
        geoCache::clearCache('text');
        //also clear page output cache
        geoCache::clearCache('pages');
        if ($this->debug_languages) {
            echo $this->sql_query . "<br>\n";
        }
        if (!$result) {
            return false;
        }

        $this->sql_query = "delete from " . $this->classified_categories_languages_table . " WHERE language_id = " . $language_id;
        $result = $this->db->Execute($this->sql_query);
        if ($this->debug_languages) {
            echo $this->sql_query . "<br>\n";
        }
        if (!$result) {
            return false;
        }

        $this->sql_query = "delete from " . $this->choices_table . " WHERE language_id = $language_id AND type_of_choice = 1";
        $result = $this->db->Execute($this->sql_query);
        if ($this->debug_languages) {
            echo $this->sql_query . "<br>\n";
        }
        if (!$result) {
            return false;
        }

        //remove text from db for addons
        $sql = 'DELETE FROM ' . $this->db->geoTables->addon_text_table . ' WHERE `language_id`=?';
        $result = $this->db->Execute($sql, array($language_id));
        if (!$result) {
            return false;
        }

        //remove geographic region for language
        $sql = 'DELETE FROM ' . geoTables::region_languages . ' WHERE `language_id`=?';
        $result = $this->db->Execute($sql, array($language_id));
        if (!$result) {
            return false;
        }

        //remove geographic region labels for language
        $sql = 'DELETE FROM ' . geoTables::region_level_labels . ' WHERE `language_id`=?';
        $result = $this->db->Execute($sql, array($language_id));
        if (!$result) {
            return false;
        }

        return true;
    } //end of function delete_language

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    function display_sections_edit_text($section_index = '')
    {
        if (!isset($_REQUEST['b'])) {
            $_REQUEST['b'] = 0;
        }
        if (!isset($_REQUEST['c'])) {
            $_REQUEST['c'] = 0;
        }
        if (!isset($_REQUEST['l'])) {
            $_REQUEST['l'] = 0;
        }
        if (!isset($_REQUEST['z'])) {
            $_REQUEST['z'] = 0;
        }

        if (($_REQUEST["b"]) && ($_REQUEST["c"]) && ($_REQUEST["l"])) {
            //edit this message
            if (!$this->edit_text_message($this->db, $_REQUEST["c"], $_REQUEST["l"], $_REQUEST["b"], $section_index)) {
                return false;
            }
        } elseif (($_REQUEST["b"]) && ($_REQUEST["l"])) {
            //display this pages messages
            if (!$this->display_page_messages($this->db, $_REQUEST["b"], $_REQUEST["l"], $section_index)) {
                    return false;
            }
        } else {
            //display the text management homepage
            $this->languages_home($this->db);
        }
        $this->display_page();
    }
    function update_sections_edit_text()
    {
        if (($_REQUEST["b"]) && ($_REQUEST["c"]) && ($_REQUEST["l"]) && ($_REQUEST["z"])) {
            return $this->update_text_message($this->db, $_REQUEST["b"], $_REQUEST["c"], $_REQUEST["z"], $_REQUEST["l"]);
        }
        return false;
    }
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
    function display_modules_edit_text()
    {
        if (!isset($_REQUEST['b'])) {
            $_REQUEST['b'] = 0;
        }
        if (!isset($_REQUEST['c'])) {
            $_REQUEST['c'] = 0;
        }
        if (!isset($_REQUEST['l'])) {
            $_REQUEST['l'] = 0;
        }
        if (!isset($_REQUEST['z'])) {
            $_REQUEST['z'] = 0;
        }

        if (($_REQUEST["b"]) && ($_REQUEST["c"]) && ($_REQUEST["l"])) {
            //edit this message
            if (!$this->edit_text_message($this->db, $_REQUEST["c"], $_REQUEST["l"], $_REQUEST["b"])) {
                return false;
            }
        } elseif (($_REQUEST["b"]) && ($_REQUEST["l"])) {
            //display this pages messages
            if (!$this->display_page_messages($this->db, $_REQUEST["b"], $_REQUEST["l"])) {
                    return false;
            }
        } else {
            //display the text management homepage
            $this->languages_home($this->db);
        }
        $this->display_page();
    }
    function update_modules_edit_text()
    {
        if (($_REQUEST["b"]) && ($_REQUEST["c"]) && ($_REQUEST["l"]) && ($_REQUEST["z"])) {
            return $this->update_text_message($this->db, $_REQUEST["b"], $_REQUEST["c"], $_REQUEST["z"], $_REQUEST["l"]);
        }
        return false;
    }
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%


    function display_languages_home()
    {
        if (PHP5_DIR) {
            $menu_loader = geoAdmin::getInstance();
        } else {
            $menu_loader =& geoAdmin::getInstance();
        }
        $this->body .= $menu_loader->getUserMessages();

        $this->languages_home($this->db);
        $this->display_page();
    }
    function update_languages_home()
    {
    }
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
    function display_languages_edit()
    {
        if (PHP5_DIR) {
            $menu_loader = geoAdmin::getInstance();
        } else {
            $menu_loader =& geoAdmin::getInstance();
        }
        $this->body .= $menu_loader->getUserMessages();

        if ($_REQUEST['l']) {
            if (!$this->edit_language_form($this->db, $_REQUEST['l'])) {
                return false;
            }
        } else {
            if (!$this->languages_home($this->db)) {
                return false;
            }
        }

        $this->display_page();
    }
    function update_languages_edit()
    {
        return $this->update_language($this->db, $_REQUEST['l'], $_REQUEST['c']);
    }
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
    function display_languages_delete()
    {
        if (PHP5_DIR) {
            $menu_loader = geoAdmin::getInstance();
        } else {
            $menu_loader =& geoAdmin::getInstance();
        }
        $this->body .= $menu_loader->getUserMessages();

        if ($_REQUEST['l']) {
            if (!$this->delete_language($this->db, $_REQUEST['l'])) {
                return false;
            }

            if (!$this->languages_home($this->db)) {
                return false;
            }
        }
        $this->display_page();
        return true;
    }
    function update_languages_delete()
    {
        return true;
    }
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
    function display_languages_import()
    {
        $langs = DataAccess::getInstance()->Execute("SELECT * FROM " . geoTables::pages_languages_table);
        foreach ($langs as $l) {
            $tpl_vars['languages'][$l['language_id']] = $l['language'];
        }
        $tpl_vars['demo'] = defined('DEMO_MODE') ? true : false;
        geoView::getInstance()->setBodyTpl('language_import.tpl')->setBodyVar($tpl_vars);
    }
    function update_languages_import()
    {
    }
    function display_languages_import_legacy()
    {

        define('TRANSLATED_FILE', 'text.csv');
        if (PHP5_DIR) {
            $menu_loader = geoAdmin::getInstance();
        } else {
            $menu_loader =& geoAdmin::getInstance();
        }

        $body = "";

        if (!file_exists('text_utility/importMessages.php')) {
            $menu_loader->userError("Could not find <em>text_utility/importMessages.php</em>. Please upload this file.");
        }
        if (!isset($_GET["l"])) {
            $menu_loader->userError("No language selected");
        }


        if (!isset($_GET['doImport'])) {
            if (!file_exists(TRANSLATED_FILE)) {
                $menu_loader->userError("Could not find <em>" . TRANSLATED_FILE . "</em> under your admin directory.
						Please upload this file and try again.");
            } else {
                $body .= file_get_contents('text_utility/readme.importing.htm');
                $body .= "<br /><br />Found <em>" . TRANSLATED_FILE . "</em><br />
				<a href='index.php?" . $_SERVER['QUERY_STRING'] . "&doImport' onclick='return confirm(\"Are you sure you want to import this language? This operation may take a few minutes to complete. If the server times out or you get a blank screen, refresh the page, and the process will resume where it left off.\");'>Import</a>";
            }
        } else {
            include 'text_utility/importMessages.php';
            doImport($_GET['l']);
            //clear text cache
            geoCache::clearCache('text');
            //clear page/module output cache
            geoCache::clearCache('pages');
        }
        $this->body .= $menu_loader->getUserMessages();
        $this->body .= $body;
        $this->display_page();
    }
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
    function display_languages_export()
    {
        if (!file_exists('text_utility/exportMessages.php')) {
            geoAdmin::m('Couldn\'t find <em>text_utility/exportMessages.php</em>. Please upload this file.', geoAdmin::ERROR);
            return;
        }

        $tpl_vars['admin_msgs'] = geoAdmin::m();

        if ($_REQUEST['download'] != 1) {
            $langs = DataAccess::getInstance()->Execute("SELECT * FROM " . geoTables::pages_languages_table);
            foreach ($langs as $l) {
                $tpl_vars['languages'][$l['language_id']] = $l['language'];
            }
            geoView::getInstance()->setBodyTpl('language_export.tpl')->setBodyVar($tpl_vars);
        } else {
            @ob_end_clean();
            $filename = $_REQUEST['type'];
            if ($_REQUEST['type'] != 'category_structure' && $_REQUEST['type'] != 'region_structure') {
                $filename .= '_' . $_POST['lang'];
            }
            $filename .= '.csv';
            header("Content-Type: text/csv");
            if (strlen($_REQUEST['fromTemplateSet']) > 0) {
                if (is_file(GEO_TEMPLATE_DIR . $_REQUEST['fromTemplateSet'] . '/text.csv')) {
                    header("Content-disposition: attachment; filename=text.csv");
                    include GEO_TEMPLATE_DIR . $_REQUEST['fromTemplateSet'] . '/text.csv';
                } else {
                    header($_SERVER["SERVER_PROTOCOL"] . " 404 Not Found");
                }
            } else {
                header("Content-disposition: attachment; filename=$filename");
                include 'text_utility/exportMessages.php';
            }
            exit;
        }
    }
    function update_languages_export()
    {
    }
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
    function display_languages_new()
    {
        if (PHP5_DIR) {
            $menu_loader = geoAdmin::getInstance();
        } else {
            $menu_loader =& geoAdmin::getInstance();
        }
        $this->body .= $menu_loader->getUserMessages();

        if ($_REQUEST['c']) {
            if (!$this->languages_home($this->db)) {
                return false;
            }
        } else {
            if (!$this->new_language_form()) {
                return false;
            }
        }

        $this->display_page();
    }
    function update_languages_new()
    {
        return $this->insert_new_language($this->db, $_REQUEST['c']);
    }
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
    function display_text_dev_tools()
    {
        if (($_GET["d"]) && ($_REQUEST["e"])) {
            if (!$this->name_and_description_form($db, $_GET["d"])) {
                return false;
            }
        } elseif ($_GET["d"]) {
            if (!$this->name_and_description_form($db, $_GET["d"])) {
                return false;
            }
        }
        $this->display_page();
    }
    function update_text_dev_tools()
    {
        if (($_GET["d"]) && ($_REQUEST["e"])) {
            return $this->update_message_name_and_description($db, $_GET["d"], $_REQUEST["e"]);
        }
    }

    //browsing duplicates
    function display_sections_browsing_edit_text()
    {
        return $this->display_sections_edit_text('_browsing');
    }
    function update_sections_browsing_edit_text()
    {
        return $this->update_sections_edit_text('_browsing');
    }

    //listing_process duplicates
    function display_sections_listing_process_edit_text()
    {
        return $this->display_sections_edit_text('_listing_process');
    }
    function update_sections_listing_process_edit_text()
    {
        return $this->update_sections_edit_text('_listing_process');
    }

    //registration duplicates
    function display_sections_registration_edit_text()
    {
        return $this->display_sections_edit_text('_registration');
    }
    function update_sections_registration_edit_text()
    {
        return $this->update_sections_edit_text('_registration');
    }

    //user_mgmt duplicates
    function display_sections_user_mgmt_edit_text()
    {
        return $this->display_sections_edit_text('_user_mgmt');
    }
    function update_sections_user_mgmt_edit_text()
    {
        return $this->update_sections_edit_text('_user_mgmt');
    }

    //login_languages duplicates
    function display_sections_login_languages_edit_text()
    {
        return $this->display_sections_edit_text('_login_languages');
    }
    function update_sections_login_languages_edit_text()
    {
        return $this->update_sections_edit_text('_login_languages');
    }

    //extra_pages duplicates
    function display_sections_extra_pages_edit_text()
    {
        return $this->display_sections_edit_text('_extra_pages');
    }
    function update_sections_extra_pages_edit_text()
    {
        return $this->update_sections_edit_text('_extra_pages');
    }

    //bidding duplicates
    function display_sections_bidding_edit_text()
    {
        return $this->display_sections_edit_text('_bidding');
    }
    function update_sections_bidding_edit_text()
    {
        return $this->update_sections_edit_text('_bidding');
    }
} //end of class Text_management
