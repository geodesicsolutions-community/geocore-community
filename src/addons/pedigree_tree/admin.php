<?php

//addons/pedigree_tree/admin.php

# Pedigree Tree

require_once ADDON_DIR . 'pedigree_tree/info.php';

class addon_pedigree_tree_admin extends addon_pedigree_tree_info
{
    public function init_pages()
    {
        menu_page::addonAddPage('pedigreeTree', '', 'Settings', $this->name, 'fa-paw');
    }

    public function display_pedigreeTree()
    {
        $view = geoView::getInstance();
        $reg = geoAddon::getRegistry($this->name);

        $tpl_vars['maxGens'] = $reg->maxGens;
        $tpl_vars['maxReqGens'] = $reg->maxReqGens;
        $tpl_vars['allowUppercase'] = $reg->allowUppercase;

        $tpl_vars['adminMessages'] = geoAdmin::m();



        //get the different icon sets

        $tset = geoTemplate::whichTemplateSet('external', 'images', 'addon/pedigree_tree/bg_norm.gif');
        if ($tset) {
            $file = geoFile::getInstance(geoFile::TEMPLATES);
            $sets = $file->scandir("$tset/external/images/addon/pedigree_tree/icon_sets/", false, false, true);

            foreach ($sets as $set) {
                $tpl_vars['icon_sets'][$set] = array(
                    'sire' => "images/addon/pedigree_tree/icon_sets/$set/sire.gif",
                    'dam' => "images/addon/pedigree_tree/icon_sets/$set/dam.gif"
                );
            }

            $tpl_vars['iconSet'] = $reg->get('iconSet', 'none');
        }

        $view->setBodyTpl('admin/settings.tpl', $this->name)
            ->setBodyVar($tpl_vars);
    }

    public function update_pedigreeTree()
    {
        $maxGens = (int)$_POST['maxGens'];
        $maxReqGens = (int)$_POST['maxReqGens'];
        $iconSet = trim($_POST['iconSet']);
        $allowUppercase = (isset($_POST['allowUppercase']) && $_POST['allowUppercase']) ? 1 : false;

        if ($maxReqGens > $maxGens) {
            geoAdmin::m('Required number of generations cannot be larger than the maximum number of generations.');
            return false;
        }

        $tset = geoTemplate::whichTemplateSet('external', 'images', 'addon/pedigree_tree/bg_norm.gif');
        if ($tset) {
            $file = geoFile::getInstance(geoFile::TEMPLATES);
            $valid_sets = $file->scandir("$tset/external/images/addon/pedigree_tree/icon_sets/", false, false, true);
        }
        $valid_sets [] = 'none';

        if (!in_array($iconSet, $valid_sets)) {
            geoAdmin::m('Invalid icon set specified.', geoAdmin::ERROR);
            return false;
        }

        $reg = geoAddon::getRegistry($this->name);
        $reg->maxGens = $maxGens;
        $reg->maxReqGens = $maxReqGens;
        $reg->iconSet = $iconSet;
        $reg->allowUppercase = $allowUppercase;
        $reg->save();

        return true;
    }

    public function init_text($languageId)
    {
        $return = array
        (
            'placement_section_title' => array (
                'name' => 'Section Title',
                'desc' => 'Used during listing placement and listing edit process.',
                'type' => 'input',
                'default' => 'Pedigree Tree'
            ),
            'placement_section_desc' => array (
                'name' => 'Section Description',
                'desc' => 'Used during listing placement and listing edit process.',
                'type' => 'textarea',
                'default' => 'Please enter the pedigree tree information in the fields below.'
            ),
            'field_required' => array (
                'name' => 'Field Required error message',
                'desc' => 'Used during listing placement and listing edit process.',
                'type' => 'input',
                'default' => 'Required'
            ),
            'search_sire_label' => array (
                'name' => 'Search Sire label',
                'desc' => 'Used on advanced search page.',
                'type' => 'input',
                'default' => 'Sire:'
            ),
            'search_dam_label' => array (
                'name' => 'Search Dam label',
                'desc' => 'Used on advanced search page.',
                'type' => 'input',
                'default' => 'Dam:'
            ),
            'sire' => array (
                'name' => 'Sire',
                'desc' => '',
                'type' => 'input',
                'default' => 'Sire'
            ),
            'dam' => array (
                'name' => 'Dam',
                'desc' => '',
                'type' => 'input',
                'default' => 'Dam'
            ),
            'sires' => array (
                'name' => 'Sire\'s',
                'desc' => '',
                'type' => 'input',
                'default' => 'Sire\'s'
            ),
            'dams' => array (
                'name' => 'Dam\'s',
                'desc' => '',
                'type' => 'input',
                'default' => 'Dam\'s'
            ),
        );

        return $return;
    }
}
