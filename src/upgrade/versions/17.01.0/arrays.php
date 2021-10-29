<?php
//arrays.php
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
## ##    16.09.0-73-g3431727
## 
##################################


$upgrade_array = array (
	# start
	
	array (183, 502445, 1, 'Invoice'),//INSERT_UPGRADE_ARRAY//
	
);

$insert_text_array = array (
	# start	
	
	array (502445, 'Invoice+Title', '', '', 183, 0, 0),//INSERT_INSERT_TEXT_ARRAY//
	
);


$remove_old_array = array (
	/*
	 * Array of text id's that are no longer used, so we get rid of em to stop confusion
	 * IMPORTANT:  Be SURE to search the entire trunk for a text ID before removing it to make
	 * sure it's not used anywhere.
	 * Also remember to remove entry from sql snapshot, CAREFULLY
	 */	
);


$remove_old_pages_array = array (
	/*
	 * Array of page id's to remove for pages no longer used in software.  This
	 * will also remove all text for that page.  For each page being removed, do
	 * the following:
	 * 1.  Double check that the page is not used anywhere.  (search for ->page_id
	 *     and ->get_text and check that none of places use that page)
	 * 2.  Add to this list, and add comment for what the page used to be named,
	 *     and why it is removed, for future reference.
	 * 3.  Remove page from SQL snapshot in geodesic_pages table. (in sql/design_pages.sql)
	 * 4.  Remove all text entries for that page from SQL snapshot in 
	 *     geodesic_pages_messages and geodesic_pages_messages_languages (in 
	 *     sql/messages.sql)
	 * 5.  Remove each page from array of page ID's in the package builder, in
	 *     ini/text/pages.ini
	 */
	
);
