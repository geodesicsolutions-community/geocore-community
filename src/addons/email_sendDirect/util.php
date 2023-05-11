<?php

//addons/email_sendDirect/util.php

# Email Send Direct Addon (Main e-mail sender)

class addon_email_sendDirect_util
{
    private $queue; //queue of messages in case some messages attempt to be sent before a connection is made.
    private $swift;
    function connect()
    {
        $db = DataAccess::getInstance();

        //make sure the main swift libraries are included.
        require_once(CLASSES_DIR . PHP5_DIR . 'swift5/swift_required.php');

        $connect_type = $db->get_site_setting('email_server_type');

        if ($connect_type == 'sendmail') {
            //can accept a parameter of the console command to run sendmail, if non-standard (/usr/sbin/sendmail -bs)
            $transport = Swift_SendmailTransport::newInstance();
        } elseif ($connect_type == 'mail') {
            //compatibility, use php's mail() function, but wrapped by the swift mailer.
            //Note: there is ability to specify how the $additional_parameters variable is sent, see documentation
            //for the native mail connection on swiftmailer.org and php.net/mail
            $transport = Swift_MailTransport::newInstance();
        } else {
            $server = $db->get_site_setting('email_SMTP_server') ? $db->get_site_setting('email_SMTP_server') : 'localhost';
            $port = $db->get_site_setting('email_SMTP_port') > 0 ? $db->get_site_setting('email_SMTP_port') : 25;
            switch ($db->get_site_setting('email_server_type')) {
                case 'smtp_auth_tls':
                case 'smtp_tls':
                    $security = 'tls';
                    break;
                case 'smtp_auth_ssl':
                case 'smtp_ssl':
                    $security = 'ssl';
                    break;
                default:
                    $security = null;
                    break;
            }
            $transport = Swift_SmtpTransport::newInstance($server, $port, $security);
            if ($db->get_site_setting('email_username')) {
                $transport->setUsername($db->get_site_setting('email_username'));
            }
            if ($db->get_site_setting('email_password')) {
                $transport->setPassword($db->get_site_setting('email_password'));
            }
        }
        $this->swift = Swift_Mailer::newInstance($transport);
        return true;
    }

    function core_email($message_data)
    {
        $db = DataAccess::getInstance();
        trigger_error('DEBUG SENDMAIL: Message data used BEFORE all processing: ' . print_r($message_data, 1));
        //set default settings.
        //if type is not set, set it to text/plain
        $message_data['type'] = (strlen($message_data['type']) > 1) ? $message_data['type'] : 'text/plain';
        //if from is not set, set it to site_email
        $message_data['from'] = (strlen($message_data['from']) > 1) ? $message_data['from'] : $db->get_site_setting('site_email');
        //set encoding type, to allow manual encoding
        $message_data['encoding'] = (isset($message_data['encoding']) && strlen($message_data['encoding']) > 1) ? $message_data['encoding'] : $db->get_site_setting('email_encoding_type');
        //set charset
        $message_data['charset'] = (isset($message_data['charset']) && strlen($message_data['charset']) > 1) ? $message_data['charset'] : null;

        $site_email_header = $db->get_site_setting('site_email_header', 1);

        if (strlen(trim($site_email_header)) > 0) {
            if ($message_data['type'] == 'text/html') {
                $sep = "\n<br /><br />\n";
            } else {
                $sep = "\n\n";
            }
            $message_data['content'] = $site_email_header . $sep . $message_data['content'];
        }

        $site_email_footer = $db->get_site_setting('site_email_footer', 1);

        if (strlen(trim($site_email_footer)) > 0) {
            if ($message_data['type'] == 'text/html') {
                $sep = "\n<br /><br />\n";
            } else {
                $sep = "\n\n";
            }
            $message_data['content'] .= $sep . $site_email_footer;
        }


        if (!defined('IN_ADMIN')) {
            //counter-act user input filters, if on client side.
            //don't do this if in the admin, since input is not filtered if in admin.
            $message_data['subject'] = geoString::specialCharsDecode($message_data['subject']);
            if ($message_data['type'] != 'text/html') {
                $message_data['content'] = geoString::specialCharsDecode($message_data['content']);
            }
        }
        if ($message_data['type'] == 'text/plain') {
            //if plaintext, convert the content to look good...
            //strip any tags
            $message_data['content'] = geoString::specialCharsDecode(strip_tags($message_data['content']));
            if ($db->get_site_setting('email_convert_plain_to') != 'plain') {
                //fix special chars, or anything strip tags misses
                $message_data['content'] = geoString::specialChars($message_data['content']);
                //convert newlines to br's
                $message_data['content'] = nl2br($message_data['content']);
                $message_data['type'] = 'text/html';
                if ($db->get_site_setting('email_convert_url_to_link')) {
                    //convert any URLs to be links automatically...
                    $message_data['content'] = preg_replace('`(https?://[^\s<>\'"]+)`i', '<a href="$1">$1</a>', $message_data['content']);
                }
            }
        }

        //to cc and bcc
        if (isset($message_data['to']) && !is_array($message_data['to']) && strlen(trim($message_data['to'])) > 0) {
            //change to array of to addresses.
            $message_data['to'] = array ($message_data['to']);
        }
        if (isset($message_data['cc']) && !is_array($message_data['cc']) && strlen(trim($message_data['cc'])) > 0) {
            //change to array of to addresses.
            $message_data['cc'] = array ($message_data['cc']);
        }
        if (isset($message_data['bcc']) && !is_array($message_data['bcc']) && strlen(trim($message_data['bcc'])) > 0) {
            //change to array of to addresses.
            $message_data['bcc'] = array ($message_data['bcc']);
        }
        if (!isset($message_data['to']) || !is_array($message_data['to']) || count($message_data['to']) == 0) {
            //should not send e-mail, can't send w/o to field.
            trigger_error('ERROR SENDMAIL: No to address specified, cannot send e-mail.');
            return false;
        }
        //check to addresses, make sure theres at least one good to address
        $to_ok = false;
        foreach ($message_data['to'] as $key => $to) {
            if (strlen(trim($to)) > 0) {
                $to_ok = true;
            }
        }
        if (!$to_ok) {
            //none of the to e-mail addresses were ok
            trigger_error('ERROR SENDMAIL: No valid to address specified, cannot send e-mail.');
            return false;
        }

        //get rid of extra white space surrounding subject
        $message_data['subject'] = trim($message_data['subject']);

        //also get rid of newlines from subject.
        $message_data['subject'] = str_replace(array("\n","\r"), '', $message_data['subject']);

        //see if we should add the bcc all to the list of bcc recipients
        $bcc_all = $db->get_site_setting('bcc_all_email');
        if (strlen(trim($bcc_all)) > 0) {
            //add bcc e-mail address.
            $message_data['bcc'][] = $bcc_all;
        }

        //see if we need to force the from address
        $force_from = trim($db->get_site_setting('force_admin_email_from'));
        if ($force_from) {
            //set original from as the replyto, and set the force from as the new from.
            $from = $message_data['from'];
            $message_data['from'] = $force_from;
            if (!isset($message_data['replyto']) || !$message_data['replyto']) {
                //no reply to already specified, so use the original from address as reply to address.
                $message_data['replyto'] = $from;
            }
        }

        //see if sender header has been set for the site
        $sender_header = trim($db->get_site_setting('sender_email_header'));
        if ($sender_header) {
            $message_data['sender'] = $sender_header;
        }

        trigger_error('DEBUG SENDMAIL: Message data used after all processing: ' . print_r($message_data, 1));
        //add to queue
        $this->queue[] = $message_data;
        //make sure we are connected.
        if (!isset($this->swift) || !is_object($this->swift)) {
            //lets attempt to connect.
            try {
                if (!$this->connect()) {
                    //if we can't connect, we can't send the e-mail
                    trigger_error('ERROR SENDMAIL: Not able to start connection!');
                    return false;
                }
            } catch (Exception $e) {
                trigger_error('ERROR SENDMAIL: Exception caught, msg: ' . $e->getMessage());
            }
        }
        $this->flushMail();
    }

    /**
     * Attempts to send out any e-mails in the local queue.
     */
    function flushMail()
    {
        //go through the whole queue and send it all.
        if (is_array($this->queue) && is_object($this->swift)) {
            foreach ($this->queue as $messageData) {
                if (is_object($this->swift)) {
                    $message = Swift_Message::newInstance($messageData['subject'], $messageData['content'], $messageData['type'], $messageData['charset']);

                    if (strpos($messageData['from'], '<') !== false) {
                        //friendly name in use :: (address is something like "John Doe <john@doe.com>")
                        $name = strtok($messageData['from'], '<');
                        $addr = strtok('>');
                        $messageData['from'] = array(trim($addr) => trim($name));
                    }
                    $message->setFrom($messageData['from']);

                    if ($messageData['encoding']) {
                        $message->setEncoding($messageData['encoding']);
                    }

                    if ($messageData['sender']) {
                        $message->setSender($messageData['sender']);
                    }

                    if (isset($messageData['replyto']) && $messageData['replyto']) {
                        if (strpos($messageData['replyto'], '<') !== false) {
                            //friendly name in use :: (address is something like "John Doe <john@doe.com>")
                            $name = strtok($messageData['replyto'], '<');
                            $addr = strtok('>');
                            $messageData['replyto'] = array(trim($addr) => trim($name));
                        }
                        $message->setReplyTo($messageData['replyto']);
                    }

                    if (isset($messageData['to']) && is_array($messageData['to'])) {
                        foreach ($messageData['to'] as $address) {
                            if (strlen($address) > 0) {
                                if (strpos($address, '<') !== false) {
                                    //friendly name in use :: (address is something like "John Doe <john@doe.com>")
                                    $name = strtok($address, '<');
                                    $addr = strtok('>');
                                    $address = array(trim($addr) => trim($name));
                                }
                                $message->addTo($address);
                            }
                        }
                    }
                    if (isset($messageData['cc']) && is_array($messageData['cc'])) {
                        foreach ($messageData['cc'] as $address) {
                            if (strlen($address) > 0) {
                                if (strpos($address, '<') !== false) {
                                    //friendly name in use :: (address is something like "John Doe <john@doe.com>")
                                    $name = strtok($address, '<');
                                    $addr = strtok('>');
                                    $address = array(trim($addr) => trim($name));
                                }
                                $message->addCc($address);
                            }
                        }
                    }
                    if (isset($messageData['bcc']) && is_array($messageData['bcc'])) {
                        foreach ($messageData['bcc'] as $address) {
                            if (strlen($address) > 0) {
                                if (strpos($address, '<') !== false) {
                                    //friendly name in use :: (address is something like "John Doe <john@doe.com>")
                                    $name = strtok($address, '<');
                                    $addr = strtok('>');
                                    $address = array(trim($addr) => trim($name));
                                }
                                $message->addBcc($address);
                            }
                        }
                    }

                    trigger_error('DEBUG SENDMAIL STATS: Sending new e-mail, to:' . print_r($messageData['to'], 1) . ' from:' . print_r($messageData['from'], 1) . ' subject:' . $messageData['subject'] . ' body:' . $messageData['content'] . 'type: ' . $messageData['type']);

                    try {
                        $sent = $this->swift->send($message, $failures);
                        if (!$sent) {
                            trigger_error('ERROR SENDMAIL: Sending of message failed to all recipients!');
                        } elseif (count($failures) > 0) {
                            trigger_error('ERROR SENDMAIL: Sending of message failed to SOME (not all) recipients. Failures: ' . print_r($failures, 1));
                        }
                    } catch (Exception $e) {
                        trigger_error('ERROR SENDMAIL: Error caught from sending message, error: ' . $e->getMessage());
                    }
                    trigger_error('DEBUG SENDMAIL STATS: Finished sending e-mail.');
                } else {
                    //no longer connected for some reason...
                    break;
                }
            }
        }
        //reset the queue since we just sent them all.
        $this->queue = array();
    }
}
