<?php

// admin_wysiwyg_config.php
##########GIT Build Data##########
##
## File Changed In GIT Commit:
## 
##    16.09.0-59-g1cb1d15
##
##################################

class wysiwyg_configuration
{
    //list of valid codemirror themes, UPDATE this when new versions of codemirror
    //are added
    public $codemirrorThemes = array (
            //'ambiance-mobile',//This one is not stand-alone it seems
            'ambiance',
            'blackboard',
            'cobalt',
            'eclipse',
            'elegant',
            'erlang-dark',
            'lesser-dark',
            'monokai',
            'neat',
            'night',
            'rubyblue',
            'solarized',
            'twilight',
            'vibrant-ink',
            'xq-dark',
        );
    function wysiwyg_configuration()
    {
        $this->admin_site = Singleton::getInstance('Admin_site');
    }

    function display_wysiwyg_general_config()
    {
        //get the instance of the db.
        $db = DataAccess::getInstance();
        $view = geoView::getInstance();

        $tpl_vars = array();

        $tpl_vars['admin_msgs'] = geoAdmin::m();

        $tpl_vars['tooltip'] = $this->admin_site->show_tooltip('EGAD', 1);
        $tpl_vars['use_admin_wysiwyg'] = $db->get_site_setting('use_admin_wysiwyg');
        $tpl_vars['wysiwyg_css_uri'] = $db->get_site_setting("wysiwyg_css_uri", true);



        //Codemirror stuff
        $tpl_vars['codemirrorTheme'] = $db->get_site_setting('codemirrorTheme');

        $tpl_vars['codemirrorThemes'] = $this->codemirrorThemes;

        $tpl_vars['codemirrorAutotab'] = $db->get_site_setting('codemirrorAutotab');
        $tpl_vars['codemirrorSearch'] = $db->get_site_setting('codemirrorSearch');

        $view->addJScript('js/admin_tinymce_config_tooltips.js')
            ->setBodyTpl('settings/editor.tpl')
            ->setBodyVar($tpl_vars);
        return false;
    }

    function update_wysiwyg_general_config()
    {
        //get the instance of the db.
        $db = DataAccess::getInstance();

        //if set to 0, set it to PHP false
        $wysiwyg = ($_POST['use_admin_wysiwyg']) ? $_POST['use_admin_wysiwyg'] : false;

        $db->set_site_setting('use_admin_wysiwyg', $wysiwyg);
        $db->set_site_setting('wysiwyg_css_uri', trim($_POST['wysiwyg_css_uri']));


        //settings for codemirror

        $theme = $_POST['codemirrorTheme'];
        if (!in_array($theme, $this->codemirrorThemes)) {
            //set false for default
            $theme = false;
        }
        $db->set_site_setting('codemirrorTheme', $theme);

        $codemirrorAutotab = (isset($_POST['codemirrorAutotab']) && $_POST['codemirrorAutotab']) ? 1 : false;
        $db->set_site_setting('codemirrorAutotab', $codemirrorAutotab);

        $codemirrorSearch = (isset($_POST['codemirrorSearch']) && $_POST['codemirrorSearch']) ? 1 : false;
        $db->set_site_setting('codemirrorSearch', $codemirrorSearch);

        return true;
    }
    /**
     * Gets header javascript text for given type.
     *
     * @param String $type either textManager, htmlModules, or templateCode
     * @return String The text to be inserted into header for the given textarea type.
     */
    public static function getHeaderText($type, $fullpage = false, $fileBased = false, $restoreDefault = false)
    {
        $db = DataAccess::getInstance();
        $text = '';
        $view = geoView::getInstance();
        //load prototype first


        if ($db->get_site_setting('use_admin_wysiwyg') == 'TinyMCE') {
            //let view class know to add stuff for WYSIWYG editor
            $view->editor = true;

            $tpl = new geoTemplate('admin');
            $tpl->assign('doc_base_url', dirname($db->get_site_setting('classifieds_url')) . '/');

            $tpl->assign('type', $type);
            $tpl->assign('fullpage', ($fullpage) ? 1 : 0);
            $content_css_parts = explode(',', $db->get_site_setting("wysiwyg_css_uri", true));
            $content_css = array();
            foreach ($content_css_parts as $filename) {
                $content_css[] = geoTemplate::getUrl('', $filename);
            }

            $tpl->assign('content_css', implode(',', $content_css));
            $tpl->assign('fileBased', $fileBased);
            $tpl->assign('restoreDefault', $restoreDefault);
            return $tpl->fetch('tinymce.tpl');
        }
        return '';
    }
}
