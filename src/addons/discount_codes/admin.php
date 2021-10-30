<?php

//addons/discount_codes/admin.php

# Discount Codes Addon

class addon_discount_codes_admin extends addon_discount_codes_info
{
    var $admin_site;
    var $db;
    var $discount_form_input;
    public function __construct()
    {
        $this->admin_site = Singleton::getInstance('Admin_site');
        $this->db = DataAccess::getInstance();
    }

    public function init_pages()
    {
        //menu_page::addonAddPage($index, $parent, $title, $addon_name, $image, $type);
        menu_page::addonAddPage('discounts_view', '', 'View Discount Codes', 'discount_codes', 'fa-ticket');
        menu_page::addonAddPage('discounts_new', '', 'New Discount Code', 'discount_codes', 'fa-ticket');
        menu_page::addonAddPage('discounts_edit', 'discounts_view', 'Edit Discount Code', 'discount_codes', 'fa-ticket', 'sub_page');
        menu_page::addonAddPage('discounts_delete', 'discounts_view', 'Delete Discount Code', 'discount_codes', 'fa-ticket', 'sub_page');
        menu_page::addonAddPage('discounts_stats', 'discounts_view', 'Discount Code Stats', 'discount_codes', 'fa-ticket', 'sub_page');
    }

    public function init_text($language_id)
    {
        $return_var = array();
        $return_var['inputLabel'] = array (
            'name' => 'Discount Code Label in Cart',
            'desc' => 'Labels the text entry field that allows a user to input a discount code',
            'type' => 'textarea',
            'default' => 'Enter Your Discount Code:'
        );
        $return_var['inputLabelAlt'] = array (
            'name' => 'Discount Code Label everywhere else',
            'desc' => 'Labels the discount code ouside the main cart view',
            'type' => 'textarea',
            'default' => 'Discount Applied'
        );
        $return_var['updateLabel'] = array (
            'name' => 'Update Button Text',
            'desc' => 'Text used for update button.',
            'type' => 'textarea',
            'default' => 'update'
        );
        $return_var['errorMsg'] = array (
            'name' => 'Discount Code Error Message',
            'desc' => 'Error message shown when code entered is not valid.',
            'type' => 'textarea',
            'default' => 'The discount code entered is not valid, please check the code and try again.'
        );

        return $return_var;
    }

    public function display_discounts_view()
    {
        //view list of all discount codes
        $db = DataAccess::getInstance();

        $tpl_vars = array();

        $sql = "SELECT * FROM " . self::DISCOUNT_TABLE . " ORDER BY `starts`, `name`";
        $codes = $db->GetAll($sql);

        foreach ($codes as $code) {
            $code['normal_count'] = $code['recurring_count'] = '-';
            if ($code['apply_normal']) {
                $sql = "SELECT COUNT(*) FROM " . geoTables::order_item . " i, " . geoTables::order_item_registry . " r WHERE r.order_item=i.id AND i.`type`='addon_discount_codes' AND i.`status`='active'
					AND r.index_key='discount_id' AND val_string=?";

                $code['normal_count'] = (int)$db->GetOne($sql, array($code['discount_id']));
            }
            if ($code['apply_recurring']) {
                $sql = "SELECT COUNT(*) FROM " . geoTables::order_item . " i, " . geoTables::order_item_registry . " r WHERE r.order_item=i.id AND i.`type`='addon_discount_codes_recurring' AND i.`status`='active'
					AND r.index_key='discount_id' AND val_string=?";

                $code['recurring_count'] = (int)$db->GetOne($sql, array($code['discount_id']));
            }
            $tpl_vars['codes'][] = $code;
        }
        $tpl_vars['admin_msgs'] = geoAdmin::m();
        $tpl_vars['isEnt'] = geoPC::is_ent();

        geoView::getInstance()->setBodyTpl('admin/list.tpl', $this->name)
            ->setBodyVar($tpl_vars);
    }
    public function display_discounts_new()
    {
        //add a new discount code, uses exactly same code as edit
        return $this->display_discounts_edit();
    }

    public function update_discounts_new()
    {
        return $this->update_discounts_edit();
    }

    public function display_discounts_edit()
    {
        //edit a discount code
        $admin = geoAdmin::getInstance();
        $db = DataAccess::getInstance();

        $tpl_vars = array();

        $tpl_vars['admin_msgs'] = geoAdmin::m();
        $tpl_vars['is_ent'] = geoPC::is_ent();
        $discount_id = $tpl_vars['discount_id'] = ((isset($_GET['discount_id'])) ? (int)$_GET['discount_id'] : 0);

        if (isset($_POST['edit'])) {
            $tpl_vars['data'] = $_POST['edit'];

            //fix dates to be what it is expecting
            if (strpos($tpl_vars['data']['starts'], '-') !== false) {
                $parts = explode('-', $tpl_vars['data']['starts']);
                $tpl_vars['data']['starts'] = mktime(0, 0, 0, $parts[1], $parts[2], $parts[0]);
            }
            if (strpos($tpl_vars['data']['ends'], '-') !== false) {
                $parts = explode('-', $tpl_vars['data']['ends']);
                $tpl_vars['data']['ends'] = mktime(23, 59, 59, $parts[1], $parts[2], $parts[0]);
            }
        } elseif ($discount_id) {
            //populate data
            $tpl_vars['data'] = $db->GetRow("SELECT * FROM " . self::DISCOUNT_TABLE . " WHERE `discount_id`=?", array($discount_id));

            if ($tpl_vars['data']['is_group_specific'] == 1) {
                $groups = $db->GetAll("SELECT `group_id` FROM " . self::DISCOUNT_GROUPS_TABLE . " WHERE `discount_id`=$discount_id");
                foreach ($groups as $row) {
                    $tpl_vars['data']['groups'][$row['group_id']] = 1;
                }
            }
        } else {
            //some defaults for new
            $tpl_vars['data'] = array(
                'starts' => geoUtil::time(),
                'apply_normal' => 1,
                'discount_percentage' => '10'
            );
        }
        $tpl_vars['groups'] = $db->GetAll("SELECT `group_id`, `name` FROM " . geoTables::groups_table);

        $tpl_vars['joe_edwards_discountLink'] = $db->get_site_setting('joe_edwards_discountLink');

        $admin->v()->setBodyTpl('admin/edit_code.tpl', $this->name)
            ->setBodyVar($tpl_vars)
            ->addJScript(array('../js/calendarview.js','../addons/discount_codes/admin.js'))
            ->addCssFile('css/calendarview.css');
    }

    public function update_discounts_edit()
    {
        $db = DataAccess::getInstance();
        $admin = geoAdmin::getInstance();

        $discount_id = (int)$_GET['discount_id'];

        $settings = $_POST['edit'];

        //input checking
        $settings['name'] = trim($settings['name']);
        if (!strlen($settings['name'])) {
            $admin->userError('Name required.');
            return false;
        }

        $settings['discount_code'] = trim($settings['discount_code']);
        if (!strlen($settings['discount_code'])) {
            $admin->userError('Code required.');
            return false;
        }

        //check discount code make sure it is not duplicate
        $duplicates = $db->GetOne(
            "SELECT COUNT(*) FROM " . self::DISCOUNT_TABLE . " WHERE `discount_id`!=$discount_id AND `discount_code`=?",
            array (geoString::toDB($settings['discount_code']))
        );

        if ($duplicates > 0) {
            $admin->userError('Code used already in use by another discount code, code must be unique.');
            return false;
        }

        $settings['discount_percentage'] = round(floatval($settings['discount_percentage']), 4);

        if ($settings['discount_percentage'] > 100 || $settings['discount_percentage'] < 0) {
            $admin->userError('Invalid amount for discount percentage.  Must be between 0 and 100%.');
            return false;
        }

        $settings['apply_normal'] = ((isset($settings['apply_normal']) && $settings['apply_normal']) || !geoPC::is_ent()) ? 1 : 0;
        $settings['apply_recurring'] = (geoPC::is_ent() && isset($settings['apply_recurring']) && $settings['apply_recurring']) ? 1 : 0;

        if (!$settings['apply_normal'] && !$settings['apply_recurring']) {
            $admin->userError('Must check at least one value for "Used For".');
            return false;
        }
        $parts = explode('-', $settings['starts']);
        $settings['starts'] = mktime(0, 0, 0, $parts[1], $parts[2], $parts[0]);

        if ($settings['ends'] && $settings['ends'] > 0) {
            $parts = explode('-', $settings['ends']);
            $settings['ends'] = mktime(23, 59, 59, $parts[1], $parts[2], $parts[0]);
        } else {
            $settings['ends'] = 0;
        }
        if ($settings['ends'] && ($settings['ends'] < $settings['starts'])) {
            $admin->userError('Invalid start/end dates, end date must occur after start date!');
            return false;
        }

        $settings['active'] = (isset($settings['active']) && $settings['active']) ? 1 : 0;

        $settings['is_group_specific'] = (isset($settings['is_group_specific']) && $settings['is_group_specific']) ? 1 : 0;

        if ($db->get_site_setting('joe_edwards_discountLink')) {
            $settings['user_id'] = (int)$settings['user_id'];
            if ($settings['user_id']) {
                $user = geoUser::getUser($settings['user_id']);
                if (!$user) {
                    $admin->userError('Invalid Cross-Debit User ID specified, no user found with ID (' . $settings['user_id'] . ')!');
                    return false;
                }
            }

            if ($settings['discount_email'] && !geoString::isEmail($settings['discount_email'])) {
                $admin->userError('Invalid e-mail specified!');
                return false;
            }
        } else {
            $settings['user_id'] = 0;
            $settings['discount_email'] = '';
        }

        $query_data = array(
            geoString::toDB($settings['name']), geoString::toDB($settings['description']),
            geoString::toDB($settings['discount_code']), $settings['discount_percentage'],
            $settings['starts'], $settings['ends'], $settings['apply_normal'],
            $settings['apply_recurring'], $settings['is_group_specific'], $settings['active'],
            $settings['user_id'], geoString::toDB($settings['discount_email']),
        );
        if ($discount_id) {
            $sql = "UPDATE " . self::DISCOUNT_TABLE . " SET
				`name`=?,
				`description`=?,
				`discount_code`=?,
				`discount_percentage`=?,
				`starts`=?,
				`ends`=?,
				`apply_normal`=?,
				`apply_recurring`=?,
				`is_group_specific`=?,
				`active`=?,
				`user_id`=?,
				`discount_email`=?
				WHERE `discount_id`=?";
            $query_data[] = $discount_id;
        } else {
            $sql = "INSERT INTO " . self::DISCOUNT_TABLE . " SET
				`name`=?,
				`description`=?,
				`discount_code`=?,
				`discount_percentage`=?,
				`starts`=?,
				`ends`=?,
				`apply_normal`=?,
				`apply_recurring`=?,
				`is_group_specific`=?,
				`active`=?,
				`user_id`=?,
				`discount_email`=?";
        }

        $db->Execute($sql, $query_data);

        if ($discount_id) {
            //delete all the existing group attachments as we'll re-add them
            $this->deleteGroups($discount_id);
        } elseif ($settings['is_group_specific']) {
            //Brand spankin new discount code, get discount ID in case is group specific
            $discount_id = (int)$db->Insert_Id();
        }
        if ($settings['is_group_specific'] && $discount_id) {
            //update groups
            $groups = array_keys((array)$settings['groups']);
            foreach ($groups as $groupId) {
                $db->Execute(
                    "INSERT INTO " . self::DISCOUNT_GROUPS_TABLE . " SET `discount_id`=?, `group_id`=?",
                    array($discount_id, (int)$groupId)
                );
            }
        }

        //unset post data so it pulls from db instead of submitted data, since values
        //were applied
        unset($_POST['edit']);



        return true;
    }


    public function display_discounts_delete()
    {
        //delete a discount code

        $this->display_discounts_view();
    }

    public function update_discounts_delete()
    {
        $discount_id = (int)$_REQUEST['c'];

        $sql = "DELETE FROM " . self::DISCOUNT_TABLE . " WHERE `discount_id` = ?";
        //echo $sql."<br />\n";
        $result = $this->db->Execute($sql, array(intval($discount_id)));
        if (!$result) {
            //db error
            return false;
        }
        $this->deleteGroups($discount_id);
        return true;
    }

    public function display_discounts_stats()
    {
        $db = DataAccess::getInstance();
        $tpl_vars = array();

        $discount_id = (int)$_GET['discount_id'];
        if (!$discount_id) {
            geoAdmin::m('Invalid discount ID.');
        }

        $tpl_vars['data'] = $data = $db->GetRow(
            "SELECT * FROM " . self::DISCOUNT_TABLE . " WHERE `discount_id`=?",
            array($discount_id)
        );

        if ($data['discount_id']) {
            //get normal orders
            $tpl_vars['normal_count'] = $tpl_vars['recurring_count'] = '-';
            if ($data['apply_normal']) {
                $sql = "SELECT `i`.`order` FROM " . geoTables::order_item . " i, " . geoTables::order_item_registry . " r WHERE r.order_item=i.id AND i.`type`='addon_discount_codes' AND i.`status`='active'
					AND r.index_key='discount_id' AND val_string=?";

                $normalOrders = $db->GetAll($sql, array($data['discount_id']));

                //Perhaps show list of orders at some point...  for now though just show count
                $tpl_vars['normal_count'] = count($normalOrders);
            }
            if ($data['apply_recurring']) {
                $sql = "SELECT `i`.`order` FROM " . geoTables::order_item . " i, " . geoTables::order_item_registry . " r WHERE r.order_item=i.id AND i.`type`='addon_discount_codes_recurring' AND i.`status`='active'
					AND r.index_key='discount_id' AND val_string=?";

                $recurringOrders = $db->GetAll($sql, array($data['discount_id']));

                //Perhaps show list of orders at some point...  for now though just show count
                $tpl_vars['recurring_count'] = count($recurringOrders);
            }

            //get all list of user ID's that have used it
            $sql = "SELECT o.buyer FROM " . geoTables::order_item . " i, " . geoTables::order_item_registry . " r, " . geoTables::order . " o
				WHERE r.order_item=i.id AND i.order=o.id AND (i.`type`='addon_discount_codes' OR i.`type`='addon_discount_codes_recurring') AND i.`status`='active'
				AND r.index_key='discount_id' AND val_string=?
				GROUP BY o.buyer";

            $all = $db->GetAll($sql, array($data['discount_id']));
            $users = array();
            foreach ($all as $row) {
                $users[] = $row['buyer'];
            }
            $tpl_vars['userCount'] = count($users);
            $tpl_vars['usersList'] = implode(',', $users);

            $sql = "SELECT COUNT(*) FROM " . geoTables::userdata_table . " u, " . geoTables::logins_table . " l WHERE u.id=l.id AND u.id!=1";

            if ($data['is_group_specific']) {
                //group specific
                $groups_all = $db->GetAll(
                    "SELECT `group_id` FROM " . self::DISCOUNT_GROUPS_TABLE . " WHERE `discount_id`=?",
                    array($data['discount_id'])
                );

                $groups = array();
                foreach ($groups_all as $row) {
                    $groups[$row['group_id']] = $row['group_id'];
                }
                $tpl_vars['groupList'] = implode(',', $groups);
                $sql = "SELECT COUNT(*) FROM " . geoTables::userdata_table . " u, " . geoTables::logins_table . " l, " . geoTables::user_groups_price_plans_table . " as pp
					WHERE u.id=l.id AND pp.id=l.id AND u.id!=1 AND pp.group_id IN (" . implode(',', $groups) . ")";
            }

            $totalCount = (int)$db->GetOne($sql);

            $tpl_vars['userNegativeCount'] = $totalCount - $tpl_vars['userCount'];
        }

        if (geoAjax::isAjax()) {
            $tpl_vars['isAjax'] = true;

            $tpl = new geoTemplate(geoTemplate::ADDON, $this->name);
            $tpl->assign($tpl_vars);

            echo $tpl->fetch('admin/stats.tpl');
            geoView::getInstance()->setRendered(true);
        } else {
            geoView::getInstance()->setBodyTpl('admin/stats.tpl', $this->name)
                ->setBodyVar($tpl_vars);
        }
    }

    public function deleteGroups($discount_id)
    {
        $db = DataAccess::getInstance();
        $discount_id = (int)$discount_id;
        if (!$discount_id) {
            //nothing to delete!
            return;
        }
        $db->Execute("DELETE FROM " . self::DISCOUNT_GROUPS_TABLE . " WHERE `discount_id`=?", array($discount_id));
    }
}
