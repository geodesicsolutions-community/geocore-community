<?php 
//browse_display_ad_full_images.php
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
## ##    7.5.3-36-gea36ae7
## 
##################################

class Display_ad_full_images extends geoBrowse {
	var $subcategory_array = array();
	var $notify_data = array();

//########################################################################

	public function __construct ($db,$classified_user_id,$language_id,$category_id=0,$page=0,$classified_id=0,$affiliate_id=0,$product_configuration=0,$affiliate_group_id=0)
	{
		if ($limit) {
			$this->browse_limit = (int)$limit;
		}
		
		
		if ($category_id) {
			$this->site_category = (int)$category_id;
		} else if ($classified_id) {
			$listing = geoListing::getListing($classified_id);
			if ($listing && $listing->category) {
				$this->site_category = (int)$listing->category;
			}
		} else {
			$this->site_category = 0;
		}
		
		$db = $this->db = DataAccess::getInstance();
		
		$this->get_ad_configuration($db);
		
		$this->page_result = ($page)? (int)$page: 1;
		$this->affiliate_id = (int)$affiliate_id;
		$this->affiliate_group_id = (int)$affiliate_group_id;
		$this->classified_id = (int)$classified_id;
		parent::__construct();
	} //end of function Display_ad

//###########################################################

	function display_classified_full_images($id=0)
	{
		$db = DataAccess::getInstance();
		$this->page_id = 84;
		$this->get_text();
		if (!$id) {
			return false;
		}
		$listing = geoListing::getListing($id);
		if (!$listing || $listing->live != 1) {
			return false;
		}
		
		$view = geoView::getInstance();
		$tpl_vars = array();
		$tpl_vars['id'] = $id;
		$tpl_vars['title'] = geoString::fromDB($listing->title);
		$tpl_vars['description'] = geoString::fromDB($listing->description);
		$tpl_vars['seller'] = $listing->seller;
		
		//if this is an anonymous listing, don't show the contact seller or seler's other listings links
		$anon = geoAddon::getUtil('anonymous_listing');
		$tpl_vars['anonymous'] = ($anon) ? $anon->isAnonymous($id) : false;
		
		$tpl_vars['images'] = $img = $this->display_full_image_template($id);
		
		if ($this->affiliate_id) {
			$tpl_vars['aff_url'] = $this->configuration_data['affiliate_url']."?aff=".$this->affiliate_id;
		}

		$view->setBodyTpl('full_images.tpl','','browsing')->setBodyVar($tpl_vars);
		$this->display_page();
		return true;

	} //end of function display_classifed

//####################################################################################

	function display_full_image_template($classified_id)
	{
		if (!$classified_id) {
			return false;
		}
		$db = DataAccess::getInstance();
		$listing = geoListing::getListing($classified_id);
		if ($listing && $listing->category) {
			$template_category = (int)$listing->category;
		} else {
			return false;
		}
		
		$view = geoView::getInstance();
		$view->setCategory((int)$template_category);
		//don't do the template stuff, let view class do that...
		//just set the vars
		$template_file = $view->getTemplateAttachment('84_detail',$this->language_id, $template_category);
		
		$this->get_image_data($db,$classified_id,1);
		reset ($this->images_to_display);
			
		$images = array();
		
		foreach ($this->images_to_display as $value) {
			$tpl = new geoTemplate(geoTemplate::MAIN_PAGE,'');
			
			//check any full sized image limits
			if (($value["original_image_width"] > $this->ad_configuration_data->MAXIMUM_FULL_IMAGE_WIDTH) ||
			($value["original_image_height"] > $this->ad_configuration_data->MAXIMUM_FULL_IMAGE_HEIGHT))
			{
				if (($value["original_image_width"] > $this->ad_configuration_data->MAXIMUM_FULL_IMAGE_WIDTH) && ($value["original_image_height"] > $this->ad_configuration_data->MAXIMUM_FULL_IMAGE_HEIGHT))
				{
					$imageprop = ($this->ad_configuration_data->MAXIMUM_FULL_IMAGE_WIDTH * 100) / $value["original_image_width"];
					$imagevsize = ($value["original_image_height"] * $imageprop) / 100 ;
					$image_width = $this->ad_configuration_data->MAXIMUM_FULL_IMAGE_WIDTH;
					$image_height = ceil($imagevsize);

					if ($image_height > $this->ad_configuration_data->MAXIMUM_FULL_IMAGE_HEIGHT)
					{
						$imageprop = ($this->ad_configuration_data->MAXIMUM_FULL_IMAGE_HEIGHT * 100) / $value["original_image_height"];
						$imagehsize = ($value["original_image_width"] * $imageprop) / 100 ;
						$image_height = $this->ad_configuration_data->MAXIMUM_FULL_IMAGE_HEIGHT;
						$image_width = ceil($imagehsize);
					}
				}
				elseif ($value["original_image_width"] > $this->ad_configuration_data->MAXIMUM_FULL_IMAGE_WIDTH)
				{
					$imageprop = ($this->ad_configuration_data->MAXIMUM_FULL_IMAGE_WIDTH * 100) / $value["original_image_width"];
					$imagevsize = ($value["original_image_height"] * $imageprop) / 100 ;
					$image_width = $this->ad_configuration_data->MAXIMUM_FULL_IMAGE_WIDTH;
					$image_height = ceil($imagevsize);
				}
				elseif ($value["original_image_height"] > $this->ad_configuration_data->MAXIMUM_FULL_IMAGE_HEIGHT)
				{
					$imageprop = ($this->ad_configuration_data->MAXIMUM_FULL_IMAGE_HEIGHT * 100) / $value["original_image_height"];
					$imagehsize = ($value["original_image_width"] * $imageprop) / 100 ;
					$image_height = $this->ad_configuration_data->MAXIMUM_FULL_IMAGE_HEIGHT;
					$image_width = ceil($imagehsize);
				}
				else
				{
					$image_width = $value["original_image_width"];
					$image_height = $value["original_image_height"];
				}
					
			}
			else
			{
				$image_width = $value["original_image_width"];
				$image_height = $value["original_image_height"];
			}
			
			if($value['icon']) {
				$image = "<a href=\"".$value['url']."\"><img src=\"".geoTemplate::getUrl('',$value['icon'])."\" alt=\"\" style=\"border: none;\" /></a>";
			} else {
				$image =  "<img src='{$value["url"]}' alt='' />";
			}

			$tpl->assign('full_size_image', $image);
			if (strlen($value["image_text"]) > 0) {
				$text = "<br />".$value["image_text"];
				$tpl->assign('full_size_text', $text);
			}
			
			$images[] = $tpl->fetch($template_file);
		}
		return $images;
	}
}