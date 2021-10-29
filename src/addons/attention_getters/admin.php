<?php
//addons/attention_getters/admin.php
/**************************************************************************
Addon Created by Geodesic Solutions, LLC
Copyright (c) 2001-2018 Geodesic Solutions, LLC
All rights reserved
http://geodesicsolutions.com
see license attached to distribution
**************************************************************************/
##########GIT Build Data##########
## 
## File Changed In GIT Commit:
## ##    16.09.0-96-gf3bd8a1
## 
##################################

# Attention Getters Addon
require_once ADDON_DIR.'attention_getters/info.php';
class addon_attention_getters_admin extends addon_attention_getters_info
{
	var $admin_site;
	var $db;
	function addon_attention_getters_admin(){
		if (Singleton::isInstance('Admin_site')){
			if (strlen(PHP5_DIR)){
				$this->admin_site = Singleton::getInstance('Admin_site');
				$this->db = DataAccess::getInstance();
			} else {
				$this->admin_site =& Singleton::getInstance('Admin_site');
				$this->db =& DataAccess::getInstance();
			}
		} else {
			//if the admin site does not exist yet, something weird is going on,
			//since the admin site should have been the class to initialize this.
			return false;
		}
	}

	function init_pages () {
		//menu_page::addonAddPage($index, $parent, $title, $addon_name, $image, $type);
		menu_page::addonAddPage('listing_attention_getters_view','','Settings','attention_getters','fa-flag');
		menu_page::addonAddPage('listing_attention_getters_delete','listing_attention_getters_view','Delete Attention Getter','attention_getters','fa-flag','sub_page');

	}
	function init_text($language_id) {
		$return_var['yes'] = array (
			'name' => 'Attention Getter Yes Option',
			'desc' => 'Label for "Yes" radio button when user is selecting whether to use an attention getter',
			'type' => 'input',
			'default' => 'yes'
		);
		$return_var['no'] = array (
			'name' => 'Attention Getter No Option',
			'desc' => 'Label for "No" radio button when user is selecting whether to use an attention getter',
			'type' => 'input',
			'default' => 'no'
		);
		$return_var['AG_label'] = array (
			'name' => 'Attention Getter Label',
			'desc' => '',
			'type' => 'textarea',
			'default' => 'Attention Getter'
		);
		$return_var['AG_desc'] = array (
			'name' => 'Attention Getter Explanation',
			'desc' => '',
			'type' => 'textarea',
			'default' => 'Attention getters appear next to your listing\'s title while users are browsing the categories. They help to bring additional attention to your listing when it otherwise may be overlooked.'
		);
		$return_var['no_choice_error'] = array (
			'name' => 'Error mesage - No Choice',
			'desc' => 'error shown when a user checks to use an attention getter, but doesn\'t select which one he wants',
			'type' => 'textarea',
			'default' => 'You must select an attention getter to use.'
		);

		return $return_var;
	}
	function display_listing_attention_getters_view () {
		//view list of all attention getters

		$sql_query = "select * from ".$this->db->geoTables->choices_table." where type_of_choice = 10 order by display_order, value";
		$attention_getters_result = $this->db->Execute($sql_query);
		if (!$attention_getters_result)
		{
			$this->admin_site->site_error($this->db->ErrorMsg());
			return false;
		}
		if (isset($this->admin_site->error_message) || isset($this->admin_site->site_result_message)){
			$body .= '<span class="medium_error_font">'.$this->admin_site->site_result_message.'<br />'.$this->admin_site->error_message.'</span>';
		}
		$body .= "<form action=\"\" method=\"POST\" class=\"form-horizontal form-label-left\">\n";
		$body .= "<fieldset id='CurAttnGtrs'>
				<legend>Current Attention Getters</legend><div class=\"table-responsive\"><table cellpadding=\"3\" cellspacing=\"1\" style=\"margin: 0 auto;\" class=\"table table-hover table-striped table-bordered\" >\n";
		//$this->admin_site->title = "Listing Setup > Attention Getters";
		$this->admin_site->description = "Below is the list of attention getters
			that can be placed within the description field of an individual users listing.  You must turn on the use of attention getters before they
			become usable and then set the price to use them on a per price plan basis within the price plan administration.";
		if ($this->admin_site->site_result_message)
				$body .= "
					<tr>
						<td colspan=\"2\" class=\"medium_error_font\">".$this->admin_site->site_result_message."</td>
					</tr>";
		$body .= "<thead><tr class=\"col_hdr_top\">\n\t<td class=\"col_hdr_left\">\n\tAdmin Name\n\t</td>
			<td class=\"col_hdr_left\">\n\tURL of Image\n\t</td>
			<td class=\"col_hdr_left\">\n\tImage\n\t</td>
			<td class=\"col_hdr_left\">\n\t&nbsp;\n\t</td>\n</tr></thead>\n";
		$this->admin_site->row_count = 0;
		if ($attention_getters_result->RecordCount() > 0)
		{
			while ($show_attention_getters = $attention_getters_result->FetchRow())
			{
				$body .= "<tr class=\"".$this->admin_site->get_row_color()."\">\n\t<td class=\"medium_font\">\n\t".$show_attention_getters["display_value"]."\n\t</td>\n\t
					<td class=\"medium_font\">\n\t".$show_attention_getters["value"]."\n\t</td>";
				$body .= "<td class=\"medium_font\">\n\t<img src=\"../".$show_attention_getters["value"]."\" alt=\"attention getter image: ".$show_attention_getters["value"]."\" />\n\t</td>";
				$body .= "<td style=\"width: 100px; text-align: center;\">".geoHTML::addButton('Delete', "index.php?mc=listing_setup&amp;page=listing_attention_getters_delete&amp;c=".$show_attention_getters["choice_id"]."&amp;auto_save=1", false, '', 'lightUpLink mini_cancel')."</td>\n</tr>\n";
				$this->admin_site->row_count++;
			}
		}
		else
		{
			$body .= "<tr class=".$this->admin_site->get_row_color().">\n\t<td colspan=4>\n\t<span class=medium_font>There are currently no attention getters.  Add them through the fields
				below</span>\n\t</td>\n</tr>\n";
			$this->admin_site->row_count++;
		}
		if (!$this->admin_site->admin_demo())
		{
			$body .= "<tfoot><tr><td class=\"col_ftr\"><input type=\"text\" name=\"b[attention_getter_name]\" /></td>\n\t
				<td colspan=\"2\" class=\"col_ftr\"><input type=\"text\" name=\"b[attention_getter_url]\" size=\"50\" /></td>\n\t
				<td class=\"col_ftr\" align=\"center\"><input type=\"hidden\" name=\"auto_save\" value=\"yes\" /><input type=\"submit\" value=\"Save\" /></td>\n<tr></tfoot>\n";
		}
		$body .= "</table></div></fieldset>\n";
		$body .= "</form>\n";

		//add ability to auto-load
		geoView::getInstance()->setBodyTpl('admin/auto-load.tpl','attention_getters')
			->setBodyVar('adminMessages',geoAdmin::m())
			->addBody($body);
	}

	function display_listing_attention_getters_delete () {
		//delete an attention getter
		if (PHP5_DIR)
			$menu_loader = geoAdmin::getInstance();
		else
			$menu_loader =& geoAdmin::getInstance();
		$this->admin_site->body .= $menu_loader->getUserMessages();

		if(isset($_REQUEST["c"]) && is_numeric($_REQUEST["c"]))
			if(!$this->delete_attention_getter($_REQUEST["c"]))
				return false;
		$html = "<span class=\"medium_font\">Attention Getter Deleted!<br /><br /><a href=\"index.php?page=listing_attention_getters_view\">Back</a></span>";
		geoAdmin::display_page($html);
		//$this->display_listing_attention_getters_view();
	}


	function update_listing_attention_getters_view(){
		if (isset($_POST['autoLoadDir']) && $_POST['autoLoadDir']) {
			//auto-add specified dir
			$dir = trim($_POST['autoLoadDir']);
			$util = geoAddon::getUtil($this->name);
			$clearExisting = (isset($_POST['clearExisting']) && $_POST['clearExisting']);
			if (!$util->autoAdd($dir, $clearExisting)) {
				geoAdmin::m('Error parsing images in specified directory, check that you are using the path relative to the directory the software is installed to.');
				return false;
			}
			return true;
		} else if (isset($_POST['b']) && $_POST['b']) {
			$info = $_POST['b'];
			//do error checking yo..
			$ok = true;
			if (!strlen(trim($info['attention_getter_name']))){
				geoAdmin::m('Name Required!', geoAdmin::ERROR);
				$ok = false;
			}
			if (!strlen(trim($info['attention_getter_url']))){
				geoAdmin::m('URL Required!', geoAdmin::ERROR);
				$ok = false;
			}
			if (!$ok){
				return false;
			}
			$insert = array($info["attention_getter_name"],$info["attention_getter_url"]);
			$sql_query = "insert into ".$this->db->geoTables->choices_table."
				(type_of_choice,display_value,value)
				values
				(10,?,?)";
			$insert_result = $this->db->Execute($sql_query, $insert);
			if (!$insert_result) {
				geoAdmin::m('DB Error: '.$this->db->ErrorMsg(), geoAdmin::ERROR);
				return false;
			}
			geoAdmin::m('Attention Getter Added.');
			return true;
		}
		geoAdmin::m('Invalid data, please try again.',geoAdmin::ERROR);
		return false;
	}

//*************************************************************************

	function delete_attention_getter($attention_getter_id=0)
	{
		if ($attention_getter_id)
		{
			$sql_query = "delete from  ".$this->db->geoTables->choices_table." where choice_id = ".$attention_getter_id;
			$delete_result = $this->db->Execute($sql_query);
			if (!$delete_result)
			{
				$this->admin_site->site_error($this->db->ErrorMsg());
				return false;
			}
			return true;
		}
		else
		{
			$this->admin_site->error_message = $this->admin_site->internal_error_message;
			return false;
		}
	} //end of function delete_attention_getter

}
