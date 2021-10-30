<?php

class Notify_seller extends geoBrowse
{
    var $subcategory_array = array();
    var $notify_data = array();
    var $seller_message;
    public $json = false;

//########################################################################

    public function __construct($classified_user_id, $language_id, $category_id = 0, $page = 0, $classified_id = 0, $affiliate = 0, $product_configuration = 0)
    {
        $db = $this->db = DataAccess::getInstance();

        $this->json = (isset($_GET['json']) && $_GET['json'] && geoAjax::isAjax());

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
    } //end of function Notify_seller

//###########################################################

    function send_a_message_to_seller_form($classified_id = 0)
    {
        if (!$classified_id) {
            return false;
        }

        $db = DataAccess::getInstance();

        if (($this->classified_user_id && $db->get_site_setting('seller_contact') && (geoPC::is_ent() || geoPC::is_premier())) || !$db->get_site_setting('seller_contact') || (!geoPC::is_ent() && !geoPC::is_premier()) || $this->affiliate_id) {
            $this->page_id = 6;
            $this->get_text();
            $listing = geoListing::getListing($classified_id);

            if (!$listing) {
                //not a valid listing
                $this->browse_error();
                return false;
            }

            $anon = geoAddon::getRegistry('anonymous_listing');
            if ($anon) {
                //make sure they're not trying to contact the anon user
                $anon_user_id = $anon->get('anon_user_id');
                if (!$listing->email && $listing->seller == $anon_user_id) {
                    //this seller is the anonymous seller
                    //no way to get here through normal use of the software
                    //so it's ok to return false and error out
                    trigger_error("ERROR ANON: Can't contact the anonymous user!!");
                    return false;
                }
            }

            $current_user = geoUser::getUser(geoSession::getInstance()->getUserID());

            $user_data = geoUser::getUser($listing->seller);
            if (!$user_data) {
                //don't know about this user
                trigger_error('ERROR NOTIFY_SELLER: invalid target user');
                return false;
            }
            //give template access to complete seller & listing info
            $tpl_vars['seller'] = $user_data->toArray();
            $tpl_vars['listing'] = $listing->toArray();
            if ($this->error_message) {
                switch ($this->error_message) {
                    case 'FLOOD_LIMIT':
                        $tpl_vars['errors'][] = $this->messages[500725];
                        break;
                    case 'INVALID_DATA':
                        $tpl_vars['errors'][] = $this->messages[500724];
                        break;
                    default:
                        $tpl_vars['errors'][] = $this->error_message;
                }
            }
            if ($this->json) {
                $tpl_vars['json'] = true;
                $return = array();

                $tpl = new geoTemplate(geoTemplate::SYSTEM, 'browsing');
                $tpl->assign($tpl_vars);

                $return['success'] = 0;
                $return['message'] = $tpl->fetch('contact_forms/common/json.tpl');
                return $this->_jsonResponse($return);
            }

            //deny access if classified has ended
            if (!$listing->live && $listing->item_type == 1) {
                $tpl_vars['classified_ended'] = true;
            } else {
                $tpl_vars['form_target'] = $this->affiliate_id ? ($db->get_site_setting('affiliate_url') . '?aff=' . $this->affiliate_id . '&amp;') : ($db->get_site_setting('classifieds_file_name') . '?');
                $tpl_vars['form_target'] .= "a=13&amp;b=" . $classified_id;

                $css = array();

                $tpl_vars['section_title'] = $this->messages[605];
                $css['section_title'] = 'section_title';

                $tpl_vars['page_title'] = $this->messages[53];
                $css['page_title'] = 'send_seller_message_page_title';

                $tpl_vars['instructions'] = $this->messages[54];
                $css['instructions'] = 'send_seller_message_instructions';

                $labels = $values = array();

                //sellers username
                $labels['seller_name'] = $this->messages[55];
                $values['seller_name'] = $user_data->username;

                // title as subject
                $labels['listing_title'] = $this->messages[56];
                $values['listing_title'] = geoString::fromDB($listing->title) . ' - #' . $classified_id;

                // your email
                $labels['your_email'] = $this->messages[57];
                $email = ($_POST['c']['senders_email']) ? filter_var($_POST['c']['senders_email'], FILTER_SANITIZE_EMAIL) : '';
                $values['your_email'] = ($current_user) ? $current_user->email : $email;

                //contact name
                $labels['your_name'] = $this->messages[1366];
                $name = ($_POST['c']['senders_name']) ? filter_var($_POST['c']['senders_name'], FILTER_SANITIZE_EMAIL) : '';
                $values['your_name'] = ($current_user) ? geoString::fromDB($current_user->firstname) : $name;

                //phone to contact
                $labels['your_phone'] = $this->messages[1512];
                $phone = ($_POST['c']['senders_phone']) ? filter_var($_POST['c']['senders_phone'], FILTER_SANITIZE_EMAIL) : '';
                $values['your_phone'] = ($current_user) ? geoNumber::phoneFormat(geoString::fromDB($current_user->phone)) : $phone;

                //is this question public?
                //don't allow public questions if asker is not logged in. TODO: remove this restriction when the messaging system has been refactored and it's possible to do so
                $tpl_vars['canAskPublicQuestion'] = ($current_user && $current_user->id != $user_data->id && $db->get_site_setting('public_questions_to_show')) ? true : false;
                $labels['public_question'] = $this->messages[500890];
                $labels['public_question_no'] = $this->messages[500891];
                $labels['public_question_yes'] = $this->messages[500892];

                //comment
                $labels['comment'] = $this->messages[58];
                $values['comment'] = $this->seller_message;


                $tpl_vars['labels'] = $labels;
                $tpl_vars['values'] = $values;

                $secure = geoAddon::getUtil('security_image');

                if ($secure && $secure->check_setting('messaging')) {
                    $security_text =& geoAddon::getText('geo_addons', 'security_image');
                    $error = $this->_security_error;
                    $section = "message";
                    $tpl_vars['security_image'] = $secure->getHTML($error, $security_text, $section, false);
                    geoView::getInstance()->addTop($secure->getJs());
                }

                $tpl_vars['submit'] = $this->messages[60];
                $css['submit'] = 'send_seller_message_input_box';
                $tpl_vars['reset'] = $this->messages[500115];
                $css['reset'] = 'send_seller_message_input_box';
                $tpl_vars['link_text'] = $this->messages[1187];
                $css['link_text'] = 'send_seller_message_link_text';

                $tpl_vars['link'] = $this->affiliate_id ? ($db->get_site_setting('affiliate_url') . '?aff=' . $this->affiliate_id . '&amp;') : ($db->get_site_setting('classifieds_file_name') . '?');
                $tpl_vars['link'] .= 'a=2&amp;b=' . $classified_id;
            }

            $tpl_vars['css'] = $css;

            geoView::getInstance()->setBodyTpl('contact_forms/seller_form.tpl', '', 'browsing')->setBodyVar($tpl_vars);
            $this->error_found = 0;
            $this->display_page();
            return true;
        } else {
            include_once("authenticate_class.php");
            $auth = new Auth($db, $this->language_id);
            $auth->login_form($db, 0, 0, "a*is*" . $_REQUEST["a"] . "*and*b*is*" . $_REQUEST["b"], 1);
        }
    }

//########################################################################3
    private $_security_error;
    function notify_seller_($classified_id = 0, $info = 0)
    {
        //allow addon to "take over" this function using core event overload_Notify_seller_notify_seller_
        $overload = geoAddon::triggerDisplay('overload_Notify_seller_notify_seller_', array ('classified_id' => $classified_id, 'info' => $info, 'this' => $this), geoAddon::OVERLOAD);
        if ($overload !== geoAddon::NO_OVERLOAD) {
            return $overload;
        }

        $db = DataAccess::getInstance();

        $this->seller_message = $info["senders_comments"];
        if (!$classified_id || !$info) {
            //need that to send message
            return false;
        }
        $sendCheck = false;
        if ($this->classified_user_id && $db->get_site_setting('seller_contact') && ( geoPC::is_ent() || geoPC::is_premier() )) {
            $sendCheck = true;
        }
        if (!$db->get_site_setting('seller_contact')) {
            $sendCheck = true;
        }
        if ($this->affiliate_id) {
            $sendCheck = true;
        }
        if (!geoPC::is_ent() && !geoPC::is_premier()) {
            $sendCheck = true;
        }

        if (!$sendCheck) {
            //if none of the above passes, then can't send notice.
            return false;
        }
        $this->page_id = 7;
        $this->get_text();
        $secure_image =& geoAddon::getUtil('security_image');
        if ($secure_image && $secure_image->check_setting('messaging')) {
            if (!$secure_image->check_security_code($info["securityCode"])) {
                $security_text =& geoAddon::getText('geo_addons', 'security_image');
                $this->error_message = $security_text['error'];
                $this->_security_error = true;
                $this->error_found++;
                return false;
            }
        }
        if (strlen(trim($info['senders_comments'])) == 0 || strlen(trim($info['senders_email'])) == 0 || !geoString::isEmail($info["senders_email"])) {
            //can't send if comments or e-mail is blank, or if e-mail is not valid e-mail
            $this->error_message = 'INVALID_DATA';
            return false;
        }
        //figure out how to save the "from" field in the messages table
        if ($this->classified_user_id) {
            $fromField = 'message_from';
            $fromData = (int)$this->classified_user_id;
        } else {
            $fromField = 'message_from_non_user';
            $fromData = '' . $info['senders_email'];
        }

        if (($db->get_site_setting('contact_seller_limit')) && ($db->get_site_setting('contact_seller_limit') != 0)) {
            //there is a max number of contacts per hour set by the admin
            //check that the potential has not reached that limit

            //get time
            $cutoff_time = (geoUtil::time() - 3600);
            $sql = "SELECT COUNT(*) FROM " . geoTables::user_communications_table . " WHERE
					$fromField = ?
					AND `date_sent` > $cutoff_time";
            $data = array($fromData);

            //echo $sql."<br>\n";
            $count = (int)$db->GetOne($sql, $data);

            if ($count >= $db->get_site_setting('contact_seller_limit')) {
                //the current contacter has reached the admin defined limit
                $this->error_message = 'FLOOD_LIMIT';
                return false;
            } else {
                //the current contacter has not reached the admin defined limit
            }
        }

        $classified_id = intval($classified_id);
        //if item is classified, only allow contacting if listing is live.  If auction, allow contacting at any time, as long as listing has not been moved to archive list yet.
        $sql = "SELECT `id` FROM " . geoTables::classifieds_table . " WHERE `id` = " . $classified_id . " AND (`live` = 1 OR `item_type` = 2)";
        $result = $db->Execute($sql);
        if (!$result || $result->RecordCount() != 1) {
            $this->error_message = $this->messages[80];
            return false;
        }

        $listing = geoListing::getListing($classified_id);

        $listing->responded = $listing->responded + 1;

        //if this listing has an email address attached, use it
        $mailto = geoString::fromDB($listing->email);
        $anon = geoAddon::getRegistry('anonymous_listing');
        if ($anon) {
            //make sure they're not trying to contact the anon user
            $anon_user_id = $anon->get('anon_user_id');
            if (!$mailto && $listing->seller == $anon_user_id) {
                //Anon user, and e-mail is invalid.
                return false;
            }
        }

        //allows the admin to set the email address to receive contact seller emails instead of the seller
        $force_contact_seller_to_email = $db->get_site_setting('force_contact_seller_to_email');

        if ($listing->seller) {
            $seller = geoUser::getUser($listing->seller);

            if (!strlen($mailto) && strlen($seller->email)) {
                //email not attached to listing, but there is an e-mail for the user, so use that e-mail...
                $mailto = $seller->email;
            }
        }
        if (!strlen($mailto)) {
            //we have no one to mail to, can't proceed...
            return false;
        }
        $message["subject"] = $this->messages[727] . geoString::fromDB($listing->title);

        $tpl = new geoTemplate('system', 'emails');

        $introduction_message = $this->messages[1189];
        if (strlen(trim($force_contact_seller_to_email)) > 0) {
            //add seller email address to top of email so can be forwarded later
            $introduction_message = $this->messages[502296] . $mailto . "<br><br>" . $this->messages[502297] . $introduction_message;
        }
        $tpl->assign('introduction', $introduction_message);
        $tpl->assign('salutation', geoUser::getUser($seller->id)->getSalutation());
        $tpl->assign('listingPreface', $this->messages[1332]);

        $tpl->assign('listingURL', $listing->getFullUrl());

        $tpl->assign('listing', $listing->toArray());
        $tpl->assign('seller', $seller->toArray());

        if (strlen(trim($info["senders_name"])) > 0) {
            $tpl->assign('senderNameLabel', $this->messages[1513]);
            $tpl->assign('senderName', strip_tags(geoString::specialCharsDecode($info['senders_name'])));
        }
        if (strlen(trim($info["senders_phone"])) > 0) {
            $tpl->assign('senderPhoneLabel', $this->messages[1514]);
            $tpl->assign('senderPhone', strip_tags(geoString::specialCharsDecode($info['senders_phone'])));
        }
        if (strlen(trim($info["senders_comments"])) > 0) {
            $tpl->assign('senderCommentsLabel', $this->messages[61]);
            $tpl->assign('senderComments', nl2br(strip_tags(geoString::specialCharsDecode($info['senders_comments']))));
        }

        $ip = $_SERVER['REMOTE_ADDR'];
        $host = @gethostbyaddr($ip);
        $tpl->assign('senderIP', $ip);
        $tpl->assign('senderHost', $host);

        $from = $info["senders_email"];
        $tpl->assign('sendersEmail', $from);
        $tpl->assign('sendersEmailLabel', $this->messages[502194]);

        //added by user request (not used in default template)
        $tpl->assign('titleLabel', $this->messages[502172]);
        for ($i = 1; $i <= 20; $i++) {
            $opt = "optional_field_$i";
            $tpl->assign($opt . '_label', $this->messages[502172 + $i]);
            $tpl->assign($opt, geoString::fromDB($listing->$opt));
        }
        $tpl->assign('senderCompanyNameLabel', $this->messages[502193]);
        $tpl->assign('senderCompanyName', geoUser::getData(geoSession::getInstance()->getUserId(), 'company_name'));
        $levels = geoRegion::getLevelsForOverrides();
        if ($levels['country']) {
            $tpl->assign('senderCountryLabel', geoRegion::getLabelForLevel($levels['country']));
            $tpl->assign('senderCountry', geoRegion::getCountryNameForUser(geoSession::getInstance()->getUserId()));
        }


        //insert message into the database now, to create the autoincrement id for the reply link in the email
        $message['message'] = $tpl->fetch('communication/notify_seller.tpl');

        $is_public = ($info['public_question'] == 1) ? 1 : 0;

        if (strlen(trim($force_contact_seller_to_email)) == 0) {
            //do not insert communication into seller communications queue if being sent only to admin contact seller override email
            $sql = "INSERT INTO " . geoTables::user_communications_table . "
			(message_to, $fromField, regarding_ad, date_sent, message, public_question, body_text)
			VALUES
			(?, ?, ?, ?, ?, ?, ?)";
            $result = $db->Execute($sql, array($listing->seller, $fromData, $classified_id, geoUtil::time(),
             geoString::toDB($message['message']), $is_public, geoString::toDB($info['senders_comments'])));
            if (!$result) {
                return false;
            }

            $message_id = $db->Insert_Id();

            if ($seller->communication_type == 3 && $this->classified_user_id) {
                //seller has private communication turned on. add a link to the reply form
                //unless sender is not logged in, in which case a site-reply doesn't make sense
                $tpl->assign('replyLinkInstructions', $this->messages[1188]);
                $tpl->assign('replyLink', $db->get_site_setting('classifieds_url') . '?a=4&&amp;b=8&amp;c=1&amp;d=' . $message_id);

                //also override the "from" address with the generic site email
                $from = $db->get_site_setting('site_email');

                //added the reply link, so re-generate message body from tpl
                $message['message'] = $tpl->fetch('communication/notify_seller.tpl');
            }
        }
        $addon_info = array (
            'classified_id' => $classified_id,
            'info' => $info,
            'this' => $this,
            'message' => $message,
            'mailto' => $mailto,
            'from' => $from,
            'seller' => $seller,
            'listing' => $listing
        );
        $overload = geoAddon::triggerDisplay('overload_Notify_seller_notify_seller_sendMail', $addon_info, geoAddon::OVERLOAD);
        if ($overload !== geoAddon::NO_OVERLOAD) {
            return $overload;
        }

        if (strlen(trim($force_contact_seller_to_email)) > 0) {
            geoEmail::sendMail($force_contact_seller_to_email, $message["subject"], $message["message"], $from, 0, 0, 'text/html');
        } else {
            geoEmail::sendMail($mailto, $message["subject"], $message["message"], $from, 0, 0, 'text/html');
        }

        if (strlen(trim($db->get_site_setting('admin_email_bcc'))) > 0 && geoPC::is_ent()) {
            geoEmail::sendMail($db->get_site_setting('admin_email_bcc'), $message["subject"], $message["message"], $from, 0, 0, 'text/html');
        }

        $this->insert_favorite($db, $classified_id);

        return true;
    } //end of function notify_friend

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    function notify_seller_success($classified_id)
    {
        $db = DataAccess::getInstance();
        $this->page_id = 6;
        $this->get_text();
        $tpl_vars = array();

        if ($this->json) {
            $return = array();
            $return['success'] = 1;
            $tpl = new geoTemplate(geoTemplate::SYSTEM, 'browsing');
            $tpl_vars['json'] = 1;
            $tpl_vars['success'] = $this->messages[59];
            $tpl->assign($tpl_vars);
            $return['message'] = $tpl->fetch('contact_forms/common/json.tpl');
            return $this->_jsonResponse($return);
        }

        $tpl_vars['css_prefix'] = 'send_seller_message_';
        $tpl_vars['section_title'] = $this->messages[605];
        $tpl_vars['page_title'] = $this->messages[53];
        $tpl_vars['instructions'] = $this->messages[59];

        $tpl_vars['link'] = ($this->affiliate_id) ? ($db->get_site_setting('affiliate_url') . '?aff=' . $this->affiliate_id . '&amp;') : ($db->get_site_setting('classifieds_file_name') . '?');
        $tpl_vars['link'] .= 'a=2&amp;b=' . $classified_id;

        $tpl_vars['link_text'] = $this->messages[1187];

        $tpl_vars['css'] = array(
            'page_title' => 'send_seller_message_page_title',
            'instructions' => 'send_seller_message_instructions',
            'link_text' => 'send_seller_message_link_text'
        );

        geoView::getInstance()->setBodyTpl('contact_forms/seller_success.tpl', '', 'browsing')->setBodyVar($tpl_vars);
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

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
}
