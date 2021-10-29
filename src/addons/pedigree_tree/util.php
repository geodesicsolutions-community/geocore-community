<?php

//addons/pedigree_tree/util.php
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
## ##    16.05.0-24-g33979a7
##
##################################

# Pedigree Tree

require_once ADDON_DIR . 'pedigree_tree/info.php';

class addon_pedigree_tree_util extends addon_pedigree_tree_info
{
    //recursively cleans the pedigree tree.
    public function cleanTree($data)
    {
        //will be stored all lowercase (for easier searching)
        $name = $this->cleanName($data['name']);

        $data['name'] = $name;
        if (isset($data['sire'])) {
            $data['sire'] = $this->cleanTree($data['sire']);
        }
        if (isset($data['dam'])) {
            $data['dam'] = $this->cleanTree($data['dam']);
        }
        if (!$data['name'] && !$data['sire'] && !$data['dam']) {
            //this node is empty
            return null;
        }
        return $data;
    }

    public function getMaxGen($tree, $currentGen = 0, $maxGen = 0)
    {
        if (isset($tree['sire'])) {
            $maxGen = $this->getMaxGen($tree['sire'], $currentGen + 1, $maxGen);
        }
        if (isset($tree['dam'])) {
            $maxGen = $this->getMaxGen($tree['dam'], $currentGen + 1, $maxGen);
        }
        if ($tree['name'] && $currentGen > $maxGen) {
            //current gen is max
            $maxGen = $currentGen;
        }
        return $maxGen;
    }

    public function cleanName($name)
    {
        //will be cleaning up name to get rid of in-consistencies (for easier searching)
        $name = trim(geoString::specialCharsDecode($name));

        $reg = geoAddon::getRegistry($this->name);
        if (!$reg->allowUppercase) {
            //store all lowercase (for easier searching)
            $name = strtolower($name);
        }

        //get rid of non alpha-numeric, plus space and '
        $name = preg_replace('/[^a-zA-Z0-9_ \']*/', '', $name);

        //push through badword filter
        return geoFilter::badword($name);
    }

    public function checkRequired($data, $fieldName, $currentLevel = 0, $maxRequiredLevel = 3)
    {
        $required = true;

        if ($required && $currentLevel > 0 && !$data['name']) {
            $cart = geoCart::getInstance();
            $msgs = geoAddon::getText($this->auth_tag, $this->name);
            $cart->addError()
                ->addErrorMsg($fieldName, $msgs['field_required']);
        }
        $currentLevel++;
        if ($currentLevel <= $maxRequiredLevel) {
            $this->checkRequired($data['sire'], $fieldName . '[sire]', $currentLevel, $maxRequiredLevel);
            $this->checkRequired($data['dam'], $fieldName . '[dam]', $currentLevel, $maxRequiredLevel);
        }
    }

    public function getTreeFor($listingId)
    {
        $db = 1;
        include GEO_BASE_DIR . 'get_common_vars.php';

        $sql = "SELECT * FROM " . self::LISTING_TREE . " WHERE `listing_id`=? ORDER BY `generation`";
        $all = $db->GetAll($sql, array((int)$listingId));
        if (!$all) {
            return array();
        }
        $tree = array();
        $depth = 0;
        foreach ($all as $row) {
            $tree[$row['child']][$row['gender']] = $row['id'];
            $tree[$row['id']]['name'] = $row['name'];
            $depth = ($row['generation'] > $depth) ? (int)$row['generation'] : $depth;
        }
        $tree = $this->_popTree($tree);
        $tree['maxGen'] = $depth;
        return $tree;
    }

    private function _popTree($raw, $for = 0)
    {
        $return = array();
        if (isset($raw[$for]['sire'])) {
            $return['sire'] = $this->_popTree($raw, $raw[$for]['sire']);
        }
        if (isset($raw[$for]['dam'])) {
            $return['dam'] = $this->_popTree($raw, $raw[$for]['dam']);
        }
        if (isset($raw[$for]['name'])) {
            $return['name'] = $raw[$for]['name'];
        }
        return $return;
    }

    private $_setSql;

    public function setTreeFor($listingId, $pedigreeTree, $gender = 'sire', $childId = 0, $generation = 0)
    {
        $listingId = (int)$listingId;
        $gender = (in_array($gender, array('sire','dam'))) ? $gender : 'sire';
        $childId = (int)$childId;
        $generation = (int)$generation;
        if (!$listingId || !$pedigreeTree) {
            //nothing to do here, not enough info...
            return;
        }
        if (!$generation) {
            //first remove any existing tree for this listing, so we don't end up with dups
            $this->removeTreeFor($listingId);
        }

        $db = 1;
        require GEO_BASE_DIR . 'get_common_vars.php';

        $reg = geoAddon::getRegistry($this->name);

        if (!isset($this->_setSql)) {
            //prepare sql for faster stuff
            $this->_setSql = $db->Prepare("INSERT INTO " . self::LISTING_TREE . " (`listing_id`, `name`, `gender`, `generation`, `child`)
				VALUES (?, ?, ?, ?, ?)");
        }

        if (isset($pedigreeTree['name']) && $generation) {
            //if not recursive call, then it won't have a name to save...
            $qData = array(
                $listingId,
                $pedigreeTree['name'] . '',
                $gender,
                $generation,
                $childId
            );
            $result = $db->Execute($this->_setSql, $qData);
            if (!$result) {
                //error in db?
                trigger_error('ERROR SQL: db error: ' . $db->ErrorMsg());
                return;
            }
            $childId = (int)$db->Insert_Id();
        }
        $generation++;
        if (isset($pedigreeTree['sire'])) {
            $this->setTreeFor($listingId, $pedigreeTree['sire'], 'sire', $childId, $generation);
        }
        if (isset($pedigreeTree['dam'])) {
            $this->setTreeFor($listingId, $pedigreeTree['dam'], 'dam', $childId, $generation);
        }
    }

    public function removeTreeFor($listingId)
    {
        $db = 1;
        include GEO_BASE_DIR . 'get_common_vars.php';

        $db->Execute("DELETE FROM " . self::LISTING_TREE . " WHERE `listing_id`=?", array((int)$listingId));
    }

    public function core_Search_classifieds_search_form($vars)
    {
        $searchClass = $vars['this'] ? $vars['this'] : false;

        if ($searchClass && $_GET['c']) {
            //special case: skip the rest of this for now, because the category-specific AJAX thingy will do it later
            return;
        }

        $cat = $searchClass ? (int)$searchClass->site_category : (int)$vars['category_id'];
        $groupId = 0;

        $userId = (int)geoSession::getInstance()->getUserId();
        if ($userId) {
            $user = geoUser::getUser($userId);
            if ($user) {
                $groupId = (int)$user->group_id;
            }
        }

        $fields = geoFields::getInstance($groupId, $cat);

        $locations = $fields->getDisplayLocationFields('search_fields');
        if (!$locations['addon_pedigree_tree']) {
            //don't display in search
            return;
        }
        //This should be returning an array of arrays, that way the addon can
        //be adding multiple search criteria.
        $return = array();

        //lets add 2 "entries" to be added to the search page, sire and dam
        $msgs = geoAddon::getText($this->auth_tag, $this->name);

        $return [] = array (
            'label' => $msgs['search_sire_label'],
            'data' => '<input type="text" name="b[pedigree_sire]" value="" />',
            'skipBreakAfter' => true,
        );
        $return [] = array (
            'label' => $msgs['search_dam_label'],
            'data' => '<input type="text" name="b[pedigree_dam]" value="" />',
        );

        return $return;
    }

    public function core_Search_classifieds_generate_query($vars)
    {
        $sire = (isset($_REQUEST['b']['pedigree_sire'])) ? trim($_REQUEST['b']['pedigree_sire']) : '';
        $dam = (isset($_REQUEST['b']['pedigree_dam'])) ? trim($_REQUEST['b']['pedigree_dam']) : '';

        //clean it
        $sire = $this->cleanName($sire);
        $dam = $this->cleanName($dam);

        if (!$sire && !$dam) {
            return;
        }

        $db = DataAccess::getInstance();
        //To manipulate the query, get the query being used for searches, like so:
        $query = $db->getTableSelect(DataAccess::SELECT_SEARCH);

        $lTree = self::LISTING_TREE;

        if ($sire) {
            $wheres[] = "($lTree.`gender`='sire' AND $lTree.`name` LIKE " . $db->qstr($sire) . ")";
        }
        if ($dam) {
            $wheres[] = "($lTree.`gender`='dam' AND $lTree.`name` LIKE " . $db->qstr($dam) . ")";
        }
        $sql = "SELECT * FROM " . self::LISTING_TREE . " WHERE 
			$lTree.`listing_id` = " . geoTables::classifieds_table . ".`id` AND " . implode(' AND ', $wheres);

        $query->where("EXISTS ($sql)");
    }


    public function core_geoFields_getDefaultFields()
    {
        $return = array (
            'addon_pedigree_tree' => array (
                'label' => 'Pedigree Tree',
                'type' => 'other',
                'skipData' => array ('is_required')
            ),
            //you can add as many fields as you want.
        );
        return $return;
    }
}
