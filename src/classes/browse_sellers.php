<?php

//browse_sellers.php


class Browse_sellers extends geoBrowse
{
    var $subcategory_array = array();
    var $notify_data = array();
    var $seller_configuration_data;
    var $debug_sellers = 0;

//########################################################################

    public function __construct($db, $classified_user_id, $language_id, $category_id = 0, $page = 0, $classified_id = 0, $product_configuration = 0)
    {
        if ($category_id) {
            $this->site_category = (int)$category_id;
        } elseif ($classified_id) {
            $listing = geoListing::getListing($classified_id);
            if ($listing && $listing->category) {
                $this->site_category = (int)$listing->category;
            }
        } else {
            $this->site_category = 0;
        }
        if ($limit) {
            $this->browse_limit = (int)$limit;
        }

        $db = $this->db = DataAccess::getInstance();

        $this->get_ad_configuration($db);
        if ($page) {
            $this->page_result = (int)$page;
        } else {
            $this->page_result = 1;
        }
        parent::__construct();
    }

//###########################################################

    function browse($db, $browse_type = 0)
    {
        $db = DataAccess::getInstance();
        $this->browse_type = $browse_type;
        $this->page_id = 113;
        $this->get_text();

        $this->get_seller_configuration_data($db);
        if (!$this->seller_configuration_data) {
            return false;
        }

        $this->body .= "<table cellpadding=2 cellspacing=1 border=0 width=\"100%\">\n";
        if (strlen(trim($this->messages[500021])) > 0) {
            $this->body .= "<tr class=\"browse_sellers_main_page_title\">\n\t<td valign=top height=20>" . $this->messages[500021] . "</td>\n</tr>\n";
        }
        if (strlen(trim($this->messages[500022])) > 0) {
            $this->body .= "<tr class=\"browse_sellers_main_page_message\">\n\t<td valign=top height=20>" . $this->messages[500022] . "</td>\n</tr>\n";
        }

        //WOW this entire thing needs to be re-coded...  Since it still exists, just going to patch it
        //to work with new category stuff, we may want to re-visit this later to bring it in spec
        //with rest of the software...

        $classTable = geoTables::classifieds_table;
        $listCatTable = geoTables::listing_categories;

        $this->site_category = (int)$this->site_category;

        $cat_subquery = "SELECT * FROM $listCatTable WHERE $listCatTable.`listing`=$classTable.`id`
			AND $listCatTable.`category`={$this->site_category}";

        $sql = "SELECT DISTINCT(`seller`) FROM $classTable WHERE
			EXISTS ($cat_subquery) AND `live` = 1 ";
        switch ($browse_type) {
            case 0: //normal
                $sql .= " order by seller desc ";
                break;
            case 1: //seller asc
                $sql .= " order by seller asc ";
                break;
            default:
                $sql .= " order by seller desc ";
                break;
        }

        $sql .= " limit " . (($this->page_result - 1) * $this->seller_configuration_data->MODULE_NUMBER_OF_ADS_TO_DISPLAY) . "," . $this->seller_configuration_data->MODULE_NUMBER_OF_ADS_TO_DISPLAY;

        //echo $sql." is the query<br>\n";
        $sql_count = "select count(distinct(seller)) as total from " . $this->classifieds_table . " where
			EXISTS($cat_subquery) and live = 1";
        if ($this->debug_sellers) {
            echo $sql_count . " at top<br>\n";
        }
        $result = $this->db->Execute($sql);
        if (!$result) {
            if ($this->debug_sellers) {
                echo $sql_count . "<br>\n";
            }
            $this->error_message = "<span class=\"error_message\">" . $this->messages[10364] . "</span>";
            return false;
        } else {
            if ($sql_count) {
                $total_count_result = $this->db->Execute($sql_count);
                //echo $sql_count." is the query<br>\n";
                if ($total_count_result) {
                    $show_total = $total_count_result->FetchNextObject();
                    $total_returned = $show_total->TOTAL;
                    //$this->body .=$total_returned." is the total returned<br>\n";
                }
            }
            //get this categories name
            if ($this->site_category) {
                $this->body .= "<tr>\n\t<td valign=top class=\"back_to_normal_browsing\">
					<a href=" . $this->configuration_data['classifieds_url'] . "?a=5&b=" . $this->site_category . " class=\"back_to_normal_browsing\">";
                $this->body .= $this->messages[1952] . "</a></td>\n<tr>\n";
                if ($this->browse_type) {
                    $this->body .= "<tr>\n\t<td valign=top class=\"back_to_normal_browsing\">
						<a href=" . $this->configuration_data['classifieds_url'] . "?a=25&b=" . $this->site_category . " class=\"back_to_normal_browsing\">";
                    $this->body .= $this->messages[1953] . "</a></td>\n<tr>\n";
                }
                $current_category_name = geoCategory::getName($this->site_category);
                if ($current_category_name->SELLER_CACHE_EXPIRE > geoUtil::time() && $this->configuration_data['use_category_cache'] && $current_category_name->SELLER_CACHE_EXPIRE != 0 && !$this->db->getTableSelect(DataAccess::SELECT_BROWSE)->hasWhere()) {
                    //use the cache
                    //echo "using category cache<br>\n";
                    $this->body .= $current_category_name->SELLER_CATEGORY_CACHE;
                } else {
                    //get the categories inside of this category
                    $sql = "select * from " . $this->categories_table . " where
						parent_id = " . $this->site_category . " order by display_order,category_name";
                    $category_result = $this->db->Execute($sql);
                    if ($this->debug_sellers) {
                        echo $sql . "<br>\n";
                    }
                    if (!$category_result) {
                        if ($this->debug_sellers) {
                            echo $sql . "<br>\n";
                        }
                        $this->error_message = "<span class=\"error_message\">" . $this->messages[10364] . "</span>";
                        return false;
                    } else {
                        if ($category_result->RecordCount() > 0) {
                            $this->category_cache .= "<tr>\n\t<td valign=top height=20>\n\t<table cellpadding=2 cellspacing=1 border=0 width=\"100%\">\n\t";
                            switch ($this->seller_configuration_data->MODULE_NUMBER_OF_COLUMNS) {
                                case 1:
                                    $column_width = "100%";
                                case 2:
                                    $column_width = "50%";
                                case 3:
                                    $column_width = "33%";
                                case 4:
                                    $column_width = "25%";
                                case 5:
                                    $column_width = "20%";
                            } //end of switch

                            while ($row = $category_result->FetchRow()) {
                                $categories [$row['category_name']] ['category_id'] = $row['category_id'];
                                $categories [$row['category_name']] ['category_image'] = $row['category_image'];
                                $categories [$row['category_name']] ['category_count'] = $row['category_count'];
                                $categories [$row['category_name']] ['auction_category_count'] = $row['auction_category_count'];
                                $categories [$row['category_name']] ['category_name'] = $row['category_name'];
                                $categories [$row['category_name']] ['category_description'] = $row['description'];
                                if (isset($sub_categories[$row['category_name']] ['sub_categories'])) {
                                    $categories[$row['category_name']] ['sub_categories'] = $sub_categories [$row['category_name']] ['sub_categories'];
                                } else {
                                    $categories [$row['category_name']] ['sub_categories'] = 'na';
                                }
                            }

                            $category_count = 0;
                            if ($this->seller_configuration_data->ALPHA_ACROSS_COLUMNS) {
                                foreach ($categories as $show_category) {
                                    if (! ($category_count % $this->seller_configuration_data->MODULE_NUMBER_OF_COLUMNS)) {
                                            $this->category_cache .= "<tr>\n\t";
                                    }

                                    //display the sub categories of this category
                                    $this->category_cache .= "<td valign=top width=" . $column_width . "><a href=" . $this->configuration_data['classifieds_file_name'] . "?a=25&b=" . $show_category['category_id'] . "&c=" . $browse_type . ">";
                                    if ((strlen(trim($show_category['category_image'])) > 0) && ($this->seller_configuration_data->DISPLAY_CATEGORY_IMAGE)) {
                                        $this->category_cache .= "<img src=\"" . geoTemplate::getUrl('', $show_category['category_image']) . "\" hspace=2 vspace=0 border=0 align=left>";
                                    }

                                    $this->category_cache .= "<span class=\"browsing_subcategory_name\">" . $show_category['category_name'] . "</span>";
                                    if ($this->seller_configuration_data->DISPLAY_CATEGORY_COUNT) {
                                        $this->category_cache .= "<span class=\"browsing_subcategory_count\">(" . $this->get_seller_category_count($db, $show_category['category_id']) . ")</span>";
                                    }
                                    if ($this->seller_configuration_data->DISPLAY_CATEGORY_DESCRIPTION) {
                                        $this->category_cache .= "</a><br><span class=\"browsing_subcategory_description\">" . $show_category['category_description'] . "</span>";
                                    }
                                    $this->category_cache .= "</td>";

                                    $category_count++;
                                    if (! ($category_count % $this->seller_configuration_data->MODULE_NUMBER_OF_COLUMNS)) {
                                        $this->category_cache .= "</tr>";
                                    }
                                }
                            } else {
                                // Data variables
                                $total = count($categories);
                                $num_cols = $this->seller_configuration_data->MODULE_NUMBER_OF_COLUMNS;
                                // computational variables
                                $col_amount = ceil($total / $num_cols); // get the number of items per column (max)
                                $categories_x = array_values($categories); // convert associative to numeric array
                                $long_cols = $total % $num_cols; // the amount of columns that will have extra. think 10 categories with 3 colums = 1 long column
                                // iterator variables
                                $current_col = 0;
                                $current_row = 0;


                                for ($x = 0; $x < $total; $x++, $current_col++) {
                                    if ($current_col >= $num_cols) {
                                        $current_col = 0;
                                        $current_row++;
                                    }

                                    if ($current_col < $long_cols + 1) {
                                        $next_cat = $current_row + ($current_col * $col_amount);
                                    } else {
                                        $next_cat = $current_row + ($current_col * ($col_amount)) - 1;
                                    }

                                    //echo $next_cat." "; // debuging variable.
                                    $show_category = $categories_x[$next_cat];
                                    //var_dump($show_category);

                                    if (! ($category_count % $this->seller_configuration_data->MODULE_NUMBER_OF_COLUMNS)) {
                                            $this->category_cache .= "<tr>\n\t";
                                    }

                                    //display the sub categories of this category
                                    $this->category_cache .= "<td valign=top width=" . $column_width . "><a href=" . $this->configuration_data['classifieds_file_name'] . "?a=25&b=" . $show_category['category_id'] . "&c=" . $browse_type . ">";
                                    if ((strlen(trim($show_category['category_image'])) > 0) && ($this->seller_configuration_data->DISPLAY_CATEGORY_IMAGE)) {
                                        $this->category_cache .= "<img src=\"" . geoTemplate::getUrl('', $show_category['category_image']) . "\" hspace=2 vspace=0 border=0 align=left>";
                                    }

                                    $this->category_cache .= "<span class=\"browsing_subcategory_name\">" . $show_category['category_name'] . "</span>";
                                    if ($this->seller_configuration_data->DISPLAY_CATEGORY_COUNT) {
                                        $this->category_cache .= "<span class=\"browsing_subcategory_count\">(" . $this->get_seller_category_count($db, $show_category['category_id']) . ")</span>";
                                    }
                                    if ($this->seller_configuration_data->DISPLAY_CATEGORY_DESCRIPTION) {
                                        $this->category_cache .= "</a><br><span class=\"browsing_subcategory_description\">" . $show_category['category_description'] . "</span>";
                                    }
                                    $this->category_cache .= "</td>";

                                    $category_count++;
                                    if (! ($category_count % $this->seller_configuration_data->MODULE_NUMBER_OF_COLUMNS)) {
                                        $this->category_cache .= "</tr>";
                                    }
                                }
                            }
                            $this->category_cache .= "</table>\n\t</td>\n</tr>\n";
                        } else {
                            //if ($this->seller_configuration_data->DISPLAY_NO_SUBCATEGORY_MESSAGE)
                                //$this->category_cache .="<tr class=\"no_subcategories_to\">\n\t<td valign=top height=20>\n\t".$this->messages[1962]." ".$current_category_name->CATEGORY_NAME."\n\t</td>\n</tr>\n";
                        }
                    }

                    $category_tree = $this->category_tree_array = geoCategory::getTree($this->site_category);

                    if ($category_tree) {
                        //category tree
                        $this->category_cache .= "<tr class=\"main\">\n\t<td valign=top height=20 class=\"browsing_category_tree\">\n\t";
                        $this->category_cache .= $this->messages[2452] . " <a href=" . $this->configuration_data['classifieds_file_name'] . "?a=25&c=" . $browse_type . " class=\"main\">" . $this->messages[2453] . "</a> > ";
                        if (is_array($this->category_tree_array)) {
                            $i = 0;
                            foreach ($this->category_tree_array as $cat) {
                                //display all the categories
                                if (++$i == count($this->category_tree_array)) {
                                    //last one -- no linky because we're already here!
                                    $this->category_cache .= $cat[$i]["category_name"];
                                } else {
                                    $this->category_cache .= "<a href=" . $this->configuration_data['classifieds_file_name'] . "?a=25&b=" . $cat["category_id"] . "&c=" . $browse_type . " class=\"browsing_category_tree\">" . $cat[$i]["category_name"] . "</a> > ";
                                }
                            }
                        } else {
                            $this->category_cache .= $category_tree;
                        }
                        $this->category_cache .= "\n\t</td>\n</tr>\n";
                    }
                    if ($this->configuration_data['use_category_cache'] && !$this->db->getTableSelect(DataAccess::SELECT_BROWSE)->hasWhere()) {
                        $recache_time = geoUtil::time() + (3600 * $this->configuration_data['use_category_cache']);
                        $sql = "update " . $this->categories_languages_table . " set
							seller_category_cache = \"" . addslashes(urlencode($this->category_cache)) . "\",
							seller_cache_expire = \"" . $recache_time . "\"
							where category_id = " . $this->site_category . " and language_id = " . $this->language_id;
                        if ($this->debug_sellers) {
                            echo $sql . "<br>\n";
                        }
                        $cache_result = $this->db->Execute($sql);
                        if (!$cache_result) {
                            if ($this->debug_sellers) {
                                echo $sql . "<br>\n";
                            }
                            $this->error_message = "<span class=\"error_message\">" . $this->messages[10364] . "</span>";
                            return false;
                        }
                    }
                    $this->body .= urldecode(stripslashes($this->category_cache));
                }
                if ($this->debug_sellers) {
                    echo $total_returned . " is total_returned<br>\n";
                    echo $this->seller_configuration_data->MODULE_NUMBER_OF_ADS_TO_DISPLAY . " is MODULE_NUMBER_OF_ADS_TO_DISPLAY<Br>\n";
                }
                if ($this->seller_configuration_data->MODULE_NUMBER_OF_ADS_TO_DISPLAY < $total_returned) {
                    //display the link to the next 10
                    $number_of_page_results = ceil($total_returned / $this->seller_configuration_data->MODULE_NUMBER_OF_ADS_TO_DISPLAY);
                    $this->body .= "<tr class=\"browsing_result_page_links\">\n\t<td valign=top><span class=\"more_results\">" . $this->messages[500025] . " " . $this->page_result . " </span><span class=\"page_of\">" . $this->messages[500024] . ceil($total_returned / $this->seller_configuration_data->MODULE_NUMBER_OF_ADS_TO_DISPLAY) . "</span></td>\n</tr>\n";
                }

                $result->Move(0);
                $this->display_browse_result($result, "browsing_result_table_header");
                if ($this->seller_configuration_data->MODULE_NUMBER_OF_ADS_TO_DISPLAY < $total_returned) {
                    //display the link to the next 10
                    $number_of_page_results = ceil($total_returned / $this->seller_configuration_data->MODULE_NUMBER_OF_ADS_TO_DISPLAY);
                    $this->body .= "<tr class=\"more_results\">\n\t<td valign=top>" . $this->messages[2454] . " ";
                    if ($number_of_page_results < 10) {
                        for ($i = 1; $i <= $number_of_page_results; $i++) {
                            if ($this->page_result == $i) {
                                $this->body .= " <b>" . $i . "</b> ";
                            } else {
                                $this->body .= "<a href=" . $this->configuration_data['classifieds_file_name'] . "?a=25&b=" . $this->site_category . "&page=" . $i . "&c=" . $browse_type . " class=\"browsing_result_page_links\">" . $i . "</a> ";
                            }
                        }
                    } else {
                        $number_of_sections =  ceil($number_of_page_results / 10);
                        for ($section = 0; $section < $number_of_sections; $section++) {
                            if (($this->page_result > ($section * 10)) && ($this->page_result <= (($section + 1) * 10))) {
                                //display the individual pages within this section
                                for ($page = (($section * 10) + 1); $page <= (($section + 1) * 10); $page++) {
                                    if ($page <= $number_of_page_results) {
                                        $this->body .= "<a href=" . $this->configuration_data['classifieds_file_name'] . "?a=25&b=" . $this->site_category . "&page=" . $page . "&c=" . $browse_type . " class=\"browsing_result_page_links\">" . $page . "</a> ";
                                    }
                                }
                            } else {
                                //display the link to the section
                                $this->body .= "<a href=" . $this->configuration_data['classifieds_file_name'] . "?a=25&b=" . $this->site_category . "&page=" . (($section * 10) + 1) . "&c=" . $browse_type . " class=\"browsing_result_page_links\">" . (($section * 10) + 1) . "</a>";
                            }
                            if (($section + 1) < $number_of_sections) {
                                $this->body .= "<span class=\"browsing_result_page_links\">..</span>";
                            }
                        }
                    }
                    $this->body .= "</td>\n</tr>\n";
                }
            } else {
                if (!$this->browse_main($db)) {
                    $this->error_message = "<span class=\"error_message\">" . $this->messages[10364] . "</span>";
                    return false;
                } else {
                    return true;
                }
            }
        }
        $this->body .= "</table>\n";
        $this->display_page($db);
        return true;
    } //end of function browse

//####################################################################################

    function display_browse_result($browse_result)
    {
        $db = DataAccess::getInstance();
        if ($browse_result->RecordCount() > 0) {
            $browse_result->Move(0);
            $link_text = "<a href=" . $this->configuration_data['classifieds_file_name'] . "?a=25&b=" . $this->site_category . "&c=";

            //display the ads inside of this category
            $this->body .= "<tr>\n\t<td  height=20>\n\t";
            $this->body .= "<table cellpadding=3 cellspacing=1 border=0 align=center width=\"100%\">\n\t";
            $this->body .= "<tr class=\"column_headers\">\n\t\t";

            if ($this->seller_configuration_data->MODULE_DISPLAY_USERNAME) {
                $this->body .= "<td>" . $this->messages[1963] . "</td>\n\t";
            }



            if ($this->seller_configuration_data->MODULE_DISPLAY_BUSINESS_TYPE) {
//                          $this->body .="<td>".$link_text;
//                          if ($this->browse_type == 43) $this->body .= "44";
//                          elseif ($this->browse_type == 44) $this->body .= "0";
//                          else $this->body .= "43";
//                          $this->body .= " class=\"column_headers\">".$this->messages[1954]."</a></td>\n\t";

                $this->body .= "<td>" . $this->messages[1954] . "</td>\n\t";
            }

            if ($this->seller_configuration_data->MODULE_DISPLAY_COMPANY_NAME) {
                $this->body .= "<td>" . $this->messages[500023] . "</td>\n\t";
            }

            if ($this->seller_configuration_data->MODULE_DISPLAY_NAME) {
                $this->body .= "<td>" . $this->messages[1955] . "</td>\n\t";
            }
            if ($this->seller_configuration_data->MODULE_DISPLAY_OPTIONAL_FIELD_1) {
                $this->body .= "<td >" . $this->messages[1965] . "</td>\n\t";
            }
            if ($this->seller_configuration_data->MODULE_DISPLAY_OPTIONAL_FIELD_2) {
                $this->body .= "<td >" . $this->messages[1966] . "</td>\n\t";
            }

            if ($this->seller_configuration_data->MODULE_DISPLAY_OPTIONAL_FIELD_3) {
                $this->body .= "<td >" . $this->messages[1967] . "</td>\n\t";
            }

            if ($this->seller_configuration_data->MODULE_DISPLAY_OPTIONAL_FIELD_4) {
                $this->body .= "<td >" . $this->messages[1968] . "</td>\n\t";
            }

            if ($this->seller_configuration_data->MODULE_DISPLAY_OPTIONAL_FIELD_5) {
                $this->body .= "<td >" . $this->messages[1969] . "</td>\n\t";
            }

            if ($this->seller_configuration_data->MODULE_DISPLAY_OPTIONAL_FIELD_6) {
                $this->body .= "<td >" . $this->messages[1970] . "</td>\n\t";
            }

            if ($this->seller_configuration_data->MODULE_DISPLAY_OPTIONAL_FIELD_7) {
                $this->body .= "<td >" . $this->messages[1971] . "</td>\n\t";
            }

            if ($this->seller_configuration_data->MODULE_DISPLAY_OPTIONAL_FIELD_8) {
                $this->body .= "<td >" . $this->messages[1972] . "</td>\n\t";
            }

            if ($this->seller_configuration_data->MODULE_DISPLAY_OPTIONAL_FIELD_9) {
                $this->body .= "<td >" . $this->messages[1973] . "</td>\n\t";
            }

            if ($this->seller_configuration_data->MODULE_DISPLAY_OPTIONAL_FIELD_10) {
                $this->body .= "<td >" . $this->messages[1974] . "</td>\n\t";
            }
            if ($this->seller_configuration_data->MODULE_DISPLAY_ADDRESS) {
                $this->body .= "<td >" . $this->messages[1964] . "</td>\n\t";
            }
            if ($this->seller_configuration_data->MODULE_DISPLAY_CITY) {
                $this->body .= "<td>" . $this->messages[1956] . "</td>\n\t";
            }
            if ($this->seller_configuration_data->MODULE_DISPLAY_STATE) {
                $this->body .= "<td>" . $this->messages[1957] . "</td>\n\t";
            }
            if ($this->seller_configuration_data->MODULE_DISPLAY_COUNTRY) {
                $this->body .= "<td>" . $this->messages[1958] . "</td>\n\t";
            }
            if ($this->seller_configuration_data->MODULE_DISPLAY_ZIP) {
                $this->body .= "<td>" . $this->messages[1959] . "</td>\n\t";
            }
            if ($this->seller_configuration_data->MODULE_DISPLAY_PHONE1) {
                $this->body .= "<td >" . $this->messages[1975] . "</td>\n\t";
            }
            if ($this->seller_configuration_data->MODULE_DISPLAY_PHONE2) {
                $this->body .= "<td >" . $this->messages[1976] . "</td>\n\t";
            }

            $this->body .= "</tr>\n\t";

            $this->row_count = 0;
            while ($show_classifieds = $browse_result->FetchNextObject()) {
                if (($this->row_count % 2) == 0) {
                    $css_class_tag =  "browsing_result_table_body_even ";
                } else {
                    $css_class_tag =  "browsing_result_table_body_odd ";
                }
                $this->body .= "<tr class=" . $css_class_tag . ">\n\t\t";
                $seller_data = $this->get_user_data($show_classifieds->SELLER);

                $link = "<a href=\"{$this->configuration_data['classifieds_file_name']}?a=6&amp;b={$show_classifieds->SELLER}\">";

                //display username
                if ($this->seller_configuration_data->MODULE_DISPLAY_USERNAME) {
                    $this->body .= "<td >$link" . $seller_data->USERNAME . "</a></td>\n\t";
                }

                //display company type
                if ($this->seller_configuration_data->MODULE_DISPLAY_BUSINESS_TYPE) {
//                              $this->body .="<td>";
//                              if ($seller_data->BUSINESS_TYPE == 1)
//                                  $this->body .= $this->messages[10010];
//                              elseif ($seller_data->BUSINESS_TYPE == 2)
//                                  $this->body .= $this->messages[10009];
//                              else
//                                  $this->body .= "&nbsp;";
//                              $this->body .= "</td>\n\t";

                    $this->body .= "<td>";
                    if ($seller_data->BUSINESS_TYPE == 1) {
                        $this->body .= $this->messages[1961];
                    } elseif ($seller_data->BUSINESS_TYPE == 2) {
                        $this->body .= $this->messages[1960];
                    } else {
                        $this->body .= "&nbsp;";
                    }
                    $this->body .= "</td>\n\t";
                }

                //display company name
                if ($this->seller_configuration_data->MODULE_DISPLAY_COMPANY_NAME) {
                    $this->body .= "<td>$link" . stripslashes($seller_data->COMPANY_NAME) . "</a></td>\n\t";
                }

                //display name
                if ($this->seller_configuration_data->MODULE_DISPLAY_NAME) {
                    $this->body .= "<td>$link" . stripslashes($seller_data->FIRSTNAME) . " " . stripslashes($seller_data->LASTNAME) . "</a></td>\n\t";
                }

                if ($this->seller_configuration_data->MODULE_DISPLAY_OPTIONAL_FIELD_1) {
                    $this->body .= "<td >" . stripslashes($seller_data->OPTIONAL_FIELD_1) . "</td>\n\t";
                }

                if ($this->seller_configuration_data->MODULE_DISPLAY_OPTIONAL_FIELD_2) {
                    $this->body .= "<td >" . stripslashes($seller_data->OPTIONAL_FIELD_2) . "</td>\n\t";
                }

                if ($this->seller_configuration_data->MODULE_DISPLAY_OPTIONAL_FIELD_3) {
                    $this->body .= "<td >" . stripslashes($seller_data->OPTIONAL_FIELD_3) . "</td>\n\t";
                }

                if ($this->seller_configuration_data->MODULE_DISPLAY_OPTIONAL_FIELD_4) {
                    $this->body .= "<td >" . stripslashes($seller_data->OPTIONAL_FIELD_4) . "</td>\n\t";
                }

                if ($this->seller_configuration_data->MODULE_DISPLAY_OPTIONAL_FIELD_5) {
                    $this->body .= "<td >" . stripslashes($seller_data->OPTIONAL_FIELD_5) . "</td>\n\t";
                }

                if ($this->seller_configuration_data->MODULE_DISPLAY_OPTIONAL_FIELD_6) {
                    $this->body .= "<td >" . stripslashes($seller_data->OPTIONAL_FIELD_6) . "</td>\n\t";
                }

                if ($this->seller_configuration_data->MODULE_DISPLAY_OPTIONAL_FIELD_7) {
                    $this->body .= "<td >" . stripslashes($seller_data->OPTIONAL_FIELD_7) . "</td>\n\t";
                }

                if ($this->seller_configuration_data->MODULE_DISPLAY_OPTIONAL_FIELD_8) {
                    $this->body .= "<td >" . stripslashes($seller_data->OPTIONAL_FIELD_8) . "</td>\n\t";
                }

                if ($this->seller_configuration_data->MODULE_DISPLAY_OPTIONAL_FIELD_9) {
                    $this->body .= "<td >" . stripslashes($seller_data->OPTIONAL_FIELD_9) . "</td>\n\t";
                }

                if ($this->seller_configuration_data->MODULE_DISPLAY_OPTIONAL_FIELD_10) {
                    $this->body .= "<td >" . stripslashes($seller_data->OPTIONAL_FIELD_10) . "</td>\n\t";
                }

                //display address
                if ($this->seller_configuration_data->MODULE_DISPLAY_ADDRESS) {
                    $this->body .= "<td >" . stripslashes($seller_data->ADDRESS) . " " . stripslashes($seller_data->ADDRESS_2) . "</td>\n\t";
                }

                //display city
                if ($this->seller_configuration_data->MODULE_DISPLAY_CITY) {
                    $this->body .= "<td >" . stripslashes($seller_data->CITY) . "</td>\n\t";
                }

                //display state
                if ($this->seller_configuration_data->MODULE_DISPLAY_STATE) {
                    $this->body .= "<td >" . stripslashes($seller_data->STATE) . "</td>\n\t";
                }

                //display country
                if ($this->seller_configuration_data->MODULE_DISPLAY_COUNTRY) {
                    $this->body .= "<td >" . stripslashes($seller_data->COUNTRY) . "</td>\n\t";
                }

                //display zip
                if ($this->seller_configuration_data->MODULE_DISPLAY_ZIP) {
                    $this->body .= "<td >" . stripslashes($seller_data->ZIP) . "</td>\n\t";
                }

                //display phone
                if ($this->seller_configuration_data->MODULE_DISPLAY_PHONE1) {
                    $this->body .= "<td >" . stripslashes($seller_data->PHONE) . "</td>\n\t";
                }

                //display phone2
                if ($this->seller_configuration_data->MODULE_DISPLAY_PHONE2) {
                    $this->body .= "<td >" . stripslashes($seller_data->PHONE2) . "</td>\n\t";
                }

                $this->body .= "</tr>\n\t";
                $this->row_count++;
            } //end of while
            $this->body .= "</table>\n\t</td>\n</tr>\n";
        } else {
            //no classifieds in this category
            $this->body .= "<tr class=\"no_sellers_in_category\">\n\t<td >\n\t" . $this->messages[1962] . "\n\t</td>\n</tr>\n";
        }
        return;
    } //end of function display_browse_result

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    function DateDifference($interval, $date1, $date2)
    {
        $difference =  $date2 - $date1;
        switch ($interval) {
            case "w":
                $returnvalue  = $difference / 604800;
                break;
            case "d":
                $returnvalue  = $difference / 86400;
                break;
            case "h":
                $returnvalue = $difference / 3600;
                break;
            case "m":
                $returnvalue  = $difference / 60;
                break;
            case "s":
                $returnvalue  = $difference;
                break;
        }
            return $returnvalue;
    } //end of function DateDifference

//####################################################################################

    function main()
    {
        $this->page_id = 113;
        $this->get_text();
        $this->body .= "<table cellpadding=5 cellspacing=1 border=0 width=\"100%\">\n";
        if (strlen(trim($this->messages[500021])) > 0) {
            $this->body .= "<tr class=\"browse_sellers_main_page_title\">\n\t<td valign=top height=20>" . $this->messages[500021] . "</td>\n</tr>\n";
        }
        if (strlen(trim($this->messages[500022])) > 0) {
            $this->body .= "<tr class=\"browse_sellers_main_page_message\">\n\t<td valign=top height=20>" . $this->messages[500022] . "</td>\n</tr>\n";
        }
        $this->body .= "<tr>\n\t<td valign=top>\n\t";
        if (!$this->browse_main()) {
            $this->browse_error();
        }
        $this->body .= "</td>\n</tr>\n";
        $this->body .= "</table>\n";
        $this->display_page();
        return true;
    } //end of function main

//####################################################################################

    function browse_main()
    {
        $db = DataAccess::getInstance();
        if (!$this->seller_configuration_data) {
            if (!$this->get_seller_configuration_data()) {
                return false;
            }
        }

        $sql = "select * from " . $this->categories_table . " where parent_id = 0 order by display_order,category_name";
        $result = $this->db->Execute($sql);
        //echo $sql."<br>\n";
        if (!$result) {
            //$this->body .=$sql." is the query<br>\n";
            $this->error_message = "<span class=\"error_message\">" . $this->messages[10364] . "</span>";
            return false;
        } elseif ($result->RecordCount() > 0) {
            $this->body .= "<table cellpadding=3 cellspacing=1 border=0 width=100% valign=top>\n\t";
            $this->body .= "<tr>\n\t<td valign=top class=\"back_to_normal_browsing\">
				<a href=" . $this->configuration_data['classifieds_url'] . "?a=5&b=" . $this->site_category . " class=\"back_to_normal_browsing\">";
            $this->body .= $this->messages[10001] . "</a></td>\n<tr>\n";

            switch ($this->seller_configuration_data->MODULE_NUMBER_OF_COLUMNS) {
                case 1:
                    $column_width = "100%";
                    break;
                case 2:
                    $column_width = "50%";
                    break;
                case 3:
                    $column_width = "33%";
                    break;
                case 4:
                    $column_width = "25%";
                    break;
                case 5:
                    $column_width = "20%";
                    break;
            } //end of switch
            $column_width = floor(100 / $this->seller_configuration_data->MODULE_NUMBER_OF_COLUMNS) . '%';
            while ($row = $result->FetchRow()) {
                $categories [$row['category_name']] ['category_id'] = $row['category_id'];
                $categories [$row['category_name']] ['category_image'] = $row['category_image'];
                $categories [$row['category_name']] ['category_count'] = $row['category_count'];
                $categories [$row['category_name']] ['auction_category_count'] = $row['auction_category_count'];
                $categories [$row['category_name']] ['category_name'] = $row['category_name'];
                $categories [$row['category_name']] ['category_description'] = $row['description'];
                if (isset($sub_categories[$row['category_name']] ['sub_categories'])) {
                    $categories[$row['category_name']] ['sub_categories'] = $sub_categories [$row['category_name']] ['sub_categories'];
                } else {
                    $categories [$row['category_name']] ['sub_categories'] = 'na';
                }
            }

            $category_count = 0;
            if ($this->seller_configuration_data->ALPHA_ACROSS_COLUMNS) {
                foreach ($categories as $show_category) {
                    if (! ($category_count % $this->seller_configuration_data->MODULE_NUMBER_OF_COLUMNS)) {
                            $this->body .= "<tr>\n\t";
                    }

                    //display the sub categories of this category
                    $this->body .= "<td valign=top width=" . $column_width . "><a href=" . $this->configuration_data['classifieds_file_name'] . "?a=25&b=" . $show_category['category_id'] . "&c=" . $browse_type . ">";
                    if ((strlen(trim($show_category['category_image'])) > 0) && ($this->seller_configuration_data->DISPLAY_CATEGORY_IMAGE)) {
                        $this->body .= "<img src=\"" . geoTemplate::getUrl('', $show_category['category_image']) . "\" hspace=2 vspace=0 border=0 align=left>";
                    }

                    $this->body .= "<span class=\"browsing_subcategory_name\">" . $show_category['category_name'] . "</span>";
                    if ($this->seller_configuration_data->DISPLAY_CATEGORY_COUNT) {
                        $this->body .= "<span class=\"browsing_subcategory_count\">(" . $this->get_seller_category_count($db, $show_category['category_id']) . ")</span>";
                    }
                    if ($this->seller_configuration_data->DISPLAY_CATEGORY_DESCRIPTION) {
                        $this->body .= "</a><br><span class=\"browsing_subcategory_description\">" . $show_category['category_description'] . "</span>";
                    }
                    $this->body .= "</td>";

                    $category_count++;
                    if (! ($category_count % $this->seller_configuration_data->MODULE_NUMBER_OF_COLUMNS)) {
                        $this->body .= "</tr>";
                    }
                }
            } else {
                // Data variables
                $total = count($categories);
                $num_cols = $this->seller_configuration_data->MODULE_NUMBER_OF_COLUMNS;
                $categories_x = array_values($categories); // convert associative to numeric array
                // computational variables
                $num_rows = ceil($total / ($num_cols)); // get the number of items per column (max)

                $cats = array();
                $current = 0;

                for ($x = 0; $x < $num_cols; $x++) { //go through each column
                    for ($y = 0; $y < $num_rows; $y++,$current++) {
                        if (($current) >= $total) {
                            break (2);//done building
                        }
                        $cats[$y][$x] = $categories_x[$current];
                    }
                }
                foreach ($cats as $col_num => $row) {
                    $this->body .= "<tr>\n\t";
                    foreach ($row as $row_num => $show_category) {
                        $this->body .= "<td valign=top width=" . $column_width . "><a href=" . $this->configuration_data['classifieds_file_name'] . "?a=25&b=" . $show_category['category_id'] . "&c=" . $browse_type . ">";
                        if ((strlen(trim($show_category['category_image'])) > 0) && ($this->seller_configuration_data->DISPLAY_CATEGORY_IMAGE)) {
                            $this->body .= "<img src=\"" . geoTemplate::getUrl('', $show_category['category_image']) . "\" hspace=2 vspace=0 border=0 align=left>";
                        }
                        $this->body .= "<span class=\"browsing_subcategory_name\">" . $show_category['category_name'] . "</span>";
                        if ($this->seller_configuration_data->DISPLAY_CATEGORY_COUNT) {
                            $this->body .= "<span class=\"browsing_subcategory_count\">(" . $this->get_seller_category_count($db, $show_category['category_id']) . ")</span>";
                        }
                        if ($this->seller_configuration_data->DISPLAY_CATEGORY_DESCRIPTION) {
                            $this->body .= "</a><br><span class=\"browsing_subcategory_description\">" . $show_category['category_description'] . "</span>";
                        }
                        $this->body .= "</td>";
                    }
                    $this->body .= "</tr>\n\t";
                }
            }
            $this->body .= "</table>\n";
            return true;
        } else {
            $this->body .= "<table cellpadding=5 cellspacing=1 border=0 valign=top>\n\t";
            $this->body .= "<tr class=\"no_categories_yet\">\n\t<td valign=top>" . $this->messages[10371] . "</td>\n</tr>\n";
            $this->body .= "</table>\n";
            return true;
        }
    } //end of function main

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    function get_seller_category_count($category_id = 0)
    {
        $db = DataAccess::getInstance();
        $category_id = (int)$category_id;

        if (!$category_id) {
            return false;
        }

        $classTable = geoTables::classifieds_table;
        $listCatTable = geoTables::listing_categories;

        $cat_subquery = "SELECT * FROM $listCatTable WHERE $listCatTable.`listing`=$classTable.`id`
			AND $listCatTable.`category`={$category_id}";

        $sql = "SELECT COUNT(DISTINCT(`seller`)) as seller_count FROM $classTable WHERE
			EXISTS ($cat_subquery) AND `live` = 1 ";
        $count_result = $this->db->Execute($sql);
        //echo $sql."<BR>\n";
        if (!$count_result) {
            return false;
        } elseif ($count_result->RecordCount() == 1) {
            $show = $count_result->FetchNextObject();
            return $show->SELLER_COUNT;
        } else {
            return false;
        }
    } //end of function get_seller_category_count

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    function get_seller_configuration_data()
    {
        $db = DataAccess::getInstance();
        $sql = "select * from " . $this->pages_table . " where page_id = 113";
        //echo $sql." at top<br>\n";
        $page_result = $this->db->Execute($sql);
        if (!$page_result) {
            $this->error_message = "<span class=\"error_message\">" . $this->messages[10364] . "</span>";
            return false;
        } elseif ($page_result->RecordCount() == 1) {
            $this->seller_configuration_data = $page_result->FetchNextObject();
        } else {
            return false;
        }

        return true;
    }
}
