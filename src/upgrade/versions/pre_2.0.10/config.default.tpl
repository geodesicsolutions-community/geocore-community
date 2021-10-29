<?php
//config.php
##########GIT Build Data##########
## 
## File Changed In GIT Commit:
## 
##    6.0.7-2-gc953682
## 
##################################
/*
 This file was auctomatically generated using the config.php upgrade script.
*/

/*
 Welcome to the Geodesic Software Configuration File. This file is the link
 between the php files that run the functions of the software and your
 database which stores all of the information.

We have organized the file in sections. Please follow the below carefully to
ensure a successful installation.
*/

// STEP 1 ################### MySQL Database Settings #######################
/*
The information below is for the main installation. For configuring an instalation with the API
see further below.
*/

$db_host = "<<db_host>>";//location of sql host - usually localhost
$db_username = "<<db_username>>";//username used to connect to database
$db_password = "<<db_password>>";//password used to connect to database
$database = "<<database>>";//name of database



//########################## MySQL Database Settings #######################

// STEP 2 ################### Database Connection Type #######################
/*
OPTIONAL
Uncomment the below line($persistent_connections = 1) if you want to use
persistent database connections.
Some hosts do not allow this so only do so if you are sure that you want
to use this type of connection. To uncomment, remove the two slashes.
*/

<<persistent_connections_comment>>$persistent_connections = <<persistent_connections>>;

############################# Database Connection Type #######################

// STEP 3 ################### API MySQL Database Settings #######################
/*
ONLY COMPLETE IF USING THE API.
The information below is for the installation of the API. For configuring an instalation with the API
see further below.
*/


$api_db_host = "<<api_db_host>>"; //location of sql host - usually localhost
$api_db_username = "<<api_db_username>>"; //username used to connect to database
$api_db_password = "<<api_db_password>>"; //password used to connect to database
$api_database = "<<api_database>>"; //name of database api tables are stored in

//########################## API MySQL Database Settings #######################

//////////////////////// DO NOT EDIT BELOW THIS LINE ////////////////////////

$product_type = <<product_type>>;

/////////////////////////////////////////////////////////////////////////////////////////////////////


//Database Type.  Should never need to change this if using MySQL.
//WARNING:  Only 'mysql' database is fully tested, other database types may not work correctly.
$db_type ='mysql';
//$db_type = 'access';
//$db_type = 'ado';
//$db_type = 'ado_mssql';
//$db_type = 'borland_ibase';
//$db_type = 'csv';
//$db_type = 'db2';
//$db_type = 'fbsql';
//$db_type = 'firebird';
//$db_type = 'ibase';
//$db_type = 'informix';
//$db_type = 'mssql';
//$db_type = 'mysqlt';
//$db_type = 'oci8';
//$db_type = 'oci8po';
//$db_type = 'odbc';
//$db_type = 'odbc_mssql';
//$db_type = 'odbc_oracle';
//$db_type = 'oracle';
//$db_type = 'postgres7';
//$db_type = 'postgress';
//$db_type = 'proxy';
//$db_type = 'sqlanywhere';
//$db_type = 'sybase';
//$db_type = 'vfp';


/*
The following are controls for beta testing features.  Do not edit these controls without direction
from Geodesic Support.  Changing these values could affect the stability of your installation.
Proceed with caution.
*/

//do not change the following 2 lines, they do not affect any settings.
if (!defined("BETA_SWITCHES")) {
define ('BETA_SWITCHES',1);


//this controls whether or not a client browsing your site should have a subscription
//to view an ads details.  If set to 1 and the client does not have a subscription
//they will not be able to view an ad details
define ("MUST_HAVE_SUBSCRIPTION_TO_VIEW_AD_DETAIL",<<MUST_HAVE_SUBSCRIPTION_TO_VIEW_AD_DETAIL>>);
//this controls the default users communication
//configuration setting at the time of registration.  The default configuration
//setting is "1".  This is the public communication configuration for all new registrants at the time
//of registration.  To change to the completely private setting at time of registration for all new clients
//change this to 3.  The client can always change their configuration after they have finished
//registration.  The only possible setting for this are 1 or 3.
define("DEFAULT_COMMUNICATION_SETTING",<<DEFAULT_COMMUNICATION_SETTING>>);

//if set to 1 this will allow any bidder to bid against themselves even if they are the current
//high bidder.  The current default controls will not allow the current high bidder to bid against
//themselves.  If this is set to 0 the default code will not allow the current high bidder to
//bid against themselves...searching for the hidden reserve price.
define("ALLOW_BIDDING_AGAINST_SELF", <<ALLOW_BIDDING_AGAINST_SELF>>);

// If set to 1 this will allow the user to copy a current or newly expired listing into a brand
// new listing.  It will pop them directly to the approval page.
define("ALLOW_COPYING_NEW_LISTING", <<ALLOW_COPYING_NEW_LISTING>>);

// If set to 1 these will activate the security image field for the corresponding form.
define("SECURE_REGISTRATION", <<SECURE_REGISTRATION>>);
define("SECURE_LOGIN", <<SECURE_LOGIN>>);
define("SECURE_MESSAGING", <<SECURE_MESSAGING>>);
define("CS_MIN", <<CS_MIN>>);
define("CS_MAX", <<CS_MAX>>);

//These are the widths and heigths to the description text box areas for either the WYSIWYG Rich Text Editor
//Or the default HTML TextArea. BetaSwitch.
define("DESC_BOX_WIDTH",<<DESC_BOX_WIDTH>>);
define("DESC_BOX_HEIGTH",<<DESC_BOX_HEIGTH>>);

//the following switch allows the client to choose whether the email address is displayed
//in the auction bid history for each bidder after the auction has expired.
define("VIEW_EMAIL_AFTER_AUCTION_OVER",<<VIEW_EMAIL_AFTER_AUCTION_OVER>>);

//the title field in the place a listing process does not allow double quotes if the field type
//used is an input box.  The title field must use a textarea box to be able to place
//double quotes within the title.  This switch allows the switching of the title input
//field to a textarea box from a input text field.  This will change the use within
//the place a listing process and within the listing edit detail
define("USE_TEXTAREA_IN_TITLE",<<USE_TEXTAREA_IN_TITLE>>);

//The following variable can be used only in conjuction with the site on/off switch located
//in Site Setup > General Settings within your admin.  This will allow you to disable your
//website from public access, while at the same time allowing you (or any IPs you choose) to perform
//maintenance such as placing test listings, etc.  You can place as many IPs as you wish.  You can use
//partial IPs, but the software assumes you are leaving off the right-most octets (ex.  192.168 will be
//interpreted as 192.168.x.x).  Separate each IP by a comma.
//NOTE: You must supply 3 digits for any given octet, or else end the octet with a period for an exact match.
//For example, 10.0 would match 10.0.x.x AND 10.056.x.x, but 10.0. would only match 10.0.x.x
/*
  //EXAMPLE

  define("ALLOWED_IPS_WHEN_SITE_DISABLED","
    10.127.,
    192.168.0.1,
    ");
*/
define("ALLOWED_IPS_WHEN_SITE_DISABLED","<<ALLOWED_IPS_WHEN_SITE_DISABLED>>");

//Displays Description Last In The From --
define("DISPLAY_DESCRIPTION_LAST_IN_FORM",<<DISPLAY_DESCRIPTION_LAST_IN_FORM>>);

//this control displays/not displays the email address of user search results within the
//black list and invited list features of the client side admin tool
define("DISPLAY_EMAIL_INVITE_BLACK_LIST",<<DISPLAY_EMAIL_INVITE_BLACK_LIST>>);

//to put the site into demo (non-functional) mode, uncomment.  NOT FULLY
//IMPLEMENTED YET!!!!
//define ('DEMO_MODE', 1);
//with the use of some character set encoding of search terms is not needed.  This beta switch
//will turn on/off encoding of the search term so that results could be returned using search terms
//0 - is the default so that all search terms will be encoded 
//1 - is the setting where the search term will not be encoded before searching database
define("ENCODE_SEARCH_TERMS",<<ENCODE_SEARCH_TERMS>>);


}
//do NOT change the next line.  This clean_inputs.php file is customized for class auctions, it is not compatible with
//other versions.  Use xss_clean_inputs.php for older versions.
include_once('clean_inputs.php');
?>