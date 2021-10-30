<?php

//admin_category_questions_class.php



class Admin_category_questions extends Admin_site
{

    var $debug_questions = 0;
    var $questions;
    var $titles;
    var $title_count = 0;

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    function __construct()
    {
        //constructor
        parent::__construct();

        $this->messages["5500"] = "wrong count in return - either category does not exist or too many returns";
        $this->messages["5501"] = "internal db error";
        $this->messages["5502"] = "The subcategories of ";
        $this->messages["5503"] = "There are no subcategories in this category";
        $this->messages["5504"] = "An error ocurred while processing";
        $this->messages["5505"] = "There are no questions attached to this category.";
        $this->messages["5506"] = "Questions Attached to: ";
        $this->messages["5507"] = "Not enough information to complete your request";
        $this->messages["5508"] = "The main category is the parent category and has no questions attached to it.";
        $this->messages["5509"] = "Add New Question";
        $this->messages["5510"] = "There are no question types to choose from";
        $this->messages["5511"] = "A question already exists by that name.<br>click the back button and change the name.";
    } //end of function Admin_category_questions

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    function check_if_category($db, $category = 0)
    {
        if ($category) {
            //check to see if this number is even a category
            $sql = "select * from " . $this->classified_categories_table . " where category_id = " . $category;
            $result = $db->Execute($sql);
            if (!$result) {
                //echo $sql." is the query<br>\n";
                $this->error_message = $this->messages[5501];
                return false;
            } elseif ($result->RecordCount() == 1) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    } //end of function check_if_category

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    function admin_question_error()
    {
        $this->body .= "<table cellpadding=5 cellspacing=1 border=0>\n";
        $this->body .= "<tr>\n\t<td>" . $this->messages[5504] . "</td>\n</tr>\n";
        if ($this->error_message) {
            $this->body .= "<tr>\n\t<td>" . $this->error_message . "</td>\n</tr>\n";
        }
        $this->body .= "</table>\n";
    } //function admin_question_error

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    function sell_question_form($db, $question_id = 0, $category = 0)
    {
        //setup tooltip data
        $this->body .= "<script>";
        $this->body .= "Text[1] = [\"Name\", \"The value entered into this blank will appear as the question next to the type of question answer method chosen (ie. blank box, dropdown,...).  It could be a question or a field label, whatever you choose.\"]\n
			Text[2] = [\"Explanation\", \"If you feel that your question needs an explanation you can enter a value into this box. If you enter an explanation a question mark will appear next to the question in the sell process.  When the question mark is clicked this explanation will appear in a popup box further explaining how the question helps or should be answered.\"]\n
			Text[3] = [\"Question Type\", \"The value entered here determines the method by which the seller will answer this question. Give them a blank input field, check box, blank text area, url field, or one of the Pre-Valued Dropdowns you have already set up.\"]\n
			Text[4] = [\"Display \\\"Other\\\" Box\", \"You can opt to give the seller an additional entry field they can use if one of the choices you give in the dropdown box does not fit the product or service they are selling. The other box will only appear if \\\"Pre-Valued Dropdown\\\" has been chosen in the \\\"question type\\\" field above. If \\\"just blank input box\\\" is selected in the \\\"question type\\\" field above this setting will have no effect.\"]\n
			Text[5] = [\"Display Order\", \"Choose the order in the existing category questions that this question appears in the category question list.\"]\n";
        $this->body .= "</script>";

        if ($question_id) {
            $sql = "select * from " . $this->sell_questions_table . " where question_id = " . $question_id;
            if ($this->debug_questions) {
                echo $sql . "<br>\n";
            }
            $result = $db->Execute($sql);
            if (!$result) {
                //echo $sql." is the query<br>\n";
                $this->error_message = $this->messages[5501];
                return false;
            } elseif ($result->RecordCount() == 1) {
                $show_question = $result->FetchRow();
            } else {
                $this->error_message = $this->messages[5500];
                return false;
            }

            $category_name = $this->get_category_name($db, $show_question["category_id"]);
            $some_category = $show_question["category_id"];
            $this->body .= "<div class='page-title1'>Category: <span class='color-primary-two'>{$category_name}</span> <span class='color-primary-six' style='font-size:0.8em;'>({$category})</span></div>";
            if (!$this->admin_demo()) {
                $this->body .= "<form action=index.php?page=categories_questions_add&c=" . $show_question["question_id"] . "&d=" . $show_question["category_id"] . " method=post  class='form-horizontal form-label-left'>";
            } else {
                $this->body .= "<div class='form-horizontal'>";
            }
            $this->body .= "<fieldset id='EditCatQuesDetails'>
				<legend>Edit Category Question</legend><div class='x_content'>";
            //$this->title = " ({$category_name} - {$show_question["category_id"]})";

            $sql = "select * from " . $this->pages_languages_table;
            if ($this->debug_questions) {
                echo $sql . "<br>\n";
            }
            $language_result = $db->Execute($sql);
            if (!$language_result) {
                //echo $sql." is the query<br>\n";
                $this->error_message = $this->messages[5501];
                return false;
            } elseif ($language_result->RecordCount() > 0) {
                while ($language_id = $language_result->FetchRow()) {
                    //$sql = "select * from ".$this->classified_sell_questions_languages_table." where question_id = ".$question_id." and language_id = ".$language_id["language_id"];
                    $lang =

                    $sql = "select * from geodesic_classifieds_sell_questions_languages where question_id = " . $question_id . " and language_id = " . $language_id["language_id"];

                    $result = $db->Execute($sql);
                    $new = false;
                    if ($result && $result->RecordCount() == 0 && $language_id['language_id'] != 1) {
                        $new = true;
                        $result = $db->Execute("select * from geodesic_classifieds_sell_questions_languages where question_id = " . $question_id . " and language_id = 1");
                    }

                    if (!$result) {
                        //echo $sql." is the query<br>\n";
                        $this->error_message = $this->messages[5501];
                        return false;
                    } elseif ($result->RecordCount() == 1) {
                        //display this language name and explanation
                        $show_language_question = $result->FetchRow();

                        //display this language name and explanation
                        if ($new) {
                            $this->body .= "<input type='hidden' name='b[question_new][" . $language_id["language_id"] . "]' value='1' />";
                        }

                        $this->body .= "<div class='header-color-primary-one'>Question in <span style='font-weight:bold; text-transform: uppercase;'>" . $language_id["language"] . "</span> Language</div>";

                        $this->body .= "
						<div class='form-group'>
						<label class='control-label col-md-4 col-sm-4 col-xs-12'>Name: " . $this->show_tooltip(1, 1) . "</label>
						  <div class='col-md-6 col-sm-6 col-xs-12'>
						  <input type=text name=b[question_name][" . $language_id["language_id"] . "] class='form-control col-md-7 col-xs-12' value=\"" . geoString::specialChars($show_language_question["name"]) . "\">
						  </div>
						</div>
						";

                        $this->body .= "
						<div class='form-group'>
						<label class='control-label col-md-4 col-sm-4 col-xs-12'>Explanation: " . $this->show_tooltip(2, 1) . "</label>
						  <div class='col-md-6 col-sm-6 col-xs-12'>
						  <textarea name=b[question_explanation][" . $language_id["language_id"] . "] cols=40 rows=10 class='form-control col-md-7 col-xs-12'>" . geoString::specialChars($show_language_question["explanation"]) . "</textarea>
						  </div>
						</div>
						";

                        $this->body .= "
						<div class='form-group'>
						<label class='control-label col-md-4 col-sm-4 col-xs-12'>Question Type: " . $this->show_tooltip(3, 1) . "</label>
						  <div class='col-md-6 col-sm-6 col-xs-12'>
						<select name=b[question_choices][" . $language_id["language_id"] . "]
								onchange='if(jQuery.isNumeric(jQuery(this).val())){jQuery(\"#numSearch{$language_id["language_id"]}\").show();}else{jQuery(\"#numSearch{$language_id["language_id"]}\").hide();}' class='form-control col-md-7 col-xs-12' >";
                        $this->body .= "<option value=none ";
                        if ($show_language_question["choices"] == "none") {
                            $this->body .= "selected";
                        }
                        $this->body .= "> Blank Input Field</option>";

                        $this->body .= "<option value='number' ";
                        if ($show_language_question["choices"] == 'number') {
                            $this->body .= "selected='selected'";
                        }
                        $this->body .= "> Numeric Input Field</option>";

                        $this->body .= "<option value='date' ";
                        if ($show_language_question["choices"] == 'date') {
                            $this->body .= "selected='selected'";
                        }
                        $this->body .= "> Date Field</option>";

                        $this->body .= "<option value=check ";
                        if ($show_language_question["choices"] == "check") {
                            $this->body .= "selected";
                        }
                        $this->body .= "> Check Box</option>";
                        $this->body .= "<option value=textarea ";
                        if ($show_language_question["choices"] == "textarea") {
                            $this->body .= "selected";
                        }
                        $this->body .= "> Blank Textarea Box</option>";
                        $this->body .= "<option value=url ";
                        if ($show_language_question["choices"] == "url") {
                            $this->body .= "selected";
                        }
                        $this->body .= "> Url Field</option>";
                        $sql = "select * from " . $this->sell_choices_types_table . " ORDER BY type_name";
                        $types_result = $db->Execute($sql);
                        if ($this->debug_questions) {
                            echo $sql . " is the query<br>";
                        }
                        if (!$types_result) {
                            //echo $sql." is the query<br>\n";
                            $this->error_message = $this->messages[5501];
                            $this->site_error($db->ErrorMsg());
                            return false;
                        } elseif ($types_result->RecordCount() > 0) {
                            while ($show_type = $types_result->FetchRow()) {
                                //show questions as drop down box
                                $this->body .= "<option value=" . $show_type["type_id"];
                                if ($show_type["type_id"] == $show_language_question["choices"]) {
                                    $this->body .= " selected";
                                }
                                $this->body .= ">Pre-Valued Dropdown: " . $show_type["type_name"] . "\n\t";
                            } //end of while
                        }
                        $searchNumTT = geoHTML::showTooltip('Search as numbers', "With this box checked, the Advanced Search page will use high/low range inputs for this field, instead of listing individual checkboxes for every option");
                        $this->body .= "</select>

						<span id='numSearch{$language_id["language_id"]}' " . (is_numeric($show_language_question["choices"]) ? "" : "style='display:none;'") . ">
							<input type='checkbox' style='vertical-align: middle;' value='1' name='b[search_as_numbers][{$language_id["language_id"]}]' " . ($show_language_question['search_as_numbers'] == 1 ? "checked='checked'" : "") . "/> Search as numbers {$searchNumTT}
						</span>

						  </div>
						</div>
						";
                    }
                }
            } else {
                return false;
            }
        } elseif ($category) {
            //echo $category;
            $category_name = $this->get_category_name($db, $category);
            $some_category = $category;
            //this is a new attached to this category
            $this->body .= "<div class='page-title1'>Category: <span class='color-primary-two'>{$category_name}</span> <span class='color-primary-six' style='font-size:0.8em;'>({$category})</span></div>";
            if (!$this->admin_demo()) {
                $this->body .= "<form action=index.php?page=categories_questions_add&c=" . $category . " method=post class='form-horizontal form-label-left'>";
            } else {
                $this->body .= "<div class='form-horizontal'>";
            }
            $this->body .= "<fieldset id='CatQuesDetails'>
				<legend>New Category Question</legend><div class='x_content'>";
            //$this->title = " ({$category_name} - {$category})";
            $this->description = "Add a category question to this category with the form below.  Fill in the blanks and the question will be added to the
				" . $category_name . " category";

            $sql = "select * from " . $this->pages_languages_table;
            if ($this->debug_questions) {
                echo $sql . "<br>\n";
            }
            $language_result = $db->Execute($sql);
            if (!$language_result) {
                //echo $sql." is the query<br>\n";
                $this->error_message = $this->messages[5501];
                return false;
            } elseif ($language_result->RecordCount() > 0) {
                while ($language_id = $language_result->FetchRow()) {
                    //display this language name and explanation
                    $this->body .= "<div class='header-color-primary-one'>Question in <span style='font-weight:bold; text-transform: uppercase;'>" . $language_id["language"] . "</span> Language</div>";

                    $this->body .= "
					<div class='form-group'>
					<label class='control-label col-md-4 col-sm-4 col-xs-12'>Name: " . $this->show_tooltip(1, 1) . "</label>
					  <div class='col-md-6 col-sm-6 col-xs-12'>
					  <input type=text name=b[question_name][" . $language_id["language_id"] . "] class='form-control col-md-7 col-xs-12' value=\"\">
					  </div>
					</div>
					";

                    $this->body .= "
					<div class='form-group'>
					<label class='control-label col-md-4 col-sm-4 col-xs-12'>Explanation: " . $this->show_tooltip(2, 1) . "</label>
					  <div class='col-md-6 col-sm-6 col-xs-12'>
					  <textarea name=b[question_explanation][" . $language_id["language_id"] . "] cols=40 rows=10 class='form-control col-md-7 col-xs-12'></textarea>
					  </div>
					</div>
					";

                    $this->body .= "
					<div class='form-group'>
					<label class='control-label col-md-4 col-sm-4 col-xs-12'>Question Type: " . $this->show_tooltip(3, 1) . "</label>
					  <div class='col-md-6 col-sm-6 col-xs-12'>
					  <select name=b[question_choices][" . $language_id["language_id"] . "]
									onchange='if(jQuery.isNumeric(jQuery(this).val())){jQuery(\"#numSearch{$language_id["language_id"]}\").show();}else{jQuery(\"#numSearch{$language_id["language_id"]}\").hide();}' class='form-control col-md-7 col-xs-12' >";
                    $this->body .= "<option value=none ";
                    if ($show_question["choices"] == "none") {
                        $this->body .= "selected";
                    }
                    $this->body .= "> Blank Input Field</option>\n\t";

                    $this->body .= "<option value='number' ";
                    if ($show_question["choices"] == 'number') {
                        $this->body .= "selected='selected'";
                    }
                    $this->body .= "> Numeric Input Field</option>\n\t";

                    $this->body .= "<option value='date' ";
                    if ($show_language_question["choices"] == 'date') {
                        $this->body .= "selected='selected'";
                    }
                    $this->body .= "> Date Field</option>\n\t";

                    $this->body .= "<option value=check ";
                    if ($show_question["choices"] == "check") {
                        $this->body .= "selected";
                    }
                    $this->body .= "> Check Box</option>\n\t";
                    $this->body .= "<option value=textarea ";
                    if ($show_question["choices"] == "textarea") {
                        $this->body .= "selected";
                    }
                    $this->body .= "> Blank Textarea Box</option>\n\t";
                    $this->body .= "<option value=url ";
                    if ($show_question["choices"] == "url") {
                        $this->body .= "selected";
                    }
                    $this->body .= "> Url Field</option>\n\t";
                    $sql = "select * from " . $this->sell_choices_types_table . " ORDER BY type_name";
                    $types_result = $db->Execute($sql);
                    //echo $sql." is the query<br>\n";
                    if (!$types_result) {
                        //echo $sql." is the query<br>\n";
                        $this->error_message = $this->messages[5501];
                        $this->site_error($db->ErrorMsg());
                        return false;
                    } elseif ($types_result->RecordCount() > 0) {
                        while ($show_type = $types_result->FetchRow()) {
                            //show questions as drop down box
                            $this->body .= "<option value=" . $show_type["type_id"];
                            if ($show_type["type_id"] == $show_question["choices"]) {
                                $this->body .= " selected";
                            }
                            $this->body .= ">Pre-Valued Dropdown: " . $show_type["type_name"] . "\n\t";
                        } //end of while
                    }
                    $searchNumTT = geoHTML::showTooltip('Search as numbers', "With this box checked, the Advanced Search page will use high/low range inputs for this field, instead of listing individual checkboxes for every option");
                    $this->body .= "</select>

					<span id='numSearch{$language_id["language_id"]}' " . (is_numeric($show_language_question["choices"]) ? "" : "style='display:none;'") . ">
					<input type='checkbox' style='vertical-align: middle;' value='1' name='b[search_as_numbers][{$language_id["language_id"]}]' " . ($show_language_question['search_as_numbers'] == 1 ? "checked='checked'" : "") . "/> Search as numbers {$searchNumTT}
					</span>

					  </div>
					</div>
					";
                }
            } else {
                return false;
            }
        } else {
            $this->error_message = $this->messages["5507"];
            return false;
        }

        //display the current quesions attached this category

        //Get the current language id's from the languages table

        $this->body .= "
		<div class='header-color-primary-two'>Settings for this question in ALL Languages</div>
        <div class='form-group'>
        <label class='control-label col-md-4 col-sm-4 col-xs-12'>Display \"Other\" Box: " . $this->show_tooltip(4, 1) . "</label>
          <div class='col-md-6 col-sm-6 col-xs-12'>
			<input type=radio name=b[other_input_box] value=1 ";
        if ($show_question["other_input"] == 1) {
            $this->body .= " checked ";
        }
            $this->body .= "> Yes<br><input type=radio name=b[other_input_box] value=0 ";
        if ($show_question["other_input"] == 0) {
            $this->body .= " checked ";
        }
            $this->body .= "> No
          </div>
        </div>
		";

        $this->body .= "
        <div class='form-group'>
        <label class='control-label col-md-4 col-sm-4 col-xs-12'>Display Order: " . $this->show_tooltip(5, 1) . "</label>
          <div class='col-md-6 col-sm-6 col-xs-12'>
          <select name=b[question_display_order] class='form-control col-md-7 col-xs-12'>";
        for ($i = 0; $i < 200; $i++) {
            $this->body .= "<option ";
            if ($show_question["display_order"] == $i) {
                $this->body .= "selected";
            }
            $this->body .= ">" . $i . "\n\t";
        } // end of for
            $this->body .= "</select>
          </div>
        </div>

        ";

        if (!$this->admin_demo()) {
            $this->body .= "<div class='center'><input type=submit name='auto_save' value=\"Save\"></div>";
        }
        $this->body .= "</div></fieldset>";
        if (!$this->admin_demo()) {
            $this->body .= "</form>";
        } else {
            $this->body .= "</div>";
        }

        $this->body .= "
		<div style='padding: 5px;'><a href=index.php?page=categories_questions&b=" . $some_category . " class='back_to'>
		<i class='fa fa-backward'> </i> Back to " . $category_name . " Category Questions</a></div>
		";

        return true;
    } //end of function sell_question_form

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    function update_sell_question($db, $question_id = 0, $info = 0)
    {
        $question_id = (int)$question_id;
        if (($question_id) && ($info)) {
            $info["question_reference"] = str_replace(" ", "_", $info["question_reference"]);
            if ($info["question_choices"] == "check") {
                $info["other_input_box"] = 0;
            }
            $sql = "UPDATE " . $this->sell_questions_table . " set
				name = ?,
				explanation = ?,
				choices = ?,
				other_input = ?,
				display_order = ?
				where question_id = $question_id";

            $query_data = array (
                $info["question_name"][1],
                $info["question_explanation"][1],
                $info["question_choices"][1],
                $info["other_input_box"],
                $info["question_display_order"]
            );
            if ($this->debug_questions) {
                echo $sql . "<br>\n";
            }
            $result = $db->Execute($sql, $query_data);
            if (!$result) {
                //echo $sql." is the query<br>\n";
                $this->error_message = $this->messages[5501];
                return false;
            }
            //update name and explanation for all langugage
            $sql = "select * from " . $this->pages_languages_table;
            if ($this->debug_questions) {
                echo $sql . "<br>\n";
            }
            $language_result = $db->Execute($sql);
            if (!$language_result) {
                //echo $sql." is the query<br>\n";
                $this->error_message = $this->messages[5501];
                return false;
            } elseif ($language_result->RecordCount() > 0) {
                while ($language_id = $language_result->FetchRow()) {
                    $lang_id = (int)$language_id["language_id"];
                    if ($info['question_new'][$lang_id]) {
                        $sql = "INSERT INTO geodesic_classifieds_sell_questions_languages SET
							name = ?,
							explanation = ?,
							choices = ?,
							search_as_numbers = ?,
							question_id = $question_id,
							language_id = $lang_id";
                    } else {
                        $sql = "UPDATE geodesic_classifieds_sell_questions_languages set
							name = ?,
							explanation = ?,
							choices = ?,
							search_as_numbers = ?
							WHERE question_id = $question_id and language_id = $lang_id";
                    }
                    if ($this->debug_questions) {
                        echo $sql . "<br>\n";
                    }
                    $query_data = array (
                        $info["question_name"][$lang_id],
                        $info["question_explanation"][$lang_id],
                        $info["question_choices"][$lang_id],
                        ($info["search_as_numbers"][$lang_id] == 1 ? 1 : 0)
                    );

                    $result = $db->Execute($sql, $query_data);
                    if (!$result) {
                        //echo $sql." is the query<br>\n";
                        $this->error_message = $this->messages[5501];
                        return false;
                    }
                }
            } else {
                return false;
            }
            return true;
        } else {
            $this->error_message = $this->messages[5507];
            return false;
        }
    } //end of function update_sell_question

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    function insert_sell_question($db, $info = 0, $category_id = 0)
    {
        if (($info) && ($category_id)) {
            //$sql = "select name from ".$this->sell_questions_table." where name = \"".$info["question_name"]."\" and category_id = ".$category_id;
            //echo $sql." is the query<br>\n";
            //$name_result = $db->Execute($sql);
            //if (!$name_result)
            //{
            //  //echo $sql." is the query<br>\n";
            //  $this->error_message = $this->messages[5501];
            //  return false;
            //}
            //elseif ($name_result->RecordCount() == 0)
            //{
                //echo $name_result->RecordCount()." is the recordcount<br>\n";
            if ($info["question_choices"] == "check") {
                $info["other_input_box"] = 0;
            }

                $input = array( $category_id, $info["question_name"][1],
                                $info["question_explanation"][1], $info["question_choices"][1],
                                $info["other_input_box"], $info["question_display_order"] );

                $sql = "insert into " . $this->sell_questions_table . "
					(category_id, name, explanation, choices, other_input, display_order)
					values (?,?,?,?,?,?)";

                $result = $db->Execute($sql, $input);
                if ($this->debug_questions) {
                    echo $sql . "<br>\n";
                }
                if (!$result) {
                    //echo $sql." is the query<br>\n";
                    $this->error_message = $this->messages[5501];
                    echo $this->db->ErrorMsg() . " is the sql error<br>\n";
                    return false;
                }
                //get id created from insert
                $insert_id = $this->db->Insert_ID();

                $sql = "select * from " . $this->pages_languages_table;
                if ($this->debug_questions) {
                    echo $sql . "<br>\n";
                }
                $language_result = $db->Execute($sql);
                if (!$language_result) {
                    //echo $sql." is the query<br>\n";
                    $this->error_message = $this->messages[5501];
                    return false;
                } elseif ($language_result->RecordCount() > 0) {
                    while ($language_id = $language_result->FetchRow()) {
                        $input = array( $insert_id, $language_id["language_id"], $info["question_name"][$language_id["language_id"]],
                                        $info["question_explanation"][$language_id["language_id"]], $info["question_choices"][$language_id["language_id"]],
                                        ($info["search_as_numbers"][$lang_id] == 1 ? 1 : 0));
                        $sql = "insert into geodesic_classifieds_sell_questions_languages
							(question_id, language_id, name, explanation, choices, search_as_numbers)
							values (?,?,?,?,?,?)";
                        $insert_result = $db->Execute($sql, $input);
                        if ($this->debug_questions) {
                            echo $sql . "<br>\n";
                        }
                        if (!$insert_result) {
                            //echo $sql." is the query<br>\n";
                            if ($this->debug_questions) {
                                echo $db->ErrorMsg() . " is the error<br>\n";
                                echo $insert_id . " is \$insert_id<br>\n";
                                echo $language_id["language_id"] . " is \$language_id[language_id]<br>\n";
                                echo $info["question_name"][$language_id["language_id"]] . " is \$info[question_name][" . $language_id["language_id"] . "]<br>\n";
                                echo $info["question_explanation"][$language_id["language_id"]] . " is \$info[question_explanation][" . $language_id["language_id"] . "]<br>\n";
                            }
                            $this->error_message = $this->messages[5501];
                            return false;
                        }
                    }
                } else {
                    return false;
                }
                return true;
            //}
            //else
            //{
            //  $this->error_message = $this->messages["5511"];
            //  return false;
            //}
        } else {
            $this->error_message = $this->messages[5507];
            return false;
        }
    } //end of function insert_sell_question

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    function show_current_questions($db, $category)
    {
        if (PHP5_DIR) {
            $menu_loader = geoAdmin::getInstance();
        } else {
            $menu_loader =& geoAdmin::getInstance();
        }

        $catInfo = geoCategory::getInstance()->getInfo($category);

        $category_name = $catInfo['name'];
        $sql = "select parent_id from " . $this->classified_categories_table . " where category_id = " . $category;
        $result = $db->Execute($sql);
        if (!$result) {
            trigger_error("ERROR SQL: " . $db->ErrorMsg());
            $menu_loader->userError("Internal error. Please contact <a href='http://www.geodesicsolutions.com/support/index.htm'>support</a>.");
            $this->body .= $menu_loader->getUserMessages();

            return false;
        } elseif ($result->RecordCount() == 1) {
            $show_name = $result->FetchRow();
        } else {
            //echo $sql." is the query<br>\n";
            $this->error_message = $this->messages[5501];
            return false;
        }

        $sql = "select * from " . $this->sell_questions_table . " where category_id = " . $category . " order by display_order";
        $result = $db->Execute($sql);
        if (!$result) {
            //echo $sql." is the query<br>\n";
            $this->error_message = $this->messages[5501];
            return false;
        }

        $this->questions = array();
        $this->questions[0] = "&nbsp;";
        $this->body .= $menu_loader->getUserMessages();
        //display the current quesions attached this category

        if (!$categoryId) {
            $typesAllowedLookup[0] = ' checked="checked"';
        }

        $this->body .= '<div class="breadcrumbBorder">';
            $this->body .= '<ul id="breadcrumb">';
            //$this->body .= '<li class="current">Currently Viewing</li>';
            $this->body .= '<li><a href="index.php?mc=categories&page=category_config">Main</a></li>';

        if ($category) {
            $category_tree = geoCategory::getTree($category);
            for ($i = 0; $i < count($category_tree); $i++) {
                if ($i == count($category_tree) - 1) {
                    $this->body .= '<li class="current2">';
                    $this->body .= $category_tree[$i]['category_name'];
                } else {
                    $this->body .= '<li>';
                    $this->body .= "<a href=index.php?mc=categories&page=category_config&parent=" . $category_tree[$i]["category_id"] . ">" . $category_tree[$i]['category_name'] . '</a>';
                }
                $this->body .= '</li>';
            }
        }
            $this->body .= '</ul></div>';

        $this->body .= "<div class='page-title1'>Category: <span class='color-primary-two'>{$category_name}</span> <span class='color-primary-six' style='font-size:0.8em;'>({$category})</span></div>";

        $this->body .= "<fieldset id='CatQues'>
				<legend>Category Questions</legend><div class='table-responsive'><table cellpadding=5 border=0 cellpadding=2 cellspacing=1 class='table table-hover table-striped table-bordered'>\n";
        //$this->title = "Categories Setup > Edit Category Questions";
        $this->description = "This page allows you to designate specific questions to be asked of the seller when they place an listing in this category. These same fields will
		also display on the \"advanced search page\" as searchable criteria when a site visitor selects this particular category to search for an item.";
        $this->title = ' (' . $category_name . ')';
        $this->body .= "<thead><tr class=col_hdr_top>\n\t<td class=col_hdr_left>Name\n\t</td>\n\t";
        $this->body .= "<td class=col_hdr_left>Explanation\n\t</td>\n\t";
        $this->body .= "<td class=col_hdr_left>Question Type\n\t</td>\n\t";
        $this->body .= "<td class=col_hdr>\"Other\" Box?</td>\n";
        $this->body .= "<td class=col_hdr>Display Order</td>\n\t";
        $this->body .= "<td class=col_hdr>Edit</td>\n\t";
        $this->body .= "<td class=col_hdr>Delete</td>\n</tr></thead>\n";

        if ($result->RecordCount() > 0) {
            $this->row_count = 0;
            while ($show_current_questions = $result->FetchRow()) {
                //add to list of questions for title dropdown
                $this->questions[$show_current_questions['question_id']] = $show_current_questions['name'];

                //show the current questions by row
                $this->body .= "<tr class=" . $this->get_row_color() . ">\n\t<td valign=top class=medium_font>\n\t" . $show_current_questions["name"] . "(" . $show_current_questions["question_id"] . ")\n\t</td>\n\t";
                $this->body .= "<td valign=top class=medium_font>\n\t" . $show_current_questions["explanation"] . "\n\t&nbsp;</td>\n\t";
                $this->body .= "<td valign=top class=medium_font>\n\t";
                if ($show_current_questions["choices"] == "none") {
                    $this->body .= "blank input field";
                } elseif ($show_current_questions['choices'] == 'number') {
                    $this->body .= "numeric input field";
                } elseif ($show_current_questions['choices'] == 'date') {
                    $this->body .= "date field";
                } elseif ($show_current_questions["choices"] == "check") {
                    $this->body .= "checkbox";
                } elseif ($show_current_questions["choices"] == "textarea") {
                    $this->body .= "blank textarea box";
                } elseif ($show_current_questions["choices"] == "url") {
                    $this->body .= "url";
                } else {
                    $sql = "select type_name from " . $this->sell_choices_types_table . " where type_id = " . $show_current_questions["choices"];
                    $choice_result = $db->Execute($sql);
                    if (!$choice_result) {
                        //echo $sql." is the query<br>\n";
                        $this->error_message = $this->messages[5501];
                        return false;
                    } elseif ($choice_result->RecordCount() == 1) {
                        $show_choice_name = $choice_result->FetchRow();
                    } else {
                        return false;
                    }

                    $this->body .= $show_choice_name["type_name"];
                }

                $this->body .= "&nbsp;\n\t</td>\n\t";
                $this->body .= "<td valign=top align=center class=medium_font>\n\t";
                if ($show_current_questions["other_input"] == 1) {
                    $this->body .= "yes";
                } else {
                    $this->body .= "no";
                }
                $this->body .= "&nbsp;\n\t</td>\n\t";
                $this->body .= "<td valign=top class=medium_font align=center>\n\t" . $show_current_questions["display_order"] . "&nbsp;\n\t</td>\n\t";
                $this->body .= "<td valign=top align=center>" . geoHTML::addButton('Edit', "index.php?page=categories_questions_edit&b=" . $show_current_questions["question_id"]) . "</td>\n\t";
                $this->body .= "<td valign=top align=center>" . geoHTML::addButton('Delete', "index.php?page=categories_questions_delete&b=" . $show_current_questions["question_id"] . "&c=" . $category . "&amp;auto_save=1", false, '', 'lightUpLink mini_cancel') . "</td>\n\t";
                $this->body .= "</tr>";
                $this->row_count++;
            }// end of while
        } //end of if
        else {
            //say there are no questions in this category
            $this->body .= "<tr>\n\t<td colspan=8>\n\t<div class='page_note_error'>" . $this->messages["5505"] . "</div>\n\t</td>\n</tr>\n";
        }
        $this->body .= "<tr>\n\t<td colspan=8 align=center>\n\t<a href=index.php?page=categories_questions_add&b=" . $category . " class='mini_button'>" . $this->messages["5509"] . "</a>\n\t</td>\n</tr>\n";
        $this->body .= "</table></fieldset>";

        //get inherited questions
        $parent_id = $show_name["parent_id"];
        $this->body .= "<fieldset id='CatQuesInherited'>
				<legend>Questions Inherited from Parent Categories Above: <span class='color-primary-two'>" . $category_name . "</span> <span class='color-primary-six' style='font-size: 0.8; !important'>(" . $category . ")</span></legend>
				<div class='table-responsive'><table cellpadding=5 border=0 cellpadding=2 cellspacing=1 class='table table-hover table-striped table-bordered'>\n";
        $this->body .= "<thead><tr class=col_hdr_top>\n\t<td class=col_hdr_left><b>Name</b></font>\n\t</td>\n\t";
        $this->body .= "<td class=col_hdr_left><b>Explanation</b></font>\n\t</td>\n\t";
        $this->body .= "<td class=col_hdr><b>Question Type</b></font>\n\t</td>\n\t";
        $this->body .= "<td class=col_hdr align=center><b>\"Other\" Box?</b></font></td>\n";
        $this->body .= "<td class=col_hdr align=center><b>Display Order</b></font>\n\t</td>\n\t";
        $this->body .= "<td class=col_hdr align=center><b>Edit</b></font></td>\n\t";
        $this->body .= "<td class=col_hdr align=center><b>Delete</b></font></td>\n</tr></thead>\n";
        if ($parent_id != 0) {
            while ($parent_id != 0) {
                $parent_category_name = $this->get_category_name($db, $parent_id);
                $sql = "select parent_id from " . $this->classified_categories_table . " where category_id = " . $parent_id;
                $result = $db->Execute($sql);
                if (!$result) {
                    //echo $sql." is the query<br>\n";
                    $this->error_message = $this->messages[5501];
                    return false;
                } elseif ($result->RecordCount() == 1) {
                    $show_category = $result->FetchRow();
                    $this->body .= "<tr>\n\t<td colspan=7 class=sec_hdr2 align=center>\n\t<strong>Questions Inherited from: " . $parent_category_name . " ( " . $parent_id . " )</strong>\n\t</td>\n</tr>\n";
                    $sql = "select * from " . $this->sell_questions_table . " where category_id = " . $parent_id;
                    $result = $db->Execute($sql);
                    if (!$result) {
                        //echo $sql." is the query<br>\n";
                        $this->error_message = $this->messages[5501];
                        return false;
                    } elseif ($result->RecordCount() > 0) {
                        $this->row_count = 0;
                        $css_tag = $this->get_row_color(2);
                        while ($show_current_questions = $result->FetchRow()) {
                            //add to list of questions for title dropdown
                            $this->questions[$show_current_questions['question_id']] = $show_current_questions['name'];

                            //show the current questions by row
                            $css_tag = $this->get_row_color(2);
                            $this->body .= "<tr class=" . $css_tag . ">\n\t<td class=medium_font>" . $show_current_questions["name"] . "</font>\n\t</td>\n\t";
                            $this->body .= "<td class=medium_font>" . $show_current_questions["explanation"] . "\n\t&nbsp;</font></td>\n\t";
                            $this->body .= "<td class=medium_font>" . $show_current_questions["choices"] . "&nbsp;</font>\n\t</td>\n\t";
                            $this->body .= "<td class=medium_font align=center>";
                            if ($show_current_questions["other_input"] == 1) {
                                $this->body .= "yes";
                            } else {
                                $this->body .= "no";
                            }
                            $this->body .= "&nbsp;\n\t</td>\n\t";
                            $this->body .= "<td class=medium_font align=center>" . $show_current_questions["display_order"] . "&nbsp;</font>\n\t</td>\n\t";
                            $this->body .= "<td align=center>" . geoHTML::addButton('Edit', "index.php?page=categories_questions_edit&b=" . $show_current_questions["question_id"] . "&terminal_category=" . $category) . "</td>\n\t";
                            $this->body .= "<td align=center>" . geoHTML::addButton('Delete', "index.php?page=categories_questions_delete&b=" . $show_current_questions["question_id"] . "&c=" . $category . "&amp;auto_save=1", false, '', 'lightUpLink mini_cancel') . "</td>\n\t";
                            $this->body .= "</tr>";
                            $this->row_count++;
                        }// end of while
                    } else {
                        $this->body .= "<tr>\n\t<td colspan=8 class=medium_font>" . $this->messages["5505"] . "</td>\n</tr>\n";
                    }
                    $parent_id = $show_category["parent_id"];
                } else {
                    //$this->body .= $sql." is the query<br>\n";
                    $this->error_message = $this->messages[5501];
                    return false;
                }
            }//end of while
        } else {
            //this is a subcategory of main and can inherit no questions from main
            $this->body .= "<tr>\n\t<td colspan=8><div class='page_note_error'>" . $this->messages["5508"] . "</div>\n\t</td>\n</tr>\n";
        }

        $this->body .= "</table></div></fieldset>";

        $this->body .= "<fieldset id='AutoListTitle'>
				<legend>Automatic Listing Title Generation</legend><div class='x_content'>";
        $this->title_form($category);
        $this->body .= "</div></fieldset>\n";

        $this->body .= "
		<div style='padding: 5px;'><a href=index.php?mc=categories&page=dropdowns class='back_to'>
		<i class='fa fa-backward'> </i> View Current Pre-Valued Dropdowns</a></div>
		<div style='padding: 5px;'><a href=index.php?page=category_config&parent=" . $catInfo['parent_id'] . " class='back_to'>
		<i class='fa fa-backward'> </i> Back to Categories</a></div>
		";

        return true;
    } //end of function show_current_questions

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
    function title_form($category_id)
    {
        //tooltip script
        $this->body .= "<SCRIPT language=\"JavaScript1.2\">";
        $this->body .= "Text[1] = [\"Automatic Listing Title Generation\", \"With this option enabled, users will not be able to input a custom title for their listings in this category, but the title will instead be dynamically generated using their answers to the category-specific questions you choose here.\"]\n";
        $this->body .= "</script>";


        $sql = "select use_auto_title, auto_title from " . $this->db->geoTables->categories_table . " where category_id = " . $category_id;
        $data = $this->db->Execute($sql);
        if (!$data) {
            $checked = false;
        } else {
            $data = $data->FetchRow();
            $checked = ($data['use_auto_title'] == 1) ? true : false;
            if (strlen($data['auto_title']) > 0 && $data['auto_title']) {
                $this->titles = explode("|", $data['auto_title']);
            } else {
                $this->titles = 0;
            }
        }

        if (!$this->admin_demo()) {
            $this->body .= "<form action=\"index.php?mc=categories&page=categories_questions&b={$category_id}\" method=\"post\" class='form-horizontal form-label-left'>";
        } else {
            $this->body .= "<div class='form-horizontal'>";
        }

        $this->body .= "
		<div class='form-group'>
		<label class='control-label col-md-4 col-sm-4 col-xs-12'> </label>
		  <div class='col-md-6 col-sm-6 col-xs-12'>";
        $this->body .= "<input type=\"checkbox\" " . (($checked) ? "checked=\"checked\" " : "");
        $this->body .= "onClick=\"if(this.checked) document.getElementById('title_stuff').style.display=''; else document.getElementById('title_stuff').style.display='none';\" ";
        $this->body .= "name=\"use_auto_title\" value=\"1\" /> Automatically generate listing titles from category-specific questions and optional site wide fields. " . $this->show_tooltip(1, 1) . "
		  </div>
		</div>
		";

        $this->body .= "
		<div id=\"title_stuff\" class=\"medium_font\" style=\"padding-bottom: 30px; text-align: center; width: 100%;" . (($checked) ? "" : " display: none;") . "\">";
            //add sitewide optional fields to array of stuff that can be chosen
            $this->load_sitewides_into_auto_title();
            $this->title_count = 0;
        for ($i = 0; $i < 5; $i++) {
            $this->body .= $this->title_questions_dropdown("title_questions[" . $i . "]") . " \n\t";
        }
            $this->body .= "<input type=\"hidden\" value=\"" . $category . "\" name=\"cat\" />";
        $this->body .= "
		</div>
		";

        if (!$this->admin_demo()) {
            $this->body .= "<div class='center'><input type=\"submit\" name=\"auto_save\" value=\"Save\" /></div></form>";
        } else {
            $this->body .= "</div>";
        }
    }

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
    function load_sitewides_into_auto_title()
    {
        if (geoPC::is_ent()) {
            for ($i = 1; $i <= 20; $i++) {
                $key = "oswf" . $i;
                $val = $this->db->get_site_setting('optional_field_' . $i . '_name');
                $this->questions[$key] = $val;
            }
        }
    }
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    function title_questions_dropdown($name)
    {
        if ($name) {
            $html = "<select name=\"" . $name . "\" class=\"form-control col-md-7 col-xs-12\" style=\"width:auto; margin:3px;\">\n\t";
            foreach ($this->questions as $id => $question) {
                $title = (isset($this->titles[$this->title_count])) ? $this->titles[$this->title_count] : "0";
                $selected = ($title == $id) ? " selected=\"selected\"" : "";
                $html .= "<option value=\"" . $id . "\"" . $selected . ">" . $question . "</option>\n\t";
            }
            $this->title_count++;
            $html .= "</select>\n";
            return $html;
        }
    }
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
    function set_auto_title($category, $useMe, $choices)
    {
        if ($category) {
            if ($useMe != 1) {
                $sql = "update " . $this->db->geoTables->categories_table . " set
					use_auto_title = 0 where category_id = " . $category;
            } else {
                if (!$choices) {
                    return false;
                }

                $title = "";
                foreach ($choices as $q) {
                    $title .= $q . "|";
                }
                $title = substr($title, 0, -1); // remove ending bar
                $sql = "update " . $this->db->geoTables->categories_table . " set
					use_auto_title = 1, auto_title = '" . $title . "' where category_id = " . $category;
            }
            $result = $this->db->Execute($sql);
            if (!$result) {
                return false;
            } else {
                return true;
            }
        } else {
            return false;
        }
    }

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    function show_all_dropdowns($db)
    {
        if (PHP5_DIR) {
            $menu_loader = geoAdmin::getInstance();
        } else {
            $menu_loader =& geoAdmin::getInstance();
        }

        $sql = "select * from " . $this->sell_choices_types_table . " order by type_name";
        $result = $db->Execute($sql);
        if (!$result) {
            trigger_error("ERROR SQL: " . $db->ErrorMsg());
            $menu_loader->userError("Internal error. Please contact <a href='http://www.geodesicsolutions.com/support/index.htm'>support</a>.");
            $this->body .= $menu_loader->getUserMessages();
            return false;
        }
        $this->body .= $menu_loader->userMessages();

        //$this->title = "Listing Setup > Pre-Valued Dropdowns";
        $this->description = "This is the current list of Pre-Valued Dropdown choices that can be used (assigned to) any customized category question,
		which is presented to the seller during the Listing process.
		First, create your dropdowns using the form below. These dropdowns will then appear as choices when you set up your \"category questions\".
		Do not confuse these Pre-Valued Dropdowns with Registration Pre-Valued Dropdowns, which are used only for Registration Questions.";
        $this->body .= "
			<table width=450 cellpadding=2 cellspacing=1 border=0 class=row_color1>";

        if ($result->RecordCount() > 0) {
            $this->body .= '<div></div><script type="text/javascript">
Text[1] = ["Current Pre-Valued Dropdowns","Dropdowns created here can be used in 3 places:<br /><br />- <strong>Optional Site Wide Fields</strong><br />- <strong>Category Specific Questions</strong><br />- <strong>Group Questions</strong> (Group questions are Enterprise Only)<br />"]

</script>';
            $this->body .= "
				<tr bgcolor=000066><td class=medium_font_light align=center colspan=3><b>Current Pre-Valued Dropdowns</b>" . $this->show_tooltip(1, 1) . "</td></tr>
				<tr class=row_color_black>
					<td class=medium_font_light><b>Dropdown Name</b></font></td>
					<td class=medium_font_light align=center>&nbsp;</font></td>
					<td class=medium_font_light align=center>&nbsp;</font></td>
				</tr>";
            $this->row_count = 1;
            while ($show = $result->FetchRow()) {
                $this->body .= "
				<tr class=" . $this->get_row_color() . ">
					<td class=medium_font>" . $show["type_name"] . "</td>
					<td align=center width=80>" . geoHTML::addButton('Edit', "index.php?page=pre_valued_edit&c=" . $show["type_id"]) . "</td>
					<td align=center width=80>" . geoHTML::addButton('Delete', "index.php?page=listing_pre_valued&d=" . $show["type_id"], false, '', 'mini_cancel') . "</td>
				</tr>";
                $this->row_count++;
            }
        } else {
            $this->body .= "<tr>\n\t<td align=center><div class='page_note_error'>There are currently no Pre-Valued Dropdowns.</div>\n\t</td>\n</tr>\n";
        }
        $this->body .= "<tr>\n\t<td align=center colspan=3><a href=index.php?page=pre_valued_new&e=1 class='mini_button'>Add New Pre-Valued Dropdown</a>\n\t</td>\n</tr>\n";
        $this->body .= "<tr>\n\t<td align=left colspan=3>\n\t
		<div style='padding: 5px;'><a href=index.php?page=category_config class='back_to'>
		<img src='admin_images/design/icon_back.gif' alt='' class='back_to'>Back to Categories Setup</a></div>
		</td>\n</tr>\n";
        $this->body .= "</table>\n";
        return true;
    } //end of function show_all_dropdowns

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    function new_dropdown_form()
    {
        if (!$this->admin_demo()) {
            $this->body .= "<form action=index.php?page=listing_pre_valued&e=1 method=post class='form-horizontal form-label-left'>\n";
        } else {
            $this->body .= "<div class='form-horizontal'>";
        }
        $this->body .= "<table cellpadding=2 cellspacing=0 border=0 class=row_color1>\n";
        //$this->title = "Listing Setup > Pre-Valued Dropdowns > New";
        $this->description = "Use the form to create a new dropdown that can be used as a choice when you create category
		questions on a category by category basis. The next step will then allow you to add values to the dropdown you have
		just created.";
        $this->body .= "<tr>\n\t
			<td align=right class=medium_font>dropdown label:</font></td>\n\t
			<td class=medium_font><input type=text name=b[dropdown_label] size=35></td>\n</tr>\n";
        if (!$this->admin_demo()) {
            $this->body .= "<tr align=center>\n\t<td colspan=2><input type=submit name=\"auto_save\" value=\"Save\">\n\t</td>\n</tr>\n";
        }
        $this->body .= "</table>\n";
        if (!$this->admin_demo()) {
            $this->body .= "</form>\n";
        } else {
            $this->body .= "</div>";
        }
        return true;
    } //end of function new_dropdown_form

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    function insert_new_dropdown($db, $information = 0)
    {
        if ($information) {
            if (strlen(trim($information["dropdown_label"])) > 0) {
                $sql = "insert into " . $this->sell_choices_types_table . "
					(type_name)
					values
					(\"" . $information["dropdown_label"] . "\")";
                $result = $db->Execute($sql);
                if (!$result) {
                    //echo $sql."<br>\n";
                    return false;
                }
                $id = $db->Insert_ID();
                return $id;
            } else {
                $sql = "insert into " . $this->sell_choices_types_table . "
					(type_name)
					values
					(\"" . $information["dropdown_label"] . "\")";
                $result = $db->Execute($sql);
                if (!$result) {
                    echo $sql . "<br>\n";
                    return false;
                }
                $id = $db->Insert_ID();
                return $id;
                //return false;
            }
        } else {
            return false;
        }
    } //end of function insert_new_dropdown

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    function edit_dropdown($db, $dropdown_id = 0)
    {
        if (PHP5_DIR) {
            $menu_loader = geoAdmin::getInstance();
        } else {
            $menu_loader =& geoAdmin::getInstance();
        }

        if ($dropdown_id) {
            $sql = "select * from " . $this->sell_choices_types_table . " where type_id = " . $dropdown_id;
            $result = $db->Execute($sql);
            if (!$result) {
                return false;
            } elseif ($result->RecordCount() == 1) {
                //this dropdown exists
                $show_dropdown = $result->FetchRow();
                $sql = "select * from " . $this->classified_sell_choices_table . " where type_id = " . $dropdown_id . " order by display_order,value";
                $result = $db->Execute($sql);
                if (!$result) {
                    return false;
                }
                //show the form to edit this dropdown
                if (!$this->admin_demo()) {
                    $this->body .= "<form action=index.php?page=pre_valued_edit&c=" . $dropdown_id . " method=post class='form-horizontal form-label-left'>\n";
                } else {
                    $this->body .= "<div class='form-horizontal'>";
                }
                $this->body .= "<table cellpadding=2 cellspacing=0 border=0 class=row_color1 width=\"100%\">\n";

                $this->body .= $menu_loader->getUserMessages();
                $this->body .= "<tr>\n\t<td align=center>\n\t<table width=450 cellpadding=2 cellspacing=1 border=0 class=row_color2>\n\t";
                $this->body .= "<tr bgcolor=000066><td class=medium_font_light align=center colspan=3><b>Dropdown Values</b></td></tr>\n\t";
                $this->body .= "<tr class=row_color_black>\n\t\t<td class=medium_font_light>\n\t<b> dropdown value</b></font>\n\t\t</td>\n\t\t";
                $this->body .= "<td class=medium_font_light align=center>\n\t<b>display order</b></font>\n\t\t</td>\n\t\t";
                $this->body .= "<td class=medium_font_light align=center>\n\t&nbsp;</font>\n\t\t</td>\n\t</tr>\n\t";
                if ($result->RecordCount() > 0) {
                    //this dropdown exists
                    //show the value in a list
                    $this->row_count = 0;
                    while ($show = $result->FetchRow()) {
                        $this->body .= "<tr class=" . $this->get_row_color() . ">\n\t\t<td class=medium_font>\n\t" . $show["value"] . "</font>\n\t\t</td>\n\t\t";
                        $this->body .= "<td class=medium_font align=center width=120>\n\t" . $show["display_order"] . "</font>\n\t\t</td>\n\t\t";
                        $this->body .= "<td align=center width=80>\n\t\t" . geoHTML::addButton('delete', "index.php?page=pre_valued_edit&c=23&g=" . $show["value_id"] . "&c=" . $dropdown_id . "&auto_save=1", false, '', 'lightUpLink mini_cancel') . "\n\t\t</td>\n\t\t";
                        $this->row_count++;
                    }
                }
                $this->body .= "<tr>\n\t<td class=medium_font>\n\t<input type=text name=b[value] size=25 maxsize=50></font>\n\t</td>\n\t
					<td class=medium_font align=center>\n\t<select name=b[display_order]>\n\t\t\t";
                for ($i = 1; $i < 151; $i++) {
                    $this->body .= "<option>" . $i . "</option>\n\t\t\t";
                }
                $this->body .= "</select></font>\n\t</td>\n\t
					<td class=medium_font align=center>\n\t";
                if (!$this->admin_demo()) {
                    $this->body .= "<input type=submit name=\"auto_save\" value=\"Save\">";
                }
                $this->body .= "\n\t</td>\n\t</tr>\n\t";
                $this->body .= "</table>";
                if (!$this->admin_demo()) {
                    $this->body .= '</form>';
                } else {
                    $this->body .= '</div>';
                }
                $this->body .= "
				<div style='padding: 5px;'><a href=index.php?page=listing_pre_valued class='back_to'>
				<img src='admin_images/design/icon_back.gif' alt='' class='back_to'>Back to Pre-Valued Dropdown Choices</a></div>";
                $this->body .= "
				<div style='padding: 5px;'><a href=index.php?page=category_config class='back_to'>
				<img src='admin_images/design/icon_back.gif' alt='' class='back_to'>Back to Categories Setup</a></div>";
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    } //end of function edit_dropdown

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    function add_dropdown_value($db, $dropdown_id = 0, $information = 0)
    {
        if ($this->debug_questions) {
            echo "<BR><BR>TOP OF ADD_DROPDOWN_VALUE<BR>";
            echo $dropdown_id . " is dropdown_id<br>\n";
            echo $information["value"] . " is new value to add<bR>\n";
        }
        if (($information) && ($dropdown_id)) {
            if (strlen(trim($information["value"])) > 0) {
                $sql = "insert into " . $this->classified_sell_choices_table . "
					(type_id,value,display_order)
					values
					(" . $dropdown_id . ",\"" . $information["value"] . "\"," . $information["display_order"] . ")";
                $result = $db->Execute($sql);
                if ($this->debug_questions) {
                    echo $sql . " is 1<bR>\n";
                }
                if (!$result) {
                    if ($this->debug_questions) {
                        echo $sql . "<bR>\n";
                    }
                    return false;
                }
                $id = $db->Insert_ID();
                return $id;
            } else {
                $sql = "insert into " . $this->classified_sell_choices_table . "
					(type_id,value,display_order)
					values
					(" . $dropdown_id . ",\"" . $information["value"] . "\"," . $information["display_order"] . ")";
                $result = $db->Execute($sql);
                if ($this->debug_questions) {
                    echo $sql . " is 2<bR>\n";
                }
                if (!$result) {
                    if ($this->debug_questions) {
                        echo $sql . "<bR>\n";
                    }
                    return false;
                }
                $id = $db->Insert_ID();
                return $id;
                //return false;
            }
        } else {
            return false;
        }
    } //end of function add_dropdown_value

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    function delete_dropdown_value($db, $value_id = 0)
    {
        if ($value_id) {
            $sql = "delete from " . $this->classified_sell_choices_table . " where value_id = " . $value_id;
            $result = $db->Execute($sql);
            if (!$result) {
                return false;
            }
            return true;
        } else {
            return false;
        }
    } //end of function delete_dropdown_value

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    function delete_dropdown_intermediate($db, $dropdown_id = 0)
    {
        if ($dropdown_id) {
            $sql = "select * from " . $this->sell_choices_types_table . " where type_id = " . $dropdown_id;
            $result = $db->Execute($sql);
            if (!$result) {
                return false;
            } elseif ($result->RecordCount() == 1) {
                if (!$this->admin_demo()) {
                    $this->body .= "<form action=index.php?page=listing_pre_valued&d=" . $dropdown_id . " method=post class='form-horizontal form-label-left'>\n";
                } else {
                    $this->body .= "<div class='form-horizontal'>";
                }
                $this->body .= "<table cellpadding=2 cellspacing=0 border=0 class=row_color1 width=\"100%\">\n";

                $show_dropdown = $result->FetchRow();
                $sql = "select * from " . $this->sell_questions_table . " where choices = " . $dropdown_id;
                $result = $db->Execute($sql);
                if (!$result) {
                    return false;
                } elseif ($result->RecordCount() > 0) {
                    //there are sell questions attached to this
                    $attached = 1;

                    //show attached categories
                    $this->body .= "<tr>\n\t<td>\n\t";
                    $this->body .= "<table cellpadding=2 cellspacing=0 border=0 class=row_color1>\n";
                    $this->body .= "<tr class=row_color_black>\n\t<td class=medium_font_light>\n\tcategories attached to<br>
						this question dropdown</font>\n\t</td>\n</tr>\n";
                    $this->row_count = 1;
                    while ($show_categories = $result->FetchRow()) {
                        $current_category_name = $this->get_category_name($db, $show_categories["category_id"]);
                        $this->body .= "<tr class=" . $this->get_row_color() . ">\n\t<td class=medium_font>\n\t" . $current_category_name . "</font>\n\t</td>\n</tr>\n";
                        $this->row_count++;
                    }
                    $this->body .= "</td>\n\t</tr>\n";

                    $sql = "select * from " . $this->sell_choices_types_table . " where type_id = " . $dropdown_id;
                    $dropdown_result = $db->Execute($sql);
                    if (!$dropdown_result) {
                        return false;
                    } elseif ($dropdown_result->RecordCount() > 0) {
                        $this->body .= "<tr>\n\t<td class=medium_font>\n\tmove these category sell <br>questions to this dropdown ";
                        $this->body .= "<select name=z[new_dropdown]>\n\t\t";
                        $this->body .= "<option value=none>choose dropdown</option>\n\t\t";
                        while ($show_other = $dropdown_result->FetchRow()) {
                            $this->body .= "<option value=" . $show_other["type_id"] . ">" . $show_other["type_name"] . "</option>\n\t\t";
                        }
                        $this->body .= "</font>\n\t</td>\n</tr>\n";
                    }
                    if (!$this->admin_demo()) {
                        /*$this->body .= "<tr>\n\t<td class=medium_font align=center>\n\t<input type=hidden name=z[type_of_submit]
                            value=\"change and delete\"></font>\n\t</td>\n</tr>\n";
                        */$this->body .= "<tr>\n\t<td class=medium_font align=center>\n\t<input type=submit name=z[type_of_submit]
							value=\"change and delete\"></font>\n\t</td>\n</tr>\n";
                        $this->body .= "<tr>\n\t<td class=medium_font align=center>\n\t<input type=submit name=z[type_of_submit]
							value=\"delete\"></font>\n\t</td>\n</tr>\n";
                    }
                    $this->body .= "</table>\n";
                } else {
                    $this->body .= "<tr>\n\t<td class=medium_font align=center>\n\t";
                    if (!$this->admin_demo()) {
                        $this->body .= "<input type=hidden name=z[type_of_submit] value=\"delete all references\">";
                        $this->body .= "<input type=submit name=\"auto_save\" value=\"delete all references\"></font>\n\t";
                    }
                    $this->body .= "</td>\n</tr>\n";
                }
                $this->body .= "</table>\n";
                $this->body .= ($this->admin_demo()) ? "</div>" : "</form>";

                //show the delete from db (and everywhere else
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    } //end of function delete_dropdown_intermediate

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    function delete_dropdown($db, $dropdown_id = 0, $information = 0)
    {
        //echo "hello from delete dropdown<br>\n";
        if (($dropdown_id) && ($information)) {
            //if this breaks, add <|| $information["type_of_submit"] == "delete"> without anglebrackets to the next IF statement
            //today is 1/24/07 -- delete this comment if it's been a while and this is still working ok
            if ($information["type_of_submit"] == "delete all references") {
                $sql = "delete from " . $this->sell_questions_table . " where choices = " . $dropdown_id;
                //echo $sql."<br>\n";
                $result = $db->Execute($sql);
                if (!$result) {
                    return false;
                }

                $sql = "delete from " . $this->classified_sell_choices_table . " where type_id = " . $dropdown_id;
                //echo $sql."<br>\n";
                $result = $db->Execute($sql);
                if (!$result) {
                    return false;
                }

                $sql = "delete from " . $this->sell_choices_types_table . " where type_id = " . $dropdown_id;
                //echo $sql."<br>\n";
                $result = $db->Execute($sql);
                if (!$result) {
                    return false;
                }
                return true;
            } elseif ($information["type_of_submit"] == "change and delete") {
                if ($information["new_dropdown"] != "none") {
                    $sql = "update " . $this->sell_questions_table . " set
						choices = " . $information["new_dropdown"] . "
						where choices = " . $dropdown_id;
                    //echo $sql."<br>\n";
                    $result = $db->Execute($sql);
                    if (!$result) {
                        return false;
                    }
                }
                $sql = "delete from " . $this->classified_sell_choices_table . "," . $this->sell_choices_types_table . " where type_id = " . $dropdown_id;
                //echo $sql."<br>\n";
                $result = $db->Execute($sql);
                if (!$result) {
                    return false;
                }
                return true;
            } else {
                return false;
            }
        } else {
            //echo "not enough info<br>\n";
            return false;
        }
    } //end of function delete_dropdown

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    function delete_sell_question($db, $question_id = 0)
    {
        if ($question_id) {
            $sql = "delete from " . $this->sell_questions_table . " where question_id = " . $question_id;
            $result = $db->Execute($sql);
            if ($this->debug_questions) {
                echo $sql . "<br>\n";
            }
            if (!$result) {
                if ($this->debug_questions) {
                    echo $sql . "<br>\n";
                    echo $db->ErrorMsg() . " is the error<br>\n";
                }
                return false;
            }
            $sql = "delete from geodesic_classifieds_sell_questions_languages where question_id = " . $question_id;
            $result = $db->Execute($sql);
            if ($this->debug_questions) {
                echo $sql . "<br>\n";
            }
            if (!$result) {
                if ($this->debug_questions) {
                    echo $sql . "<br>\n";
                    echo $db->ErrorMsg() . " is the error<br>\n";
                }
                return false;
            }
            return true;
        } else {
            return false;
        }
    } //end of function delete_dropdown_value

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
    function display_categories_questions_add()
    {
        if (is_numeric($_REQUEST["b"])) {
            if (!$this->sell_question_form($this->db, 0, $_REQUEST["b"])) {
                return false;
            }
        } elseif (is_numeric($_REQUEST["d"])) {
            if (!$this->show_current_questions($this->db, $_REQUEST["d"])) {
                return false;
            }
        } else {
            if (!$this->show_current_questions($this->db, $_REQUEST["c"])) {
                return false;
            }
        }

        $this->display_page();
    }
    function update_categories_questions_add()
    {
        if ($_REQUEST["d"]) {
            return $this->update_sell_question($this->db, $_REQUEST["c"], $_REQUEST["b"]);
        } else {
            return $this->insert_sell_question($this->db, $_REQUEST["b"], $_REQUEST["c"]);
        }
    }
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
    function display_categories_questions_delete()
    {
        $this->show_current_questions($this->db, $_REQUEST["c"]);
        $this->display_page();
    }
    function update_categories_questions_delete()
    {
        $menu_loader = geoAdmin::getInstance();

        if (($_REQUEST["b"]) && ($_REQUEST["c"])) {
            if ($this->delete_sell_question($this->db, $_REQUEST["b"])) {
                return true;
            }
        }
        return false;
    }
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
    function display_categories_questions()
    {
        if (!$this->show_current_questions($this->db, $_REQUEST["b"])) {
            return false;
        }
        $this->display_page();
    }

    function update_categories_questions()
    {
        return $this->set_auto_title($_REQUEST["b"], $_REQUEST["use_auto_title"], $_REQUEST["title_questions"]);
    }
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
    function display_categories_questions_edit()
    {
        if (!$this->sell_question_form($this->db, $_REQUEST["b"])) {
            return false;
        }
        $this->display_page();
    }
    function update_categories_questions_edit()
    {
    }
} //end of class Admin_category_questions
