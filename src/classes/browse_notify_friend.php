<?php

class Notify_friend extends geoBrowse
{
    var $subcategory_array = array();
    var $notify_data = array();
    var $debug_notify = 0;
    public $json = false;

//########################################################################

    public function __construct($classified_user_id, $language_id, $category_id = 0, $page = 0, $classified_id = 0, $affiliate = 0, $product_configuration = 0)
    {
        $db = $this->db = DataAccess::getInstance();

        $this->json = (isset($_GET['json']) && $_GET['json'] && geoAjax::isAjax());

        if ($this->debug_notify) {
            echo $affiliate . " is affiliate in constructor<bR>\n";
        }
        if ($category_id) {
            $this->site_category = (int)$category_id;
        } elseif ($classified_id) {
            $listing = geoListing::getListing($classified_id);
            if ($listing && $listing->category) {
                $this->site_category = (int)$listing->category;
            }
        } else {
            $this->site_category = 0;
        }
        if ($limit) {
            $this->browse_limit = (int)$limit;
        }

        $this->get_ad_configuration($db);
        if ($page) {
            $this->page_result = (int)$page;
        } else {
            $this->page_result = 1;
        }

        parent::__construct();
    } //end of function Notify_friend

//###########################################################

    function notify_friend_form($classified_id = 0)
    {
        if (!$classified_id) {
            //can't tell a friend about an ad if we don't know which it is
            trigger_error('ERROR NOTIFY_FRIEND: no classified_id');
            return false;
        }

        $listing = geoListing::getListing($classified_id);

        if (!$listing) {
            //not a valid listing
            $this->browse_error();
            return false;
        }

        $db = DataAccess::getInstance();
        $this->page_id = 4;
        $this->get_text();
        $tpl_vars = array();

        if (count($this->error_message['send_a_friend']) > 0) {
            $tpl_vars['errors'] = $this->error_message['send_a_friend'];
            $css['errors'] = 'notify_friend_error';
        }
        if ($this->json) {
            if ($this->error_message['securityCode']) {
                $tpl_vars['errors'][] = $this->error_message['securityCode'];
                $tpl_vars['resetSecurity'] = true;
            }

            $tpl_vars['json'] = true;
            $return = array();

            $tpl = new geoTemplate(geoTemplate::SYSTEM, 'browsing');
            $tpl->assign($tpl_vars);

            $return['success'] = 0;
            $return['message'] = $tpl->fetch('contact_forms/common/json.tpl');
            return $this->_jsonResponse($return);
        }

        //get current user
        $user = geoUser::getUser(geoSession::getInstance()->getUserID());
        $senders_name = trim($user->firstname . " " . $user->lastname);
        $senders_email = trim($user->email);


        $tpl_vars['form_target'] = $this->affiliate_id ? ($db->get_site_setting('affiliate_url') . '?aff=' . $this->affiliate_id . '&amp;') : ($db->get_site_setting('classifieds_file_name') . '?');
        $tpl_vars['form_target'] .= "a=12&amp;b=" . $classified_id;

        $css = array();

        $tpl_vars['section_title'] = $this->messages[603];
        $css['section_title'] = 'section_title';

        $tpl_vars['page_title'] = $this->messages[41];
        $css['page_title'] = 'notify_friend_page_title';

        $tpl_vars['instructions'] = $this->messages[42];
        $css['instructions'] = 'notify_friend_form_instructions';

        $labels = $values = array();

        //friend's name
        $labels['friends_name'] = $this->messages[43];
        $values['friends_name'] = $this->notify_data['friends_name'];

        // friend's email
        $labels['friends_email'] = $this->messages[44];
        $values['friends_email'] = $this->notify_data['friends_email'];


        //your name
        $labels['your_name'] = $this->messages[45];
        $values['your_name'] = $senders_name;

        // your email
        $labels['your_email'] = $this->messages[46];
        $values['your_email'] = $senders_email;

        $labels['comment'] = $this->messages[47];
        $values['comment'] = $this->notify_data['senders_comments'];

        $tpl_vars['labels'] = $labels;
        $tpl_vars['values'] = $values;

        $secure =& geoAddon::getUtil('security_image');

        if ($secure && $secure->check_setting('messaging')) {
            $security_text =& geoAddon::getText('geo_addons', 'security_image');
            $error = $this->error_message['securityCode'];
            $section = "message";
            $tpl_vars['security_image'] = $secure->getHTML($error, $security_text, $section, false);
            geoView::getInstance()->addTop($secure->getJs());
        }

        $tpl_vars['submit'] = $this->messages[52];
        $css['submit'] = 'notify_friend_input_box';
        $tpl_vars['reset'] = $this->messages[500116];
        $css['reset'] = 'notify_friend_input_box';
        $tpl_vars['link_text'] = $this->messages[51];
        $css['link_text'] = 'notify_friend_link_text';

        $tpl_vars['link'] = $this->affiliate_id ? ($db->get_site_setting('affiliate_url') . '?aff=' . $this->affiliate_id . '&amp;') : ($db->get_site_setting('classifieds_file_name') . '?');
        $tpl_vars['link'] .= 'a=2&amp;b=' . $classified_id;

        $tpl_vars['css'] = $css;
        geoView::getInstance()->setBodyTpl('contact_forms/friend_form.tpl', '', 'browsing')->setBodyVar($tpl_vars);
        $this->error_found = 0;
        $this->display_page();
        return true;
    } //end of notify_friend_form

//########################################################################

    function verify_notify_friend($classified_id = 0, $info = 0)
    {
        $db = DataAccess::getInstance();
        $this->page_id = 4;
        $this->get_text();

        if (($classified_id) && ($info)) {
            $this->error_found = 0;
            if (!geoSession::getInstance()->getUserID()) {
                //check for senders stuff
                if (strlen(trim($info["senders_email"])) > 0) {
                    if (!geoString::isEmail($info["senders_email"])) {
                        $this->error_message['send_a_friend'][] = $this->messages[500620];
                        $this->error_found++;
                    }
                } else {
                    $this->error_message["send_a_friend"][] = $this->messages[500621];
                    $this->error_found++;
                }

                if (strlen(trim($info["senders_name"])) == 0) {
                    $this->error_message["send_a_friend"][] = $this->messages[500622];
                    $this->error_found++;
                }
            }
            if (strlen(trim($info["friends_email"])) > 0) {
                if (!geoString::isEmail($info["friends_email"])) {
                    $this->error_message['send_a_friend'][] = $this->messages[500623];
                    $this->error_found++;
                }
            } else {
                $this->error_message["send_a_friend"][] = $this->messages[500624];
                $this->error_found++;
            }

            if (strlen(trim($info["friends_name"])) == 0) {
                $this->error_message["send_a_friend"][] = $this->messages[500625];
                $this->error_found++;
            }

            $secure_image = geoAddon::getUtil('security_image');
            if ($secure_image && $secure_image->check_setting('messaging')) {
                if (!$secure_image->check_security_code($info["securityCode"])) {
                    $security_text =& geoAddon::getText('geo_addons', 'security_image');

                    $this->error_message['securityCode'] = $security_text['error'];
                    $this->error_found++;
                }
            }

            if ($this->error_found) {
                if (!geoSession::getInstance()->getUserID()) {
                    $this->notify_data["senders_name"] = $info["senders_name"];
                    $this->notify_data["senders_email"] = $info["senders_email"];
                }

                $this->notify_data["friends_name"] = $info["friends_name"];
                $this->notify_data["friends_email"] = $info["friends_email"];
                $this->notify_data["senders_comments"] = $info["senders_comments"];
                $this->notify_data["classified_id"] = $info["classified_id"];

                return false;
            } else {
                return true;
            }
        } else {
            return false;
        }
    } //end of function verify_notify_friend

//########################################################################

    function notify_friend_($classified_id = 0, $info = 0)
    {
        $this_copy =& $this;
        $overload = geoAddon::triggerDisplay('overload_Notify_friend_notify_friend_', array ('classified_id' => $classified_id, 'info' => $info, 'this' => $this_copy), geoAddon::OVERLOAD);
        if ($overload !== geoAddon::NO_OVERLOAD) {
            return $overload;
        }

        $db = DataAccess::getInstance();

        $this->page_id = 5;
        $this->get_text();
        if (($classified_id) && ($info)) {
            if (geoSession::getInstance()->getUserID()) {
                $this->sql_query = "select email,firstname,lastname from " . $this->userdata_table . " where id = " . geoSession::getInstance()->getUserID();
                $result = $db->Execute($this->sql_query);
                if ($this->debug_notify) {
                    echo $this->sql_query . "<br>\n";
                }
                if (!$result) {
                    //$this->body .=$this->sql_query." is the state query<br>\n";
                    $this->error_message = $this->messages[832];
                    return false;
                } elseif ($result->RecordCount() == 1) {
                    $show_user = $result->FetchNextObject();
                    $senders_name = $show_user->FIRSTNAME . " " . $show_user->LASTNAME;
                    $senders_email = $show_user->EMAIL;
                } else {
                    $this->error_message = $this->messages[832];
                    return false;
                }
            } else {
                $senders_name = $info["senders_name"];
                $senders_email = $info["senders_email"];
            }

            if (geoString::isEmail($senders_email) && geoString::isEmail($info["friends_email"])) {
                $mailto = $info["friends_email"];
                $subject = stripslashes(urldecode($this->messages[36] . " " . $senders_name));


                $tpl = new geoTemplate('system', 'emails');
                $tpl->assign('introduction', $this->messages[37]);
                $tpl->assign('friendsName', strip_tags($info['friends_name']));
                $tpl->assign('sendersName', strip_tags($senders_name));
                $tpl->assign('sendersEmail', $senders_email);
                $tpl->assign('messageBody', $this->messages[38]);
                $tpl->assign('commentsIntro', $this->messages[39]);
                $tpl->assign('sendersComments', strip_tags(geoString::specialCharsDecode($info['senders_comments'])));
                $tpl->assign('linkInstructions', $this->messages[40]);

                //make listing data available to template
                $listing = geoListing::getListing($classified_id);
                if ($listing) {
                    $tpl->assign('listing', $listing->toArray());
                    $tpl->assign('listingURL', $listing->getFullUrl());
                }

                $ip = $_SERVER['REMOTE_ADDR'];
                $host = @gethostbyaddr($ip);
                $tpl->assign('senderIP', $ip);
                $tpl->assign('senderHost', $host);
                $message = $tpl->fetch('communication/notify_friend.tpl');

                //let addons overload just the mail send part of this if wanted

                $addon_info = array (
                    'classified_id' => $classified_id,
                    'info' => $info,
                    'this' => $this,
                    'message' => $message,
                    'mailto' => $mailto,
                    'senders_name' => $info["senders_name"],
                    'senders_email' => $info["senders_email"],
                    'seller' => $seller,
                    'listing' => $listing
                );
                $overload = geoAddon::triggerDisplay('overload_Notify_friend_notify_friend_sendMail', $addon_info, geoAddon::OVERLOAD);
                if ($overload !== geoAddon::NO_OVERLOAD) {
                    return $overload;
                }

                geoEmail::sendMail($mailto, $subject, $message, $senders_email, $senders_email, 0, 'text/html');

                if (strlen(trim($db->get_site_setting('admin_email_bcc'))) > 0 && geoPC::is_ent()) {
                    geoEmail::sendMail($db->get_site_setting('admin_email_bcc'), $subject, $message, $senders_email, $senders_email, 0, 'text/html');
                }
                if ($listing) {
                    $listing->forwarded = $listing->forwarded + 1;
                }
                return true;
            }
        } else {
            return false;
        }
    } //end of function notify_friend

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    function notify_success($classified_id)
    {
        $db = DataAccess::getInstance();
        $this->page_id = 4;
        $this->get_text();
        $tpl_vars = array();

        if ($this->json) {
            $return = array();
            $return['success'] = 1;
            $tpl = new geoTemplate(geoTemplate::SYSTEM, 'browsing');
            $tpl_vars['json'] = 1;
            $tpl_vars['success'] = $this->messages[50];
            $tpl->assign($tpl_vars);
            $return['message'] = $tpl->fetch('contact_forms/common/json.tpl');
            return $this->_jsonResponse($return);
        }

        $tpl_vars['section_title'] = $this->messages[603];
        $tpl_vars['page_title'] = $this->messages[41];
        $tpl_vars['instructions'] = $this->messages[50];

        $tpl_vars['link'] = ($this->affiliate_id) ? ($db->get_site_setting('affiliate_url') . '?aff=' . $this->affiliate_id . '&amp;') : ($db->get_site_setting('classifieds_file_name') . '?');
        $tpl_vars['link'] .= 'a=2&amp;b=' . $classified_id;

        $tpl_vars['link_text'] = $this->messages[51];

        $tpl_vars['css'] = array(
            'page_title' => 'notify_friend_page_title',
            'instructions' => 'notify_friend_form_instructions',
            'link_text' => 'notify_friend_link_text'
        );

        geoView::getInstance()->setBodyTpl('contact_forms/friend_success.tpl', '', 'browsing')->setBodyVar($tpl_vars);
        $this->display_page();
        return true;
    }

    private function _jsonResponse($data)
    {
        $ajax = geoAjax::getInstance();
        $ajax->jsonHeader();
        echo $ajax->encodeJSON($data);
        geoView::getInstance()->setRendered(true);
        return true;
    }

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    function go_to_classifieds()
    {
        header("Location: " . geoFilter::getBaseHref() . DataAccess::getInstance()->get_site_setting('classifieds_file_name') . "?" . $_SERVER["QUERY_STRING"]);
        exit;
    } // end of function go_to_classifieds
}
