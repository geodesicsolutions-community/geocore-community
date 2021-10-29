<?php
//order_items/_site_class_temp.php
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
## ##    17.04.0-5-ge04db22
## 
##################################

require_once CLASSES_DIR . 'site_class.php';

/**
 * This is a TEMPORARY CLASS: This class is only meant to be temporary, to be used while
 * refactoring to split up the sell and site classes
 */

class tempSiteClass extends geoSite {
	public $session_variables;
	public $return_value = 0;
	
	public $offsite_videos = null;
	public $offsite_videos_from_db = true;
	
	public $debug_ad_display = 0;
	public $debug_ad_display_time = 0;
	public $debug_show_all_options = 0;
	
	/**
	 * Convenience var for sending data between parent/child functions durring recurring classified placement
	 * @internal
	 * @var bool
	 */
	public $recurring_classified_details = false; 
	
	//TODO: Temporary moved this here, need to put it somewhere better and smarty template it.
	function display_group_questions($db)
	{
		if(!geoPC::is_ent()) {
			return false;
		}
		//$this->body .=count($this->category_questions)." is the count of category questions<br />\n";
		if (isset($this->group_questions) && count($this->group_questions) > 0)
		{
			//$category_questions = array_reverse($category_questions);  //puts question in order of general to specific
			//if (strlen(urldecode($this->messages[404])))
			//	$this->body .="<tr class=\"place_an_ad_details_fields\">\n\t<td colspan=\"2\" >".urldecode($this->messages[404])."</td>\n</tr>\n";
			//if (strlen(urldecode($this->messages[405])))
			//	$this->body .="<tr class=\"place_an_ad_details_fields\">\n\t<td colspan=\"2\" >".urldecode($this->messages[405])."</td>\n</tr>\n";
			//asort($this->category_questions); //crutch

			foreach ($this->group_questions as $key => $value) {
				//spit out the questions
				$this->body .="<tr>\n\t<td>&nbsp;</td><td  class=\"place_an_ad_details_fields\">".$this->group_questions[$key]."\n\t</td>\n\t";
				$this->body .="<td class=\"place_an_ad_details_data\">\n\t";
				//$this->body .=$this->category_choices[$key]." is category choices ".$key." <br />\n\t";
				//if (($this->category_choices[$key] == "none") || ($this->category_choices[$key] == "url"))
				if ((strcmp(trim($this->group_choices[$key]), "none") == 0) || (strcmp(trim($this->group_choices[$key]), "url") == 0))
				{
					//spit out the normal input tag if there are no choices for this question
					$this->body .="<input class=\"data_fields\" type=\"text\" name=\"b[group_value][".$key."]\" value=\"".urldecode($this->session_variables["group_value"][$key])."\" size=\"30\" maxlength=\"256\" />\n\t";
				}
				elseif (strcmp(trim($this->group_choices[$key]), "check") == 0)
				{
					//display a checkbox
					$this->body .= "<input class=\"data_fields\" type=\"checkbox\" name=\"b[group_value][".$key."]\" value=\"Yes\" ";
					if ($this->session_variables["group_value"][$key] == $this->group_questions[$key])
					$this->body .= "checked=\"checked\"";
					$this->body .= " />".$show_choices->VALUE;
				}
				elseif (strcmp(trim($this->group_choices[$key]), "textarea") == 0)
				{
					$this->body .="\n\t<textarea name=\"b[group_value][".$key."]\" cols=\"60\" rows=\"15\" class=\"place_an_ad_details_data\" ";
					$textareawrap = (is_object($this->ad_configuration_data)) ? $this->ad_configuration_data->TEXTAREA_WRAP : $this->ad_configuration_data['textarea_wrap'];

					if ($textareawrap)
					{
						$this->body .= "style=\"white-space: pre;\">";//"wrap=\"virtual\">";
						$this->body .= geoString::specialChars(preg_replace('/<br[\s]*\/?>/i'," \n",$this->session_variables["group_value"][$key]));
					}
					else
					{
						$this->body .= ">";//"wrap=\"soft\">";
						$this->body .= geoString::specialChars($this->session_variables["group_value"][$key]);
					}
					$this->body .="</textarea>";
				}				
				elseif (strcmp(trim($this->group_choices[$key]), "none") != 0)
				{
					//get the list of choices for this question
					$sql = "SELECT * FROM ".$this->sell_choices_table." WHERE type_id = \"".$this->group_choices[$key]."\" ORDER BY display_order,value";
					//$this->body .=$sql." is the query to get sell_choices<br />\n";
					$result = $this->db->Execute($sql);
					if (!$result)
					{
						return false;
					}
					elseif ($result->RecordCount() > 0)
					{
						$this->body .="<select class=\"place_an_ad_details_data\" name=\"b[group_value][".$key."]\">\n\t\t";
						$this->body .="<option></option>\n\t\t";
						while ($show_choices = $result->FetchNextObject())
						{
							//put choices in options of this select statement
							$this->body .="<option ";
							if ($this->session_variables["group_value"][$key] == $show_choices->VALUE)
							$this->body .="selected=\"selected\"";
							$this->body .=">".$show_choices->VALUE."</option>\n\t\t";
						}
						$this->body .="</select>\n\t";
					}
					if ($this->group_other_box[$key] == 1)
					$this->body .=urldecode($this->messages[406])."<input type=\"text\" size=\"12\" maxlength=\"50\" name=\"b[group_value_other][".$key."]\" value=\"".$this->session_variables["group_value_other"][$key]."\" />";
				} //end of if $category_questions[$i]["choices"] != "none"
				else
				{
					//spit out the normal input tag if there are no choices for this question
					$this->body .="<input class=\"place_an_ad_details_data\" type=\"text\" name=\"b[group_value][".$key."]\" value=\"".$this->session_variables["question_value"][$key]."\" size=\"30\" maxlength=\"30\" />\n\t";
				}
				$this->body .="</td>\n</tr>\n";

			} // end of while
		} //end of if (count($category_questions) > 0)
	}
	
	//TODO: Temporary moved this here, need to put it somewhere better and smarty template it.
	function get_group_questions($db,$group_id=0)
	{
		if(!geoPC::is_ent()) {
			return false;
		}
		//get sell questions specific to this category
		if ($this->debug_sell)
		{
			echo "<br />TOP OF GET_GROUP_QUESTIONS<br />\n";
		}
		if ($group_id != 0)
		{
			//get the questions for this category
			$sql = "SELECT * FROM ".$this->classified_sell_questions_table." WHERE group_id = ".$group_id." ORDER BY display_order";
			$result = $this->db->Execute($sql);
			if (!$result)
			{
				$this->site_error($this->db->ErrorMsg());
				return false;
			}

			if ($result->RecordCount() > 0)
			{
				//$this->body .="hello from inside a positive results<br />\n";
				while ($get_questions = $result->FetchNextObject())
				{
					//get all the questions for this category and store them in the auction_questions variable
					//$this->body .=$get_questions["question_key"]." is the question key<br />\n";
					//get all the questions for this category and store them in the category_questions variable
					//also get the language specific name and explanation
					$this->sql_query = "SELECT * FROM `geodesic_classifieds_sell_questions_languages` WHERE question_id = ? and language_id = ?";
					$language_specific_result = $this->db->Execute($this->sql_query, array($get_questions->QUESTION_ID,$this->language_id));					
					if ((!$language_specific_result) || ($language_specific_result->RecordCount() != 1))
					{
						//set the default language text from the classified_sell_questions_table
						//as the upgrade may have failed or not been run
						$this->group_questions[$get_questions->QUESTION_ID] = $get_questions->NAME;
						$this->group_explanation[$get_questions->QUESTION_ID] = $get_questions->EXPLANATION;
						$this->group_choices[$get_questions->QUESTION_ID] = $get_questions->CHOICES;
					}
					else
					{
						$question_name_and_explanation = $language_specific_result->FetchRow();
						$this->group_questions[$get_questions->QUESTION_ID] = $question_name_and_explanation["name"];
						$this->group_explanation[$get_questions->QUESTION_ID] = $question_name_and_explanation["explanation"];
						$this->group_choices[$get_questions->QUESTION_ID] = $question_name_and_explanation["choices"];
					}
					
					//$this->group_questions[$get_questions->QUESTION_ID] = $get_questions->NAME;
					//$this->group_explanation[$get_questions->QUESTION_ID] = $get_questions->EXPLANATION;
					//$this->group_choices[$get_questions->QUESTION_ID] = $get_questions->CHOICES;
					$this->group_display_order[$get_questions->QUESTION_ID] = $get_questions->DISPLAY_ORDER;
					$this->group_other_box[$get_questions->QUESTION_ID] = $get_questions->OTHER_INPUT;

					//$this->body .=$get_questions->CHOICES." is the choices for ".$get_questions->QUESTION_ID."<br />\n\t";
				} //end of while $get_questions = mysql_fetch_array($result)
			} //end of if ($result)

		} //end of if ($group_id != 0)

	}
	
	//TODO: move this somewhere else, this is temporary location for sell class
	function set_terminal_category($db = 0,$category_id)
	{
		//set the category name and category variables
		$sql = "select category_name from ".$this->categories_table." where category_id = ".$category_id;
		$result = $this->db->Execute($sql);

		if (!$result)
		{
			//$this->body .=$sql." is the query<br />\n";
			$this->error_message = urldecode($this->messages[57]);
			return false;
		}
		elseif ($result->RecordCount() == 1)
		{
			if ($this->classified_id)
			{
				//delete the current category questions because the category has changed
				$this->delete_current_category_questions($db);

				//and unset any current category questions in session_variables
				unset ($this->session_variables["question_value"]);
				unset ($this->category_questions);
				unset ($this->category_explanation);
				unset ($this->category_choices);
				unset ($this->category_other_box);
			}
			$show = $result->FetchNextObject();
			$this->terminal_category_name = $show->CATEGORY_NAME;
			$this->update_terminal_category($db,$category_id);
			$this->price_plan = $this->get_price_plan($db);
			return true;
		}
		else
		{
			$this->error_message = urldecode($this->messages[57]);
			return false;
		}
	}
	
	
	function update_terminal_category($db,$terminal_category)
	{
		$this->terminal_category = $terminal_category;
		$this->session_variables['terminal_category'] = $terminal_category;
	}
	
	
	//TODO: FIX THESE FUNCTIONS
	
	function get_form_variables ($info)
	{
		//get the variables from the form and save them
		
		//List of keys that should NOT be set here, for instance if these
		//are set using AJAX so should never be inserted as part of the form fields
		$skip = array('cost_options'=>1, 'cost_options_quantity'=>1);
		if (is_array($info)) {
			foreach ($info as $key => $value) {
				if (isset($skip[$key])) {
					//don't set this from form variables
					continue;
				}
				if ($value != "none") {
					if (!is_array($value)) {
						$this->session_variables[$key] = $value;
					} else {
						if ($key == 'start_time') {
							$this->session_variables[$key] = mktime($value['start_hour'],$value['start_minute'],0,$value['start_month'],$value['start_day'],$value['start_year']);
						} else {
							// NOTE:
							// Below is a work around to what we believe is a bug in PHP
							// If we assigned a value to any element in a sub-array of
							// the $this->session_variables variable it would just grab the
							// first character of the string or number
							$this->temp_array = array();
							foreach ($value as $category_specific_key => $category_specific_value) {
								$this->temp_array[$category_specific_key] = $category_specific_value;
								//highlight_string(print_r($this->temp_array, 1));
								//echo $key." is the category_specific_key - \"".$category_specific_value."\"<br />\n";
							}
							$this->session_variables[$key] = $this->temp_array;
							//highlight_string(print_r($this->session_variables[$key], 1));
						}
					}
					//echo $key." is the key and this is the value - ".$this->session_variables[$key]."<br />\n";
				} elseif (($key == "state") || ($key == "country") || ($key == "mapping_state") || ($key == "mapping_country")) {
					$this->session_variables[$key] = $value;
					//echo $key." is the key and this is the value - ".$this->session_variables[$key]."<br />\n";
				}
			}

			//need to account for UN-CHECKING checkboxes during an edit/copy (specifically, Payment Types Accepted and Category-Specific Checkboxes)
			// -- in both of these cases, the data is simply not present in the submitted form, which makes things fall back on saved session vars, when the intent is really to clear ANY saved value
			if(!isset($info['payment_options_from_form']) && isset($this->session_variables['payment_options_from_form']) && count($this->session_variables['payment_options_from_form']) > 0) {
				//unsetting all Payment Types Accepted
				unset($this->session_variables['payment_options_from_form']);
				unset($this->session_variables['payment_options']);
			}
			if(!isset($info['question_value']) && isset($this->session_variables['question_value']) && count($this->session_variables['question_value']) > 0) {
				//unsetting ALL category-specific questions
				unset($this->session_variables['question_value']);
				unset($this->session_variables['question_display_order']);
			}
			
			//assemble auto-title if we're using it
			$title = "";
			//first, try by category
			$sql = "select use_auto_title, auto_title from ".$this->db->geoTables->categories_table." where category_id=".$this->terminal_category;
			$result = $this->db->Execute($sql);
			if($result&&$result->RecordCount() == 1)
			{
				$title_result = $result->FetchRow();
				$use_auto_title = ($title_result['use_auto_title'] == 1) ? true : false;
				if($use_auto_title)
				{
					$pieces = explode("|", $title_result['auto_title']);
					foreach($pieces as $piece)
					{
						if($piece == "0") // this must be "0" and not 0
							continue;
						if(strpos($piece, "oswf") !== false && geoPC::is_ent())
						{
							//this piece is a sitewide field
							$field = substr($piece, 4);
							$title .= (strlen(trim($info['optional_field_'.$field.'_other']))) ? $info['optional_field_'.$field.'_other'] : $info['optional_field_'.$field];
						}
						else
						{
							if(strlen($this->session_variables['question_value_other'][$piece]) > 0)
								$title .= $this->session_variables['question_value_other'][$piece];
							elseif(strlen($this->session_variables['question_value'][$piece]) > 0)
								$title .= $this->session_variables['question_value'][$piece];
						}
						$title .= " ";
					}
					$title = trim($title);
					if(strlen($title) > 0)
						$this->session_variables["classified_title"] = $title;
				}
			}
			//then, by sitewide
			if(strlen($title) == 0 && $this->db->get_site_setting("use_sitewide_auto_title") == 1)
			{
				$pieces = explode("|", $this->db->get_site_setting("sitewide_auto_title"));
				foreach($pieces as $piece) {
					if($piece == "0") {
						//this must be "0" and not 0
						continue;
					}
					//only using sitewide fields this time
					$field = substr($piece, 4);
					$title .= (strlen(trim($info['optional_field_'.$field.'_other']))) ? $info['optional_field_'.$field.'_other'] : $info['optional_field_'.$field];
					$title .= " ";
				}
				$title = trim($title);
				if(strlen($title) > 0) {
					$this->session_variables["classified_title"] = $title;
				}
			}

			//check if buy_now_only was an option in the form
			if (isset($info['bno_submitted']) && $this->session_variables['bno_submitted']) {
				$this->session_variables['buy_now_only'] = (isset($info['buy_now_only']) && $info['buy_now_only']) ? 1 : 0;
			}

			if (isset($info["type_id"]) && $this->session_variables["type_id"]) {
				$sql = "SELECT `precurrency`, `postcurrency` FROM `geodesic_currency_types` WHERE type_id = ? LIMIT 1";
				$precurrency_result = $this->db->Execute($sql, array($this->session_variables['type_id']));
			
				if ($precurrency_result && $precurrency_result->RecordCount()>0) {
					$precurrency = $precurrency_result->FetchRow();
					$this->session_variables["precurrency"] = geoString::fromDB($precurrency['precurrency']);
					$this->session_variables["postcurrency"] = geoString::fromDB($precurrency['postcurrency']);
				} else {
					trigger_error('ERROR SQL: couldn\'t get currency types. db error: '.$this->db->ErrorMsg());	
				}
			}

			$this->session_variables["buy_now_only"] = (isset($this->session_variables["buy_now_only"]) && $this->session_variables["buy_now_only"] && $this->sell_type == 2)? 1 : 0;
			$price_applies = 'lot';
			
			if ($this->session_variables["buy_now_only"]) {
				$this->session_variables["auction_buy_now"] = geoNumber::deformat($this->session_variables["auction_buy_now"]);
				$this->session_variables["auction_reserve"] = null;
				//set minimum to buy now price, so that it sorts correctly.
				$this->session_variables["auction_minimum"] = $this->session_variables["auction_buy_now"];
				if (isset($info['price_applies']) && $info['price_applies']=='item') {
					$price_applies='item';
				} else if (!isset($info['price_applies']) && isset($this->session_variables['price_applies']) && $this->session_variables['price_applies']=='item') {
					$price_applies='item';
				}
			} else {
				//loop through different prices and "sanitize" them, since they
				//are all sanitized same way.
				$prices = array ('price','auction_minimum','auction_buy_now','auction_reserve');

				foreach ($prices as $price) {
					if (isset($this->session_variables[$price]) && $this->session_variables[$price]) {
	     				if (!preg_match('/[0-9]/',$this->session_variables[$price])) {
	     					//absolutely no numbers in the value!  user must have misunderstood
	     					//the field or is playing around, treat it as if it is blank.
	     					$this->session_variables[$price]='';
	     					continue;
	     				}
	     				if(isset($info[$price])) {
	     					//only deformat this price if it's in $info (values actually submitted from the form)
	     					//if it's not in the fresh user input, this value is likely already deformatted, and doing it again will break european numbers
	     					$this->session_variables[$price] = geoNumber::deformat($this->session_variables[$price]);
	     				}
					}
				}
			}
			
			$this->session_variables['price_applies'] = $price_applies;
			
			/*
			 * note: for optional fields that are used as cost, the call to geoNumber::deformat() is done in classified_detail_check()
			 * (because there is access to $this->fields from there)
			*/

			/*
			 * note: for optional fields that are used as cost, the call to geoNumber::deformat() is done in classified_detail_check()
			 * (because there is access to $this->fields from there)
			*/

			if (isset($this->session_variables["payment_options_from_form"]) && count($this->session_variables["payment_options_from_form"]) > 0) {
				$count = 0;
				$this->session_variables['payment_options'] = implode('||',$this->session_variables['payment_options_from_form']);
			}
		}
	}
	
	function check_extra_questions()
	{
		$num_questions = isset($this->session_variables["question_value"]) ? count($this->session_variables["question_value"]) : 0;
		//$this->body .=$num_questions." is the num of questions remembered<br />\n";
		if ($num_questions > 0 )
		{
			foreach($this->session_variables['question_value'] as $key => $value)
			{
				if (strlen(trim($value)) > 0)
				{
					if ($this->category_choices[$key] == 'number') {
						//This is a number field, so scrub it down to be number-only
						$this->session_variables['question_value'][$key] = geoNumber::deformat(trim($value));
					} else if ($this->category_choices[$key] == 'date') {
						//this is a date field, scrub it down..
						$this->session_variables['question_value'][$key] = geoCalendar::fromInput($value);
					} else {
						if (isset($this->session_variables["question_value_other"][$key]) && strlen(trim($this->session_variables["question_value_other"][$key])) > 0) {
							//check other value
							//wordrap
							$this->session_variables["question_value_other"][$key] = geoString::breakLongWords($this->session_variables["question_value_other"][$key],$this->db->get_site_setting('max_word_width'), " ");
							//check the value for badwords
							$this->session_variables["question_value_other"][$key] = $this->check_for_badwords($this->session_variables["question_value_other"][$key]);
							//check the value for disallowed html
							$this->session_variables["question_value_other"][$key] = geoFilter::replaceDisallowedHtml($this->session_variables["question_value_other"][$key],0);
	
						} else {
							//check dropdown or input box value
							//wordrap
							$this->session_variables["question_value"][$key] = geoString::breakLongWords($this->session_variables["question_value"][$key],$this->db->get_site_setting('max_word_width'), " ");
							//check the value for badwords
							$this->session_variables["question_value"][$key] = $this->check_for_badwords($this->session_variables["question_value"][$key]);
							//check the value for disallowed html
							$this->session_variables["question_value"][$key] = geoFilter::replaceDisallowedHtml($this->session_variables["question_value"][$key],0);
						}
						$textareawrap = (is_object($this->ad_configuration_data)) ? $this->ad_configuration_data->TEXTAREA_WRAP : $this->ad_configuration_data['textarea_wrap'];
						if($textareawrap) {
							//newline replace for category questions
							$this->session_variables['question_value'][$key] = preg_replace('/(\r\n|\n|\r)/', "<br />", $value);
						}
					}
				}
			}
		}
	}
	
	function classified_detail_check($db=0,$category_id=0, $edit = false)
	{
		trigger_error("DEBUG SITE_CLASS: TOP OF CLASSIFIED_DETAIL_CHECK");
		trigger_error("DEBUG SITE_CLASS: sell_type: ".$this->sell_type);
		$current_time = geoUtil::time();
		$live = $edit;
		//be sure to get configuration data so that this->fields is set specific to
		//the category we are in.
		$this->site_category = (int)$category_id;
		$this->get_configuration_data();
		
		$this->get_ad_configuration($db,1);
		
		$this->error = 0;
		unset($this->error_variables);
		$this->error_variables = array();
		//get seller info for use if needed.
		$seller = (isset($this->session_variables['seller']) && $this->session_variables['seller'])? geoUser::getUser($this->session_variables['seller']): false;
		//echo "about to check for badwords<br />\n";
		
		if ($this->fields->title->is_enabled) {
			if (defined('USE_TEXTAREA_IN_TITLE')) {
				$use_textarea_in_title = USE_TEXTAREA_IN_TITLE;
			} else {
				$use_textarea_in_title = 0;
			}
			if (!$use_textarea_in_title) {
				//07/24/14 -- why the heck is this here? What purpose does it serve? It's not documented at all, and seems a stupid thing to do, so removing it....
				//$this->session_variables["classified_title"] = strtr($this->session_variables["classified_title"],"\"","'");
			}
			
			//get rid of extra newlines/weird whitespace
			$this->session_variables['classified_title'] = str_replace(array("\n","\r","\t"),"", $this->session_variables['classified_title']);
			
			if ($this->debug) echo $this->session_variables["classified_title"]." after stripslashes - " . strlen($this->session_variables["classified_title"]) . "<br />\n";
			$this->session_variables["classified_title"] = geoString::specialChars(geoString::substr(geoString::specialCharsDecode($this->session_variables["classified_title"]),0,$this->fields->title->text_length));
			if ($this->debug) echo $this->session_variables["classified_title"]." after maxlength - " . strlen($this->session_variables["classified_title"]) . "<br />\n";
			$this->session_variables["classified_title"] = geoString::breakLongWords($this->session_variables["classified_title"],$this->db->get_site_setting('max_word_width'), " ");
			if ($this->debug) echo $this->session_variables["classified_title"]." after wordwrap<br />\n";
			$this->session_variables["classified_title"] = geoFilter::replaceDisallowedHtml($this->session_variables["classified_title"],0);
			if ($this->debug) echo $this->session_variables["classified_title"]." after disallowed<br />\n";
			$this->session_variables["classified_title"] = $this->check_for_badwords($this->session_variables["classified_title"]);
			if ($this->debug) echo $this->session_variables["classified_title"]." after check for badwords<br />\n";
			
			if ($this->fields->title->is_required) {
				if (strlen(trim($this->session_variables["classified_title"])) ==0) {
					if ($edit && !$this->fields->title->can_edit) {
						//Oops!  Field is required, this is an edit, but the field 
						//is not editable, and the field is blank!
						
						//Don't throw an error in this case, or it will lock up.
					} else {
						//error in classified_title - was not entered
						$this->error++;
						$this->error_variables["classified_title"] = "error";
					}
				}
			}
		}
		
		if ($this->fields->tags->is_enabled) {
			//clean it
			$tags = explode(',',trim($this->session_variables['tags']));
			$cleanedTags = array();
			foreach ($tags as $tag) {
				//clean it, remove stuff not valid in a tag
				//Note that cleanListingTag pushes through badword filter and all that for us
				$tag = geoFilter::cleanListingTag($tag);
				
				//tag length
				if ($this->fields->tags->text_length) {
					if (strlen($tag) > $this->fields->tags->text_length) {
						$tag = geoString::substr($tag, 0, (int)$this->fields->tags->text_length);
						//get rid of end space
						$tag = trim($tag,'- ');
					}
				}
				
				if (!strlen($tag)) {
					//tag reduced down to nothing, so skip the tag
					continue;
				}
				
				if (in_array($tag, $cleanedTags)) {
					//tag added twice?
					continue;
				}
				
				$cleanedTags [] = $tag;
				if ($this->fields->tags->type_data && count($cleanedTags) >= (int)$this->fields->tags->type_data) {
					//we reached max number tags allowed
					break;
				}
			}
			
			if ($this->fields->tags->is_required && !count($cleanedTags)) {
				if ($edit && !$this->fields->tags->can_edit) {
					//Oops!  Field is required, this is an edit, but the field 
					//is not editable, and the field is blank!
					
					//Don't throw an error in this case, or it will lock up.
				} else {
					//no address chosen
					$this->error++;
					$this->error_variables["tags"] = "error";
				}
			}
			
			
			$this->session_variables['tags'] = implode(', ',$cleanedTags);
		}
		
		if ($this->fields->address->is_enabled && $this->fields->address->is_required) {
			if (($this->session_variables["address"] == "none") || (strlen(trim($this->session_variables["address"])) == 0)) {
				if ($edit && !$this->fields->address->can_edit) {
					//Oops!  Field is required, this is an edit, but the field 
					//is not editable, and the field is blank!
					
					//Don't throw an error in this case, or it will lock up.
				} else {
					//no address chosen
					$this->error++;
					$this->error_variables["address"] = "error";
				}
			}
		}
		
		
		$lowestRequiredRegion = false;
		for($r = geoRegion::getLowestLevel(); $r > 0; $r--) {
			$f = 'region_level_'.$r;
			if($this->fields->$f->is_enabled && $this->fields->$f->is_required) {
				if($edit && !$this->fields->region_level_1->can_edit) {
					//field is required, but this is an edit and regions are not editable
					//(region editing is based entirely on region_level_1's edit switch)
					continue;
				}
				$lowestRequiredRegion = $r;
				break;
			}
		}
		if($lowestRequiredRegion) {
			$locations = $this->session_variables['location'];
			if(!$locations) {
				//at least one region level is required, but there are no regions here!
				$this->error++;
				$this->error_variables['location'] = 'error';
			}
			//check for branches that don't extend all the way down to the lowest-level required
			//(i.e. if level 3 is required, but some level 2 region has no children, that's okay -- behave as if level 2 is required)
			for($i = $lowestRequiredRegion; $i > 0; $i--) {
				if(isset($locations[$i]) && !$locations[$i]) {
					//this level is present but not set, and is the lowest required or higher. generate an error.
					$this->error++;
					$this->error_variables['location'] = 'error';
					//no need to keep going once we have at least one error
					break;
				}
			}
		}
		
		$overrides = geoRegion::getLevelsForOverrides();
		if (!$overrides['city'] && $this->fields->city->is_enabled && $this->fields->city->is_required) {
			if (($this->session_variables["city"] == "none") || (strlen(trim($this->session_variables["city"])) == 0)) {
				if ($edit && !$this->fields->city->can_edit) {
					//Oops!  Field is required, this is an edit, but the field
					//is not editable, and the field is blank!
						
					//Don't throw an error in this case, or it will lock up.
				} else {
					//no city chosen
					$this->error++;
					$this->error_variables["city"] = "error";
				}
			}
		}
		

		if ($this->fields->zip->is_enabled) {
			$this->session_variables["zip_code"] = geoFilter::replaceDisallowedHtml($this->session_variables["zip_code"],1);
			$this->session_variables["zip_code"] = $this->check_for_badwords($this->session_variables["zip_code"]);
			if ($this->fields->zip->is_required) {
				if (strlen(trim($this->session_variables["zip_code"])) == 0) {
					if ($edit && !$this->fields->zip->can_edit) {
						//Oops!  Field is required, this is an edit, but the field 
						//is not editable, and the field is blank!
						
						//Don't throw an error in this case, or it will lock up.
					} else {
						//error in classified_zip - was not entered
						$this->error++;
						$this->error_variables["zip_code"] = "error";
					}

				} else if ($this->fields->zip->text_length && strlen(trim($this->session_variables["zip_code"])) > $this->fields->zip->text_length) {
					//zip too long
					$this->error++;
					$this->error_variables["zip_code"] = "error";
				}
			}
		}
		
		if ($this->userid == 0 && $this->get_site_setting('jit_registration')) {
			//just-in-time system requires an email address be given
			$jit_requireEmail = true;
		}
		
		if ($this->fields->email->is_required || $jit_requireEmail) {
			if (($this->session_variables["email_option"] == "none") || (strlen(trim($this->session_variables["email_option"])) == 0)) {
				if ($edit && !$this->fields->email->can_edit) {
					//Oops!  Field is required, this is an edit, but the field 
					//is not editable, and the field is blank!
					
					//Don't throw an error in this case, or it will lock up.
				} else {
					//no email entered
					$this->error++;
					$this->error_variables["email_option"] = "error";
				}
			}
			if($jit_requireEmail && !geoString::emailDomainCanRegister($this->session_variables["email_option"])) {
				//this email address is on a blocked domain -- dont allow it to JIT
				$this->error++;
				$this->error_variables["email_option"] = "error";
			}
		}
		if ($this->fields->phone_1->is_required) {
			if (($this->session_variables["phone_1_option"] == "none") || (strlen(trim($this->session_variables["phone_1_option"])) == 0)) {
				if ($edit && !$this->fields->phone_1->is_required) {
					//Oops!  Field is required, this is an edit, but the field 
					//is not editable, and the field is blank!
					
					//Don't throw an error in this case, or it will lock up.
				} else {
					//no phone 1 entered
					$this->error++;
					$this->error_variables["phone_1_option"] = "error";
				}
			}
		}
		if ($this->fields->phone_2->is_required) {
			if (($this->session_variables["phone_2_option"] == "none") || (strlen(trim($this->session_variables["phone_2_option"])) == 0)) {
				if ($edit && !$this->fields->phone_2->can_edit) {
					//Oops!  Field is required, this is an edit, but the field 
					//is not editable, and the field is blank!
					
					//Don't throw an error in this case, or it will lock up.
				} else {
					//no phone 2 entered
					$this->error++;
					$this->error_variables["phone_2_option"] = "error";
				}
			}
		}
		if ($this->fields->fax->is_required) {
			if (($this->session_variables["fax_option"] == "none") || (strlen(trim($this->session_variables["fax_option"])) == 0)) {
				if ($edit && !$this->fields->fax->can_edit) {
					//Oops!  Field is required, this is an edit, but the field 
					//is not editable, and the field is blank!
					
					//Don't throw an error in this case, or it will lock up.
				} else {
					//no fax number entered
					$this->error++;
					$this->error_variables["fax_option"] = "error";
				}
			}
		}
		if ($this->fields->mapping_location->is_required) {
			if (($this->session_variables["mapping_location"] == "none") || (strlen(trim($this->session_variables["mapping_location"])) == 0)) {
				if ($edit && !$this->fields->mapping_location->can_edit) {
					//Oops!  Field is required, this is an edit, but the field 
					//is not editable, and the field is blank!
					
					//Don't throw an error in this case, or it will lock up.
				} else {
					//no mapping address
					$this->error++;
					$this->error_variables["mapping_location"] = "error";
				}
			}
		}
		$lField = geoLeveledField::getInstance();
		
		$leveled_fields = $lField->getLeveledFieldIds();
		foreach ($leveled_fields as $lev_id) {
			$maxLevel = $lField->getMaxLevel($lev_id,true);
			$maxRequired = 0;
			$lev_1 = 'leveled_'.$lev_id.'_1';
			for ($l=1; $l<=$maxLevel; $l++) {
				$f = 'leveled_'.$lev_id.'_'.$l;
				if ($this->fields->$f->is_enabled && $this->fields->$f->is_required) {
					if ($edit && !$this->fields->$lev_1->can_edit) {
						//field is required, but this is an edit and this field is not editable
						break;
					}
					$maxRequired = $l;
				} else {
					//not required, and required doesn't allow skipping levels
					//so don't need to keep checking
					break;
				}
			}
			if ($maxRequired) {
				$values = (isset($this->session_variables['leveled'][$lev_id]))? (array)$this->session_variables['leveled'][$lev_id] : array();
				$val = array_pop($values);
				//check it just to make sure
				$valInfo = ($val)? $lField->getValueInfo($val) : false;
				//ok now see if level is good
				if ($valInfo && $valInfo['level']>=$maxRequired) {
					//we have enough to meet requirement!
					continue;
				}
				//not enough to meet requirement
				if ($valInfo && $valInfo['id'] == $val && $valInfo['leveled_field'] == $lev_id) {
					//see if it is a "dead end"
					if (!$lField->getValueCount($lev_id, $valInfo['id'])) {
						//no children found!  Must be a dead end, allow it...
						continue;
					}
				}
				//gets this far, it was required but not enough was filled out!
				$this->error++;
				$this->error_variables['leveled_'.$lev_id] = 'error';
			}
		}
		
		// Make sure minimum bid isnt 0.00
		trigger_error('DEBUG SITE_CLASS: '.$this->session_variables["auction_minimum"]." is the auction_minimum");
		trigger_error('DEBUG SITE_CLASS: '.$this->session_variables["auction_reserve"]." is the auction_reserve");
		trigger_error('DEBUG SITE_CLASS: '.$this->session_variables["auction_buy_now"]." is the auction_buy_now");
		if ($this->sell_type == 2) {
			$this->session_variables["auction_quantity"] = intval($this->session_variables["auction_quantity"]);
			$minQuantity = 1;
			if ($this->session_variables["auction_type"] == 2) {
				//dutch auction
				//make sure the count is at least 2
				$minQuantity = 2;				
			}
			//standard or no type selected (treat as standard)
			//make sure the count is at least $minQuantity
			if ($this->session_variables["auction_quantity"] < $minQuantity) {
				$this->error++;
				$this->error_variables["auction_quantity"] = "error";
			}			
			
			
			if ($this->session_variables["buy_now_only"]) {
				if (!$this->session_variables["auction_buy_now"] || !preg_match("/^[0-9]{1,10}.?[0-9]{0,2}$/", $this->session_variables["auction_buy_now"])) {
					//buy now only auction, but the buy now price was left at 0
					$this->error++;
					$this->error_variables['auction_buy_now'] = 'error';
				}
				$this->session_variables["auction_reserve"] = null;
				$this->session_variables["auction_minimum"] = null;
			} else {
				if (!$this->session_variables["auction_reserve"] || !preg_match("/^[0-9]{1,10}.?[0-9]{0,2}$/", $this->session_variables["auction_reserve"])) {
					settype($this->session_variables["auction_reserve"], "float");
					$this->session_variables["auction_reserve"] = 0.00;
				} else {
					settype($this->session_variables["auction_reserve"], "float");
				}

				if (!$this->session_variables["auction_buy_now"] || !preg_match("/^[0-9]{1,10}.?[0-9]{0,2}$/", $this->session_variables["auction_buy_now"])) {
					settype($this->session_variables["auction_buy_now"], "float");
					$this->session_variables["auction_buy_now"] = 0.00;
				} else {
					settype($this->session_variables["auction_buy_now"], "float");
				}

				if (!$this->session_variables["auction_minimum"] || !preg_match("/^[0-9]{1,10}.?[0-9]{0,2}$/", $this->session_variables["auction_minimum"])) {
					settype($this->session_variables["auction_minimum"], "float");
					$this->session_variables["auction_minimum"] = 0.00;
				} else {
					settype($this->session_variables["auction_minimum"], "float");
				}

				if (!$this->session_variables["auction_minimum"] || $this->session_variables["auction_minimum"] == 0.00) {
					$this->session_variables["auction_minimum"] = 0.01;
				} else if (strlen(trim($this->session_variables["auction_minimum"])) > 0) {
					if (!preg_match("/^[0-9]{1,10}.?[0-9]{0,2}$/", $this->session_variables["auction_minimum"])) {
						$this->session_variables["auction_minimum"] = 0.01;
					}
				}
			}
		}

		
		// If buy now show no errors unless buy now is 0.0
		if ($this->session_variables["buy_now_only"]) {
			$this->session_variables["auction_reserve"] = null;
			$this->session_variables["auction_minimum"] = null;

			if ($this->session_variables["auction_buy_now"] <= 0.0) {
				$this->error++;
				$this->error_variables["auction_buy_now"] = "error";
			}
		} else {
			if ($this->sell_type == 2) {
				$auction_type = $this->session_variables['auction_type'];
				
				//whether reserve and buy now are being used or not
				$use_reserve = ($this->session_variables['auction_reserve'] != 0.00);
				$use_buy_now = ($this->session_variables["auction_buy_now"] != 0.00);
				
				if ($auction_type == 1 || $auction_type == 2) {
					//Do normal checks...  min <= reserve <= buy now
					if ($use_reserve && $this->session_variables['auction_reserve'] < $this->session_variables["auction_minimum"]) {
						//reserve < min, not allowed
						$this->error++;
						$this->error_variables["auction_reserve"] = "error";
					}
					if ($use_reserve && $use_buy_now && $this->session_variables["auction_buy_now"] < $this->session_variables["auction_reserve"]) {
						//buy now < reserve, not allowed
						$this->error++;
						$this->error_variables["auction_buy_now"] = "error";
					}
					if ($use_buy_now && $this->session_variables["auction_buy_now"] < $this->session_variables["auction_minimum"]) {
						//buy now < min, not allowed
						$this->error++;
						$this->error_variables["auction_buy_now"] = "error";
					}
				} else if ($auction_type==3) {
					//reverse auction checks...  min is actually max.. so use:
					//buy now <= reserve <= max (aka auction_minimum)
					if ($use_reserve && $this->session_variables['auction_reserve'] > $this->session_variables['auction_minimum']) {
						//reserve > min, not allowed (in reverse auctions)
						$this->error++;
						$this->error_variables["auction_reserve"] = "error";
					}
					if ($use_reserve && $use_buy_now && $this->session_variables["auction_buy_now"] > $this->session_variables["auction_reserve"]) {
						//buy now > reserve, not allowed (in reverse auctions)
						$this->error++;
						$this->error_variables["auction_buy_now"] = "error";
					}
					if ($use_buy_now && $this->session_variables["auction_buy_now"] > $this->session_variables["auction_minimum"]) {
						//buy now > min, not allowed (in reverse auctions)
						$this->error++;
						$this->error_variables["auction_buy_now"] = "error";
					}
				}
			}
		}
		

		//payment type
		if ($this->fields->payment_types->is_required) {
			if (strlen(trim($this->session_variables["payment_options"])) == 0) {
				$this->error++;
				$this->error_variables["payment_options"] = "error";
			}
		}

		//Loop through all the optional fields
		if ( geoPC::is_ent() ) {
			for ($i=1;$i<21;$i++) {
				$fieldName = 'optional_field_'.$i;
				$field = $this->fields->$fieldName;
				if ($field->is_enabled) {
					//newline replace
					//FIXME: Should this check to see if "convert to newline" is checked before doing this?
					$this->session_variables[$fieldName] = preg_replace('/(\r\n|\n|\r)/', "<br />", $this->session_variables[$fieldName]);
					$useOther = (strpos($field->type_data, ':use_other')!==false);
					
					if ($useOther && strlen(trim($this->session_variables[$fieldName.'_other'])) > 0) {
						$this->session_variables[$fieldName] = $this->session_variables[$fieldName.'_other'];
					}
					if ($field->text_length > 0 && $field->field_type != 'date') {
						$this->session_variables[$fieldName] = geoString::substr($this->session_variables[$fieldName],0,$field->text_length);
					}
					if ($useOther || $field->field_type == 'text' || $field->field_type == 'textarea') {
						//it is text, "clean" the input
						$this->session_variables[$fieldName] = geoString::breakLongWords($this->session_variables[$fieldName],$this->db->get_site_setting('max_word_width'), " ");
						$this->session_variables[$fieldName] = geoFilter::replaceDisallowedHtml($this->session_variables[$fieldName],0);
						$this->session_variables[$fieldName] = $this->check_for_badwords($this->session_variables[$fieldName]);
					}
					if ($field->field_type == 'number' || $field->field_type == 'cost') {
						//trim it, it's a number
						$this->session_variables[$fieldName] = trim($this->session_variables[$fieldName]);
						if (strlen($this->session_variables[$fieldName])>0 && !preg_match('/^[0-9.,]+$/',$this->session_variables[$fieldName])) {
							//if it is a number field, (and does not add cost) it must be a positive number, NOT 0
							//echo "error in number only optional 1<br />\n";
							$this->error++;
							$this->error_variables[$fieldName] = "error_number";
						} else if ($field->field_type == 'cost' && $field->is_required && geoNumber::deformat($this->session_variables[$fieldName]) == 0) {
							//it is required and adds cost and they entered 0, silly peoples...
							$this->error++;
							$this->error_variables[$fieldName] = "error";
						} else if (strlen($this->session_variables[$fieldName]) > 0) {
							//deformat() removes any extra characters and puts the number in 'American' format, for storage
							$this->session_variables[$fieldName] = geoNumber::deformat($this->session_variables[$fieldName]);
						}
					}
					if ($field->field_type == 'date') {
						//convert it to cleaned date
						$this->session_variables[$fieldName] = geoCalendar::fromInput($this->session_variables[$fieldName]);
						if (strlen($this->session_variables[$fieldName])!==8) {
							//wrong length!
							$this->session_variables[$fieldName] = '';
						}
					}
					if ($field->is_required) {
						if (strlen(trim($this->session_variables[$fieldName]))== 0) {
							$this->error++;
							$this->error_variables[$fieldName] = "error";
						}
					}
				}
			}//end optional fields for loop
		}
		if ($this->fields->url_link_1->is_enabled) {
			if ($this->fields->url_link_1->text_length > 0) {
				$this->session_variables["url_link_1"] = geoString::substr($this->session_variables["url_link_1"],0,$this->fields->url_link_1->text_length);
			}
			$this->session_variables["url_link_1"] = geoFilter::replaceDisallowedHtml($this->session_variables["url_link_1"],1);
			$this->session_variables["url_link_1"] = $this->check_for_badwords($this->session_variables["url_link_1"]);

			if ($this->fields->url_link_1->is_required) {
				if (strlen(trim($this->session_variables["url_link_1"])) == 0) {
					if ($edit && !$this->fields->url_link_1->can_edit) {
						//Oops!  Field is required, this is an edit, but the field 
						//is not editable, and the field is blank!
						
						//Don't throw an error in this case, or it will lock up.
					} else {
						$this->error++;
						$this->error_variables["url_link_1"] = "error";
					}
				}
			}
		}

		if ($this->fields->url_link_2->is_enabled) {
			if ($this->fields->url_link_2->text_length > 0) {
				$this->session_variables["url_link_2"] = geoString::substr($this->session_variables["url_link_2"],0,$this->fields->url_link_2->text_length);
			}
			$this->session_variables["url_link_2"] = geoFilter::replaceDisallowedHtml($this->session_variables["url_link_2"],1);
			$this->session_variables["url_link_2"] = $this->check_for_badwords($this->session_variables["url_link_2"]);

			if ($this->fields->url_link_2->is_required) {
				if (strlen(trim($this->session_variables["url_link_2"])) == 0) {
					if ($edit && !$this->fields->url_link_2->can_edit) {
						//Oops!  Field is required, this is an edit, but the field 
						//is not editable, and the field is blank!
						
						//Don't throw an error in this case, or it will lock up.
					} else {
						$this->error++;
						$this->error_variables["url_link_2"] = "error";
					}
				}
			}
		}

		if ($this->fields->url_link_3->is_enabled) {
			if ($this->fields->url_link_3->text_length > 0) {
				$this->session_variables["url_link_3"] = geoString::substr($this->session_variables["url_link_3"],0,$this->fields->url_link_3->text_length);
			}
			$this->session_variables["url_link_3"] = geoFilter::replaceDisallowedHtml($this->session_variables["url_link_3"],1);
			$this->session_variables["url_link_3"] = $this->check_for_badwords($this->session_variables["url_link_3"]);

			if ($this->fields->url_link_3->is_required) {
				if (strlen(trim($this->session_variables["url_link_3"])) == 0) {
					if ($edit && !$this->fields->url_link_3->can_edit) {
						//Oops!  Field is required, this is an edit, but the field 
						//is not editable, and the field is blank!
						
						//Don't throw an error in this case, or it will lock up.
					} else {
						$this->error++;
						$this->error_variables["url_link_3"] = "error";
					}
				}
			}
		}
		
		if ($this->fields->email->is_enabled) {
			//check that it is valid e-mail
			if ($this->fields->email->is_required || strlen(trim($this->session_variables['email_option'])) > 0 ) {
				if (!geoString::isEmail($this->session_variables['email_option'])) {
					$this->error_variables["email_option"] ="error1";
					$this->error++;
				}
			}
		} else {
			//set it to seller's e-mail address - seller attached to listing, not current seller since current seller
			//could be admin user
			if ($seller) {
				$this->session_variables['email_option'] = geoString::fromDB($seller->email);
			}
		}

		if ($this->sell_type == 1 && $this->fields->price->is_enabled) {
			if ($this->userid) {
				$sql = "select * from ".$this->user_groups_price_plans_table." where id = ".$this->userid;
				$plan_result = $this->db->Execute($sql);
				if (!$plan_result)
				{
					//echo $sql."<br />\n";
					return false;
				}
				elseif ($plan_result->RecordCount() == 1)
				{
					$show_price_plan_id = $plan_result->FetchNextObject();


					//echo $this->session_variables["price"]." is \$this->session_variables[price]<bR>\n";
					$overriding_category = 0;
					if ($category_id)
					{
						$category_next = $category_id;
						do
						{
							$sql = "select category_id,parent_id from ".$this->categories_table."
								where category_id = ".$category_next;
							$category_result =  $this->db->Execute($sql);
							//echo $sql." is the query<br>\n";
							if (!$category_result)
							{
								//echo $sql." is the query<br>\n";
								$this->error_message = urldecode($this->messages[3501]);
								return false;
							}
							elseif ($category_result->RecordCount() == 1)
							{
								$show_category = $category_result->FetchNextObject();
								$sql = "select * from ".$this->price_plans_categories_table."
									where category_id = ".$category_next." and price_plan_id = ".$show_price_plan_id->PRICE_PLAN_ID;
								$category_price_plan_result =  $this->db->Execute($sql);
								//echo $sql." is the query<br>\n";
								if ($category_price_plan_result->RecordCount() == 1)
								{
									$overriding_category = $category_next;
									$show_category_price_plan = $category_price_plan_result->FetchNextObject();
								}
								$category_next = $show_category->PARENT_ID;
							}
							else
							{
								return false;
							}

						} while (($show_category->PARENT_ID != 0 ) && ($overriding_category== 0));

					}
					if ( $overriding_category != 0 )
					{
						$show_price_plan_category = $show_category_price_plan;
					}
					else
					{
						$show_price_plan_category = false;
					}

					//check charge_per_ad_type in pp_categories table
					$sql = "select charge_per_ad_type from ".$this->db->geoTables->price_plans_categories_table." where category_id = ".$category_id." and price_plan_id = ".$show_price_plan_id->PRICE_PLAN_ID;
					$price_plan_category_result = $this->db->Execute($sql);
					if($price_plan_category_result && $price_plan_category_result->RecordCount() == 1) {
						$show_price_plan_category = $price_plan_category_result->FetchNextObject();
					} else {
						$show_price_plan_category = false;
					}

					$sql = "select type_of_billing, charge_per_ad_type from ".$this->price_plans_table." where price_plan_id = ".$show_price_plan_id->PRICE_PLAN_ID;
					$price_plan_result = $this->db->Execute($sql);

					if (!$price_plan_result)
					{
						//echo $sql."<br />\n";
						return false;
					}
					elseif ($price_plan_result->RecordCount() == 1)
					{
						$show_price_plan = $price_plan_result->FetchNextObject();

						if ($show_price_plan_category)
						{
							$charge_per_ad_type = $show_price_plan_category->CHARGE_PER_AD_TYPE;
						}
						else
						{
							$charge_per_ad_type = $show_price_plan->CHARGE_PER_AD_TYPE;
						}

						if ($show_price_plan->TYPE_OF_BILLING == 1 && ($show_price_plan->CHARGE_PER_AD_TYPE == 1 || ($show_price_plan_category !== false && $charge_per_ad_type == 1)))
						{
							//FIXME:  Shouldn't this use geoNumber::deformat()?
							$this->session_variables["price"] = trim(str_replace(",","",$this->session_variables["price"]));

							if (!preg_match("/^[0-9]{1,10}.?[0-9]{0,2}$/", $this->session_variables["price"])) {
								$this->error++;
								$this->error_variables["price"] = "error";
							}
						} elseif ($this->fields->price->is_required && strlen($this->session_variables["price"]) == 0) {
							$this->error++;
							$this->error_variables["price"] = "error";
						}
					}
				}
			}
			//Note:  price is put through geoNumber::deformat() in get_form_variables()
			if ($this->fields->price->text_length > 0) {
				$this->session_variables["price"] = geoString::substr($this->session_variables["price"],0,$this->fields->price->text_length);
			}
			if ($this->fields->price->is_required && !strlen($this->session_variables["price"])) {
				//error in price - was not entered or entered as 0
				$this->error++;
				$this->error_variables["price"] = "error";
			}
			if($this->db->get_site_setting('price_minimum_limit') && $this->session_variables['price'] && $this->session_variables['price'] < $this->db->get_site_setting('price_minimum_limit')) {
				//price does not meet the minimum limit
				//NOTE: we only check this if price != 0, since the block above this one checks price's "required" switch
				$this->error++;
				$this->error_variables['price'] = $this->error_variables['price_minimum'] = 'error';
			}
		}
		if (isset($this->session_variables['start_time']) && is_array($this->session_variables['start_time'])) {
			$this->session_variables["start_time"] = $this->get_time($this->session_variables["start_time"]["start_hour"],$this->session_variables["start_time"]["start_minute"],$this->session_variables["start_time"]["start_month"],$this->session_variables["start_time"]["start_day"],$this->session_variables["start_time"]["start_year"]);
		}
		if (!isset($this->session_variables["start_time"]) || (!$edit && $this->session_variables["start_time"] < $current_time)) {
			$this->session_variables["start_time"] = $current_time;
		}
		
		if (isset($this->session_variables['end_time']) && is_array($this->session_variables['end_time'])){
			$this->session_variables["end_time"] = $this->get_time($this->session_variables["end_time"]["end_hour"],$this->session_variables["end_time"]["end_minute"],$this->session_variables["end_time"]["end_month"],$this->session_variables["end_time"]["end_day"],$this->session_variables["end_time"]["end_year"]);
			if($this->session_variables['end_time'] <= $this->session_variables['start_time']) {
				//end time is before start time
				$this->error++;
				$this->error_variables['end_time'] = 'error';	
			}
		}
		
		
		trigger_error('DEBUG SITE_CLASS: '.$this->session_variables["start_time"]." before start time");
		trigger_error('DEBUG SITE_CLASS: '. $this->session_variables["end_time"]." before end time");
		trigger_error('DEBUG SITE_CLASS: '. $this->session_variables["classified_length"]." before auction length");
		trigger_error('DEBUG SITE_CLASS: '. $current_time." is current_time 2");

		if ($this->sell_type == 2) {
			if(!$live)
			{
				if(!$this->db->get_site_setting('user_set_auction_start_times')) {
					//not necessarily an error state, but not setting start time manually, so set it to 0 for sanity
					$this->session_variables['start_time'] = 0;
				} elseif($this->session_variables['start_time'] < $current_time) {
					//explicit start time is earlier than now. move it up to present
					$this->session_variables['start_time'] = $current_time;
				}
				if($this->session_variables['end_mode'] == 1) {
					//user has tried to set an explicit endtime
					$this->session_variables['classified_length'] = 0; //set "duration" to 0 to prevent confusion later
					if(!$this->db->get_site_setting('user_set_auction_end_times')) {
						//explicit endtime disallowed in admin
						$this->error++;
						$this->error_variables['classified_length'] = 'error';
					}
					if($this->session_variables['end_time'] <= $current_time) {
						//ends now or earlier. error.
						$this->error++;
						$this->error_variables['classified_length'] = 'error';
					}
					if($this->session_variables['start_time'] > 0 && $this->session_variables['end_time'] <= $this->session_variables['start_time']) {
						//has an explicit start time, but ends before it begins
						$this->error++;
						$this->error_variables['classified_length'] = 'error';
					}
				} else {
					//using a "duration"
					$this->session_variables['end_time'] = 0; //set end_time to 0 to prevent confusion later
					if(!is_numeric($this->session_variables['classified_length'])) {
						//duration not set
						//note: we use is_numeric here because 0 is a valid duration (meaning an Unlimited listing)
						$this->error++;
						$this->error_variables['duration'] = 'error';
					}
				}	
			}
		}

		if ($this->fields->phone_1->is_enabled)
		{
			$this->session_variables["phone_1_option"] = trim(str_replace(",","",$this->session_variables["phone_1_option"]));
			$this->session_variables["phone_1_option"] = geoString::substr($this->session_variables["phone_1_option"],0,$this->fields->phone_1->text_length);
		}

		if ($this->fields->phone_2->is_enabled)
		{
			$this->session_variables["phone_2_option"] = trim(str_replace(",","",$this->session_variables["phone_2_option"]));
			$this->session_variables["phone_2_option"] = geoString::substr($this->session_variables["phone_2_option"],0,$this->fields->phone_2->text_length);
		}

		if ($this->fields->fax->is_enabled)
		{
			$this->session_variables["fax_option"] = trim(str_replace(",","",$this->session_variables["fax_option"]));
			$this->session_variables["fax_option"] = geoString::substr($this->session_variables["fax_option"],0,$this->fields->fax->text_length);
		}
		if ($this->fields->description->is_enabled) {
			//clean description

			//remove any bad HTML *before* checking field length, so as not to count the characters being taken away
			$this->session_variables["description"] = geoFilter::replaceDisallowedHtml($this->session_variables["description"]);
			
			if ($this->fields->description->text_length) {
				$this->session_variables["description"] = geoString::substr($this->session_variables["description"],0,$this->fields->description->text_length);
			}
			
			//longwords, badwords, and wrapping happen *AFTER* the length check, because they potentially can silently add characters
			$this->session_variables["description"] = geoString::breakLongWords($this->session_variables["description"],$this->db->get_site_setting('max_word_width'), " ");
			$this->session_variables["description"] = $this->check_for_badwords($this->session_variables["description"]);
			if ($this->ad_configuration_data['textarea_wrap']) {
				$this->session_variables["description"] = preg_replace('/(\r\n|\n|\r)/', "<br />", $this->session_variables["description"]);	
			}
						
			if ($this->fields->description->is_required) {
				if (strlen(trim($this->session_variables["description"])) == 0) {
					if ($edit && !$this->fields->description->can_edit) {
						//Oops!  Field is required, this is an edit, but the field 
						//is not editable, and the field is blank!
						
						//Don't throw an error in this case, or it will lock up.
					} else {
						$this->error++;
						$this->error_variables["description"] = "error";
					}
				}
			}
		}

		if ($this->fields->address->is_enabled) {
			$this->session_variables["address"] = geoFilter::replaceDisallowedHtml($this->session_variables["address"],1);
			$this->session_variables["address"] = $this->check_for_badwords($this->session_variables["address"]);
		}
		
		if ($this->fields->city->is_enabled) {
			$this->session_variables["city"] = geoFilter::replaceDisallowedHtml($this->session_variables["city"],1);
			$this->session_variables["city"] = $this->check_for_badwords($this->session_variables["city"]);
		}


		trigger_error('DEBUG SITE_CLASS: '.$this->error." is the error count");
		reset($this->error_variables);
		foreach ($this->error_variables as $key => $value) {
			trigger_error('DEBUG SITE_CLASS: '. $key." is the key to ".$value."");
		}

		trigger_error('DEBUG SITE_CLASS: '."END OF CLASSIFIED_DETAIL_CHECK");
		return ($this->error == 0);
	}
	
	function display_basic_duration_dropdown($return)
	{
		$db = DataAccess::getInstance();
		//check for category specific dropdown lengths first
		
		$current_category = $this->terminal_category;
		do {
			$sql = "select * from ".$this->price_plan_lengths_table." where category_id = ".$current_category."
				and price_plan_id = 0 order by length_of_ad asc";
			$category_duration_result = $db->Execute($sql);
			if (!$category_duration_result) {
				return false;
			} elseif ($category_duration_result->RecordCount() == 0 && $current_category) {
				//get parent category
				$sql = "select parent_id from ".$this->categories_table." where category_id = ".$current_category;
				$parent_result = $db->Execute($sql);
				if (!$parent_result) {
					return false;
				}
				$show_parent = $parent_result->FetchNextObject();
				$current_category = $show_parent->PARENT_ID;
			}
		} while (($current_category != 0) && ($category_duration_result->RecordCount() == 0));
		
		$options = array();
		
		if ($category_duration_result->RecordCount() > 0) {
			//category-specific durations
			for($i = 0; $show_durations = $category_duration_result->FetchRow(); $i++) {
				$options[$i]['numerical_length'] = $show_durations["length_of_ad"];
				$options[$i]['selected'] = ($this->session_variables["classified_length"] == $show_durations["length_of_ad"]) ? true : false;
				$options[$i]['display_length'] = $show_durations["display_length_of_ad"];
			}
		} else {
			//no category-specific durations: use site-wide
			$sql = "select * from ".$this->choices_table." where type_of_choice = 1 and language_id=".$this->language_id." order by numeric_value";
			$duration_result = $db->Execute($sql);
			if (!$duration_result) {
				//no results for this language -- look for a fallback
				$sql = "select * from ".$this->choices_table." where type_of_choice = 1 order by numeric_value";
				$duration_result = $db->Execute($sql);
				if (!$duration_result) {
					//no results at all -- something's wrong
					return false;
				}
			}
			$options = array();
			for($i = 0; $show_durations = $duration_result->FetchRow(); $i++) {
				$options[$i]['numerical_length'] = $show_durations["numeric_value"];
				$options[$i]['selected'] = ($this->session_variables["classified_length"] == $show_durations["numeric_value"]) ? true : false;
				$options[$i]['display_length'] = $show_durations["display_value"];
			}
		}
		
		$tpl = new geoTemplate('system','order_items');
		$tpl->assign('durations', $options);
		$html = $tpl->fetch('shared/duration_choices_basic.tpl');
		if ($return) {
			return $html;
		}
		$this->body .= $html;
		return true;
	}
	
	function get_currency_info(){
		//only get the info if its not already gotten.
		if (!isset($this->session_variables['precurrency'], $this->session_variables['postcurrency'])){
			$where_clause = '';
			if ($this->session_variables['currency_type']>0){
				//if the currency type is not 0, then use the already set currency type.  Otherwise just
				//get the currency that is first ordered by display_order.
				$where_clause = ' WHERE type_id = '.$this->session_variables['currency_type'];
			}
			$sql = 'SELECT precurrency, postcurrency FROM '.$this->currency_types_table.$where_clause.' ORDER BY display_order';
			$result = $this->db->Execute($sql);
			$show = $result->FetchRow();
			$this->session_variables['precurrency']=$show['precurrency'];
			$this->session_variables['postcurrency']=$show['postcurrency'];
		}
		return true;
	}
	
	function display_charge_by_duration_dropdown($return = false)
	{
		$db = DataAccess::getInstance();
		
		//if there are category-specific prices, use them
		$cat_id = $this->price_plan['category_id'] ? $this->price_plan['category_id'] : '0';
		
		$sql = "select * from ".$this->price_plan_lengths_table." where	price_plan_id = ".$this->users_price_plan." and category_id = ".$cat_id." order by length_of_ad asc";
		
		$length_result = $db->Execute($sql);
		if (!$length_result) {
			//no result -- fallback on basic dropdown
			$html = $this->display_basic_duration_dropdown(true);
		} else {
			$options = array();
			for($i = 0; $show_durations = $length_result->FetchRow(); $i++) {
				$options[$i]['numerical_length'] = $show_durations["length_of_ad"];
				$options[$i]['selected'] = ($this->session_variables["classified_length"] == $show_durations["length_of_ad"]) ? true : false;
				$options[$i]['display_length'] = $show_durations["display_length_of_ad"];
				$options[$i]['display_amount'] = geoMaster::is('site_fees') ? geoString::displayPrice($show_durations['length_charge'],$db->get_site_setting('precurrency'),$db->get_site_setting('postcurrency'), 'cart') : '';
			}
			$tpl = new geoTemplate('system','order_items');
			$tpl->assign('durations', $options);
			$html = $tpl->fetch('shared/duration_choices_charged.tpl');
		}
		if ($return) {
			return $html;
		}
		$this->body .= $html;
		return true;
	}
	
	public function display_subscription_duration_dropdown($return = false)
	{
		$db = DataAccess::getInstance();
		
		//if there are category-specific prices, use them
		$cat_id = $this->price_plan['category_id'] ? $this->price_plan['category_id'] : '0';
				
		$planItem = geoPlanItem::getPlanItem('classified_recurring', $this->price_plan['price_plan_id'], $cat_id);
		if(!$planItem || !$planItem->isEnabled()) {
			//this category isn't actually in use for recurring classifieds
			//probably something like the default combined view before a category has been selected, when only certain categories are active for recurring
			//need to show something here so it's not just a blank dropdown, though, so go ahead and send up the basic duration choices
			
			//easy way to do that: do absolutely nothing here so that $length_result doesn't get set!
		} else {
		
			$sql = "select * from ".geoTables::listing_subscription_lengths." where price_plan = ? and category = ? order by period asc";
			$length_result = $db->Execute($sql, array($this->price_plan['price_plan_id'], $cat_id));
		}
		
		if (!$length_result || $length_result->RecordCount() == 0) {
			//no result -- fallback on basic dropdown
			$html = $this->display_basic_duration_dropdown(true);
		} else {
			$options = array();
			for($i = 0; $show_durations = $length_result->FetchRow(); $i++) {
				$options[$i]['numerical_length'] = $show_durations["period"];
				$options[$i]['selected'] = ($this->session_variables["classified_length"] == $show_durations["period"]) ? true : false;
				$options[$i]['display_length'] = $show_durations["period_display"];
				$options[$i]['display_amount'] = geoMaster::is('site_fees') ? geoString::displayPrice($show_durations['price'],$db->get_site_setting('precurrency'),$db->get_site_setting('postcurrency'), 'cart') : '';
			}
			
			$tpl = new geoTemplate('system','order_items');
			$tpl->assign('durations', $options);
			$html = $tpl->fetch('shared/duration_choices_subscription.tpl');
		}
		if ($return) {
			return $html;
		}
		$this->body .= $html;
		return true;
	}
	
	function DateAdd ($interval, $date,$quantity)
	{
		//$difference =  $date2 - $date1;
		switch ($interval)
		{
			case "w":
				$timevalue  = 604800;
				break;
			case "d":
				$timevalue  = 86400;
				break;
			case "h":
				$timevalue = 3600;
				break;
			case "m":
				$timevalue  = 60;
				break;
		}
		
		$returnvalue = $date + ($quantity * $timevalue);
		return $returnvalue;
	}
	
	public function display_classified($id, $return=false, $session_vars = array())
	{
		$db = DataAccess::getInstance();
		require_once CLASSES_DIR . 'browse_display_ad.php';
		$browse = new Display_ad (1, $id);
		
		$browse->offsite_videos = $this->offsite_videos;
		$browse->offsite_videos_from_db = $this->offsite_videos_from_db;
		
		return $browse->display_classified($id, $return, 'preview_only', true, $session_vars);
	}
	function format_phone_data($phone_number=0)
	{
		return geoNumber::phoneFormat($phone_number);
	}
	function delete_current_category_questions()
	{
		$sql = "delete from ".$this->classified_extra_table." where
			classified_id = ".$this->classified_id;
		$delete_extra_result = $this->db->Execute($sql);
		//$this->body .=$sql."<br />\n";
		if (!$delete_extra_result)
		{
			//$this->body .=$sql." is the query<br />\n";
			$this->error_message = urldecode($this->messages[57]);
			return false;
		}
		return true;
	}
	
	function get_ads_extra_values($classified_id) {
		if(!$classified_id) return false;
		$sql = "select * from ".$this->classified_extra_table." where classified_id = ".$classified_id." and checkbox !=1 order by display_order asc";
		$result = $this->db->Execute($sql);
		if ($this->debug_ad_display) echo $sql."<br/>\n";
		if (!$result)
		{
			if ($this->debug_ad_display) echo $sql."<br/>\n";
			$this->error_message = geoString::fromDB($this->messages[81]);
			return false;
		}
		elseif ($result->RecordCount() <= 0 ) return false;
		$name = "\n<ul class='extraQuestionName' id='extraQuestionName'>\n";
		$value = "\n<ul class='extraQuestionValue' id='extraQuestionValue'>\n";
		while ($show_special = $result->FetchRow())
		{
			if ($show_special["checkbox"] == 1)
				$name .= "\t<li>&nbsp;</li>\n";
			else
				$name .= "\t<li>".geoString::fromDB($show_special["name"])."</li>\n";
			if ((strlen(trim($show_special["url_icon"])) > 0) && ($show_special["checkbox"] == 2))
			{
				if (stristr(stripslashes($show_special["value"]), urlencode("http://")))
					$url_link = "<a href=\"".geoString::fromDB($show_special["value"])."\" target=\"_blank\"><img src=\"".$show_special["url_icon"]."\" border=\"0\"></a>";
				else
					$url_link = "<a href=\"http://".geoString::fromDB($show_special["value"])."\" target=\"_blank\"><img src=\"".$show_special["url_icon"]."\" border=\"0\"></a>";
				$value .= "\t<li>".$url_link."</li>\n";
			}
			elseif ($show_special["checkbox"] == 2)
			{
				if (stristr(stripslashes($show_special["value"]), urlencode("http://")))
					$url_current_line = "<a href=\"".geoString::fromDB($show_special["value"])."\" target=\"_blank\" class=\"display_ad_extra_question_value\">".geoString::fromDB($show_special["value"])."</a>";
				else
					$url_current_line = "<a href=\"http://".geoString::fromDB($show_special["value"])."\" target=\"_blank\" class=\"display_ad_extra_question_value\">".geoString::fromDB($show_special["value"])."</a>";
				//echo $current_line."<br/>\n";
				$value .= "\t<li>".$url_current_line."</li>\n";
			}
			else
			{
				$value .= "\t<li>".geoString::fromDB($show_special["value"])."</li>\n";
			}
		}
		$name .= "</ul>\n";
		$value .= "</ul>\n";
		$question_block["names"] = $name;
		$question_block["values"] = $value;
		return $question_block;
	}
	function get_ads_extra_checkboxes($classified_id) {
		if(!$classified_id) return false;
		$sql = "select * from ".$this->classified_extra_table." where classified_id = ".$classified_id." and checkbox = 1 order by display_order asc";
		$result = $this->db->Execute($sql);
		if ($this->debug_ad_display) echo $sql."<br/>\n";
		if (!$result) {
			if ($this->debug_ad_display) echo $sql."<br/>\n";
			$this->error_message = geoString::fromDB($this->messages[81]);
			return false;
		}
		elseif ($result->RecordCount() <= 0 ) return false;
		$columns = array();
		if($this->configuration_data['checkbox_columns']>0) {
			$width = (100/$this->configuration_data['checkbox_columns']);
			for($i=0;$i<$this->configuration_data['checkbox_columns'];$i++) {
				$columns[$i] = "\n<ul style='width:$width%;' class='extraCheckboxes'>\n";
			}
		}else{
			$columns[0] = "\n<ul style='width:100%;' class='extraCheckboxes'>\n";
		}
		$counter = 0;
		while ($showResult = $result->FetchRow())
		{
			$key = ($this->configuration_data['checkbox_columns']) ? ($counter%$this->configuration_data['checkbox_columns']) : 0;
			$columns[$key] .= "\t<li>".geoString::fromDB($showResult["name"])."</li>\n";
			$counter++;
		}
		for($i=0;$i<count($columns);$i++) {
			$question_block .= $columns[$i]."</ul>\n";
		}
		return $question_block;
	}

	 //replaced this function with a smarty version in geoHTML, but using this as a wrapper, so as not to break existing content (yet!)
	public function get_date_select($year_name,$month_name,$day_name,$hour_name,$minute_name,
	$timestamp = 0,$year_value=0,$month_value=0,$day_value=0,$hour_value=0,$minute_value=0,$doDurationId=false)
	{
		$fields = array(
			'year' => $year_name,
			'month' => $month_name,
			'day' => $day_name,
			'hour' => $hour_name,
			'minute' => $minute_name
		);
		$labels = array(
			'year' => geoString::fromDB($this->messages[103060]),
			'month' => geoString::fromDB($this->messages[103059]),
			'day' => geoString::fromDB($this->messages[103058]),
			'hour' => geoString::fromDB($this->messages[103061]),
			'minute' => geoString::fromDB($this->messages[103062])
		);
		$values = array(
			'year' => $year_value,
			'month' => $month_value,
			'day' => $day_value,
			'hour' => $hour_value,
			'minute' => $minute_value
		);		
		
		$html = geoHTML::dateSelect($fields, $labels, $timestamp, $values, $doDurationId); 
				
		if ($this->return_value) {
			return $html;
		}
		$this->body .= $html;

	} //end of function get_fine_date_select
}
