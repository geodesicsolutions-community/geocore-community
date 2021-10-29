<?php
//arrays.php
##########GIT Build Data##########
## 
## File Changed In GIT Commit:
## 
##    6.0.7-2-gc953682
## 
##################################

/**
 * SPECIAL PART:  This part is not standard, do not copy to new version.
 * 
 * 
 */


$_iconSettings = $this->_db->GetRow("SELECT * FROM `geodesic_classifieds_configuration`");
//just in case there was problem
if (!$_iconSettings) {
	$_iconSettings = array (
		'category_new_ad_image' => 'images/new_listing.gif',
		'no_image_url' => 'images/no_photo.gif',
		'photo_icon_url' => 'images/photo.gif',
		'help_image' => 'images/help.gif',
		'sold_image' => 'images/sold.gif',
		'buy_now_image' => 'images/buy_now.gif',
		'reserve_met_image' => 'images/reserve_met.gif',
		'no_reserve_image' => 'images/no_reserve.gif',
	);
} else {
	//for some reason help image is encoded?
	$_iconSettings['help_image'] = urldecode($_iconSettings['help_image']);
}

/**
 * End of special part!  Clear the above when copying this to new version.
 */

$upgrade_array = array (
	# start
	array (171, 500767, 1, 'Full+sized+images+for'),
	array (10202, 500768, 1, 'Login'),
	array (10202, 500769, 1, 'The+email+address+you+entered+already+exists+in+our+database.+To+continue%2C+please+login+using+the+link+below.+Alternatively%2C+you+can+use+the+link+labeled+%22Go+Back%22+to+return+to+the+listing+details+page+and+enter+a+different+email+address.'),
	array (10202, 500770, 1, 'In+order+to+proceed%2C+you+must+be+logged+into+the+website.+If+you+already+have+a+username%2C+click+%22Login%22+below+and+you+will+be+taken+to+the+login+screen.+Otherwise%2C+click+%22Continue%22+and+we+will+create+a+username+for+you+and+automatically+log+you+in.'),
	array (10202, 500771, 1, 'Login'),
	array (10202, 500772, 1, '+or+'),
	array (10202, 500773, 1, 'Go+Back'),
	array (10202, 500774, 1, 'Continue'),
	array (10202, 500775, 1, 'New+Registration'),
	array (10202, 500776, 1, 'Hello%2C%0A%0AIn+connection+with+your+recent+listing%2C+we+have+created+a+user+account+for+you.%0A%0AYou+can+log+into+the+site+to+edit+your+listing%2C+post+others%2C+and+gain+access+to+all+our+features+for+registered+users.+To+do+so%2C+enter+the+data+below+on+the+login+page+found+here%3A'),
	array (10202, 500777, 1, 'Username%3A+'),
	array (10202, 500778, 1, 'Password%3A+'),
	array (10202, 500779, 1, '+%28We+recommend+changing+your+password+after+logging+in%2C+by+going+to+My+Account+%3E+My+Account+Information%29%0A%0AThanks%21'),
	array (37, 500780, 1, '+Free'),
	array (10203, 500781, 1, 'Netcash'),
	array (183, 500782, 1, 'Payment+made+via+Netcash'),
	array (10202, 500783, 1, 'Username+and+password+cannot+be+the+same.'),
	array (10202, 500784, 1, 'All+fields+are+required.'),
	array (10202, 500785, 1, 'Passwords+do+not+match.'),
	array (10202, 500786, 1, 'That+username+is+already+in+use.'),
	array (10202, 500787, 1, 'There+was+a+database+error.+Please+contact+the+site+administrator.'),
	array (10202, 500788, 1, 'In+order+to+proceed%2C+you+must+be+logged+into+the+website.+If+you+already+have+a+username%2C+click+%22Login%22+below+and+you+will+be+taken+to+the+login+screen.+Otherwise%2C+enter+your+desired+username+and+password+below+to+register.'),
	array (10202, 500789, 1, 'Username%3A'),
	array (10202, 500790, 1, 'Password%3A'),
	array (10202, 500791, 1, 'Confirm+Password%3A'),
	array (10202, 500792, 1, 'Register'),
	array (10209, 500793, 1, 'My+Account'),
	array (59, 500794, 1, urlencode($_iconSettings['category_new_ad_image'])),
	array (59, 500795, 1, urlencode($_iconSettings['no_image_url'])),
	array (59, 500796, 1, urlencode($_iconSettings['photo_icon_url'])),
	array (59, 500797, 1, urlencode($_iconSettings['help_image'])),
	array (59, 500798, 1, urlencode($_iconSettings['sold_image'])),
	array (59, 500799, 1, urlencode($_iconSettings['buy_now_image'])),
	array (59, 500800, 1, urlencode($_iconSettings['reserve_met_image'])),
	array (59, 500802, 1, urlencode($_iconSettings['no_reserve_image'])),
	array (10208, 500803, 1, 'Account+Finance'),
	array (9, 500804, 1, 'Contact+%26amp%3B+Location+Information'),
	array (9, 500805, 1, 'Additional+Information'),
	array (44, 500806, 1, 'Category+Specific+Criteria'),
	array (44, 500807, 1, 'Additional+Criteria%3A'),
	array (44, 500808, 1, 'By+City%3A'),
	array (44, 500809, 1, 'Location%3A'),
	array (84, 500810, 1, 'Options'),
	array (10158, 500811, 1, '%3Cstrong%3ESeller%3A%3C%2Fstrong%3E'),
	array (10202, 500812, 1, 'You+have+already+started'),
	array (10202, 500813, 1, '.++How+do+you+want+to+proceed%3F'),
	array (10202, 500814, 1, 'Resume'),
	array (10202, 500815, 1, 'Start+Over'),
	array (37, 500816, 1, 'Cancel'),
	array (37, 500817, 1, 'Saving...'),
	array (10, 500818, 1, 'The+file+you+selected+is+too+large%2C+the+max+allowed+file+size+is%3A+'),
	array (10199, 500819, 1, 'All+Categories'),//INSERT_UPGRADE_ARRAY//
	
);

$insert_text_array = array (
	# start
	array (500767, 'Full+sized+image+page', '', '', 171, 0, 0),
	array (500768, 'Just-In-Time+step+label', 'Labels+the+cart+step+%28breadcrumb%29+entry+for+the+Just+In+Time+Registration+page', '', 10202, 0, 0),
	array (500769, 'Just-In-Time+instructions+%28email+exists%29', 'Instructions+shown+to+the+user+during+the+Just+In+Time+step+when+the+email+he+entered+is+already+in+the+database', '', 10202, 0, 0),
	array (500770, 'Just-In-Time+instructions+%28email+not+found%29', 'Instructions+shown+to+the+user+during+the+Just+In+Time+step+when+the+email+he+entered+is+NOT+in+the+database', '', 10202, 0, 0),
	array (500771, 'Just-In-Time+login+link', 'Text+of+the+%22login%22+link+during+the+Just+In+Time+step', '', 10202, 0, 0),
	array (500772, 'Just-In-Time+%22or%22', 'Text+used+to+separate+the+two+links+on+the+Just+In+Time+step', '', 10202, 0, 0),
	array (500773, 'Just-In-Time+%22Go+Back%22+link', 'Text+for+the+link+back+to+the+details+collection+page%2C+in+the+Just+In+Time+step', '', 10202, 0, 0),
	array (500774, 'Just-In-Time+%22Continue%22+link', 'Text+for+the+%22continue%22+%28auto-registration%29+link+in+the+Just+In+Time+step', '', 10202, 0, 0),
	array (500775, 'JIT+-+Registration+Email%3A+subject', 'subject+of+the+email+sent+to+users+after+an+account+is+created+for+them+by+the+JIT+system', '', 10202, 0, 0),
	array (500776, 'JIT+-+Registration+Email%3A+body1', 'first+part+of+the+body+of+the+email+sent+to+users+after+an+account+is+created+for+them+by+the+JIT+system', '', 10202, 0, 0),
	array (500777, 'JIT+-+Registration+Email%3A+username+label', 'labels+the+new+username+in+the+email+sent+to+users+after+an+account+is+created+for+them+by+the+JIT+system', '', 10202, 0, 0),
	array (500778, 'JIT+-+Registration+Email%3A+password+label', 'labels+the+new+password+in+the+email+sent+to+users+after+an+account+is+created+for+them+by+the+JIT+system', '', 10202, 0, 0),
	array (500779, 'JIT+-+Registration+Email%3A+body2', 'end+of+the+email+sent+to+users+after+an+account+is+created+for+them+by+the+JIT+system', '', 10202, 0, 0),
	array (500780, 'number+of+free+pics+label', '', '', 37, 0, 0),
	array (500781, 'Netcash+gateway+-+label', '', '', 10203, 0, 0),
	array (500782, 'Netcash+gateway+-+transaction+description', 'used+internally+to+label+a+netcash+transaction', '', 183, 0, 0),
	array (500783, 'JIT+error+message%3A+username+and+password+match', '', '', 10202, 0, 0),
	array (500784, 'JIT+error+message%3A+missing+data', '', '', 10202, 0, 0),
	array (500785, 'JIT+error+message%3A+password+and+confirmation+don%27t+match', '', '', 10202, 0, 0),
	array (500786, 'JIT+error+message%3A+username+taken', '', '', 10202, 0, 0),
	array (500787, 'JIT+error+message%3A+database+error', '', '', 10202, 0, 0),
	array (500788, 'JIT%3A+registration+form+instructions', '', '', 10202, 0, 0),
	array (500789, 'JIT%3A+registration+form+username+label', '', '', 10202, 0, 0),
	array (500790, 'JIT%3A+registration+form+password+label', '', '', 10202, 0, 0),
	array (500791, 'JIT%3A+registration+form+confirm+password+label', '', '', 10202, 0, 0),
	array (500792, 'JIT%3A+registration+form+submit+button+label', '', '', 10202, 0, 0),
	array (500793, 'My+Account+header+text', '', '', 10209, 0, 0),
	array (500794, 'ICON+-+New+Listing+Icon+URL', 'Used+on+multiple+pages%2Fmodules%2C+whever+new+listing+is+displayed.++Make+the+text+blank+to+disable+new+icon.', '', 59, 0, 0),
	array (500795, 'ICON+-+No+Photo+Icon+URL', 'Used+on+multiple+pages%2Fmodules%2C+whever+the+no+photo+icon+is+displayed.', '', 59, 0, 0),
	array (500796, 'ICON+-+Photo+Icon+URL', 'Used+on+multiple+pages%2Fmodules%2C+whever+the+photo+icon+is+displayed.', '', 59, 0, 0),
	array (500797, 'ICON+-+Help+Icon+URL', 'Used+on+multiple+pages%2Fmodules%2C+whever+the+help+icon+is+displayed.', '', 59, 0, 0),
	array (500798, 'ICON+-+Sold+Icon+URL', 'Used+on+multiple+pages%2Fmodules%2C+whever+the+sold+icon+is+displayed.', '', 59, 0, 0),
	array (500799, 'ICON+-+Buy+Now+Icon+URL', 'Used+on+multiple+pages%2Fmodules%2C+whever+the+Buy+Now+icon+is+displayed.', '', 59, 0, 0),
	array (500800, 'ICON+-+Reserve+Met+Icon+URL', 'Used+on+multiple+pages%2Fmodules%2C+whever+the+Reserve+Met+icon+is+displayed.', '', 59, 0, 0),
	array (500802, 'ICON+-+No+Reserve+Icon+URL', 'Used+on+multiple+pages%2Fmodules%2C+whever+the+No+Reserve+icon+is+displayed.', '', 59, 0, 0),
	array (500803, 'Account+Finance+section+title', 'If+text+is+blank%2C+will+combine+account+finance+with+normal+my+account+links', '', 10208, 0, 0),
	array (500804, 'Contact+and+Location+Label', 'Used+for+section+title', '', 9, 0, 0),
	array (500805, 'Additional+Information+Label', 'Used+for+section+title', '', 9, 0, 0),
	array (500806, 'Category+Specific+Criteria+section+title', '', '', 44, 0, 0),
	array (500807, 'Additional+Criteria+section+title', '', '', 44, 0, 0),
	array (500808, 'By+city+field+label', '', '', 44, 0, 0),
	array (500809, 'Location+section+title', '', '', 44, 0, 0),
	array (500810, 'Options+Title', '', '', 84, 0, 0),
	array (500811, 'Seller+label', '', '', 10158, 0, 0),
	array (500812, 'Action+Interrupted+-+old+%26+new+same+action+-+%5BDESCRIPTION+PART+1%5D', 'Used+when+the+old+and+new+actions+are+the+same%2C+to+allow+having+shorter+resume%2Fcancel+button.++Description+will+be+used+like%3A++%5BDESCRIPTION+PART+1%5D+%5BACTION+INTERRUPTED%5D%5BDESCRIPTION+PART+2%5D', '', 10202, 0, 0),
	array (500813, 'Action+Interrupted+-+old+%26+new+same+action+-+%5BDESCRIPTION+PART+2%5D', 'Used+when+the+old+and+new+actions+are+the+same%2C+to+allow+having+shorter+resume%2Fcancel+button.++Description+will+be+used+like%3A++%5BDESCRIPTION+PART+1%5D+%5BACTION+INTERRUPTED%5D%5BDESCRIPTION+PART+2%5D', '', 10202, 0, 0),
	array (500814, 'Action+Interrupted+-+old+%26+new+same+action+-+Resume+Button+Text', 'Used+when+the+old+and+new+actions+are+the+same%2C+to+allow+having+shorter+resume%2Fcancel+button.', '', 10202, 0, 0),
	array (500815, 'Action+Interrupted+-+old+%26+new+same+action+-+Start+Over+Button+Text', 'Used+when+the+old+and+new+actions+are+the+same%2C+to+allow+having+shorter+resume%2Fcancel+button.', '', 10202, 0, 0),
	array (500816, 'Cancel+editing+paypal+ID+text', 'text+used+for+cancel+button+when+editing+paypal+ID', '', 37, 0, 0),
	array (500817, 'Saving+changes+to+paypal+ID+text', 'text+used+when+saving+changes+to+paypal+ID', '', 37, 0, 0),
	array (500818, 'File+selected+is+too+large', 'The+max+file+size+allowed+will+be+printed+after+the+end+of+this+error+message.', '', 10, 0, 0),
	array (500819, 'All+Categories+text', '', '', 10199, 0, 0),//INSERT_INSERT_TEXT_ARRAY//
	
);

$insert_font_array = array(
//sample:
//array (/*element_id*/ 123, /*page_id*/ 456, /*element*/ 'css_element_name', /*name*/ 'name to show in admin', /*description*/ 'description to show in the admin', /*font_family*/ '', /*font_size*/ '', /*font_style*/ '', /*font_weight*/ '', /*color*/ '', /*text_decoration*/ '', /*background_color*/ '', /*background_image*/ '', /*text_align*/ '', /*display_order*/ '', /* text_vertical_align*/ '', /* text_transform*/ '', /* custom_css*/ '' )

);

$remove_old_array = array (
	//Array of text id's that are no longer used, so we get rid of em to stop confusion
	//IMPORTANT:  Be SURE to search the entire trunk for a text ID before removing it to make
	// sure it's not used anywhere.
	//Also remember to remove entry from sql snapshot, CAREFULLY
	
);
