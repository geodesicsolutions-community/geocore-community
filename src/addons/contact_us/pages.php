<?php

//addons/contact_us/pages.php
/**************************************************************************
Addon Created by Geodesic Solutions, LLC
Copyright (c) 2001-2018 Geodesic Solutions, LLC
All rights reserved
http://geodesicsolutions.com
see license attached to distribution
**************************************************************************/
##########GIT Build Data##########
##
## File Changed In GIT Commit:
## ##    7.1beta1-1165-g3b33473
##
##################################

# Contact us addon

require_once ADDON_DIR . 'contact_us/info.php';

class addon_contact_us_pages extends addon_contact_us_info
{
    public function main()
    {
        $db = 1;
        include GEO_BASE_DIR . 'get_common_vars.php';

        $showForm = true;//whether or not to show the form
        $errors = $tpl_vars = array();
        $reg = geoAddon::getRegistry($this->name);

        if ($reg->show_ip) {
            $tpl_vars['ip'] = $ip = $_SERVER['REMOTE_ADDR'];
        }

        $tpl_vars['msgs'] = $msgs = geoAddon::getText($this->auth_tag, $this->name);

        $secure = geoAddon::getUtil('security_image');

        if (isset($_POST['contact'])) {
            if (isset($_GET['reportAbuse'])) {
                //this is abuse report...  make the department still show report abuse
                $tpl_vars['reportAbuse'] = (int)$_GET['reportAbuse'];
            }

            $contact = $_POST['contact'];

            if ($secure && $secure->check_setting('messaging')) {
                if (!$secure->check_security_code($_POST['c']["securityCode"])) {
                    $security_text = geoAddon::getText('geo_addons', 'security_image');

                    $errors['securityCode'] = $security_text['error'];
                }
            }

            $dept = $tpl_vars['vals']['dept'] = (in_array($contact['dept'], array(1,2))) ? (int)$contact['dept'] : false;
            if (!$dept) {
                $errors['dept'] = $tpl_vars['msgs']['error_dept'];
            }

            //name - not required
            $name = $tpl_vars['vals']['name'] = trim($contact['name']);

            //email - required
            $email = $tpl_vars['vals']['email'] = trim($contact['email']);
            if (!$email) {
                $errors['email'] = $tpl_vars['msgs']['error_email_blank'];
            } elseif (!geoString::isEmail($email)) {
                $errors['email'] = $tpl_vars['msgs']['error_email_invalid'];
            }

            //subject - required
            $subject = $tpl_vars['vals']['subject'] = preg_replace('/[\n\r\t]+/', '', trim($contact['subject']));

            if (!$subject) {
                $errors['subject'] = $tpl_vars['msgs']['error_subject'];
            }

            //message - required
            $message = $tpl_vars['vals']['message'] = trim($contact['message']);
            if (!$message) {
                $errors['message'] = $tpl_vars['msgs']['error_message'];
            }

            $username = geoSession::getInstance()->getUsername();

            if (count($errors) == 0) {
                //no errors, send e-mail
                $showForm = false;

                $email_subject = $reg->get('subject_prefix', 'contact us - ') . $subject;
                $tpl = new geoTemplate('addon', 'contact_us');
                $mailVars = array();
                $mailVars['ip'] = $ip;
                $mailVars['name'] = $name;
                $mailVars['email'] = $email;
                $mailVars['subject'] = $subject;
                $mailVars['message'] = $message;
                $mailVars['username'] = geoSession::getInstance()->getUsername();
                $mailVars['show_ip'] = $reg->show_ip;
                $mailVars['dept'] = $dept . ' - ' . $tpl_vars['msgs']['dept_' . $dept];
                $tpl->assign($mailVars);
                $email_message = $tpl->fetch('emails/contact.tpl');

                $to_emails = explode(',', $reg->get('dept_' . $dept . '_email', $db->get_site_setting('site_email')));
                //send the e-mail, as an HTML e-mail
                geoEmail::sendMail(
                    $to_emails,
                    $email_subject,
                    $email_message,
                    $db->get_site_setting('site_email'),
                    $email,
                    0,
                    'text/html'
                );
            }
        } elseif (isset($_GET['reportAbuse'])) {
            //reporting abuse on a listing?!
            $listing_id = (int)$_GET['reportAbuse'];
            $listing = geoListing::getListing($listing_id);

            if ($listing_id && $listing && $listing->live) {
                //pre-fill the values
                $tpl_vars['reportAbuse'] = $listing_id;
                $abuse_msgs = $this->_parseText($msgs, $listing);

                $tpl_vars['vals']['subject'] = $abuse_msgs['abuse_subject'];//Report Abuse for Listing #'.$listing_id;

                $abuseTpl = new geoTemplate(geoTemplate::ADDON, $this->name);
                $abuseTpl->assign('listing_id', $listing_id);
                $abuseTpl->assign('listing_title', geoString::fromDB($listing->title));
                $abuseTpl->assign('listing_url', $listing->getFullUrl());
                $abuseTpl->assign('msgs', $abuse_msgs);

                $tpl_vars['vals']['message'] = $abuseTpl->fetch('report_abuse_default_message.tpl');
                unset($abuseTpl, $abuse_msgs);
            }
        }
        $tpl_vars['show_ip'] = $reg->show_ip;

        if ($secure && $secure->check_setting('messaging')) {
            $security_text = geoAddon::getText('geo_addons', 'security_image');
            $error = $errors['securityCode'];
            $section = "message";
            $tpl_vars['security_image'] = $secure->getHTML($error, $security_text, $section, false);
            geoView::getInstance()->addTop($secure->getJs());
        }

        $tpl_vars['errors'] = $errors;
        $tpl_file = ($showForm) ? 'contact_form.tpl' : 'contact_success.tpl';
        if (geoAjax::isAjax()) {
            //special case...  ONLY display the contact us form.
            $tpl_vars['is_ajax'] = true;
            $tpl = new geoTemplate(geoTemplate::ADDON, $this->name);
            $tpl->assign($tpl_vars);
            echo $tpl->fetch($tpl_file);
            geoView::getInstance()->setRendered(true);
        } else {
            //the "normal" way to do it
            geoView::getInstance()->setBodyTpl($tpl_file, 'contact_us')
                ->setBodyVar($tpl_vars);
        }
    }

    private function _parseText($msgs, $listing)
    {
        $find = array ('{listing_id}','{listing_title}');
        $replace = array ($listing->id, geoString::fromDB($listing->title));
        $msgs = str_replace($find, $replace, $msgs);
        return $msgs;
    }
}
