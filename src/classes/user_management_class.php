<?php 
//user_management_class.php
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
## ##    6.0.7-2-gc953682
## 
##################################

class User_management extends geoSite
{
	/**
	 * This file doesn't appear to be used anymore,
	 * so I'm marking it @deprecated 9/16/08
	 * 
	 * functionality that appears to have once been here is in
	 * user_management_home.php 
	 * 
	var $error_found;
	var $error;

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

	function user_management_home ($db)
	{
		if ($this->userid)
		{
			//display the user management home page
			echo "<table cellpadding=\"2\" cellspacing=\"1\" style=\"border: none; width: 100%;\">\n\t";
			echo "<tr class=\"user_management_title\">\n\t\t<td valign=\"top\">\n\t\t".urldecode($this->messages[89])."\n\t\t</td>\n\t</tr>\n\t";
			echo "<tr>\n\t\t<td>\n\t\t";
			$this->user_management_menu($db,$switch);
			echo "</td>\n\t</tr>\n\t";
			echo "</table>\n\t";
		}
		return true;
	} //end of function user_management_home

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

	function user_management_menu ($db,$switch=0)
	{
		if ($this->userid)
		{	
			echo "<table cellpadding=\"2\" cellspacing=\"1\" style=\"width: 100%; margin: 0 auto; text-align: center; border: none;\" class=\"user_management_menu_links\">\n\t";
			echo "<tr>\n\t\t<td align=\"center\"><a href=\"".$this->configuration_data['classifieds_file_name']."?a=4&amp;b=1\" class=\"user_management_menu_links\">".urldecode($this->messages[93])."</a>\n\t\t</td>\n\t</tr>\n\t";
			echo "<tr>\n\t\t<td align=\"center\"><a href=\"".$this->configuration_data['classifieds_file_name']."?a=4&amp;b=2\" class=\"user_management_menu_links\">".urldecode($this->messages[94])."</a>\n\t\t</td>\n\t</tr>\n\t";
			echo "<tr>\n\t\t<td align=\"center\"><a href=\"".$this->configuration_data['classifieds_file_name']."?a=4&amp;b=3\" class=\"user_management_menu_links\">".urldecode($this->messages[95])."</a>\n\t\t</td>\n\t</tr>\n\t";
			echo "<tr>\n\t\t<td align=\"center\"><a href=\"".$this->configuration_data['classifieds_file_name']."?a=1\" class=\"user_management_menu_links\">".urldecode($this->messages[96])."</a>\n\t\t</td>\n\t</tr>\n\t";
			echo "<tr>\n\t\t<td align=\"center\"><a href=\"".$this->configuration_data['classifieds_file_name']."?a=4&amp;b=9\" class=\"user_management_menu_links\">".urldecode($this->messages[97])."</a>\n\t\t</td>\n\t</tr>\n\t";
			echo "<tr>\n\t\t<td align=\"center\">\n\t\t<a href=\"".$this->configuration_data['classifieds_file_name']."?a=4&amp;b=10\" class=\"user_management_menu_links\">".urldecode($this->messages[289])."</a>\n\t\t</td>\n\t</tr>\n\t";
			echo "<tr>\n\t\t<td align=\"center\"><a href=\"".$this->configuration_data['classifieds_file_name']."?a=4&amp;b=8\" class=\"user_management_menu_links\">".urldecode($this->messages[98])."</a>\n\t\t</td>\n\t</tr>\n\t";
			echo "<tr>\n\t\t<td align=\"center\"><a href=\"".$this->configuration_data['classifieds_file_name']."?a=4&amp;b=7\" class=\"user_management_menu_links\">".urldecode($this->messages[99])."</a>\n\t\t</td>\n\t</tr>\n\t";
			echo "</table>\n\t";
		}
		else
		{
			//no user id
			$this->error_message = $this->data_missing_error_message;
			return false;
		}
	} //end of function user_management_menu

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
*/
}