<?php

//addons/email_sendDirect/info.php

# Email Send Direct Addon (Main email sender)

class addon_email_sendDirect_info
{
    //The following are required variables
    var $name = 'email_sendDirect';
    var $version = '2.0.1';
    var $title = 'Main Email Sender';
    var $author = "Geodesic Solutions LLC.";
    var $description = 'This is the main email sender.  It sends email using 
linux sendmail function, or using SMTP connection, or using the native mail() function, depending on settings in the admin.
<br /><br />
It sends the email right away.  If your email settings are mis-configured, it
can cause pages that send out emails to "freeze up".';

    var $icon_image = 'menu_mail.gif'; //located in addons/example/icon.gif
    var $info_url = 'http://geodesicsolutions.com/component/content/article/55-miscellaneous/214-main-email-sender.html?directory=64';
    var $core_events = array ('email');

    var $exclusive = array(
        'email' => true,  //do not load this addon at the same time
                        //as another addon using email
    );
}

/**
 * Email sendDirect Changelog
 *
 * GeoCore v18.01.0
 *  - Fixed username/password settings not being used for SMTP
 *
 * 2.0.1 - 7.0.0
 *  - Add ability to have email header (not bumping version since this is system addon)
 *
 * 2.0.1 - 5.1.2
 *  - (re?)added ability to specify email salutation.
 *  - No need to actually bump version, a and this is sytem addon, so staying at 2.0.1
 *
 * 2.0.1 - 4.0.0RC11
 *  - First version using changelog block for email addon
 *  - Added "force from email" as a setting, for sites that won't send emails unless the from is valid on the
 *    same domain name or whatever.
 *
 */
