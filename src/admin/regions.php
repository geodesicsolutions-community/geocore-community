<?php

//admin/regions.php


class RegionsManagement
{
    public function display_regions()
    {
        $admin = geoAdmin::getInstance();
        $db = DataAccess::getInstance();
        $region = geoRegion::getInstance();

        //the parent region to show..
        $parent = (int)$_GET['parent'];

        //verify it is valid...
        $parent = $this->validRegionId($parent);

        $tpl_vars = array();

        $tpl_vars['admin_msgs'] = $admin->message();

        $perPage = 100;

        $allPages = (isset($_GET['p']) && $_GET['p'] === 'all');
        $page = (isset($_GET['p'])) ? $_GET['p'] : 1;

        $total_count = $db->GetOne("SELECT COUNT(*) FROM " . geoTables::region . " WHERE parent=?", array($parent));

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
            $tpl_vars['pagination'] = geoPagination::getHTML($maxPages, $page, 'index.php?page=regions&amp;parent=' . $parent . '&amp;p=', '', '', true, false);
        }

        $limit = ($page === 'all') ? '' : 'LIMIT ' . (($page - 1) * $perPage) . ', ' . $perPage;

        $regions = $db->Execute("SELECT * FROM " . geoTables::region . " r, " . geoTables::region_languages . " l WHERE l.id=r.id AND l.language_id=1 AND r.parent=? ORDER BY r.display_order, l.name $limit", array($parent));

        foreach ($regions as $row) {
            //get listing count
            $row['listing_count'] = $db->GetOne("SELECT COUNT(*) FROM " . geoTables::listing_regions . " WHERE `region`=?", array($row['id']));
            $tpl_vars['regions'][] = $row;
        }

        $tpl_vars['parent'] = $parent;
        if ($parent) {
            $tpl_vars['parents'] = $region->getParents($parent, true);
        }
        if ($parent) {
            $info = $region->getRegionInfo($parent);
            $tpl_vars['level'] = $region->getLevel($info['level'] + 1);
            $tpl_vars['parent_name'] = $info['name'];
        } else {
            $tpl_vars['level'] = $region->getLevel(1);
        }

        $admin->setBodyTpl('regions/index.tpl')
            ->v()->setBodyVar($tpl_vars);
    }

    public function display_region_create()
    {
        if (!geoAjax::isAjax()) {
            return $this->display_regions();
        }

        $admin = geoAdmin::getInstance();
        $db = DataAccess::getInstance();
        $region = geoRegion::getInstance();

        //the parent region to show..
        $parent = (int)$_GET['parent'];

        //verify it is valid...
        $parent = $this->validRegionId($parent);
        if ($parent) {
            $parent_info = $region->getRegionInfo($parent);
            $level = (int)$parent_info['level'] + 1;
        } else {
            $level = 1;
        }
        $tpl_vars = array();

        //get all the languages:
        $tpl_vars['languages'] = $this->getLanguages();


        //ajax call, just display template
        $tpl_vars['is_ajax'] = true;
        $tpl_vars['parent'] = $parent;
        if ($parent) {
            $tpl_vars['parents'] = $region->getParents($parent, true);
        }
        $tpl_vars['region'] = array(
                'unique_name' => '','name' => '',
                'display_order' => '1','tax_percent' => '0','tax_flat' => 0,
                'level' => $level, 'parent' => $parent
                );
        $tpl_vars['new'] = true;
        $tpl_vars['precurrency'] = $db->get_site_setting('precurrency');
        $tpl_vars['postcurrency'] = $db->get_site_setting('postcurrency');

        $tpl = new geoTemplate(geoTemplate::ADMIN);

        $tpl->assign($tpl_vars);

        $tpl->display('regions/edit.tpl');

        $admin->v()->setRendered(true);
    }

    public function update_region_create()
    {
        $parent = (int)$_GET['parent'];
        $parent = $this->validRegionId($parent);
        $region = geoRegion::getInstance();

        if ($parent) {
            $parent_info = $region->getRegionInfo($parent);
            $level = (int)$parent_info['level'] + 1;
        } else {
            $level = 1;
        }

        $db = DataAccess::getInstance();
        $langs = $this->getLanguages();

        //find out if this level already exists. If not, create it.
        $levelExists = $db->GetOne("SELECT `level` FROM " . geoTables::region_level . " WHERE `level` = ?", array($level));
        if (!$levelExists) {
            $insert = $db->Execute("INSERT INTO " . geoTables::region_level . " (level, region_type, use_label, always_show) VALUES (?, ?, ?, ?)", array($level, 'other', 'yes', 'yes'));
            if (!$insert) {
                geoAdmin::m('Failed to insert level due to database error: ' . $db->ErrorMsg(), geoAdmin::ERROR);
                return false;
            }
            foreach ($langs as $lang) {
                $insert = $db->Execute("INSERT INTO " . geoTables::region_level_labels . " (level, language_id) VALUES (?, ?)", array($level, $lang['language_id']));
                if (!$insert) {
                    geoAdmin::m('Failed to insert level due to database error on language' . $lang['language_id'] . ': ' . $db->ErrorMsg(), geoAdmin::ERROR);
                    return false;
                }
            }
        }


        $unique_name = $this->cleanUnique($_POST['unique_name']);
        if ($unique_name && !$this->checkUnique(0, $unique_name)) {
            geoAdmin::m("Unique name ({$unique_name}) already in use, not able to add new region.", geoAdmin::ERROR);
            return false;
        }

        $display_order = (int)$_POST['display_order'];

        $enabled = (isset($_POST['enabled']) && $_POST['enabled']) ? 'yes' : 'no';
        $billing_abbreviation = trim($_POST['billing_abbreviation']);
        $tax_percent = round($_POST['tax_percent'], 4);
        $tax_flat = geoNumber::deformat($_POST['tax_flat']);

        //insert it in there
        $result = $db->Execute(
            "INSERT INTO " . geoTables::region . " SET `parent`=?, `level`=?, `enabled`=?, `unique_name`=?, `display_order`=?,
				`billing_abbreviation`=?, `tax_percent`=?, `tax_flat`=?",
            array($parent, $level, $enabled, $unique_name, $display_order, $billing_abbreviation, $tax_percent, $tax_flat)
        );

        if (!$result) {
            geoAdmin::m("DB error when attempting to add new region: " . $db->ErrorMsg(), geoAdmin::ERROR);
            return false;
        }

        $region_id = $db->Insert_Id();

        foreach ($langs as $lang) {
            $name = trim($_POST['name'][$lang['language_id']]);
            $name = geoString::toDB($name);
            $result = $db->Execute("INSERT INTO " . geoTables::region_languages . " SET `id`=?, `language_id`=?, `name`=?", array($region_id, $lang['language_id'], $name));
            if (!$result) {
                geoAdmin::m("DB error (2) when attempting to add new region: " . $db->ErrorMsg(), geoAdmin::ERROR);
                return false;
            }
        }
        return true;
    }

    public function display_region_enabled()
    {
        if (!geoAjax::isAjax()) {
            //this shouldn't really happen but whatevs
            return $this->display_regions();
        }
        geoAjax::getInstance()->jsonHeader();
        $region = geoRegion::getInstance();

        $region_id = (int)$_POST['region'];
        $regionInfo = $region->getRegionInfo($region_id);

        $tpl = new geoTemplate(geoTemplate::ADMIN);

        $tpl->assign('region', $regionInfo)
            ->assign('is_ajax', true);

        $tpl->display('regions/enabled.tpl');

        geoView::getInstance()->setRendered(true);
    }

    public function update_region_enabled()
    {
        $db = DataAccess::getInstance();
        $region = geoRegion::getInstance();

        $region_id = (int)$_POST['region'];

        $regionInfo = $region->getRegionInfo($region_id);

        if ($regionInfo) {
            $enabled = ($regionInfo['enabled'] === 'yes') ? 'no' : 'yes';

            $db->Execute("UPDATE " . geoTables::region . " SET `enabled`=? WHERE `id`=?", array($enabled, $region_id));
        }
        return true;
    }

    public function display_region_create_bulk()
    {
        if (!geoAjax::isAjax()) {
            return $this->display_regions();
        }

        $admin = geoAdmin::getInstance();
        $db = DataAccess::getInstance();
        $region = geoRegion::getInstance();

        //the parent region to show..
        $parent = (int)$_GET['parent'];

        //verify it is valid...
        $parent = $this->validRegionId($parent);
        if ($parent) {
            $parent_info = $region->getRegionInfo($parent);
            $level = (int)$parent_info['level'] + 1;
        } else {
            $level = 1;
        }
        $tpl_vars = array();

        //ajax call, just display template
        $tpl_vars['parent'] = $parent;
        if ($parent) {
            $tpl_vars['parents'] = $region->getParents($parent, true);
        }
        $tpl_vars['level'] = $level;
        $tpl_vars['parent_info'] = $parent_info;
        $tpl_vars['precurrency'] = $db->get_site_setting('precurrency');
        $tpl_vars['postcurrency'] = $db->get_site_setting('postcurrency');

        //figure out what to use as the display order...
        $display_order = (int)$db->GetOne("SELECT MAX(`display_order`) FROM " . geoTables::region . " WHERE `parent`=?", array($parent));
        if ($display_order > 1) {
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

        $tpl->display('regions/bulk.tpl');

        $admin->v()->setRendered(true);
    }

    public function update_region_create_bulk()
    {
        $parent = (int)$_GET['parent'];
        $parent = $this->validRegionId($parent);
        $region = geoRegion::getInstance();

        if ($parent) {
            $parent_info = $region->getRegionInfo($parent);
            $level = (int)$parent_info['level'] + 1;
        } else {
            $level = 1;
        }

        $db = DataAccess::getInstance();
        $langs = $this->getLanguages();

        //find out if this level already exists. If not, create it.
        $levelExists = $db->GetOne("SELECT `level` FROM " . geoTables::region_level . " WHERE `level` = ?", array($level));
        if (!$levelExists) {
            $insert = $db->Execute("INSERT INTO " . geoTables::region_level . " (level, region_type, use_label, always_show) VALUES (?, ?, ?, ?)", array($level, 'other', 'yes', 'yes'));
            if (!$insert) {
                geoAdmin::m('Failed to insert level due to database error: ' . $db->ErrorMsg(), geoAdmin::ERROR);
                return false;
            }
            foreach ($langs as $lang) {
                $insert = $db->Execute("INSERT INTO " . geoTables::region_level_labels . " (level, language_id) VALUES (?, ?)", array($level, $lang['language_id']));
                if (!$insert) {
                    geoAdmin::m('Failed to insert level due to database error on language' . $lang['language_id'] . ': ' . $db->ErrorMsg(), geoAdmin::ERROR);
                    return false;
                }
            }
        }


        if (isset($_POST['undo']) && $_POST['undo']) {
            $min_region_id = (int)$_POST['min_region_id'];
            $max_region_id = (int)$_POST['max_region_id'];

            if (!$min_region_id || !$max_region_id) {
                geoAdmin::m("Error, invalid min/max value when attempting to undo bulk add.", geoAdmin::ERROR);
                return false;
            }
            //undo (remove) regions in range
            $result = $db->Execute("DELETE FROM " . geoTables::region . " WHERE `id`>=? AND `id`<=?", array($min_region_id, $max_region_id));
            if (!$result) {
                geoAdmin::m("Error attempting to remove regions, DB error: " . $db->ErrorMsg(), geoAdmin::ERROR);
                return false;
            }
            $result = $db->Execute("DELETE FROM " . geoTables::region_languages . " WHERE `id`>=? AND `id`<=?", array($min_region_id, $max_region_id));
            if (!$result) {
                geoAdmin::m("Error attempting to remove region languages, DB error: " . $db->ErrorMsg(), geoAdmin::ERROR);
                return false;
            }
            //reset increment so that if someone adds thousands of regions and un-does several times,
            //it won't end up taking all the available slots...
            $result = $db->Execute("ALTER TABLE " . geoTables::region . " AUTO_INCREMENT=1");
            if (!$result) {
                geoAdmin::m("DB Error attempting to reset AUTO_INCREMENT, DB error: " . $db->ErrorMsg(), geoAdmin::ERROR);
            }
            geoAdmin::m('Undo successful, removed the regions that were just added.');
            return true;
        }

        $unique_use = (isset($_POST['unique_use']) && $_POST['unique_use']);
        if ($unique_use) {
            //note: do the cleaning once the full unique name is assembled..
            $unique_use = true;
            $unique_pre = trim($_POST['unique_pre']);
            $unique_post = trim($_POST['unique_post']);
        }
        $display_order = (int)$_POST['display_order'];
        $inc_order = ($_POST['display_order_type'] === 'inc');

        $tax_percent = round($_POST['tax_percent'], 4);
        $tax_flat = geoNumber::deformat($_POST['tax_flat']);


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

        //keep track of names added to prevent adding multiples...
        $duplicates = 0;
        $max_region_id = 0;
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
            $dup_count = (int)$db->GetOne("SELECT COUNT(*) FROM " . geoTables::region_languages . " l,
					" . geoTables::region . " r
					WHERE l.id=r.id AND r.parent=? AND l.`name`=?", array($parent, urlencode($name)));
            if ($dup_count > 0) {
                $duplicates++;
                continue;
            }

            //figure out unique name to use (if any)
            $unique_name = '';
            if ($unique_use) {
                $unique_name = $this->cleanUnique($unique_pre . $name . $unique_post);
                if ($unique_name && !$this->checkUnique(0, $unique_name)) {
                    //the unique name was found being used already, don't use it
                    $unique_name = '';
                }
            }

            //insert it in there
            $result = $db->Execute(
                "INSERT INTO " . geoTables::region . " SET `parent`=?, `level`=?, `enabled`=?, `unique_name`=?, `display_order`=?,
					`billing_abbreviation`='', `tax_percent`=?, `tax_flat`=?",
                array($parent, $level, $enabled, $unique_name, $display_order, $tax_percent, $tax_flat)
            );

            if (!$result) {
                geoAdmin::m("DB error when attempting to bulk add new region: " . $db->ErrorMsg(), geoAdmin::ERROR);
                return false;
            }

            $region_id = $db->Insert_Id();
            //clean name for DB
            $name = geoString::toDB($name);

            foreach ($langs as $lang) {
                $result = $db->Execute("INSERT INTO " . geoTables::region_languages . " SET `id`=?, `language_id`=?, `name`=?", array($region_id, $lang['language_id'], $name));
                if (!$result) {
                    geoAdmin::m("DB error (2) when attempting to add new region: " . $db->ErrorMsg(), geoAdmin::ERROR);
                    return false;
                }
            }
            if ($inc_order) {
                $display_order++;
            }
            $count++;
            if (!isset($min_region_id)) {
                $min_region_id = $region_id;
            }
            $max_region_id = $region_id;
        }
        $undo = '';
        if (($max_region_id - $min_region_id + 1) === $count) {
            //region id's are continuous, so let them undo if they want...
            $undo = '<a href="index.php?page=region_create_bulk&amp;undo=1&amp;parent=' . $parent . '&amp;min_region_id=' . $min_region_id . '&amp;max_region_id=' . $max_region_id . '&amp;auto_save=1" class="mini_cancel lightUpLink">Undo Bulk Add!</a>';
        }

        geoAdmin::m("Successfully added $count regions!  $undo");
        if ($duplicates > 0) {
            geoAdmin::m("Note: there were $duplicates duplicate entries that were skipped.");
        }

        return true;
    }

    public function display_region_edit()
    {
        if (!geoAjax::isAjax()) {
            return $this->display_regions();
        }

        $admin = geoAdmin::getInstance();
        $db = DataAccess::getInstance();
        $region = geoRegion::getInstance();

        //the parent region to show..
        $region_id = (int)$_GET['region'];

        if (!$region_id) {
            $admin->message("Error: Invalid region specified.", geoAdmin::ERROR);
            return $this->ajaxError();
        }

        $regionInfo = $region->getRegionInfo($region_id);
        if (!$regionInfo) {
            $admin->message("Error: Region not found.", geoAdmin::ERROR);
            return $this->ajaxError();
        }

        $tpl_vars = array();

        //get all the languages:
        $tpl_vars['languages'] = $this->getLanguages();

        $tpl_vars['region'] = $regionInfo;
        $tpl_vars['level'] = $region->getLevel($regionInfo['level']);
        $tpl_vars['parent'] = $regionInfo['parent'];
        $page = 1;
        if (isset($_GET['p'])) {
            $page = ($_GET['p'] === 'all') ? 'all' : (int)$_GET['p'];
        }
        $tpl_vars['page'] = $page;

        $names = array ();
        $rows = $db->Execute("SELECT * FROM " . geoTables::region_languages . " WHERE `id`=? ORDER BY `language_id`", array($region_id));

        foreach ($rows as $row) {
            $names[$row['language_id']] = geoString::fromDB($row['name']);
        }
        $tpl_vars['names'] = $names;
        $tpl_vars['parents'] = $region->getParents($region_id);

        $tpl_vars['precurrency'] = $db->get_site_setting('precurrency');
        $tpl_vars['postcurrency'] = $db->get_site_setting('postcurrency');

        //ajax call, just display template
        $tpl = new geoTemplate(geoTemplate::ADMIN);

        $tpl->assign($tpl_vars);

        $tpl->display('regions/edit.tpl');

        $admin->v()->setRendered(true);
    }

    public function update_region_edit()
    {
        $region_id = (int)$_GET['region'];
        if (!$region_id) {
            geoAdmin::m('Invalid region to edit!', geoAdmin::ERROR);
            return false;
        }

        $region = geoRegion::getInstance();

        $regionInfo = $region->getRegionInfo($region_id);
        $parent = $regionInfo['parent'];

        //always re-calculate level...  at some point we might add "skiping levels" but not yet.
        if ($parent) {
            $parent_info = $region->getRegionInfo($parent);
            $level = (int)$parent_info['level'] + 1;
        } else {
            $level = 1;
        }

        $db = DataAccess::getInstance();

        $unique_name = $this->cleanUnique($_POST['unique_name']);
        if ($unique_name && !$this->checkUnique($region_id, $unique_name)) {
            geoAdmin::m("Unique name ({$unique_name}) already in use, not able to modify region.", geoAdmin::ERROR);
            return false;
        }

        $names = $_POST['name'];
        foreach ($names as $name) {
            if (!trim($name)) {
                geoAdmin::m('Error:  Region name is required for all languages.', geoAdmin::ERROR);
                return false;
            }
        }

        $display_order = (int)$_POST['display_order'];

        $enabled = (isset($_POST['enabled']) && $_POST['enabled']) ? 'yes' : 'no';
        $billing_abbreviation = trim($_POST['billing_abbreviation']);
        $tax_percent = round($_POST['tax_percent'], 4);
        $tax_flat = geoNumber::deformat($_POST['tax_flat']);

        //insert it in there
        $result = $db->Execute(
            "UPDATE " . geoTables::region . " SET `level`=?, `enabled`=?, `unique_name`=?, `display_order`=?,
				`billing_abbreviation`=?, `tax_percent`=?, `tax_flat`=? WHERE `id`=?",
            array($level, $enabled, $unique_name, $display_order, $billing_abbreviation, $tax_percent, $tax_flat, $region_id)
        );

        if (!$result) {
            geoAdmin::m("DB error when attempting to edit region: " . $db->ErrorMsg(), geoAdmin::ERROR);
            return false;
        }

        $langs = $this->getLanguages();
        //Clear current ones...
        $db->Execute("DELETE FROM " . geoTables::region_languages . " WHERE `id`=?", array($region_id));
        //now re-add them all
        foreach ($langs as $lang) {
            $name = trim($names[$lang['language_id']]);
            $name = geoString::toDB($name);
            $result = $db->Execute("INSERT INTO " . geoTables::region_languages . " SET `id`=?, `language_id`=?, `name`=?", array($region_id, $lang['language_id'], $name));
            if (!$result) {
                geoAdmin::m("DB error when attempting to add region name for language {$lang['language_id']}, db error: " . $db->ErrorMsg(), geoAdmin::ERROR);
                return false;
            }
        }
        return true;
    }

    public function display_region_edit_bulk()
    {
        if (!geoAjax::isAjax()) {
            return $this->display_regions();
        }

        $admin = geoAdmin::getInstance();
        $db = DataAccess::getInstance();
        $region = geoRegion::getInstance();

        //the regions...
        $regions = array();
        foreach ($_POST['regions'] as $region_id) {
            $region_id = (int)$region_id;
            if ($region_id && !in_array($region_id, $regions)) {
                $regions[] = $region_id;
            }
        }
        if (!count($regions)) {
            geoAdmin::m("Error: No regions selected!", geoAdmin::ERROR);
            return $this->ajaxError();
        }

        $parent = (int)$_GET['parent'];
        if ($parent) {
            $parent_info = $region->getRegionInfo($parent);
            $level = (int)$parent_info['level'] + 1;
        } else {
            $level = 1;
        }
        $tpl_vars = array();

        //get all the languages:
        $tpl_vars['languages'] = $this->getLanguages();

        $tpl_vars['regions'] = $regions;
        $tpl_vars['region_count'] = count($regions);
        $tpl_vars['parent'] = $parent;
        $tpl_vars['level'] = $region->getLevel($level);
        $page = 1;
        if (isset($_GET['p'])) {
            $page = ($_GET['p'] === 'all') ? 'all' : (int)$_GET['p'];
        }
        $tpl_vars['page'] = $page;
        if ($parent) {
            $tpl_vars['parents'] = $region->getParents($parent, true);
            $tpl_vars['parent_info'] = $region->getRegionInfo($parent);
        }

        $tpl_vars['precurrency'] = $db->get_site_setting('precurrency');
        $tpl_vars['postcurrency'] = $db->get_site_setting('postcurrency');

        //ajax call, just display template
        $tpl = new geoTemplate(geoTemplate::ADMIN);

        $tpl->assign($tpl_vars);

        $tpl->display('regions/bulkEdit.tpl');

        $admin->v()->setRendered(true);
    }

    public function update_region_edit_bulk()
    {
        $parent = (int)$_GET['parent'];
        $parent = $this->validRegionId($parent);
        $region = geoRegion::getInstance();

        if ($parent) {
            $parent_info = $region->getRegionInfo($parent);
            $level = (int)$parent_info['level'] + 1;
        } else {
            $level = 1;
        }

        $db = DataAccess::getInstance();

        $regions = array();
        foreach ($_POST['regions'] as $region_id) {
            $region_id = (int)$region_id;
            if ($region_id && !in_array($region_id, $regions)) {
                $regions[] = $region_id;
            }
        }

        if (!count($regions)) {
            geoAdmin::m("Error: No regions selected!", geoAdmin::ERROR);
            return false;
        }

        $set = array();
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

        $unique_use = (isset($_POST['unique_use']) && $_POST['unique_use']) ? $_POST['unique_use'] : false;
        if ($unique_use) {
            //note: do the cleaning once the full unique name is assembled..
            if ($unique_use === 'clear') {
                $set[] = "`unique_name`=''";
                $unique_use = false;
            } elseif ($unique_use === 'abbreviation' || $unique_use === 'auto_set' || (int)$unique_use > 0) {
                $unique_pre = trim($_POST['unique_pre']);
                $unique_post = trim($_POST['unique_post']);
            }
        }

        if ($_POST['tax_percent_change']) {
            $set[] = "`tax_percent`=?";
            $query_vars[] =  round($_POST['tax_percent'], 4);
        }

        if ($_POST['tax_flat_change']) {
            $set[] = "`tax_flat`=?";
            $query_vars[] =  geoNumber::deformat($_POST['tax_flat']);
        }

        //now do the mass update which should be easy
        if (!count($set) && !$inc_display_order && !$unique_use) {
            //nothing to do...
            geoAdmin::m("Error: No changes specified, nothing to bulk-change!", geoAdmin::ERROR);
            return false;
        }

        if (count($set)) {
            $sql = "UPDATE " . geoTables::region . " SET " . implode(', ', $set) . " WHERE `id` IN (" . implode(', ', $regions) . ")";
            $result = $db->Execute($sql, $query_vars);
            if (!$result) {
                geoAdmin::m("DB error attempting to apply bulk changes, error reported: " . $db->ErrorMsg(), geoAdmin::ERROR);
                return false;
            }
        }

        if ($inc_display_order || $unique_use) {
            //must also go through every single one...
            //first clear time limit
            set_time_limit(0);

            foreach ($regions as $region_id) {
                $set = $query_vars = array();

                if ($unique_use) {
                    $language_id = 1;
                    if (is_numeric($unique_use) && $unique_use > 0) {
                        //use this language ID instead
                        $language_id = (int)$unique_use;
                    }

                    $info = $region->getRegionInfo($region_id, $language_id);

                    if (!$info) {
                        geoAdmin::m("Error attempting to get region info for #{$region_id}, cannot finish bulk edit.", geoAdmin::ERROR);
                        return false;
                    }
                    if ($unique_use === 'abbreviation') {
                        $name = trim($info['billing_abbreviation']);
                    } else {
                        $name = trim($info['name']);
                    }
                    if ($name) {
                        //only try to set if name is not blank (common for abbreviation to be blank)
                        $unique_name = $this->cleanUnique($unique_pre . $name . $unique_post);
                        if ($unique_name && !$this->checkUnique($region_id, $unique_name)) {
                            //the unique name was found being used already, don't use it
                        } else {
                            $set[] = "`unique_name`=?";
                            $query_vars[] = $unique_name;
                        }
                    }
                }
                if ($inc_display_order) {
                    $set[] = "`display_order`=?";
                    $query_vars[] = $display_order;
                    $display_order++;
                }
                if (count($set)) {
                    $sql = "UPDATE " . geoTables::region . " SET " . implode(', ', $set) . " WHERE `id`={$region_id}";
                    $result = $db->Execute($sql, $query_vars);
                    if (!$result) {
                        geoAdmin::m("Error attempting to update region! DB error reported: " . $db->ErrorMsg(), geoAdmin::ERROR);
                        return false;
                    }
                }
            }
        }
        $count = count($regions);
        geoAdmin::m("Successfully updated $count regions!");

        return true;
    }

    public function display_region_delete()
    {
        if (!geoAjax::isAjax()) {
            return $this->display_regions();
        }

        $admin = geoAdmin::getInstance();
        $db = DataAccess::getInstance();
        $region = geoRegion::getInstance();

        //the regions...
        $regions = array();
        foreach ($_POST['regions'] as $region_id) {
            $region_id = (int)$region_id;
            if ($region_id && !in_array($region_id, $regions)) {
                $regions[] = $region_id;
            }
        }
        if (!count($regions)) {
            geoAdmin::m("Error: No regions selected!", geoAdmin::ERROR);
            return $this->ajaxError();
        }

        //what we gonna do:  show all the regions and sub-regions that would be affected...
        $parent = (int)$_GET['parent'];
        if ($parent) {
            $parent_info = $region->getRegionInfo($parent);
            $level = (int)$parent_info['level'] + 1;
        } else {
            $level = 1;
        }
        $tpl_vars = array();

        $tpl_vars['levels_removed'] = geoRegion::remove($regions, true);

        $tpl_vars['regions'] = $regions;
        $tpl_vars['region_count'] = count($regions);
        $tpl_vars['parent'] = $parent;
        $tpl_vars['level'] = $level;
        if ($parent) {
            $tpl_vars['parents'] = $region->getParents($parent, true);
            $tpl_vars['parent_info'] = $region->getRegionInfo($parent);
        }

        $tpl_vars['precurrency'] = $db->get_site_setting('precurrency');
        $tpl_vars['postcurrency'] = $db->get_site_setting('postcurrency');

        $page = 1;
        if (isset($_GET['p'])) {
            $page = ($_GET['p'] === 'all') ? 'all' : (int)$_GET['p'];
        }
        $tpl_vars['page'] = $page;

        //ajax call, just display template
        $tpl = new geoTemplate(geoTemplate::ADMIN);

        $tpl->assign($tpl_vars);

        $tpl->display('regions/delete.tpl');

        $admin->v()->setRendered(true);
    }

    public function update_region_delete()
    {
        $db = DataAccess::getInstance();

        $regions = array();
        foreach ($_POST['regions'] as $region_id) {
            $region_id = (int)$region_id;
            if ($region_id && !in_array($region_id, $regions)) {
                $regions[] = $region_id;
            }
        }

        if (!count($regions)) {
            geoAdmin::m("Error: No regions selected to delete!", geoAdmin::ERROR);
            return false;
        }

        if (!isset($_POST['really']) || $_POST['really'] !== 'yes') {
            geoAdmin::m("OK, nothing done since you may not be looking at what you are clicking on.  (You almost just deleted a bunch of regions without realizing it!)", geoAdmin::NOTICE);
            return false;
        }
        //ok, delete them!
        geoRegion::remove($regions, false);
        geoAdmin::m("Deleted the " . count($regions) . " selected region(s) and all sub-regions.");
        return true;
    }

    public function display_region_move()
    {
        if (!geoAjax::isAjax()) {
            return $this->display_regions();
        }

        $admin = geoAdmin::getInstance();
        $db = DataAccess::getInstance();
        $region = geoRegion::getInstance();

        //the regions...
        $regions = array();
        foreach ($_POST['regions'] as $region_id) {
            $region_id = (int)$region_id;
            if ($region_id && !in_array($region_id, $regions)) {
                $regions[] = $region_id;
            }
        }
        if (!count($regions)) {
            geoAdmin::m("Error: No regions selected!", geoAdmin::ERROR);
            return $this->ajaxError();
        }

        if (isset($_GET['browse']) && $_GET['browse']) {
            $tpl_vars = array();
            $new_parent = (int)$_GET['parent'];
            $new_parent = $this->validRegionId($new_parent);
            $tpl_vars['new_parent'] = $new_parent;
            if ($new_parent) {
                $tpl_vars['new_parents'] = $region->getParents($new_parent, true);
            }
            //leave out the selected regions...
            $tpl_vars['browse_regions'] = $db->GetAll("SELECT * FROM " . geoTables::region . " r, " . geoTables::region_languages . " l WHERE l.id=r.id AND l.language_id=1 AND r.parent=? AND r.id NOT IN (" . implode(',', $regions) . ") ORDER BY r.display_order, l.name", array($new_parent));

            $tpl_vars['browse_link'] = 'index.php?page=region_move&amp;browse=1&amp;parent=';

            $tpl = new geoTemplate(geoTemplate::ADMIN);
            $tpl->assign($tpl_vars);
            $tpl->display('regions/moveBrowse.tpl');
            $admin->v()->setRendered(true);
            return;
        }

        //what we gonna do:  show all the regions and sub-regions that would be affected...
        $parent = (int)$_GET['parent'];
        if ($parent) {
            $parent_info = $region->getRegionInfo($parent);
            $level = (int)$parent_info['level'] + 1;
        } else {
            $level = 1;
        }
        $tpl_vars = array();

        $tpl_vars['regions'] = $regions;
        $tpl_vars['region_count'] = count($regions);
        $tpl_vars['parent'] = $parent;
        $tpl_vars['level'] = $level;
        if ($parent) {
            $tpl_vars['parents'] = $region->getParents($parent, true);
            $tpl_vars['parent_info'] = $region->getRegionInfo($parent);
        }
        $new_parent = (isset($_GET['new_parent'])) ? $_GET['new_parent'] : $parent;

        if ($new_parent) {
            $tpl_vars['new_parents'] = $region->getParents($new_parent, true);
            $tpl_vars['new_parent_info'] = $region->getRegionInfo($new_parent);
        }
        //leave out the selected regions...
        $tpl_vars['browse_regions'] = $db->GetAll("SELECT * FROM " . geoTables::region . " r, " . geoTables::region_languages . " l WHERE l.id=r.id AND l.language_id=1 AND r.parent=? AND r.id NOT IN (" . implode(',', $regions) . ") ORDER BY r.display_order, l.name", array($new_parent));

        $tpl_vars['browse_link'] = 'index.php?page=region_move&amp;browse=1&amp;parent=';

        $page = 1;
        if (isset($_GET['p'])) {
            $page = ($_GET['p'] === 'all') ? 'all' : (int)$_GET['p'];
        }
        $tpl_vars['page'] = $page;

        //ajax call, just display template
        $tpl = new geoTemplate(geoTemplate::ADMIN);

        $tpl->assign($tpl_vars);

        $tpl->display('regions/move.tpl');

        $admin->v()->setRendered(true);
    }

    public function update_region_move()
    {
        $db = DataAccess::getInstance();
        $region = geoRegion::getInstance();

        if (isset($_GET['browse']) && $_GET['browse']) {
            //nothing to do, just browsing...
            return true;
        }

        $regions = array();
        foreach ($_POST['regions'] as $region_id) {
            $region_id = (int)$region_id;
            if ($region_id && !in_array($region_id, $regions)) {
                $regions[] = $region_id;
            }
        }

        if (!count($regions)) {
            geoAdmin::m("Error: No regions selected to delete!", geoAdmin::ERROR);
            return false;
        }

        $parent = (int)$_GET['parent'];
        //FAILSAFE:  Make sure the parent specified matches the selected regions...
        //because if it does not that might indicate hitting refresh or something
        $wrong_parent_count = (int)$db->GetOne("SELECT COUNT(*) FROM " . geoTables::region . " WHERE
				`id` IN (" . implode(',', $regions) . ") AND `parent`!=?", array($parent));
        if ($wrong_parent_count > 0) {
            //oops!  Must have hit refresh or something...
            geoAdmin::m("Error: some of the selected regions are no longer in the same place,
					aborting the move proceedure.  This typically happens when you refresh the page
					directly after moving region(s).", geoAdmin::ERROR);
            return false;
        }

        $new_type = $_POST['to_type'];

        if ($new_type === 'top') {
            //easy, new parent is 0
            $new_parent = 0;
        } elseif ($new_type === 'id') {
            //use either ID or unique_name
            if (!strlen(trim($_POST['new_parent']))) {
                geoAdmin::m("Error: No parent region ID or unique name specified!", geoAdmin::ERROR);
                return false;
            }
            $new_parent = trim($_POST['new_parent']);
            if (!is_numeric($new_parent)) {
                //get ID from unique name
                $new_parent = (int)$db->GetOne("SELECT `id` FROM " . geoTables::region . " WHERE `unique_name`=?", array($new_parent));
                if (!$new_parent) {
                    geoAdmin::m("Error: Could not find the unique name specified!", geoAdmin::ERROR);
                    return false;
                }
            }
            $new_parent = $this->validRegionId($new_parent);
            if (!$new_parent) {
                geoAdmin::m("Error: Parent Region ID specified is not valid or could not be found!", geoAdmin::ERROR);
                return false;
            }

            //now figure out if we really should be moving here, make sure not
            //trying to move region into itself.

            if (in_array($new_parent, $regions)) {
                geoAdmin::m("ERROR:  Can't move a region into itself!");
                return false;
            }
            //now go through each level...

            $next_level = $db->Execute("SELECT `id` FROM " . geoTables::region . " WHERE `parent` IN (" . implode(',', $regions) . ")");
            $next_count = $next_level->RecordCount();
            while ($next_level && $next_count) {
                $this_level = array();
                foreach ($next_level as $row) {
                    if ($row['id'] == $new_parent) {
                        //oops found a child of the regions being moved that matches the new location,
                        //so can't do this!
                        geoAdmin::m("ERROR: Cannot move into a child of a selected region(s)!", geoAdmin::ERROR);
                        return false;
                    }
                    $this_level[] = $row['id'];
                }
                $next_level = $db->Execute("SELECT `id` FROM " . geoTables::region . " WHERE `parent` IN (" . implode(',', $this_level) . ")");
                $next_count = $next_level->RecordCount();
            }
            unset($next_level, $next_count, $this_level);
            //gets this far, it "should" be ok to move into this region... maybe...
        } elseif ($new_type === 'browse') {
            $new_parent = (int)$_POST['browse_region'];
            $new_parent = $this->validRegionId($new_parent);
            if ($new_parent !== (int)$_POST['browse_region']) {
                //while can browse to top, if the number specified was not 0 but
                //valid region ID returned 0 then it isn't the one they asked for...
                geoAdmin::m("Error: Parent Region ID specified is not valid or could not be found!", geoAdmin::ERROR);
                return false;
            }
        }


        if ($parent) {
            $parent_info = $region->getRegionInfo($parent);
            $old_level = (int)$parent_info['level'] + 1;
        } else {
            $old_level = 1;
        }

        if ($new_parent) {
            $new_parent_info = $region->getRegionInfo($new_parent);
            $new_level = (int)$new_parent_info['level'] + 1;
        } else {
            $new_level = 1;
        }

        if ($parent == $new_parent) {
            geoAdmin::m("That is the same place it started from!", geoAdmin::NOTICE);
            return false;
        }

        //first part is easy, just update the parents...
        $result = $db->Execute("UPDATE " . geoTables::region . " SET `parent`=? WHERE `id` IN (" . implode(',', $regions) . ")", array($new_parent));

        if ($new_level !== $level) {
            //oops, have to fix the levels on all...
            foreach ($regions as $region_id) {
                $this->fixRegionLevel($region_id, $new_level);
            }
        }
        geoAdmin::m("Regions moved successfully!  TODO:  Update regions saved for listings and users...");
        return true;
    }

    public function display_region_levels()
    {
        $admin = geoAdmin::getInstance();
        $db = DataAccess::getInstance();
        $region = geoRegion::getInstance();

        //the parent region to show..
        $parent = (int)$_GET['parent'];

        //verify it is valid...
        $parent = $this->validRegionId($parent);

        $tpl_vars = array();

        $tpl_vars['admin_msgs'] = $admin->message();

        $tpl_vars['levels'] = $region->getLevels();
        $tpl_vars['languages'] = $this->getLanguages();
        $tpl_vars['build_down'] = $db->get_site_setting('region_select_build_down');
        $helpLink = 'https://geodesicsolutions.org/wiki/admin_menu/geographic_setup/levels/start#region_type_information';
        $tpl_vars['tooltips'] = array(
            'level' => geoHTML::showTooltip('Level', 'Levels are automatically created as you add new regions to the Regions menu.'),
            'sample' => geoHTML::showTooltip('Sample', 'The sample regions below were pulled from the Regions menu based on the Region with the deepest number of levels you have entered.'),
            'type' => geoHTML::showTooltip('Region Sample', 'The region type set here will change how regions in this level are treated by other parts of the software.  See the <a href="' . $helpLink . '" onclick="window.open(this.href); return false;">user manual</a> for more information.'),
            'always_show' => geoHTML::showTooltip('Always Show', 'If checked, this level will appear as a disabled dropdown in front-side region selectors (such as in Listing Placement) before its parent regions are selected'),
            'labeled' => geoHTML::showTooltip('Labeled', 'If checked, these labels will appear in front-side region selectors (such as in Listing Placement)'),
        );


        $admin->setBodyTpl('regions/levels.tpl')
            ->v()->setBodyVar($tpl_vars);
    }

    public function update_region_levels()
    {
        $db = DataAccess::getInstance();

        $db->set_site_setting('region_select_build_down', ($_POST['region_select_build_down']) ? 1 : 0);

        //We get max level by looking at the levels for the regions in the system, we don't
        //care about levels beyond that since they would not effect anything
        $maxLevels = (int)$db->GetOne("SELECT `level` FROM " . geoTables::region . " ORDER BY level DESC");

        $region_types = (array)$_POST['region_type'];
        $use_labels = (array)$_POST['use_label'];
        $always_show = (array)$_POST['always_show'];

        $labels = (array)$_POST['label'];
        $languages = $this->getLanguages();

        $allTypes = $availableTypes = array (
            'country' => 'country',
            'state/province' => 'state/province',
            'city' => 'city',
            'other' => 'other',
        );

        //go level by level saving the details for each level
        for ($i = 1; $i <= $maxLevels; $i++) {
            $region_type = (in_array($region_types[$i], $allTypes)) ? $region_types[$i] : 'other';

            if ($region_type !== 'other') {
                if (!in_array($region_type, $availableTypes)) {
                    //make sure don't try to use country/state/province types on multiple levels
                    geoAdmin::m("Cannot use the region type " . $region_type . " for more than one level, using 'other' for level $i.", geoAdmin::NOTICE);
                    $region_type = 'other';
                }


                if ($region_type === 'country' && (!isset($availableTypes['city']) || !isset($availableTypes['state/province']))) {
                    //country is below city or state/province, that will mess things up!
                    geoAdmin::m("Cannot use the country region type in a lower level than city or state/province, using 'other' for level $i.", geoAdmin::NOTICE);
                    $region_type = 'other';
                }

                if ($region_type === 'state/province' && !isset($availableTypes['city'])) {
                    //state/province is below city level, that will mess things up!
                    geoAdmin::m("Cannot use the state or province region type in a lower level than city, using 'other' for level $i.", geoAdmin::NOTICE);
                    $region_type = 'other';
                }

                if ($region_type !== 'other') {
                    //remove it from list of "available" region types
                    unset($availableTypes[$region_type]);
                }
            }


            $use_label = (isset($use_labels[$i]) && $use_labels[$i] === 'yes') ? 'yes' : 'no';

            //see if label already set
            $label = $db->GetRow("SELECT * FROM " . geoTables::region_level . " WHERE `level`=$i");
            if ($label) {
                //update
                $result = $db->Execute("UPDATE " . geoTables::region_level . " SET `region_type`=?, `use_label`=? WHERE `level`=?", array($region_type, $use_label, $i));
                if (!$result) {
                    geoAdmin::m("DB Error when attempting to save region level settings, error reported: " . $db->ErrorMsg(), geoAdmin::ERROR);
                    return false;
                }
            } else {
                //this label not yet saved, so save it!
                $result = $db->Execute("INSERT INTO " . geoTables::region_level . " SET `level`=?, `region_type`=?, `use_label`=?", array($i, $region_type, $use_label));
                if (!$result) {
                    geoAdmin::m("DB Error when attempting to add new region level settings, error reported: " . $db->ErrorMsg(), geoAdmin::ERROR);
                    return false;
                }
            }

            if ($use_label) {
                //update label(s)!
                foreach ($languages as $lang) {
                    $label = trim($labels[$i][$lang['language_id']]);
                    $label = geoString::toDB($label);

                    //see if update or insert
                    if ((int)$db->GetOne("SELECT COUNT(*) FROM " . geoTables::region_level_labels . " WHERE `level`=? AND `language_id`=?", array($i, $lang['language_id']))) {
                        //already exists, update
                        $result = $db->Execute("UPDATE " . geoTables::region_level_labels . " SET `label`=? WHERE `level`=? AND `language_id`=?", array($label, $i, $lang['language_id']));
                        if (!$result) {
                            geoAdmin::m("DB Error when attempting update region level labels, error reported: " . $db->ErrorMsg(), geoAdmin::ERROR);
                            return false;
                        }
                    } else {
                        //not exist yet, insert
                        $result = $db->Execute("INSERT INTO " . geoTables::region_level_labels . " SET `level`=?, `language_id`=?, `label`=?", array($i, $lang['language_id'], $label));
                        if (!$result) {
                            geoAdmin::m("DB Error when attempting to add new region level label, error reported: " . $db->ErrorMsg(), geoAdmin::ERROR);
                            return false;
                        }
                    }
                }
            }

            $setting = (isset($always_show[$i]) && $always_show[$i] === 'yes') ? 'yes' : 'no';
            $result = $db->Execute("UPDATE " . geoTables::region_level . " SET `always_show` = ? WHERE `level` = ?", array($setting, $i));
            if (!$result) {
                geoAdmin::m("DB Error setting 'always show.' Message: " . $db->ErrorMsg(), geoAdmin::ERROR);
                return false;
            }
        }
        return true;
    }

    /**
     * Recursively "fixes" the levels of the specified region ID and all "child"
     * regions, according to the new level specified.
     *
     * @param int $region_id
     * @param int $level
     */
    public function fixRegionLevel($region_id, $level)
    {
        $db = DataAccess::getInstance();

        //first off, go through any children and update their levels...
        $regions = $db->Execute("SELECT `id` FROM " . geoTables::region . " WHERE `parent`=?", array($region_id));
        foreach ($regions as $regionInfo) {
            $this->fixRegionLevel($regionInfo['id'], ($level + 1));
        }

        //after taken care of all children, fix level for this one
        $db->Execute("UPDATE " . geoTables::region . " SET `level`=? WHERE `id`=?", array((int)$level, (int)$region_id));
    }

    public function ajaxError()
    {
        $tpl = new geoTemplate(geoTemplate::ADMIN);

        $tpl->assign('admin_msgs', geoAdmin::m());
        $tpl->display('regions/ajaxError.tpl');

        geoView::getInstance()->setRendered(true);
    }

    public function validRegionId($region_id)
    {
        $region_id = (int)$region_id;
        if (!$region_id) {
            //region ID 0, don't verify
            return $region_id;
        }

        $db = DataAccess::getInstance();

        $count = $db->GetOne("SELECT COUNT(*) FROM " . geoTables::region . " WHERE `id`=?", array($region_id));
        if ($count) {
            return $region_id;
        }
        //not found!
        geoAdmin::m("Could not find the parent region specified!  Showing the top level 1 regions.", geoAdmin::NOTICE);
        return 0;
    }

    /**
     * Function that cleans up a unique string to be suitable for use as
     * subdomain if need be.
     *
     * @param string $unique
     * @return string
     */
    public function cleanUnique($unique)
    {
        //make it lowercase and trim it
        $unique = trim(strtolower($unique));

        //replace spaces with hyphen
        $unique = preg_replace("/[\s]+/", '-', $unique);

        /**
         * Changes applied, as per official specification of valid hostnames
         * in RFC1123:
         * - Only a-z, 0-9, and hyphens allows (along with . for part seperation)
         * - each part cannot start or end with a -
         * - each part cannot be more than 63 chars
         */

        //can only contain a-z, 0-9, dots, and hyphens -
        $unique = preg_replace("/[^-a-z0-9.]+/", '', $unique);
        //cannot start or end with - or .
        $unique = trim($unique, '-.');

        //clean up each part of the subdomain
        $parts_raw = explode('.', $unique);
        $parts = array();
        foreach ($parts_raw as $part) {
            //make sure part is not more than 63 chars
            $part = substr($part, 0, 63);
            //cannot start or end in -
            $part = trim($part, '-');

            if (strlen($part)) {
                //there is something left after cleaning it, so add part to array of subdomain parts
                $parts[] = $part;
            }
        }
        //re-put-together parts
        $unique = implode('.', $parts);
        return $unique;
    }

    public function checkUnique($existing_id, $unique)
    {
        $db = DataAccess::getInstance();

        $existing = ($existing_id) ? "AND `id` != " . (int)$existing_id : '';

        return $db->GetOne("SELECT COUNT(*) FROM " . geoTables::region . " WHERE `unique_name`=? $existing", array($unique)) === '0';
    }


    /**
     * Get an array of languages from the DB
     *
     * @return array
     */
    public function getLanguages()
    {
        $db = DataAccess::getInstance();
        return $db->GetAll("SELECT `language_id`, `language` FROM " . geoTables::pages_languages_table . " ORDER BY `language_id`");
    }
}
