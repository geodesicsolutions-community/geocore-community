<?php

$upgrade_array = array (
    # start
    array (10203, 502292, 1, 'ePay'),
    array (183, 502293, 1, 'Payment+via+ePay'),
    array (10203, 502294, 1, 'Proceed+to+Checkout'),
    array (10, 502295, 1, '%3Cimg+src%3D%22%7Bexternal+file%3D%27images%2Fbuttons%2Frotate-cw.png%27%7D%22+alt%3D%22Rotate+Image%22+%2F%3E'),
    array (24, 502298, 1, 'message+sent+to'),
    array (24, 502299, 1, 'date+sent'),//INSERT_UPGRADE_ARRAY//

);

$insert_text_array = array (
    # start
    array (502292, 'ePay.dk+gateway+label', '', '', 10203, 0, 0),
    array (502293, 'ePay.dk+transaction+description', '', '', 183, 0, 0),
    array (502294, 'Paypal+Payments+Advanced+checkout+label', '', '', 10203, 0, 0),
    array (502295, 'Rotate+image+link', '', '', 10, 0, 0),
    array (502298, 'message+sent+column+header', '', '', 24, 0, 0),
    array (502299, 'message+date+sent+column+header', '', '', 24, 0, 0),//INSERT_INSERT_TEXT_ARRAY//

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
