<?php
// admin_ad_configuration_class.php
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
## ##    17.05.0-22-g4435795
## 
##################################

class Ad_configuration extends Admin_site {

	var $internal_error_message = "There was an internal error";
	var $data_error_message = "Not enough data to complete request";
	var $page_text_error_message = "No text connected to this page";
	var $no_pages_message = "No pages to list";

	var $ad_configuration_message;
	var $ad_configuration_data;

	var $default_ad_template = "";

	var $default_extra_template = "";

	var $default_extra_template2 = "";

	var $default_checkbox_template2 = "";

	var $debug_ad = 0;
	var $debug_auction = 0;
	var $auction_debug = 0;

	var $auto_title_array;
	var $titles;
	var $title_count;

	public $onOffSettings = array(
		'useSlideshow', 'startSlideshow', 'imagecreatetruecolor_switch',
		'image_upload_type',
		'image_link_destination_type',
		'useLightboxAnimations'
	);
	public $valueSettings = array (
		'url_image_directory', 'image_upload_path', 'maximum_upload_size',
		'photo_quality', 'maximum_image_description',
		'number_of_photos_in_detail', 'photo_columns',
		'gallery_style','starting_image_title',
	);
	public function display_listing_photo_upload_settings()
	{
		$db = DataAccess::getInstance();

		$oldSchool = $db->GetRow("SELECT * FROM ".$this->ad_configuration_table);

		if ($oldSchool['image_upload_path'] && !is_writable($oldSchool['image_upload_path'])) {
			geoAdmin::m('The current server path to image directory is not writable!  Be sure the setting is set correctly and that the
				directory is writable (CHMOD 777), the current setting is
				<span style="white-space: nowrap;">&quot;'.geoString::specialChars($oldSchool['image_upload_path']).'&quot;</span>', geoAdmin::NOTICE);
		}
		$tpl_vars = array();
		$tpl_vars['adminMessages'] = geoAdmin::m();

		foreach ($this->onOffSettings as $setting) {
			if (isset($oldSchool[$setting])) {
				//this is old school setting
				$tpl_vars[$setting] = $oldSchool[$setting];
			} else {
				$tpl_vars[$setting] = $db->get_site_setting($setting);
			}
		}
		foreach ($this->valueSettings as $setting) {
			if (isset($oldSchool[$setting])) {
				//this is old school setting
				$tpl_vars[$setting] = $oldSchool[$setting];
			} else {
				$tpl_vars[$setting] = $db->get_site_setting($setting);
			}
		}
		$tpl_vars['server_dir'] = GEO_BASE_DIR;
		$tpl_vars['is_ent'] = geoPC::is_ent();

		$dim = array ();
		//maximum dimensions of thumbnails for the table view
		$dim[] = array (
			'label' => 'All Thumbnails',
			'name' => 'maximum_image',
			'width' => $oldSchool["maximum_image_width"],
			'height' => $oldSchool["maximum_image_height"]
		);

		//maximum dimensions of thumbnails for gallery
		$dim[] = array (
			'label' => 'Small Gallery Thumbnails '.geoHTML::showTooltip('Small Gallery Thumbnails','Setting this larger than "All Thumbnails" above will have no effect.'),
			'name' => 'maximum_thumb',
			'width' => $this->db->get_site_setting("maximum_thumb_width"),
			'height' => $this->db->get_site_setting("maximum_thumb_height")
		);

		//maximum dimension of big picture in gallery
		$dim[] = array (
			'label' => 'Large Gallery Thumbnail '.geoHTML::showTooltip('Large Gallery Thumbnail','Setting this larger than "All Thumbnails" above will have no effect.'),
			'name' => 'gallery_main',
			'width' => $this->db->get_site_setting("gallery_main_width"),
			'height' => $this->db->get_site_setting("gallery_main_height")
		);

		//maximum dimensions of full-sized image
		$dim[] = array (
			'label' => 'Lightbox Popup (Full) Image',
			'name' => 'maximum_full_image',
			'width' => $oldSchool["maximum_full_image_width"],
			'height' => $oldSchool["maximum_full_image_height"]
		);

		//Lead Photo -- NOT part of the "image block"
		if (geoPC::is_ent()) {
			$dim[] = array (
				'label' => 'Lead Picture',
				'name' => 'lead_picture',
				'width' => $oldSchool["lead_picture_width"],
				'height' => $oldSchool["lead_picture_height"]
			);
		}


		$tpl_vars['dimensionSettings'] = $dim;

		$view = geoView::getInstance();
		$view->setBodyTpl('settings/photo_upload_settings.tpl')
			->setBodyVar($tpl_vars);
	}


	//re-do checks for: photo_quality maximum_image_description
	public function update_listing_photo_upload_settings()
	{
		$db = DataAccess::getInstance();

		$oldSchool = $db->GetRow("SELECT * FROM ".$this->ad_configuration_table);
		$oldNewSettings = array();
		foreach ($this->onOffSettings as $setting) {
			//the on/off settings are simple, it's either on or off.
			$use = (isset($_POST[$setting]) && $_POST[$setting])? 1: false;
			if (isset($oldSchool[$setting])) {
				$use = ($use)? $use: 0;
				$oldNewSettings[$setting] = $use;
			} else {
				$db->set_site_setting($setting,$use);
			}
		}

		$oldNewSettings['url_image_directory'] = trim($_POST['url_image_directory']);
		$oldNewSettings['image_upload_path'] = trim($_POST['image_upload_path']);
		if ($oldNewSettings['image_upload_path'] && !is_dir($oldNewSettings['image_upload_path'])) {
			//warn them, but still allow it..
			geoAdmin::m('Warning: The specified image upload path <span style="white-space: nowrap;">&quot;'.geoString::specialChars($oldNewSettings['image_upload_path']).'&quot;</span>
				does not exist, or is not a valid directory.
				Be sure you are entering the full server path to the image upload directory.  See the user manual for more information.',
				geoAdmin::NOTICE);
		}

		$oldNewSettings['maximum_upload_size'] = (int)$_POST['maximum_upload_size'];
		$oldNewSettings['photo_quality'] = (int)$_POST['photo_quality'];
		if ($oldNewSettings['photo_quality'] < 1 || $oldNewSettings['photo_quality'] > 100) {
			//unset it so it's not changed from current value.
			unset($oldNewSettings['photo_quality']);
			//let them know
			geoAdmin::m('Invalid photo quality specified, expecting a number between 1 and 100.', geoAdmin::ERROR);
		}
		$oldNewSettings['maximum_image_description'] = (int)$_POST['maximum_image_description'];
		$oldNewSettings['number_of_photos_in_detail'] = (int)$_POST['number_of_photos_in_detail'];

		$dimensions = $_POST['dim'];
		$oldNewSettings['maximum_image_width'] = (int)$dimensions['maximum_image_width'];
		$oldNewSettings['maximum_image_height'] = (int)$dimensions['maximum_image_height'];

		$oldNewSettings['maximum_full_image_width'] = (int)$dimensions['maximum_full_image_width'];
		$oldNewSettings['maximum_full_image_height'] = (int)$dimensions['maximum_full_image_height'];

		$oldNewSettings['photo_columns'] = ((int)$_POST['photo_columns'])? (int)$_POST['photo_columns']: 2;

		$db->set_site_setting('maximum_thumb_width', (int)$dimensions['maximum_thumb_width']);
		$db->set_site_setting('maximum_thumb_height', (int)$dimensions['maximum_thumb_height']);

		$db->set_site_setting('gallery_main_width', (int)$dimensions['gallery_main_width']);
		$db->set_site_setting('gallery_main_height', (int)$dimensions['gallery_main_height']);

		//gallery style
		$gallery_style = (isset($_POST['gallery_style']) && in_array($_POST['gallery_style'], array ('photoswipe','gallery','gallery2', 'classic','filmstrip')))? $_POST['gallery_style'] : 'photoswipe';
		$db->set_site_setting('gallery_style', $gallery_style);

		$starting_image_title = (in_array($_POST['starting_image_title'], array('filename','blank')))? $_POST['starting_image_title'] : 'filename';
		$db->set_site_setting('starting_image_title', $starting_image_title);

		if (geoPC::is_ent()) {
			$oldNewSettings['lead_picture_width'] = (int)$dimensions['lead_picture_width'];
			$oldNewSettings['lead_picture_height'] = (int)$dimensions['lead_picture_height'];
		}

		if (count($oldNewSettings)) {
			//save all the old data
			$sets = array();
			$query_data = array();
			foreach ($oldNewSettings as $setting => $value) {
				$sets[] = "`$setting` = ?";
				$query_data[] = $value;
			}
			$sql = "UPDATE ".geoTables::ad_configuration_table." SET ".implode(', ', $sets);
			$result = $db->Execute($sql, $query_data);
			if (!$result) {
				geoAdmin::m('DB Error when saving data.  Debug info: '.$db->ErrorMsg(),geoAdmin::ERROR);
				return false;
			}
		}
		return true;
	}
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
	function title_questions_dropdown($name)
	{
		if($name)
		{
			$html = "<select name=\"".$name."\">\n\t";
			foreach($this->auto_title_array as $id => $label)
			{

				$title = (isset($this->titles[$this->title_count])) ? $this->titles[$this->title_count] : "0";
				$selected = ($title == $id) ? " selected=\"selected\"" : "";
				$html .= "<option value=\"".$id."\"".$selected.">".$label."</option>\n\t";
			}
			$this->title_count++;
			$html .= "</select>\n";
			return $html;
		}
	}
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
	function set_sitewide_auto_title($useMe, $choices)
	{
			if($useMe != 1)
			{
				if(!$this->db->set_site_setting("use_sitewide_auto_title", 0))
					return false;
			}
			else
			{
				if(!$choices)
				{
					return false;
				}

				$title = "";
				foreach($choices as $q)
				{
					$title .= $q . "|";
				}
				$title = substr($title, 0, -1); // remove ending bar
				if(!$this->db->set_site_setting("use_sitewide_auto_title", 1)) return false;
				if(!$this->db->set_site_setting("sitewide_auto_title", $title)) return false;
			}
			return true;
	}

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

	function classified_length_form()
	{
		//$this->title = "Listing Setup > Listing Durations";
		$this->description = "The table below allows you to prepopulate a Listing Duration dropdown box that appears to the
		  seller during the Listing process.  This allows them to choose how long they want the listing to stay active on
		  your site. Note: This box will not be used for those users who fall under	a Price Plan that is set to charge based
		  upon the duration of the listing.";

		if (!$this->admin_demo()) {
			$this->body .= "<form action=index.php?mc=listing_setup&page=listing_listing_durations class=\"form-horizontal form-label-left\" method=post>";
		} else {
			$this->body .= "<div class='form-horizontal'>";
		}

		$sql = "select * from ".$this->pages_languages_table." ORDER BY language_id";
		$language_result = $this->db->Execute($sql);

		if (PHP5_DIR) $menu_loader = geoAdmin::getInstance();
		else $menu_loader =& geoAdmin::getInstance();


		if (!$language_result) {
			trigger_error("ERROR SQL: " . $this->db->ErrorMsg());
			$menu_loader->userError("Internal error. Please contact <a href='http://www.geodesicsolutions.com/support/index.htm'>support</a>.  Debug info:  DB error: ".$this->db->ErrorMsg().' in '.__file__.' line '.__line__);
			$this->body .= $menu_loader->getUserMessages();
			return false;
		} elseif ( $language_result->RecordCount() > 0 ) {
			$this->body .= $menu_loader->getUserMessages();
			$this->body .= $body;
			$this->description .= $description;

			$this->row_count = 0;
			$this->body .= "<fieldset id='ListingDurations'>
				<legend>Current Listing Durations</legend><div class='table-responsive'><table cellpadding=3 cellspacing=1 border=0 align=center class='table table-hover table-striped table-bordered'>\n";
			$this->body .= "<thead><tr class='col_hdr_top'>\n\t<td width=33% align=center class=col_hdr2><strong>Duration (in days)<br/><nobr>(numeric only)</nobr></strong>\n\t</td>\n\t";

			while ($show_language = $language_result->FetchRow())
			{
				$languageIDs[] = $show_language['language_id'];
		    	$this->body .= "<td width=33% align=center class=col_hdr2><strong>Displayed Value<br/><nobr>(".$show_language['language'].")</nobr></strong>\n\t</td>\n\t";
		    }


			$this->body .= "<td width=\"33%\" class=col_hdr2>\n\t&nbsp;\n\t</td>\n</tr></thead>\n";

			$sql_query = "SELECT * FROM ".$this->choices_table." WHERE type_of_choice = 1 ORDER BY numeric_value,language_id";

			$length_result = $this->db->Execute($sql_query);
			if (!$length_result)
			{
		  		$this->site_error($this->db->ErrorMsg());
		  		return false;
		  	}
		  	while ($show_lengths = $length_result->FetchRow())
		  	{
		  	  $choices[$show_lengths['numeric_value']][$show_lengths['language_id']] = $show_lengths['display_value'];
		  	}
		    foreach ($choices as $key => $array_value) {
		    	$key = ($key == 0) ? 'Unlimited' : $key;
		    	$this->body .= "<tr class=".$this->get_row_color().">\n\t<td width=33% align=center class=medium_font>\n\t".$key."\n\t</td>\n\t";
		    	foreach ($array_value as $langKey => $value) {
		  		  //$display_value = array_search($langID,)
		  		  $this->body .= "<td width=33% align=center class=medium_font>\n\t".$value."\n\t</td>\n\t";
		  		}

				$delete_button = geoHTML::addButton('Delete','index.php?mc=listing_setup&page=listing_listing_durations&d='.$key.'&auto_save=1', false, '', 'lightUpLink mini_cancel');
				$this->body .= "<td width=33% align=center>".$delete_button."</td>";

				$this->body .= "</tr>\n";

		  		$this->row_count++;
		    }
		    $this->body .= "<tr>\n\t<td align=center width=\"33%\" class=col_ftr>New Duration: \n\t<input type=text name=c[value] size=4 maxsize=4 id='value'> days
		    	or Unlimited <input type='checkbox' name='c[unlimited]' value='1' onclick='if(this.checked)jQuery(\"#value\").prop(\"disabled\",true); else jQuery(\"#value\").prop(\"disabled\",false);'/>
		    	</td>";
		    foreach ($languageIDs as $langID) {
		      $this->body .= "<td width=33% align=center class=col_ftr>\n\t<INPUT type=text name=c[display_value][$langID]>\n\t</td>\n\t";
		    }
		    if (!$this->admin_demo())
		    		$this->body .= "<td width=33% align=center class=col_ftr>\n\t<input type=submit name=\"auto_save\" value=\"Save\">\n\t</td>\n</tr>\n";
		  	$this->body .= "</table></div></fieldset>\n";
		  	if(!$this->admin_demo()) {
		  		$this->body .= "</form>\n";
		  	} else {
		  		$this->body .= "</div>";
		  	}
		  	return true;
		}
	}//end of function classified_length_form

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

	function add_classified_length($new_length=0)
	{
		if (PHP5_DIR) $menu_loader = geoAdmin::getInstance();
		else $menu_loader =& geoAdmin::getInstance();
		if (is_array($new_length))
		{
			if($new_length['unlimited'] == 1) {
				$new_length['value'] = 0;
			}
			if ($new_length['unlimited'] || ($new_length['value'] && is_numeric($new_length['value'])))
			{
				$new_length['value'] = intval($new_length['value']);
				$sql_query = "SELECT * FROM  ".$this->choices_table." WHERE type_of_choice = 1
				  AND numeric_value = ".$new_length["value"];
				$result = $this->db->Execute($sql_query);
				if (!$result)
				{
					$this->userError('DB Query error, please try again.  Debug info: SQL: '.$sql_query.' Error Msg: '.$this->db->ErrorMsg());
					return false;
				}
				elseif ($result->RecordCount() == 0 )
				{
					foreach ($new_length['display_value'] as $langID => $display_value)
					{
	  					$sql_query = "insert into ".$this->choices_table."
	  						(type_of_choice,display_value,numeric_value,language_id)
	  						values
	  						(1,'$display_value',".$new_length["value"].",$langID)";
	  					$result = $this->db->Execute($sql_query);
	  					if (!$result)
	  					{
	  						$this->userError('DB Query error, please try again.  Debug info: SQL: '.$sql_query.' Error Msg: '.$this->db->ErrorMsg());
	  						return false;
	  					}
					}
				}
				else
				{
					$menu_loader->userError("That duration length already exists. Please try again.");
					return false;
				}
			}
			elseif (!is_numeric($new_length["value"]))
			{
				$menu_loader->userError("Please only enter numbers for 'length in days'.");
				return false;
			} else {
				//they entered 0 days, or left it blank...
				$menu_loader->userError("0 days is not a valid duration length, please try again.");
				return false;
			}
		}
		else
		{
			$menu_loader->userError("Internal Error, please try again.  Debug info: is_array(\$new_length) is false, in ".__file__.' line '.__line__);
			return false;
		}
		return true;
	} //end of function update_ad_configuration

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

	function delete_classified_length($numeric_value=false)
	{
		if (PHP5_DIR) $menu_loader = geoAdmin::getInstance();
		else $menu_loader =& geoAdmin::getInstance();

		if ($numeric_value !== false)
		{
			//make sure this is not the only duration before proceeding (group languages by numeric value, since each language is its own db row)
			$sql_query = "SELECT * FROM  ".$this->choices_table." WHERE type_of_choice = 1 GROUP BY numeric_value";
			$result = $this->db->Execute($sql_query);
			if (!$result)
			{
				$menu_loader->userError('DB Error, please try again.  Debug info: SQL: '.$sql_query.' Error Msg: '.$this->db->ErrorMsg());
				return false;
			}
			elseif ($result->RecordCount() > 1)
			{
				$sql_query = "DELETE FROM  ".$this->choices_table." WHERE type_of_choice = 1 AND numeric_value = ?";
				$result = $this->db->Execute($sql_query, array($numeric_value));
				if (!$result)
				{
					$menu_loader->userError('DB Error, please try again.  Debug info: SQL: '.$sql_query.' Error Msg: '.$this->db->ErrorMsg());
					return false;
				}
				$menu_loader->userSuccess('Listing duration removed.');
				return true;
			}
			else
			{
				$menu_loader->userError("Can not remove the only duration.  You must have at least one duration value at all times.  To delete the current value you
					must add another listing duration first.");
				return false;
			}
		}
		else
		{
			$menu_loader->userError('Error, duration not specified.  Please try again.');
			return false;
		}
	} //end of function delete_classified_length

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

	function file_types_form($db)
	{
		$this->body .= geoAdmin::m();
		
		//file types accepted
		$sql_query = "select * from ".$this->file_types_table." ORDER BY `file_type_id`";
		$type_result = $this->db->Execute($sql_query);
		if (!$type_result) {
			trigger_error("ERROR SQL: " . $this->db->ErrorMsg());
			$menu_loader->userError("Internal error. Please contact <a href='http://www.geodesicsolutions.com/support/index.htm'>support</a>.");
			$this->body .= $menu_loader->getUserMessages();
			return false;
		}
		

		if (!$this->admin_demo()) {
			$this->body .= "<form action='index.php?mc=listing_setup&page=listing_allowed_uploads' method='post'>";
		}
		$this->body .= "
		<fieldset id='AllowedUploads'>
			<legend>File Types Allowed in Listing</legend>
			<div>
			<div class='table-responsive'>
			<table class='table table-striped table-hover'>
				<tr>
					<td class='col_hdr_left'>File Name</td>
					<td class='col_hdr_left'>Mime Type</td>
					<td class='col_hdr_left'>Extension</td>
					<td class='col_hdr'>Allowed</td>
					<td class='col_hdr'>Icon Used</td>
					<td class='col_hdr'>&nbsp;</td>
				</tr>";

		while ($show_types = $type_result->FetchRow())
		{
			$yes_checked = ($show_types["accept"] == 1)? " checked='checked'": '';
			$no_checked = ($show_types["accept"] == 2)? " checked='checked'": '';

			$icon_to_use = (strlen(trim($show_types['icon_to_use']))==0)? $show_types['icon_to_use'] : '../'.geoTemplate::getUrl('', $show_types['icon_to_use']);

			$icon = ((strlen(trim($icon_to_use)) > 0))? "<img src='".trim($icon_to_use)."' alt='' />": "<span class='medium_font'><strong>no icon</strong></span>";
			$delete_button = geoHTML::addButton('Delete','index.php?mc=listing_setup&page=listing_allowed_uploads&b='.$show_types["file_type_id"].'&auto_save=1', false, '', 'lightUpLink mini_cancel');
			$this->body .= "
				<tr>
					<td class='medium_font'>{$show_types["name"]}</td>
					<td class='medium_font'>{$show_types["mime_type"]}</td>
					<td class='medium_font'>{$show_types["extension"]}</td>
					<td class='medium_font' align='center'>
						<label><input type='radio' name='e[{$show_types["file_type_id"]}]' value='1'  class='medium_font'$yes_checked /> yes</label>
						<label><input type='radio' name='e[{$show_types["file_type_id"]}]' value='2'  class='medium_font'$no_checked /> no</label>
					</td>
					<td align='center'>$icon</td>
					<td align='center' width='100'>".$delete_button."
					</td>
				</tr>";
		}
		$this->body .= '</table></div>';
		if (!$this->admin_demo()) {
			$this->body .= "<div class='center'><input type=submit name=\"auto_save\" value=\"Save\"></div>";
		}
		$this->body .= "<div class='center'><a href='index.php?mc=listing_allowed_uploads&page=uploads_new_type' class='mini_button'>Add New File Type</a></div>";
		$this->body .= "</div></fieldset></form>";
		return true;
	} //end of function file_types_form

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

	function update_file_types($db,$type_info=0)
	{
		if ($type_info)
		{
			//file type accepted
			if ((is_array($type_info)) && (count($type_info)))
			{
				reset($type_info);
				foreach ($type_info as $key => $value)
				{
					$sql_query = "update ".$this->file_types_table." set
						accept = ".$value."
						where file_type_id = ".$key;
					$type_result = $this->db->Execute($sql_query);
					if (!$type_result)
					{
						$this->site_error($this->db->ErrorMsg());
						return false;
					}
				}
				return true;
			}
			else
				return false;
		}
		else
		{
			return false;
		}

	} //end of function update_file_types

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

	function new_file_types_form()
	{
		$admin = geoAdmin::getInstance();

		$toolTip['mime_type'] = geoHTML::showTooltip('File Mime Type', 'This tag is passed to the server by the uploading computer to let the server know what kind of file is being uploaded. There are several places to identify this tag. These mime types are constantly changing so if you do not find the one you are looking for in the list perform a search for "mime-types" through an Internet search engine.');
		$toolTip['icon'] = geoHTML::showTooltip('Icon to Use', 'Many file types cannot be displayed within a browser. Displaying some others in a browser is undesireable because of the size of the file. Provide the url of an icon that will be shown instead.');
		$toolTip['ext'] = geoHTML::showTooltip('Extension of File Type', 'Insert the file extension (ie. gif, jpg,...).');

		$sql = "select * from ".$this->ad_configuration_table;
		//echo $sql."<br>\n";
		$result = $this->db->Execute($sql);
		if (PHP5_DIR) $menu_loader = geoAdmin::getInstance();
			else $menu_loader =& geoAdmin::getInstance();


		if (!$result) {
			trigger_error("ERROR SQL: " . $this->db->ErrorMsg());
			$menu_loader->userError("Internal error. Please contact <a href='http://www.geodesicsolutions.com/support/index.htm'>support</a>.");
			$this->body .= $menu_loader->getUserMessages();
			return false;
		}
		if ( $result->RecordCount() == 1 ) {
			$this->ad_configuration_data = $result->FetchRow();
		} else {
			return false;
		}

		$tpl_vars = array();
		$tpl_vars['toolTip'] = $toolTip;
		$tpl_vars['adminMsgs'] = geoAdmin::m();
		$tpl_vars['geo_templatesDir'] = $admin->geo_templatesDir();

		geoView::getInstance()->setBodyTpl('allowed_uploads/newType.tpl')
			->setBodyVar($tpl_vars);
		return true;
	} // end of function new_file_types_form

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

	function insert_new_file_type($db,$file_type_info=0,$post_file=0)
	{
		if ($file_type_info) {
			//echo "post file<pre>".print_r($post_file,1)."</pre>";
			//echo $post_file[c]['size']." is size<bR>\n";
			//echo $post_file[c]['mime_type']." is type<bR>\n";
			//echo geoImage::getMimeType($post_file[c]['tmp_name'])." is mime type<bR>\n";
			//echo $post_file[c]['name']." is name<bR>\n";
			$mime_type = '';
			if (isset($post_file['c']['tmp_name']) && $post_file['c']['tmp_name'] && geoImage::getMimeType($post_file['c']['tmp_name'], '', $post_file['c']['mime_type'])) {
				//Pass in file uploaded, and mime type, so mime type used if not able to
				//determine type just by looking at contents of file.  Do not pass
				//in uploaded file name, as using that relies on mime types already in system,
				//which we don't want to do since we are adding a new mime type here.
				$mime_type = geoImage::getMimeType($post_file['c']['tmp_name'], '', $post_file['c']['mime_type']);
				//echo "mime type a:  $mime_type<br />";
			} else if ($post_file['c']['size'] > 0) {
				//get file type info from file
				$mime_type = trim($post_file[c]['type']);
				//echo "mime type b:  $mime_type<br />";
			} else {

				//echo "file info: <pre>".print_r($post_file,1)."</pre><br />";
			}
			if (!$mime_type) {
				//get file type info from form
				$mime_type = trim($file_type_info["mime_type"]);
				//echo "mime type c: $mime_type<br />";
			}

			if (strlen($mime_type) > 0) {
				//trim leading dots off extension
				$match = preg_match("/\.[a-zA-Z0-9]+/",$file_type_info["extension"]);
				$extension = ($match) ? substr_replace($file_type_info["extension"],'',0,1) : $file_type_info["extension"];

				$sql_query = "insert into ".$this->file_types_table."
					(name,mime_type,icon_to_use,accept,extension)
					values
					(\"".$file_type_info["type_name"]."\",\"".$mime_type."\",\"".$file_type_info["icon_to_use"]."\",1,\"".$extension."\")";
				$result = $this->db->Execute($sql_query);
				//echo $sql_query."<bR>\n";
				if (!$result) {
					//echo $sql_query."<bR>\n";
					$this->site_error($this->db->ErrorMsg());
					return false;
				}
				return true;
			}
		}
		return false;

	} // end of function insert_new_file_type

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

	function delete_file_type($db,$type_id=0)
	{
		if ($type_id)
		{
			$sql_query = "delete from ".$this->file_types_table."
				where file_type_id = ".$type_id;
			$result = $this->db->Execute($sql_query);
			if (!$result)
			{
				$this->site_error($this->db->ErrorMsg());
				return false;
			}
			else
				return true;
		}
		else
			return false;

	} // end of function insert_new_file_type

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

	function ad_configuration_home($db)
	{
		if (PHP5_DIR) $menu_loader = geoAdmin::getInstance();
		else $menu_loader =& geoAdmin::getInstance();

		//need to make names consitent.
		$config_title = "Listing Setup";
		$item_name = "listing";
		$this->body .= "<script type=\"text/javascript\">";
		// Set title and text for tooltip
		$this->body .= "Text[1] = [\"Length in characters for description\", \"This setting defines the maximum length in characters that users will be allowed to enter into a listing's description.\"]\n
			Text[2] = [\"Email to SELLER upon successful listing placement\", \"Choosing yes will cause the system to send an email to the user when his/her listing becomes live (after paypal and credit card payment acceptance and when the other forms of payment are approved in the admin).\"]\n
			Text[3] = [\"Send email to Admin upon successful listing placement\", \"Choosing yes will cause the system to send an email to the Admin when a listing becomes live (after paypal and credit card payment acceptance and when the other forms of payment are approved in the admin).\"]\n
			Text[4] = [\"Send email to Admin when manual payment type chosen\", \"Choosing yes will cause the system to send an email to the Admin when a listing has been placed using a manual payment type. See the <em>Transactions &gt; Awaiting Approval</em> page when you recieve this email.\"]\n
			Text[5] = [\"Send email to user before listing expiration\", \"This setting defines the number of days before the pending expiration of a listing that the system will send an email to the user with notification of the upcoming expiration. If this value is set to 0, the email will not be sent at all.\"]\n
			Text[6] = [\"Send email to user before subscription expiration\", \"This setting defines the number of days before the pending expiration of a subscription that the system will send an email to the user with notification of the upcoming expiration. If this value is set to 0, the email will not be sent at all.\"]\n
			Text[7] = [\"Renewal Period (if 0 - no renewals)\", \"This setting defines the number of days from the <em>end</em> of a listing during which that listing may be renewed. If this is set to 0, no listing renewals will be accepted. Otherwise, this will be the number of days before <em>and</em> after listing expiration that the listing can be renewed.\"]\n
			Text[8] = [\"Upgrade Period (if 0 - no upgrades)\", \"This setting defines the number of days from the <em>beginning</em> of a listing during which that a user may place extra feature upgrades on that listing. If this value is set to 0, no upgrades will be allowed on any listing.<br /><br />The upgrades governed by this setting are: bolding, better placement, featured listings, and attention getters.<br /><br />The reason for putting a limit on the number of days to allow upgrades is that the listing is \\\"renewed\\\" when an upgrade is successfully added. This means the listing will be given its initially chosen duration from the date the upgrade was added.  Example: A user places a 30-day listing and wants to come back and add an extra feature after 3 days (lets assume you set this limit to 7 days). If the user successfully purchases the the upgrade, the extra features will be added to the listing and a new expiration date will be set -- 30 days from the upgrade date, making this particular listing run for 33 days. This allows the user take full advantage of the upgrade, which is purchased for the full initial duration of the listing.\"]\n
			Text[9] = [\"Place Listings Only in Terminal Categories\", \"Choosing yes will allow users to place listings only in categories that do not have subcategories attached to them. Choosing no will allow users to place listings in any category.\"]\n
			Text[10] = [\"Use rich text editor when placing a listing (for description field only)\", \"Choosing yes will allow the use of a Rich Text (WYSIWYG) Editor for the description field only. This will give the user an interface for automatically adding HTML formatting to his/her listing's description. This has the benefit of providing quick, easy to use formatting capabilities for the HTML gifted and for the HTML challenged.  DO NOT USE THIS IN CONJUNCTION WITH 'automatic line breaks on text areas'.  Note that the TinyMCE Rich Text Editor is provided under the terms of the GNU Lesser General Public License.  The source code is available in the tiny_mce folder of the js directory.\"]\n
			Text[11] = [\"Pop up listing display when browsing by category\", \"Choosing yes will cause a listing to open in a new window when a user clicks to view it.\"]\n
			Text[12] = [\"Category order within place a listing and edit category processes\", \"Choosing 'Alphabetical' will cause all categories to display in alphabetical order. Otherwise, you may manage the display order of each category individually from the Categories section of the admin.\"]\n
			Text[13] = [\"How voting system is used for listings\", \"This setting defines the behavior of the listings voting system. The voting system allows the user to place a vote of recommend, ok, or not recommend on an individual listing. According to the setting you choose, the system will: Allow each unique IP to vote once on each listing, allow each registered user to place a vote on each listing, or allow each registered user with a unique IP to place a vote on each listing.  Each vote must come with a rating, title and comment before it is logged. The comments may then be viewed by accessing a link on the listing dispay page.\"]\n
			Text[14] = [\"Number of vote comments per page\", \"This setting defines the number of votes displayed on a page.\"]\n
			Text[15] = [\"Number of columns used to display categories during listing process\",\"This setting defines the number of columns used to display the categories on the 'choose category' page during the listing placement process.\"]\n
			Text[16] = [\"Number of columns to display checkbox category specific questions in\",\"Choosing a setting of 2 or higher will cause the category specific questions to be displayed in multiple columns on the detail display page.  If set to 0 or 1, the questions will be displayed in one column.\"]\n
			Text[17] = [\"Reset start date on Upgrade or Renewal\",\"Choosing yes will cause a listing upgraded or renewed by the seller to be considered a new listing. Such a listing will, therefore, be placed at the top of the newest listings tables on the site.  If it is an upgrade, the end time will also be adjusted as if the user just now placed the listing.\"]\n
			Text[18] = [\"Frequency to send expiration email after first:\",\"This setting defines the frequency of days after the initial expiration notification email is sent that another expiration notification will be sent.\"]\n
			Text[19] = [\"Send email to Admin upon expiration of listing or subscription:\",\"This setting defines whether an email will be sent to the admin when a subscription or listing expires.<br /><br /><strong>NOTE:</strong>This switch is entirely dependent upon the settings you choose for the next three settings below.<br/><strong>If no email is sent to the client, then no email will be sent to the admin.</strong><br />This is to save confusion about the frequency since the Admin email will be sent at the same time(s) as the user's.\"]\n

			Text[20] = [\"Allow Standard Auctions\", \"Choosing yes will allow your users to use the standard auction format for their auctions.\"]\n
			Text[21] = [\"Allow Dutch Auctions\", \"Choosing yes will allow your users to use the dutch auction format for their auctions.\"]\n
			Text[22] = [\"Display Bid History Link\", \"This setting defines when the bid history link will be displayed.\"]\n
			Text[23] = [\"Allow for Blacklisting Buyers\", \"Choosing yes will allow a user to maintain a blacklist of buyers who are not allowed to access his/her auctions.\"]\n
			Text[24] = [\"Allow for Inviting Buyers\", \"Choosing yes will allow a user to maintain an invited list of buyers who are the only users allowed to access his/her auctions.\"]\n
			Text[25] = [\"Allow Auction Start Rime\", \"This setting defines whether the Auction Start Date field will be displayed. If \\\"no,\\\" then Auction Start Date will default to current time.  If \\\"yes\\\" and 'Allow auction end time' is set to \\\"no,\\\" then Duration (defaulted to minimum value set by client) will be added to Auction Start Date or current time (whichever is greater) for calculation of Auction End Date.\"]\n
			Text[26] = [\"Allow Auction End Time\", \"This setting defines whether the Auction End Date field will be displayed. If \\\"yes\\\" and the value set by client is less than or equal to current time, then Duration (if set to anything other than zero/null which is the default when this switch is \\\"yes\\\") will be added to Auction Start Date or current time (whichever is greater if 'Allow auction start time' is set to \\\"yes\\\") to create Auction End Date.  Otherwise, ANY value greater than or equal to current time will OVERRIDE ANY Duration setting.\"]\n
			Text[27] = [\"Display Buy Now as a Choice\", \"This setting defines whether the 'buy now' option will be available until the reserve price is met or will go away after the first bid is placed.\"]\n
			Text[28] = [\"Allow editing of a live auction\", \"Choosing yes will allow a user to edit an auction after its creation but before the first bid is placed. Otherwise, an auction will not be editable at all after it is created.\"]\n
			Text[29] = [\"Allow viewing of an auction before begins\", \"This setting, in conjunction with 'allow auction start time,' defines whether an auction may be viewed after it is placed but before the auction starts.\"]\n
			Text[30] = [\"<nobr>Extend an auction by ? when a bid is made within ?</nobr>\",\"These settings define how long an auction will be extended if a new bid is received within a chosen amount of time from the auction's end.<ul><li>Set the first dropdown to the amount of time to extend an auction.<br /><strong>IMPORTANT: This setting is entirely dependent upon the second dropdown.</strong></li><li>Set the second dropdown to the amount of time before an auction ends that a bidder can extend an auction. This will happen automatically if the bid is made within the chosen time period. <BR><strong>IMPORTANT: Setting the second value to 0 will disable this feature.</strong></li></ul>\"]\n
			Text[31] = [\"Auction removal permissions\", \"This setting defines whether a user will be allowed to delete his/her own live auctions.\"]\n
			Text[32] = [\"Maximum word width within entered data\",\"This setting defines the maximum width in characters of a single word in user-entered data. Choosing a maximum width will protect your design layout from veeeeeeeeeeerrrrrrrrrrryyyyyyyyy looooooonnnnnnngggggg words. HTML does not break words across lines, so someone playing around can inadvertently throw your HTML tables out of format with a word that is wider than the table it is in. <br /><br /><strong>NOTE:</strong> URLs are considered single words for the purpose of this setting, so choose a maximum as wide as you think is permissible.\"]\n
			Text[33] = [\"Allow editing of live auction prices\", \"Choosing yes will allow a user to modify the starting bid, reserve price, and buy now price of an auction after its creation but before the first bid is placed. Otherwise, an auction's prices will not be editable after the auction is created.\"]\n
			Text[34] = [\"Popup full size image when thumbnail is clicked while browsing\",\"Choosing yes will cause an image to open at its full size in a new window if its thumbnail is clicked while browsing category and search results, mirroring the functionality of clicking on a thumbnail within the ad details page.\"]\n
			Text[35] = [\"( Enterprise Only ) Popup full size image when thumbnail is clicked while browsing\",\"Choosing yes will cause an image to open at its full size in a new window if its thumbnail is clicked while browsing category and search results, mirroring the functionality of clicking on a thumbnail within the ad details page.\"]\n
			Text[36] = [\"Category Icons with Place a Listing Process\", \"Setting this to <em>Show</em> will display the category images (as set in the category setup for each category), on the listing step where the user selects which category to use.\"]\n
			Text[37] = [\"Number of Columns to display questions within place a listing process\", \"Affects the display of the category specific question type questions within the listing collect details form of the place a listing process.  Does not affect other question types. One column will be displayed with this set to 0 or 1.  Otherwise the number of columns set will be used\"]
			Text[38] = [\"Bump Feature\", \"Allows the seller to reset the start time of their listing.   Resetting the start time when display order is newest first pushes that listing to the top of the category browsing results until newer listings.<br>0 - is off<br>1+ - Button first appears this number of days after placement.  Then reappears this number of days after the seller last clicked this button.  <br><b>NOTE:  ONLY USEFUL IF CATEGORY BROWSING DEFAULT DISPLAY ORDER IS NEWEST LISTINGS FIRST</B>\"]
			";
		//".$this->show_tooltip(20,1)."

		// Set style for tooltip
		$this->body .= "</script>";

		$sql_query = "select * from ".$this->ad_configuration_table;
		$result = $this->db->Execute($sql_query);
		if (!$result)
		{
			$menu_loader->userError();
			$this->body .= $menu_loader->getUserMessages();
			trigger_error("ERROR SQL: " . $this->db->ErrorMsg());
			return false;
		}
		elseif ($result->RecordCount() == 1)
		{
			$show_configuration = $result->FetchRow();
			$this->row_count = 0;
			$this->body .= "
				<script type=\"text/javascript\">
					function validate(field,max)
					{
						max=(max)?max:65535;
						if (!(field.value>=0 && field.value<=max))
						{
							alert('Must be between 0 and '+max+'. Values outside this range as well as invalid characters will not be submitted.');
							field.value=\"\";
							field.focus();
						}
					}
					function check_all(elements,col)
					{
						for(x = 0; x < elements.length; x++)
						{
							if(elements[x].id == col && !elements[x].disabled)
								elements[x].checked=elements[col+'_all'].checked;
							if(elements[x].id == col+'_section' && !elements[x].disabled)
								elements[x].checked=elements[col+'_all'].checked;
						}
					}
				</script>";
			if (!$this->admin_demo()) {
				$this->body .= "<form action=index.php?mc=listing_setup&page=listing_general_settings method=post class=\"form-horizontal form-label-left\">";
			} else {
				$this->body .= "<div class='form-horizontal'>";
			}
			$this->body .= $menu_loader->getUserMessages();

			if (geoPC::is_print()) {
				require_once(ADMIN_DIR.'print_settings.php');
				$print = Singleton::getInstance('printSettings');
				$this->body .= $print->listing_settings_display();
			}

			$this->body .= "
			<fieldset>
				<legend>Listing Process and Display Settings</legend>
				<div class='x_content'>";

			$sql_query = "select * from ".$this->site_configuration_table;
			$result = $this->db->Execute($sql_query);
			if (!$result)
			{
				$this->site_error($this->db->ErrorMsg());
				return false;
			}
			elseif ($result->RecordCount() == 1)
			{
				$show_site_configuration = $result->FetchRow();

				//place listings in terminal categories only
				$this->body .= "<div class='form-group'>";
				$this->body .= "<label class='control-label col-md-5 col-sm-5 col-xs-12'>Terminal Categories Only: ".$this->show_tooltip(9,1)."</label>";
				$this->body .= "<div class='col-md-6 col-sm-6 col-xs-12'>
				<input type=radio name=b[place_ads_only_in_terminal_categories] value=1 ";
								if ($show_site_configuration["place_ads_only_in_terminal_categories"] == 1)
									$this->body .= "checked";
								$this->body .= "> Yes&nbsp;&nbsp;<input type=radio name=b[place_ads_only_in_terminal_categories] value=0 ";
								if ($show_site_configuration["place_ads_only_in_terminal_categories"] == 0)
									$this->body .= "checked";
				$this->body .= "> No";
				$this->body .= "</div>";
				$this->body .= "</div>";

				//Use WYSIWYG Editor
				$this->body .= "<div class='form-group'>";
				$this->body .= "<label class='control-label col-md-5 col-sm-5 col-xs-12'>Use WYSIWYG Editor: ".$this->show_tooltip(10,1)."<br /><span class='small_font'>(description field only)</span></label>";
				$this->body .= "<div class='col-md-6 col-sm-6 col-xs-12'>
				<input type=radio name=b[use_rte] value=1 ";
								if ($show_site_configuration["use_rte"] == 1)
										$this->body .= "checked";
								$this->body .= "> Yes&nbsp;&nbsp;<input type=radio name=b[use_rte] value=0 ";
								if ($show_site_configuration["use_rte"] == 0)
										$this->body .= "checked";
				$this->body .= "> No";
				$this->body .= "</div>";
				$this->body .= "</div>";

				//Description Field Position
				$this->body .= "<div class='form-group'>";
				$this->body .= "<label class='control-label col-md-5 col-sm-5 col-xs-12'>Description Field Position on Page
									".geoHTML::showTooltip('Description Field Position', 'Controls whether the description field appears near the top or bottom of the details collection page')."</label>
								<div class='col-md-6 col-sm-6 col-xs-12'>
									<input type='radio' name='b[display_description_last_in_form]' value='0' ".($this->db->get_site_setting('display_description_last_in_form') ? '' : "checked='checked'")." /> Top<br />
									<input type='radio' name='b[display_description_last_in_form]' value='1' ".($this->db->get_site_setting('display_description_last_in_form') ? "checked='checked'" : '')." /> Bottom";
				$this->body .= "</div>";
				$this->body .= "</div>";


				//Category Order Display
				$this->body .= "<div class='form-group'>";
				$this->body .= "<label class='control-label col-md-5 col-sm-5 col-xs-12'>Category Order Display: ".$this->show_tooltip(12,1)."</label>";
				$this->body .= "<div class='col-md-6 col-sm-6 col-xs-12'>
				<input type=radio name=b[order_choose_category_by_alpha] value=1 ";
				if ($show_site_configuration["order_choose_category_by_alpha"] == 1)
						$this->body .= "checked";
				$this->body .= "> Alphabetically<br><input type=radio name=b[order_choose_category_by_alpha] value=0 ";
				if ($show_site_configuration["order_choose_category_by_alpha"] == 0)
						$this->body .= "checked";
				$this->body .= "> By display order set within categories setup";
				$this->body .= "</div>";
				$this->body .= "</div>";

				// number of columns to show in place listing process
				$this->body .= "<div class='form-group'>";
				$this->body .= "<label class='control-label col-md-5 col-sm-5 col-xs-12'>Category Columns Displayed: ".$this->show_tooltip(15,1)."</label>";
				$this->body .= "<div class='input-group col-md-6 col-sm-6 col-xs-12'>
				<select name=b[sell_category_column_count] class='form-control col-md-7 col-xs-12'> ";
				for ($i=1;$i<=5;$i++)
				{
					$this->body .= "<option ";
					if ($i == $show_site_configuration["sell_category_column_count"])
					$this->body .= "selected";
					$this->body .= ">".$i."</option>";
				}
				$this->body .= "</select> <div class='input-group-addon'>columns</div>";
				$this->body .= "</div>";
				$this->body .= "</div>";

				//Category Images
				$this->body .= "<div class='form-group'>";
				$this->body .= "<label class='control-label col-md-5 col-sm-5 col-xs-12'>Category Images: ".$this->show_tooltip(36,1)."</label>
								<div class='col-md-6 col-sm-6 col-xs-12'>
									<input type=\"radio\" name=\"b[display_cat_image_listing_process]\" value=\"1\" ".(($this->db->get_site_setting('display_cat_image_listing_process'))? 'checked="checked" ': '')."> Show<br />
									<input type=\"radio\" name=\"b[display_cat_image_listing_process]\" value=\"0\" ".(($this->db->get_site_setting('display_cat_image_listing_process'))? '': 'checked="checked" ')."> Hide";
				$this->body .= "</div>";
				$this->body .= "</div>";

				//Category Descriptions
				$this->body .= "<div class='form-group'>";
				$this->body .= "<label class='control-label col-md-5 col-sm-5 col-xs-12'>Category Descriptions: ".$this->show_tooltip(36,1)."</label>
								<div class='col-md-6 col-sm-6 col-xs-12'>
									<input type=\"radio\" name=\"b[display_cat_description_listing_process]\" value=\"1\" ".(($this->db->get_site_setting('display_cat_description_listing_process'))? 'checked="checked" ': '')."> Show<br />
									<input type=\"radio\" name=\"b[display_cat_description_listing_process]\" value=\"0\" ".(($this->db->get_site_setting('display_cat_description_listing_process'))? '': 'checked="checked" ')."> Hide";
				$this->body .= "</div>";
				$this->body .= "</div>";

				if(geoPC::is_ent()) {
					// Voting system
					$this->body .= "<div class='form-group'>";
					$this->body .= "<label class='control-label col-md-5 col-sm-5 col-xs-12'>Vote Restriction Handling: ".$this->show_tooltip(13,1)."</label>";
					$this->body .= "<div class='col-md-6 col-sm-6 col-xs-12'>
					<select name=b[voting_system] class='form-control col-md-7 col-xs-12'> ";
					$this->body .= "<option value=1 ";
					if ($show_site_configuration["voting_system"] == 1) $this->body .= "selected";
					$this->body .= ">IP based discrimination</option>";
					$this->body .= "<option value=2 ";
					if ($show_site_configuration["voting_system"] == 2) $this->body .= "selected";
					$this->body .= ">User based</option>";
					$this->body .= "<option value=3 ";
					if ($show_site_configuration["voting_system"] == 3) $this->body .= "selected";
					$this->body .= ">IP and User based</option>";
					$this->body .= "</select>";
					$this->body .= "</div>";
					$this->body .= "</div>";

					// Number of vote comments
					$this->body .= "<div class='form-group'>";
					$this->body .= "<label class='control-label col-md-5 col-sm-5 col-xs-12'>Max Vote Comments Displayed: ".$this->show_tooltip(14,1)."</label>";
					$this->body .= "<div class='input-group col-md-6 col-sm-6 col-xs-12'>
					<select name=b[number_of_vote_comments_to_display] class='form-control col-md-7 col-xs-12'> ";
					for ($i = 0; $i<50; $i++)
					{
						$this->body .= "<option value=".$i;
						if ($show_site_configuration["number_of_vote_comments_to_display"] == $i)
							$this->body .= " selected";
						$this->body .= ">".$i."</option>";
					}
					$this->body .= "</select><div class='input-group-addon'>per page</div>";
					$this->body .= "</div>";
					$this->body .= "</div>";
				}


				// Number of checkbox columns
				$this->body .= "<div class='form-group'>";
				$this->body .= "<label class='control-label col-md-5 col-sm-5 col-xs-12'>Checkbox Columns Displayed in Listing Details Page: ".$this->show_tooltip(16,1)."</label>";
				$this->body .= "<div class='input-group col-md-6 col-sm-6 col-xs-12'>
				<select name=b[checkbox_columns] class='form-control col-md-7 col-xs-12'> ";
				for ($i = 0; $i<6; $i++)
				{
					$this->body .= "<option value=".$i;
					if ($show_site_configuration["checkbox_columns"] == $i)
						$this->body .= " selected";
					$this->body .= ">".$i."</option>";
				}
				$this->body .= "</select><div class='input-group-addon'>columns</div>";
				$this->body .= "</div>";
				$this->body .= "</div>";

				// Number of category specific columns displayed in listing details process
				$this->body .= "<div class='form-group'>";
				$this->body .= "<label class='control-label col-md-5 col-sm-5 col-xs-12'>Category Question Columns Displayed During Listing Process: ".$this->show_tooltip(37,1)."</label>";
				$this->body .= "<div class='input-group col-md-6 col-sm-6 col-xs-12'>
				<select name=b[listing_process_count_columns] class='form-control col-md-7 col-xs-12'> ";
				for ($i = 0; $i<6; $i++)
				{
				$this->body .= "<option value=".$i;
						if ($this->db->get_site_setting('listing_process_count_columns') == $i)
								$this->body .= " selected";
								$this->body .= ">".$i."</option>";
				}
				$this->body .= "</select><div class='input-group-addon'>columns</div>";
				$this->body .= "</div>";
				$this->body .= "</div>";

				// number values per "page"
				$this->body .= "<div class='form-group'>";
				$this->body .= "<label class='control-label col-md-5 col-sm-5 col-xs-12'>Multi-Level Fields Value Selection: </label>";
				$this->body .= "<div class='input-group col-md-6 col-sm-6 col-xs-12'>
				<input type='text' name='b[leveled_max_vals_per_page]' class='form-control col-md-7 col-xs-12' value='".$this->db->get_site_setting('leveled_max_vals_per_page')."' size='3' />";
				$this->body .= "<div class='input-group-addon'>values per page</div>";
				$this->body .= "</div>";
				$this->body .= "</div>";

				//max word length
				$this->body .= "<div class='form-group'>";
				$this->body .= "<label class='control-label col-md-5 col-sm-5 col-xs-12'>Max Word Length: ".$this->show_tooltip(32,1)."</label>";
				$this->body .= "<div class='input-group col-md-6 col-sm-6 col-xs-12'><select name=b[max_word_width] class='form-control col-md-7 col-xs-12'>";
				$this->body .= "<option value='0' ".(!$show_site_configuration["max_word_width"]?"selected='selected'":"").">no max</option>";
				for ($i=20;$i<=255;$i++)
				{
					$this->body .= "<option ";
					if ($show_site_configuration["max_word_width"] == $i)
						$this->body .= "selected='selected'";
					$this->body .= ">".$i."</option>\n\t\t";
				}
				$this->body .= "</select><div class='input-group-addon'>characters</div>";
				$this->body .= "</div>";
				$this->body .= "</div>";

          		// Pre-populate Billing Info
          		$this->body .= "<div class='form-group'>";
         		$this->body .= "<label class='control-label col-md-5 col-sm-5 col-xs-12'>Pre-populate Billing Info: </label>
                  <div class='col-md-6 col-sm-6 col-xs-12'>
                    <input type=\"radio\" name=\"b[populate_billing_info]\" value=\"1\" ".(($this->db->get_site_setting('populate_billing_info'))? 'checked="checked" ': '')." /> With User's Data<br />
                    <input type=\"radio\" name=\"b[populate_billing_info]\" value=\"0\" ".(($this->db->get_site_setting('populate_billing_info'))? '': 'checked="checked" ')." /> Blank Fields";
          		$this->body .= "</div>";
          		$this->body .= "</div>";

				//Public question/answer show per listing
          		$val = $this->db->get_site_setting('public_questions_to_show');
			    $this->body .= "<div class='form-group'>";
			    $this->body .= "<label class='control-label col-md-5 col-sm-5 col-xs-12'>Number of Public Questions/Answers to show per Listing:<br /><span class='small_font'>(0 to disable)</span></label>";
			    $this->body .= "<div class='col-md-6 col-sm-6 col-xs-12'><input type='text' name='b[public_questions_to_show]' class='form-control col-md-7 col-xs-12' value='$val' size='4' />";
			    $this->body .= "</div>";
          		$this->body .= "</div>";

				//pre-populate the tags so that {$tag_name} works?
				$val = $this->db->get_site_setting('pre_populate_listing_tags');
		  	    $this->body .= "<div class='form-group'>";
			    $this->body .= "<label class='control-label col-md-5 col-sm-5 col-xs-12'>Pre-Render {listing} Tags on Listing Details Page:
			    			<br /><span class='small_font'>(For backwards-compatibility, Allow using {\$tag_name} instead of {listing tag='tag_name'})</span></label>
			  		  <div class='col-md-6 col-sm-6 col-xs-12'>
			   			<input type='radio' name='b[pre_populate_listing_tags]' value='1'".(($val)? ' checked="checked"':'')." /> Yes&nbsp;&nbsp;
			  			<input type='radio' name='b[pre_populate_listing_tags]' value='0'".((!$val)? ' checked="checked"':'')." /> No";
			    $this->body .= "</div>";
			    $this->body .= "</div>";

				//Add nofollow to user entered links
				$val = $this->db->get_site_setting('add_nofollow_user_links');
		  	    $this->body .= "<div class='form-group'>";
			    $this->body .= "<label class='control-label col-md-5 col-sm-5 col-xs-12'>Add \"nofollow\" to User-Entered Links: </label>
			  		  <div class='col-md-6 col-sm-6 col-xs-12'>
							<input type='radio' name='b[add_nofollow_user_links]' value='1'".(($val)? ' checked="checked"':'')." /> Yes&nbsp;&nbsp;
							<input type='radio' name='b[add_nofollow_user_links]' value='0'".((!$val)? ' checked="checked"':'')." /> No";
			    $this->body .= "</div>";
			    $this->body .= "</div>";

				//open user entered links in new tab
				$val = $this->db->get_site_setting('open_window_user_links');
		  	    $this->body .= "<div class='form-group'>";
			    $this->body .= "<label class='control-label col-md-5 col-sm-5 col-xs-12'>Open User-Entered Links in New Tab: </label>
			  		  <div class='col-md-6 col-sm-6 col-xs-12'>
							<input type='radio' name='b[open_window_user_links]' value='1'".(($val)? ' checked="checked"':'')." /> Yes&nbsp;&nbsp;
							<input type='radio' name='b[open_window_user_links]' value='0'".((!$val)? ' checked="checked"':'')." /> No";
			    $this->body .= "</div>";
			    $this->body .= "</div>";

				$this->body .= "
						</div>
					</fieldset>";


			$this->body .= "
			<fieldset>
				<legend>Listing Management Settings</legend>
				<div class='x_content'>";

			$sql_query = "select * from ".$this->site_configuration_table;
			$result = $this->db->Execute($sql_query);
			if (!$result)
			{
				$this->site_error($this->db->ErrorMsg());
				return false;
			}
			elseif ($result->RecordCount() == 1)
			{
				$show_site_configuration = $result->FetchRow();

				// Renewal Period
          		$this->body .= "<div class='form-group'>";
          		$this->body .= "<label class='control-label col-md-5 col-sm-5 col-xs-12'>Renewal Period: ".$this->show_tooltip(7,1)."<br /><span class='small_font'>(enter 0 for no renewals)</label></span>";
          		$this->body .= "<div class='input-group col-md-6 col-sm-6 col-xs-12'><input type='text' name='b[days_to_renew]' size='4' class='form-control col-md-7 col-xs-12' value='{$show_site_configuration['days_to_renew']}' /><div class='input-group-addon'>days</div>";
          		$this->body .= "</div>";
          		$this->body .= "</div>";

				if(geoPC::is_ent()) {

				// Upgrade periods
          		$this->body .= "<div class='form-group'>";
          		$this->body .= "<label class='control-label col-md-5 col-sm-5 col-xs-12'>Upgrade Period: ".$this->show_tooltip(8,1)."<br /><span class='small_font'>(enter 0 for no upgrades)</label></span>";
          		$this->body .= "<div class='input-group col-md-6 col-sm-6 col-xs-12'><input type='text' name='b[days_can_upgrade]' class='form-control col-md-7 col-xs-12' value='{$show_site_configuration['days_can_upgrade']}' size='4' /><div class='input-group-addon'>days</div>";
          		$this->body .= "</div>";
          		$this->body .= "</div>";
				}

				if(geoPC::is_ent()) {
				// Reset start date
				$this->body .= "<div class='form-group'>";
				$this->body .= "<label class='control-label col-md-5 col-sm-5 col-xs-12'>Reset Start Date of Listing when Seller Edits: </label>
						 <div class='col-md-6 col-sm-6 col-xs-12'>
							<input type=radio name=b[edit_reset_date] value=1 ";
								if ($show_site_configuration["edit_reset_date"] == 1)
									$this->body .= "checked";
								$this->body .= "> Yes&nbsp;&nbsp;<input type=radio name=b[edit_reset_date] value=0 ";
								if ($show_site_configuration["edit_reset_date"] == 0)
									$this->body .= "checked";
								$this->body .= "> No";
				$this->body .= "</div>";
          		$this->body .= "</div>";
				}
			}

				// Allow Copying to a New listing
				$this->body .= "<div class='form-group'>";
				$this->body .= "<label class='control-label col-md-5 col-sm-5 col-xs-12'>Allow Copying to a New Listing: </label>";
				$setting = $this->db->get_site_setting('allow_copying_new_listing');
				$this->body .= "
						 <div class='col-md-6 col-sm-6 col-xs-12'>
							<input type='radio' name='b[allow_copying_new_listing]' value='1' ".($setting?'checked="checked"':'')." /> Yes&nbsp;&nbsp;
							<input type='radio' name='b[allow_copying_new_listing]' value='0' ".(!$setting?'checked="checked"':'')." /> No";
				$this->body .= "</div>";
          		$this->body .= "</div>";



				// Allow Additional Regions in Listings
				$this->body .= "<div class='form-group'>";
				$this->body .= "<label class='control-label col-md-5 col-sm-5 col-xs-12'>Allow Additional Regions in Listings: </label>";
				$this->body .= "<div class='col-md-6 col-sm-6 col-xs-12'>
							<input type=\"radio\" name=\"b[additional_regions_per_listing]\" value=\"1\" ".(($this->db->get_site_setting('additional_regions_per_listing'))? 'checked="checked" ': '')." /> Yes&nbsp;&nbsp;
							<input type=\"radio\" name=\"b[additional_regions_per_listing]\" value=\"0\" ".(($this->db->get_site_setting('additional_regions_per_listing'))? '': 'checked="checked" ')." /> No";
				$this->body .= "</div>";
          		$this->body .= "</div>";

				//Allow seller to reset their listings start date to now pushing their listing to the top of the category browsing
				//results if default listing display order is newest listings first
          		$this->body .= "<div class='form-group'>";
			  	$this->body .= "<label class='control-label col-md-5 col-sm-5 col-xs-12'>Allow Sellers to Bump Listings: ".$this->show_tooltip(38,1)."</label>";
			  	$this->body .= "<div class='input-group col-md-6 col-sm-6 col-xs-12'>
			  	<select name=b[bump_feature] class='form-control col-md-7 col-xs-12'> ";
			  	for ($i=0;$i<=100;$i++)
			  	{
					$this->body .= "<option ".(($i == $this->db->get_site_setting('bump_feature'))?'selected="selected"':'').">".$i."</option>";
			  	}
			  	$this->body .= "</select><div class='input-group-addon'>days</div>";
			  	$this->body .= "</div>";
			  	$this->body .= "</div>";

				$this->body .= '</div></fieldset>';


				if (geoMaster::is('auctions'))
				{
					$this->row_count = 0;
					$this->body .= "
					<fieldset>
						<legend>Auction Specific Settings</legend>
						<div class='x_content'>";
					
					// Allow Standard Auctions
          			$this->body .= "<div class='form-group'>";
          			$this->body .= "<label class='control-label col-md-5 col-sm-5 col-xs-12'>Allow Standard Auctions: </label>";
          			$this->body .= "
           			<div class='col-md-6 col-sm-6 col-xs-12'>
              			<input type=radio name=b[allow_standard] value=1 ";
							if ($show_site_configuration["allow_standard"] == 1)
								$this->body .= "checked";
							$this->body .= "> Yes&nbsp;&nbsp;<input type=radio name=b[allow_standard] value=0 ";
							if ($show_site_configuration["allow_standard"] == 0)
								$this->body .= "checked";
							$this->body .= "> No";
          			$this->body .= "</div>";
          			$this->body .= "</div>";

					// Allow Dutch Auctions
					
          			// Check for price plans having buy now only set
          			$sql = "select buy_now_only from ".$this->price_plan_table." WHERE `buy_now_only` = 1";
          			$result = $this->db->Execute($sql);
          			if($result && $result->RecordCount() > 0) {
          				$buyNowOnlyIsOn = true;
          			}
          			$this->body .= "<div class='form-group'>";
          			$dutchTT = ($buyNowOnlyIsOn) ? geoHTML::showTooltip('Allow Dutch Auctions', "The Dutch Auctions option is disabled because one or more of your price plans has the <strong>Buy Now Only</strong> Auctions option set. To enable Dutch Auctions, please disable <strong>Buy Now Only</strong> in all of your price plans")
          											: $this->show_tooltip(21,1);
          			$dutchDisabled = ($buyNowOnlyIsOn) ? "disabled='disabled'" : '';
          			$this->body .= "<label class='control-label col-md-5 col-sm-5 col-xs-12'>Allow Dutch Auctions: ".$dutchTT."</label>";
          			$this->body .= "
           			<div class='col-md-6 col-sm-6 col-xs-12'>
              			<input type=radio name=b[allow_dutch] $dutchDisabled value=1 ".($show_site_configuration["allow_dutch"] == 1 ? 'checked="checked"':'')." /> Yes
						&nbsp;&nbsp;
          				<input type=radio name=b[allow_dutch] $dutchDisabled value=0 ".($show_site_configuration["allow_dutch"] == 0 ? 'checked="checked"':'')." /> No";
          			$this->body .= "</div>";
          			$this->body .= "</div>";

					// Proxy Bids
          			$this->body .= "<div class='form-group'>";
          			$this->body .= "<label class='control-label col-md-5 col-sm-5 col-xs-12'>Proxy Bidding: </label>";
          			$this->body .= "
           			<div class='col-md-6 col-sm-6 col-xs-12'>
              			<input type=radio name=b[allow_proxy_bids] value='all' ";
							if ($this->db->get_site_setting('allow_proxy_bids')=='all') {
								$this->body .= "checked";
							}
							$this->body .= "> Enable Proxy Bidding<br />
						<input type=radio name=b[allow_proxy_bids] value='reserve_met' ";
							if ($this->db->get_site_setting('allow_proxy_bids')=='reserve_met') {
								$this->body .= "checked='checked' ";
							}
							$this->body .= "/> Proxy Bidding Only if Reserve is Met<br />
						<input type=radio name=b[allow_proxy_bids] value='0' ";
							if (!$this->db->get_site_setting('allow_proxy_bids')) {
								$this->body .= "checked='checked' ";
							}
					$this->body .= "/> Straight Bidding Only";
          			$this->body .= "</div>";
          			$this->body .= "</div>";

					// Bid Against Self
          			$this->body .= "<div class='form-group'>";
          			$this->body .= "<label class='control-label col-md-5 col-sm-5 col-xs-12'>Allow Bidding Against Self: </label>";
          			$this->body .= "
           			<div class='col-md-6 col-sm-6 col-xs-12'>
              			<input type=radio name=b[allow_bidding_against_self] value='1' ";
							if ($this->db->get_site_setting('allow_bidding_against_self')) {
								$this->body .= "checked";
							}
							$this->body .= "> Yes&nbsp;&nbsp;
									<input type=radio name=b[allow_bidding_against_self] value='0' ";
							if (!$this->db->get_site_setting('allow_bidding_against_self')) {
								$this->body .= "checked='checked' ";
							}
					$this->body .= "/> No";
          			$this->body .= "</div>";
          			$this->body .= "</div>";

					//Added for bid history link
          			$this->body .= "<div class='form-group'>";
          			$this->body .= "<label class='control-label col-md-5 col-sm-5 col-xs-12'>Display Bid History Link: ".$this->show_tooltip(22,1)."</label>";
          			$this->body .= "
           			<div class='col-md-6 col-sm-6 col-xs-12'>
              				<input type=radio name=b[bid_history_link_live] value=1 ";
						if ($show_site_configuration["bid_history_link_live"] == 1)
							$this->body .= "checked ";
						$this->body .= "> When listing goes Live<br><input type=radio name=b[bid_history_link_live] value=0 ";
						if ($show_site_configuration["bid_history_link_live"] == 0)
							$this->body .= "checked";
					$this->body .= "> After listing ends";
          			$this->body .= "</div>";
          			$this->body .= "</div>";

					//Added for Black list of buyers
          			if (geoPC::is_ent()){$this->body .= "<div class='form-group'>";
          			$this->body .= "<label class='control-label col-md-5 col-sm-5 col-xs-12'>Allow for Blacklisting Buyers: ".$this->show_tooltip(23,1)."</label>";
          			$this->body .= "
           			<div class='col-md-6 col-sm-6 col-xs-12'>
              				<input type=radio name=b[black_list_of_buyers] value=1 ";
						if ($show_site_configuration["black_list_of_buyers"] == 1)
							$this->body .= "checked ";
						$this->body .= "> Yes&nbsp;&nbsp;<input type=radio name=b[black_list_of_buyers] value=0 ";
						if ($show_site_configuration["black_list_of_buyers"] == 0)
							$this->body .= "checked";
					$this->body .= "> No";
          			$this->body .= "</div>";
          			$this->body .= "</div>";

					//Added for Inviting buyers
          			$this->body .= "<div class='form-group'>";
          			$this->body .= "<label class='control-label col-md-5 col-sm-5 col-xs-12'>Allow for Inviting Buyers: ".$this->show_tooltip(24,1)."</label>";
          			$this->body .= "
           			<div class='col-md-6 col-sm-6 col-xs-12'>
              				<input type=radio name=b[invited_list_of_buyers] value=1 ";
					if ($show_site_configuration["invited_list_of_buyers"] == 1)
						$this->body .= "checked ";
					$this->body .= "> Yes&nbsp;&nbsp;<input type=radio name=b[invited_list_of_buyers] value=0 ";
					if ($show_site_configuration["invited_list_of_buyers"] == 0)
						$this->body .= "checked";
					$this->body .= "> No";
          			$this->body .= "</div>";
          			$this->body .= "</div>";

					//Allow Auction Start Time
          			$this->body .= "<div class='form-group'>";
          			$this->body .= "<label class='control-label col-md-5 col-sm-5 col-xs-12'>Allow Auction Start Time: ".$this->show_tooltip(25,1)."</label>";
          			$this->body .= "
           			<div class='col-md-6 col-sm-6 col-xs-12'>
              				<input type=radio name=b[user_set_auction_start_times] value=1 ";
					if ($show_site_configuration["user_set_auction_start_times"] == 1)
						$this->body .= "checked ";
					$this->body .= "> Yes&nbsp;&nbsp;<input type=radio name=b[user_set_auction_start_times] value=0 ";
					if ($show_site_configuration["user_set_auction_start_times"] == 0)
						$this->body .= "checked";
					$this->body .= "> No";
          			$this->body .= "</div>";
          			$this->body .= "</div>";

					//Allow Auction End Time
          			$this->body .= "<div class='form-group'>";
          			$this->body .= "<label class='control-label col-md-5 col-sm-5 col-xs-12'>Allow Auction End Time: ".$this->show_tooltip(26,1)."</label>";
          			$this->body .= "
           			<div class='col-md-6 col-sm-6 col-xs-12'>
              				<input type=radio name=b[user_set_auction_end_times] value=1 ";
					if ($show_site_configuration["user_set_auction_end_times"] == 1)
						$this->body .= "checked ";
					$this->body .= "> Yes&nbsp;&nbsp;<input type=radio name=b[user_set_auction_end_times] value=0 ";
					if ($show_site_configuration["user_set_auction_end_times"] == 0)
						$this->body .= "checked";
					$this->body .= "> No";
          			$this->body .= "</div>";
          			$this->body .= "</div>";}

					//Display Buy Now Price as a Choice
         			$this->body .= "<div class='form-group'>";
          			$this->body .= "<label class='control-label col-md-5 col-sm-5 col-xs-12'>Display \"Buy Now\" Price as a Choice: ".$this->show_tooltip(27,1)."</label>";
          			$this->body .= "
           			<div class='col-md-6 col-sm-6 col-xs-12'>
              				<input type=radio name=b[buy_now_reserve] value=1 ";
					if ($show_site_configuration["buy_now_reserve"] == 1)
						$this->body .= "checked ";
					$this->body .= "> Until Reserve Met<br><input type=radio name=b[buy_now_reserve] value=0 ";
					if ($show_site_configuration["buy_now_reserve"] == 0)
						$this->body .= "checked";
					$this->body .= "> Until first bid";
          			$this->body .= "</div>";
          			$this->body .= "</div>";

					// Allow Editing of a Live Auction
         			if (geoPC::is_ent()){$this->body .= "<div class='form-group'>";
          			$this->body .= "<label class='control-label col-md-5 col-sm-5 col-xs-12'>Allow Editing of a Live Auction: ".$this->show_tooltip(28,1)."</label>";
          			$this->body .= "
           			<div class='col-md-6 col-sm-6 col-xs-12'>
              				<input type=radio name=b[edit_begin] value=1 ";
					if ($show_site_configuration["edit_begin"] == 1)
						$this->body .= "checked ";
					$this->body .= "> No&nbsp;&nbsp;<input type=radio name=b[edit_begin] value=0 ";
					if ($show_site_configuration["edit_begin"] == 0)
						$this->body .= "checked";
					$this->body .= "> Yes, until first bid<br />

						<input type='checkbox' name='b[edit_begin_bno]' value='1' ".($this->db->get_site_setting('edit_begin_bno')?"checked='checked'":"")." />
						Always for Buy Now Only auctions with multiple quantity";
          			$this->body .= "</div>";
          			$this->body .= "</div>";

					//Allow Editing of Live Auction Prices

					$this->body .= "<div class='form-group'>";
          			$this->body .= "<label class='control-label col-md-5 col-sm-5 col-xs-12'>Allow Editing of Live Auction Prices: ".$this->show_tooltip(33,1)."</label>";
          			$this->body .= "
           			<div class='col-md-6 col-sm-6 col-xs-12'>
              				<input type=radio name=b[edit_auction_prices] value=0 ";
					if ($this->db->get_site_setting("edit_auction_prices") == 0)
						$this->body .= "checked ";
					$this->body .= "> No&nbsp;&nbsp;<input type=radio name=b[edit_auction_prices] value=1 ";
					if ($this->db->get_site_setting("edit_auction_prices") == 1)
						$this->body .= "checked";
					$this->body .= "> Yes, until first bid<br />

						<input type='checkbox' name='b[edit_auction_prices_bno]' value='1' ".($this->db->get_site_setting('edit_auction_prices_bno')?"checked='checked'":"")." />
						Always for Buy Now Only auctions with multiple quantity";
          			$this->body .= "</div>";
          			$this->body .= "</div>";


					//Extend an Auction by
					$this->body .= "<div class='form-group'>";
					$this->body .= "<label class='control-label col-md-5 col-sm-5 col-xs-12'>Extend an Auction by: ".$this->show_tooltip(30,1)."</label>";
					$this->body .= "<div class='input-group col-md-6 col-sm-6 col-xs-12'>
					<select name=b[auction_extension] class='form-control col-md-7 col-xs-12'>";
					for ($i = 0; $i<=60; $i++)
					{
						$this->body .= "
									<option value=".$i.(($show_site_configuration['auction_extension'] == $i) ? " selected" : "").">".$i."</option>";
					}
					$this->body .= "
								</select><div class='input-group-addon'>minute(s)</div>";
					$this->body .= "</div>";
					$this->body .= "</div>";

					//when a Bid is made within
					$this->body .= "<div class='form-group'>";
					$this->body .= "<label class='control-label col-md-5 col-sm-5 col-xs-12'>. . . when a Bid is made within: </label>";
					$this->body .= "<div class='input-group col-md-6 col-sm-6 col-xs-12'>
					<select name=b[auction_extension_check] class='form-control col-md-7 col-xs-12'>";
					for ($i = 1; $i<=60; $i++)
					{
						$this->body .= "
									<option value=".$i.(($show_site_configuration['auction_extension_check'] == $i) ? " selected" : "").">".$i."</option>";
					}
					$this->body .= "
								</select><div class='input-group-addon'>minute(s) of the end of an auction</div>";
					$this->body .= "</div>";
					$this->body .= "</div>";}

					// Auction Removal Permissions
         			$this->body .= "<div class='form-group'>";
          			$this->body .= "<label class='control-label col-md-5 col-sm-5 col-xs-12'>Auction Removal Permissions: ".$this->show_tooltip(31,1)."</label>";
          			$this->body .= "
           			<div class='col-md-6 col-sm-6 col-xs-12'>
              				<input type=radio name=b[admin_only_removes_auctions] value=1 ";
					if ($show_site_configuration['admin_only_removes_auctions'] == 1)
						$this->body .= "checked ";
					$this->body .= "> Only allow admin to delete a live auction<br><input type=radio name=b[admin_only_removes_auctions] value=0 ";
					if ($show_site_configuration['admin_only_removes_auctions'] == 0)
						$this->body .= "checked";
					$this->body .= "> Allow a user to delete their own live auctions";
          			$this->body .= "</div>";
          			$this->body .= "</div>";

					$this->body .= "
						</div>
					</fieldset>";
				}

				if ( geoPC::is_ent() ) {
					$this->row_count = 0;
					$this->body .= "
					<fieldset>
						<legend>Popup Window Settings</legend>
						<div class='x_content'>";

					// Popup Listing Display when Browsing Categories
         			$this->body .= "<div class='form-group'>";
          			$this->body .= "<label class='control-label col-md-5 col-sm-5 col-xs-12'>Popup Listing Display when Browsing Categories: ".$this->show_tooltip(11,1)."</label>";
          			$this->body .= "
           			<div class='col-md-6 col-sm-6 col-xs-12'>
              				<input type=radio name=b[popup_while_browsing] value=1 ";
					if ($show_site_configuration["popup_while_browsing"] == 1)
							$this->body .= "checked";
					$this->body .= "> Yes&nbsp;&nbsp;<input type=radio name=b[popup_while_browsing] value=0 ";
					if ($show_site_configuration["popup_while_browsing"] == 0)
							$this->body .= "checked";
					$this->body .= "> No";
          			$this->body .= "</div>";
          			$this->body .= "</div>";

					// Size of Popup Window
         			$this->body .= "<div class='form-group'>";
          			$this->body .= "<label class='control-label col-md-5 col-sm-5 col-xs-12'>Size of Popup Window:</label>";
          			$this->body .= "
           			<div class='col-md-6 col-sm-6 col-xs-12'>
						<div class='input-group'>
							<div class='input-group-addon'>width (in pixels)</div>
              				<input type=text size=5 name=b[popup_while_browsing_width] class='form-control col-md-7 col-xs-12'
							value=".$show_site_configuration["popup_while_browsing_width"].">
						</div>
						<div class='input-group'>
							<div class='input-group-addon'>height (in pixels)</div>
							<input type=text size=5 name=b[popup_while_browsing_height] class='form-control col-md-7 col-xs-12'
							value=".$show_site_configuration["popup_while_browsing_height"].">
						</div>";
          			$this->body .= "</div>";
          			$this->body .= "</div>";

					// Popup Full-Size Image on Thumbnail Click
         			$this->body .= "<div class='form-group'>";
          			$this->body .= "<label class='control-label col-md-5 col-sm-5 col-xs-12'>Popup Full-Size Image on Thumbnail Click: ".$this->show_tooltip(34,1)."</label>";
          			$this->body .= "
           			<div class='col-md-6 col-sm-6 col-xs-12'>
              				<input type=radio name=b[popup_image_while_browsing] value=1 ";
					if ($show_site_configuration["popup_image_while_browsing"] == 1)
							$this->body .= "checked";
					$this->body .= "> Yes&nbsp;&nbsp;<input type=radio name=b[popup_image_while_browsing] value=0 ";
					if ($show_site_configuration["popup_image_while_browsing"] == 0)
							$this->body .= "checked";
					$this->body .= "> No";
          			$this->body .= "</div>";
          			$this->body .= "</div>";


					$this->body .= "
						<div>
					</fieldset>";
				}
			}

			$this->body .= "
			<fieldset>
				<legend>Expiration Settings</legend>
				<div><div class='page_note'>Use caution when changing these settings.</div>
				";

			$exp_settings = array (
				'cart_expire_user' => 'User Cart Expiration',
				'archive_listing_delay' => 'Archive Listing',
				'archive_age' => 'Remove Archived Listings',
				'order_data_age' => 'Remove Orders &amp; Items',
				'invoice_remove_age' => 'Remove Invoices &amp; Transactions',
				'messages_age' => 'Remove User Communications',
			);
			if (geoPC::is_ent()) {
				$exp_settings ['recurring_billing_data_age'] = 'Expired Recurring Billing';
			}

			//display in user-friendly format
			$day = 86400;
			$hour = 3600;
			$minute = 60;

			foreach ($exp_settings as $setting => $title) {
				$exp = (int)$this->db->get_site_setting($setting);

				$timeUnit = 1;
				if ($exp >= $day && $exp%$day == 0) {
					$timeUnit = $day;
				} else if ($exp >= $hour && $exp % $hour == 0) {
					$timeUnit = $hour;
				} else if ($exp >= $minute && $exp % $minute == 0) {
					$timeUnit = $minute;
				}

				$adjustedExpire = $exp / $timeUnit;
				$input = "";

				$this->body .= "<div class='form-group form-inline'>
									<label class='control-label col-md-5 col-sm-5 col-xs-12'>$title</label>
									<div class='input-group'>
										<div class='input-group-addon'>After</div>
										<input type='text' name='b[$setting]' class='form-control col-md-7 col-xs-12' size='5' value='$adjustedExpire' />
									</div>
									<div class='input-group'>
										<select name='b[{$setting}_unit]' class='form-control col-md-7 col-xs-12'>
											<option value='$day'".(($timeUnit == $day)? ' selected="selected" ': '').">Days</option>
											<option value='$hour'".(($timeUnit == $hour)? ' selected="selected" ': '').">Hours</option>
											<option value='$minute'".(($timeUnit == $minute)? ' selected="selected" ': '').">Minutes</option>
											<option value='1'".(($timeUnit == 1)? ' selected="selected" ': '').">Seconds</option>
										</select>
									</div>
								</div>";
				//$this->body .= geoHTML::addOption($title,'After '.$input);
			}

			$this->body .= "
				</div>
			</fieldset>
			";

			if (!$this->admin_demo()) {
				$this->body .= "<div style=\"text-align: center;\"><input type=submit name=\"auto_save\" value=Save></div></form>";
			} else {
				$this->body .= "</div>";
			}
		}
		return true;
	} //end of function ad_configuration_home

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

	function update_max_lengths($db,$length_info=0)
	{
		if ($length_info)
		{
			if (geoPC::is_print()) {
				require_once ADMIN_DIR . 'print_settings.php';
				$print = Singleton::getInstance('printSettings');
				$print->listing_settings_update();
			}

			//save whether category images are displayed
			$display_cat_image_listing_process = (isset($length_info['display_cat_image_listing_process']) && $length_info['display_cat_image_listing_process'])? 1 : false;
			$this->db->set_site_setting('display_cat_image_listing_process',$display_cat_image_listing_process);

			//save whether category descriptions are displayed
			$display_cat_description_listing_process = (isset($length_info['display_cat_description_listing_process']) && $length_info['display_cat_description_listing_process'])? 1 : false;
			$this->db->set_site_setting('display_cat_description_listing_process',$display_cat_description_listing_process);

			$leveled_max_vals_per_page = ($length_info['leveled_max_vals_per_page'])? (int)$length_info['leveled_max_vals_per_page'] : 100;
			$this->db->set_site_setting('leveled_max_vals_per_page', $leveled_max_vals_per_page);

			$this->db->set_site_setting("edit_begin_bno", $length_info["edit_begin_bno"]);
			$this->db->set_site_setting("edit_auction_prices_bno", $length_info["edit_auction_prices_bno"]);

			$this->db->set_site_setting("edit_auction_prices", $length_info["edit_auction_prices"]);
			$this->db->set_site_setting('populate_billing_info', (int)$length_info['populate_billing_info']);
			$this->db->set_site_setting('public_questions_to_show', (int)$length_info['public_questions_to_show']);

			$this->db->set_site_setting('listing_process_count_columns', (int)$length_info['listing_process_count_columns']);

			//additional_regions_per_listing
			$this->db->set_site_setting('additional_regions_per_listing', ((isset($length_info['additional_regions_per_listing']) && $length_info['additional_regions_per_listing'])? 1 : false));

			//bump listing/start time reset
			$this->db->set_site_setting('bump_feature', (int)$length_info['bump_feature']);

			//pre_populate_listing_tags
			$this->db->set_site_setting('pre_populate_listing_tags', ((isset($length_info['pre_populate_listing_tags']) && $length_info['pre_populate_listing_tags'])? 1 : false));

			//add_nofollow_user_links
			$this->db->set_site_setting('add_nofollow_user_links', ((isset($length_info['add_nofollow_user_links']) && $length_info['add_nofollow_user_links'])? 1 : false));
			//open_window_user_links
			$this->db->set_site_setting('open_window_user_links', ((isset($length_info['open_window_user_links']) && $length_info['open_window_user_links'])? 1 : false));

			$this->db->set_site_setting('allow_copying_new_listing', $length_info['allow_copying_new_listing']);
			$this->db->set_site_setting('allow_proxy_bids', ((isset($length_info['allow_proxy_bids']) && $length_info['allow_proxy_bids'])? trim($length_info['allow_proxy_bids']) : false));
			$this->db->set_site_setting('allow_bidding_against_self', ((isset($length_info['allow_bidding_against_self']) && $length_info['allow_bidding_against_self'])? 1 : false));

			$this->db->set_site_setting('display_description_last_in_form', ((isset($length_info['display_description_last_in_form']) && $length_info['display_description_last_in_form'])? 1 : false));


			//all expiration settings work the same
			$exp_names = array (
			'cart_expire_user',
			'archive_listing_delay',
			'archive_age',
			'order_data_age',
			'invoice_remove_age',
			'messages_age',
			);
			if (geoPC::is_ent()) {
				$exp_names[] = 'recurring_billing_data_age';
			}
			foreach ($exp_names as $exp_name) {
				$exp = intval($length_info[$exp_name] * $length_info[$exp_name.'_unit']);
				$this->db->set_site_setting($exp_name,$exp);
			}

			$sql = "update ".$this->site_configuration_table." set
				place_ads_only_in_terminal_categories = \"".$length_info["place_ads_only_in_terminal_categories"]."\",";
			if(geoPC::is_ent()) {
				$sql .= "voting_system = \"".$length_info["voting_system"]."\",
					days_can_upgrade = \"".(int)$length_info["days_can_upgrade"]."\",
					number_of_vote_comments_to_display = \"".$length_info["number_of_vote_comments_to_display"]."\",";
			}
			$sql .= "sell_category_column_count = \"".$length_info["sell_category_column_count"]."\",
				popup_while_browsing = \"".$length_info["popup_while_browsing"]."\",
				popup_image_while_browsing = \"".$length_info["popup_image_while_browsing"]."\",
				order_choose_category_by_alpha = \"".$length_info["order_choose_category_by_alpha"]."\",
				popup_while_browsing_width = \"".$length_info["popup_while_browsing_width"]."\",
				popup_while_browsing_height = \"".$length_info["popup_while_browsing_height"]."\",
				edit_reset_date = \"".$length_info['edit_reset_date']."\",
				use_rte = \"".$length_info["use_rte"]."\",
				checkbox_columns = \"".$length_info["checkbox_columns"]."\",
				days_to_renew = \"".(int)$length_info["days_to_renew"]."\",
				max_word_width = \"".$length_info["max_word_width"]."\",
				allow_standard = \"".$length_info["allow_standard"]."\",
				allow_dutch = \"".$length_info["allow_dutch"]."\",
				bid_history_link_live = \"".$length_info["bid_history_link_live"]."\",
				black_list_of_buyers = \"".$length_info["black_list_of_buyers"]."\",
				invited_list_of_buyers = \"".$length_info["invited_list_of_buyers"]."\",
				user_set_auction_start_times = \"".$length_info["user_set_auction_start_times"]."\",
				user_set_auction_end_times = \"".$length_info["user_set_auction_end_times"]."\",
				buy_now_reserve = \"".$length_info["buy_now_reserve"]."\",
				edit_begin = \"".$length_info["edit_begin"]."\",
				auction_extension = \"".$length_info["auction_extension"]."\",
				auction_extension_check = \"".$length_info["auction_extension_check"]."\",
				admin_only_removes_auctions = \"".$length_info['admin_only_removes_auctions']."\"
				";
			$update_result = $this->db->Execute($sql);
			//clear the settings cache
			geoCacheSetting::expire('configuration_data');
			//echo $sql."<br>\n";
			if (!$update_result)
			{
				$this->site_error($this->db->ErrorMsg());
				return false;
			}
			else
			{
				return true;
			}

		}
		else
		{
			return false;
		}
	} //end of function update_max_lengths
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

	function image_resize_table($db)
	{
		//Is this still used?

		//get listing configuration data
		$sql = "select * from ".$this->ad_configuration_table;
		//echo $sql."<br>\n";
		$result = $this->db->Execute($sql);
		if (!$result)
		{
			//echo $sql."<br>\n";
			return false;
		}
		elseif ($result->RecordCount() == 1)
		{
			$this->ad_configuration_data = $result->FetchRow();
		}
		else
		{
			return false;
		}
		//echo $this->ad_configuration_data["maximum_image_width"]." is the width<br>\n";
		//echo $this->ad_configuration_data["maximum_image_height"]." is the height<br>\n";
		$tables = array($this->images_urls_table);
		//$tables = array($this->images_urls_table);
		reset($tables);
		foreach ($tables as $value)
		{
			$sql = "select original_image_width,original_image_height, image_id from ".$value;
			//echo $sql."<br>\n";
			$image_result = $this->db->Execute($sql);
			if (!$image_result)
			{
				//echo $sql."<br>\n";
				return false;
			}
			elseif ($image_result->RecordCount() > 0)
			{
				while ($show_images = $image_result->FetchRow())
				{
					if (($show_images["original_image_width"] > $this->ad_configuration_data["maximum_image_width"]) && ($show_images["original_image_height"] > $this->ad_configuration_data["maximum_image_height"]))
					{
						$imageprop = ($this->ad_configuration_data["maximum_image_width"] * 100) / $show_images["original_image_width"];
						$imagevsize = ($show_images["original_image_height"] * $imageprop) / 100 ;
						$final_image_width = $this->ad_configuration_data["maximum_image_width"];
						$final_image_height = ceil($imagevsize);

						if ($final_image_height > $this->ad_configuration_data["maximum_image_height"])
						{
							$imageprop = ($this->ad_configuration_data["maximum_image_height"] * 100) / $show_images["original_image_height"];
							$imagehsize = ($show_images["original_image_width"] * $imageprop) / 100 ;
							$final_image_height = $this->ad_configuration_data["maximum_image_height"];
							$final_image_width = ceil($imagehsize);
						}
					}
					elseif ($show_images["original_image_width"] > $this->ad_configuration_data["maximum_image_width"])
					{
						$imageprop = ($this->ad_configuration_data["maximum_image_width"] * 100) / $show_images["original_image_width"];
						$imagevsize = ($show_images["original_image_height"] * $imageprop) / 100 ;
						$final_image_width = $this->ad_configuration_data["maximum_image_width"];
						$final_image_height = ceil($imagevsize);
					}
					elseif ($show_images["original_image_height"] > $this->ad_configuration_data["maximum_image_height"])
					{
						$imageprop = ($this->ad_configuration_data["maximum_image_height"] * 100) / $show_images["original_image_height"];
						$imagehsize = ($show_images["original_image_width"] * $imageprop) / 100 ;
						$final_image_height = $this->ad_configuration_data["maximum_image_height"];
						$final_image_width = ceil($imagehsize);
					}
					else
					{
						$final_image_width = $show_images["original_image_width"];
						$final_image_height = $show_images["original_image_height"];
					}

					$sql = "update ".$value." set
						image_width = ".$final_image_width.",
						image_height = ".$final_image_height."
						where image_id = ".$show_images["image_id"];
					//echo $sql."<br>\n";
					$update_result = $this->db->Execute($sql);
					if (!$update_result)
					{
						//echo $sql."<br>\n";
						return false;
					}
				}
			}
			else
			{
				return false;
			}
		}


	} //end of function image_resize_table

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

	function ad_extras_form()
	{
		$addons = geoAddon::getInstance();
		$attention_getters = $addons->isEnabled('attention_getters');
		$featured_levels = $addons->isEnabled('featured_levels');

		$this->body .= "<script type='text/javascript'>";
		// Set title and text for tooltip
		$this->body .= "Text[6] = [\"number of featured picture listings columns to display on featured listing pics page\", \"This determines number of columns that will be displayed on the featured listings page that only displays the featured listing pics. The pictures will be displayed at the size they would be displayed within the display of the listing itself.\"]\n
			Text[7] = [\"Number of Featured Picture Listings\", \"This determines the number of listings that will be displayed on any single page returned. Make sure that you keep in mind the number of columns you set above. If this number is not evenly divided by the above figure the last row of the results would not be filled in completely if there were at least this many or more featured listings in the result.\"]\n
			Text[8] = [\"Bolding\", \"Checking \\\"yes\\\" will allow the users placing listings to bold the title of their listings when their listings are displayed while browsing the listings. The price for this added feature will be set by the pricing plan attached to that user.\"]\n
			Text[9] = [\"Better Placement\", \"Checking \\\"yes\\\" will allow the users to put their listings at the top of the browsed categories when users are browsing. The \\\"better placed\\\" listings comes first in any browsed return from a category. The better placed listings themselves are arranged by date the listing was placed (from most recent to oldest).\"]\n
			Text[10] = [\"Attention Getters\", \"Checking \\\"yes\\\" will allow the users to purchase an attention getter to place in the front of their description within the listing browsing pages.\"]\n";
		if(!$attention_getters)
			$this->body .= "Text[10][1] += \"<p style=\\\"font-weight: bold;\\\">You must first enable the Attention Getters addon</p>\"\n";

		//".$this->show_tooltip(10,1)."

		$this->body .= "</script>";

		$sql = "select * from ".$this->site_configuration_table;
		$result = $this->db->Execute($sql);

		if (PHP5_DIR) $menu_loader = geoAdmin::getInstance();
		else $menu_loader =& geoAdmin::getInstance();


		if (!$result) {
			trigger_error("ERROR SQL: " . $this->db->ErrorMsg());
			$menu_loader->userError("Internal error. Please contact <a href='http://www.geodesicsolutions.com/support/index.htm'>support</a>.");
			$this->body .= $menu_loader->getUserMessages();
			return false;
		} elseif ( $result->RecordCount() == 1 ) {
			$this->body .= $menu_loader->getUserMessages();
			$this->body .= $body;
			$this->description .= $description;

			$show_configuration = $result->FetchRow();

			$sql_query = "select * from ".$this->ad_configuration_table;
			$result = $this->db->Execute($sql_query);
			if (!$result)
			{
				$this->site_error($this->db->ErrorMsg());
				return false;
			}
			elseif ($result->RecordCount() == 1)
			{
				$show_ad_configuration = $result->FetchRow();
			}

			if (!$this->admin_demo()) {
				$this->body .= "<form action=index.php?mc=listing_setup&page=listing_extras method=post class=\"form-horizontal form-label-left\">\n";
			} else {
				$this->body .= "<div class='form-horizontal'>";
			}
			$this->body .= "<fieldset id='FeatListingSettings'>
				<legend>Featured Listing Extra Settings</legend>
				<div class='x_content'><table cellpadding=3 cellspacing=1 border=0 align=center >\n";
			//$this->title = "Listing Setup > Listing Extras";
			$this->description = "Control which features your users can take advantage
				of for their listings through your site.  The price charged for each feature will be controlled by the price plan attached to that user.  Note that these simply activate
				the category specific features.  You will still have to decide which ones will go with each category.";

			//featured
			$this->body .= "
			<div class='form-group'>
			<label class='control-label col-md-6 col-sm-6 col-xs-12'>Enable Featured Listings: ".geoHTML::showTooltip('Enable Featured Listings', "Enables the Featured Listing Listing Extra.")."</label>
			  <div class='col-md-6 col-sm-6 col-xs-12'>
				<input type=radio name=b[use_featured_feature] value=1 ";
							if ($this->db->get_site_setting("use_featured_feature") == 1)
								$this->body .= "checked";
							$this->body .= "> Yes&nbsp;&nbsp;<input type=radio name=b[use_featured_feature] value=0 ";
							if ($this->db->get_site_setting("use_featured_feature") == 0)
								$this->body .= "checked";
			$this->body .= "> No
			  </div>
			</div>
			";

			//featured level 2
			$this->body .= "
			<div class='form-group'>
			<label class='control-label col-md-6 col-sm-6 col-xs-12'>Enable Featured Listings Level 2: ".
			geoHTML::showTooltip('Enable Featured Listings', "Enables the Featured Listing Level 2 Listing Extra.".(!$featured_levels?'<p style="font-weight: bold;">Requires the Featured Levels addon</p>':'')).
			"</label>
			";
			$this->body .= '
			  <div class="col-md-6 col-sm-6 col-xs-12">
				<input type="radio" name="b[use_featured_feature_2]" value="1"'.($this->db->get_site_setting('use_featured_feature_2')==1?' checked="checked"':'').(!$featured_levels?' disabled="disabled"':'').' /> Yes&nbsp;
				<input type="radio" name="b[use_featured_feature_2]" value="0"'.($this->db->get_site_setting('use_featured_feature_2')==0?' checked="checked"':'').(!$featured_levels?' disabled="disabled"':'').' /> No
			  </div>
			</div>
			';

			//featured level 3
			$this->body .= "
			<div class='form-group'>
			<label class='control-label col-md-6 col-sm-6 col-xs-12'>Enable Featured Listings Level 3: ".
			geoHTML::showTooltip('Enable Featured Listings', "Enables the Featured Listing Level 3 Listing Extra.".(!$featured_levels?'<p style="font-weight: bold;">Requires the Featured Levels addon</p>':'')).
			"</label>
			";
			$this->body .= '
			  <div class="col-md-6 col-sm-6 col-xs-12">
				<input type="radio" name="b[use_featured_feature_3]" value="1"'.($this->db->get_site_setting('use_featured_feature_3')==1?' checked="checked"':'').(!$featured_levels?' disabled="disabled"':'').' /> Yes&nbsp;
				<input type="radio" name="b[use_featured_feature_3]" value="0"'.($this->db->get_site_setting('use_featured_feature_3')==0?' checked="checked"':'').(!$featured_levels?' disabled="disabled"':'').' /> No
			  </div>
			</div>
			';


			//featured level 4
			$this->body .= "
			<div class='form-group'>
			<label class='control-label col-md-6 col-sm-6 col-xs-12'>Enable Featured Listings Level 4: ".
			geoHTML::showTooltip('Enable Featured Listings', "Enables the Featured Listing Level 4 Listing Extra.".(!$featured_levels?'<p style="font-weight: bold;">Requires the Featured Levels addon</p>':'')).
			"</label>
			";
			$this->body .= '
			  <div class="col-md-6 col-sm-6 col-xs-12">
				<input type="radio" name="b[use_featured_feature_4]" value="1"'.($this->db->get_site_setting('use_featured_feature_4')==1?' checked="checked"':'').(!$featured_levels?' disabled="disabled"':'').' /> Yes&nbsp;
				<input type="radio" name="b[use_featured_feature_4]" value="0"'.($this->db->get_site_setting('use_featured_feature_4')==0?' checked="checked"':'').(!$featured_levels?' disabled="disabled"':'').' /> No
			  </div>
			</div>
			';

			//featured level 5
			$this->body .= "
			<div class='form-group'>
			<label class='control-label col-md-6 col-sm-6 col-xs-12'>Enable Featured Listings Level 5: ".
			geoHTML::showTooltip('Enable Featured Listings', "Enables the Featured Listing Level 5 Listing Extra.".(!$featured_levels?'<p style="font-weight: bold;">Requires the Featured Levels addon</p>':'')).
			"</label>
			";
			$this->body .= '
			  <div class="col-md-6 col-sm-6 col-xs-12">
				<input type="radio" name="b[use_featured_feature_5]" value="1"'.($this->db->get_site_setting('use_featured_feature_5')==1?' checked="checked"':'').(!$featured_levels?' disabled="disabled"':'').' /> Yes&nbsp;
				<input type="radio" name="b[use_featured_feature_5]" value="0"'.($this->db->get_site_setting('use_featured_feature_5')==0?' checked="checked"':'').(!$featured_levels?' disabled="disabled"':'').' /> No
			  </div>
			</div>
			';

			//columns on featured pics page
			$this->body .= "
			<div class='form-group'>
			<label class='control-label col-md-6 col-sm-6 col-xs-12'>Number of Columns on Featured Listing Pics Page: ".$this->show_tooltip(6,1)."<br>
				<span class=small_font>You currently have these sizes for those images:<br>
				maximum width: ".$show_ad_configuration["maximum_image_width"]." pixels<Br>
				maximum height: ".$show_ad_configuration["maximum_image_height"]." pixels</span></label>
			  <div class='col-md-6 col-sm-6 col-xs-12'>
			  <select name=b[featured_pic_ad_column_count] class='form-control col-md-7 col-xs-12'>";
			  			for ($i=1;$i <= 5; $i++)
			  			{
			  				$this->body .= "<option ";
			  				if ($this->db->get_site_setting("featured_pic_ad_column_count") == $i)
			  					$this->body .= "selected";
			  				$this->body .= ">".$i."</option>\n\t\t";
			  			}
			$this->body .= "</select>
			  </div>
			</div>
			";

			//featured listing count
			$this->body .= "
			<div class='form-group'>
			<label class='control-label col-md-6 col-sm-6 col-xs-12'>Max Number of Featured Listings to Display: ".$this->show_tooltip(7,1)."</label>
			  <div class='col-md-6 col-sm-6 col-xs-12'>
			  <select name=b[featured_ad_page_count] class='form-control col-md-7 col-xs-12'>";
			  			for ($i=1;$i <= 100; $i++)
			  			{
			  				$this->body .= "<option ";
			  				if ($this->db->get_site_setting("featured_ad_page_count") == $i)
			  					$this->body .= "selected";
			  				$this->body .= ">".$i."</option>\n\t\t";
			  			}
			$this->body .= "</select>
			  </div>
			</div>
			";


			$this->body .= "</table></div></fieldset>\n";


			$tpl_vars = array();
			$tpl_vars['prefix'] = $prefix = 'BetPla_';
			$tpl_vars['rotate'] = $this->db->get_site_setting($prefix.'rotate');
			$tpl_vars['maxBooths'] = $this->db->get_site_setting($prefix.'maxBooths');
			$tpl_vars['perCategory'] = $this->db->get_site_setting($prefix.'perCategory');

			$tpl_vars['use_better_placement_feature'] = $this->db->get_site_setting('use_better_placement_feature');
			$tpl_vars['cronKey'] = $this->db->get_site_setting('cron_key');

			//see interval
			$cron = geoCron::getInstance();
			$taskInfo = $cron->getTaskInfo('better_placement_rotation');
			$interval = (int)$taskInfo['interval'];

			//display in user-friendly format
			$day = $tpl_vars['day'] = 86400;
			$hour  = $tpl_vars['hour'] = 3600;
			$minute  = $tpl_vars['minute'] = 60;

			$timeUnit = 1;
			if ($interval >= $day && $interval%$day == 0) {
				$timeUnit = $day;
			} else if ($interval >= $hour && $interval % $hour == 0) {
				$timeUnit = $hour;
			} else if ($interval >= $minute && $interval % $minute == 0) {
				$timeUnit = $minute;
			}
			$tpl_vars['rotateUnit'] = $timeUnit;
			$tpl_vars['adjustedInterval'] = ($interval == -1)? 0 : ($interval / $timeUnit);

			$tpl = new geoTemplate();
			$tpl->assign($tpl_vars);

			$this->body .= $tpl->fetch('listing_extras.tpl');

			$this->body .= "<fieldset id='MiscExtraListingSettings'>
				<legend>Miscellaneous Listing Extra Settings</legend><table cellpadding=3 cellspacing=1 border=0 align=center width=\"100%\">\n";

			//bolding
			$this->body .= "
			<div class='form-group'>
			<label class='control-label col-md-6 col-sm-6 col-xs-12'>Enable Bolding: ".$this->show_tooltip(8,1)."</label>
			";
			$this->body .= "
			  <div class='col-md-6 col-sm-6 col-xs-12'>
				<input type=radio name=b[use_bolding_feature] value=1 ";
			if ($this->db->get_site_setting("use_bolding_feature") == 1)
				$this->body .= "checked";
			$this->body .= "> Yes&nbsp;&nbsp;<input type=radio name=b[use_bolding_feature] value=0 ";
			if ($this->db->get_site_setting("use_bolding_feature") == 0)
				$this->body .= "checked";
			$this->body .= "> No
			  </div>
			</div>
			";

			//attention getters
			$this->body .= "
			<div class='form-group'>
			<label class='control-label col-md-6 col-sm-6 col-xs-12'>Enable Attention Getters: ".$this->show_tooltip(10,1)."</label>
			";
			$this->body .= "
			  <div class='col-md-6 col-sm-6 col-xs-12'>
				<input type=radio name=b[use_attention_getters] value=1 ";
			if ($this->db->get_site_setting("use_attention_getters") == 1)
				$this->body .= "checked=\"checked\" ";
			if(!$attention_getters)
				$this->body .= "disabled=\"disabled\" ";
			$this->body .= "/> Yes&nbsp;&nbsp;<input type=radio name=b[use_attention_getters] value=0 ";
			if ($this->db->get_site_setting("use_attention_getters") == 0)
				$this->body .= "checked=\"checked\" ";
			if(!$attention_getters)
				$this->body .= "disabled=\"disabled\" ";
			$this->body .= "/> No
			  </div>
			</div>
			";

			$this->body .= "</table></fieldset>\n";
			$this->body .= "<table cellpadding=3 cellspacing=1 border=0 align=center >\n";
			$this->body .= "<tr>\n\t<td align=center class=medium_font colspan=3>\n\t<input type=submit name=\"auto_save\" value=\"Save\">\n\t</td>\n</tr>\n";
			$this->body .= "</table>\n";
			if(!$this->admin_demo()) {
				$this->body .= "</form>\n";
			} else {
				$this->body .= '</div>';
			}
			return true;

		}
		else
		{
			return false;
		}
	} //end of function ad_extras_form

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

	function update_ad_extras($db,$extras_info=0)
	{
		if ($extras_info)
		{
			$prefix = 'BetPla_';
			$extras_fields = array(
				'use_bolding_feature',
				'use_featured_feature',
				'use_attention_getters');
			if (geoPC::is_ent()){
				$extras_fields = array('use_bolding_feature',
					'use_featured_feature',
					'use_featured_feature_2',
					'use_featured_feature_3',
					'use_featured_feature_4',
					'use_featured_feature_5',
					'use_attention_getters');
			}
			foreach ($extras_info as $key => $val){
				if (in_array($key,$extras_fields)){
					$val = (intval($val))? 1 : false;
					$this->db->set_site_setting($key, $val);
				}
			}
			$this->db->set_site_setting('featured_ad_page_count', $extras_info['featured_ad_page_count']);
			$this->db->set_site_setting('featured_pic_ad_column_count', $extras_info['featured_pic_ad_column_count']);

			$this->db->set_site_setting('use_better_placement_feature', ((isset($extras_info['use_better_placement_feature']) && $extras_info['use_better_placement_feature'])? 1 : false));
			$this->db->set_site_setting($prefix.'rotate', ((isset($extras_info[$prefix.'rotate']) && $extras_info[$prefix.'rotate'])? 1 : false));
			if ($extras_info[$prefix.'rotate']) {
				//rotation is enabled, save rotation values

				//set interval by changing cron job interval.
				$interval = (int)($extras_info['rotationInterval'] * $extras_info['rotationIntervalUnit']);

				if ($interval <= 0) {
					//force to be a day
					$interval = 86400;
					geoAdmin::m('Invalid rotation interval specified, defaulting to rotate every day.', geoAdmin::NOTICE);
				}
				$cron = geoCron::getInstance();
				$cron->set('better_placement_rotation', geoCron::TYPE_MAIN, $interval);
				//save perCategory setting
				$this->db->set_site_setting($prefix.'perCategory', ((isset($extras_info[$prefix.'perCategory']) && $extras_info[$prefix.'perCategory'])? 1 : false));
			}
			return true;
		}
		else
		{
			return false;
		}

	} //end of function update_ad_extras

	private function _fixLowestBracket ()
	{
		$db = DataAccess::getInstance();

		$currentLowest = $db->GetOne ("SELECT MIN(`low`) FROM `geodesic_auctions_increments`");

		if ($currentLowest === null || $currentLowest === false) {
			//insert increment, somehow the table is empty
			$db->Execute("INSERT INTO `geodesic_auctions_increments` (`low`, `increment`) VALUES (0.00, 5.00)");
			geoAdmin::m("Added default bid increment to prevent errors in system, as it will not work properly unless there is at least one increment configured.  Click on the bid increment to edit the value.", geoAdmin::NOTICE);
		} else if ($currentLowest != 0.00) {
			$db->Execute("UPDATE `geodesic_auctions_increments` SET `low`=0.00 WHERE `low`='{$currentLowest}' LIMIT 1");
			geoAdmin::m("Automatically updated the first bid increment bracket to start at 0.00 to prevent errors in the system.", geoAdmin::NOTICE);
		}
	}

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

	function payment_types_form($db)
	{
		//file type accepted
		$this->get_configuration_data($db);

		$sql = "select * from ".$this->auction_payment_types_table." order by display_order, type_name";
		$type_result = $this->db->Execute($sql);
		if ($this->debug_auction) echo $sql."<bR>\n";

		if (PHP5_DIR) $menu_loader = geoAdmin::getInstance();
		else $menu_loader =& geoAdmin::getInstance();


		if (!$type_result) {
			trigger_error("ERROR SQL: " . $this->db->ErrorMsg());
			$menu_loader->userError("Internal error. Please contact <a href='http://www.geodesicsolutions.com/support/index.htm'>support</a>.");
			$this->body .= $menu_loader->getUserMessages();
			return false;
		} elseif ( $type_result->RecordCount() > 0 ) {
			$this->body .= $menu_loader->getUserMessages();
			$this->body .= $body;
			$this->description .= $description;

			if (!$this->admin_demo())$this->body .= "<form action=index.php?mc=listing_setup&page=listing_payment_types method=post>\n";
			$this->body .= "<fieldset id='CurrentPayTypes'>
				<legend>Payment Types Accepted (by Seller)</legend><div class='table-responsive'><table cellpadding=3 cellspacing=1 border=0 align=center width=450 class='table table-hover table-striped table-bordered'>\n";

			$this->body .= "<thead><tr class='col_hdr_top'>\n\t
				<td class=col_hdr_left>\n\t<strong>Payment Type</strong></font>\n\t</td>\n\t";
			$this->body .= "<td class=col_hdr align=center>\n\t <strong>Display Order</strong></font>\n\t</td>\n\t";
			$this->body .= "<td class=col_hdr_left align=center width=80>\n\t &nbsp;</font>\n\t</td>\n\t";
			$this->body .= "</tr></thead>\n";
			while ($show_types = $type_result->FetchNextObject())
			{
				$delete_button = geoHTML::addButton('Delete','index.php?mc=listing_setup&page=listing_payment_types&z='.$show_types->TYPE_ID.'&auto_save=1', false, '', 'lightUpLink mini_cancel');
				$this->body .= "<tr class=".$this->get_row_color().">\n\t<td class=medium_font>\n\t".$show_types->TYPE_NAME."</font>\n\t</td>\n\t";
				$this->body .= "<td class=medium_font align=center>\n\t".$show_types->DISPLAY_ORDER."</font>\n\t</td>\n\t";

				$this->body .= "<td align=center>".$delete_button."</td>";
				$this->body .= "</tr>\n";

				$this->row_count++;
			}
			$this->body .= "<tr>\n\t
				<td class=col_ftr align=center>New Payment Type: \n\t <input type=text name=b[type_name]></font>\n\t</td>\n\t";
			$this->body .= "<td class=col_ftr align=center> Display Order: \n\t<select name=b[display_order]>\n\t\t";
				for ($i=1;$i<101;$i++)
				{
					$this->body .= "<option>".$i."</option>";
				}
				$this->body .= "</select></font>\n\t</td>\n\t";
			if (!$this->admin_demo())
				$this->body .= "<td class=col_ftr align=center>\n\t <input type=submit name='auto_save' value=\"Save\"></font>\n\t</td></tr>\n\t";
			$this->body .= "</table></div></fieldset>\n";
			$this->body .= "</form>\n";

			return true;
		}
		else
		{
			if (!$this->admin_demo())$this->body .= "<form action=index.php?mc=listing_setup&page=listing_payment_types method=post>\n";
			$this->body .= "<fieldset id='CurrentPayTypes'>
				<legend>Payment Types Accepted (by Seller)</legend><table cellpadding=3 cellspacing=1 border=0 align=center  class=row_color2>\n";
			$this->body .= "<tr>\n\t
				<td class=col_hdr_left>\n\t Payment Type</font>\n\t</td>\n\t";
			$this->body .= "<td class=col_hdr>\n\t display order</font>\n\t</td>\n\t";
			$this->body .= "<td class=col_hdr>\n\t &nbsp;</font>\n\t</td>\n\t";
			$this->body .= "</tr>\n";
			$this->body .= "<tr>\n\t
				<td class=col_ftr>\n\t <input type=text name=b[type_name]></font>\n\t</td>\n\t";
			$this->body .= "<td class=col_ftr>\n\t<select name=b[display_order]>\n\t\t";
				for ($i=1;$i<101;$i++)
				{
					$this->body .= "<option>".$i."</option>";
				}
				$this->body .= "</select></font>\n\t</td>\n\t";
			if (!$this->admin_demo())
				$this->body .= "<td class=col_ftr>\n\t <input type=submit name=\"auto_save\" value=\"Save\"></font>\n\t</td>\n\t";
			$this->body .= "</table></fieldset>\n";
			$this->body .= "</form>\n";

			return true;
		}
	} //end of function payment_types_form

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

	function delete_payment_type($db,$type_id=0)
	{
		if ($type_id)
		{
			$sql = "delete from ".$this->auction_payment_types_table."
				where type_id = ".$type_id;
			$type_result = $this->db->Execute($sql);
			if (!$type_result)
			{
				$this->site_error($this->db->ErrorMsg());
				return false;
			}
			return true;
		}
		else
		{
			return false;
		}

	} //end of function delete_payment_type

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

	function insert_payment_type($db,$type_info=0)
	{
		if ($type_info)
		{
			$sql = "insert into ".$this->auction_payment_types_table."
				(type_name,display_order)
				values
				(\"".$type_info["type_name"]."\",\"".$type_info["display_order"]."\")";
			$type_result = $this->db->Execute($sql);
			if (!$type_result)
			{
				$this->site_error($this->db->ErrorMsg());
				return false;
			}
			return true;
		}
		else
		{
			return false;
		}

	} //end of function insert_payment_type

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
	function display_listing_general_settings()
	{
		$this->ad_configuration_home($this->db);
		$this->display_page();
	}
	function update_listing_general_settings()
	{
		return $this->update_max_lengths($this->db,$_REQUEST["b"]);
	}
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
	function display_listing_extras()
	{
		$this->ad_extras_form();
		$this->display_page();
	}
	function update_listing_extras()
	{
		return $this->update_ad_extras($this->db,$_REQUEST["b"]);
	}

	public function display_listing_bid_increments ()
	{
		$tpl_vars = array();

		$tpl_vars['increments'] = $this->db->GetAll("SELECT * FROM ".geoTables::increments_table." ORDER BY `low`");

		//get highest increment
		$highest = $this->db->GetRow("SELECT * FROM ".geoTables::increments_table." ORDER BY `low` DESC");

		$tpl_vars['nextLow'] = ($highest['low'])? ($highest['low']+$highest['increment']+0.01) : 5.00;
		$tpl_vars['nextIncrement'] = $highest['increment']+1.00;

		$tpl_vars['precurrency'] = $this->db->get_site_setting('precurrency');
		$tpl_vars['postcurrency'] = $this->db->get_site_setting('postcurrency');

		$tpl_vars['adminMsgs'] = geoAdmin::m();

		geoView::getInstance()->setBodyTpl('bid_increments.tpl')
			->setBodyVar($tpl_vars)
			->addJScript('js/bid_increments.js');
	}
	public function update_listing_bid_increments()
	{
		//may be deleting...

		$deleting = (array) $_POST['deleteBrackets'];

		foreach ($deleting as $low) {
			$this->db->Execute("DELETE FROM ".geoTables::increments_table." WHERE `low`=? LIMIT 1",
				array($low.''));
		}
		if (count($deleting)) {
			geoAdmin::m("Selected bid increment brackets deleted.", geoAdmin::SUCCESS);
		}

		if (isset($_POST['addLow']) && isset($_POST['addIncrement'])) {
			$low = geoNumber::deformat($_POST['addLow']);
			$increment = geoNumber::deformat($_POST['addIncrement']);

			if ($low >= 0.00 && $increment > 0.00) {
				//check duplicate
				$count = (int)$this->db->GetOne("SELECT COUNT(*) FROM ".geoTables::increments_table." WHERE `low`=?", array($low));
				if ($count == 0) {
					//insert it
					$this->db->Execute("INSERT INTO ".geoTables::increments_table." (`low`,`increment`) VALUES (?, ?)",
						array ($low, $increment));
					geoAdmin::m("New increment bracket added!");
				} else {
					geoAdmin::m("Bracket already exists starting at ".geoString::displayPrice($low,'','').", cannot add duplicate.  Edit existing bracket instead by clicking on the increment value.", geoAdmin::ERROR);
				}
			} else {
				geoAdmin::m("Invalid values specified, increment value must be higher than ".geoString::displayPrice('0.00','',''), geoAdmin::ERROR);
			}
		}

		$this->_fixLowestBracket();

		return true;
	}
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
	function display_listing_payment_types()
	{

		$this->payment_types_form($this->db);
		$this->display_page();
	}
	function update_listing_payment_types()
	{
		if ($_REQUEST["z"]) {
			//delete payment type
			return ($this->delete_payment_type($this->db,$_REQUEST["z"]));
		}
		return $this->insert_payment_type($this->db,$_REQUEST["b"]);
	}
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
	function display_listing_listing_durations()
	{
		$this->classified_length_form($this->db);
		$this->display_page();
	}
	function update_listing_listing_durations()
	{
		if ( isset($_REQUEST["d"]) )
		{
			return ( $this->delete_classified_length($_REQUEST["d"]) );
		}
		return $this->add_classified_length($_REQUEST["c"]);
	}
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
	function display_listing_allowed_uploads()
	{

		$this->file_types_form($this->db);
		$this->display_page();
	}
	function update_listing_allowed_uploads()
	{
		if ($_REQUEST["b"]){
			return $this->delete_file_type($this->db,$_REQUEST["b"]);
		}
		return $this->update_file_types($this->db,$_REQUEST["e"]);
	}

	public function display_listing_currency_types ()
	{
		$tpl_vars = array();

		//file type accepted
		$sql = "SELECT * FROM ".geoTables::currency_types_table." ORDER BY `display_order`";
		$tpl_vars['currencies'] = $this->db->GetAll($sql);
		$tpl_vars['is_auctions'] = geoMaster::is('auctions');

		//seller buyer stuff
		if (geoPC::is_ent()) {
			$tpl_vars['sb_headers'] = geoSellerBuyer::callDisplay('adminDisplayCurrencyTypes_header', null, 'string_array');

			$tpl_vars['sb_currency_choices'] = geoSellerBuyer::callDisplay('adminDisplayCurrencyTypes_choices', null, 'array');

			//go through and populate currency type values
			$currencies = array();
			foreach ($tpl_vars['currencies'] as $row) {
				$row['sb_values'] = geoSellerBuyer::callDisplay('adminDisplayCurrencyTypes_type_value', $row['type_id'], 'string_array');
				$currencies[] = $row;
			}
			$tpl_vars['currencies'] = $currencies;
		}

		$tpl_vars['admin_msgs'] = geoAdmin::m();

		geoView::getInstance()->setBodyTpl('listing_setup/currency_types.tpl')
			->setBodyVar($tpl_vars)
			->addJScript('js/currencies.js');
	}

	public function display_listing_currency_types_delete ()
	{
		//let main thingy display page.
		$this->display_listing_currency_types();
	}

	public function update_listing_currency_types_delete ()
	{
		$type_id = (int)$_POST['type_id'];
		if (!$type_id) {
			geoAdmin::m("The currency type to delete is not specified!", geoAdmin::ERROR);
			return false;
		}
		$this->db->Execute("DELETE FROM ".geoTables::currency_types_table." WHERE `type_id`=?", array($type_id));
		geoAdmin::m("Currency type deleted.", geoAdmin::SUCCESS);
		return true;
	}

	public function display_listing_currency_types_add ()
	{
		//let main thingy display the page
		$this->display_listing_currency_types();
	}

	public function update_listing_currency_types_add ()
	{
		$admin = geoAdmin::getInstance();
		//add new listing type
		$new = $_POST['new'];

		$new['type_name'] = trim($new['type_name']);
		if (!strlen($new['type_name'])) {
			$admin->userError("Currency Type Name is required to add new currency type.");
			return false;
		}

		if (!strlen(trim($new['postcurrency']))) {
			$admin->userError("Postcurrency is required to add new currency type.");
			return false;
		}
		//don't trim the pre or post currency

		//conversion rate must be number
		$new['conversion_rate'] = (isset($new['conversion_rate']))? (float)$new['conversion_rate'] : 1;
		//just to make sure it's not weird...
		$new['conversion_rate'] = ($new['conversion_rate']>0)? $new['conversion_rate'] : 1;

		//display order must be integer.
		$new['display_order'] = (int)$new['display_order'];

		//now insert the thing.
		$sql_data = array (
			$new['type_name'], $new['precurrency'], $new['postcurrency'], $new['conversion_rate'], $new['display_order']
		);

		$result = $this->db->Execute("INSERT INTO ".geoTables::currency_types_table." SET `type_name`=?, `precurrency`=?, `postcurrency`=?, `conversion_rate`=?, `display_order`=?", $sql_data);
		if (!$result) {
			$admin->userError("DB Error: ".$this->db->ErrorMsg());
			return false;
		}

		if (geoPC::is_ent() && $new['sb']) {
			//for simplicity, just run it through the update routine
			$return = array();
			$return['type_id'] = $this->db->Insert_Id();
			foreach ($new['sb'] as $sb_type => $value) {
				$return['value'] = $value;
				$return['sb_type'] = $sb_type;
				geoSellerBuyer::callUpdate('adminDisplayCurrencyTypes_update', $return);
			}

		}

		return true;
	}

	public function display_listing_currency_types_edit ()
	{
		if (!geoAjax::isAjax()) {
			//weird
			return $this->display_listing_currency_types();
		}
		geoView::getInstance()->setRendered(true);
	}

	private function _updateSB ()
	{
		$return = array ();
		$ajax = geoAjax::getInstance();

		$value = $return['value'] = $_POST['value'];
		$parts = explode('__',$_POST['editorId']);

		$type_id = (int)$parts[0];
		$sb_type = $parts[1];

		//let gateway save
		$return['value'] = $return['value_display'] = $value;
		$return['sb_type'] = $sb_type;
		$return['type_id'] = $type_id;
		$return['sb'] = 1;

		$return = geoSellerBuyer::callDisplay('adminDisplayCurrencyTypes_update', $return, 'filter');
		echo $ajax->encodeJSON($return);
		return;
	}

	public function update_listing_currency_types_edit ()
	{
		if (!geoAjax::isAjax()) {
			//this is ajax-only
			return false;
		}

		$ajax = geoAjax::getInstance();
		$ajax->jsonHeader();
		if (isset($_GET['sb']) && $_GET['sb']) {
			return $this->_updateSB();
		}

		$return = array ();

		$value = $return['value'] = $_POST['value'];
		$parts = explode('__',$_POST['editorId']);

		$return['value'] = $value;

		$field = $parts[0];

		if (!in_array($field, array('name', 'pre','post','conversion','order'))) {
			//booooo..  not valid field
			$return['error'] = 'Not sure what is being edited.'.$field;

			echo $ajax->encodeJSON($return);
			return;
		}

		$type_id = (int)$parts[1];
		if (!$type_id) {
			//something wrong
			$return['error'] = 'Type ID not known!';
			echo $ajax->encodeJSON($return);
			return;
		}

		switch ($field) {
			case 'name':
				$fieldname = '`type_name`';
				$value = trim($value);
				if (!strlen($value)) {
					$return['error'] = 'Currency name cannot be blank.';
					echo $ajax->encodeJSON($return);
					return;
				}
				break;

			case 'pre':
				$fieldname = '`precurrency`';
				break;

			case 'post':
				$fieldname = '`postcurrency`';
				break;

			case 'conversion':
				$fieldname = '`conversion_rate`';
				$value = floatval($value);
				//make sure conversion rate is > 0 or force it to be 1
				$value = $return['value'] = ($value>0)? $value : 1;
				break;

			case 'order':
				$fieldname = '`display_order`';
				$value = $return['value'] = (int)$value;
		}

		$db = DataAccess::getInstance();
		$db->Execute("UPDATE ".geoTables::currency_types_table." SET $fieldname=? WHERE `type_id`=?", array (''.$value, $type_id));

		$return['message'] = 'Changes applied!';
		if ($field=='order') {
			$return['refresh'] = 1;
			$return['message'] .= ' Please wait while we refresh the page for the updated order.';
		}
		echo $ajax->encodeJSON($return);
	}

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
	function display_uploads_new_type()
	{
		$this->new_file_types_form($this->db);
		$this->display_page();
	}
	function update_uploads_new_type()
	{
		if (geoPC::is_trial()) {
			geoAdmin::m(geoPC::adminTrialMessage(), geoAdmin::NOTICE);
			return true;
		}

		return $this->insert_new_file_type($this->db,$_REQUEST["b"],$_FILES);
	}
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
	function display_listing_attention_getters()
	{
		if (PHP5_DIR)
			$menu_loader = geoAdmin::getInstance();
		else
			$menu_loader =& geoAdmin::getInstance();


		if ( isset( $_REQUEST["c"] ) )
		{
			if ($this->delete_attention_getter($this->db,$_REQUEST["c"]))
				$menu_loader->userSuccess("Settings Saved.");
			else
				$menu_loader->userError("Settings NOT saved, check for errors in any fields.");
		}
		$this->attention_getters_form($this->db);
		$this->display_page();
	}
	function update_listing_attention_getters()
	{
		return $this->insert_attention_getter($this->db,$_REQUEST["b"]);
	}

	public function display_listing_hide_fields ()
	{
		$view = geoView::getInstance();
		$reg = geoAddon::getRegistry('_core', true);
		//that's right, a new method in an old class.

		$tpl_vars = array (
			'fields' => geoFields::getListingTagsMeta(),
			'hiddenFields' => $reg->hiddenFields,
			'adminMsgs' => geoAdmin::m()
		);

		$view->setBodyTpl('hiddenFields.tpl')
			->setBodyVar($tpl_vars);
	}

	public function update_listing_hide_fields ()
	{
		if (!geoPC::is_ent()) return false;

		$fields = $_POST['hiddenFields'];

		$reg = geoAddon::getRegistry('_core', true);
		require_once ADMIN_DIR.'admin_pages_class.php';

		//get list of fields that are valid
		$validFields = geoFields::getListingTagsMeta(array('tag','field','label'), true);

		$hiddenFields = array ();
		foreach ($fields as $field => $val) {
			//see if it is valid
			if (!$val || !isset($validFields[$field])) {
				//not a valid field
				continue;
			}
			$hiddenFields [$field] = 1;
		}
		if (!$hiddenFields) $hiddenFields = false;
		$reg->hiddenFields = $hiddenFields;

		$reg->save();

		return true;
	}
} //end of class Ad_configuration
