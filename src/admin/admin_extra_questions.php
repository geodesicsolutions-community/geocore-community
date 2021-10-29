<?php
// admin_extra_questions.php
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
## 
##    16.09.0-109-g68fca00
## 
##################################

/**
 * Gives access to extra questions and dropdowns
 *
 * This class provides access to displaying and manipulating
 * functions for the extra questions and dropdowns.
 *
 * @package geo_admin
 */
class admin_extra_questions
{

	var $menu_loader; // variable for the admin menu loader
	var $admin_site; // variable for admin page
	var $html; // variable for the html of the admin page
	var $db; // variable for the database
	var $sql_query; // variable for sql queries

	function admin_extra_questions(){
		$this->menu_loader = geoAdmin::getInstance();
		$this->db = DataAccess::getInstance();
		$this->admin_site = Singleton::getInstance('Admin_site');

		if (!$this->menu_loader || !$this->admin_site){
			//if the admin site does not exist yet, something weird is going on,
			//since the admin site should have been the class to initialize this.
			return false;
		}
		$this->html = "";

	}

// delete dropdown
	/**
	 * Displays admin page for dropdown deletetion
	 */
	function display_delete_dropdown(){
		$this->display_dropdowns();
	}
	/**
	 * Preforms actions for dropdown deletetion
	 */
	function update_delete_dropdown(){
		$admin = geoAdmin::getInstance();
		//echo "hello from delete dropdown<br>\n";
		$dropdown_id = (int)$_REQUEST['d'];

		$sql = "DELETE FROM ".geoTables::classified_sell_questions_table." WHERE `choices` = ".$dropdown_id;
		//echo $this->sql_query."<br>\n";
		$result = $this->db->Execute($sql);
		if (!$result) {
			$admin->userError('DB Error deleting drop-down, try again.  Debug: '.$sql.' : Error: '.$this->db->ErrorMsg());
			return false;
		}

		$sql = "DELETE FROM ".geoTables::sell_choices_table." WHERE `type_id` = ".$dropdown_id;
		//echo $this->sql_query."<br>\n";
		$result = $this->db->Execute($sql);
		if (!$result) {
			$admin->userError('DB Error deleting drop-down, try again.  Debug: '.$sql.' : Error: '.$this->db->ErrorMsg());
			return false;
		}

		$sql = "DELETE FROM ".geoTables::sell_choices_types_table." WHERE `type_id` = ".$dropdown_id;
		//echo $this->sql_query."<br>\n";
		$result = $this->db->Execute($sql);
		if (!$result) {
			$admin->userError('DB Error deleting drop-down, try again.  Debug: '.$sql.' : Error: '.$this->db->ErrorMsg());
			return false;
		}
		return true;
	}

// delete dropdown int
	/**
	 * displays confirmation for deletion of dropdown
	 */
	function display_delete_dropdown_int(){
		$admin = geoAdmin::getInstance();
		$view = $admin->v();

		$view->setRendered(true);

		$tpl_vars = array ();

		$dropdown_id = $tpl_vars['dropdown_id'] = (int) $_REQUEST['d'];

		if (!$dropdown_id) {
			$admin->userError('Invalid dropdown ID, cannot delete.');
			echo geoAdmin::m();
			return;
		}



		$sql = "SELECT * FROM ".geoTables::sell_choices_types_table." WHERE `type_id` = ".$dropdown_id;
		$show_dropdown = $tpl_vars['show_dropdown'] = $this->db->GetRow($sql);

		if (!$show_dropdown) {
			$admin->userError('Invalid dropdown ID (could not find dropdown in database), cannot delete.');
			echo geoAdmin::m();
			return;
		}

		$sql = "SELECT * FROM ".geoTables::classified_sell_questions_table." WHERE `choices` = ".$dropdown_id;
		$choices = $this->db->GetAll($sql);
		foreach ($choices as $key => $choice) {
			$choices[$key]['category_name'] = geoCategory::getName($choice['category_id'], true);
		}
		$tpl_vars['choices'] = $choices;

		$tpl = new geoTemplate(geoTemplate::ADMIN);
		$tpl->assign($tpl_vars);

		echo $tpl->fetch('categories/deleteDropdownConfirmation.tpl');
	}
	/**
	 * does nothing, place holder
	 */
	function update_delete_dropdown_int(){
	}

// delete dropdown value
	/**
	 * displays value list after deletion
	 */
	function display_delete_dropdown_value(){
		$this->display_edit_dropdown(); // got nothing special to show, show the value list
		//$this->menu_loader->display_page($this->html);
	}
	/**
	 * deletes value from dropdown
	 */
	function update_delete_dropdown_value(){
		$value_id = $_REQUEST['g'];

		if ($value_id)
		{
			$this->sql_query = "delete from ".$this->db->geoTables->sell_choices_table." where value_id = ".$value_id;
			$result = $this->db->Execute($this->sql_query);
			if (!$result)
			{
				return false;
			}
			return true;
		}
		else
		{
			return false;
		}
	}

// edit dropdown
	/**
	 * displays Add/Delete values from dropdowns
	 */
	function display_edit_dropdown(){
		$dropdown_id = $_REQUEST['c'];

		if ($dropdown_id)
		{
			$this->sql_query = "select * from ".$this->db->geoTables->sell_choices_types_table." where type_id = $dropdown_id";
			$result = $this->db->Execute($this->sql_query);
			if (!$result)
			{
				trigger_error("ERROR SQL: " . $this->db->ErrorMsg());
				$this->html .= $this->menu_loader->getUserMessages();
				$this->menu_loader->display_page($this->html);
				return false;
			}
			elseif ($result->RecordCount() == 1)
			{
				//this dropdown exists
				$show_dropdown = $result->FetchRow();
				$this->sql_query = "select * from ".$this->db->geoTables->sell_choices_table." where type_id = ".$dropdown_id." order by display_order";
				$result = $this->db->Execute($this->sql_query);
				if (!$result)
				{
					$this->html .= $this->menu_loader->getUserMessages();
					$this->menu_loader->display_page($this->html);
					return false;
				}
				$this->html .= $this->menu_loader->getUserMessages();

				$this->html .= "<div class='page-title1'>Current Dropdown: <span class='color-primary-two'>{$show_dropdown['type_name']}</span></div>";
				
				//show the form to edit this dropdown
				if (!$this->admin_site->admin_demo()) {
					$this->html .= "<form action=index.php?page=edit_dropdown&c=".$dropdown_id." method=post class='form-horizontal'>";
				} else {
					$this->html .= "<div class='form-horizontal'>";
				}
				$this->html .= "
						<fieldset id='EditPreValDropdown'><legend>Edit a Dropdown</legend>
							<div class='table-responsive'>
							<table class='table table-hover table-striped table-bordered'>
								";
				$this->html .= "<thead>
											<tr>
												<th>Value</td>
												<th style='text-align: center;'>Display Order</td>
												<th>&nbsp;</td>
											</tr>
								</thead><tbody>";
				if ($result->RecordCount() > 0){
					$this->admin_site->row_count = 0;
					while ($show = $result->FetchRow())	{
						$this->html .= "<tr>
												<td class=medium_font>".$show["value"]."</td>
												<td class=medium_font style='text-align: center;'>".$show["display_order"]."</td>
												<td style='text-align: center;'>".geoHTML::addButton('delete',"index.php?page=delete_dropdown_value&g=".$show["value_id"]."&c=".$dropdown_id."&auto_save=1", false, '', 'lightUpLink mini_cancel')."
												</td>
											</tr>";
					}
				}
				$this->html .= "<tr>
									<td class='col_ftr' style='text-align: center;'>
										<div class='form-group'>
											<label class='col-xs-12 col-sm-4 control-label'>Enter New Value: </label>
											<div class='col-xs-12 col-sm-7'><input type=text name=b[value] size=25 class='form-control'></div>
										</div>
									</td>
									<td class='col_ftr' style='text-align: center;'>
										<div class='form-group'>
											<label class='col-xs-12 col-sm-4 control-label'>Display Order: </label>
											<div class='col-xs-12 col-sm-7'><input type='number' min='0' name=b[display_order] value='1' size=3 class='form-control'></div>
										</div>
									</td>
									<td class='col_ftr' style='text-align: center;'>";
				
									if (!$this->admin_site->admin_demo()) {
										$this->html .= "<input type=submit name='auto_save' value=\"Add\">";
									}
				$this->html .= "
									</td>
								</tr>";

				$this->html .= "
						</tbody>		
						</table>
						</div>
						<div style='padding: 5px;'><a href=index.php?page=dropdowns class='back_to'>
						<i class='fa fa-backward'></i> Back to All Dropdowns</a></div>
					
				</fieldset>";
				if(!$this->admin_site->admin_demo()) {
					$this->html .= '</form>';
				} else {
					$this->html .= '</div>';
				}
			}else{
				$this->html .= $this->menu_loader->getUserMessages();
				$this->menu_loader->display_page($this->html);
			}
		}
		else
		{
			$this->html .= $this->menu_loader->getUserMessages();
			$this->menu_loader->display_page($this->html);
		}
		$this->menu_loader->display_page($this->html);
		return true;
	}
	/**
	 * Adds value to dropdowns
	 */
	function update_edit_dropdown(){
		$information = $_REQUEST['b'];
		$dropdown_id = intval($_REQUEST['c']);
		if (!is_array($information) || !$information || !$dropdown_id) {
			//invalid input
			return false;
		}
		$information['display_order'] = intval($information['display_order']);
		if (!$information['display_order']) {
			$this->menu_loader->userError('Display order must be a numeric value greater than 0.');
			return false;
		}
		if (!strlen(trim($information['value'])) && $information['display_order'] != 1) {
			$this->menu_loader->userError('Error:  Blank values are only allowed with display order 1.');
			return false;
		}
		//got this far: all checks are OK, go ahead and insert.

		$this->sql_query = "INSERT INTO ".geoTables::sell_choices_table."
			(type_id,value,display_order)
			VALUES (?, ?, ?)";
		$query_data = array ($dropdown_id, trim($information["value"]),$information["display_order"]);
		$result = $this->db->Execute($this->sql_query, $query_data);
		if (!$result) {
			return false;
		}
		$id = $this->db->Insert_ID();
		return $id;
	}

// display dropdowns
	/**
	 * Displays the site dropdowns.
	 */
	function display_dropdowns(){
		$this->sql_query = "select * from ".$this->db->geoTables->sell_choices_types_table." order by type_name";
		$result = $this->db->Execute($this->sql_query);
		if (!$result)
		{
			trigger_error("ERROR SQL: " . $this->db->ErrorMsg());
			$this->menu_loader->userError("Internal error. Please contact <a href='http://www.geodesicsolutions.com/support/index.htm'>support</a>.");
			$this->html .= $this->menu_loader->getUserMessages();
			return false;
		}

		$this->html .= $this->menu_loader->getUserMessages();
		$this->html .= "
			<fieldset id='PreValDropdowns'>
				<legend>Current Pre-Valued Dropdowns</legend>
				<div class=\"table-responsive\">
				<table cellpadding=2 cellspacing=1 border=0 class=\"table table-hover table-striped table-bordered\">
				";
		if ($result->RecordCount() > 0)
		{
			$this->html .= "
						<thead>
							<tr class='col_hdr_top'>
								<td class=\"col_hdr_left\"><b>Dropdown Name</b></td>
								<td class=\"col_hdr\" align=\"center\">&nbsp;</td>
								<td class=\"col_hdr\" align=\"center\">&nbsp;</td>
							</tr>
						</thead>";
			$this->admin_site->row_count = 0;
			while ($show = $result->FetchRow())
			{
				$this->html .= "
						<tr class=".$this->admin_site->get_row_color().">
							<td class=medium_font>".$show["type_name"]." </td>
							<td align='center'><a href='index.php?page=edit_dropdown&c={$show["type_id"]}' class='btn btn-info btn-xs'><i class='fa fa-pencil'></i> Edit</a></td>
							<td align='center'><a href=\"index.php?page=delete_dropdown_int&d={$show["type_id"]}\" class='lightUpLink btn btn-danger btn-xs'><i class='fa fa-remove'></i> Delete</a></td>
						</tr>";
				$this->admin_site->row_count++;
			}
		}
		else
			$this->html .= "
						<tr><td class=medium_font>There are no current dropdowns</td></tr>";
		$this->html .= "<tr><td colspan=\"3\">";
		$this->html .= $this->new_dropdown_form();
		$this->html .= "</td></tr>
					</td>
				</tr>
			</table></div></fieldset>";

		$this->menu_loader->display_page($this->html);
		return true;
	}
	/**
	 * no update function for dropdowns
	 */
	function update_dropdowns(){
	}

// new dropdown
	/**
	 * displays form for new dropdown
	 */
	function display_new_dropdown(){
		if ($_REQUEST['auto_save'])
		{
			$this->display_dropdowns();
		}
		else
		{
			$this->menu_loader->display_page($this->new_dropdown_form());
		}
	}
	/**
	 * adds new dropdown to database
	 */
	function update_new_dropdown(){
		$information = $_REQUEST["b"];

		if ($information)
		{
			if (strlen(trim($information["dropdown_label"])) > 0)
			{
				$this->sql_query = "insert into ".$this->db->geoTables->sell_choices_types_table."
					(type_name)
					values
					(\"".$information["dropdown_label"]."\")";
				$result = $this->db->Execute($this->sql_query);
				if (!$result)
				{
					//echo $this->sql_query."<br>\n";
					return false;
				}
				$id = $this->db->Insert_ID();
				return true;
			}
			else
			{
				return false;
			}
		}
		else
		{
			return false;
		}
	}

// class functions
	function new_dropdown_form(){
		if (!$this->admin_site->admin_demo())$html .= "<form action=index.php?page=new_dropdown method=post>\n";

		$html .= "<table cellpadding=2 cellspacing=0 border=0 class=row_color1>\n";
		//$this->title = "Add a new Sell question Dropdown Form";
		$this->description = "Use this form to add a new dropdown to
			the dropdowns usable as a question.  Type the name below and click \"enter\".  You will then be able to add values to
			the dropdown you have just created.";
		$html .= "<tr>\n\t
			<td align=center colspan=3 class=col_ftr>New Dropdown:&nbsp;<input type=text name=b[dropdown_label] size=35>&nbsp;<input type=submit name='auto_save' value=\"Add Dropdown\" class='mini_button'></td>\n\t";
		$html .= "</tr>\n";
		$html .= "</table>\n";
		$html .= "</form>\n";
		return $html;
	}
}
