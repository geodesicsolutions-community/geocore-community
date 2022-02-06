<?php
//Admin_Feedback.class.php


if(!defined('FEEDBACK_ICON_MAX_VALUE')) {
	define('FEEDBACK_ICON_MAX_VALUE', 2147483647);
}

class Admin_Feedback {
	private $_db;
	private $_id;

	public function __construct() {
		$this->_db = DataAccess::getInstance();
	}

	/**
	 * Get feedback ID given various data.
	 *
	 * If the feedback ID is already set, this function simply returns that ID;
	 * otherwise, $auctionId and one other parameter must be passed to the
	 * function. This function does NOT set the feedback ID, it simply returns
	 * it.
	 *
	 * @param int $auctionId
	 * @param int $ratedId
	 * @param int $raterId
	 * @return int
	 */
	function getId($auctionId = null, $ratedId=null, $raterId=null) {
		if(null != $auctionId) {
			if(null != $ratedId) {
				return $this->getIdByRated($auctionId, $ratedId);
			} else if (null != $raterId) {
				return $this->getIdByRater($auctionId, $raterId);
			} else {
				trigger_error('ERROR FEEDBACK: Cannot determine ID based on
					auction ID alone');
			}
		} else if(isset($this->_id) && !is_null($this->_id)) {
			return $this->_id;
		} else {
			trigger_error("ERROR FEEDBACK: Not enough information to determine
				feedback ID");
			return false;
		}
	}

	/**
	 * Get feedback ID based on auction ID and rated user ID
	 *
	 * @param int $auctionId
	 * @param int $ratedId
	 * @return int
	 */
	function getIdByRated($auctionId, $ratedId) {
		$id = $this->_db->GetOne('select id from '
			.$this->_db->geoTables->auctions_feedbacks_table.' where
			auction_id = ? and rated_user_id = ?', array($auctionId, $ratedId));
		trigger_error('DEBUG: ID = '.$id);
		return $id;
	}

	/**
	 * Get feedback ID based on auction ID and rater user ID
	 *
	 * @param int $auctionId
	 * @param int $raterId
	 * @return int
	 */
	function getIdByRater($auctionId, $raterId) {
		$id = $this->_db->GetOne('select id from '
			.$this->_db->geoTables->auctions_feedbacks_table.' where
			auction_id = ? and rater_user_id = ?', array($auctionId, $raterId));
		trigger_error('DEBUG: ID = '.$id);
		return $id;
	}

	/**
	 * Set feedback ID
	 *
	 * @param int $id
	 */
	function setId($id) {
		$this->_id = $id;
	}

	/**
	 * Delete Feedback
	 *
	 * Feedback ID must be set or passed as a parameter.
	 *
	 * @param int $id
	 */
	function delete_Feedback($id = null) {
		if(null === $id) {
			$id = $this->getId();
		}

		$id = $this->_db->GetOne('delete from '
			.$this->_db->geoTables->auctions_feedbacks_table.' where
			id = ?', array($id));

	}

	/**
	 * Update feedback info
	 *
	 * Pulls feedback info from $_POST['d'];
	 *
	 * @param int $id
	 */
	function update_UserFeedback($id) {
		// Get the DB object
		$db = true;
		include '../get_common_vars.php';

		$feedback = $_POST['d'];

		$query = "select * from ".$db->geoTables->auctions_feedbacks_table." where id = ?";
		$result = $db->Execute($query, array($id));
		if(!$result) {
			return false;
		}

		$previous = $result->FetchRow();

		$query = "update ".$db->geoTables->auctions_feedbacks_table." set feedback = ? where id = ?";
		$result = $db->Execute($query, array($_POST['d']['feedback'], $id));
		$query = "update ".$db->geoTables->auctions_feedbacks_table." set rate = ? where id = ?";
		$result2 = $db->Execute($query, array($_POST['d']['rate'], $id));

		if(!$result || !$result2) {
			trigger_error('ERROR: Couldn\t update user feedback');
			return false;
		}

		// If rates match we are finished
		if($previous['rate'] == $feedback["rate"]) {
			return true;
		}

		// Fix the count up in the userdata table
		$query = "select rated_user_id from ".$db->geoTables->auctions_feedbacks_table." where id = ?";
		$result = $db->Execute($query, array($id));

		if(!$result) {
			return false;
		}

		$rated_user_result = $result->FetchRow();
		$rated_id = $rated_user_result['rated_user_id'];

		$query = "select feedback_positive_count,feedback_score from ".$db->geoTables->userdata_table." where id = ?";
		$result = $db->Execute($query, array($rated_id));
		if(!$result) {
			trigger_error('ERROR: Couldn\'t load feedback score');
			return false;
		}

		$userdata = $result->FetchRow();

		if($feedback["rate"] == 1 && $previous['rate'] != 1) {
			$query = "update ".$db->geoTables->userdata_table." set feedback_positive_result = "
                .($userdata['feedback_positive_count']+1);
			if($previous['rate'] == 0) {
				// If the previous rate was neutral
				$query .= ", feedback_score = ".($userdata['feedback_score']+1);
			} else {
				// If the previous rate was negative
				$query .= ", feedback_score = ".($userdata['feedback_score']+2);
			}
		} else if($feedback["rate"] == 0 && $previous['rate'] != 0)	{
			$query = "update ".$db->geoTables->userdata_table." set ";
			if($previous['rate'] == 1) {
				// If the previous rate was positive
				$query .= "feedback_score = ".($userdata['feedback_score']-1);
			} else {
				// If the previous rate was negative
				$query .= "feedback_score = ".($userdata['feedback_score']+1);
			}
		} elseif($feedback["rate"] == -1 && $previous['rate'] != -1) {
			$query = "update ".$db->geoTables->userdata_table." set ";
			if($previous['rate'] == 1) {
				// If the previous rate was positive
				$query .= "feedback_score = ".($userdata['feedback_score']-2);
			} else {
				// If the previous rate was neutral
				$query .= "feedback_score = ".($userdata['feedback_score']-1);
			}
		}
		$query .= " WHERE id = ?";
		$result = $db->Execute($query, array($rated_id));
		if(!$result) {
			trigger_error("ERROR: Couldn't update feedback score");
			return false;
		}

		return true;
	}

	/**
	 * Update feedback settings
	 *
	 * Uses auto_save functionality of geoAdmin class
	 *
	 * @return bool
	 */
	function update_GlobalSettings() {
		if (PHP5_DIR)
			$menu_loader = geoAdmin::getInstance();
		else
			$menu_loader =& geoAdmin::getInstance();

		// Get the DB object
		$db = true;
		include '../get_common_vars.php';

		$result = $db->set_site_setting('number_of_feedbacks_to_display', $_POST['auto_save']['number_of_feedbacks_to_display']);
		if(!$result) {
			trigger_error('ERROR: Couldn\'t update pagination');
			$menu_loader->userError('Settings NOT Saved.');
			return false;
		}
		$menu_loader->userSuccess('Settings Saved.');
		return true;
	}

	/**
	 * Show info for a specific feedback
	 *
	 * @param int $id
	 */
	function display_UserFeedback($id = null) {
		// Get the DB object
		$db = true;
		include '../get_common_vars.php';

		if(null === $id) {
			$id = $this->getId();
		}

		$html .= "<table cellpadding=3 cellspacing=0 border=0 align=center width=\"100%\">\n";
		$html .= "<tr class=row_color_red>\n\t<td colspan=2 class=medium_font_light>\n\tEdit the feedback for the user in the box below to how you like it and then hit Save.</font>\n\t</td>\n</tr>\n";

		// Output the feedback
		$query = "select * from ".$db->geoTables->auctions_feedbacks_table." where id = ?";

		$result = $db->Execute($query, array($id));

		if(false === $result) {
			trigger_error('ERROR: Couldn\'t update user feedback');
		}
		$show = $result->FetchNextObject();
//			if (!$this->admin_demo())
		$html .= "<form action='index.php?mc=feedback&page=feedback_show&userId=".$show->RATED_USER_ID."&feedbackId=".$id."' method=post>\n";
		$html .= "<tr align=center>\n\t<td>\n\t";
		$html .= "<tr align=center>\n\t<td>\n\t";
		$html .= "<input type=radio name=d[rate] ";
		if ($show->RATE == -1) $html .= "checked";
		$html .= " value=-1>Negative<br>\n\t\t
			<input type=radio name=d[rate] ";
		if ($show->RATE == 0) $html .= "checked";
		$html .= " value=0>Neutral<br>\n\t\t
			<input type=radio name=d[rate] ";
		if ($show->RATE == 1) $html .= "checked";
		$html .= " value=1>Positive\n\t\t</td>\n\t</tr>\n\t";
		$html .= "<tr align=center>\n\t<td>\n\t<textarea name=d[feedback] rows=10 cols=30>".geoString::specialChars(geoString::fromDB($show->FEEDBACK))."</textarea></td>\n</tr>\n";
//			if (!$this->admin_demo())
		$html .= "<tr align=center>\n\t<td>\n\t<input type=submit value=\"Save\" name=save_feedback class=mini_button>\n\t</td>\n</tr>\n";
		$html .= "</form></table>";

		geoAdmin::display_page($html, 'Edit user\'s feedback', 'admin_images/menu_users.gif');
	}

	/**
	 * Displays feedback increment settings (feedback stars)
	 *
	 * Uses auto_save functionality of geoAdmin class
	 *
	 * @return bool
	 */
	function display_IncrementSettings()
	{
		$db = DataAccess::getInstance();
		$admin = geoAdmin::getInstance();

		$html = '';
		$html .= geoAdmin::m();
		$sql_query = "select * from ".geoTables::auctions_feedback_icons_table." where begin > 0 order by icon_num asc";

		$icon_result = $db->Execute($sql_query);
		if (!$icon_result)
		{
			trigger_error("ERROR: Couldn't get feedback icons data.");
			return false;
		}

			$html .= "<form action=\"index.php?mc=feedback&page=IncrementSettings\" method=\"post\" class='form-horizontal form-label-left'>\n";
		$html .= "<fieldset id='FdbkIncForm'>
				<legend>Feedback Increments Setup</legend><div class='x_content'>
				<div class='page_note'>All file paths are relative to <strong>".$admin->geo_templatesDir()."[Template Set]/external/</strong></div>";

		for($i = 0; $i < 10; $i++)
		{
			$icons = $icon_result->FetchRow();

			$html .= '<div style="border: 2px solid #eee; margin: 4px; padding: 10px 5px;">';

			$html .= '<div class="form-group">
						<label class="control-label col-xs-12 col-sm-5">
							Icon '.($i+1).' <img src="../'.geoTemplate::getUrl('',$icons['filename']).'" />
						</label>
						<div class="col-xs-12 col-sm-6">
							<input class="form-control col-md-7 col-xs-12" value="'.$icons['filename'].'" type="text" name="auto_save[icon]['.$i.']" />
						</div>
					</div>';

			$html .= '<div class="form-group">
							<label class="control-label col-xs-12 col-sm-5">
								Minimum Score
							</label>
							<div class="col-xs-12 col-sm-6">
								<input class="form-control col-md-7 col-xs-12" value="'.$icons['begin'].'" type="text" name="auto_save[min_icon]['.$i.']" />
							</div>
						</div>';

			$html .= '<div class="form-group">
							<label class="control-label col-xs-12 col-sm-5">
								Maximum Score
							</label>
							<div class="col-xs-12 col-sm-6">
								<input class="form-control col-md-7 col-xs-12" value="'.min($icons['end'],FEEDBACK_ICON_MAX_VALUE).'" type="text" name="auto_save[max_icon]['.$i.']" />
							</div>
						</div>';

			$html .= '<div class="form-group">
							<label class="control-label col-xs-12 col-sm-5">
								And up?
							</label>
							<div class="col-xs-12 col-sm-6">
								<input type="checkbox" '.($icons['end'] == FEEDBACK_ICON_MAX_VALUE ? 'checked="checked"' : '').' value="on" name="auto_save[and_up]['.$i.']" />
							</div>
						</div>';

			$html .= '</div>';
		}

		$zeroIcon = $db->GetOne("SELECT `filename` FROM ".geoTables::auctions_feedback_icons_table." WHERE `begin` = 0 ORDER BY `icon_num` ASC");
		$html .= '<div style="border: 2px solid #92a6bb; margin: 4px; padding: 2px;">';
		$html .= '<div class="form-group">
						<label class="control-label col-xs-12 col-sm-5">
							Icon for Zero Feedback (typically new users) <img src="../'.geoTemplate::getUrl('',$zeroIcon).'" />
						</label>
						<div class="col-xs-12 col-sm-6">
							<input class="form-control col-md-7 col-xs-12" value="'.$zeroIcon.'" type="text" name="auto_save[zero_icon]" />
						</div>
					</div>';
		$html .= '</div>';

		$negativeIcon = $db->GetOne("SELECT `filename` FROM ".geoTables::auctions_feedback_icons_table." WHERE `begin` = -1 ORDER BY `icon_num` ASC");
		$html .= '<div style="border: 2px solid #92a6bb; margin: 4px; padding: 2px;">';
		$html .= '<div class="form-group">
						<label class="control-label col-xs-12 col-sm-5">
							Icon for Negative Feedback <img src="../'.geoTemplate::getUrl('',$negativeIcon).'" />
						</label>
						<div class="col-xs-12 col-sm-6">
							<input class="form-control col-md-7 col-xs-12" value="'.$negativeIcon.'" type="text" name="auto_save[negative_icon]" />
						</div>
					</div>';
		$html .= '</div>';

		if (!defined('DEMO_MODE')) {
			$html .= "<div class='center'><input type='submit' value='Save' name='submit'></div>";
		}

		$html .= "</div></fieldset></form>";

		geoAdmin::display_page($html);
	}

	/**
	 * Update feedback increments
	 *
	 * Uses auto_save functionality of geoAdmin class
	 *
	 */
	function update_IncrementSettings() {
		if (PHP5_DIR)
			$menu_loader = geoAdmin::getInstance();
		else
			$menu_loader =& geoAdmin::getInstance();

		$iconsData = $_POST['auto_save'];

		// Get the DB object
		$db = true;
		include '../get_common_vars.php';

//		$this->title .= "Feedback Management > Edit Icons";
//		$this->description .= "You can control the aspects of the feedback system through this administration.  Control the number of feedback icons and their ranges with the feedback score.  In the URL field enter the full URL of the icon and in the score fields enter the range of scores you want.  Please put these in numerical order from lowest value to highest for optimal performance.  To designate a value as being from something on up put your lower value in the min and check the and up checkbox.  To delete or not use an icon leave all fields corresponding to it blank.";

		// Find last element to insert
		$last = 9;
		for($i = 0; $i < 10; $i++)
		{
			if(isset($iconsData['and_up'][$i]) && $iconsData['and_up'][$i] == 'on') {
				$last = $i;
				$i = 10;
			} elseif(!$iconsData['icon'][$i] && !$iconsData['min_icon'][$i] && !$iconsData['max_icon'][$i]) {
				$last = $i-1;
				$i = 10;
			}
		}

		// Range checking
		for($i = 0; $i < $last; $i++)
		{
			for($j = 1; $j < $last+1; $j++)
			{
				if($iconsData['min_icon'][$i] >=  $iconsData['min_icon'][$j] &&
				   $iconsData['max_icon'][$i] >=  $iconsData['max_icon'][$j] &&
				   $iconsData['min_icon'][$i] >=  $iconsData['max_icon'][$i] &&
				   $iconsData['min_icon'][$j] >=  $iconsData['max_icon'][$j] &&
				   $iconsData['max_icon'][$i] >=  $iconsData['min_icon'][$j])
				{
					trigger_error('ERROR: Invalid icon settings');
					$menu_loader->userError('Settings NOT Saved: Invalid icon settings');
					return false;
				}
			}
		}

		// Clear out the table
		$query = "delete from ".$db->geoTables->auctions_feedback_icons_table." where begin > 0";

		$result = $db->Execute($query);
		if(!$result)
		{
			trigger_error("ERROR: Couldn't flush icon settings");
			$menu_loader->userError('Settings NOT Saved.');
			return false;
		}

		// Insert elements
		for($i = 0; $i < $last+1; $i++)
		{
			if($iconsData['and_up'][$i]) {
				$iconsData['max_icon'][$i] = FEEDBACK_ICON_MAX_VALUE;
			}

			$query = "insert into ".$db->geoTables->auctions_feedback_icons_table." (filename, icon_num, begin, end) values (\"".$iconsData['icon'][$i]."\", ".$i.", ".$iconsData['min_icon'][$i].", ".$iconsData['max_icon'][$i].")";

			$result = $db->Execute($query);
			if(!$result)
			{
				trigger_error("ERROR: Couldn't insert icon settings");
				$menu_loader->userError('Settings NOT Saved.');
				return false;
			}
		}

		//update 0 feedback symbol
		$query = "update ".$db->geoTables->auctions_feedback_icons_table." set
			filename =\"".$iconsData['zero_icon']."\"
			where begin = 0";
		$result = $db->Execute($query);
		if(false === $result)
		{
			trigger_error("ERROR: Couldn't update icon settings");
			$menu_loader->userError('Settings NOT Saved.');
			return false;
		}

		//update negative feedback symbol
		$query = "update ".$db->geoTables->auctions_feedback_icons_table." set
			filename =\"".$iconsData['negative_icon']."\"
			where begin = -1";
		$result = $db->Execute($query);
		if(false === $result)
		{
			trigger_error("ERROR: Couldn't update icon settings");
			$menu_loader->userError('Settings NOT Saved.');
			return false;
		}
		$menu_loader->userSuccess('Settings Saved.');
		return true;
	}

	/**
	 * Displays global feedback settings form
	 */
	function display_GlobalSettings() {
		// Get the DB object
		$db = true;
		include '../get_common_vars.php';

		$html = "";

		if (PHP5_DIR) $menu_loader = geoAdmin::getInstance();
		else $menu_loader =& geoAdmin::getInstance();

		$html .= $menu_loader->getUserMessages();
		$html .= $body;
		$this->description .= $description;

		//$html .= $body;
		//$this->description .= $description;

		$html .= "
			<table cellpadding=3 cellspacing=1 border=0 align=center width=\"100%\">
				<tr>
					<td>
						<form action='index.php?mc=feedback&page=GlobalSettings' method='post' class='form-horizontal form-label-left' >
							<fieldset id='FdbkSettings'>
							<legend>Feedback Settings</legend>
								";

		$row_count = 0;
		$html .= "";

		$dropdown = '';
		for($i = 1; $i < 101; $i++)
		{
			if($db->get_site_setting('number_of_feedbacks_to_display') == $i) {
				$dropdown .= "<option value='".$i."' selected>".$i."</option>";
			} else {
				$dropdown .= "<option value='".$i."'>".$i."</option>";
			}
		}
		$dropdown = "<select name='auto_save[number_of_feedbacks_to_display]' class='form-control col-md-7 col-xs-12'>".$dropdown."</select>";
		$html .= "

							<div class='form-group'>
							<label class='control-label col-md-5 col-sm-5 col-xs-12'>Number of Feedbacks to Display per Page: </label>
							  <div class='col-md-6 col-sm-6 col-xs-12'>
							  ".$dropdown."
							  </div>
							</div>

							<div class='center'><input type='submit' value='Save' class='mini_button'></div>
							</fieldset></form>";

		$row_count++;
		$html .= "
						<fieldset id='FdbkSettings'>
				<legend>Current Feedback Increments</legend>
					<div class=\"table-responsive\">
				 		<table cellpadding=3 cellspacing=1 border=0 class=\"table table-hover table-striped table-bordered\">";

		// Show table with data
		$query = "select * from ".$db->geoTables->auctions_feedback_icons_table." where begin > 0 order by icon_num asc";

		$result = $db->Execute($query);
		if (false === $result) {
			trigger_error("ERROR: couldn't get feedback icons data");
			return false;
		} else if($result->RecordCount() > 0) {
			$html .= "<thead>
							<tr class='col_hdr_top'>
								<td align='center' class=col_hdr><b>Low End</b></td>
								<td class=col_hdr>&nbsp;</td>
								<td align='center' class=col_hdr><b>High End</b></td>
								<td align='center' class=col_hdr><b>Icon</b></td>
							</tr>
					  </thead>";

			$mod = true;
			while($increment = $result->FetchRow()) {
				$html .= "
							<tr class='".($mod ? 'row_color1' : 'row_color2')."'>
								<td align='center' class='medium_font'>".$increment['begin']." </td>
								<td align='center' class='medium_font'>to</font></td>
								<td align='center' class='medium_font'>".($increment['end'] == FEEDBACK_ICON_MAX_VALUE ? "and up" : $increment['end'])."</td>
								<td align='center'><img src='../".geoTemplate::getUrl('',$increment['filename'])."' alt='' /></td>
							</tr>";
				// Alternate the row colors
				$mod = !$mod;
			}
		} else {
			$html .= "
							<tr class=''>
								<td align='center'><span class=medium_font><b>There are currently no feedback icons set for the scores.</b></span></td>
							</tr>";
		}

		$html .= "
						</table></div></fieldset><div class='clearColumn'></div>
				</td></tr></table>
						";
		$query = "select * from ".$db->geoTables->auctions_feedback_icons_table." where begin = 0 order by icon_num asc";
		$zero_icon_result = $db->Execute($query);
		if (false === $zero_icon_result) {
			trigger_error("ERROR: couldn't get feedback icons data");
			return false;
		} else if($zero_icon_result->RecordCount() == 1) {
			$zero_icon = $zero_icon_result->FetchNextObject();
			$html .= "
			<fieldset id='AddlFdbkSettings'>
				<legend>Additional Feedback Settings</legend><div class=\"table-responsive\"><table cellpadding=3 cellspacing=1 border=0 class=\"table table-hover table-striped table-bordered\">
				<tr>
					<td>
						<table cellpadding=3 cellspacing=1 border=0 width=\"100%\">
							<tr class=row_color2>
								<td class=medium_font>for feedback ratings equal to 0</td>
								<td align=center>
									".(strlen(trim($zero_icon->FILENAME)) ? "<img src='../".geoTemplate::getUrl('',$zero_icon->FILENAME)."' border=0>" : "<span class=medium_font>no image set</span>")."
								</td>
							</tr>
						</table>
					</td>
				</tr>";
		}

		$query = "select * from ".$db->geoTables->auctions_feedback_icons_table." where begin = -1 order by icon_num asc";
		$negative_icon_result = $db->Execute($query);
		if (false === $negative_icon_result) {
			trigger_error("ERROR: couldn't get feedback icons data");
			return false;
		} else if($negative_icon_result->RecordCount() == 1) {
			$negative_icon = $negative_icon_result->FetchNextObject();
			$html .= "
				<tr>
					<td>
						<table cellpadding=3 cellspacing=1 border=0 width=\"100%\">
							<tr class=row_color1>
								<td class=medium_font>for feedback ratings less than 0</font></td>
								<td align=center>
									".(strlen(trim($negative_icon->FILENAME)) ? "<img src=\"../".geoTemplate::getUrl('',$negative_icon->FILENAME)."\" border=0>" : "<span class=medium_font>no image set</span>")."
								</td>
							</tr>
						</table>
					</td>
				</tr>";
		}

		$html .= "</table></div></fieldset>";
		$html .= "<table width=100%>
				<tr align=center>
					<td>
						".geoHTML::addButton('Edit',"index.php?mc=feedback&page=IncrementSettings")."
					</td>
				</tr>
				</table>";

		geoAdmin::display_page($html);
	}

	/**
	 * Display userdata for user with ID == $id
	 *
	 * @param int $id User ID
	 * @return unknown
	 */
	function display_Userdata($id) {
		if (strlen(PHP5_DIR)){
			$product_configuration = geoPC::getInstance();
		} else {
			$product_configuration =& geoPC::getInstance();
		}
		include_once 'admin_site_class.php';
		$admin = new Admin_site($this->_db, $product_configuration);
		if (!$admin->display_user_data($this->_db, $id)) {
			trigger_error("ERROR FEEDBACK: Couldn't show userdata");
			return false;
		}
		$admin->display_page();
	}



	function display_feedback_show()
	{
		if(isset($_POST['feedbackId']) && is_numeric($_POST['feedbackId'])) {
			$this->setId($_POST['feedbackId']);
		}
		if(isset($_GET['delete']) && is_numeric($_GET['delete'])) {
			/**
			 * Delete feedback
			 * Shows user data page after update
			 *
			 * b = auction ID
			 * c = rater ID
			 * e = user ID
			 */

			$this->delete_Feedback($_GET["delete"]);

		}

		if(isset($_GET['feedbackId']) && is_numeric($_GET['feedbackId']) && isset($_POST['d'])) {
			$this->update_UserFeedback($_GET['feedbackId']);
		}

		if(isset($_GET['userId'])) {
			$this->display_Userdata($_GET['userId']);
		} else if(isset($_GET['feedbackId']) && is_numeric($_GET['feedbackId'])) {
			$this->display_UserFeedback($_GET['feedbackId']);
		} else {
			$this->display_GlobalSettings();
		}
		//$this->display_page();
		//include_once 'admin_app_bottom.php';
	}
}

