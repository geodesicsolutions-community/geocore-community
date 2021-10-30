<?php

//addons/contact_us/admin.php

# Contact us addon

require_once ADDON_DIR . 'contact_us/info.php';

class addon_contact_us_admin extends addon_contact_us_info
{
    public function init_pages()
    {
        #menu_page::addonAddPage($index, $parent, $title, $addon_name, $image, $type, $replace_existing);
        menu_page::addonAddPage('addon_contact_us_main', '', 'Settings', $this->name, '');
    }

    public function display_addon_contact_us_main()
    {
        $admin = $db = 1;
        include GEO_BASE_DIR . 'get_common_vars.php';

        $reg = geoAddon::getRegistry($this->name);
        $tpl_vars = array();

        $tpl_vars['adminMsgs'] = geoAdmin::m();

        $tpl_vars['msgs'] = geoAddon::getText($this->auth_tag, $this->name);

        //add newlines between e-mails so easier to read
        $tpl_vars['dept_1_email'] = explode(',', $reg->get('dept_1_email', $db->get_site_setting('site_email')));
        $tpl_vars['dept_1_email'] = implode(",\n", $tpl_vars['dept_1_email']);

        $tpl_vars['dept_2_email'] = explode(',', $reg->get('dept_2_email', $db->get_site_setting('site_email')));
        $tpl_vars['dept_2_email'] = implode(",\n", $tpl_vars['dept_2_email']);

        $tpl_vars['show_ip'] = $reg->show_ip;
        $tpl_vars['subject_prefix'] = $reg->get('subject_prefix', 'contact us - ');

        $admin->v()->setBodyVar($tpl_vars)->setBodyTpl('admin/settings.tpl', $this->name);
    }

    public function update_addon_contact_us_main()
    {
        $admin = 1;
        include GEO_BASE_DIR . 'get_common_vars.php';



        $reg = geoAddon::getRegistry($this->name);

        $dept1 = trim($_POST['dept_1_email']);
        $dept1 = explode(',', $dept1);

        $emails1 = array();
        foreach ($dept1 as $email) {
            $email = trim($email);
            if (geoString::isEmail($email)) {
                $emails1[] = $email;
            } else {
                $admin->userError('Invalid E-Mail (' . $email . ') specified in department 1.');
                return false;
            }
        }

        $dept2 = trim($_POST['dept_2_email']);
        $dept2 = explode(',', $dept2);

        $emails2 = array();
        foreach ($dept2 as $email) {
            $email = trim($email);
            if (geoString::isEmail($email)) {
                $emails2[] = $email;
            } else {
                $admin->userError('Invalid E-Mail (' . $email . ') specified in department 2.');
                return false;
            }
        }
        if (geoPC::is_trial()) {
            //Do NOT allow changing department
            $admin->userNotice('Note that the department e-mails cannot be changed from the default in admin demo trials, in order to prevent abuse by would-be spammers.  This restriction is not in place for full installations.');
        } else {
            $reg->dept_1_email = '' . implode(',', $emails1);
            $reg->dept_2_email = '' . implode(',', $emails2);
        }
        $reg->show_ip = (isset($_POST['show_ip']) && $_POST['show_ip']) ? 1 : false;
        $reg->subject_prefix = $_POST['subject_prefix'];
        $reg->save();
        return true;
    }

    public function init_text($language_id)
    {
        $email_replace_message = 'This is used as plain-text in an e-mail, so <strong>no HTML</strong> as it will be displayed as-is.  The following "tags" will be replaced as noted below.  Note that these are NOT normal smarty tags, this is a simple search/replace.<br /><br />
				<strong>{listing_id}</strong> - The listing ID number<br />
				<strong>{listing_title}</strong> - The listing title (not recommended for use in the e-mail subject, as it may cause e-mail to be marked as spam by your e-mail software)';
        $default_addon_text = array
        (
            'section_title' => array (
                'section' => 'Contact Form Labels',
                'name' => 'Contact Form section title',
                'desc' => '',
                'type' => 'input',
                'default' => 'Contact Form'
            ),
            'section_desc' => array (
                'section' => 'Contact Form Labels',
                'name' => 'Contact Form description',
                'desc' => 'Displays in contact form box at the top of the form',
                'type' => 'textarea',
                'default' => '(*) Required Fields'
            ),
            'ip_label' => array (
                'section' => 'Contact Form Labels',
                'name' => 'IP Address Label',
                'desc' => '',
                'type' => 'input',
                'default' => 'Your IP:'
            ),
            'dept_label' => array (
                'section' => 'Contact Form Labels',
                'name' => 'Department Label',
                'desc' => '',
                'type' => 'input',
                'default' => 'Department:'
            ),
            'dept_1' => array (
                'section' => 'Contact Form Labels',
                'name' => 'Department 1 Label',
                'desc' => '',
                'type' => 'input',
                'default' => 'Sales Inquiries'
            ),
            'dept_2' => array (
                'section' => 'Contact Form Labels',
                'name' => 'Department 2 Label',
                'desc' => '',
                'type' => 'input',
                'default' => 'Support Inquiries'
            ),
            'dept_abuse' => array (
                'section' => 'Contact Form Labels',
                'name' => 'Department Abuse Label',
                'desc' => 'Note: It will simply display the text specified for the department, and will use department 1 e-mail to report abuse.',
                'type' => 'input',
                'default' => 'Report Abuse'
            ),
            'name_label' => array (
                'section' => 'Contact Form Labels',
                'name' => 'Name Field Label',
                'desc' => '',
                'type' => 'input',
                'default' => 'Your Name:'
            ),
            'email_label' => array (
                'section' => 'Contact Form Labels',
                'name' => 'E-Mail Field Label',
                'desc' => '',
                'type' => 'input',
                'default' => 'Your E-Mail:'
            ),
            'subject_label' => array (
                'section' => 'Contact Form Labels',
                'name' => 'Subject Field Label',
                'desc' => '',
                'type' => 'input',
                'default' => 'Subject:'
            ),
            'message_label' => array (
                'section' => 'Contact Form Labels',
                'name' => 'Message Field Label',
                'desc' => '',
                'type' => 'input',
                'default' => 'Message:'
            ),
            'send_button' => array (
                'section' => 'Contact Form Labels',
                'name' => 'Send message button text',
                'desc' => '',
                'type' => 'input',
                'default' => 'Send Message'
            ),
            'reset_button' => array (
                'section' => 'Contact Form Labels',
                'name' => 'reset button text',
                'desc' => '',
                'type' => 'input',
                'default' => 'Reset'
            ),
            'success_message' => array (
                'section' => 'Contact Form Labels',
                'name' => 'Success Message when email is sent',
                'desc' => 'Used on success page',
                'type' => 'textarea',
                'default' => 'Message Sent!  Thank you for contacting us, we will reply shortly.'
            ),
            'continue_button' => array (
                'section' => 'Contact Form Labels',
                'name' => 'continue button text',
                'desc' => 'Used on success page',
                'type' => 'input',
                'default' => 'Continue'
            ),
            'ajax_cancel' => array (
                'section' => 'Contact Form Labels',
                'name' => 'cancel button text',
                'desc' => 'Used when contact form loaded using AJAX lightbox.',
                'type' => 'input',
                'default' => 'Cancel'
            ),
            'error_dept' => array (
                'section' => 'Contact Form Errors',
                'name' => 'Error Message: Invalid Department',
                'desc' => '',
                'type' => 'input',
                'default' => 'Invalid Department Specified.'
            ),
            'error_email_blank' => array (
                'section' => 'Contact Form Errors',
                'name' => 'Error Message: e-mail left blank',
                'desc' => '',
                'type' => 'input',
                'default' => 'Your e-mail is required so that we can reply to your message.'
            ),
            'error_email_invalid' => array (
                'section' => 'Contact Form Errors',
                'name' => 'Error Message: invalid e-mail',
                'desc' => '',
                'type' => 'input',
                'default' => 'Invalid e-mail specified, please re-enter.'
            ),
            'error_subject' => array (
                'section' => 'Contact Form Errors',
                'name' => 'Error Message: No Subject',
                'desc' => '',
                'type' => 'input',
                'default' => 'Please enter the subject so we know what this is about.'
            ),
            'error_message' => array (
                'section' => 'Contact Form Errors',
                'name' => 'Error Message: No Message',
                'desc' => '',
                'type' => 'input',
                'default' => 'Please enter a message to send us.'
            ),
            'abuse_subject' => array (
                'section' => 'Report Abuse Defaults',
                'name' => 'Report Abuse default subject text',
                'desc' => $email_replace_message,
                'type' => 'input',
                'default' => 'Report Abuse for Listing #{listing_id}'
            ),
            'abuse_message_top' => array (
                'section' => 'Report Abuse Defaults',
                'name' => 'Report Abuse default - message top',
                'desc' => $email_replace_message,
                'type' => 'textarea',
                'default' => 'I am reporting abuse for listing #{listing_id} ({listing_title}) at the link:'
            ),
            'abuse_message_bottom' => array (
                'section' => 'Report Abuse Defaults',
                'name' => 'Report Abuse default - message bottom',
                'desc' => $email_replace_message,
                'type' => 'textarea',
                'default' => 'Reason:
(Enter reason here)'
            ),
        );
        return $default_addon_text;
    }
}
