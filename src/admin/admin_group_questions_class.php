<?php

class Admin_category_questions extends Admin_site
{

    var $category_name = "";
    var $returned_value = "";
    var $debug_questions = 0;

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    public function __construct()
    {
        //constructor
        parent::__construct();

        $this->messages["5500"] = "wrong count in return - either category does not exist or too many returns";
        $this->messages["5501"] = "internal db error";
        $this->messages["5502"] = "The subcategories of ";
        $this->messages["5503"] = "There are no subcategories in this category";
        $this->messages["5504"] = "An error ocurred while processing";
        $this->messages["5505"] = "there are no questions attached to this category";
        $this->messages["5506"] = "questions attached to the ";
        $this->messages["5507"] = "Not enough information to complete your request";
        $this->messages["5508"] = "The main category is the parent category and has no questions attached to it";
        $this->messages["5509"] = "Add New Question";
        $this->messages["5510"] = "There are no question types to choose from";
        $this->messages["5511"] = "A question already exists by that name.<br>click the back button and change the name.";
    } //end of function Admin_category_questions

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    function check_if_group($db, $group_id = 0)
    {
        if ($group_id) {
            //check to see if this number is even a category
            $this->sql_query = "select * from " . $this->classified_groups_table . " where group_id = " . $group_id;
            $result = $db->Execute($this->sql_query);
            if (!$result) {
                //echo $this->sql_query." is the query<br>\n";
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

    function group_question_form($db, $question_id = 0, $group_id = 0)
    {
        $this->body .= "<script>";
        $this->body .= "Text[1] = [\"Name\", \"The value entered into this blank will appear as the question next to the type of question answer method chosen (ie. blank box, dropdown,...). It could be a question or a field label, whatever you choose.\"]\n
			Text[2] = [\"Explanation\", \"If you feel that your question needs an explanation you can enter a value into this box. If you enter an explanation a question mark will appear next to the question in the sell process. When the question mark is clicked this explanation will appear in a popup box further explaining how the question helps or should be answered.\"]\n
			Text[3] = [\"Choices\", \"The value entered here determines the method this question can be answered. You can leave just a blank box or if you have added pre-valued dropdown boxes choose from one of them.\"]\n
			Text[4] = [\"Display \\\"Other\\\" Box\", \"Here, you can opt to give the classified seller an \\\"other\\\" box if one of the choices you give in the dropdown box does not fit the product or service they are selling. The \\\"other\\\" box will only appear if a dropdown box has been chosen in the \\\"choices\\\" field above. If \\\"just blank input box\\\" is selected in the \\\"choices\\\" field above this value will have no effect.\"]\n
			Text[5] = [\"Display Order\", \"Choose the order in the existing group questions that this question appears in the group question list.\"]\n";
        $this->body .= "</script>";

        if ($question_id) {
            //get the question data from the database
            $input = array( $question_id );

            $this->sql_query = "select * from " . $this->sell_questions_table . "
				where question_id = ? ";

            $question_result = $db->Execute($this->sql_query, $input);
            if ($this->debug_questions) {
                echo $this->sql_query . "<br>\n";
            }
            if (!$question_result) {
                //echo $this->sql_query." is the query<br>\n";
                $this->error_message = $this->messages[5501];
                echo $this->db->ErrorMsg() . " is the sql error<br>\n";
                return false;
            } elseif ($question_result->RecordCount() == 1) {
                $question_data = $question_result->FetchRow();
                $group_name = $this->get_group_name($db, $question_data["group_id"]);
                $some_group = $question_data["group_id"];
                if (!$this->admin_demo()) {
                    $this->body .= "<form action=index.php?mc=users&page=users_group_questions_new&c=" . $question_id . "&d=" . $question_data["group_id"] . " class='form-horizontal form-label-left' method=post>";
                } else {
                    $this->body .= "<div class='form-horizontal'>";
                }
                $this->body .= "<div class='page-title1'>User Group: <span class='group-color'>" . $group_name . "</span></div>
				<fieldset id='GroupQues'>
					<legend>User Group Question</legend>
					<div class='x_content'>";

                    $this->body .= "
						<div class='form-group'>
						<label class='control-label col-md-4 col-sm-4 col-xs-12'>Display \"Other\" Box: " . $this->show_tooltip(4, 1) . "</label>
						  <div class='col-md-6 col-sm-6 col-xs-12'>
							<input type=radio name=b[other_input_box] value=1 ";
                if ($question_data["other_input"] == 1) {
                    $this->body .= " checked ";
                }
                                    $this->body .= "> Yes<br><input type=radio name=b[other_input_box] value=0 ";
                if ($question_data["other_input"] == 0) {
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
                for ($i = 0; $i < 60; $i++) {
                    $this->body .= "<option ";
                    if ($question_data["display_order"] == $i) {
                        $this->body .= "selected";
                    }
                    $this->body .= ">" . $i . "\n\t";
                } // end of for
                            $this->body .= "</select>
						  </div>
						</div>
						";

                $this->sql_query = "select * from " . $this->pages_languages_table;
                $language_result = $db->Execute($this->sql_query);
                if (!$language_result) {
                    $this->error_message = $this->messages[5501];
                    return false;
                } elseif ($language_result->RecordCount() > 0) {
                    while ($language_id = $language_result->FetchRow()) {
                        $this->sql_query = "select * from geodesic_classifieds_sell_questions_languages where question_id = " . $question_id . " and language_id = " . $language_id["language_id"];
                        $result = $db->Execute($this->sql_query);
                        if (!$result) {
                            $this->error_message = $this->messages[5501];
                            return false;
                        } elseif ($result->RecordCount() == 1) {
                            $show_language_question = $result->FetchRow();

                            //display the current quesions attached this category
                            $this->body .= "<div class='header-color-primary-mute' style='margin:5px 0;'>Question in <span style='text-transform:uppercase;'>" . $language_id["language"] . "</span> Language</div>";

                            $this->body .= "<div class='form-group'>";
                            $this->body .= "<label class='control-label col-md-4 col-sm-4 col-xs-12'>Name: " . $this->show_tooltip(1, 1) . "</label>";
                            $this->body .= "<div class='col-md-6 col-sm-6 col-xs-12'>
									<input type=text name=b[question_name][" . $language_id["language_id"] . "] class='form-control col-md-7 col-xs-12' value=\"" . $show_language_question["name"] . "\">";
                            $this->body .= "</div>";
                            $this->body .= "</div>";

                            $this->body .= "<div class='form-group'>";
                            $this->body .= "<label class='control-label col-md-4 col-sm-4 col-xs-12'>Explanation: " . $this->show_tooltip(2, 1) . "</label>";
                            $this->body .= "<div class='col-md-6 col-sm-6 col-xs-12'>
									<textarea name=b[question_explanation][" . $language_id["language_id"] . "] class='form-control'>" . geoString::specialChars($show_language_question["explanation"]) . "</textarea>";
                            $this->body .= "</div>";
                            $this->body .= "</div>";

                            $this->body .= "<div class='form-group'>";
                            $this->body .= "<label class='control-label col-md-4 col-sm-4 col-xs-12'>Choices: " . $this->show_tooltip(3, 1) . "</label>";
                            $this->body .= "<div class='col-md-6 col-sm-6 col-xs-12'>";
                                $this->body .= "<select name=b[question_choices][" . $language_id["language_id"] . "] class='form-control col-md-7 col-xs-12'>";
                            $this->body .= "<option value=none ";
                            if ($show_language_question["choices"] == "none") {
                                $this->body .= "selected";
                            }
                            $this->body .= ">Blank Input Field</option>";
                            $this->body .= "<option value=check ";
                            if ($show_language_question["choices"] == "check") {
                                $this->body .= "selected";
                            }
                            $this->body .= ">Check Box</option>";
                            $this->body .= "<option value=textarea ";
                            if ($show_language_question["choices"] == "textarea") {
                                $this->body .= "selected";
                            }
                            $this->body .= ">Blank Textarea Box</option>";
                            $this->body .= "<option value=url ";
                            if ($show_language_question["choices"] == "url") {
                                $this->body .= "selected";
                            }
                            $this->body .= ">Url Field</option>";
                            $this->sql_query = "select * from " . $this->sell_choices_types_table;
                            $types_result = $db->Execute($this->sql_query);
                            //echo $this->sql_query." is the query<br>";
                            if (!$types_result) {
                                //echo $this->sql_query." is the query<br>";
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
                            $this->body .= "</select>";
                            $this->body .= "</div>";
                            $this->body .= "</div>";
                        }
                    } // end of while
                }
            } else {
                return false;
            }
        } elseif ($group_id) {
            $group_name = $this->get_group_name($db, $group_id);
            $some_group = $group_id;
            //this is a new attached to this category
            if (!$this->admin_demo()) {
                $this->body .= "<form action=index.php?mc=users&page=users_group_questions_new&c=" . $group_id . " class='form-horizontal form-label-left' method=post>";
            } else {
                $this->body .= "<div class='form-horizontal'>";
            }
            $this->body .= "
			<div class='page-title1'>User Group: <span class='group-color'>" . $group_name . "</span></div>
			<fieldset id='GroupQues'>
				<legend>User Group Question</legend><div class='x_content'>";

                $this->body .= "
					<div class='form-group'>
					<label class='control-label col-md-4 col-sm-4 col-xs-12'>Display \"Other\" Box: " . $this->show_tooltip(4, 1) . "</label>
					  <div class='col-md-6 col-sm-6 col-xs-12'>
						<input type=radio name=b[other_input_box] value=1 ";
            if ($question_data["other_input"] == 1) {
                $this->body .= " checked ";
            }
                                $this->body .= "> Yes<br><input type=radio name=b[other_input_box] value=0 ";
            if ($question_data["other_input"] == 0) {
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
            for ($i = 0; $i < 60; $i++) {
                $this->body .= "<option ";
                if ($question_data["display_order"] == $i) {
                    $this->body .= "selected";
                }
                $this->body .= ">" . $i . "\n\t";
            }
                        $this->body .= "</select>
					  </div>
					</div>
					";

            $this->sql_query = "select * from " . $this->pages_languages_table;
            $language_result = $db->Execute($this->sql_query);
            if (!$language_result) {
                $this->error_message = $this->messages[5501];
                return false;
            } elseif ($language_result->RecordCount() > 0) {
                while ($language_id = $language_result->FetchRow()) {
                            //display the current quesions attached this category

                            $this->body .= "<div class='col_hdr' style='margin:5px 0;'>Question in " . $language_id["language"] . " language</div>";

                            $this->body .= "<div class='form-group'>";
                            $this->body .= "<label class='control-label col-md-4 col-sm-4 col-xs-12'>Name: " . $this->show_tooltip(1, 1) . "</label>";
                            $this->body .= "<div class='col-md-6 col-sm-6 col-xs-12'>
									<input type=text name=b[question_name][" . $language_id["language_id"] . "] class='form-control col-md-7 col-xs-12' value=\"" . $show_question["name"] . "\">";
                            $this->body .= "</div>";
                            $this->body .= "</div>";

                            $this->body .= "<div class='form-group'>";
                            $this->body .= "<label class='control-label col-md-4 col-sm-4 col-xs-12'>Explanation: " . $this->show_tooltip(2, 1) . "</label>";
                            $this->body .= "<div class='col-md-6 col-sm-6 col-xs-12'>
									<textarea name=b[question_explanation][" . $language_id["language_id"] . "] class='form-control'>" . geoString::specialChars($show_question["explanation"]) . "</textarea>";
                            $this->body .= "</div>";
                            $this->body .= "</div>";

                            $this->body .= "<div class='form-group'>";
                            $this->body .= "<label class='control-label col-md-4 col-sm-4 col-xs-12'>Choices: " . $this->show_tooltip(3, 1) . "</label>";
                            $this->body .= "<div class='col-md-6 col-sm-6 col-xs-12'>";

                                $this->body .= "<select name=b[question_choices][" . $language_id["language_id"] . "] class='form-control col-md-7 col-xs-12'>";
                                $this->body .= "<option value=none ";
                    if ($show_question["choices"] == "none") {
                        $this->body .= "selected";
                    }
                                $this->body .= ">Blank Input Field</option>";
                                $this->body .= "<option value=check ";
                    if ($show_question["choices"] == "check") {
                        $this->body .= "selected";
                    }
                                $this->body .= ">Check Box</option>";
                                $this->body .= "<option value=textarea ";
                    if ($show_question["choices"] == "textarea") {
                        $this->body .= "selected";
                    }
                                $this->body .= ">Blank Textarea Box</option>";
                                $this->body .= "<option value=url ";
                    if ($show_question["choices"] == "url") {
                        $this->body .= "selected";
                    }
                                $this->body .= ">Url Field</option>";
                                $this->sql_query = "select * from " . $this->sell_choices_types_table;
                                $types_result = $db->Execute($this->sql_query);
                                //echo $this->sql_query." is the query<br>";
                    if (!$types_result) {
                        //echo $this->sql_query." is the query<br>";
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
                                $this->body .= "</select>";
                            $this->body .= "</div>";
                            $this->body .= "</div>";
                } // end of while
            }
        } else {
            $this->error_message = $this->messages["5507"];
            return false;
        }


        if (!$this->admin_demo()) {
            $this->body .= "<div class='center'><input type=submit name='auto_save' value=\"Save\"> </div>";
        }

        $this->body .= "</div></fieldset>";

        $this->body .= ($this->admin_demo()) ? '</div>' : '</form>';

        $this->body .= "<div><a href=index.php?mc=users&page=users_group_questions&d=" . $some_group . " class='back_to'>
		<i class='fa fa-backward'> </i> Back to " . $group_name . " Questions</a></div>";

        return true;
    } //end of function sell_question_form

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    function update_sell_question($db, $question_id = 0, $info = 0)
    {
        if ($this->debug_questions) {
            echo "top of UPDATE_SELL_QUESTION<BR>\n";
            echo $question_id . " is question_id<bR>\n";
            echo $info . " is info<bR>\n";
        }
        if (($question_id) && ($info)) {
            $info["question_reference"] = str_replace(" ", "_", $info["question_reference"]);

            $this->sql_query = "UPDATE " . $this->sell_questions_table . " set
				name = \"" . $info["question_name"][1] . "\",
				explanation = \"" . $info["question_explanation"][1] . "\",
				choices = \"" . $info["question_choices"][1] . "\",
				other_input = " . $info["other_input_box"] . ",
				display_order = " . $info["question_display_order"] . "
				where question_id = " . $question_id;

            if ($this->debug_questions) {
                echo $this->sql_query . " is the query<br>\n";
            }
            $result = $db->Execute($this->sql_query);
            if (!$result) {
                if ($this->debug_questions) {
                    echo $this->sql_query . " is the query<br>\n";
                }
                $this->error_message = $this->messages[5501];
                return false;
            }
                    //update name and explanation for all langugage
            $this->sql_query = "select * from " . $this->pages_languages_table;
            $language_result = $db->Execute($this->sql_query);
            if ($this->debug_questions) {
                echo $this->sql_query . " is the query<br>\n";
            }
            if (!$language_result) {
                if ($this->debug_questions) {
                    echo $this->sql_query . " is the query<br>\n";
                }
                $this->error_message = $this->messages[5501];
                return false;
            } elseif ($language_result->RecordCount() > 0) {
                while ($language_id = $language_result->FetchRow()) {
                    $this->sql_query = "update geodesic_classifieds_sell_questions_languages set
						name = \"" . $info["question_name"][$language_id["language_id"]] . "\",
						explanation = \"" . $info["question_explanation"][$language_id["language_id"]] . "\",
						choices = \"" . $info["question_choices"][$language_id["language_id"]] . "\"
						where question_id = " . $question_id . " and language_id = " . $language_id["language_id"];
                    if ($this->debug_questions) {
                        echo $this->sql_query . "<br>\n";
                    }
                    $result = $db->Execute($this->sql_query);
                    if (!$result) {
                        if ($this->debug_questions) {
                            echo $this->sql_query . " is the query<br>\n";
                            echo $db->ErrorMsg() . " is the error message<br>\n";
                        }
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

    function insert_sell_question($db, $info = 0, $group_id = 0)
    {
        if (($info) && ($group_id)) {
            if ($info["question_choices"] == "check") {
                $info["other_input_box"] = 0;
            }

            $input = array( $group_id, $info["question_name"][1],
                            $info["question_explanation"][1], $info["question_choices"][1],
                            $info["other_input_box"], $info["question_display_order"] );

            $this->sql_query = "insert into " . $this->sell_questions_table . "
				(group_id, name, explanation, choices, other_input, display_order)
				values (?,?,?,?,?,?)";

            $result = $db->Execute($this->sql_query, $input);
            if ($this->debug_questions) {
                echo $this->sql_query . "<br>\n";
            }
            if (!$result) {
                //echo $this->sql_query." is the query<br>\n";
                $this->error_message = $this->messages[5501];
                echo $this->db->ErrorMsg() . " is the sql error<br>\n";
                return false;
            }

            //get id created from insert
            $insert_id = $this->db->Insert_ID();

            $this->sql_query = "select * from " . $this->pages_languages_table;
            if ($this->debug_questions) {
                echo $this->sql_query . "<br>\n";
            }
            $language_result = $db->Execute($this->sql_query);
            if (!$language_result) {
                //echo $this->sql_query." is the query<br>\n";
                $this->error_message = $this->messages[5501];
                return false;
            } elseif ($language_result->RecordCount() > 0) {
                while ($language_id = $language_result->FetchRow()) {
                    $input = array( $insert_id, $language_id["language_id"], $info["question_name"][$language_id["language_id"]],
                                    $info["question_explanation"][$language_id["language_id"]], $info["question_choices"][$language_id["language_id"]]);
                    $this->sql_query = "insert into geodesic_classifieds_sell_questions_languages
						(question_id, language_id, name, explanation, choices)
						values (?,?,?,?,?)";
                    $insert_result = $db->Execute($this->sql_query, $input);
                    if ($this->debug_questions) {
                        echo $this->sql_query . "<br>\n";
                    }
                    if (!$insert_result) {
                        //echo $this->sql_query." is the query<br>\n";
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
        } else {
            $this->error_message = $this->messages[5507];
            return false;
        }
    } //end of function insert_sell_question

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%


    function show_current_questions($db, $group_id)
    {
        if ($this->debug_questions) {
            echo "top of SHOW_CURRENT_QUESTIONS<br>\n";
        }
        $group_name = $this->get_group_name($db, $group_id);
        if (!$group_name) {
            return false;
        }

        $this->sql_query = "select * from " . $this->sell_questions_table . " where group_id = " . $group_id . " order by display_order";
        $result = $db->Execute($this->sql_query);
        if ($this->debug_questions) {
            echo $this->sql_query . " is the query<br>\n";
        }
        if (!$result) {
            if ($this->debug_questions) {
                echo $this->sql_query . " is the query<br>\n";
            }
            $this->error_message = $this->messages[5501];
            return false;
        }

        //display the current quesions attached this category
        //$this->title = "Users / User Groups > Edit Details > User Group Questions";
        $this->description = "User Group specific questions are used to ask / offer each user within this User Group certain questions
		during the Listing Process. Each user within this particular User Group will be able to answer the questions you specify below,
		while users of other User Groups will not have access to these questions during their listing process";
        $this->body .= "
			<div class='page-title1'>User Group: <span class='group-color'>" . $group_name . "</span></div>
		<fieldset id='GroupQues'>
				<legend>User Group Questions</legend><div class='table-responsive'>

				<table border=0 cellpadding=1 cellspacing=1 width=\"100%\" class=\"table table-hover table-striped table-bordered\">
				<thead>
				<tr class='col_hdr_top'>
					<td class=col_hdr_left>Name</td>
					<td class=col_hdr_left>Explanation</td>
					<td class=col_hdr>Question Type</td>
					<td class=col_hdr align=center>\"Other\" Box?</td>
					<td class=col_hdr align=center>Display Order</td>
					<td class=col_hdr width=200 align=center> </td>
				</tr></thead><tbody>";


        if ($result->RecordCount() > 0) {
            $this->row_count = 0;
            while ($show_current_questions = $result->FetchRow()) {
                //show the current questions by row
                $this->body .= "
				<tr class=" . $this->get_row_color() . ">
					<td valign=top class=medium_font>" . $show_current_questions["name"] . "</td>
					<td valign=top class=medium_font>" . $show_current_questions["explanation"] . "&nbsp;</td>
					<td valign=top align=center class=medium_font>";
                if ($show_current_questions["choices"] == "none") {
                    $this->body .= "";
                } elseif ($show_current_questions["choices"] == "check") {
                    $this->body .= "checkbox";
                } elseif ($show_current_questions["choices"] == "textarea") {
                    $this->body .= "blank textarea box";
                } elseif ($show_current_questions["choices"] == "url") {
                    $this->body .= "url";
                } else {
                    $this->sql_query = "select type_name from " . $this->sell_choices_types_table . " where type_id = " . $show_current_questions["choices"];
                    $choice_result = $db->Execute($this->sql_query);
                    if (!$choice_result) {
                        if ($this->debug_questions) {
                            echo $this->sql_query . " is the query<br>\n";
                        }
                        $this->error_message = $this->messages[5501];
                        return false;
                    } elseif ($choice_result->RecordCount() == 1) {
                        $show_choice_name = $choice_result->FetchRow();
                    } else {
                        return false;
                    }
                    $this->body .= $show_choice_name["type_name"];
                }
                $this->body .= "&nbsp;</td>
					<td valign=top class=medium_font align=center>";
                if ($show_current_questions["other_input"] == 1) {
                    $this->body .= "yes";
                } else {
                    $this->body .= "no";
                }
                $this->body .= "&nbsp;</td>
					<td valign=top class=medium_font align=center>" . $show_current_questions["display_order"] . "&nbsp;</td>
					<td valign=top align=center>
						" . geoHTML::addButton('Edit', "index.php?mc=users&page=users_group_questions_edit&b=" . $show_current_questions["question_id"]) . "
						" . geoHTML::addButton('Delete', "index.php?mc=users&page=users_group_questions_delete&b=" . $show_current_questions["question_id"] . "&c=" . $group_id . "&auto_save=1", false, '', 'lightUpLink mini_cancel') . "
					</td>
				</tr>";
                $this->row_count++;
            }// end of while
        } //end of if
        else {
            //say there are no questions in this category
            $this->body .= "
				</tbody><tr><td colspan=6 align=center><div class='page_note_error'>There are no questions attached to this group.</div></td></tr>";
        }
        $this->body .= "
				</table>
				</div></fieldset>
				<div class='center'><a href=index.php?mc=users&page=users_group_questions_new&b=" . $group_id . " class=mini_button>" . $this->messages["5509"] . "</a></div>
				<div class='center'><a href=index.php?mc=users&page=dropdowns class=mini_button>View Current Pre-Valued Dropdowns</a></div>
				";

        $this->body .= "
			<div style='padding: 5px;'><a href=index.php?mc=users&page=users_group_edit&c=" . $group_id . " class='back_to'>
			<i class='fa fa-backward'></i> Back to " . $group_name . " Details</a></div>
			";

        return true;
    } //end of function show_current_questions

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    function show_all_dropdowns($db)
    {
        if (PHP5_DIR) {
            $menu_loader = geoAdmin::getInstance();
        } else {
            $menu_loader =& geoAdmin::getInstance();
        }

        $this->sql_query = "select * from " . $this->sell_choices_types_table . " order by type_name";
        $result = $db->Execute($this->sql_query);
        if (!$result) {
            trigger_error("ERROR SQL: " . $db->ErrorMsg());
            $menu_loader->userError("Internal error.");
            $this->body .= $menu_loader->getUserMessages();
            return false;
        }

        //$this->title = "Current Pre-configured Sell Question Dropdown Boxes";
        $this->description = "This is the current list of dropdown box choices
			that can be used by any customized question. <br> - To change a dropdown box click the edit link next to it<br>
			- To delete that dropdown set click delete link next to it<br>
			<b>remember:</b>  these are just dropdown box choices you attach to sell questions that are then displayed with the group
			they are attached to.  These dropdowns will show up as a choice in the \"choices\" category of the add or edit sell question form.
			So create your dropdowns here first then they will then become a choice to attach to a question
			<br><br>All dropdown choice boxes are administered here.";
        $this->body .= '<div></div><script type="text/javascript">
Text[1] = ["Current Pre-Valued Dropdowns","Dropdowns created here can be used in 3 places:<br /><br />- <strong>Optional Site Wide Fields</strong><br />- <strong>Category Specific Questions</strong><br />- <strong>Group Questions</strong> (Group questions are Enterprise Only)<br />"]

</script>';

        $this->body .= $menu_loader->getUserMessages();
        $this->body .= "
			<fieldset id='PreValDropdowns'>
				<legend>Current Pre-Valued Dropdowns " . $this->show_tooltip(1, 1) . "</legend><table cellpadding=2 cellspacing=1 border=0 width=450>
				";
        if ($result->RecordCount() > 0) {
            $this->body .= "
						<tr>
							<td class=\"col_hdr_left\"><b>Dropdown Name</b></td>
							<td class=\"col_hdr\" align=\"center\">&nbsp;</td>
							<td class=\"col_hdr\" align=\"center\">&nbsp;</td>
						</tr>";
            $this->row_count = 1;
            while ($show = $result->FetchRow()) {
                $this->body .= "
						<tr class=" . $this->get_row_color() . ">
							<td class=medium_font>" . $show["type_name"] . " </td>
							<td align='center'>" . geoHTML::addButton('edit', "index.php?mc=" . $this->category_name . "&page=edit_dropdown&c=" . $show["type_id"]) . "</td>
							<td align='center'>" . geoHTML::addButton('delete', "index.php?mc=" . $this->category_name . "&page=delete_dropdown_int&d=" . $show["type_id"] . "&auto_save=1", false, '', 'lightUpLink mini_cancel') . "</td>
						</tr>";
                $this->row_count++;
            }
        } else {
            $this->body .= "
						<tr><td class=medium_font>There are no current dropdowns</td></tr>";
        }
        $this->body .= "
						<tr><td colspan=\"3\">";
        $this->new_dropdown_form();
        $this->body .= "</td></tr>
					<td>
				</tr>
			</table></fieldset>";
        /*
         * REMOVED FOR SHARED FUNCTION USE
         * <tr><td><a href=index.php?mc=users&page=users_groups class=medium_font>back to groups home</span></a></td></tr>
         */
        return true;
    } //end of function show_all_dropdowns

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    function new_dropdown_form()
    {
        if (!$this->admin_demo()) {
            $this->body .= "<form action=index.php?page=new_dropdown method=post>\n";
        }

        $this->body .= "<table cellpadding=2 cellspacing=0 border=0>\n";
        //$this->title = "Add a new Sell question Dropdown Form";
        $this->description = "Use this form to add a new dropdown to
			the dropdowns usable as a question.  Type the name below and click \"enter\".  You will then be able to add values to
			the dropdown you have just created.";
        $this->body .= "<tr>\n\t
			<td align=right class=col_ftr>New Dropdown: </td>\n\t
			<td class=col_ftr><input type=text name=b[dropdown_label] size=35></td>\n";
        $this->body .= "<td class=col_ftr><input type=submit name='auto_save' value=\"Add Dropdown\">\n\t</td>\n</tr>\n";
        $this->body .= "</table>\n";
        $this->body .= "</form>\n";
        return true;
    } //end of function new_dropdown_form

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    function insert_new_dropdown($db, $information = 0)
    {
        if ($information) {
            if (strlen(trim($information["dropdown_label"])) > 0) {
                $this->sql_query = "insert into " . $this->sell_choices_types_table . "
					(type_name)
					values
					(\"" . $information["dropdown_label"] . "\")";
                $result = $db->Execute($this->sql_query);
                if (!$result) {
                    //echo $this->sql_query."<br>\n";
                    return false;
                }
                $id = $db->Insert_ID();
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    } //end of function insert_new_dropdown

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    function edit_dropdown($db, $dropdown_id = 0)
    {
        //NOTE: function not used? See display_edit_dropdown() in admin_extra_questions.php
        if (PHP5_DIR) {
            $menu_loader = geoAdmin::getInstance();
        } else {
            $menu_loader =& geoAdmin::getInstance();
        }


        if ($dropdown_id) {
            $this->sql_query = "select * from " . $this->sell_choices_types_table . " where type_id = $dropdown_id";
            $result = $db->Execute($this->sql_query);
            if (!$result) {
                trigger_error("ERROR SQL: " . $db->ErrorMsg());
                $menu_loader->userError("Internal error.");
                $this->body .= $menu_loader->getUserMessages();
                return false;
            } elseif ($result->RecordCount() == 1) {
                //this dropdown exists
                $show_dropdown = $result->FetchRow();
                $this->sql_query = "select * from " . $this->classified_sell_choices_table . " where type_id = " . $dropdown_id . " order by display_order";
                $result = $db->Execute($this->sql_query);
                if (!$result) {
                    return false;
                }
                $this->body .= $menu_loader->getUserMessages();
                //show the form to edit this dropdown
                if (!$this->admin_demo()) {
                    $this->body .= "<form action=index.php?mc=" . $this->category_name . "&page=edit_dropdown&c=" . $dropdown_id . " method=post>\n";
                }
                $this->body .= "<fieldset id='EditPreValDropdown'>
				<legend>Edit a Pre-Valued Dropdown</legend><table cellpadding=2 cellspacing=0 border=0 width=\"100%\">\n";
                //$this->title = "Edit Sell question Dropdown Form";
                $this->description = "Use this form to add or delete values
					appearing in the category question dropdowns.  Insert a new value by typing the value and then choosing a value for
					display order.  The display order value determines the order the values appear in the dropdown.  Otherwise the order is
					alphabetically.";

                $this->body .= "<tr>\n\t<td>\n\t<table cellpadding=2 cellspacing=0 border=0>\n\t";
                $this->body .= "<tr>\n\t\t<td class=col_hdr_left>\n\tValue \n\t\t</td>\n\t\t";
                $this->body .= "<td class=col_hdr>\n\tDisplay Order \n\t\t</td>\n\t\t";
                $this->body .= "<td class=col_hdr>\n\t&nbsp; \n\t\t</td>\n\t</tr>\n\t";
                if ($result->RecordCount() > 0) {
                    //this dropdown exists
                    //show the value in a list
                    $this->row_count = 0;
                    while ($show = $result->FetchRow()) {
                        $this->body .= "<tr class=" . $this->get_row_color() . ">\n\t\t<td class=medium_font>\n\t" . $show["value"] . " \n\t\t</td>\n\t\t";
                        $this->body .= "<td class=medium_font align=center>\n\t" . $show["display_order"] . " \n\t\t</td>\n\t\t";
                        $this->body .= "<td>\n\t\t" . geoHTML::addButton('delete', "index.php?mc=" . $this->category_name . "&page=delete_dropdown_value&g=" . $show["value_id"] . "&c=" . $dropdown_id . "&auto_save=1", false, '', 'lightUpLink mini_cancel') . "\n\t\t</td>\n\t\t";
                        $this->row_count++;
                    }
                }
                $this->body .= "<tr>\n\t<td class=col_ftr align=center>Enter New Value: \n\t<input type=text name=b[value] size=25 maxsize=50> \n\t</td>\n\t
					<td class=col_ftr align=center>\n\t<select name=b[display_order]>\n\t\t\t";
                for ($i = 1; $i < 51; $i++) {
                    $this->body .= "<option>" . $i . "</option>\n\t\t\t";
                }
                $this->body .= "</select> \n\t</td>\n\t";
                if (!$this->admin_demo()) {
                    $this->body .= "<td class=col_ftr>\n\t<input type=submit name='auto_save' value=\"Save\"> \n\t</td>\n\t</tr>\n\t";
                }

                /*
                 * REMOVED FOR SHARED FUNCTION USE
                 * $this->body .= "<tr class=row_color_red>\n\t<td><a href=index.php?mc=users&page=users_groups><span class=medium_font_light>Group Home</span></a></td>\n</tr>\n";
                 */
                $this->body .= "</table>\n\t</td>\n</tr>\n";
                $this->body .= "<tr>\n\t<td align=center><a href=index.php?mc=" . $this->category_name . "&page=all_dropdowns><span class=medium_font><strong>Show All Dropdowns</strong></span></a></td>\n</tr>\n";
                $this->body .= "</td>\n</tr>\n</table></fieldset>\n";
                $this->body .= "<div class='clearColumn'></div>";
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
        if (PHP5_DIR) {
            $menu_loader = geoAdmin::getInstance();
        } else {
            $menu_loader =& geoAdmin::getInstance();
        }

        if (($information) && ($dropdown_id)) {
            if (strlen(trim($information["value"])) > 0 || $information['display_order'] == 1) {
                $this->sql_query = "insert into " . $this->classified_sell_choices_table . "
					(type_id,value,display_order)
					values
					(" . $dropdown_id . ",\"" . $information["value"] . "\"," . $information["display_order"] . ")";
                $result = $db->Execute($this->sql_query);
                if (!$result) {
                    return false;
                }
                $id = $db->Insert_ID();
                return $id;
            } else {
                $menu_loader->userError('Error:  Blank values are only allowed with display order 1.');
                return false;
            }
        } else {
            return false;
        }
    } //end of function add_dropdown_value

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    function delete_dropdown_value($db, $value_id = 0)
    {
        if ($value_id) {
            $this->sql_query = "delete from " . $this->classified_sell_choices_table . " where value_id = " . $value_id;
            $result = $db->Execute($this->sql_query);
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
            $this->sql_query = "select * from " . $this->sell_choices_types_table . " where type_id = " . $dropdown_id;
            $result = $db->Execute($this->sql_query);
            if (!$result) {
                return false;
            } elseif ($result->RecordCount() == 1) {
                if (!$this->admin_demo()) {
                    $this->body .= "<form action=index.php?mc=" . $this->category_name . "&page=delete_dropdown&d=" . $dropdown_id . " method=post>\n";
                }
                $this->body .= "<table cellpadding=2 cellspacing=0 border=0 width=\"100%\">\n";
                //$this->title = "Delete Sell question Dropdown Form (verification)";
                $this->description = "If the sell question dropdown you are trying to delete
					is attached to existing categories you will be given a choice to push those category questions to other dropdowns (if any).
					Or just remove the sell questions attached (if any) to this dropdown as well as the dropdown itself.";

                $show_dropdown = $result->FetchRow();
                $this->sql_query = "select * from " . $this->sell_questions_table . " where choices = " . $dropdown_id;
                $result = $db->Execute($this->sql_query);
                if (!$result) {
                    return false;
                } elseif ($result->RecordCount() > 0) {
                    //there are sell questions attached to this
                    $attached = 1;

                    //show attached categories
                    $this->body .= "<tr>\n\t<td>\n\t";
                    $this->body .= "<table cellpadding=2 cellspacing=0 border=0>\n";
                    $this->body .= "<tr class=row_color_black>\n\t<td class=medium_font_light>\n\tcategories attached to<br>
						this question dropdown \n\t</td>\n</tr>\n";
                    $this->row_count = 1;
                    while ($show_categories = $result->FetchRow()) {
                        $this->body .= "<tr class=" . $this->get_row_color() . ">\n\t<td class=medium_font>" . $this->get_category_name($db, $show_categories["category_id"]) . " \n\t</td>\n</tr>\n";
                        $this->row_count++;
                    }
                    $this->body .= "</td>\n\t</tr>\n";

                    $this->sql_query = "select * from " . $this->sell_choices_types_table . " where type_id = " . $dropdown_id;
                    $dropdown_result = $db->Execute($this->sql_query);
                    if (!$dropdown_result) {
                        return false;
                    } elseif ($dropdown_result->RecordCount() > 0) {
                        $this->body .= "<tr>\n\t<td class=medium_font>\n\tmove these category sell <br>questions to this dropdown ";
                        $this->body .= "<select name=z[new_dropdown]>\n\t\t";
                        $this->body .= "<option value=none>choose dropdown</option>\n\t\t";
                        while ($show_other = $dropdown_result->FetchRow()) {
                            $this->body .= "<option value=" . $show_other["type_id"] . ">" . $show_other["type_name"] . "</option>\n\t\t";
                        }
                        $this->body .= " \n\t</td>\n</tr>\n";
                    }
                    if (!$this->admin_demo()) {
                        $this->body .= "<tr class=row_color_black>\n\t<td class=medium_font_light>\n\t<input type=submit name=z[type_of_submit]
							value=\"change and delete\"> \n\t</td>\n</tr>\n";
                        $this->body .= "<tr>\n\t<td align=center class=medium_font>\n\t<input type=submit name=z[type_of_submit]
							value=\"delete all references\"> \n\t</td>\n</tr>\n";
                    }
                        $this->body .= "</table>\n";
                } else {
                    $this->body .= "<tr>\n\t<td class=medium_font align=center>\n\t";
                    if (!$this->admin_demo()) {
                        $this->body .= "<input type=submit name=z[type_of_submit] value=\"delete all references\"> \n\t";
                    }
                    $this->body .= "</td>\n</tr>\n";
                }
                $this->body .= "</table>\n";

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
            //echo $information["type_of_submit"]." delete<br>\n";
            if ($information["type_of_submit"] == "delete all references") {
                $this->sql_query = "delete from " . $this->sell_questions_table . " where choices = " . $dropdown_id;
                //echo $this->sql_query."<br>\n";
                $result = $db->Execute($this->sql_query);
                if (!$result) {
                    return false;
                }

                $this->sql_query = "delete from " . $this->classified_sell_choices_table . " where type_id = " . $dropdown_id;
                //echo $this->sql_query."<br>\n";
                $result = $db->Execute($this->sql_query);
                if (!$result) {
                    return false;
                }

                $this->sql_query = "delete from " . $this->sell_choices_types_table . " where type_id = " . $dropdown_id;
                //echo $this->sql_query."<br>\n";
                $result = $db->Execute($this->sql_query);
                if (!$result) {
                    return false;
                }
                return true;
            } elseif ($information["type_of_submit"] == "change and delete") {
                if ($information["new_dropdown"] != "none") {
                    $this->sql_query = "update " . $this->sell_questions_table . " set
						choices = " . $information["new_dropdown"] . "
						where choices = " . $dropdown_id;
                    //echo $this->sql_query."<br>\n";
                    $result = $db->Execute($this->sql_query);
                    if (!$result) {
                        return false;
                    }
                }
                $this->sql_query = "delete from " . $this->classified_sell_choices_table . "," . $this->sell_choices_types_table . " where type_id = " . $dropdown_id;
                //echo $this->sql_query."<br>\n";
                $result = $db->Execute($this->sql_query);
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
            $this->sql_query = "delete from " . $this->sell_questions_table . " where question_id = " . $question_id;
            $result = $db->Execute($this->sql_query);
            if (!$result) {
                return false;
            }

            $this->sql_query = "delete from geodesic_classifieds_sell_questions_languages where question_id = " . $question_id;
            $result = $db->Execute($this->sql_query);
            if (!$result) {
                return false;
            }
            return true;
        } else {
            return false;
        }
    } //end of function delete_dropdown_value

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
    function display_users_group_questions()
    {
        if (!$this->show_current_questions($this->db, $_GET["d"])) {
            return false;
        }
        $this->display_page();
    }
    function update_users_group_questions()
    {
    }
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
    function display_users_group_questions_new()
    {
        if ($this->debug_questions) {
            echo "top of display_users_group_questions_new<br>\n";
            echo $_REQUEST['b'] . " is b<br>\n";
            echo $_REQUEST['c'] . " is c<br>\n";
            echo $_REQUEST['d'] . " is d<br>\n";
        }
        if ($_REQUEST['c'] && $_REQUEST['d']) {
            if (!$this->show_current_questions($this->db, $_REQUEST['d'])) {
                return false;
            }
        } elseif ($_REQUEST['b'] && $_REQUEST['c']) {
            if (!$this->show_current_questions($this->db, $_GET["c"])) {
                return false;
            }
        } elseif (!$this->group_question_form($this->db, 0, $_REQUEST["b"])) {
            return false;
        }
        $this->display_page();
    }
    function update_users_group_questions_new()
    {
        if ($this->debug_questions) {
            echo "top of update_users_group_questions_new<br>\n";
            echo $_REQUEST['b'] . " is b<br>\n";
            echo $_REQUEST['c'] . " is c<br>\n";
            echo $_REQUEST['d'] . " is d<br>\n";
        }
        if ($_REQUEST['c'] && $_REQUEST['d']) {
            if ($this->debug_questions) {
                echo "ABOUT TO UPDATE SELL QUESTION<bR>\n";
            }
            return $this->update_sell_question($this->db, $_REQUEST["c"], $_REQUEST["b"]);
        } elseif ($_REQUEST['b'] && $_REQUEST['c']) {
            if ($this->debug_questions) {
                echo "ABOUT TO INSERT SELL QUESTION<bR>\n";
            }
            return $this->insert_sell_question($this->db, $_REQUEST["b"], $_REQUEST["c"]);
        }
    }

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
    function display_users_group_questions_edit()
    {
        if ($_REQUEST['b'] && $_REQUEST['c']) {
            if (!$this->show_current_questions($this->db, $_GET["c"])) {
                return false;
            }
        } elseif (!$this->group_question_form($this->db, $_REQUEST["b"])) {
            return false;
        }
        $this->display_page();
    }
    function update_users_group_questions_edit()
    {
        return $this->update_sell_question($this->db, $_REQUEST["c"], $_REQUEST["b"]);
    }
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
    function display_users_group_questions_delete()
    {
        if ($_REQUEST['b'] && $_REQUEST['c']) {
            if (!$this->delete_sell_question($this->db, $_REQUEST["b"])) {
                return false;
            }
        }

        if (!$this->show_current_questions($this->db, $_GET["c"])) {
            return false;
        }
        $this->display_page();
    }
    function update_users_group_questions_delete()
    {
    }
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    function display_listing_dropdowns()
    {
        $this->show_all_dropdowns($this->db);
        $this->display_page();
    }
    function update_listing_all_dropdowns()
    {
    }
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    function display_category_dropdowns()
    {
        if ($_GET['mc'] == "categories") {
            $this->category_name = "category_dropdowns";
        }

        if (!$this->show_all_dropdowns($this->db)) {
            return false;
        }
        $this->display_page();
    }
    function update_category_all_dropdowns()
    {
    }

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    function display_group_dropdowns()
    {
        if ($_GET['mc'] == "users") {
            $this->category_name = "group_dropdowns";
        }

        if (!$this->show_all_dropdowns($this->db)) {
            return false;
        }
        $this->display_page();
    }
    function update_group_dropdowns()
    {
    }
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    function display_all_dropdowns()
    {
        if (PHP5_DIR) {
            $menu_loader = geoAdmin::getInstance();
        } else {
            $menu_loader =& geoAdmin::getInstance();
        }
        $this->body .= $menu_loader->getUserMessages();

        if ($_GET['mc']) {
            $this->category_name = $_GET['mc'];
        }

        if (!$this->show_all_dropdowns($this->db)) {
            return false;
        }
        $this->display_page();
    }
    function update_all_dropdowns()
    {
    }

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
    function display_edit_dropdown()
    {
        if ($_GET['mc']) {
            $this->category_name = $_GET['mc'];
        }

        $this->edit_dropdown($this->db, $_REQUEST["c"]);
        $this->display_page();
    }
    function update_edit_dropdown()
    {
        return $this->add_dropdown_value($this->db, $_REQUEST["c"], $_REQUEST["b"]);
    }
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
    function display_delete_dropdown()
    {
        if (PHP5_DIR) {
            $menu_loader = geoAdmin::getInstance();
        } else {
            $menu_loader =& geoAdmin::getInstance();
        }


        if ($_GET['mc']) {
            $this->category_name = $_GET['mc'];
        }

        if ($this->delete_dropdown($this->db, $_GET["d"], $_REQUEST["z"])) {
            $menu_loader->userSuccess('Settings Saved.');
            if ($this->show_all_dropdowns($this->db)) {
                $this->display_page();
                return true;
            }
        }
        return false;
    }
    function update_delete_dropdown()
    {
    }
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
    function display_delete_dropdown_value()
    {
        if (PHP5_DIR) {
            $menu_loader = geoAdmin::getInstance();
        } else {
            $menu_loader =& geoAdmin::getInstance();
        }
        $this->body .= $menu_loader->getUserMessages();

        if ($_GET['mc']) {
            $this->category_name = $_GET['mc'];
        }
        $this->edit_dropdown($this->db, $_REQUEST["c"]);
        $this->display_page();
    }
    function update_delete_dropdown_value()
    {
        return ($this->delete_dropdown_value($this->db, $_REQUEST["g"]));
    }
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
    function display_delete_dropdown_int()
    {
        if (PHP5_DIR) {
            $menu_loader = geoAdmin::getInstance();
        } else {
            $menu_loader =& geoAdmin::getInstance();
        }
        $this->body .= $menu_loader->getUserMessages();

        if ($_GET['mc']) {
            $this->category_name = $_GET['mc'];
        }

        if (!$this->delete_dropdown_intermediate($this->db, $_GET["d"])) {
            return false;
        }
        $this->display_page();
    }
    function update_delete_dropdown_int()
    {
    }
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
    function display_new_dropdown()
    {
        $this->display_listing_dropdowns();
    }
    function update_new_dropdown()
    {
        return $this->insert_new_dropdown($this->db, $_REQUEST["b"]);
    }
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
} //end of class Admin_category_questions
