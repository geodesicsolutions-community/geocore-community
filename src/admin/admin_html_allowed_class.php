<?php

// admin_html_allowed_class.php


class HTML_allowed extends Admin_site
{

    var $internal_error_message = "There was an internal error";
    var $data_error_message = "Not enough data to complete request";
    var $page_text_error_message = "No text connected to this page";
    var $no_pages_message = "No pages to list";

    var $html_allowed_message;
    public function display_html_allowed_list()
    {
        $admin = geoAdmin::getInstance();

        $tpl_vars = array();

        $tpl_vars['notifications'] = $admin->getUserMessages();
        $sql_query = "select * from " . geoTables::html_allowed_table . " ORDER BY `tag_name`";

        $allTags = $this->db->GetAll($sql_query);
        $tags = array();
        $col = 0;
        foreach ($allTags as $tag) {
            $tags[$col % 2][] = $tag;
            $col++;
        }

        $tpl_vars['tag_columns'] = $tags;

        $tpl_vars['keep_tags_not_defined'] = $this->db->get_site_setting('keep_tags_not_defined');
        $admin->v()->setBodyTpl('disallowed_html.tpl')
            ->setBodyVar($tpl_vars);
        return true;
    } //end of function display_html_allowed_list

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    function update_html_allowed_list($db, $allowed_info = 0)
    {
        if ($allowed_info) {
            $sql_query = "select * from " . $this->html_allowed_table;
            $html_result = $this->db->Execute($sql_query);
            //echo $sql_query." is the query<bR>\n";
            if (!$html_result) {
                $this->error_message = $this->internal_error_message;
                $this->site_error($this->db->ErrorMsg());
                return false;
            } elseif ($html_result->RecordCount() > 0) {
                if (strlen(trim($allowed_info["new_tag"])) != 0) {
                //add new user-defined tag to db
                    $newTag = $allowed_info["new_tag"];

                    //make sure new tag is in a format we like:
                    //all lowercase, no spaces,
                    $newTag = strtolower($newTag);
                    $newTag = preg_replace("/[^a-z0-9]+/", "", $newTag);

                    //make sure tag doesn't already exist in database
                    $getExistingTags_query = "select * from " . $this->html_allowed_table . " WHERE `tag_name`=?";
                    $exist_result = $this->db->Execute($getExistingTags_query, array($newTag));
                    if (!$exist_result) {
                        $this->site_error($this->db->ErrorMsg());
                        return false;
                    }

                    $tagExists = false;
                    while ($row = $exist_result->FetchRow()) {
                        if ($newTag == $row["tag_name"]) {
                            $tagExists = true;
                        }
                    }

                    if (!$tagExists) {
                        $sql_query = "INSERT INTO " . $this->html_allowed_table;
                        $sql_query .= " (tag_name, tag_status, display, use_search_string, strongly_recommended) ";
                        $newTagAllowed = (isset($allowed_info['new_tag_allowed']) && $allowed_info['new_tag_allowed']) ? 0 : 1;
                        $sql_query .= "VALUES ('$newTag', $newTagAllowed, 1, 1, 2)";
                        $insert_result = $this->db->Execute($sql_query);
                        if (!$insert_result) {
                            $this->site_error($this->db->ErrorMsg());
                            return false;
                        }
                    } else {
                        echo "The tag you entered is already in the list and has not been added again.";
                    }
                }
                $this->db->set_site_setting('keep_tags_not_defined', (($allowed_info['keep_tags_not_defined']) ? 1 : false));
                //echo $html_result->RecordCount()." is result count<br>\n";
                while ($show = $html_result->FetchRow()) {
                    if ($allowed_info[$show["tag_id"]] == 2) {
                        $sql_query = "DELETE FROM " . $this->html_allowed_table . " WHERE
							tag_id = " . $show["tag_id"];
                        $delete_result = $this->db->Execute($sql_query);
                        if (!$delete_result) {
                            $this->site_error($this->db->ErrorMsg());
                            return false;
                        }
                    }

                    //echo $allowed_info[$show["tag_id"]]." is allowed<bR>\n";
                    //echo $show["tag_status"]."<br><br>\n";
                    elseif ($allowed_info[$show["tag_id"]] != $show["tag_status"]) {
                        $sql_query = "update " . $this->html_allowed_table . " set
							tag_status = " . intval($allowed_info[$show["tag_id"]]) . "
							where tag_id = " . $show["tag_id"];
                        //echo $sql_query." is the query<bR>\n";
                        $update_result = $this->db->Execute($sql_query);
                        if (!$update_result) {
                            $this->site_error($this->db->ErrorMsg());
                            return false;
                        }
                    }
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
    } //end of function update_html_allowed_list

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
    function display_main_html_allowed()
    {
        $this->display_html_allowed_list();
        $this->display_page();
    }
    function update_main_html_allowed()
    {
        if ($_REQUEST["b"]) {
            //update html allowed
            return $this->update_html_allowed_list($db, $_REQUEST["b"]);
        } else {
            return false;
        }
    }
} //end of class HTML_allowed
