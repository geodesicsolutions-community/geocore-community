<?php

//admin/leveled_fields.php

class CategoriesManage
{
    public function display_category_config()
    {
        $view = geoView::getInstance();
        $db = DataAccess::getInstance();

        $catClass = geoCategory::getInstance();

        $tpl_vars = array();

        //the parent leveled field to show..
        $parent = (int)$_GET['parent'];

        //verify it is valid...
        $parent = $this->validCatId($parent);

        if (isset($_GET['parent']) && $_GET['parent'] != 0 && !$parent) {
            //invalid parent ID
            geoAdmin::m('The category ID could not be found, showing top level categories.', geoAdmin::NOTICE);
        }

        $tpl_vars = array();

        $perPage = $db->get_site_setting('leveled_max_vals_per_page');
        if (!$perPage) {
            //just a failsafe... default to 100
            $perPage = 100;
        }

        $allPages = (isset($_GET['p']) && $_GET['p'] === 'all');
        $page = (isset($_GET['p'])) ? $_GET['p'] : 1;

        $total_count = $db->GetOne("SELECT COUNT(*) FROM " . geoTables::categories_table . " WHERE `parent_id`=?", array($parent));

        $maxPages = ceil($total_count / $perPage);

        if ($page > $maxPages || $page < 1) {
            $page = 1;
        }
        if ($maxPages > 1 && $allPages) {
            //use all for current page
            $page = 'all';
        }
        $tpl_vars['page'] = $page;

        if ($maxPages > 1) {
            $tpl_vars['pagination'] = geoPagination::getHTML($maxPages, $page, 'index.php?page=category_config&amp;parent=' . $parent . '&amp;p=', '', '', true, false);
        }

        $limit = ($page === 'all') ? '' : 'LIMIT ' . (($page - 1) * $perPage) . ', ' . $perPage;

        $values = $db->Execute("SELECT * FROM " . geoTables::categories_table . " v, " . geoTables::categories_languages_table . " l WHERE l.category_id=v.category_id AND l.language_id=1 AND v.parent_id=? ORDER BY v.display_order, l.category_name $limit", array($parent));
        $tpl_vars['listing_types'] = $this->getListingTypes();
        foreach ($values as $row) {
            //get listing count
            $row['listing_count'] = $db->GetOne("SELECT COUNT(*) FROM " . geoTables::listing_categories . " as cat, " . geoTables::classifieds_table . " as class WHERE cat.`listing`=class.`id` AND class.`live` = 1 AND cat.`category`=?", array($row['category_id']));

            //normalize
            $row = $catClass->normInfo($row);

            //figure out what "extras" there are for this category...
            //cat specific pricing
            $row['price_plans'] = (int)$db->GetOne("SELECT COUNT(*) FROM " . geoTables::price_plans_categories_table . " WHERE `category_id`=?", array($row['category_id']));
            $row['questions'] = (int)$db->GetOne("SELECT COUNT(*) FROM " . geoTables::questions_table . " WHERE `category_id`=?", array($row['category_id']));
            $row['addon_extras'] = geoAddon::triggerDisplay('admin_category_list_specific_icons', $row, geoAddon::ARRAY_ARRAY);

            $all = $db->Execute("SELECT * FROM " . geoTables::category_exclusion_list . " WHERE `category_id`=?", array($row['category_id']));
            if ($all) {
                foreach ($all as $exclude_row) {
                    $row['excluded_list_types'][$exclude_row['listing_type']] = 1;
                }
            }

            $tpl_vars['categories'][] = $row;
        }

        $tpl_vars['parent'] = $parent;
        if ($parent) {
            $tpl_vars['parents'] = $catClass->getParents($parent, true);
        }
        if ($parent) {
            $info = $catClass->getInfo($parent);
            $tpl_vars['level'] = $info['level'] + 1;
            $tpl_vars['parent_name'] = $info['name'];
        } else {
            $tpl_vars['level'] = 1;
        }

        $tpl_vars['adminMsgs'] = geoAdmin::m();
        $this->_addCssJs();
        $view->setBodyTpl('categories/list_cats.tpl')
            ->setBodyVar($tpl_vars)
            ->addCssFile('css/categories.css');
    }

    public function display_category_manage()
    {
        if (!geoAjax::isAjax()) {
            return $this->display_category_config();
        }
        $catClass = geoCategory::getInstance();

        $tpl_vars = array();

        $tpl_vars['category_id'] = (int)$_GET['category'];
        $tpl_vars['category'] = $catClass->getInfo($tpl_vars['category_id']);


        //Expects format of array('href'=>'URL to link to','label'=>'Label for button')
        $tpl_vars['addon_links'] = geoAddon::triggerDisplay('admin_category_manage_add_links', $tpl_vars['category_id'], geoAddon::ARRAY_ARRAY);

        $tpl = new geoTemplate(geoTemplate::ADMIN);

        $tpl->assign($tpl_vars);

        $tpl->display('categories/manage.tpl');

        geoView::getInstance()->setRendered(true);
    }

    public function display_category_create()
    {
        if (!geoAjax::isAjax()) {
            return $this->display_category_config();
        }

        $admin = geoAdmin::getInstance();
        $db = DataAccess::getInstance();
        $catClass = geoCategory::getInstance();

        //the parent value to show..
        $parent = (int)$_GET['parent'];

        //verify it is valid...
        $parent = $this->validCatId($parent);
        if ($parent) {
            $parent_info = $catClass->getInfo($parent);
            $level = (int)$parent_info['level'] + 1;
        } else {
            $level = 1;
        }
        $tpl_vars = array();

        //get all the languages:
        $tpl_vars['languages'] = $this->getLanguages();
        $tpl_vars['listing_types'] = $this->getListingTypes();
        $tpl_vars['tpl_folder'] = $admin->geo_templatesDir();

        //ajax call, just display template
        $tpl_vars['is_ajax'] = true;
        $tpl_vars['parent'] = $parent;
        if ($parent) {
            $tpl_vars['parents'] = $catClass->getParents($parent, true);
        }
        $tpl_vars['category'] = array(
            'display_order' => '1',
            'level' => $level, 'parent' => $parent,
            'excluded_list_types' => array(),
            );
        $tpl_vars['new'] = true;

        //is SEO on?
        $tpl_vars['seo_enabled'] = geoAddon::getInstance()->isEnabled('SEO');

        $tpl = new geoTemplate(geoTemplate::ADMIN);

        $tpl->assign($tpl_vars);

        $tpl->display('categories/edit.tpl');

        $admin->v()->setRendered(true);
    }

    public function update_category_create()
    {
        $parent = (int)$_GET['parent'];
        $parent = $this->validCatId($parent);

        $catClass = geoCategory::getInstance();

        if ($parent) {
            $parent_info = $catClass->getInfo($parent);
            $level = (int)$parent_info['level'] + 1;
        } else {
            $level = 1;
        }

        $db = DataAccess::getInstance();
        $langs = $this->getLanguages();
        $lTypes = $this->getListingTypes();

        $category_images = $_POST['category_image'];
        $category_images_alt = $_POST['category_image_alt'];
        $title_modules = $_POST['title_module'];
        $seo_url_contents = $_POST['seo_url_contents'];

        $display_order = (int)$_POST['display_order'];

        $enabled = (isset($_POST['enabled']) && $_POST['enabled']) ? 'yes' : 'no';

        $which_head = $_POST['which_head_html'];
        $which_head = (in_array($which_head, array('parent','default','cat','cat+default'))) ? $which_head : 'parent';

        //insert it in there
        $result = $db->Execute(
            "INSERT INTO " . geoTables::categories_table . " SET `parent_id`=?, `level`=?, `enabled`=?, `which_head_html`=?, `display_order`=?",
            array($parent, $level, $enabled, $which_head, $display_order)
        );

        if (!$result) {
            geoAdmin::m("DB error when attempting to add new value: " . $db->ErrorMsg(), geoAdmin::ERROR);
            return false;
        }

        $cat_id = $db->Insert_Id();

        foreach ($langs as $lang) {
            $name = trim($_POST['name'][$lang['language_id']]);
            $name = geoString::toDB($name);
            $description = trim($_POST['description'][$lang['language_id']]);
            $description = geoString::toDB($description);
            $image = trim($_POST['category_image'][$lang['language_id']]);
            $image = geoString::toDB($image);

            $category_image_alt = trim($category_images_alt[$lang['language_id']]);
            $category_image_alt = geoString::toDB($category_image_alt);
            $title_module = trim($title_modules[$lang['language_id']]);
            $title_module = geoString::toDB($title_module);

            $seo_url_content = trim($seo_url_contents[$lang['language_id']]);
            $seo = geoAddon::getUtil('seo');

            if ($seo) {
                //clean SEO string according to SEO addon's settings
                $seo_url_content = $seo->revise($seo_url_content, array(), true, true);
            } else {
                //seo addon not enabled right now, so just save as urlencoded
                $seo_url_content = geoString::toDB($seo_url_content);
            }

            $head_html = '';
            if (strpos($which_head, 'cat') !== false) {
                $head_html = trim($_POST['which_html'][$lang['language_id']]);
                $head_html = geoString::toDB($head_html);
            }

            $result = $db->Execute(
                "INSERT INTO " . geoTables::categories_languages_table . " SET
					`category_id`=?,
					`language_id`=?,
					`category_name`=?,
					`category_image`=?,
					`description`=?,
					`title_module`=?,
					`seo_url_contents`=?,
					`category_image_alt`=?,
					`head_html`=?",
                array($cat_id, $lang['language_id'], $name, $image, $description, $title_module, $seo_url_content, $category_image_alt, $head_html)
            );

            if (!$result) {
                geoAdmin::m("DB error (2) when attempting to add new value: " . $db->ErrorMsg(), geoAdmin::ERROR);
                return false;
            }
        }

        //listing types allowed...  convert to exclude list.  Since this is new
        //category, don't need to worry about removing excludes already in system
        foreach ($lTypes as $type => $info) {
            if (!isset($_POST['listing_types_allowed'][$type])) {
                //this one not allowed.. insert it in exclude list
                $db->Execute("INSERT INTO " . geoTables::category_exclusion_list . " SET `category_id`=?, `listing_type`=?", array($cat_id, '' . $type));
            }
        }

        return true;
    }

    public function display_category_rescan_listings()
    {
        if (!geoAjax::isAjax()) {
            return $this->display_category_config();
        }
        $admin = geoAdmin::getInstance();
        if (isset($_GET['runBatch']) && $_GET['runBatch']) {
            //running a batch with ajax, nothing to do in display
            $admin->v()->setRendered(true);
            return;
        }
        $db = DataAccess::getInstance();
        $catClass = geoCategory::getInstance();

        //the parent value to show..
        $parent = (int)$_GET['parent'];

        //verify it is valid...
        $parent = $this->validCatId($parent);
        if ($parent) {
            $parent_info = $catClass->getInfo($parent);
            $level = (int)$parent_info['level'] + 1;
        } else {
            $level = 1;
        }
        $tpl_vars = array();

        //ajax call, just display template
        $tpl_vars['is_ajax'] = true;
        $tpl_vars['parent'] = $parent;
        if ($parent) {
            $tpl_vars['parents'] = $catClass->getParents($parent, true);
        }
        $tpl_vars['category'] = array(
            'display_order' => '1',
            'level' => $level, 'parent' => $parent,
            'excluded_list_types' => array(),
        );
        $tpl = new geoTemplate(geoTemplate::ADMIN);

        $tpl->assign($tpl_vars);

        $tpl->display('categories/reset_breadcrumbs.tpl');

        $admin->v()->setRendered(true);
        return true;
    }

    public function update_category_rescan_listings()
    {
        if (!isset($_GET['runBatch'])) {
            //not running a batch?
            return true;
        }
        geoView::getInstance()->setRendered(true);
        $data = array();
        $ajax = geoAjax::getInstance();

        $start = max(1, (int)$_POST['batch_run']);
        //we actually need start to start at 0 not 1, but make it display 1 to
        //user...
        $start_range = $start - 1;
        $at_once = (int)$_POST['batch_size'];

        if ($at_once < 1) {
            $data['error'] = 'Invalid entry for batch size, expecting a number like 200.';
            echo $ajax->encodeJSON($data);
            return;
        }

        $classT = geoTables::classifieds_table;

        $query = new geoTableSelect($classT);

        //sort by listing ID
        $query->order("$classT.`id`");

        //only specific range
        $query->limit($start_range, $at_once);

        $db = DataAccess::getInstance();

        $max = $db->GetOne('' . $query->getCountQuery());
        $data['batch_run'] = min($start + $at_once, $max + 1);
        if ($max <= $start_range) {
            $data['msg'] = 'Finished!';
            $data['complete'] = true;
            echo $ajax->encodeJSON($data);
            return;
        }
        $data['complete'] = false;
        $result = $db->Execute('' . $query);

        $count = 0;
        foreach ($result as $row) {
            //update the category...
            $catId = $db->GetOne("SELECT `category` FROM " . geoTables::listing_categories . " WHERE `listing`=?
					AND `is_terminal`='yes'", array((int)$row['id']));
            if (!$catId) {
                continue;
            }
            geoCategory::setListingCategory($row['id'], $catId);
            $count++;
        }
        $data['msg'] = 'Finished batch, processed ' . $count . ' listings.';
        echo $ajax->encodeJSON($data);
        return true;
    }

    public function display_category_edit()
    {
        if (!geoAjax::isAjax()) {
            return $this->display_category_config();
        }

        $admin = geoAdmin::getInstance();
        $db = DataAccess::getInstance();
        $catClass = geoCategory::getInstance();

        //the parent value to show..
        $category_id = (int)$_GET['category'];

        if (!$category_id) {
            $admin->message("Error: Invalid value specified.", geoAdmin::ERROR);
            return $this->ajaxError();
        }

        $catInfo = $catClass->getInfo($category_id);
        if (!$catInfo) {
            $admin->message("Error: Value not found.", geoAdmin::ERROR);
            return $this->ajaxError();
        }

        $tpl_vars = array();

        //get all the languages:
        $tpl_vars['languages'] = $this->getLanguages();
        $tpl_vars['listing_types'] = $this->getListingTypes();
        $tpl_vars['tpl_folder'] = $admin->geo_templatesDir();

        $tpl_vars['category'] = $catInfo;
        $tpl_vars['parent'] = $catInfo['parent'];
        $tpl_vars['front_page_display'] = $catInfo['front_page_display'];
        $page = 1;
        if (isset($_GET['p'])) {
            $page = ($_GET['p'] === 'all') ? 'all' : (int)$_GET['p'];
        }
        $tpl_vars['page'] = $page;

        $rows = $db->Execute("SELECT * FROM " . geoTables::categories_languages_table . " WHERE `category_id`=? ORDER BY `language_id`", array($category_id));

        foreach ($rows as $row) {
            $tpl_vars['names'][$row['language_id']] = geoString::fromDB($row['category_name']);
            $tpl_vars['category_images'][$row['language_id']] = geoString::fromDB($row['category_image']);
            $tpl_vars['descriptions'][$row['language_id']] = geoString::fromDB($row['description']);
            $tpl_vars['head_html'][$row['language_id']] = geoString::fromDB($row['head_html']);
            $tpl_vars['category_image_alt'][$row['language_id']] = geoString::fromDB($row['category_image_alt']);
            $tpl_vars['title_module'][$row['language_id']] = geoString::fromDB($row['title_module']);
            $tpl_vars['seo_url_contents'][$row['language_id']] = geoString::fromDB($row['seo_url_contents']);
        }

        $tpl_vars['parents'] = $catClass->getParents($category_id);
        //Do this to know when to display the front_page_display choice.  Only top 2 category levels can be displayed on front page
        $tpl_vars['count_of_parents'] = count($tpl_vars['parents']);

        //is SEO on?
        $tpl_vars['seo_enabled'] = geoAddon::getInstance()->isEnabled('SEO');

        //ajax call, just display template
        $tpl = new geoTemplate(geoTemplate::ADMIN);

        $tpl->assign($tpl_vars);

        $tpl->display('categories/edit.tpl');

        $admin->v()->setRendered(true);
    }

    public function update_category_edit()
    {
        $category_id = (int)$_GET['category'];
        if (!$category_id) {
            geoAdmin::m('Invalid value to edit!', geoAdmin::ERROR);
            return false;
        }

        $catClass = geoCategory::getInstance();

        $catInfo = $catClass->getInfo($category_id);
        $parent = $catInfo['parent'];
        $lTypes = $this->getListingTypes();

        //always re-calculate level...  at some point we might add "skiping levels" but not yet.
        if ($parent) {
            $parent_info = $catClass->getInfo($parent);
            $level = (int)$parent_info['level'] + 1;
        } else {
            $level = 1;
        }

        $db = DataAccess::getInstance();

        $names = $_POST['name'];
        foreach ($names as $name) {
            if (!trim($name)) {
                geoAdmin::m('Error:  Value name is required for all languages.', geoAdmin::ERROR);
                return false;
            }
        }
        $category_images = $_POST['category_image'];
        $category_images_alt = $_POST['category_image_alt'];
        $title_modules = $_POST['title_module'];
        $seo_url_contents = $_POST['seo_url_contents'];
        $descriptions = $_POST['description'];
        $head_html = $_POST['head_html'];
        if ($level <= 2) {
            $front_page_display = (isset($_POST['front_page_display']) && $_POST['front_page_display']) ? 'yes' : 'no';
        } else {
            $front_page_display = 'yes';
        }

        $display_order = (int)$_POST['display_order'];
        $which_head = $_POST['which_head_html'];
        $which_head = (in_array($which_head, array('parent','default','cat','cat+default'))) ? $which_head : 'parent';

        $enabled = (isset($_POST['enabled']) && $_POST['enabled']) ? 'yes' : 'no';
        //insert it in there
        $result = $db->Execute(
            "UPDATE " . geoTables::categories_table . " SET `level`=?, `enabled`=?, `display_order`=?,
				`which_head_html`=? ,
				`front_page_display` = ?
				WHERE `category_id`=?",
            array($level, $enabled, $display_order, $which_head,$front_page_display, $category_id)
        );

        if (!$result) {
            geoAdmin::m("DB error when attempting to edit value: " . $db->ErrorMsg(), geoAdmin::ERROR);
            return false;
        }

        $langs = $this->getLanguages();
        //Clear current ones...
        $db->Execute("DELETE FROM " . geoTables::categories_languages_table . " WHERE `category_id`=?", array($category_id));
        //now re-add them all
        foreach ($langs as $lang) {
            $name = trim($names[$lang['language_id']]);
            $name = geoString::toDB($name);
            $description = trim($_POST['description'][$lang['language_id']]);
            $description = geoString::toDB($description);
            $image = trim($_POST['category_image'][$lang['language_id']]);
            $image = geoString::toDB($image);

            $category_image_alt = trim($category_images_alt[$lang['language_id']]);
            $category_image_alt = geoString::toDB($category_image_alt);
            $title_module = trim($title_modules[$lang['language_id']]);
            $title_module = geoString::toDB($title_module);

            $seo_url_content = trim($seo_url_contents[$lang['language_id']]);
            $seo = geoAddon::getUtil('seo');

            if ($seo) {
                //clean SEO string according to SEO addon's settings
                $seo_url_content = $seo->revise($seo_url_content, array(), true, true);
            } else {
                //seo addon not enabled right now, so just save as urlencoded
                $seo_url_content = geoString::toDB($seo_url_content);
            }

            $head_html = '';
            if (strpos($which_head, 'cat') !== false) {
                $head_html = trim($_POST['head_html'][$lang['language_id']]);
                $head_html = geoString::toDB($head_html);
            }

            $result = $db->Execute(
                "INSERT INTO " . geoTables::categories_languages_table . " SET 
					 `category_id`=?,
					 `language_id`=?,
					 `category_name`=?,
					 `category_image`=?,
					 `description`=?,
					 `title_module`=?,
					 `seo_url_contents`=?,
					 `category_image_alt`=?,
					 `head_html`=?",
                array($category_id, $lang['language_id'], $name, $image, $description, $title_module, $seo_url_content, $category_image_alt, $head_html)
            );
            if (!$result) {
                geoAdmin::m("DB error when attempting to add category name/desc/etc. for language {$lang['language_id']}, db error: " . $db->ErrorMsg(), geoAdmin::ERROR);
                return false;
            }
        }

        //listing types allowed...  convert to exclude list.
        //first delete any entries already in there
        $db->Execute("DELETE FROM " . geoTables::category_exclusion_list . " WHERE `category_id`=?", array($category_id));
        $atLeastOneListingType = false;
        foreach ($lTypes as $type => $info) {
            if (!isset($_POST['listing_types_allowed'][$type])) {
                //this one not allowed.. insert it in exclude list
                $db->Execute("INSERT INTO " . geoTables::category_exclusion_list . " SET `category_id`=?, `listing_type`=?", array($category_id, '' . $type));
            } else {
                $atLeastOneListingType = true;
            }
        }
        if (!$atLeastOneListingType) {
            geoAdmin::m("You must allow at least one listing type.", geoAdmin::ERROR);
            $db->Execute("DELETE FROM " . geoTables::category_exclusion_list . " WHERE `category_id`=?", array($category_id));
            return false;
        }

        return true;
    }

    public function display_category_create_bulk()
    {
        if (!geoAjax::isAjax()) {
            return $this->display_category_config();
        }

        $admin = geoAdmin::getInstance();
        $db = DataAccess::getInstance();
        $catClass = geoCategory::getInstance();

        //the parent value to show..
        $parent = (int)$_GET['parent'];

        //verify it is valid...
        $parent = $this->validCatId($parent);
        if ($parent) {
            $parent_info = $catClass->getInfo($parent);
            $level = (int)$parent_info['level'] + 1;
        } else {
            $level = 1;
        }
        $tpl_vars = array();

        //ajax call, just display template
        $tpl_vars['parent'] = $parent;
        if ($parent) {
            $tpl_vars['parents'] = $catClass->getParents($parent, true);
        }
        $tpl_vars['level'] = $level;
        $tpl_vars['parent_info'] = $parent_info;
        $tpl_vars['listing_types'] = $this->getListingTypes();

        //figure out what to use as the display order...
        $display_order = (int)$db->GetOne("SELECT MAX(`display_order`) FROM " . geoTables::categories_table . " WHERE `parent_id`=?", array($parent));
        if ($display_order > 0) {
            //add one to the order
            $display_order++;
        } else {
            //no display orders yet, or the max is 1...  in which case they may wish
            //to make all of them 1
            $display_order = 1;
        }
        $tpl_vars['display_order'] = $display_order;

        $tpl = new geoTemplate(geoTemplate::ADMIN);

        $tpl->assign($tpl_vars);

        $tpl->display('categories/bulk.tpl');

        $admin->v()->setRendered(true);
    }

    public function update_category_create_bulk()
    {
        $parent = (int)$_GET['parent'];
        $parent = $this->validCatId($parent);
        $catClass = geoCategory::getInstance();

        if ($parent) {
            $parent_info = $catClass->getInfo($parent);
            $level = (int)$parent_info['level'] + 1;
        } else {
            $level = 1;
        }

        $db = DataAccess::getInstance();
        $langs = $this->getLanguages();
        $lTypes = $this->getListingTypes();

        if (isset($_POST['undo']) && $_POST['undo']) {
            $min_value_id = (int)$_POST['min_value_id'];
            $max_value_id = (int)$_POST['max_value_id'];

            if (!$min_value_id || !$max_value_id) {
                geoAdmin::m("Error, invalid min/max value when attempting to undo bulk add.", geoAdmin::ERROR);
                return false;
            }
            //undo (remove) values in range
            $result = $db->Execute("DELETE FROM " . geoTables::categories_table . " WHERE `category_id`>=? AND `category_id`<=?", array($min_value_id, $max_value_id));
            if (!$result) {
                geoAdmin::m("Error attempting to remove values, DB error: " . $db->ErrorMsg(), geoAdmin::ERROR);
                return false;
            }
            $result = $db->Execute("DELETE FROM " . geoTables::categories_languages_table . " WHERE `category_id`>=? AND `category_id`<=?", array($min_value_id, $max_value_id));
            if (!$result) {
                geoAdmin::m("Error attempting to remove value languages, DB error: " . $db->ErrorMsg(), geoAdmin::ERROR);
                return false;
            }
            $result = $db->Execute("DELETE FROM " . geoTables::category_exclusion_list . " WHERE `category_id`>=? AND `category_id`<=?", array($min_value_id, $max_value_id));
            if (!$result) {
                geoAdmin::m("Error attempting to remove value listing type exclusions, DB error: " . $db->ErrorMsg(), geoAdmin::ERROR);
                return false;
            }
            //reset increment so that if someone adds thousands of values and un-does several times,
            //it won't end up taking all the available slots...
            $result = $db->Execute("ALTER TABLE " . geoTables::categories_table . " AUTO_INCREMENT=?", array($min_value_id - 1));
            if (!$result) {
                geoAdmin::m("DB Error attempting to reset AUTO_INCREMENT, DB error: " . $db->ErrorMsg(), geoAdmin::ERROR);
            }
            geoAdmin::m('Undo successful, removed the categories that were just added.');
            return true;
        }

        $display_order = (int)$_POST['display_order'];
        $inc_order = ($_POST['display_order_type'] === 'inc');

        $enabled = (isset($_POST['enabled']) && $_POST['enabled']) ? 'yes' : 'no';

        $names = $_POST['names'];
        //we accept either comma, newline, or tab for delimiters...  so convert all to commas
        $names = preg_replace('/[\n\r\t]+/', ', ', $names);

        //now split it up
        $names = explode(',', $names);
        if (!count($names)) {
            geoAdmin::m('No names entered to bulk-add.', geoAdmin::ERROR);
            return false;
        }
        $count = 0;

        //figure out the exclusions
        $exclusions = array();
        $atLeastOneListingType = false;
        foreach ($lTypes as $type => $info) {
            if (!isset($_POST['listing_types_allowed'][$type])) {
                //this one not allowed.. insert it in exclude list
                $exclusions[] = $type;
            } else {
                $atLeastOneListingType = true;
            }
        }
        if (!$atLeastOneListingType) {
            geoAdmin::m("You must allow at least one listing type.", geoAdmin::ERROR);
            return false;
        }


        //keep track of names added to prevent adding multiples...
        $duplicates = 0;
        $max_value_id = 0;
        //make sure it doesn't time out
        set_time_limit(0);
        foreach ($names as $name) {
            //clean up name, remove extra space, along with quotes if they surrounded each with quote...
            $name = trim($name, ' "');

            if (strlen($name) <= 1) {
                //nothing to this name...  Either blank or just a single character
                //NOTE: we skip single characters to make easier to copy/paste from
                //lists that might contain alphabetic headers to seperate by letter.
                continue;
            }
            //check for duplicates
            $dup_count = (int)$db->GetOne("SELECT COUNT(*) FROM " . geoTables::categories_languages_table . " l,
					" . geoTables::categories_table . " r
					WHERE l.category_id=r.category_id AND r.parent_id=? AND l.`category_name`=?", array($parent, urlencode($name)));
            if ($dup_count > 0) {
                $duplicates++;
                continue;
            }


            //insert it in there
            $result = $db->Execute(
                "INSERT INTO " . geoTables::categories_table . " SET `parent_id`=?, `level`=?, `enabled`=?, `display_order`=?",
                array($parent, $level, $enabled, $display_order)
            );

            if (!$result) {
                geoAdmin::m("DB error when attempting to bulk add new value: " . $db->ErrorMsg(), geoAdmin::ERROR);
                return false;
            }

            $cat_id = $db->Insert_Id();
            //clean name for DB
            $name = geoString::toDB($name);

            foreach ($langs as $lang) {
                $result = $db->Execute("INSERT INTO " . geoTables::categories_languages_table . " SET `category_id`=?, `language_id`=?, `category_name`=?", array($cat_id, $lang['language_id'], $name));
                if (!$result) {
                    geoAdmin::m("DB error (2) when attempting to add new value: " . $db->ErrorMsg(), geoAdmin::ERROR);
                    return false;
                }
            }
            foreach ($exclusions as $l_type) {
                $result = $db->Execute("INSERT INTO " . geoTables::category_exclusion_list . " SET `category_id`=?, `listing_type`=?", array($cat_id, '' . $l_type));
                if (!$result) {
                    geoAdmin::m("DB error (3) when attempting to add new category: " . $db->ErrorMsg(), geoAdmin::ERROR);
                    return false;
                }
            }
            if ($inc_order) {
                $display_order++;
            }
            $count++;
            if (!isset($min_value_id)) {
                $min_value_id = $cat_id;
            }
            $max_value_id = $cat_id;
        }
        $undo = '';
        if (($max_value_id - $min_value_id + 1) === $count) {
            //value id's are continuous, so let them undo if they want...
            $undo = '<a href="index.php?page=category_create_bulk&amp;undo=1&amp;parent=' . $parent . '&amp;min_value_id=' . $min_value_id . '&amp;max_value_id=' . $max_value_id . '&amp;auto_save=1" class="mini_cancel lightUpLink">Undo Bulk Add!</a>';
        }

        geoAdmin::m("Successfully added $count categories!  $undo");
        if ($duplicates > 0) {
            geoAdmin::m("Note: there were $duplicates duplicate entries that were skipped.");
        }

        return true;
    }

    public function display_category_delete()
    {
        if (!geoAjax::isAjax()) {
            return $this->display_category_config();
        }

        $admin = geoAdmin::getInstance();
        $db = DataAccess::getInstance();
        $catClass = geoCategory::getInstance();

        //the values...
        $values = array();
        foreach ($_POST['values'] as $value_id) {
            $value_id = (int)$value_id;
            if ($value_id && !in_array($value_id, $values)) {
                $values[] = $value_id;
            }
        }
        if (!count($values)) {
            geoAdmin::m("Error: No categories selected!", geoAdmin::ERROR);
            return $this->ajaxError();
        }

        //what we gonna do:  show all the values and sub-values that would be affected...
        $parent = (int)$_GET['parent'];
        if ($parent) {
            $parent_info = $catClass->getInfo($parent);
            $level = (int)$parent_info['level'] + 1;
        } else {
            $level = 1;
        }
        $tpl_vars = array();
        $levels_info = array();
        foreach ($values as $value_id) {
            $this->parseKids($value_id, $levels_info);
        }
        $tpl_vars['levels_removed'] = $levels_info;

        $tpl_vars['values'] = $values;
        $tpl_vars['value_count'] = count($values);
        $tpl_vars['parent'] = $parent;
        $tpl_vars['level'] = $level;

        if ($parent) {
            $tpl_vars['parents'] = $catClass->getParents($parent, true);
            $tpl_vars['parent_info'] = $catClass->getInfo($parent);
        }

        $page = 1;
        if (isset($_GET['p'])) {
            $page = ($_GET['p'] === 'all') ? 'all' : (int)$_GET['p'];
        }
        $tpl_vars['page'] = $page;

        //ajax call, just display template
        $tpl = new geoTemplate(geoTemplate::ADMIN);

        $tpl->assign($tpl_vars);

        $tpl->display('categories/delete.tpl');

        $admin->v()->setRendered(true);
    }

    public function update_category_delete()
    {
        $db = DataAccess::getInstance();

        $values = array();
        foreach ($_POST['values'] as $value_id) {
            $value_id = (int)$value_id;
            if ($value_id && !in_array($value_id, $values)) {
                $values[] = $value_id;
            }
        }

        if (!count($values)) {
            geoAdmin::m("Error: No values selected to delete!", geoAdmin::ERROR);
            return false;
        }

        if (!isset($_POST['really']) || $_POST['really'] !== 'yes') {
            geoAdmin::m("OK, nothing done since you may not be looking at what you are clicking on.  (You almost just deleted a bunch of categories without realizing it!)", geoAdmin::NOTICE);
            return false;
        }
        set_time_limit(0);
        //ok, delete them!
        foreach ($values as $cat_id) {
            //TODO: update for removing listings the new way listings are saved
            geoCategory::remove($cat_id, $_GET['parent']);
        }
        $parent = (int)$_GET['parent'];
        if ($parent) {
            //re-count the parent
            geoCategory::updateListingCount($parent);
        }
        $plural = (count($values) > 1) ? 'ies' : 'y';
        geoAdmin::m("Deleted the " . count($values) . " selected categor{$plural} and all sub-categories.");
        return true;
    }

    public function display_category_edit_bulk()
    {
        if (!geoAjax::isAjax()) {
            return $this->display_category_config();
        }

        $admin = geoAdmin::getInstance();
        $db = DataAccess::getInstance();
        $catClass = geoCategory::getInstance();

        //the values...
        $values = array();
        foreach ($_POST['values'] as $value_id) {
            $value_id = (int)$value_id;
            if ($value_id && !in_array($value_id, $values)) {
                $values[] = $value_id;
            }
        }
        if (!count($values)) {
            geoAdmin::m("Error: No categories selected!", geoAdmin::ERROR);
            return $this->ajaxError();
        }

        $parent = (int)$_GET['parent'];
        if ($parent) {
            $parent_info = $catClass->getInfo($parent);
            $level = (int)$parent_info['level'] + 1;
        } else {
            $level = 1;
        }
        $tpl_vars = array();

        //get all the languages:
        $tpl_vars['languages'] = $this->getLanguages();

        $tpl_vars['values'] = $values;
        $tpl_vars['value_count'] = count($values);
        $tpl_vars['parent'] = $parent;
        $tpl_vars['level'] = $level;
        $tpl_vars['listing_types'] = $this->getListingTypes();

        $levels_count = array();
        foreach ($values as $cat_id) {
            $this->parseKids($cat_id, $levels_count);
        }
        unset($levels_count[$level]);
        $tpl_vars['levels_count'] = $levels_count;
        $tpl_vars['has_subcategory'] = (count($levels_count) > 0);

        $page = 1;
        if (isset($_GET['p'])) {
            $page = ($_GET['p'] === 'all') ? 'all' : (int)$_GET['p'];
        }
        $tpl_vars['page'] = $page;
        if ($parent) {
            $tpl_vars['parents'] = $catClass->getParents($parent, true);
            $tpl_vars['parent_info'] = $catClass->getInfo($parent);
        }

        //ajax call, just display template
        $tpl = new geoTemplate(geoTemplate::ADMIN);

        $tpl->assign($tpl_vars);

        $tpl->display('categories/bulkEdit.tpl');

        $admin->v()->setRendered(true);
    }

    public function update_category_edit_bulk()
    {
        $parent = (int)$_GET['parent'];
        $parent = $this->validCatId($parent);
        $catClass = geoCategory::getInstance();

        if ($parent) {
            $parent_info = $catClass->getInfo($parent);
            $level = (int)$parent_info['level'] + 1;
        } else {
            $level = 1;
        }

        $db = DataAccess::getInstance();

        $values = array();
        foreach ($_POST['values'] as $value_id) {
            $value_id = (int)$value_id;
            if ($value_id && !in_array($value_id, $values)) {
                $values[] = $value_id;
            }
        }

        if (!count($values)) {
            geoAdmin::m("Error: No values selected!", geoAdmin::ERROR);
            return false;
        }

        $apply_subcategories = (isset($_POST['apply_subcategories']) && $_POST['apply_subcategories']);

        $top_values = $values;

        if ($apply_subcategories) {
            //add sub-categories to the list...
            //NOTE: looping through "top values" since that is not being added
            //to in the middle of the loop
            foreach ($top_values as $value_id) {
                $this->parseKids($value_id, $values, true);
            }
        }

        $set = $l_set = array();
        $query_vars = array();

        if (isset($_POST['enabled']) && in_array($_POST['enabled'], array('yes','no'))) {
            //turning enabled on/off
            $set[] = "`enabled`=?";
            $query_vars[] = '' . $_POST['enabled'];
        }

        $inc_display_order = false;
        if (isset($_POST['display_order_change']) && $_POST['display_order_change']) {
            if ($_POST['display_order_change'] === 'same') {
                $set[] = "`display_order`=?";
                $query_vars[] = (int)$_POST['display_order_same'];
            } elseif ($_POST['display_order_change'] === 'inc') {
                //will be looping through in the end...
                $inc_display_order = true;
                $display_order = (int)$_POST['display_order_inc_start'];
            }
        }

        $listing_types_change = (isset($_POST['listing_types_allowed_change']) && $_POST['listing_types_allowed_change']);

        if (isset($_POST['category_image_clear']) && $_POST['category_image_clear']) {
            //clear cat image
            $l_set[] = "`category_image`=''";
        }

        if (isset($_POST['category_description_clear']) && $_POST['category_description_clear']) {
            //clear cat description
            $l_set[] = "`description`=''";
        }

        if (isset($_POST['which_head_html_change']) && $_POST['which_head_html_change']) {
            //changing value for head html
            $which_head_html = $_POST['which_head_html'];
            //make sure it is "valid"...
            $which_head_html = (in_array($which_head_html, array('parent','default'))) ? $which_head_html : 'parent';
            $set[] = "`which_head_html`=?";
            $query_vars[] = $which_head_html;
            //Note: we do not actually clear the values in languages, just set them to
            //not be used.
        }

        //now do the mass update which should be easy
        if (!count($set) && !count($l_set) && !$inc_display_order && !$listing_types_change) {
            //nothing to do...
            geoAdmin::m("Error: No changes specified, nothing to bulk-change!", geoAdmin::ERROR);
            return false;
        }

        if (count($set)) {
            $sql = "UPDATE " . geoTables::categories_table . " SET " . implode(', ', $set) . " WHERE `category_id` IN (" . implode(', ', $values) . ")";
            $result = $db->Execute($sql, $query_vars);
            if (!$result) {
                geoAdmin::m("DB error attempting to apply bulk changes, error reported: " . $db->ErrorMsg(), geoAdmin::ERROR);
                return false;
            }
        }

        if (count($l_set)) {
            //note: don't specify language in update as it is clearing values for all languages
            $sql = "UPDATE " . geoTables::categories_languages_table . " SET " . implode(', ', $l_set) . " WHERE `category_id` IN (" . implode(', ', $values) . ")";
            $result = $db->Execute($sql);
            if (!$result) {
                geoAdmin::m("DB error attempting to apply bulk changes to language data, error reported: " . $db->ErrorMsg(), geoAdmin::ERROR);
                return false;
            }
        }

        if ($inc_display_order) {
            //must also go through every single one...
            //first clear time limit
            set_time_limit(0);

            $this->_bulkEditOrder($top_values, $display_order, apply_subcategories);
        }

        if ($listing_types_change) {
            $lTypes = $this->getListingTypes();
            $excludes = array();
            $atLeastOneListingType = false;
            foreach ($lTypes as $type => $info) {
                if (!isset($_POST['listing_types_allowed'][$type])) {
                    //this one not allowed.. insert it in exclude list
                    $excludes[] = $type;
                } else {
                    $atLeastOneListingType = true;
                }
            }
            if (!$atLeastOneListingType) {
                geoAdmin::m("You must allow at least one listing type.", geoAdmin::ERROR);
                return false;
            }
            $db->Execute("DELETE FROM " . geoTables::category_exclusion_list . " WHERE `category_id` IN (" . implode(', ', $values) . ")");
            if ($excludes) {
                foreach ($values as $cat_id) {
                    foreach ($excludes as $type) {
                        $db->Execute("INSERT INTO " . geoTables::category_exclusion_list . " SET `category_id`=?, `listing_type`=?", array($cat_id, '' . $type));
                    }
                }
            }
        }
        $count = count($values);
        geoAdmin::m("Successfully updated $count categories!");

        return true;
    }

    private function _bulkEditOrder($values, $starting_order, $subcategories_also)
    {
        $db = DataAccess::getInstance();
        if ($subcategories_also) {
            foreach ($values as $value_id) {
                $value_id = (int)$value_id;
                $rows = $db->Execute("SELECT `category_id` FROM " . geoTables::categories_table . " WHERE `parent_id`={$value_id}");
                $kids = array();
                foreach ($rows as $row) {
                    $kids[] = (int)$row['category_id'];
                }
                if ($kids) {
                    if (!$this->_bulkEditOrder($kids, $starting_order, true)) {
                        //ran into some trouble, return false
                        return false;
                    }
                }
            }
        }
        $display_order = (int)$starting_order;

        foreach ($values as $value_id) {
            $sql = "UPDATE " . geoTables::categories_table . " SET `display_order`=? WHERE `category_id`={$value_id}";
            $result = $db->Execute($sql, array($display_order));
            if (!$result) {
                geoAdmin::m("Error attempting to update value! DB error reported: " . $db->ErrorMsg(), geoAdmin::ERROR);
                return false;
            }
            $display_order++;
        }
        return true;
    }

    public function display_category_enabled()
    {
        if (!geoAjax::isAjax()) {
            //this shouldn't really happen but whatevs
            return $this->display_category_config();
        }
        geoAjax::getInstance()->jsonHeader();
        $catClass = geoCategory::getInstance();

        $value_id = (int)$_POST['value'];
        $catInfo = $catClass->getInfo($value_id);

        $tpl = new geoTemplate(geoTemplate::ADMIN);

        $tpl->assign('category', $catInfo)
            ->assign('is_ajax', true);

        $tpl->display('categories/enabled.tpl');

        geoView::getInstance()->setRendered(true);
    }

    public function update_category_enabled()
    {
        $db = DataAccess::getInstance();
        $catClass = geoCategory::getInstance();

        $value_id = (int)$_POST['value'];

        $valueInfo = $catClass->getInfo($value_id);

        if ($valueInfo) {
            $enabled = ($valueInfo['enabled'] === 'yes') ? 'no' : 'yes';

            $db->Execute("UPDATE " . geoTables::categories_table . " SET `enabled`=? WHERE `category_id`=?", array($enabled, $value_id));
        }
        return true;
    }

    public function display_category_move()
    {
        if (!geoAjax::isAjax()) {
            return $this->display_category_config();
        }

        $admin = geoAdmin::getInstance();
        $db = DataAccess::getInstance();
        $catClass = geoCategory::getInstance();

        //the values...
        $values = array();
        foreach ($_POST['values'] as $value_id) {
            $value_id = (int)$value_id;
            if ($value_id && !in_array($value_id, $values)) {
                $values[] = $value_id;
            }
        }
        if (!count($values)) {
            geoAdmin::m("Error: No categories selected!", geoAdmin::ERROR);
            return $this->ajaxError();
        }

        if (isset($_GET['browse']) && $_GET['browse']) {
            $tpl_vars = array();
            $new_parent = (int)$_GET['parent'];
            $new_parent = $this->validCatId($new_parent);
            $tpl_vars['new_parent'] = $new_parent;
            if ($new_parent) {
                $tpl_vars['new_parents'] = $catClass->getParents($new_parent, true);
            }
            //leave out the selected values...
            $tpl_vars['browse_values'] = $db->GetAll("SELECT * FROM " . geoTables::categories_table . " r, " . geoTables::categories_languages_table . " l WHERE l.category_id=r.category_id AND l.language_id=1 AND r.parent_id=? AND r.category_id NOT IN (" . implode(',', $values) . ") ORDER BY r.display_order, l.category_name", array($new_parent));

            $tpl_vars['browse_link'] = 'index.php?page=category_move&amp;browse=1&amp;parent=';

            $tpl = new geoTemplate(geoTemplate::ADMIN);
            $tpl->assign($tpl_vars);
            $tpl->display('categories/moveBrowse.tpl');
            $admin->v()->setRendered(true);
            return;
        }

        //what we gonna do:  show all the values and sub-values that would be affected...
        $parent = (int)$_GET['parent'];
        if ($parent) {
            $parent_info = $catClass->getInfo($parent);
            $level = (int)$parent_info['level'] + 1;
        } else {
            $level = 1;
        }
        $tpl_vars = array();

        $tpl_vars['values'] = $values;
        $tpl_vars['value_count'] = count($values);
        $tpl_vars['cat_plural'] = ($tpl_vars['value_count'] > 1) ? 'ies' : 'y';
        $tpl_vars['parent'] = $parent;
        $tpl_vars['level'] = $level;
        if ($parent) {
            $tpl_vars['parents'] = $catClass->getParents($parent, true);
            $tpl_vars['parent_info'] = $catClass->getInfo($parent);
        }
        $new_parent = (isset($_GET['new_parent'])) ? $_GET['new_parent'] : $parent;

        if ($new_parent) {
            $tpl_vars['new_parents'] = $catClass->getParents($new_parent, true);
            $tpl_vars['new_parent_info'] = $catClass->getInfo($new_parent);
        }
        //leave out the selected values...
        $tpl_vars['browse_values'] = $db->GetAll("SELECT * FROM " . geoTables::categories_table . " r, " . geoTables::categories_languages_table . " l WHERE l.category_id=r.category_id AND l.language_id=1 AND r.parent_id=? AND r.category_id NOT IN (" . implode(',', $values) . ") ORDER BY r.display_order, l.category_name", array($new_parent));

        $tpl_vars['browse_link'] = 'index.php?page=category_move&amp;browse=1&amp;parent=';

        $page = 1;
        if (isset($_GET['p'])) {
            $page = ($_GET['p'] === 'all') ? 'all' : (int)$_GET['p'];
        }
        $tpl_vars['page'] = $page;

        //ajax call, just display template
        $tpl = new geoTemplate(geoTemplate::ADMIN);

        $tpl->assign($tpl_vars);

        $tpl->display('categories/move.tpl');

        $admin->v()->setRendered(true);
    }

    public function update_category_move()
    {
        $db = DataAccess::getInstance();
        $catClass = geoCategory::getInstance();

        if (isset($_GET['browse']) && $_GET['browse']) {
            //nothing to do, just browsing...
            return true;
        }

        $values = array();
        foreach ($_POST['values'] as $value_id) {
            $value_id = (int)$value_id;
            if ($value_id && !in_array($value_id, $values)) {
                $values[] = $value_id;
            }
        }

        if (!count($values)) {
            geoAdmin::m("Error: No values selected to move!", geoAdmin::ERROR);
            return false;
        }

        $parent = (int)$_GET['parent'];
        //FAILSAFE:  Make sure the parent specified matches the selected values...
        //because if it does not that might indicate hitting refresh or something
        $wrong_parent_count = (int)$db->GetOne("SELECT COUNT(*) FROM " . geoTables::categories_table . " WHERE
				`category_id` IN (" . implode(',', $values) . ") AND `parent_id`!=?", array($parent));
        if ($wrong_parent_count > 0) {
            //oops!  Must have hit refresh or something...
            geoAdmin::m("Error: some of the selected categories are no longer in the same place,
					aborting the move proceedure.  This typically happens when you refresh the page
					directly after moving categories, or if more than one admin is editing categories at once.", geoAdmin::ERROR);
            return false;
        }

        $new_type = $_POST['to_type'];

        if ($new_type === 'top') {
            //easy, new parent is 0
            $new_parent = 0;
        } elseif ($new_type === 'id') {
            //use either ID or unique_name
            if (!strlen(trim($_POST['new_parent']))) {
                geoAdmin::m("Error: No parent category ID specified!", geoAdmin::ERROR);
                return false;
            }
            $new_parent = (int)$_POST['new_parent'];

            $new_parent = $this->validCatId($new_parent);
            if (!$new_parent) {
                geoAdmin::m("Error: Parent Value ID specified is not valid or could not be found!", geoAdmin::ERROR);
                return false;
            }

            //now figure out if we really should be moving here, make sure not
            //trying to move value into itself.

            if (in_array($new_parent, $values)) {
                geoAdmin::m("ERROR:  Can't move a category into itself!");
                return false;
            }

            //get the new parent's info
            $new_parent_info = $catClass->getInfo($new_parent);

            //now go through each level...

            $next_level = $db->Execute("SELECT `category_id` FROM " . geoTables::categories_table . " WHERE `parent_id` IN (" . implode(',', $values) . ")");
            $next_count = $next_level->RecordCount();
            while ($next_level && $next_count) {
                $this_level = array();
                foreach ($next_level as $row) {
                    if ($row['category_id'] == $new_parent) {
                        //oops found a child of the values being moved that matches the new location,
                        //so can't do this!
                        geoAdmin::m("ERROR: Cannot move into a child of a selected category!", geoAdmin::ERROR);
                        return false;
                    }
                    $this_level[] = $row['category_id'];
                }
                $next_level = $db->Execute("SELECT `category_id` FROM " . geoTables::categories_table . " WHERE `parent_id` IN (" . implode(',', $this_level) . ")");
                $next_count = $next_level->RecordCount();
            }
            unset($next_level, $next_count, $this_level);
            //gets this far, it "should" be ok to move into this value... maybe...
        } elseif ($new_type === 'browse') {
            $new_parent = (int)$_POST['browse_value'];
            $new_parent = $this->validCatId($new_parent);
            if ($new_parent !== (int)$_POST['browse_value']) {
                //while can browse to top, if the number specified was not 0 but
                //valid value ID returned 0 then it isn't the one they asked for...
                geoAdmin::m("Error: Parent Category ID specified is not valid or could not be found!", geoAdmin::ERROR);
                return false;
            }
        }


        if ($parent) {
            $parent_info = $catClass->getInfo($parent);
            $old_level = (int)$parent_info['level'] + 1;
        } else {
            $old_level = 1;
        }

        if ($new_parent) {
            $new_parent_info = $catClass->getInfo($new_parent);
            $new_level = (int)$new_parent_info['level'] + 1;
        } else {
            $new_level = 1;
        }

        if ($parent === $new_parent) {
            geoAdmin::m("That is the same place it started from!", geoAdmin::NOTICE);
            return false;
        }

        //first part is easy, just update the parents...
        $result = $db->Execute("UPDATE " . geoTables::categories_table . " SET `parent_id`=? WHERE `category_id` IN (" . implode(',', $values) . ")", array($new_parent));
        if (!$result) {
            geoAdmin::m('Error moving categories, error reported: ' . $db->ErrorMsg(), geoAdmin::ERROR);
            return false;
        }
        if ($new_level !== $level || $new_leveled_field !== $leveled_field) {
            //oops, have to fix the levels on all...
            foreach ($values as $value_id) {
                $this->fixLevel($value_id, $new_level);
            }
        }
        geoAdmin::m("Categories moved successfully!");
        geoAdmin::m("If the categories you just moved contained one or more listings, be sure to use the Refresh Listing Breadcrumbs button, below, to update listing category data", geoAdmin::NOTICE);
        return true;
    }

    public function display_category_copy()
    {
        if (!geoAjax::isAjax()) {
            return $this->display_category_config();
        }

        $admin = geoAdmin::getInstance();
        $db = DataAccess::getInstance();
        $catClass = geoCategory::getInstance();

        //the values...
        $values = array();
        foreach ($_POST['values'] as $value_id) {
            $value_id = (int)$value_id;
            if ($value_id && !in_array($value_id, $values)) {
                $values[] = $value_id;
            }
        }
        if (!count($values)) {
            geoAdmin::m("Error: No categories selected!", geoAdmin::ERROR);
            return $this->ajaxError();
        }

        if (isset($_GET['browse']) && $_GET['browse']) {
            $tpl_vars = array();
            $new_parent = (int)$_GET['parent'];
            $new_parent = $this->validCatId($new_parent);
            $tpl_vars['new_parent'] = $new_parent;
            if ($new_parent) {
                $tpl_vars['new_parents'] = $catClass->getParents($new_parent, true);
            }
            //leave out the selected values...
            $tpl_vars['browse_values'] = $db->GetAll("SELECT * FROM " . geoTables::categories_table . " r, " . geoTables::categories_languages_table . " l WHERE l.category_id=r.category_id AND l.language_id=1 AND r.parent_id=? AND r.category_id NOT IN (" . implode(',', $values) . ") ORDER BY r.display_order, l.category_name", array($new_parent));

            $tpl_vars['browse_link'] = 'index.php?page=category_copy&amp;browse=1&amp;parent=';

            $tpl = new geoTemplate(geoTemplate::ADMIN);
            $tpl->assign($tpl_vars);
            $tpl->display('categories/copyBrowse.tpl');
            $admin->v()->setRendered(true);
            return;
        }

        //what we gonna do:  show all the values and sub-values that would be affected...
        $parent = (int)$_GET['parent'];
        if ($parent) {
            $parent_info = $catClass->getInfo($parent);
            $level = (int)$parent_info['level'] + 1;
        } else {
            $level = 1;
        }
        $tpl_vars = array();

        $tpl_vars['values'] = $values;
        $tpl_vars['value_count'] = count($values);
        $tpl_vars['parent'] = $parent;
        $tpl_vars['level'] = $level;
        if ($parent) {
            $tpl_vars['parents'] = $catClass->getParents($parent, true);
            $tpl_vars['parent_info'] = $catClass->getInfo($parent);
        }
        $new_parent = (isset($_GET['new_parent'])) ? $_GET['new_parent'] : $parent;

        if ($new_parent) {
            $tpl_vars['new_parents'] = $catClass->getParents($new_parent, true);
            $tpl_vars['new_parent_info'] = $catClass->getInfo($new_parent);
        }
        //leave out the selected values...
        $tpl_vars['browse_values'] = $db->GetAll("SELECT * FROM " . geoTables::categories_table . " r, " . geoTables::categories_languages_table . " l WHERE l.category_id=r.category_id AND l.language_id=1 AND r.parent_id=? AND r.category_id NOT IN (" . implode(',', $values) . ") ORDER BY r.display_order, l.category_name", array($new_parent));

        $tpl_vars['browse_link'] = 'index.php?page=category_copy&amp;browse=1&amp;parent=';

        $page = 1;
        if (isset($_GET['p'])) {
            $page = ($_GET['p'] === 'all') ? 'all' : (int)$_GET['p'];
        }
        $tpl_vars['page'] = $page;

        //ajax call, just display template
        $tpl = new geoTemplate(geoTemplate::ADMIN);

        $tpl->assign($tpl_vars);

        $tpl->display('categories/copy.tpl');

        $admin->v()->setRendered(true);
    }

    public function update_category_copy()
    {
        $db = DataAccess::getInstance();
        $catClass = geoCategory::getInstance();

        if (isset($_GET['browse']) && $_GET['browse']) {
            //nothing to do, just browsing...
            return true;
        }

        $values = array();
        foreach ($_POST['values'] as $value_id) {
            $value_id = (int)$value_id;
            if ($value_id && !in_array($value_id, $values)) {
                $values[] = $value_id;
            }
        }

        if (!count($values)) {
            geoAdmin::m("Error: No categories selected to copy!", geoAdmin::ERROR);
            return false;
        }

        $parent = (int)$_GET['parent'];
        //FAILSAFE:  Make sure the parent specified matches the selected values...
        //because if it does not that might indicate hitting refresh or something
        $wrong_parent_count = (int)$db->GetOne("SELECT COUNT(*) FROM " . geoTables::categories_table . " WHERE
				`category_id` IN (" . implode(',', $values) . ") AND `parent_id`!=?", array($parent));
        if ($wrong_parent_count > 0) {
            //oops!  Must have hit refresh or something...
            geoAdmin::m("Error: some of the selected categories are no longer in the same place,
					aborting the copy proceedure.  This typically happens when you refresh the page
					directly after copying categories, or if more than one admin is editing categories at once.", geoAdmin::ERROR);
            return false;
        }

        $new_type = $_POST['to_type'];

        if ($new_type === 'top') {
            //easy, new parent is 0
            $new_parent = 0;
        } elseif ($new_type === 'id') {
            //use either ID or unique_name
            if (!strlen(trim($_POST['new_parent']))) {
                geoAdmin::m("Error: No parent category ID specified!", geoAdmin::ERROR);
                return false;
            }
            $new_parent = (int)$_POST['new_parent'];

            $new_parent = $this->validCatId($new_parent);
            if (!$new_parent) {
                geoAdmin::m("Error: Parent Category ID specified is not valid or could not be found!", geoAdmin::ERROR);
                return false;
            }

            //now figure out if we really should be moving here, make sure not
            //trying to move value into itself.

            if (in_array($new_parent, $values)) {
                geoAdmin::m("ERROR:  Can't move a value into itself!");
                return false;
            }

            //get the new parent's info
            $new_parent_info = $catClass->getInfo($new_parent);
            //now go through each level...

            $next_level = $db->Execute("SELECT `category_id` FROM " . geoTables::categories_table . " WHERE `parent_id` IN (" . implode(',', $values) . ")");
            $next_count = $next_level->RecordCount();
            while ($next_level && $next_count) {
                $this_level = array();
                foreach ($next_level as $row) {
                    if ($row['category_id'] == $new_parent) {
                        //oops found a child of the values being moved that matches the new location,
                        //so can't do this!
                        geoAdmin::m("ERROR: Category ID specified is a sub-category of one of the categories being copied, cannot copy a category inside a sub-category of itself. (Like trying to stick a box inside itself, it just doesn't work)", geoAdmin::ERROR);
                        return false;
                    }
                    $this_level[] = $row['category_id'];
                }
                $next_level = $db->Execute("SELECT `category_id` FROM " . geoTables::categories_table . " WHERE `parent_id` IN (" . implode(',', $this_level) . ")");
                $next_count = $next_level->RecordCount();
            }
            unset($next_level, $next_count, $this_level);
            //gets this far, it "should" be ok to move into this value... maybe...
        } elseif ($new_type === 'browse') {
            $new_parent = (int)$_POST['browse_value'];
            $new_parent = $this->validCatId($new_parent);
            if ($new_parent !== (int)$_POST['browse_value']) {
                //while can browse to top, if the number specified was not 0 but
                //valid value ID returned 0 then it isn't the one they asked for...
                geoAdmin::m("Error: Parent Category ID specified is not valid or could not be found!", geoAdmin::ERROR);
                return false;
            }

            if ($new_parent) {
                $new_parent_info = $catClass->getInfo($new_parent);
            }
        }


        if ($parent) {
            $parent_info = $catClass->getInfo($parent);
            $old_level = (int)$parent_info['level'] + 1;
        } else {
            $old_level = 1;
        }

        if ($new_parent) {
            $new_parent_info = $catClass->getInfo($new_parent);
            $new_level = (int)$new_parent_info['level'] + 1;
        } else {
            $new_level = 1;
        }

        if ($parent === $new_parent) {
            geoAdmin::m("That is the same place it started from!", geoAdmin::NOTICE);
            return false;
        }
        //make sure it does not time out
        set_time_limit(0);
        foreach ($values as $value_id) {
            if (!$this->copyCategory($value_id, $new_parent_info)) {
                //problem with copy, don't keep going
                return false;
            }
        }
        geoAdmin::m("Categories copied successfully!");
        return true;
    }

    public function display_category_copy_parts()
    {
        if (!geoAjax::isAjax()) {
            return $this->display_category_config();
        }

        $admin = geoAdmin::getInstance();
        $db = DataAccess::getInstance();
        $catClass = geoCategory::getInstance();

        //the category to copy from
        $category_id = (int)$_GET['categoryId'];
        if (!$category_id) {
            geoAdmin::m("Error: No from category specified!", geoAdmin::ERROR);
            return $this->ajaxError();
        }

        if (isset($_GET['browse']) && $_GET['browse']) {
            $tpl_vars = array();
            $new_parent = (int)$_GET['parent'];
            $new_parent = $this->validCatId($new_parent);
            $tpl_vars['new_parent'] = $new_parent;
            if ($new_parent) {
                $tpl_vars['new_parents'] = $catClass->getParents($new_parent, true);
            }
            //leave out the selected values...
            $tpl_vars['browse_values'] = $db->GetAll("SELECT * FROM " . geoTables::categories_table . " r, " . geoTables::categories_languages_table . " l WHERE l.category_id=r.category_id AND l.language_id=1 AND r.parent_id=? AND r.category_id != {$category_id} ORDER BY r.display_order, l.category_name", array($new_parent));

            $tpl_vars['browse_link'] = 'index.php?page=category_copy_parts&amp;browse=1&amp;categoryId=' . $category_id . '&amp;parent=';

            $tpl = new geoTemplate(geoTemplate::ADMIN);
            $tpl->assign($tpl_vars);
            $tpl->display('categories/copyBrowse.tpl');
            $admin->v()->setRendered(true);
            return;
        }

        //what we gonna do:  show all the values and sub-values that would be affected...
        $parent = (int)$_GET['parent'];
        if ($parent) {
            $parent_info = $catClass->getInfo($parent);
            $level = (int)$parent_info['level'] + 1;
        } else {
            $level = 1;
        }
        $tpl_vars = array();

        $tpl_vars['category_id'] = $category_id;
        $tpl_vars['parent'] = $parent;
        $tpl_vars['level'] = $level;
        $tpl_vars['parents'] = $catClass->getParents($category_id, true);
        $tpl_vars['parent_info'] = $catClass->getInfo($parent);

        $new_parent = (isset($_GET['new_parent'])) ? $_GET['new_parent'] : $parent;

        if ($new_parent) {
            $tpl_vars['new_parents'] = $catClass->getParents($new_parent, true);
            $tpl_vars['new_parent_info'] = $catClass->getInfo($new_parent);
        }
        //do NOT leave out the selected values...
        $tpl_vars['browse_values'] = $db->GetAll("SELECT * FROM " . geoTables::categories_table . " r, " . geoTables::categories_languages_table . " l WHERE l.category_id=r.category_id AND l.language_id=1 AND r.parent_id=? ORDER BY r.display_order, l.category_name", array($new_parent));

        $tpl_vars['browse_link'] = 'index.php?page=category_copy_parts&amp;browse=1&amp;categoryId=' . $category_id . '&amp;parent=';

        $page = 1;
        if (isset($_GET['p'])) {
            $page = ($_GET['p'] === 'all') ? 'all' : (int)$_GET['p'];
        }
        $tpl_vars['page'] = $page;

        //ajax call, just display template
        $tpl = new geoTemplate(geoTemplate::ADMIN);

        $tpl->assign($tpl_vars);

        $tpl->display('categories/copyParts.tpl');

        $admin->v()->setRendered(true);
    }

    public function update_category_copy_parts()
    {
        $db = DataAccess::getInstance();
        $catClass = geoCategory::getInstance();

        if (isset($_GET['browse']) && $_GET['browse']) {
            //nothing to do, just browsing...
            return true;
        }
        $from = (int)$_GET['categoryId'];

        $copy_questions = (isset($_POST['copy_questions']) && $_POST['copy_questions']);
        $copy_fields = (isset($_POST['copy_fields']) && $_POST['copy_fields']);
        $copy_price_plans = (isset($_POST['copy_price_plans']) && $_POST['copy_price_plans']);
        $copy_subcategories = (isset($_POST['copy_subcategories']) && $_POST['copy_subcategories']);

        if (!$copy_questions && !$copy_fields && !$copy_price_plans && !$copy_subcategories) {
            geoAdmin::m('Nothing selected to copy, nothing to do!', geoAdmin::ERROR);
            return false;
        }

        $parent = (int)$_GET['parent'];

        $new_type = $_POST['to_type'];

        if ($new_type === 'id') {
            //use either ID or unique_name
            if (!strlen(trim($_POST['new_parent']))) {
                geoAdmin::m("Error: No TO category ID specified!", geoAdmin::ERROR);
                return false;
            }
            $to = (int)$_POST['new_parent'];

            $to = $this->validCatId($to);
            if (!$to) {
                geoAdmin::m("Error: TO Category ID specified is not valid or could not be found!", geoAdmin::ERROR);
                return false;
            }
        } elseif ($new_type === 'browse') {
            $to = (int)$_POST['browse_value'];
            $to = $this->validCatId($to);
            if ($to !== (int)$_POST['browse_value']) {
                //while can browse to top, if the number specified was not 0 but
                //valid value ID returned 0 then it isn't the one they asked for...
                geoAdmin::m("Error: Parent Category ID specified is not valid or could not be found!", geoAdmin::ERROR);
                return false;
            }
        }

        if (!$to || !$from) {
            geoAdmin::m('From / To Category not specified!', geoAdmin::ERROR);
            return false;
        }
        if ($to === $from) {
            geoAdmin::m('Cannot copy to itself', geoAdmin::ERROR);
        }


        if ($copy_subcategories) {
            //go through all sub-categories being copied, make sure none of them
            //match the "to" category
            $vals = array();
            $this->parseKids($from, $vals, true);
            if (in_array($to, $vals)) {
                geoAdmin::m('Error: TO category is included in sub-categories being copied, you can\'t do that.', geoAdmin::ERROR);
                return false;
            }
        }
        $info = $catClass->getInfo($from);
        //make sure it does not time out
        set_time_limit(0);
        if ($info['what_fields_to_use'] == 'own' && $copy_fields) {
            geoFields::copy($from, $to);
        }
        if ($copy_price_plans) {
            $this->copyPricePlan($from, $to);
        }
        if ($copy_questions) {
            if (!$this->duplicate_category_questions($from, $to)) {
                geoAdmin::m('Error copying questions, aborting any additional copy.', geoAdmin::ERROR);
                return false;
            }
        }
        if ($copy_subcategories) {
            //now, go through any children and copy those...
            $values = $db->Execute("SELECT `category_id` FROM " . geoTables::categories_table . " WHERE `parent_id`=?", array($from));
            foreach ($values as $valueInfo) {
                if (!$this->copyCategory($valueInfo['category_id'], $info)) {
                    //something went wrong copying a child, stop here
                    return false;
                }
            }
        }

        geoAdmin::m("Category data requested has been copied to the specified category.");
        return true;
    }

    public function display_category_durations()
    {
        $category_id = (int)$_GET['c'];
        if (!$category_id) {
            return $this->display_category_config();
        }
        $catClass = geoCategory::getInstance();
        $db = DataAccess::getInstance();
        $admin = geoAdmin::getInstance();

        $tpl_vars = array();
        $tpl_vars['admin_msgs'] = geoAdmin::m();


        $info = $catClass->getInfo($category_id);
        if (!$info) {
            geoAdmin::m('Error: Could not find category!', geoAdmin::ERROR);
            return $this->display_category_config();
        }

        $category_name = $info['name'];

        $sql = "SELECT * FROM " . geoTables::price_plan_lengths_table . " WHERE `price_plan_id` = 0 AND `category_id` = $category_id ORDER BY `length_of_ad` ASC";
        $length_result = $db->Execute($sql);
        if (!$length_result) {
            geoAdmin::m('DB Error, error reported: ' . $db->ErrorMsg(), geoAdmin::ERROR);
            return $this->display_category_config;
        }
        $admin->title = " (" . $category_name . ")";
        $tpl_vars['category_id'] = $category_id;
        $tpl_vars['lengths'] = $length_result;
        $tpl_vars['info'] = $info;
        $tpl_vars['category_tree'] = geoCategory::getTree($category_id);

        geoView::getInstance()->setBodyTpl('categories/durations.tpl')
            ->setBodyVar($tpl_vars);
        return true;
    }

    public function update_category_durations()
    {
        $db = DataAccess::getInstance();
        $category_id = (int)$_GET['c'];
        $display_length = trim($_POST['display_length_of_ad']);
        $days = (int)$_POST['length_of_ad'];

        if (!$category_id || !$display_length || $days <= 0) {
            geoAdmin::m('Both display and number of days required.');
            return false;
        }
        $sql = "SELECT * FROM  " . geoTables::price_plan_lengths_table . "
			WHERE `length_of_ad` = ? AND `price_plan_id` = 0 AND `category_id` = ?";
        $result = $db->Execute($sql, array($days, $category_id));
        if (!$result) {
            //could not get length
            geoAdmin::m('DB Query Error : ' . $db->ErrorMsg(), geoAdmin::ERROR);
            return false;
        }
        if ($result->RecordCount() > 0) {
            geoAdmin::m('Already a duration with that number of days for this category.');
            return false;
        }
        $sql = "INSERT INTO " . geoTables::price_plan_lengths_table . "
			(price_plan_id,category_id,length_of_ad,display_length_of_ad,length_charge,renewal_charge)
			values
			(0,$category_id,?,?,0,0)";
        $insert_result = $db->Execute($sql, array (
            $days, $display_length
            ));

        if (!$insert_result) {
            geoAdmin::m('DB Query Error : ' . $db->ErrorMsg(), geoAdmin::ERROR);
            return false;
        }
        return true;
    }

    public function display_category_durations_delete()
    {
        return $this->display_category_durations();
    }

    public function update_category_durations_delete()
    {
        $db = DataAccess::getInstance();

        $category_id = (int)$_GET['c'];
        $length_id = (int)$_POST['length_id'];

        return $db->Execute(
            "DELETE FROM " . geoTables::price_plan_lengths_table . " WHERE `category_id`=? AND `length_id`=? AND `price_plan_id`=0",
            array($category_id,$length_id)
        );
    }

    public function display_category_templates()
    {
        if (!geoAjax::isAjax()) {
            return $this->display_category_config();
        }

        $view = geoView::getInstance();
        $db = DataAccess::getInstance();
        $catClass = geoCategory::getInstance();
        require_once ADMIN_DIR . 'design.php';
        $design = Singleton::getInstance('DesignManage');
        $tpl_vars = array();
        $tpl_vars['categoryId'] = (isset($_GET['b'])) ? (int)$_GET['b'] : 0;
        $info = $catClass->getInfo($tpl_vars['categoryId']);
        $tpl_vars['categoryName'] = $info['name'];

        $pages = $design->getPagesData('category');
        foreach ($pages as $page_id => $data) {
            $pages[$page_id]['attachments'] = $view->getTemplateAttachments($page_id, false);
        }
        $tpl_vars['pages'] = $pages;
        $sql = "SELECT `language_id`, `language` FROM " . geoTables::pages_languages_table . " ORDER BY `language_id` ASC";
        $languages = $db->GetAll($sql);
        $tpl_vars['languages'] = array();
        foreach ($languages as $row) {
            $tpl_vars['languages'][$row['language_id']] = $row['language'];
        }

        $tpl = new geoTemplate(geoTemplate::ADMIN);
        $tpl->assign($tpl_vars);

        echo $tpl->fetch('categories/attachedTemplates.tpl');

        //echo $html;
        $view->setRendered(true);
    }

    /**
     * Handy little thing, this will either count the number of sub-categories at
     * each level, sticking the counts in $info... OR will put together an array of
     * the ID's for all sub-categories, stuffed into $info.
     *
     * @param ing $value_id
     * @param array $info Passed by reference, this will be populated by the method
     * @param bool $id_list If true, will put together an array of sub-category ID's,
     *   added to the $info param
     */
    private function parseKids($value_id, &$info, $id_list = false)
    {
        $db = DataAccess::getInstance();

        $children = $db->Execute("SELECT `category_id` FROM " . geoTables::categories_table . " WHERE `parent_id`=?", array($value_id));
        foreach ($children as $row) {
            $this->parseKids($row['category_id'], $info, $id_list);
        }

        //just getting info...
        $value = $db->GetRow("SELECT * FROM " . geoTables::categories_table . " WHERE `category_id`=?", array($value_id));
        if ($value) {
            if ($id_list) {
                if (!in_array($value_id, $info)) {
                    $info[] = $value_id;
                }
            } else {
                //just count them
                if (isset($info[$value['level']])) {
                    $info[$value['level']]++;
                } else {
                    $info[$value['level']] = 1;
                }
            }
        }
    }

    private function validCatId($value_id)
    {
        $value_id = (int)$value_id;
        if (!$value_id) {
            //value ID 0, don't verify
            return $value_id;
        }

        $db = DataAccess::getInstance();

        $count = $db->GetOne("SELECT COUNT(*) FROM " . geoTables::categories_table . " WHERE `category_id`=?", array($value_id));
        if ($count) {
            return $value_id;
        }
        //not found!
        geoAdmin::m("Could not find the parent value specified!  Showing the top level 1 categories.", geoAdmin::NOTICE);
        return 0;
    }

    private $_languages;

    /**
     * Get an array of languages from the DB
     *
     * @return array
     */
    private function getLanguages()
    {
        if (!isset($this->_languages)) {
            $db = DataAccess::getInstance();
            $this->_languages = $db->GetAll("SELECT `language_id`, `language` FROM " . geoTables::pages_languages_table . " ORDER BY `language_id`");
        }
        return $this->_languages;
    }

    /**
     * Get array of listing types used with this installation
     *
     * @return array
     */
    private function getListingTypes()
    {
        $types = array();
        if (geoMaster::is('classifieds')) {
            $types['classifieds'] = array (
                'label' => 'Classifieds',
                'src' => 'admin_images/icons/classified.gif'
                );
        }
        if (geoMaster::is('auctions')) {
            $types['auctions'] = array(
                'label' => 'Auctions',
                'src' => 'admin_images/icons/auction.gif'
            );
        }

        return $types;
    }

    private function ajaxError()
    {
        $tpl = new geoTemplate(geoTemplate::ADMIN);

        $tpl->assign('admin_msgs', geoAdmin::m());
        $tpl->display('leveled_fields/ajaxError.tpl');

        geoView::getInstance()->setRendered(true);
    }

    /**
     * Recursively "fixes" the levels of the specified value ID and all "child"
     * values, according to the new value specified.
     *
     * @param int $value_id
     * @param int $level
     */
    private function fixLevel($value_id, $level)
    {
        $db = DataAccess::getInstance();

        //first off, go through any children and update their levels...
        $values = $db->Execute("SELECT `category_id` FROM " . geoTables::categories_table . " WHERE `parent_id`=?", array($value_id));
        foreach ($values as $valueInfo) {
            $this->fixLevel($valueInfo['category_id'], ($level + 1));
        }

        //after taken care of all children, fix level for this one
        $result = $db->Execute("UPDATE " . geoTables::categories_table . " SET `level`=? WHERE `category_id`=?", array((int)$level, (int)$value_id));
        if (!$result) {
            geoAdmin::m('Error updating sub-category level after move!  Error reported: ' . $db->ErrorMsg(), geoAdmin::ERROR);
        }
    }

    private function copyCategory($value_id, $parent)
    {
        $db = DataAccess::getInstance();
        $langs = $this->getLanguages();

        $value_id = (int)$value_id;

        $info = $db->GetRow("SELECT * FROM " . geoTables::categories_table . " WHERE `category_id`=?", array($value_id));
        if (!$info) {
            geoAdmin::m("Error: copy from category ($value_id) could not be found!", geoAdmin::ERROR);
            return false;
        }
        $parent_id = ($parent) ? (int)$parent['category_id'] : 0;
        $level = ($parent) ? $parent['level'] + 1 : 1;

        $copy_fields_to_use = (isset($_POST['copy_fields']) && $_POST['copy_fields']);
        if (!$copy_fields_to_use && $info['what_fields_to_use'] == 'own') {
            //NOT copying fields to use... set it to use parent fields
            $info['what_fields_to_use'] = 'parent';
        }

        $copyFields = array ('enabled','display_order','what_fields_to_use',
            'display_ad_description_where','display_all_of_description','length_of_description',
            'default_display_order_while_browsing_category','use_auto_title','auto_title',
            'which_head_html'
            );
        $parts = $query_data = array();

        //add parent_id and level by hand, these are different than cat copying from
        $parts[] = '`parent_id`=?';
        $query_data[] = $parent_id;
        $parts[] = '`level`=?';
        $query_data[] = $level;

        //go through all the fields and add them
        foreach ($copyFields as $field) {
            $parts[] = "`{$field}`=?";
            $query_data[] = '' . $info[$field];
        }

        //need to insert new one in there...
        $result = $db->Execute(
            "INSERT INTO " . geoTables::categories_table . " SET " . implode(', ', $parts),
            $query_data
        );

        if (!$result) {
            geoAdmin::m('Error inserting copy into database, DB error reported: ' . $db->ErrorMsg(), geoAdmin::ERROR);
            return false;
        }
        $new_id = $db->Insert_Id();
        if (!$new_id) {
            geoAdmin::m('Error getting new id for new category inserted into database.', geoAdmin::ERROR);
            return false;
        }

        //set the ID in the existing info so we can pass it as the parent
        $info['category_id'] = $new_id;
        $info['level'] = $level;

        $copyLangFields = array ('category_name','description','category_image');
        if (strpos($info['which_head_html'], 'cat') !== false) {
            //cat specific head_html
            $copyLangFields[] = 'head_html';
        }

        //Ok now copy over the languages
        foreach ($langs as $lang) {
            $row = $db->GetRow(
                "SELECT * FROM " . geoTables::categories_languages_table . " WHERE `category_id`=? AND `language_id`=?",
                array($value_id, $lang['language_id'])
            );
            //insert it for the new one
            if (!$row) {
                geoAdmin::m("Error retrieving language value for category {$value_id} language {$lang['language_id']}! DB error reported: " . $db->ErrorMsg(), geoAdmin::ERROR);
                return false;
            }
            $parts = $query_data = array();
            //set id and language_id by hand
            $parts[] = "`category_id`=?";
            $query_data[] = $new_id;
            $parts[] = "`language_id`=?";
            $query_data[] = $lang['language_id'];

            foreach ($copyLangFields as $field) {
                $parts[] = "`{$field}`=?";
                $query_data[] = '' . $row[$field];
            }

            $db->Execute("INSERT INTO " . geoTables::categories_languages_table . " SET " . implode(', ', $parts), $query_data);
        }
        if (isset($_POST['copy_questions']) && $_POST['copy_questions']) {
            //copy questions...
            if (!$this->duplicate_category_questions($value_id, $new_id)) {
                return false;
            }
        }

        if ($info['what_fields_to_use'] == 'own') {
            //copy fields to use
            geoFields::copy($value_id, $new_id);
        }

        if (isset($_POST['copy_price_plans']) && $_POST['copy_price_plans']) {
            $this->copyPricePlan($value_id, $new_id);
        }

        if (isset($_POST['copy_subcategories']) && $_POST['copy_subcategories']) {
            //now, go through any children and copy those...
            $values = $db->Execute("SELECT `category_id` FROM " . geoTables::categories_table . " WHERE `parent_id`=?", array($value_id));
            foreach ($values as $valueInfo) {
                if (!$this->copyCategory($valueInfo['category_id'], $info)) {
                    //something went wrong copying a child, stop here
                    return false;
                }
            }
        }
        return true;
    }

    private function copyPricePlan($from_category, $to_category)
    {
        $db = DataAccess::getInstance();

        $from_category = (int)$from_category;
        $to_category = (int)$to_category;
        if (!$from_category || !$to_category) {
            geoAdmin::m("Error: invalid from/to category when copying price plan.", geoAdmin::ERROR);
            return false;
        }

        $plans = $db->Execute("SELECT * FROM " . geoTables::price_plans_categories_table . " WHERE `category_id`=?", array($from_category));

        foreach ($plans as $plan) {
            //copy main stuff

            //change category_id...
            $plan['category_id'] = $to_category;
            $category_price_plan_id = $plan['category_price_plan_id'];
            $price_plan_id = $plan['price_plan_id'];
            //unset it so it doesn't try to set it
            unset($plan['category_price_plan_id']);
            if (!$this->_insert(geoTables::price_plans_categories_table, $plan)) {
                //problem!
                return false;
            }

            $new_category_price_plan_id = $db->Insert_Id();
            //classifieds_price_increments
            $increments = $db->Execute("SELECT * FROM " . geoTables::price_plans_increments_table . " WHERE `price_plan_id`=?
					AND `category_id`=?", array($price_plan_id, $from_category));
            foreach ($increments as $increment) {
                //adjust data for what we are inserting
                $increment['category_id'] = $to_category;
                if (!$this->_insert(geoTables::price_plans_increments_table, $increment)) {
                    //oops, error!
                    return false;
                }
            }

            $lengths = $db->Execute("SELECT * FROM " . geoTables::price_plan_lengths_table . " WHERE `category_id`=? AND `price_plan_id`=?", array($from_category, $price_plan_id));
            foreach ($lengths as $length) {
                //adjust data for insertion
                $length['category_id'] = $to_category;
                unset($length['length_id']);
                //price_plan_ad_lengths
                if (!$this->_insert(geoTables::price_plan_lengths_table, $length)) {
                    //uh oh!
                    return false;
                }
            }

            geoPlanItem::copyPlanItems($price_plan_id, $from_category, $to_category);
        }
    }

    private function _insert($table, $data)
    {
        $db = DataAccess::getInstance();

        $parts = $query_data = array();

        foreach ($data as $col => $value) {
            if (is_numeric($col)) {
                //just in case we got the number indexes too
                continue;
            }
            $parts[] = "`$col`=?";
            $query_data[] = $value;
        }
        $result = $db->Execute("INSERT INTO $table SET " . implode(', ', $parts), $query_data);
        if (!$result) {
            geoAdmin::m("Problem adding copy of price plan, error reported: " . $db->ErrorMsg(), geoAdmin::ERROR);
            return false;
        }
        return true;
    }

    private function duplicate_category_questions($start_category, $target_category = 0)
    {
        $db = DataAccess::getInstance();
        //clean inputs
        $start_category = (int)$start_category;
        $target_category = (int)$target_category;
        if (!$start_category || !$target_category) {
            geoAdmin::m('From and/or TO category required when copying questions.', geoAdmin::ERROR);
            return false;
        }

        //since entering category ID is allowed, check the TO category
        $sql = "SELECT * FROM " . geoTables::categories_table . " WHERE `category_id` = $target_category LIMIT 1";
        $row = $db->GetRow($sql);
        if (!$row) {
            geoAdmin::m("Invalid TO category specified (category ID $target_category), please verify the category you are copying questions to." . $db->ErrorMsg(), geoAdmin::ERROR);
            return false;
        }
        $sql = "SELECT * FROM " . geoTables::questions_table . " WHERE `category_id`=$start_category ORDER BY `display_order` ASC";
        $category_question_result = $db->Execute($sql);

        if (!$category_question_result) {
            geoAdmin::m("Error getting question to copy, error reported: " . $db->ErrorMsg(), geoAdmin::ERROR);
            return false;
        }

        while ($show_category_question = $category_question_result->FetchRow()) {
            $sql = "INSERT INTO " . geoTables::questions_table . "
			(category_id, name, explanation, choices, other_input, display_order)
			values
			($target_category, ?, ?, ?, ?, ? )";

            $query_data = array ($show_category_question["name"],
                $show_category_question["explanation"],
                $show_category_question["choices"],
                $show_category_question["other_input"],
                $show_category_question["display_order"],
            );

            $result = $db->Execute($sql, $query_data);
            if (!$result) {
                geoAdmin::m("Error inserting category question, error reported: " . $db->ErrorMsg(), geoAdmin::ERROR);
                return false;
            }
            $insert_id = $db->Insert_ID();

            //get language specific portions of category question
            $sql = "SELECT * FROM " . geoTables::questions_languages . " WHERE `question_id` = " . $show_category_question["question_id"];
            $question_language_result = $db->Execute($sql);

            //insert the language specific
            while ($language_specific = $question_language_result->FetchRow()) {
                $input = array( $insert_id, $language_specific["language_id"], $language_specific["name"],
                    $language_specific["explanation"], $language_specific["choices"]);
                $sql = "INSERT INTO " . geoTables::questions_languages . "
					(question_id, language_id, name, explanation, choices)
					values (?,?,?,?,?)";
                $insert_result = $db->Execute($sql, $input);

                if (!$insert_result) {
                    geoAdmin::m("Error inserting category question, error reported: " . $db->ErrorMsg(), geoAdmin::ERROR);
                    return false;
                }
            }
        }
        return true;
    }

    private function _addCssJs()
    {
        geoView::getInstance()
            //->addCssFile('css/leveled_fields.css')
            ->addJScript('js/categories_admin.js');
    }
}
