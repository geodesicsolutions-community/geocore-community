<?php
$upgrade_array = array (
	# start
	
	
	array (10202, 502446, 1, 'Confirmation+Code%3A'),
	array (10214, 502447, 1, ''),
	array (10214, 502448, 1, ''),
	array (10214, 502449, 1, ''),
	array (10214, 502450, 1, ''),
	array (10214, 502451, 1, ''),
	array (10214, 502452, 1, ''),
	array (10214, 502453, 1, ''),
	array (10214, 502454, 1, ''),
	array (10214, 502455, 1, ''),
	array (10214, 502456, 1, ''),//INSERT_UPGRADE_ARRAY//
	
);

$insert_text_array = array (
	# start	
	
	
	array (502446, 'Just-In-Time+Confirmation+Code+label', '', '', 10202, 0, 0),
	array (502447, 'Extra+Text+1', 'Use+this+to+add+extra+translated+text+to+your+templates', '', 10214, 0, 0),
	array (502448, 'Extra+Text+2', 'Use+this+to+add+extra+translated+text+to+your+templates', '', 10214, 0, 0),
	array (502449, 'Extra+Text+3', 'Use+this+to+add+extra+translated+text+to+your+templates', '', 10214, 0, 0),
	array (502450, 'Extra+Text+4', 'Use+this+to+add+extra+translated+text+to+your+templates', '', 10214, 0, 0),
	array (502451, 'Extra+Text+5', 'Use+this+to+add+extra+translated+text+to+your+templates', '', 10214, 0, 0),
	array (502452, 'Extra+Text+6', 'Use+this+to+add+extra+translated+text+to+your+templates', '', 10214, 0, 0),
	array (502453, 'Extra+Text+7', 'Use+this+to+add+extra+translated+text+to+your+templates', '', 10214, 0, 0),
	array (502454, 'Extra+Text+8', 'Use+this+to+add+extra+translated+text+to+your+templates', '', 10214, 0, 0),
	array (502455, 'Extra+Text+9', 'Use+this+to+add+extra+translated+text+to+your+templates', '', 10214, 0, 0),
	array (502456, 'Extra+Text+10', 'Use+this+to+add+extra+translated+text+to+your+templates', '', 10214, 0, 0),//INSERT_INSERT_TEXT_ARRAY//
	
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
