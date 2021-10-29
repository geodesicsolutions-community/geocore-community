<?php

//admin_pages_class.php
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
## ##    17.12.0-7-ga1cc2dc
##
##################################

class Admin_pages extends Admin_site
{

    var $debug_pages = 0;
    var $modules_debug = 0;

    //module and page id definitions
    var $main_cat_nav_mods = array(114);
    var $filter_dropdown_mods = array(91,101);
    var $display_username_mods = array(53);
    var $cat_tree_display_mods = array(97,98,99);
    var $reg_login_link_mods = array(54,66,67,68,78,79,80,88);
    var $cat_nav_mods = array(94,95,96,100,10199);
    var $featured_pics_mods = array(89,90,102,117,118,119,120,121,122,123,124);
    var $logged_in_out_HTML_mods = array(75,76,77,165,166,167,185,186,187,188,189,190,191,192,193,194,195,196,197,198);
    var $PHP_mods = array(103,104,105,110,111,112,10185,10186,10187,10188,10189,10190,10191,10192,10193,10194,10195,10196,10197,10198);
    var $search_mods = array(106);
    var $zip_browse_mods = array(133);
    var $state_browse_mods = array(134);
    var $extra_pages = array(135,136,137,138,139,140,141,142,143,144,145,146,147,148,149,150,151,152,153,154);
    var $featured_listings_mods = array(155,156);
    var $fixed_cat_nav_mods = array(158,159,160,161,162,163,164);
    var $title_mods = array(171);
    var $featured_and_newest_mods = array(125,126,127,128,129,130,131,132,46,47,48,49,50,60,61);
    var $newest_mods = array(60,61);
    var $hottest = array(172);
    var $users = array(169,170);
    var $cat_browse_options = array(10200);
    var $my_account_links = array(10208);
    var $sectionTitle = "";

    function home()
    {
        $this->body .= "<table valign=center cellspacing=0 cellpadding=0 width=\"100%\">\n";
        $this->body .= "<tr>\n\t<td><a href=index.php?mc=categories&page=category_config>browse and edit categories</a>\n\t</td>\n</tr>\n";
        $this->body .= "</table>\n";
    } //end of function home

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    function display_current_page($db, $page_id = 0, $section_index = '')
    {
        if (!$page_id || !$this->isPageEditable($page_id)) {
            //oops, not a valid page!
            return false;
        }

        $sql = "select * from " . $this->pages_table . " where page_id = " . $page_id;
        $page_result = $this->db->Execute($sql);
        if ($this->debug_pages) {
            echo $sql . "<br>\n";
        }
        if (!$page_result) {
            if ($this->debug_pages) {
                echo $sql . "<br>\n";
            }
            return false;
        } elseif ($page_result->RecordCount() == 1) {
            $show = $page_result->FetchRow();
        } else {
            return false;
        }
        if ($show['module']) {
            //this is a module
            $this->edit_module_specifics_form($db, $page_id);
            return true;
        }

        $section = $this->get_section($db, $show["section_id"]);
        $page = $this->get_page($db, $page_id);

        // Find number of languages so we can fix the colspan
        $sql = "select count(language_id) as count from " . $this->pages_languages_table . " where active = 1";
        $result = $this->db->Execute($sql);
        if ($this->debug_pages) {
            echo $sql . "<br>\n";
        }
        $languages = $result->FetchRow();
        $num_languages = $languages["count"];

        //$this->title = "Pages Management > Section: ".$section["name"]." > Page: ".$show["name"];
        //$this->title = " ({$show["name"]})";

        $this->body .= geoAdmin::m();

        if (!$this->admin_demo()) {
            $this->body .= "<form action=\"\" method=post class='form-horizontal form-label-left'>";
        } else {
            $this->body .= "<div class='form-horizontal'>";
        }
            $this->body .= "<div class=\"page-title1\"><i class=\"fa fa-file\"></i><span class=\"visible-lg-inline\"> Page:</span> <span class=\"color-primary-two\">{$show["name"]}</span></div>";
            $this->body .= "<div style=\"padding: 5px;\"><a href=\"index.php?mc=pages_sections&page=sections_show&b={$section['section_id']}\" class=\"back_to\"><i class=\"fa fa-backward\"></i> Back to {$section["name"]}</a></div>";
        $this->body .= "<fieldset><legend>Page Details</legend><div class='x_content'>";

        $this->input_admin_label_and_tag_name($page, 1, 1);
        if ($page_id >= 135 && $page_id <= 154) {
            $sql = "select * from " . $this->site_configuration_table;
            $configuration_result = $this->db->Execute($sql);
            if (!$configuration_result) {
                $this->site_error($this->db->ErrorMsg());
                return false;
            } elseif ($configuration_result->RecordCount() == 1) {
                $this->configuration_data = $configuration_result->FetchRow();
            } else {
                return false;
            }

                $this->body .= '
					<div class="form-group">
						<label class="control-label col-md-5 col-sm-5 col-xs-12">Page URL: </label>
						<div class="col-md-6 col-sm-6 col-xs-12">
							<a href="' . $this->configuration_data["classifieds_url"] . '?a=28&b="' . $page_id . '" class="vertical-form-fix">' . $this->configuration_data["classifieds_url"] . "?a=28&b=" . $page_id . '</a>
						</div>
					</div>';
        }

        if ($show['section_id'] != 12) {
            //not extra page, show text links
            $sql = "select * from " . $this->pages_languages_table;
            $language_result = $this->db->Execute($sql);
            if ($this->debug_pages) {
                echo $sql . "<br>\n";
            }
            if (!$language_result) {
                if ($this->debug_pages) {
                    echo $sql . "<br>\n";
                }
                return false;
            } elseif ($language_result->RecordCount() > 0) {
                $this->row_count = 0;
                $this->body .= "<div class=\"header-color-primary-one\">Edit Text Fields Appearing on this Page:</div>";
                while ($show_language = $language_result->FetchRow()) {
                    //show edit text button for each language
                    $this->body .= '
						<div class="form-group">
							<label class="control-label col-md-6 col-sm-6 col-xs-12">' . $this->get_language_name($db, $show_language["language_id"]) . '</label>
							<div class="col-md-6 col-sm-6 col-xs-12">
								' . geoHTML::addButton('Edit Text', "index.php?page=sections" . $section_index . "_edit_text&b=" . $page_id . "&l=" . $show_language["language_id"]) . '
							</div>
						</div>';
                }
            }
        }
        $this->body .= "</div></fieldset><div class='clearColumn'></div>";
        require_once ADMIN_DIR . 'design.php';
        $design = Singleton::getInstance('DesignManage');

        $templatePagesData = $design->getPagesData();

        $needsTags = array (1, 43, 69, 73, 74, 84);

        if (isset($templatePagesData[$page_id]) || $page_id == 69) {
            //Filter out what pages we want
            $tpl_vars = $pages = array ();
            $view = geoView::getInstance();
            if (isset($templatePagesData[$page_id])) {
                $pages[$page_id] = $templatePagesData[$page_id];
                $pages[$page_id]['name'] = 'Overall Template Used';
            }
            if ($page_id == 69) {
                //special case, there is no direct attachment for page 69
                //but there is for 69_classified and/or 69_auction
                if (geoMaster::is('classifieds')) {
                    $pages['69_classified'] = $templatePagesData['69_classified'];
                }
                if (geoMaster::is('auctions')) {
                    $pages['69_auction'] = $templatePagesData['69_auction'];
                }
            }
            if ($page_id == 1) {
                //special case, get the sub-template attachments
                if (geoMaster::is('classifieds')) {
                    $pages['1_classified'] = $templatePagesData['1_classified'];
                }
                if (geoMaster::is('auctions')) {
                    $pages['1_auction'] = $templatePagesData['1_auction'];
                }
            } elseif ($page_id == 43) {
                $pages['43_home'] = $templatePagesData['43_home'];
            } elseif ($page_id == 84) {
                $pages['84_detail'] = $templatePagesData['84_detail'];
            }
            if ($show['section_id'] == 12) {
                //extra page...  template main body
                $tpl_vars['extraPage'] = 1;
            }
            $sql = "SELECT `language_id`, `language` FROM " . geoTables::pages_languages_table . " ORDER BY `language_id` ASC";
            $languages = $this->db->GetAll($sql);
            $tpl_vars['languages'] = array();
            foreach ($languages as $row) {
                $tpl_vars['languages'][$row['language_id']] = $row['language'];
            }

            if (in_array($page_id, $needsTags)) {
                $templates = array();
            }

            foreach ($pages as $pageId => $data) {
                $pages[$pageId]['t_set'] = geoTemplate::whichTemplateSet('main_page', 'attachments', "templates_to_page/{$pageId}.php");
                $pages[$pageId]['attachments'] = $attachments = $view->getTemplateAttachments($pageId, false);
                if (in_array($page_id, $needsTags)) {
                    //remember each of the templates
                    foreach ($attachments as $lanId => $attached) {
                        if (isset($attached[0]) && !isset($templates[$attached[0]])) {
                            $path = geoTemplate::getFilePath('main_page', '', $attached[0], $false, $true);

                            if ($path) {
                                //this data will get used later down
                                $templates[$attached[0]]['path'] = $path;
                                $templates[$attached[0]]['t_set'] = geoTemplate::whichTemplateSet('main_page', '', $attached[0]);
                            }
                        }
                    }
                }
            }

            $tpl_vars['categoryId'] = 0;

            $tpl_vars['pages'] = $pages;
            $tpl = new geoTemplate(geoTemplate::ADMIN);
            $tpl->assign($tpl_vars);
            $this->body .= $tpl->fetch('pages/attachedTemplates.tpl');
        }
        if (in_array($page_id, $needsTags)) {
            //show attachments for templates
            $tpl = new geoTemplate(geoTemplate::ADMIN);
            if ($page_id == 1 || $page_id == 69) {
                //listing details tags
                $tags = geoFields::getListingTagsMeta();
                $tpl->assign('use_listing_tag', true);
                $check_listing_tags = true;
            } elseif ($page_id == 43) {
                $tags = $this->getOldUserManagementTags();
            } elseif ($page_id == 73 || $page_id == 74) {
                $tags = $this->getSignFlyerTags();
            } elseif ($page_id == 84) {
                //full size image display page
                $tags = $this->getFullSizeImageTags();
            }
            $tpl->assign('tags', $tags);
            if ($templates) {
                //go through each template and figure out what tags are in it...
                //this data will get used later down

                foreach ($templates as $localFile => $templateData) {
                    $fileContents = file_get_contents($templateData['path']);

                    foreach ($tags as $tagTypes) {
                        foreach ($tagTypes as $tag => $data) {
                            if ($check_listing_tags && $data['type'] != 'label') {
                                $checks = array();
                                if ($data['type'] == 'field') {
                                    $checks[] = "{listing field='$tag'";
                                    $checks[] = '{$' . $tag . '}';
                                } elseif ($data['type'] == 'tag') {
                                    $checks[] = "{listing tag='$tag'";
                                }
                                foreach ($checks as $find) {
                                    if (strpos($fileContents, $find) !== false) {
                                        $templates[$localFile]['tags'][$tag] = 1;
                                        break;
                                    }
                                }
                            } else {
                                if (strpos($fileContents, '{$' . $tag . '}') !== false) {
                                    $templates[$localFile]['tags'][$tag] = 1;
                                }
                            }
                        }
                    }
                }
            }
            $tpl->assign('templates', $templates);
            $this->body .= $tpl->fetch('pages/listingTagsFound.tpl');
        }

        $parent_name = $this->get_page($db, $page_id);

        //if (strlen($show["special_instructions"]) > 0)
            //$this->body .= "<tr class=row_color_black>\n\t<td colspan=".(2+$num_languages)." class=medium_font_light>".$show["special_instructions"]." </a>\n\t</td>\n</tr>\n";
        if (($page_id == 70) || ($page_id == 71)) {
            //this is the sign or flyer page, show simple fields to use settings
            //TODO: Convert this to use new fields to use
            if ($page_id == 70) {
                $page_large_name = "Flyer";
                $page_name = "flyer";
            } else {
                $page_large_name = "Sign";
                $page_name = "sign";
            }
            $this->body .= "
<fieldset>
<legend>" . $page_large_name . " Form Fields to Use</legend>
";

            $this->body .= "<table width=100% cellpadding=3 cellspacing=1>";

            $this->input_radio_yes_no($show, 'module_use_image', 'Display Image:');
            $this->input_sign_flyer_width_height($db, $page_id, $page);

            $this->input_radio_yes_no($show, 'module_display_title', 'Hide Title:');
            $this->input_radio_yes_no($show, 'module_display_price', 'Display Price of Listing:');
            $this->input_radio_yes_no($show, 'module_display_phone1', 'Display Phone 1 Field:');
            $this->input_radio_yes_no($show, 'module_display_phone2', 'Display Phone 2 Field:');
            $this->input_radio_yes_no($show, 'module_display_contact', 'Display Contact Info:');
            $this->input_module_display_location($page, 1);
            $this->input_radio_yes_no($show, 'module_display_ad_description', 'Display Description:');
            $this->input_radio_yes_no($show, 'module_display_classified_id', 'Display Listing ID:');
            if (geoPC::is_ent()) {
                $this->input_module_display_optional_fields($page);
            }
            if (!$this->admin_demo()) {
                $this->body .= "
					<tr class=row_color2 align=center>
						<td colspan=2>
							<input type=submit name='auto_save' value=\"Save\">
						</td>
					</tr>";
            }
            $this->body .= "</table></fieldset><div class='clearColumn'></div>";
        } elseif ($page_id == 113) {
            //browse seller listings settings & fields to use
            //TODO: Convert this to use new fields to use
            $this->body .= "<SCRIPT language=\"JavaScript1.2\">";
            // Set title and text for tooltip
            $this->body .= "Text[1] = [\"Number of Sellers to Display on a Page\", \"Set the number of sellers to display on any result page while browsing the sellers.\"]\n
				Text[2] = [\"Number of Category Columns to Display on a Page\", \"Set the columns the categories will display so the user can browse.\"]\n
				Text[3] = [\"Display Subcategory Sellers\", \"Choosing \\\"\\\"yes\\\"\\\" will display the sellers of a current category\'s sub-categories while browsing the seller listing.\"]\n
				Text[4] = [\"Display Sellers Category Count\", \"Choosing \\\"yes\\\" will display the number of unique sellers of the categories listed.\"]\n
				Text[5] = [\"Display Category Description\", \"Choosing \\\"yes\\\" will display the category description.\"]\n
				Text[6] = [\"Display Category Image\", \"Choosing \\\"yes\\\" will display the category image next to the category name.\"]\n";

            //".$this->show_tooltip(6,1)."

            // Set style for tooltip
            //echo "Style[0] = [\"white\",\"\",\"\",\"\",\"\",,\"black\",\"#ffffcc\",\"\",\"\",\"\",,,,2,\"#b22222\",2,24,0.5,0,2,\"gray\",,2,,13]\n";
            $this->body .= "</script>";
            $this->body .= "
<fieldset>
<legend>Browse Seller Settings</legend>
<table valign=center cellspacing=0 cellpadding=3 width=\"100%\">";

            $this->input_option_dropdown($show, 'module_number_of_ads_to_display', 'Number of Sellers to Display on a Page:' . $this->show_tooltip(1, 1));
            $this->input_option_dropdown($show, 'module_number_of_columns', 'Number of Category Columns to Display:' . $this->show_tooltip(2, 1), 6);
            $this->body .= '<tr class="' . $this->get_row_color() . '">
							<td align="right" width="50%" class="medium_font">
								<strong>Arrange Categories Alphabetically:</strong>
							</td><td class="medium_font">
								<input name="c[alpha_across_columns]" value="1" ' . (($page['alpha_across_columns']) ? 'checked="checked"' : "") . ' type="radio" /> Across the Column<br />
								<input name="c[alpha_across_columns]" value="0" ' . ((!$page['alpha_across_columns']) ? 'checked="checked"' : "") . ' type="radio" /> Down the Column
							</td>
						  </tr>';
            $this->row_count++;
            $this->input_radio_yes_no($show, 'display_no_subcategory_message', 'Display Subcategory Sellers:' . $this->show_tooltip(3, 1));
            $this->input_radio_yes_no($show, 'display_category_count', 'Display Sellers Category Count:' . $this->show_tooltip(4, 1));
            $this->input_radio_yes_no($show, 'display_category_description', 'Display Category Description:' . $this->show_tooltip(5, 1));
            $this->input_radio_yes_no($show, 'display_category_image', 'Display Category Image:' . $this->show_tooltip(6, 1));

            $this->input_radio_yes_no($show, 'module_display_username', 'Display Username Column:');
            $this->input_radio_yes_no($show, 'module_display_business_type', 'Display Business Type Column:');
            $this->input_radio_yes_no($show, 'module_display_company_name', 'Display Company Name Column:');

            $this->input_radio_yes_no($show, 'module_display_name', 'Display Name Column:');
            $this->input_radio_yes_no($show, 'module_display_phone1', 'Display Phone Column:');
            $this->input_radio_yes_no($show, 'module_display_phone2', 'Display Phone 2 Column:');
            $this->input_module_display_location($page, 1);
            if (geoPC::is_ent()) {
                $this->input_module_display_optional_fields($show);
            }
            if (!$this->admin_demo()) {
                $this->body .= "
					<tr class=row_color2 align=center>
						<td colspan=\"100%\">
							<input type=submit name=\"auto_save\" value=\"Save\">
						</td>
					</tr>";
            }
            $this->body .= "
				</table>
				</fieldset><div class='clearColumn'></div>";
        }

        $this->body .= ($this->admin_demo()) ? "</div>" : "</form>";


        return true;
    } //end of function display_current_page

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    function browse_sections($db, $section = 0, $section_index = '')
    {
        if (PHP5_DIR) {
            $menu_loader = geoAdmin::getInstance();
        } else {
            $menu_loader =& geoAdmin::getInstance();
        }
        $this->body .= $menu_loader->getUserMessages();


        //browse the listings in this category that are open

        if ($section) {
            $sql = "select * from " . $this->pages_sections_table . " where section_id = " . $section;
            if (!geoMaster::is('classifieds') && geoMaster::is('auctions')) {
                if (geoMaster::is('auctions')) {
                    $sql .= " and (applies_to = 0 or applies_to = 2)";
                } else {
                    $sql .= " and (applies_to = 0 or applies_to = 1)";
                }
            }
            $section_result = $this->db->Execute($sql);
            //echo $sql." is the query<br>\n";
            if (!$section_result) {
                return false;
            } elseif ($section_result->RecordCount() == 1) {
                $show_section_data = $section_result->FetchRow();
                $section_name = $show_section_data["name"];
                $section_description = $show_section_data["description"];
                $parent_section = $show_section_data["parent_section"];
            } else {
                //category does not exist
                $this->error_message = "Category Does Not Exist";
                //echo $sql . "<br>";
                //echo "<pre>" . printf(var_dump($section_result->FetchRow()));
                return false;
            }
        } else {
            $section_name = "Pages Home";
            $section_description = "";
            $parent_section = 0;
        }

        $sql = "select * from " . $this->pages_sections_table . " where parent_section = " . $section;
        if (geoMaster::is('auctions') && !geoMaster::is('classifieds')) {
            $sql .= " and (applies_to = 0 or applies_to = 2)";
        } elseif (geoMaster::is('classifieds') && !geoMaster::is('auctions')) {
            $sql .= " and (applies_to = 0 or applies_to = 1)";
        }
        $sql .= " order by display_order";

        $sub_section_result = $this->db->Execute($sql);
        //echo $sql." is the query<br>\n";
        if (!$sub_section_result) {
            //echo $sql." is the query<br>\n";
            $this->error_message = $this->messages[5501];
            return false;
        } else {
            if ($parent_section) {
                $parent_section_data = $this->get_section($db, $parent_section);
                $this->body .= "<tr>\n\t<td class=col_hdr_top colspan=4>\n\t
					back to: <a href=index.php?mc=pages_sections&page=sections{$section_index}_show&b=" . $parent_section . " class=col_hdr_top>" . $parent_section_data["name"] . "</a>\n\t</td>\n</tr>\n";
            } elseif ($section != 0) {
                $this->body .= "<div style=\"padding: 5px;\"><a href=index.php?mc=pages_sections&page=sections_home class=\"back_to\"><i class=\"fa fa-backward\"></i> Back to Pages Home</a></div>";
            }

            $this->body .= "<fieldset id='Page Management'><legend>Pages Management</legend>
				<div class='table-responsive'>
				<table cellpadding=2 cellspacing=1 border=0 width=100% class='table table-hover table-striped table-bordered'>\n";

            if ($sub_section_result->RecordCount() > 0) {
                //display subsections to this section
                //$this->body .= "<tr>\n\t<td colspan=4 class=group_price_hdr align=center>\n\t <b>Subsections of: ".$section_name."</b> </a>\n\t</td>\n</tr>\n";
                $this->body .= "<thead><tr class='col_hdr_top'>\n\t<td align=center width=45% class=col_hdr_left><span class='color-primary-four'><b><i class=\"fa fa-folder-open\"></i> SECTIONS</b></span>\n\t</td>\n\t";
                $this->body .= "<td align=center width=25% class=col_hdr>\n\t<span class='color-primary-four'><b><i class=\"fa fa-folder\"></i> SUBSECTIONS</b></span>\n\t</td>\n\t";
                $this->body .= "<td align=center width=25% class=col_hdr>\n\t<span class='color-primary-six'><b><i class=\"fa fa-file\"></i> PAGES</b></span>\n\t</td>\n";
                $this->body .= "<td align=center width=5% class=col_hdr>\n\t&nbsp;\n\t</td>\n</tr></thead>";
                $this->row_count = 0;
                while ($show_sub_sections = $sub_section_result->FetchRow()) {
                    $this->body .= "<tr class=" . $this->get_row_color() . ">\n\t<td valign=top>\n\t<a href=index.php?mc=pages_sections" . $section_index . "&page=sections" . $section_index . "_show&b=" . $show_sub_sections["section_id"] . "><span class=medium_font><font color=000000>" . $show_sub_sections["name"] . "</font></span></a><br><span class=small_font>";
                    //$this->body .= $show_sub_sections["description"];
                    $this->body .= "</span></td>\n\t";
                    $this->body .= "<td align=center valign=top class=small_font>\n\t";

                    $sql = "select * from " . $this->pages_sections_table . " where parent_section = " . $show_sub_sections["section_id"];
                    if (!geoMaster::is('classifieds') && geoMaster::is('auctions')) {
                        if (geoMaster::is('auctions')) {
                            $sql .= " and (applies_to = 0 or applies_to = 2)";
                        } else {
                            $sql .= " and (applies_to = 0 or applies_to = 1)";
                        }
                    }
                    $sql .= " order by display_order";
                    $sub_section_sections_result = $this->db->Execute($sql);
                    //echo $sql." is the query<br>\n";
                    if (!$sub_section_sections_result) {
                        //echo $sql." is the query<br>\n";
                        $this->error_message = $this->messages[5501];
                        return false;
                    } elseif ($sub_section_sections_result->RecordCount() > 0) {
                        while ($show_this_sub_section = $sub_section_sections_result->FetchRow()) {
                            $this->body .= $show_this_sub_section["name"] . "<br>\n";
                        }
                    } else {
                        $this->body .= "none";
                    }
                    $this->body .= " \n\t</td>\n\t";

                    $this->body .= "<td align=center valign=top class=small_font>\n\t";
                    //$sql = "select * from ".$this->pages_table." where section_id = ".$show_sub_sections["section_id"]." order by display_order";
                    $sql = "select * from " . $this->pages_table . " where section_id = " . $show_sub_sections["section_id"] . " and module = 0";
                    $sql .= " and (applies_to = 0";
                    if (geoMaster::is('auctions')) {
                        $sql .= " or applies_to = 2";
                    }
                    if (geoMaster::is('classifieds')) {
                        $sql .= " or applies_to = 1";
                    }
                    if (geoMaster::is('classifieds') && geoMaster::is('auctions')) {
                        $sql .= " or applies_to = 4";
                    }
                    $sql .= ") order by page_id";
                    $sub_pages_result = $this->db->Execute($sql);
                    //echo $sql." is the query<br>\n";
                    if (!$sub_pages_result) {
                        //echo $sql." is the query<br>\n";
                        $this->error_message = $this->messages[5501];
                        return false;
                    } elseif ($sub_pages_result->RecordCount() > 0) {
                        while ($show_sub_pages = $sub_pages_result->FetchRow()) {
                            $this->body .= (($this->isPageEditable($show_sub_pages['page_id'])) ? $show_sub_pages["name"] . "<br>\n" : '');
                        }
                    } else {
                        $this->body .= "none";
                    }
                    $this->body .= " \n\t</td>\n\t";
                    $enter_button = geoHTML::addButton('Enter', 'index.php?mc=pages_sections' . $section_index . '&page=sections' . $section_index . '_show&b=' . $show_sub_sections["section_id"]);
                    $this->body .= "<td align=center valign=top>" . $enter_button . "</td>\n\t";
                    $this->body .= "</tr>\n";
                    $this->row_count++;
                }
            }

            $sql = "select * from " . $this->pages_table . " where section_id = " . $section . " and module = 0";
            if (!geoPC::is_ent()) {
                $sql .= ' and page_id not in (62, 63)';
            }
            if (!geoMaster::is('classifieds') && geoMaster::is('auctions')) {
                if (geoMaster::is('auctions')) {
                    $sql .= " and (applies_to = 0 or applies_to = 2)";
                } else {
                    $sql .= " and (applies_to = 0 or applies_to = 1)";
                }
            }
            $sql .= " order by page_id, name";
            $sub_pages_result = $this->db->Execute($sql);
            if (!$sub_pages_result) {
                return false;
            } elseif ($sub_pages_result->RecordCount() > 0) {
                //display subpages to this section
                $this->body .= "
						<div class=\"table-responsive\">
							<table width=\"100%\" class=\"table table-hover table-striped table-bordered\">
								<thead>
									<tr class='col_hdr_top'>
										<td align=left class=col_hdr_left>
											<span class='color-primary-six'><b><i class=\"fa fa-file\"></i> PAGE</b></span>
										</td>
										<td align=left width=50% class=col_hdr_left>
											ADMIN NOTE
										</td>
										<td width=\"20%\" class=col_hdr_left>&nbsp;</td>
									</tr>
								</thead><tbody>";
                $this->row_count = 0;
                while ($show_sub_pages = $sub_pages_result->FetchRow()) {
                    if (!$this->isPageEditable($show_sub_pages["page_id"])) {
                        continue;
                    }
                    $edit_button = geoHTML::addButton('Edit', 'index.php?mc=pages_sections&page=sections' . $section_index . '_page&b=' . $show_sub_pages["page_id"]);
                    $this->body .= "

									<tr class=" . $this->get_row_color() . ">
										<td valign=top>
											<a href=index.php?mc=pages_sections&page=sections" . $section_index . "_page&b=" . $show_sub_pages["page_id"] . ">
												<span class=medium_font>
													<font color=000000>" . $show_sub_pages['name'] . "</font>
												</span>
											</a>
										</td>
										<td class=medium_font align=left>
											" . $show_sub_pages['admin_label'] . "<br>
										</td>
										<td align=center valign=top>" . $edit_button . "
										</td>
									</tr>
								\n";
                    $this->row_count++;
                }
                $this->body .= "
							</tbody></table></div>";
            }
        }
        $this->body .= "</table></div>
</fieldset><div class='clearColumn'></div>
";

        return true;
    } //end of function browse_sections
    function isSectionEditable($sectionId)
    {
        //sections no longer used
        if (in_array($sectionId, array (4,5))) {
            //HTML or PHP module, invalid module since that can be done in smarty templates now
            return false;
        }

        //disable featured levels 2-5 unless the addon is present
        $featuredLevels = array(13,14,15,16);
        if (in_array($sectionId, $featuredLevels) && !geoAddon::getInstance()->isEnabled('featured_levels')) {
            return false;
        }

        return true;
    }
    function isPageEditable($pageId)
    {
        static $arr = array();

        if (count($arr) == 0) {
            $arr = array(47, 48, 49, 50, 61, 62, 63, 67, 68, 90, 91, 92, 94, 95, 96, 101, 102, 103, 104, 105, 110, 111, 112, 114, 115, 116, 117, 134, 155, 156, 173, 174, 175, 178, 179, 180, 181, 182, 184);
            $arr = array_merge($arr, range(158, 164));
            $arr = array_merge($arr, range(188, 197));
            if (!geoPC::is_premier()) {
                $arr = array_merge($arr, array(19));
            }
            if (!geoAddon::getUtil('signs_flyers')) {
                $arr = array_merge($arr, range(70, 74));
            }
        }
        return (!in_array($pageId, $arr) || geoPC::is_ent());
    }
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
    public function getOldUserManagementTags()
    {
        $tags = array();
        $tags['normal'] = array(
            'section_title' => 'Title for this section.',
            'page_title' => 'The title of the page.',
            'description' => 'Description for this page.',
            'active_ads' => 'Link to the users currently active listings.',
            'expired_ads' => 'Link to the users recently expired listings.',
            'current_info' => 'Link to the users information.',
            'place_ad' => 'Link to List an Item.',
            'favorites' => 'Link to users favorite listings.',
            'communications' => 'Link to user\'s communications.',
            'communications_config' => 'Link to user\'s communication configuration.',
            'signs_and_flyers' => 'Link to signs and flyers page.',
            'renew_extend_subscription' => 'Link to renew/extend subscription.<br /><strong>Will only appear if user is a member of a subscription-based price plan.</strong>',
            'add_money_with_balance' => 'Link to add money to your account. <b>This will also display the account balance beside it.</b>',
            'add_money' => 'Link to add money to your account.',
            'balance_transactions' => 'Link to display all balance transactions that have happened to the clients site balance.',
        );
        if (geoMaster::is('auctions')) {
            //auction only tags
            $tags['auctions'] = array (
                'feedback' => 'Link to the Feedback Management system.',
                'current_bids' => 'Link to the user\'s current bids.',
                'blacklist_buyers' => 'Link to the user\'s blacklist.',
                'invited_buyers' => 'Link to the user\'s invited list.',
            );
        }
        return $tags;
    }

    public function getSignFlyerTags()
    {
        $tags = array();
        $tags['normal'] = array (
            'title' => 'Title of the listing.',
            'image' => 'Lead image of auction.',
            'address' => 'Address of the seller.',
            'city' => 'City of the seller.',
            'state' => 'State of the seller.',
            'zip' => 'Zip code of the seller.',
            'price' => 'Price for the listing.',
            'classified_id' => 'Classified ID of the listing.',
            'description' => 'Description of the listing.',
            'phone_1' => 'Phone number 1 for the listing.',
            'phone_2' => 'Phone number 2 for the listing.',
            'contact' => 'Contact information for the listing.',
            'buy_now_price' => 'Buy Now Price for the listing.',
            'starting_bid' => 'Starting Bid for the listing.',
        );
        for ($i = 1; $i < 21; $i++) {
            $tags['normal']['module_display_optional_field_' . $i] = 'Optional Field ' . $i . ' for the listing.';
        }
        return $tags;
    }

    public function getFullSizeImageTags()
    {
        $tags = array ();
        $tags['full_size_image'] = array(
            "full_size_image" => "The full size image.",
            "ful_size_text" => "The text attached to the full size image."
        );
        return $tags;
    }

    function edit_module_specifics_form(&$db, $page_id = 0)
    {
        if (!$page_id) {
            return false;
        }
        $these_pages = array(91,101,134);
        if (in_array($page_id, $these_pages) && !geoPC::is_ent()) {
            return false;
        }
        $page =& $this->get_page($db, $page_id);
        if (!$this->admin_demo()) {
            //javascript to change hidden field
            $this->body .= '<script>
	function updateSurroundField(){
		var surround_field = document.getElementById("surround_hidden");
		var pre_field = document.getElementById("surround_pre");
		var post_field = document.getElementById("surround_post");
		var pre_value = " ";
		if (pre_field.value){
			pre_value = pre_field.value;
		}
		var post_value = " ";
		if (post_field.value){
			post_value=post_field.value
		}
		surround_field.value = pre_value+"sub|cat|list"+post_value;
		//alert (surround_field.value);
		return true;
	}
</script>';
            $this->body .= "
			<form action='index.php?mc=view_modules&page=modules_page&b=" . $page_id . "' class='form-horizontal form-label-left' method=post onSubmit=\"return (updateSurroundField());\">\n";
        } else {
            $this->body .= "<div class='form-horizontal'>";
        }

        $this->body .= "<div class='page-title1'>
							<i class='fa fa-cubes'></i>
							<span class='visible-lg-inline'>Module: </span>
							<span class='color-primary-two'>{$page['name']}</span>
						</div>";

        $this->body .= "
			<fieldset id='ModuleDetails'><legend>Module Details</legend><div class='x_content'>";

        $this->input_admin_label_and_tag_name($page);

        if (in_array($page_id, $this->cat_nav_mods)) {
            //sub categories section
            //get pre and post.
            $surrounds = explode('sub|cat|list', $page['module_sub_category_nav_surrounding']);
            if ($page['module_display_sub_category_nav_links'] == 1) {
                $no_check = '';
                $yes_check = ' checked="checked"';
                $sub_class = ' class="enabled_text"';
                $sub_disabled = '';
            } else {
                $no_check = ' checked="checked"';
                $yes_check = '';
                $sub_class = ' class="disabled_text"';
                $sub_disabled = ' disabled';
            }

            //category navigation module

            if ($page_id == 10199) {
                $this->body .= '
					<div class="form-group">
						<label class="control-label col-md-5 col-sm-5 col-xs-12">Display Subcategories: </label>
						<div class="col-md-6 col-sm-6 col-xs-12">
							<input name="c[module_display_sub_category_nav_links]" value="1" ' . $yes_check . ' type="radio" id="sub_cat_on" /> Yes<br />
							<input name="c[module_display_sub_category_nav_links]" value="0" ' . $no_check . ' type="radio" /> No
						</div>
					</div>';
                $this->input_option_dropdown($page, 'number_of_browsing_columns', 'Number of Subcategories to Display:', 6);
            } else {
                $this->input_option_dropdown($page, 'number_of_browsing_columns', 'Display this number of Category Columns:', 6);
                $this->body .= '
					<div class="form-group">
						<label class="control-label col-md-5 col-sm-5 col-xs-12">Arrange Categories Alphabetically: </label>
						<div class="col-md-6 col-sm-6 col-xs-12">
							<input name="c[alpha_across_columns]" value="1" ' . (($page['alpha_across_columns']) ? 'checked="checked"' : "") . ' type="radio" /> Across the Column<br />
							<input name="c[alpha_across_columns]" value="0" ' . ((!$page['alpha_across_columns']) ? 'checked="checked"' : "") . ' type="radio" /> Down the Column
						</div>
					</div>';
                $this->row_count++;
                $this->input_radio_yes_no($page, 'display_category_count', 'Display Category Count:');
                if (geoMaster::is('classifieds') && geoMaster::is('auctions')) {
                    $this->input_browsing_count_format($page);
                }

                $this->input_radio_yes_no($page, 'display_category_image', 'Display Category Image:');
                $this->input_radio_yes_no($page, 'display_category_description', 'Display Category Description below the Category Name:');
                $this->input_radio_yes_no($page, 'display_no_subcategory_message', 'Display the "no subcategory" Message:');
                $this->input_radio_yes_no($page, 'module_display_ad_description', 'Display the "back to Parent Category" link in Listing:');
                $message = "Display \"new listing\" icon (within this module):" . $this->show_tooltip(8, 1);

                $this->input_radio_yes_no($page, 'module_display_new_ad_icon', $message);

                $this->body .= '
					<div class="form-group">
						<label class="control-label col-md-5 col-sm-5 col-xs-12">Display Subcategories (as links): ' . $this->show_tooltip(1, 1) . '</label>
						<div class="col-md-6 col-sm-6 col-xs-12">
							<input name="c[module_display_sub_category_nav_links]" value="1" ' . $yes_check . ' type="radio" id="sub_cat_on" /> Yes<br />
							<input name="c[module_display_sub_category_nav_links]" value="0" ' . $no_check . ' type="radio" /> No
						</div>
					</div>';
            }
        } elseif (in_array($page_id, $this->main_cat_nav_mods)) {
            //category navigation module

            $this->input_module_category_level_to_display($page);
            $this->input_option_dropdown($page, 'number_of_browsing_columns', 'Display this Number of Category Columns:', 6);

            $this->body .= '
					<div class="form-group">
						<label class="control-label col-md-5 col-sm-5 col-xs-12">Arrange Categories Alphabetically: </label>
						<div class="col-md-6 col-sm-6 col-xs-12">
							<input name="c[alpha_across_columns]" value="1" ' . (($page['alpha_across_columns']) ? 'checked="checked"' : "") . ' type="radio" /> Across the Column<br />
							<input name="c[alpha_across_columns]" value="0" ' . ((!$page['alpha_across_columns']) ? 'checked="checked"' : "") . ' type="radio" /> Down the Column
						</div>
					</div>';
            $this->input_radio_yes_no($page, 'display_category_count', 'Display Category Count:');
            if (geoMaster::is('classifieds') && geoMaster::is('auctions')) {
                $this->input_browsing_count_format($page);
            }
            $this->input_radio_yes_no($page, 'display_category_image', 'Display Category Image:');
            $this->input_radio_yes_no($page, 'display_category_description', 'Display Category Description below the Category Name:');
            $this->input_radio_yes_no($page, 'display_no_subcategory_message', 'Display the "no subcategory" Message:');
            $this->input_radio_yes_no($page, 'module_display_ad_description', 'Display the "back to Parent Category" link in Listing:');
            //sub categories section
            //get pre and post.
            $surrounds = explode('sub|cat|list', $page['module_sub_category_nav_surrounding']);
            if ($page['module_display_sub_category_nav_links'] == 1) {
                $no_check = '';
                $yes_check = ' checked="checked"';
                $sub_class = ' class="enabled_text"';
                $sub_disabled = '';
            } else {
                $no_check = ' checked="checked"';
                $yes_check = '';
                $sub_class = ' class="disabled_text"';
                $sub_disabled = ' disabled';
            }
            $this->body .= '
					<div class="form-group">
						<label class="control-label col-md-5 col-sm-5 col-xs-12">Display Subcategories (as links): ' . $this->show_tooltip(1, 1) . '</label>
						<div class="col-md-6 col-sm-6 col-xs-12">
							<input name="c[module_display_sub_category_nav_links]" value="1" ' . $yes_check . ' type="radio" id="sub_cat_on" /> Yes<br />
							<input name="c[module_display_sub_category_nav_links]" value="0" ' . $no_check . ' type="radio" /> No
						</div>
					</div>';
        } elseif (in_array($page_id, $this->filter_dropdown_mods)) {
            //filters dropdown
            //$this->title = "Filter Dropdown Form Module Admin";
            $this->description = "Choose how to display this module from the choice below.";
            $this->input_module_display_filter_in_row($page);
        } elseif (in_array($page_id, $this->display_username_mods)) {
            //display username module
            //$this->title = "Edit the Display User Data Module Form";
            $this->description = "Choose what personal information you wish to display when you use this module.";
            $this->input_module_display_username($db, $page);
        } elseif (in_array($page_id, $this->cat_tree_display_mods)) {
            //nothing for cat tree modules
        } elseif (in_array($page_id, $this->cat_browse_options)) {
            $this->category_browsing_options_form();
        } elseif (in_array($page_id, $this->reg_login_link_mods)) {
            //display register/login link module
            //nothing to do for this one
        } elseif (in_array($page_id, $this->featured_pics_mods)) {
            //display featured pic module
            if (geoMaster::is('classifieds') && geoMaster::is('auctions')) {
                $this->input_module_display_type_listing($page);
            }
            $this->input_radio_yes_no($page, 'module_display_header_row', 'Display Header Row:');
            $this->input_option_dropdown($page, 'module_number_of_ads_to_display', 'Number of Featured Pics to Display:', 1000);
            $this->input_option_dropdown($page, 'module_number_of_columns', 'Number of Featured Pic Columns to Display:', 11);
            $this->input_module_thumb_width_height($page);

            $message = "
				<strong>Max Characters of Title/Description/Optional Fields to Display:</strong><br>
				<span class=small_font>
					Choose '0' for no limit.
				</span>";
            $this->input_option_dropdown($page, 'length_of_description', $message, 200, 0);

            if (geoMaster::is('classifieds') && geoMaster::is('auctions')) {
                $this->input_radio_yes_no($page, 'module_display_type_text', 'Display Listing Type (Classified/Auction):');
            }
            $this->module_display_fields_link('pic_modules');
        } elseif (in_array($page_id, $this->search_mods)) {
            //display search box module
            $this->input_radio_yes_no($page, 'display_category_description', 'Display Title/Description Choice Dropdown:');
        } elseif (in_array($page_id, $this->zip_browse_mods)) {
            //display zip browsing module
            //no settings
        } elseif (in_array($page_id, $this->state_browse_mods)) {
            //display state filter module
            //no settings
        } elseif (in_array($page_id, $this->featured_listings_mods)) {
            //featured listings module that displays a specific category

            if (geoMaster::is('classifieds') && geoMaster::is('auctions')) {
                $this->input_module_display_type_listing($page);
            }
            $this->input_option_dropdown($page, 'module_number_of_ads_to_display', 'Number of Listings to Display', 1000);
            $this->input_category_dropdown($db, $page);
            $this->input_radio_yes_no($page, 'module_display_header_row', 'Display Header Row');

            $this->input_photo_or_icon($page);
            $this->input_module_thumb_width_height($page);
            $this->input_radio_yes_no($page, 'display_all_of_description', 'Display All of Description');
            $message = "
				<strong>Max Characters of Description to Display</strong><br>
				<span class=small_font>
				Enter the maximum number of characters of the description to display.
				</span>";
            $this->input_option_dropdown($page, 'length_of_description', $message);
            $this->input_module_display_ad_description_where($page);
            $this->module_display_fields_link();
            //since using common browse class, it always shows attention getters now
            //(or any listing icons)
            /*
            $attention = geoAddon::getUtil('attention_getters');
            if ($attention)
                $this->input_radio_yes_no($page,'module_display_attention_getter','Display Attention Getters within module');
            */
        } elseif (in_array($page_id, $this->fixed_cat_nav_mods)) {
            //$this->title = "Fixed Category Navigation Form Module Admin";
            $this->description = "Choose how to display this module from the choices below.  Also choose the category from which you wish
				to display the subcategories of.  This module will display the immediate subcategories of the category you choose below.";
            $this->input_category_dropdown($db, $page);
            $this->input_option_dropdown($page, 'number_of_browsing_columns', 'Display this Number of Category Columns:', 6);
            $this->body .= '
					<div class="form-group">
						<label class="control-label col-md-5 col-sm-5 col-xs-12">Arrange Categories Alphabetically: </label>
						<div class="col-md-6 col-sm-6 col-xs-12">
							<input name="c[alpha_across_columns]" value="1" ' . (($page['alpha_across_columns']) ? 'checked="checked"' : "") . ' type="radio" /> Across the Column<br />
							<input name="c[alpha_across_columns]" value="0" ' . ((!$page['alpha_across_columns']) ? 'checked="checked"' : "") . ' type="radio" /> Down the Column
						</div>
					</div>';
            $this->input_radio_yes_no($page, 'display_category_count', 'Display Category Count:');
            // Choose how to display the category counts
            if (geoMaster::is('classifieds') && geoMaster::is('auctions')) {
                $this->input_browsing_count_format($page);
            }
            $this->input_radio_yes_no($page, 'display_category_image', 'Display Category Image:');
            $this->input_radio_yes_no($page, 'display_category_description', 'Display Category Description below Category Name:');
            $this->input_radio_yes_no($page, 'display_no_subcategory_message', 'Display the "no subcategory" Message:');
            $this->input_radio_yes_no($page, 'module_display_ad_description', 'Display the "back to Parent Category" link in Listing:');
            $message = "Display \"new listing\" Icon: " . $this->show_tooltip(8, 1);
            $this->input_radio_yes_no($page, 'module_display_new_ad_icon', $message);
            //sub categories section
            //get pre and post.
            $surrounds = explode('sub|cat|list', $page['module_sub_category_nav_surrounding']);
            if ($page['module_display_sub_category_nav_links'] == 1) {
                $no_check = '';
                $yes_check = ' checked="checked"';
                $sub_class = ' class="enabled_text"';
                $sub_disabled = '';
            } else {
                $no_check = ' checked="checked"';
                $yes_check = '';
                $sub_class = ' class="disabled_text"';
                $sub_disabled = ' disabled';
            }
            $this->body .= '
					<div class="form-group">
						<label class="control-label col-md-5 col-sm-5 col-xs-12">Display Subcategories (as links): ' . $this->show_tooltip(1, 1) . '</label>
						<div class="col-md-6 col-sm-6 col-xs-12">
							<input name="c[module_display_sub_category_nav_links]" value="1" ' . $yes_check . ' type="radio" id="sub_cat_on" /> Yes<br />
							<input name="c[module_display_sub_category_nav_links]" value="0" ' . $no_check . ' type="radio" /> No
						</div>
					</div>';
        } elseif (in_array($page_id, $this->title_mods)) {
            // Title module
            if (!$this->input_title_module_text($db, $page_id, $page)) {
                return false;
            }
        } elseif (in_array($page_id, $this->hottest)) {
            if (geoMaster::is('classifieds') && geoMaster::is('auctions')) {
                $this->input_module_display_type_listing($page);
            }
            $this->input_option_dropdown($page, 'module_number_of_ads_to_display', 'Maximum Number of Listings to Display:', 1000);
            $this->input_radio_yes_no($page, 'module_display_header_row', 'Display Header Row:');
            $this->input_photo_or_icon($page);
            $this->input_module_thumb_width_height($page);
            $this->input_radio_yes_no($page, 'display_all_of_description', 'Display All of Description:');
            $message = "
				<strong> Max Characters of Description to Display:</strong><br>
				<span class=small_font>
				 Choose the maximum number of characters of the description to display.
				</span>";
            $this->input_option_dropdown($page, 'length_of_description', $message);
            $this->input_module_display_ad_description_where($page);
            $this->module_display_fields_link();
        } elseif (in_array($page_id, $this->users)) {
            //user modules?  no settings.
        } elseif (in_array($page_id, $this->my_account_links)) {
            //nothing to do here
        } else {
            if (geoMaster::is('classifieds') && geoMaster::is('auctions')) {
                $this->input_module_display_type_listing($page);
            }
            if (in_array($page_id, $this->newest_mods)) {
                $yes_selected = ($page['alt_order_by']) ? ' checked="checked"' : '';
                $no_selected = ($yes_selected) ? '' : ' checked="checked"';

                $this->body .= '
					<div class="form-group">
						<label class="control-label col-md-5 col-sm-5 col-xs-12">Order By: </label>
						<div class="col-md-6 col-sm-6 col-xs-12">
							<input name="c[alt_order_by]" value="0"' . $no_selected . ' type="radio" /> Newest<br />
							<input name="c[alt_order_by]" value="1"' . $yes_selected . ' type="radio" /> Ending Soonest
						</div>
					</div>';
            }
            $this->input_option_dropdown($page, 'module_number_of_ads_to_display', 'Display this Number of Listings:', 1000);
            $this->input_radio_yes_no($page, 'module_display_header_row', 'Display Header Row:');
            // Photo or Icon for featured listings modules and newest modules
            if (in_array($page_id, $this->featured_and_newest_mods)) {
                $this->input_photo_or_icon($page);
            }
            $this->input_module_thumb_width_height($page);
            $this->input_radio_yes_no($page, 'display_all_of_description', 'Display All of Description:');
            $message = "
				<strong>Max characters of Description to Display:</strong><br>
				<span class=small_font>
				Choose the maximum number of characters of the description to display.
				</span>";
            $this->input_option_dropdown($page, 'length_of_description', $message);
            $this->input_module_display_ad_description_where($page);
            $this->module_display_fields_link();
            //since using common browse class, it always shows attention getters now
            //(or any listing icons)
            /*
            $attention = geoAddon::getUtil('attention_getters');
            if ($attention)
                $this->input_radio_yes_no($page,'module_display_attention_getter','Display Attention Getters:');
            if ( geoPC::is_ent() )
                $this->input_module_display_optional_fields($page);
            */
        }
        if (!$this->admin_demo()) {
            $this->body .= "
				<div class=\"input-group-btn center\">
					<input type=submit name='auto_save' value=\"Save\" class=\"btn btn-primary\">
				</div>";
        }

        $this->body .= "<div class=\"header-color-primary-one\">Edit Text appearing within this Module:</div>";
        $sql = "select * from " . $this->pages_languages_table;
        $language_result = $this->db->Execute($sql);
        if (!$language_result) {
            //$this->body .= $sql." is the query<br>\n";
            $this->error_message = $this->messages[3501];
            return false;
        } elseif ($language_result->RecordCount() > 0) {
            $this->row_count = 0;
            while ($show_language = $language_result->FetchRow()) {
                $edit_text_button = geoHTML::addButton('Edit Text', 'index.php?mc=view_modules&page=modules_edit_text&b=' . $page_id . "&l=" . $show_language["language_id"]);
                $this->body .= '
					<div class="form-group">
						<label class="control-label col-md-6 col-sm-6 col-xs-12">' . $this->get_language_name($db, $show_language["language_id"]) . '</label>
						<div class="col-md-6 col-sm-6 col-xs-12">
							' . $edit_text_button . '
						</div>
					</div>';
            }
        }
        $this->body .= "</div></fieldset><div class='clearColumn'></div>";

        $tpl = new geoTemplate(geoTemplate::ADMIN);

        $file = geoFile::getInstance(geoFile::TEMPLATES);
        require_once ADMIN_DIR . 'design.php';
        $design = new DesignManage();
        $design->init(true);

        $tList = array_diff($design->getAllTemplateSets(true), geoTemplate::getInvalidSetNames());
        $attachments = array();
        foreach ($tList as $tset) {
            $attachmentsDir = "$tset/main_page/attachments/modules_to_template/";
            $attachmentFiles = $file->scandir($attachmentsDir);
            foreach ($attachmentFiles as $fileName) {
                $tplName = preg_replace('/\.php$/', '', $fileName);
                if ($tplName === $fileName) {
                    //oops, no .php at the end, this is not an attachment file
                    continue;
                }
                $moduleAttachments = $design->getModulesToTemplate($attachmentsDir . $fileName);
                if (isset($moduleAttachments['modules'][$page_id])) {
                    $attachments[$tset][$tplName] = file_exists($file->absolutize("$tset/main_page/$tplName"));
                }
            }
        }
        $tpl->assign('attachments', $attachments);

        $this->body .= $tpl->fetch('pages/moduleAttachments.tpl');

        $this->body .= "<table valign=center cellspacing=0 cellpadding=3 width=\"100%\">";


        $this->body .= "
				<tr>
					<td colspan=2>
<div style='padding: 5px;'><a href=index.php?mc=pages_sections&page=sections{$section_index}_home class='back_to'>
<i class='fa fa-backward'> </i> Back to Pages Home</a></div>
					</td>
				</tr>
				<tr>
					<td colspan=2>
<div style='padding: 5px;'><a href=index.php?mc=view_modules&page=modules_home class='back_to'>
<i class='fa fa-backward'> </i> Back to Modules Home</a></div>
					</td>
				</tr>
			</table>";
            $this->body .= ($this->admin_demo()) ? "</div>" : "</form>";


        return true;
    } //end of function edit_module_specifics_form

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
    /**
     * Updates stuff about a module.
     *
     * @param unused $db
     * @param int $page_id
     * @param array $module_info Un-modified user inputed data of settings for the module.
     * @param int $section_index
     * @return boolean
     */
    function update_module_specifics($db, $page_id = 0, $module_info = 0, $section_index)
    {
        $page_id = intval($page_id);
        if (!$this->isPageEditable($page_id) || !$page_id) {
            return false;
        }
        //clear cache
        geoCache::clearCache('modules');
        //clear page cache
        geoCache::clearCache('pages');
        if (in_array($page_id, $this->extra_pages)) {
            //this is an extra page, not a module
            $sql = "UPDATE " . geoTables::pages_table . " SET `admin_label` = ? WHERE `page_id` = $page_id";

            $result = $this->db->Execute($sql, array($module_info["admin_label"]));
            if (!$result) {
                trigger_error('ERROR SQL: Sql: ' . $sql . ' error: ' . $this->db->ErrorMsg());
                return false;
            }
            $sql = "SELECT * FROM " . geoTables::pages_languages_table;
            $languages = $this->db->GetAll($sql);
            if ($languages === false) {
                trigger_error('ERROR SQL: Sql: ' . $sql . ' error: ' . $this->db->ErrorMsg());
                return false;
            }
            foreach ($languages as $lang) {
                $reg = new geoRegistry('extra_pages', "$page_id:{$lang['language_id']}");
                //do not encode when inserting to db, registry does it for us
                $extra_page_txt = trim($module_info['extra_page_text' . $lang['language_id']]);
                if ($reg->get('body_code') !== $extra_page_txt) {
                    //keep from re-saving if the text has not changed.
                    $reg->set('body_code', $extra_page_txt);
                    $reg->save();
                }
            }
            return true;
        } elseif (in_array($page_id, $this->title_mods)) {
            //this is a title module
            $sql = "UPDATE " . geoTables::ad_configuration_table . " SET `title_module_text` = ? , `title_module_language_display` = ? ";
            $result = $this->db->Execute($sql, array(geoString::toDB($module_info["title_module_text"]), $module_info["title_module_language_display"]));
            if (!$result) {
                trigger_error('ERROR SQL: Sql: ' . $sql . ' error: ' . $this->db->ErrorMsg());
                return false;
            }

            $sql = "UPDATE " . geoTables::pages_text_languages_table . " SET text = ? WHERE `text_id` = 2462";
            if ($this->debug_pages) {
                echo $sql . "<br>\n";
            }
            $result = $this->db->Execute($sql, array(geoString::toDB($module_info["title_module_home_text"])));
            if (!$result) {
                trigger_error('ERROR SQL: Sql: ' . $sql . ' error: ' . $this->db->ErrorMsg());
                return false;
            }
            //this is a page, not a module
            $sql = "UPDATE " . geoTables::pages_table . " SET `admin_label` = ? WHERE `page_id` = $page_id";

            $result = $this->db->Execute($sql, array($module_info["admin_label"]));
            if (!$result) {
                trigger_error('ERROR SQL: Sql: ' . $sql . ' error: ' . $this->db->ErrorMsg());
                return false;
            }

            return true;
        } elseif (in_array($page_id, $this->cat_browse_options)) {
            return $this->update_category_browse_options($page_id, $module_info);
        } elseif ($page_id && $module_info) {
            //NOTE: logged in/out html specific stuff removed already.


            //lets dynamically generate the sql query.
            $params = array();
            $set_vars = array();
            //list of fields not in the geodesic_pages database table.
            $skip_list = array ('flyer_maximum_image_width','flyer_maximum_image_height','sign_maximum_image_width','sign_maximum_image_height');
            //for fields where HTML is allowed, don't trim()
            $html_allowed = array('module_sub_category_nav_surrounding', 'module_sub_category_nav_prefix', 'module_sub_category_nav_separator');
            foreach ($module_info as $key => $value) {
                if (!in_array($key, $skip_list)) {
                    if (!in_array($key, $html_allowed)) {
                        $value = trim($value);
                    }
                    $params[] = $value;
                    $set_vars[] = "`$key` = ?";
                }
            }
            //use the format 'update table_name set var_name1 = ?, var_name2 = ? where var_namex = ?' for the query, and then
            //pass execute an array of values.  This way, we let sdo do input checking for us, and it might even
            //speed things up a bit.  See sdo documentation for more info on using ? in queries.
            $sql = 'UPDATE ' . geoTables::pages_table . ' SET ' . implode(',', $set_vars) . ' WHERE `page_id` = ?';
            $params[] = $page_id;

            if ($this->debug_pages) {
                echo $sql . "<br>\n params: <pre>";
                var_dump($params);
                echo '</pre>';
            }
            //$params is the array we generated earlier that has all the values for the ?'s in the sql query.
            $result = $this->db->Execute($sql, $params);
            if (!$result) {
                trigger_error('ERROR SQL: Sql: ' . $sql . ' error: ' . $this->db->ErrorMsg());
                return false;
            }

            if ($page_id == 70) {
                $sql = "update " . $this->ad_configuration_table . " set
					flyer_maximum_image_width = " . intval($module_info["flyer_maximum_image_width"]) . ",
					flyer_maximum_image_height = " . intval($module_info["flyer_maximum_image_height"]);
                $result = $this->db->Execute($sql);
                if ($this->debug_pages) {
                    echo $sql . "<br>\n";
                }
                if (!$result) {
                    trigger_error('ERROR SQL: Sql: ' . $sql . ' error: ' . $this->db->ErrorMsg());
                    return false;
                }
            } elseif ($page_id == 71) {
                $sql = "update " . $this->ad_configuration_table . " set
					sign_maximum_image_width = " . intval($module_info["sign_maximum_image_width"]) . ",
					sign_maximum_image_height = " . intval($module_info["sign_maximum_image_height"]);
                $result = $this->db->Execute($sql);
                if (!$result) {
                    trigger_error('ERROR SQL: Sql: ' . $sql . ' error: ' . $this->db->ErrorMsg());
                    return false;
                }
            }

            return true;
        } else {
            $this->error_message = $this->internal_error_message;
            //echo "whatever";
            return false;
        }
    } //end of function update_module_specifics

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
    function category_browsing_options_form()
    //admin options for category_browsing_options module
    {
//      $this->body .= "<tr><td class=\"col_hdr\" colspan=\"2\" style=\"width: 100%; font-weight: bold; text-align: center;\">Options selected below will be applied to the Category Browsing Options Module</td></tr>";
        $this->body .= "<div><p class=\"page_note\" style=\"text-align:center;\">Options selected below will be applied to the <strong>Category Browsing Options</strong> Module</p></div>";
        $show_options = array (
            'cat_browse_opts_as_ddl' => $this->db->get_site_setting('cat_browse_opts_as_ddl'),
            'cat_browse_all_listings' => $this->db->get_site_setting('cat_browse_all_listings'),
            'cat_browse_end_today' => $this->db->get_site_setting('cat_browse_end_today'),
            'cat_browse_has_pics' => $this->db->get_site_setting('cat_browse_has_pics'),
            'cat_browse_has_pics' => $this->db->get_site_setting('cat_browse_has_pics'),
            'cat_browse_class_only' => $this->db->get_site_setting('cat_browse_class_only'),
            'cat_browse_auc_only' => $this->db->get_site_setting('cat_browse_auc_only'),
            'cat_browse_buy_now' => $this->db->get_site_setting('cat_browse_buy_now'),
            'cat_browse_buy_now_only' => $this->db->get_site_setting('cat_browse_buy_now_only'),
            'cat_browse_auc_bids' => $this->db->get_site_setting('cat_browse_auc_bids'),
            'cat_browse_auc_no_bids' => $this->db->get_site_setting('cat_browse_auc_no_bids')

        );
        $ddlTooltip = geoHTML::showTooltip('Show as Dropdown', 'Choose yes to display the module\'s options in a dropdown list, or no to show them as a text-based, delimeter-separated list.');
        $this->input_radio_yes_no($show_options, "cat_browse_opts_as_ddl", "Show as Dropdown $ddlTooltip");
        $this->input_radio_yes_no($show_options, "cat_browse_all_listings", "All listings");
        $this->input_radio_yes_no($show_options, "cat_browse_end_today", "Listings Ending within 24 hours");
        $this->input_radio_yes_no($show_options, "cat_browse_has_pics", "Listings with Photos");

        if (geoMaster::is('classifieds') && geoMaster::is('auctions')) {
            $this->input_radio_yes_no($show_options, "cat_browse_class_only", "Classifieds only");
            $this->input_radio_yes_no($show_options, "cat_browse_auc_only", "Auctions only");
        }
        if (geoMaster::is('auctions')) {
            $this->input_radio_yes_no($show_options, "cat_browse_buy_now", "Auctions using Buy Now");
            $this->input_radio_yes_no($show_options, "cat_browse_buy_now_only", "Auctions using Buy Now Only");
            $this->input_radio_yes_no($show_options, "cat_browse_auc_bids", "Auctions with Bids");
            $this->input_radio_yes_no($show_options, "cat_browse_auc_no_bids", "Auctions without Bids");
        }
    }
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
    function update_category_browse_options($page_id, $data)
    {
        //set admin note
        $sql = "update " . $this->pages_table . " set admin_label='" . $data['admin_label'] . "' where page_id=" . $page_id;
        $result = $this->db->Execute($sql);
        //clear cache
        geoCache::clearCache('modules');
        //clear page cache
        geoCache::clearCache('pages');

        if (!$result) {
            if ($this->debug_pages) {
                echo $this->db->ErrorMsg() . "<br />";
            }
            return false;
        }
        $this->db->set_site_setting('cat_browse_opts_as_ddl', $data['cat_browse_opts_as_ddl']);
        $this->db->set_site_setting('cat_browse_all_listings', $data['cat_browse_all_listings']);
        $this->db->set_site_setting('cat_browse_end_today', $data['cat_browse_end_today']);
        $this->db->set_site_setting('cat_browse_has_pics', $data['cat_browse_has_pics']);

        if (geoMaster::is('classifieds') && geoMaster::is('auctions')) {
            $this->db->set_site_setting('cat_browse_class_only', $data['cat_browse_class_only']);
            $this->db->set_site_setting('cat_browse_auc_only', $data['cat_browse_auc_only']);
        }

        if (geoMaster::is('auctions')) {
            $this->db->set_site_setting('cat_browse_buy_now', $data['cat_browse_buy_now']);
            $this->db->set_site_setting('cat_browse_buy_now_only', $data['cat_browse_buy_now_only']);
            $this->db->set_site_setting('cat_browse_auc_bids', $data['cat_browse_auc_bids']);
            $this->db->set_site_setting('cat_browse_auc_no_bids', $data['cat_browse_auc_no_bids']);
        }
        return true;
    }

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    function show_modules()
    {
        $sql = "select * from " . $this->pages_table . " where module = 1 order by name";
        $module_result = $this->db->Execute($sql);
        if (!$module_result) {
            $this->error_message = $this->messages[5501];
            return false;
        }
        //display subsections to this section
        $this->body .= "<fieldset><legend>Complete Modules List</legend><div>";
        $this->body .= "<div class='table-responsive'><table class='table table-striped table-hover'><tbody>";
        foreach ($module_result as $show_module) {
            if (!$this->isPageEditable($show_module['page_id']) || !$this->isSectionEditable($show_module['module_type'])) {
                continue;
            }
            $tag = '<span style="white-space: nowrap;" class="color-primary-two">{module tag=\'' . $show_module["module_replace_tag"] . '\'}</span>';
            $this->body .= "<tr>
								<td style='white-space: normal;'>
									<a href='index.php?mc=view_modules&page=modules_page&amp;b={$show_module["page_id"]}'>{$show_module["name"]}</a><br />
									$tag<br />
									{$show_module["description"]}
								</td>
								<td>
									<a href='index.php?mc=view_modules&page=modules_page&b={$show_module["page_id"]}' class='btn btn-info btn-xs'><i class='fa fa-pencil'></i> Edit</a>
								</td>
							</tr>";
        }

        $this->body .= "</tbody></table></div></div></fieldset>";

        return true;
    }
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    function browse_module_sections($db, $section = 0)
    {
        if (!$this->isSectionEditable($section)) {
            return false;
        }

        //$this->title = "Page Modules";
        $this->description = "Page Modules allow you to display each module's feature / functionality on the pages that you specify. Each module has its own distinct \"tag\" which you insert within your Templates. Wherever you insert the
		tag into the html of your template is where that Module's functionality will be displayed. So, to use a module, determine that module's \"tag name\", insert that tag into your template, refresh that template through the Pages
		Management section (to ensure the system \"attaches\" the module to the page) and then edit the module's properties to display as you wish. Each module has it's own distinct display properties.";
        if ($section) {
            $this->body .= "
				<table cellpadding=4 cellspacing=0 border=0 width=100%>
				<tr>
					<td colspan=2>
						<div style='padding: 5px;'><a href=index.php?mc=view_modules&page=modules_home class='back_to'>
						<i class='fa fa-backward'> </i> Back to Modules Home</a></div>
					</td>
				</tr>
				</table>";
        }
        $this->body .= "<fieldset id='AvailableModules'><legend>Available Modules</legend><table cellpadding=2 cellspacing=1 border=0 class='table table-hover table-striped table-bordered'>\n";
        if (!$section) {
            $this->function_name = "browse_module_sections";

            $sql = "select * from " . $this->pages_modules_sections_table . " where parent_section = 0";
            //echo $sql. "is the query<br>";
            $section_result = $this->db->Execute($sql);
            //if($this->configuration_data["debug_admin"])
            //{
            //  $this->debug_display($db, $this->filename, $this->function_name, "pages_sections_modules_table", "get page sections data");
            //}
            if (!$section_result) {
                //echo $sql." is the query<br>\n";
                //$this->error_message = $this->messages[5501];
                return false;
            } elseif ($section_result->RecordCount() > 0) {
                $this->row_count = 0;
                while ($show_sections = $section_result->FetchRow()) {
                    if (!$this->isSectionEditable($show_sections['section_id'])) {
                        continue;
                    }
                    $this->body .= "<tr class=" . $this->get_row_color() . ">\n\t<td valign=top>\n\t<a href=index.php?mc=view_modules&page=modules_show&b=" . $show_sections["section_id"] . "><span class=medium_font><font color=000000>" . $show_sections["name"] . "</font></span></a><span class=small_font>";
                    //$this->body .= $show_sections["description"];
                    $this->body .= "</span></td>\n\t";
                    $enter_button = geoHTML::addButton('Enter', 'index.php?mc=view_modules&page=modules_show&b=' . $show_sections["section_id"]);
                    $this->body .= "<td align=center valign=top width=100>" . $enter_button . "</td>\n\t";
                    $this->body .= "</tr>\n";
                    $this->row_count++;
                }
                $this->body .= "<tr class=" . $this->get_row_color() . ">\n\t<td colspan='2' style='text-align: center;'>
					<a href='index.php?page=modules_page' class='mini_button'>View All Modules</a>
				</td></tr>";
            } else {
                //category does not exist
                $this->error_message = $this->messages["5500"];
                return false;
            }
        } else {
            //does this section have subsections
            $sql = "select * from " . $this->pages_modules_sections_table . " where parent_section = " . $section;

            //echo $sql. "is the query<br>";
            $parent_section_result = $this->db->Execute($sql);
            if (!$parent_section_result) {
                trigger_error('ERROR SQL: ' . $this->db->ErrorMsg());
                $this->error_message = $this->messages[5501];
            //  return false;
            }

            //if it does display these sections in a list like above

            elseif ($parent_section_result->RecordCount() > 0) {
                $this->row_count = 0;
                while ($show_sections = $parent_section_result->FetchRow()) {
                    if (!$this->isSectionEditable($show_sections['section_id'])) {
                        continue;
                    }
                        $enter_button = geoHTML::addButton('Enter', 'index.php?mc=view_modules&page=modules_show&b=' . $show_sections["section_id"]);
                        $this->body .= "
							<tr class=" . $this->get_row_color() . ">
								<td valign=top>
									<a href=index.php?mc=view_modules&page=modules_show&b=" . $show_sections["section_id"] . ">
										<span class=medium_font><font color=000000>" . $show_sections["name"] . "</font></span></a>
								</td>
								<td align=center valign=top>" . $enter_button . "</td>
							</tr>";
                        $this->row_count++;
                }
            } else {
                // it it does not have subsections then call the module list function
                //and pass it the subsection to display modules specific to that section

                $sql = "SELECT name,admin_label,page_id,module_replace_tag FROM " . $this->pages_table . "
					WHERE module = 1 AND module_type=" . $section;
                if (9 == $section && !geoPC::is_ent()) {
                    $sql .= ' and page_id = 100';
                }

                if (!$this->isSectionEditable($section)) {
                    return false;
                }

                $parent_section_result = $this->db->Execute($sql);
                if (!$parent_section_result) {
                    return false;
                }
                $unsorted_modules = array();
                while ($show_module = $parent_section_result->FetchRow()) {
                    if ($this->isPageEditable($show_module['page_id'])) {
                        array_push($unsorted_modules, $show_module);
                    }
                }

                //leave the other types of modules alone
                $modules = $unsorted_modules;
                $this->body .= "
						<thead>
							<tr class=col_hdr_top>
								<td class=col_hdr_left>
									Module Name
								</td>
								<td width=50% class=col_hdr_left>
									Admin Note
								</td>
								<td width=\"20%\" class=col_hdr_left>&nbsp;</td>
							</tr>
						</thead>";
                $this->row_count = 0;
                foreach ($modules as $module) {
                    $tag = "{module tag='{$module['module_replace_tag']}'}";
                    $edit_button = geoHTML::addButton('Edit', 'index.php?mc=view_modules&page=modules_page&b=' . $module['page_id']);
                    $this->body .= "
						<tr class=" . $this->get_row_color() . ">
							<td valign=top>
								<a href=index.php?mc=view_modules&page=modules_page&b=" . $module['page_id'] . ">
									" . $module['name'] . "
								</a><br>
								<span class=small_font>$tag</span>
							</td>
							<td class=medium_font align=left>
								" . $module['admin_label'] . "<br>
							</td>
							<td align=center valign=top>" . $edit_button . "</td>
						</tr>\n";
                    $this->row_count++;
                }
            }
        }
        $this->body .= "</table></fieldset><div class='clearColumn'></div>\n";
        return true;
    } //end of function browse_module_sections

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    function input_admin_label_and_tag_name($page, $is_page = 0, $show_submit = 0)
    {
        $type = ($is_page) ? 'page' : 'module';



        //attach the tooltips.
        $this->body .= "
			<script src='js/admin_pages_class_tooltips.js'></script>
			<div class='form-group adminNote'>
				<label class='control-label col-md-5 col-sm-5 col-xs-12'>Admin Note: " . $this->show_tooltip(6, 1) . "</label>
				<div class='col-md-6 col-sm-6 col-xs-12'>
					  <input type=text size=50 name=c[admin_label] class='form-control col-md-7 col-xs-12' value=\"" . $page["admin_label"] . "\">
				</div>
			</div>
			";

        if (!$is_page) {
            //this is a module, not a page so lets show its tag
            $show_tag = "{module tag='{$page['module_replace_tag']}'}";
            $this->body .= "
			<div class='form-group'>
			<label class='control-label col-md-5 col-sm-5 col-xs-12'>Template Tag: " . $this->show_tooltip(7, 1) . "</label>
			  <div class='col-md-6 col-sm-6 col-xs-12'>
			  <span class='vertical-form-fix'>$show_tag</span>
			  </div>
        	</div>
			";
        }

        if ($show_submit) {
            $this->body .= "<div class=\"input-group-btn center\"><input type=\"submit\" name=\"auto_save\" value=\"Save\" class=\"btn btn-primary\" /></div>";
        }
    }

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    function input_browsing_count_format(&$page)
    {
        $this->body .= "
			<div class='form-group'>
			<label class='control-label col-md-5 col-sm-5 col-xs-12'>Choose how to Display Category Counts: </label>
			  <div class='col-md-6 col-sm-6 col-xs-12'>
					<select name='c[browsing_count_format]' class='form-control col-md-7 col-xs-12'>
						<option " . (($page["browsing_count_format"] == 1) ? "selected" : "") . " value=1>
							Display Auction Count Only
						</option>
						<option " . (($page["browsing_count_format"] == 2) ? "selected" : "") . " value=2>
							Display Classified Count Only
						</option>
						<option " . (($page["browsing_count_format"] == 3) ? "selected" : "") . " value=3>
							Display Auction then Classified Count
						</option>
						<option " . (($page["browsing_count_format"] == 4) ? "selected" : "") . " value=4>
							Display Classified then Auction Count
						</option>
						<option " . (($page["browsing_count_format"] == 5) ? "selected" : "") . " value=5>
							Combined Count
						</option>
					</select>
			  </div>
			</div>
			";
        if ($this->modules_debug) {
            $this->body .= "<tr class=" . $this->get_row_color() . "><td align=center colspan=2 class=medium_error_font>browsing_count_format</td></tr>";
        }
        $this->row_count++;
    }

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    function input_module_category_level_to_display($page)
    {
        $this->body .= "
			<div class='form-group'>
			<label class='control-label col-md-5 col-sm-5 col-xs-12'>Display Category Count: </label>
			  <div class='col-md-6 col-sm-6 col-xs-12'>
				<input type=radio name='c[module_category_level_to_display]' value=0 " .
                (($page["module_category_level_to_display"] == 0) ? "checked" : "") . ">
				Main Categories<br>
				<input type=radio name='c[module_category_level_to_display]' value=1 " .
                (($page["module_category_level_to_display"] == 1) ? "checked" : "") . ">
				Second Level (all subcategories of the Main Categories only -- not Main Categories)
			  </div>
			</div>
			";
        if ($this->modules_debug) {
            $this->body .= "<tr class=" . $this->get_row_color() . "><td align=center colspan=2 class=medium_error_font>module_category_level_to_display</td></tr>";
        }
        $this->row_count++;
    }

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    function input_module_display_filter_in_row($page)
    {
        $this->body .= "
			<tr class=" . $this->get_row_color() . ">
				<td align=right class=medium_font>
					<strong>Display of Dropdowns:</strong>
				</td>
				<td>
					<span class=medium_font>
						<input type=radio name='c[module_display_filter_in_row]' value=1 " .
                        (($page["module_display_filter_in_row"] == 1) ? "checked" : "") . ">
						display dropdowns in a single row<br>
						<input type=radio name='c[module_display_filter_in_row]' value=0 " .
                        (($page["module_display_filter_in_row"] == 0) ? "checked" : "") . ">
						display dropdowns in a single column
					</span>
				</td>
			</tr>";
        if ($this->modules_debug) {
            $this->body .= "<tr class=" . $this->get_row_color() . "><td align=center colspan=2 class=medium_error_font>module_display_filter_in_row</td></tr>";
        }
        $this->row_count++;
    }

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    function input_module_display_username($db, $page)
    {
        $sql = "select * from " . $this->choices_table . " where type_of_choice = 12";
        $choices_result = $this->db->Execute($sql);
        if (!$choices_result) {
            return false;
        } elseif ($choices_result->RecordCount() > 0) {
            $options = "";
            while ($show = $choices_result->FetchRow()) {
                $options .= "
					<option value=" . $show["value"] . (($page["module_display_username"] == $show["value"]) ? " selected" : "") . ">
						" . $show["display_value"] . "
					</option>";
            }
        }
        $this->body .= "
			<div class='form-group'>
			<label class='control-label col-md-5 col-sm-5 col-xs-12'>Display Options: </label>
			  <div class='col-md-6 col-sm-6 col-xs-12'>
				<select name=c[module_display_username] class='form-control col-md-7 col-xs-12'>
					$options
				</select>
			  </div>
			</div>
			";
        if ($this->modules_debug) {
            $this->body .= "<tr class=" . $this->get_row_color() . "><td align=center colspan=2 class=medium_error_font>module_display_username</td></tr>";
        }
        $this->row_count++;
    }

    public function module_display_fields_link($activeTab = 'modules')
    {
        $this->body .= "
			<div><p class='page_note'>
					<strong>Edit Which Fields are Displayed:</strong>Edit on <a href='index.php?page=fields_to_use&amp;activeTab={$activeTab}'>Listing Setup &gt; Fields to Use</a>
				</p></div>";
        $this->row_count++;
    }

    function input_module_display_type_listing($page)
    {
        $this->body .= "

			<div class='form-group'>
			<label class='control-label col-md-5 col-sm-5 col-xs-12'>Display these Listing Types: </label>
			  <div class='col-md-6 col-sm-6 col-xs-12'>
					<input type=radio name='c[module_display_type_listing]' value=1 " .
                    (($page["module_display_type_listing"] == 1) ? "checked" : "") . ">
					Classifieds Only<br>
					<input type=radio name='c[module_display_type_listing]' value=2 " .
                    (($page["module_display_type_listing"] == 2) ? "checked" : "") . ">
					Auctions Only<br>";
        if (geoMaster::is('auctions')) {
            $this->body .= "<input type=radio name='c[module_display_type_listing]' value=2 " .
                (($page["module_display_type_listing"] == 4) ? "checked" : "") . ">
					Reverse Auctions Only<br>";
        }

        $this->body .= "
					<input type=radio name='c[module_display_type_listing]' value=0 " .
                    (($page["module_display_type_listing"] == 0) ? "checked" : "") . ">
					Classifieds & Auctions
			  </div>
			</div>
			";

        if ($this->modules_debug) {
            $this->body .= "<tr class=" . $this->get_row_color() . "><td align=center colspan=2 class=medium_error_font>module_display_type_listing</td></tr>";
        }
        $this->row_count++;
    }

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    function input_module_thumb_width_height($page)
    {
        //width of thumbnail
        $message = "
			<strong>Max Width of Thumbnail:</strong><br>
			<span class=small_font>
				If set to 0 the thumb width size will default to the thumb width set in site setup > browsing
			</span>";
        $this->input_option_dropdown($page, 'module_thumb_width', $message, 1000, 0);

        //height of thumbnail
        $message = "
			<strong>Max Height of Thumbnail:</strong><br>
			<span class=small_font>
				If set to 0 the thumb height size will default to the thumb height set in site setup > browsing
			</span>";
        $this->input_option_dropdown($page, 'module_thumb_height', $message, 1000, 0);
    }

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    function input_sign_flyer_width_height($db, $page_id, $page)
    {
        $sql_query = "select * from " . $this->ad_configuration_table;
        $ad_result = $this->db->Execute($sql_query);
        if (!$ad_result) {
            return false;
        } elseif ($ad_result->RecordCount() == 1) {
            $show_ad_configuration = $ad_result->FetchRow();
        }

        $type = ($page_id == 70) ? 'flyer' : 'sign';
        //width of sign/flyer
        $message = "Max Width to Display User Image (pixels):";
        $this->input_option_dropdown($show_ad_configuration, $type . '_maximum_image_width', $message, 1000);

        //height of sign/flyer
        $message = "Max Height to Display User Image (pixels):";
        $this->input_option_dropdown($show_ad_configuration, $type . '_maximum_image_height', $message, 1000);
    }

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    function input_module_display_title($page, $title_only = 0)
    {
        // Display text below thumbnail
        $message = ($title_only) ? 'Hide Title:' : 'Hide Title below Thumbnail:';
        $this->input_radio_yes_no($page, 'module_display_title', $message);
        if (!$title_only) {
            $this->row_count++;
            // Text Type below thumbnail
            // List for text types in dropdown
            $text_types = array('Title' => "title", 'Description' => "description", 'City' => "location_city", 'Zip' => "location_zip");
            for ($i = 1; $i < 21; $i++) {
                $text_types[$this->configuration_data['optional_field_' . $i . '_name']] = "optional_field_" . $i;
            }
            $this->body .= "
				<tr class=" . $this->get_row_color() . ">
					<td align=right width=50% class=medium_font>
						<strong>Type of Text for Title to Display below Thumbnail:</strong><br>
						<span class=small_font>
							This is only enabled if you selected 'no' above.
						</span>
					</td>
					<td class=medium_font>
						<select name=c[module_text_type]>";
            foreach ($text_types as $key => $value) {
                $this->body .= "<option value=" . $value . (($page["module_text_type"] == $value) ? " selected" : "") . ">" . $key . "</option>";
            }
            $this->body .= "
						</select>
					</td>
				</tr>";
            if ($this->modules_debug) {
                $this->body .= "<tr class=" . $this->get_row_color() . "><td align=center colspan=2 class=medium_error_font>module_text_type</td></tr>";
            }
            $this->row_count++;
        }
    }

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    function input_category_dropdown(&$db, &$page)
    {
        $this->body .= "
			<div class='form-group'>
			<label class='control-label col-md-5 col-sm-5 col-xs-12'>Category: <br>
					<span class=small_font>
						Choose the category that you wish to display featured listings from within this module.  The only listings that
						will be displayed by this module will be the featured listings within this category.
					</span></label>
			  <div class='col-md-6 col-sm-6 col-xs-12'>";
                $this->get_category_dropdown("c[module_category]", $page["module_category"], 1, $this->configuration_data["levels_of_categories_displayed_admin"]);
                $this->body .= $this->dropdown_body;
                $this->body .= "
			  </div>
			</div>
			";
        if ($this->modules_debug) {
            $this->body .= "<tr class=" . $this->get_row_color() . "><td align=center colspan=2 class=medium_error_font>module_category</td></tr>";
        }
        $this->row_count++;
    }

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    function input_module_display_location($page, $show_address = 0)
    {
        if ($show_address) {
            $this->input_radio_yes_no($page, 'module_display_address', 'Display Address:');
        }
        $this->input_radio_yes_no($page, 'module_display_city', 'Display City:');
        $this->input_radio_yes_no($page, 'module_display_state', 'Display State:');
        $this->input_radio_yes_no($page, 'module_display_country', 'Display Country:');
        $this->input_radio_yes_no($page, 'module_display_zip', 'Display Zip:');
    }

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    function input_module_display_optional_fields($page)
    {
        for ($i = 1; $i < 21; $i++) {
            $message = "Display <strong>" . $this->configuration_data['optional_field_' . $i . '_name'] . "</strong>:";
            $this->input_radio_yes_no($page, 'module_display_optional_field_' . $i, $message);
        }
    }

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    function input_title_module_text($db, $page_id, $page)
    {
        // Get text for the home page text
        $this->get_text($page_id);

        $sql = "select title_module_text,title_module_language_display from " . $this->ad_configuration_table;
        $result = $this->db->Execute($sql);
        if (!$result) {
            return false;
        } else {
            $title_result = $result->FetchRow();
        }
        // Default text
        $this->body .= "
			<div class='form-group'>
			<label class='control-label col-md-5 col-sm-5 col-xs-12'>Default Text to Display on Non-Listing Pages: </label>
			  <div class='col-md-6 col-sm-6 col-xs-12'>
			  <input type=text name=c[title_module_text] size=50 class='form-control col-md-7 col-xs-12' value=\"" . stripslashes(urldecode($title_result["title_module_text"])) . "\">
			  </div>
			</div>
        	";
        if ($this->modules_debug) {
            $this->body .= "<tr class=" . $this->get_row_color() . "><td align=center colspan=2 class=medium_error_font>title_module_text</td></tr>";
        }
        $this->row_count++;
        // Home page text
        $this->body .= "
			<div class='form-group'>
			<label class='control-label col-md-5 col-sm-5 col-xs-12'>Text to Display on Home Page: </label>
			  <div class='col-md-6 col-sm-6 col-xs-12'>
			  <input type=text name=c[title_module_home_text] size=50 class='form-control col-md-7 col-xs-12' value=\"" . stripslashes(urldecode($this->messages[2462])) . "\">
			  </div>
			</div>
			";

        $this->row_count++;
        // Display language used
        $this->body .= "
			<div class='form-group'>
			<label class='control-label col-md-4 col-sm-4 col-xs-12'></label>
			  <div class='col-md-7 col-sm-7 col-xs-12'>
				<input type=checkbox name=c[title_module_language_display] value=\"1\" " .
                    (($title_result["title_module_language_display"] == 1) ? "checked" : "") . ">&nbsp;
				Display Selected Language at End of Title
			  </div>
			</div>
			";
        //add tools to add specific title for any page in the system on the client side

        if ($this->modules_debug) {
            $this->body .= "<tr class=" . $this->get_row_color() . "><td align=center colspan=2 class=medium_error_font>title_module_home_text</td></tr>";
        }
        $this->row_count++;
        return true;
    }

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    function input_photo_or_icon($page)
    {
        $this->body .= '
			<div class="form-group">
				<label class="control-label col-md-5 col-sm-5 col-xs-12">Display Photo / Icon / Site Default:</label>
				<div class="col-md-6 col-sm-6 col-xs-12">
					<input type="radio" name="c[photo_or_icon]" value="1" ' . (($page["photo_or_icon"] == 1) ? 'checked="checked"' : '') . ' /> Photo<br />
					<input type="radio" name="c[photo_or_icon]" value="3" ' . (($page["photo_or_icon"] == 3) ? 'checked="checked"' : '') . ' /> Icon<br />
					<input type="radio" name="c[photo_or_icon]" value="2" ' . (($page["photo_or_icon"] == 2) ? 'checked="checked"' : '') . ' /> Site Default
				</div>
			</div>';
    }

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    function input_radio_yes_no($key, $variable, $message)
    {
      //need to invert the logic level for title since default is '1'
        $logic_level_1 = 1;
        $logic_level_2 = 0;

        $this->body .= '
			<div class="form-group">
				<label class="control-label col-md-5 col-sm-5 col-xs-12">' . $message . '</label>
				<div class="col-md-6 col-sm-6 col-xs-12">
					<input type="radio" name="c[' . $variable . ']" value="' . $logic_level_1 . '" ' . (($key[$variable] == $logic_level_1) ? 'checked="checked"' : '') . ' /> Yes<br />
					<input type="radio" name="c[' . $variable . ']" value="' . $logic_level_2 . '" ' . (($key[$variable] == $logic_level_2) ? 'checked="checked"' : '') . ' /> No
				</div>
			</div>';
    }

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    function input_module_display_ad_description_where($page)
    {
        $this->body .= '
			<div class="form-group">
				<label class="control-label col-md-5 col-sm-5 col-xs-12">Location of Listing Description:</label>
				<div class="col-md-6 col-sm-6 col-xs-12">
					<input type="radio" name="c[module_display_ad_description_where]" value="1" ' . (($page["module_display_ad_description_where"] == 1) ? 'checked="checked"' : '') . ' /> Below Title<br />
					<input type="radio" name="c[module_display_ad_description_where]" value="0" ' . (($page["module_display_ad_description_where"] == 0) ? 'checked="checked"' : '') . ' /> Own Column
				</div>
			</div>';
    }

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    function input_option_dropdown($key, $variable, $message, $MAX = 100, $start = 1)
    {
        $this->body .= '
			<div class="form-group">
				<label class="control-label col-md-5 col-sm-5 col-xs-12">' . $message . '</label>
				<div class="col-md-6 col-sm-6 col-xs-12">
					<input type="number" name="c[' . $variable . ']" value="' . (int)$key[$variable] . '" min="' . $start . '" max="10000000" size="5" class="form-control" />
				</div>
			</div>';
    }

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    function sortModules($moduleArray)
    {
        if (!is_array($moduleArray)) {
            return false;
        }
        $returnArray = array();
        //visit every recordset row
        //for($i=0;$i<count($moduleArray);$i++)
        foreach ($moduleArray as $i => $module) {
            //visit every module tier
            foreach ($module as $k => $val) {
                $prevNode = str_replace('tier', '', $k) - 1;
                if ($prevNode >= 0 && array_key_exists($module['tier' . $prevNode], $returnArray) && !in_array($val, $returnArray[$module['pier' . $prevNode]])) {
                    //if parent node exists, push this id into it's array
                    $returnArray[$module['tier' . $prevNode]][] = $moduleArray[$i][$k];
                }
                if (!isset($returnArray[$val])) {
                    //start new parent node
                    $returnArray[$val] = array();
                }
            }
        }
        return $returnArray;
    }

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
    function display_sections_home($section_index = '')
    {
        $this->browse_sections($this->db, 0, $section_index);

        $this->display_page();
    }
    function update_sections_home()
    {
    }
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
    function display_sections_browsing()
    {
        $this->browse_sections($this->db, 1, '_browsing');
        $this->display_page();
    }
    function update_sections_browsing()
    {
    }
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
    function display_sections_listing_process()
    {
        $this->browse_sections($this->db, 2, '_listing_process');
        $this->display_page();
    }
    function update_sections_listing_process()
    {
    }
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
    function display_sections_registration()
    {
        $this->browse_sections($this->db, 3, '_registration');
        $this->display_page();
    }
    function update_sections_registration()
    {
    }
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
    function display_sections_user_mgmt()
    {
        $this->browse_sections($this->db, 4, '_user_mgmt');
        $this->display_page();
    }
    function update_sections_user_mgmt()
    {
    }
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
    function display_sections_login_languages()
    {
        $this->browse_sections($this->db, 5, '_login_languages');
        $this->display_page();
    }
    function update_sections_login_languages()
    {
    }
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
    function display_sections_general_text()
    {
        $this->browse_sections($this->db, 15, '');
        $this->display_page();
    }
    function update_sections_general_text()
    {
    }
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
    function display_sections_extra_pages()
    {
        $this->browse_sections($this->db, 12, '_extra_pages');
        $this->display_page();
    }
    function update_sections_extra_pages()
    {
    }
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
    function display_sections_bidding()
    {
        $this->browse_sections($this->db, 14, '_bidding');
        $this->display_page();
    }
    function update_sections_bidding()
    {
    }
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
    function display_sections_show($section_index = '')
    {
        // Catch all function for sections_home links
        $url = "index.php";
        switch ($_REQUEST['b']) {
            case 1:
                $params = 'mc=pages_sections&page=sections_browsing';
                break;
            case 2:
                $params = 'mc=pages_sections&page=sections_listing_process';
                break;
            case 3:
                $params = 'mc=pages_sections&page=sections_registration';
                break;
            case 4:
                $params = 'mc=pages_sections&page=sections_user_mgmt';
                break;
            case 5:
                $params = 'mc=pages_sections&page=sections_login_languages';
                break;
            case 12:
                $params = 'mc=pages_sections&page=sections_extra_pages';
                break;
            case 14:
                $params = 'mc=pages_sections&page=sections_bidding';
                break;
            default:
                $params = '';
                $this->browse_sections($this->db, $_REQUEST["b"], $section_index);
                $this->display_page();
                return true;
                break;
        }
        header("location: " . $url . "?" . $params);
    }
    function update_sections_show()
    {
    }
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
    function display_sections_text_search()
    {
        if (!$this->browse_sections($this->db, $_REQUEST["b"])) {
            return false;
        }
        $this->display_page();
    }
    function update_sections_text_search()
    {
    }
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
    function display_sections_page($section_index = '')
    {
        if (!$this->display_current_page($this->db, $_REQUEST["b"], $section_index)) {
            return false;
        }

        $this->display_page();
    }
    function update_sections_page($section_index = '')
    {
        return $this->update_module_specifics($this->db, $_REQUEST["b"], $_REQUEST["c"], $section_index);
    }
    function display_modules_home()
    {
        $this->showModule();
        $this->display_page();
    }
    function update_modules_home()
    {
        return ;
    }
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
    function display_modules_browse()
    {
        if (!$this->showModule(1)) {
            return false;
        }
        $this->display_page();
    }
    function update_modules_browse()
    {
        return ;
    }
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
    function display_modules_featured()
    {
        if (!$this->showModule(2)) {
            return false;
        }
        $this->display_page();
    }
    function update_modules_featured()
    {
        return ;
    }
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
    function display_modules_newest()
    {
        if (!$this->showModule(3)) {
            return false;
        }
        $this->display_page();
    }
    function update_modules_newest()
    {
        return ;
    }

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
    function display_modules_misc()
    {
        if (!$this->showModule(6)) {
            return false;
        }
        $this->display_page();
    }
    function update_modules_misc()
    {
        return ;
    }
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
    function display_modules_misc_display()
    {
        if (!$this->showModule(7)) {
            return false;
        }
        $this->display_page();
    }
    function update_modules_misc_display()
    {
        return ;
    }
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
    function display_modules_page()
    {
        if ($_REQUEST["b"] && $_REQUEST["c"]) {
            if (!$this->display_current_page($this->db, $_REQUEST["b"])) {
                return false;
            }
        } elseif ($_REQUEST["b"]) {
            if (!$this->display_current_page($this->db, $_REQUEST["b"])) {
                return false;
            }
        } else {
            $this->show_modules();
        }

        $this->display_page();
    }
    function update_modules_page()
    {
        return $this->update_module_specifics($this->db, $_REQUEST["b"], $_REQUEST["c"]);
    }
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
    function display_modules_show()
    {
        if (PHP5_DIR) {
            $menu_loader = geoAdmin::getInstance();
        } else {
            $menu_loader =& geoAdmin::getInstance();
        }
        $this->body .= $menu_loader->getUserMessages();

        // Catch all function for modules_home links
        $url = "index.php";
        switch ($_REQUEST['b']) {
            case 1:
                $params = 'mc=view_modules&page=modules_browse';
                break;
            case 2:
                $params = 'mc=view_modules&page=modules_featured';
                break;
            case 3:
                $params = 'mc=view_modules&page=modules_newest';
                break;
            case 4:
                $params = 'mc=view_modules&page=modules_html';
                break;
            case 5:
                $params = 'mc=view_modules&page=modules_php';
                break;
            case 6:
                $params = 'mc=view_modules&page=modules_misc';
                break;
            case 7:
                $params = 'mc=view_modules&page=modules_misc_display';
                break;
            default:
                $params = '';

                $this->browse_module_sections($this->db, $_REQUEST["b"]);
                $this->display_page();
                return true;
                break;
        }
        header("location: " . $url . "?" . $params);
    }
    function update_modules_show()
    {
    }
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
    function showModule($id = 0)
    {
        if (PHP5_DIR) {
            $menu_loader = geoAdmin::getInstance();
        } else {
            $menu_loader =& geoAdmin::getInstance();
        }
        $this->body .= $menu_loader->getUserMessages();

        if ($id) {
            return $this->browse_module_sections($this->db, $id);
        } else {
            return $this->browse_module_sections($this->db);
        }
    }
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    //browsing duplicates
    function display_sections_browsing_page()
    {
        return $this->display_sections_page('_browsing');
    }
    function update_sections_browsing_page()
    {
        return $this->update_sections_page('_browsing');
    }
    function display_sections_browsing_show()
    {
        return $this->display_sections_show('_browsing');
    }

    //listing duplicates
    function display_sections_listing_process_page()
    {
        return $this->display_sections_page('_listing_process');
    }
    function update_sections_listing_process_page()
    {
        return $this->update_sections_page('_listing_process');
    }

    function display_sections_listing_process_show()
    {
        return $this->display_sections_show('_listing_process');
    }

    //registration duplicates
    function display_sections_registration_page()
    {
        return $this->display_sections_page('_registration');
    }
    function update_sections_registration_page()
    {
        return $this->update_sections_page('_registration');
    }

    function display_sections_registration_show()
    {
        return $this->display_sections_show('_registration');
    }

    //user_mgmt duplicates
    function display_sections_user_mgmt_page()
    {
        return $this->display_sections_page('_user_mgmt');
    }
    function update_sections_user_mgmt_page()
    {
        return $this->update_sections_page('_user_mgmt');
    }

    function display_sections_user_mgmt_show()
    {
        return $this->display_sections_show('_user_mgmt');
    }

    //login_languages duplicates
    function display_sections_login_languages_page()
    {
        return $this->display_sections_page('_login_languages');
    }
    function update_sections_login_languages_page()
    {
        return $this->update_sections_page('_login_languages');
    }

    function display_sections_login_languages_show()
    {
        return $this->display_sections_show('_login_languages');
    }

    //extra_pages duplicates
    function display_sections_extra_pages_page()
    {
        return $this->display_sections_page('_extra_pages');
    }
    function update_sections_extra_pages_page()
    {
        return $this->update_sections_page('_extra_pages');
    }

    function display_sections_extra_pages_show()
    {
        return $this->display_sections_show('_extra_pages');
    }

    //bidding duplicates
    function display_sections_bidding_page()
    {
        return $this->display_sections_page('_bidding');
    }
    function update_sections_bidding_page()
    {
        return $this->update_sections_page('_bidding');
    }

    function display_sections_bidding_show()
    {
        return $this->display_sections_show('_bidding');
    }


    function display_pages_management()
    {
        header("location: index.php?mc=pages_management&page=sections_home");
    }
} // end of class Admin_pages
