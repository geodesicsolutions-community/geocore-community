<?php

/*
 *	Install table explanation:
 *	All variables are defined in a huge 2 dimensional array called $install.
 *	Each product has a code assigned to it and then an element for each code.
 *
 *	Variables:
 *	$install[$product_type]['product_name']
 *	$install[1]['config_table']
 *	$install[1]['logins_table']
 *	$install[1]['ad_config']
 *	$install[1]['url']
 *	$install[1]['filename']
 *	$install[1]['affiliate']
 *	$install[1]['email_config']
 *	$install[1]['email']
 *	$install[1]['url_register']
 *	$install[1]['file_name']
 *	$install[1]['upload']
 *	$install[1]['upload_path']
 *	$install[1]['gdlib']
 *	$install[1]['url_image_directory']
 *	$install[1]['abs_image_directory']
 *	$install[1]['maximum_upload_size']
 *	$install[1]['site_email']
 *	$install[1]['admin_registration_email']
 *	Note to developers:
 *	If the standard MD5 cipher encryption algorithm is
 *	implemented in the software then set this to true.
 *	Otherwise set this to false.  As of 10/22/04 it is
 *	only implemented in GeoAuctions Enterprise.
 *	$install[1]['encrypt']
*/

// Auctions Enterprise
$install[1]['product_name']  = "GeoAuctions Enterprise";            // Product name

// Tables
$install[1]['config_table'] = 'geodesic_auctions_configuration';    // Configuration table
$install[1]['logins_table'] = 'geodesic_logins';            // Logins table
$install[1]['ad_config']    = 'geodesic_auctions_ad_configuration'; // Ad configuration table
$install[1]['userdata']     = 'geodesic_userdata';  // Userdata Table

// Fields
$install[1]['url']      = 'AUCTIONS_URL';       // URL of main file
$install[1]['filename'] = 'AUCTIONS_FILE_NAME';     // Filename of main file
$install[1]['email_config'] = 'EMAIL_CONFIGURATION'; // Email configuration
$install[1]['email']    = 'SITE_EMAIL';         // Site email address
$install[1]['url_register'] = 'REGISTRATION_URL';        // URL of register.php
$install[1]['file_name']    = 'AUCTIONS_FILE_NAME';     // Auctions file name
$install[1]['upload']   = 'IMAGE_UPLOAD_SAVE_TYPE';
$install[1]['upload_path']  = 'IMAGE_UPLOAD_PATH';
$install[1]['gdlib']    = 'IMAGECREATETRUECOLOR_SWITCH';
$install[1]['url_image_directory']      = 'URL_IMAGE_DIRECTORY';
$install[1]['abs_image_directory']      = 'IMAGE_UPLOAD_PATH';
$install[1]['maximum_upload_size']      = 'MAXIMUM_UPLOAD_SIZE';
$install[1]['site_email']           = 'SITE_EMAIL';
$install[1]['admin_registration_email']     = 'REGISTRATION_ADMIN_EMAIL';
$install[1]['admin_email'] = 'EMAIL';

// Default Email Address
$install[1]['email'] = 'auctions@yoursite.com';

// Encryption
$install[1]['encrypt']  = true;             // Encryption on or off

// Upgrade filename
$install[1]['upgrade'] = "upgrade_auctions_enterprise.php";

//-----------------------------------------------------------------

// Classified Enterprise
$install[2]["product_name"]  = "GeoClassifieds Enterprise";

// Tables
$install[2]['config_table'] = 'geodesic_classifieds_configuration';  // Configuration table
$install[2]['logins_table'] = 'geodesic_logins';         // Logins table
$install[2]['ad_config']    = 'geodesic_classifieds_ad_configuration';  // Ad configuration table
$install[2]['userdata']     = 'geodesic_userdata';  // Userdata Table

// Fields
$install[2]['url']      = 'CLASSIFIEDS_URL';        // URL of main file
$install[2]['filename'] = 'CLASSIFIEDS_FILE_NAME';      // Filename of main file
$install[2]['email_config'] = 'EMAIL_CONFIGURATION'; // Email configuration
$install[2]['email']    = 'SITE_EMAIL';         // Site email address
$install[2]['url_register'] = 'REGISTRATION_URL';        // URL of register.php
$install[2]['file_name']    = 'CLASSIFIEDS_FILE_NAME';      // Auctions file name
$install[2]['upload']   = 'IMAGE_UPLOAD_SAVE_TYPE';
$install[2]['upload_path']  = 'IMAGE_UPLOAD_PATH';
$install[2]['gdlib']    = 'IMAGECREATETRUECOLOR_SWITCH';
$install[2]['url_image_directory']      = 'URL_IMAGE_DIRECTORY';
$install[2]['abs_image_directory']      = 'IMAGE_UPLOAD_PATH';
$install[2]['maximum_upload_size']      = 'MAXIMUM_UPLOAD_SIZE';
$install[2]['site_email']           = 'SITE_EMAIL';
$install[2]['admin_registration_email']     = 'REGISTRATION_ADMIN_EMAIL';
$install[2]['admin_email'] = 'EMAIL';

// Default Email Address
$install[2]['email'] = 'classifieds@yoursite.com';

// Encryption Setting
$install[2]['encrypt']  = false;                // Encryption on or off

// Upgrade filename
$install[2]['upgrade'] = "upgrade_enterprise_classified.php";

//-----------------------------------------------------------------

// GeoCore
$install[3]["product_name"]  = "GeoCore";

// Tables
$install[3]['config_table'] = 'geodesic_classifieds_configuration';  // Configuration table
$install[3]['logins_table'] = 'geodesic_logins';         // Logins table
$install[3]['ad_config']    = 'geodesic_classifieds_ad_configuration';  // Ad configuration table

// Fields
$install[3]['url']      = 'CLASSIFIEDS_URL';        // URL of main file
$install[3]['filename'] = 'CLASSIFIEDS_FILE_NAME';      // Filename of main file
$install[3]['email_config'] = 'EMAIL_CONFIGURATION'; // Email configuration
$install[3]['email']    = 'SITE_EMAIL';         // Site email address
$install[3]['url_register'] = 'REGISTRATION_URL';        // URL of register.php
$install[3]['file_name']    = 'CLASSIFIEDS_FILE_NAME';      // Auctions file name
$install[3]['upload']   = 'IMAGE_UPLOAD_SAVE_TYPE';
$install[3]['upload_path']  = 'IMAGE_UPLOAD_PATH';
$install[3]['gdlib']    = 'IMAGECREATETRUECOLOR_SWITCH';
$install[3]['url_image_directory']      = 'URL_IMAGE_DIRECTORY';
$install[3]['abs_image_directory']      = 'IMAGE_UPLOAD_PATH';
$install[3]['maximum_upload_size']      = 'MAXIMUM_UPLOAD_SIZE';
$install[3]['site_email']           = 'SITE_EMAIL';
$install[3]['admin_registration_email']     = 'REGISTRATION_ADMIN_EMAIL';

// Default Email Address
$install[3]['email'] = 'classifieds@yoursite.com';

// Encryption Setting
$install[3]['encrypt']  = false;                // Encryption on or off

// Upgrade filename
$install[3]['upgrade'] = "upgrade_geocore.php";

//-----------------------------------------------------------------

// Classauctions
$install[4]["product_name"]  = "GeoClassAuctions";

// Tables
$install[4]['config_table'] = 'geodesic_classifieds_configuration'; // Configuration table
$install[4]['logins_table'] = 'geodesic_logins';            // Logins table
$install[4]['ad_config']    = 'geodesic_classifieds_ad_configuration';  // Ad configuration table
$install[4]['userdata']     = 'geodesic_userdata';  // Userdata Table

// Fields
$install[4]['url']                      = 'CLASSIFIEDS_URL';    // URL of main file
$install[4]['filename']                 = 'CLASSIFIEDS_FILE_NAME';  // Filename of main file
$install[4]['email_config']             = 'EMAIL_CONFIGURATION';    // Email configuration
$install[4]['email']                    = 'SITE_EMAIL';             // Site email address
$install[4]['url_register']             = 'REGISTRATION_URL';   // URL of register.php
$install[4]['file_name']                = 'CLASSIFIEDS_FILE_NAME';  // File name
$install[4]['upload']                   = 'IMAGE_UPLOAD_SAVE_TYPE';
$install[4]['upload_path']              = 'IMAGE_UPLOAD_PATH';
$install[4]['gdlib']                    = 'IMAGECREATETRUECOLOR_SWITCH';
$install[4]['url_image_directory']      = 'URL_IMAGE_DIRECTORY';
$install[4]['abs_image_directory']      = 'IMAGE_UPLOAD_PATH';
$install[4]['maximum_upload_size']      = 'MAXIMUM_UPLOAD_SIZE';
$install[4]['site_email']               = 'SITE_EMAIL';
$install[4]['admin_registration_email'] = 'REGISTRATION_ADMIN_EMAIL';
$install[4]['admin_email']              = 'EMAIL';

// Default Email Address
$install[4]['email'] = 'classifieds@yoursite.com';

// Encryption Setting
$install[4]['encrypt']  = true;             // Encryption on or off

// Upgrade filename
$install[4]['upgrade'] = "upgrade_classauction.php";

//-----------------------------------------------------------------

// Classifieds Premier and Below
$install[5]["product_name"]  = "GeoClassifieds";

// Tables
$install[5]['config_table'] = 'geodesic_classifieds_configuration';      // Configuration table
$install[5]['logins_table'] = 'geodesic_classifieds_logins';             // Logins table
$install[5]['ad_config']    = 'geodesic_classifieds_ad_configuration';  // Ad configuration table
$install[5]['userdata']     = 'geodesic_classifieds_userdata';          // Userdata Table

// Fields
$install[5]['url']          =   'CLASSIFIEDS_URL';      // URL of main file
$install[5]['filename']     =   'CLASSIFIEDS_FILE_NAME';        // Filename of main file
$install[5]['email_config'] =   'EMAIL_CONFIGURATION';  // Email configuration
$install[5]['email']        =   'SITE_EMAIL';           // Site email address
$install[5]['url_register'] =   'REGISTRATION_URL';     // URL of register.php
$install[5]['file_name']    =   'CLASSIFIEDS_FILE_NAME';        // Classifieds file name
$install[5]['upload']       =   'IMAGE_UPLOAD_SAVE_TYPE';
$install[5]['upload_path']  = 'IMAGE_UPLOAD_PATH';
$install[5]['gdlib']        =   'IMAGECREATETRUECOLOR_SWITCH';
$install[5]['url_image_directory']      =   'URL_IMAGE_DIRECTORY';
$install[5]['abs_image_directory']      =   'IMAGE_UPLOAD_PATH';
$install[5]['maximum_upload_size']      =   'MAXIMUM_UPLOAD_SIZE';
$install[5]['site_email']               =   'SITE_EMAIL';
$install[5]['admin_registration_email'] =   'REGISTRATION_ADMIN_EMAIL';
$install[5]['admin_email']              =   'EMAIL';

// Default Email Address
$install[5]['email'] = 'classifieds@yoursite.com';

// Encryption Setting
$install[5]['encrypt']  = false;                // Encryption on or off

// Upgrade filename
$install[5]['upgrade'] = "upgrade_classified.php";

//-----------------------------------------------------------------

// Auctions Premier
$install[6]["product_name"]  = "GeoAuctions";

// Tables
$install[6]['config_table'] = 'geodesic_auctions_configuration';     // Configuration table
$install[6]['logins_table'] = 'geodesic_auctions_logins';                // Logins table
$install[6]['ad_config']    = 'geodesic_auctions_ad_configuration'; // Ad configuration table
$install[6]['userdata']     = 'geodesic_auctions_userdata';         // Userdata Table

// Fields
$install[6]['url']          =   'AUCTIONS_URL';     // URL of main file
$install[6]['filename']     =   'AUCTIONS_FILE_NAME';       // Filename of main file
$install[6]['email_config'] =   'EMAIL_CONFIGURATION';  // Email configuration
$install[6]['email']        =   'SITE_EMAIL';           // Site email address
$install[6]['url_register'] =   'REGISTRATION_URL';     // URL of register.php
$install[6]['file_name']    =   'AUCTIONS_FILE_NAME';       // Classifieds file name
$install[6]['upload']       =   'IMAGE_UPLOAD_SAVE_TYPE';
$install[6]['upload_path']  = 'IMAGE_UPLOAD_PATH';
$install[6]['gdlib']        =   'IMAGECREATETRUECOLOR_SWITCH';
$install[6]['url_image_directory']      =   'URL_IMAGE_DIRECTORY';
$install[6]['abs_image_directory']      =   'IMAGE_UPLOAD_PATH';
$install[6]['maximum_upload_size']      =   'MAXIMUM_UPLOAD_SIZE';
$install[6]['site_email']               =   'SITE_EMAIL';
$install[6]['admin_registration_email'] =   'REGISTRATION_ADMIN_EMAIL';
$install[6]['admin_email']              =   'EMAIL';

// Default Email Address
$install[6]['email'] = 'auctions@yoursite.com';

// Encryption Setting
$install[6]['encrypt']  = false;                // Encryption on or off

// Upgrade filename
$install[6]['upgrade'] = "upgrade_auctions_premier.php";

//-----------------------------------------------------------------

//add new ones here...  I don't really follow this and not going to try understanding.
for ($i = 1; $i <= 6; $i++) {
    //set them here and they are set for each option...
    //$install[$i]['external_media_url'] = 'external_media_url';
}
