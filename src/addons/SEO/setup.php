<?php

# SEO Addon

require_once ADDON_DIR . 'SEO/info.php';

class addon_SEO_setup extends addon_SEO_info
{

    var $registry_id;
    var $tb_created;

    public function enable()
    {
        $admin = true;
        include GEO_BASE_DIR . 'get_common_vars.php';
        $admin->userNotice('NOTICE: Additional steps are required to start using search engine friendly URLs.  You will need to complete the <a href="?page=addon_SEO_main_config">SEO configuration wizard</a>.');
        return true;
    }

    function install()
    {

        //script to install a fresh copy.

        $this->CreateTable();

        //session its nothing at this point (starting stage)
        //$session = $this->getInstallSession();
        //$session->installation_type = 'fresh';


        $this->registry_id = 'install';
        $s = array('type' => 1, 'continue' => 0);
        $this->set('settings', $s);
        $this->save();

        //DIE(print_r($this->get('settings'),1));
        //get $db connection and $cron object - use get_common_vars.php to be forward compatible
        //see that file for documentation.
        $admin = true;
        include(GEO_BASE_DIR . 'get_common_vars.php');

        $this->common_tasks();

        //If it made it all the way, then the installation was a success...
        //no longer applicable?  since it is now part of a process, they will be informed of this then.
        //$admin->userSuccess('NOTICE: Make sure you update your .htaccess file or you will have 404 pages!');
        return true;
    }

    public function uninstall()
    {
        $this->DropTable();
        return true;
    }

    function simulateUpgrade()
    {
        $this->upgrade('1.0.1');
    }

    function upgrade($from_version = false)
    {
        //Get an instance of the geoAdmin object, so we can use it
        //to display messages.
        $db = $admin = true;
        include GEO_BASE_DIR . 'get_common_vars.php';
        //upgrade from the version passed in.
        switch ($from_version) {
            case '1.0.0':
                //break omitted on purpose for this case
            case '1.0.1':
                $this->CreateTable();

                $this->registry_id = 'install';
                $settings = array('type' => 2, 'continue' => 0, 'use_old_redirects' => 1);
                $this->set('settings', $settings);
                $this->save();

                $this->common_tasks();
                $filename = $db->get_site_setting('classifieds_file_name');
                $index = str_replace('.php', '', $filename);

                $this->resetUpgradeSettings();

                //$db->set_site_setting('addon.SEO.cat_url',$begin.'/listings/category(!ID!).htm'); //EXAMPLE
                //set rest of settings to match "old" way SEO worked.

                $admin->userNotice('The way the SEO works has changed, be sure to read the user manual for more information, and test to make sure links work correctly.  If you have any problems, you may need to adjust settings for the SEO addon or update your .htaccess file.');

                //break to keep from going down to default
                break;
            case '1.1.0':
                //Beta version, force stuff to be re-inited
                $this->uninstall();
                $this->install();
                geoAdmin::m('All SEO settings were reset, due to changes in how the SEO addon works.', geoAdmin::NOTICE);
                break;

            default:
                break;
        }

        //fix the orders of the url parts to remove duplicate orders
        $this->fixOrders();


        if ($this->fixRegexes()) {
            //regexes needed to be changed, until the .htaccess is re-generated, still use old URL's
            $reg = geoAddon::getRegistry('SEO', true);
            if ($reg) {
                $reg->useUnderscore = 1;
                $reg->save();
            }
            $admin->userNotice('Note: Titles in URL now use dashes - for word separators instead of underscore _.  Also a few non alpha-numeric characters
			are now allowed in the title.  You will need to re-generate the
			.htaccess file to take advantage of these changes.  Until you do, it will continue to use underscores for word separators.  Note that old
			URLs that use underscore, will now have a 301 redirect to new urls using - if the setting "Force SEO URLs" setting is enabled.');
        } else {
            $admin->userNotice('Note: make sure to re-generate the .htaccess file
					contents to take advantage of any improvements or changes to
					how it works in the new version.');
        }
        //add any new URL's
        $util = geoAddon::getUtil('SEO', true);
        if ($util) {
            //make it add any new URL's
            $util->RegisterSettings(false, true);
        }


        //if upgrade is successful, return true.
        return true;
    }
    public function fixOrders()
    {
        $db = 1;
        include GEO_BASE_DIR . 'get_common_vars.php';

        $orders = $db->GetAll("SELECT `addon_seo`, `val_complex` FROM `geodesic_addon_seo_registry` WHERE `index_key` = 'order'");

        foreach ($orders as $orderRaw) {
            if ($orderRaw['val_complex']) {
                $array = unserialize(geoString::fromDB($orderRaw['val_complex']));

                $max = 1;
                //figure out what the max order currently is
                foreach ($array as $order) {
                    if ($order > $max) {
                        $max = $order;
                    }
                }
                $max++;
                $new = array();
                $saveNeeded = false;
                foreach ($array as $key => $val) {
                    if (in_array($val, $new)) {
                        //this is a duplicate order, fix it
                        $val = $max;
                        $max++;
                        $saveNeeded = true;
                    }
                    $new[$key] = $val;
                }
                if ($saveNeeded) {
                    $this->saveRegArray('order', $orderRaw['addon_seo'], $new);
                }
            }
        }
    }

    public function fixRegexes()
    {
        $db = 1;
        include GEO_BASE_DIR . 'get_common_vars.php';

        $regexes = $db->GetAll("SELECT `addon_seo`, `val_complex` FROM `geodesic_addon_seo_registry` WHERE `index_key` = 'regex'");
        $old = '([a-zA-Z0-9_]+)';
        $old2 = '([-a-zA-Z0-9_]+)';
        $new = '([^./\\\\"\'?#]+)';
        $fixNeeded = false;

        foreach ($regexes as $regexRaw) {
            if ($regexRaw['val_complex']) {
                $array = unserialize(geoString::fromDB($regexRaw['val_complex']));
                $saveNeeded = false;
                //replace any that match old regex, with the new
                foreach ($array as $key => $regex) {
                    if ($regex == $old || $regex == $old2) {
                        $array[$key] = $new;
                        $fixNeeded = $saveNeeded = true;
                    }
                }

                if ($saveNeeded) {
                    $this->saveRegArray('regex', $regexRaw['addon_seo'], $array);
                }
            }
        }
        return $fixNeeded;
    }

    public function getRegArray($index_key, $url_name)
    {
        $db = 1;
        include GEO_BASE_DIR . 'get_common_vars.php';

        $reg_type = $db->GetRow("SELECT `val_complex` FROM `geodesic_addon_seo_registry` WHERE `index_key` = '$index_key' AND `addon_seo` = '$url_name'");
        if ($reg_type['val_complex']) {
            $array = unserialize(geoString::fromDB($reg_type['val_complex']));
            return $array;
        }
        return false;
    }

    public function saveRegArray($index_key, $url_name, $array)
    {
        $db = 1;
        include GEO_BASE_DIR . 'get_common_vars.php';

        $array = geoString::toDB(serialize($array));
        $db->Execute("UPDATE `geodesic_addon_seo_registry` SET `val_complex`=? WHERE `index_key` = '$index_key' AND `addon_seo` = '$url_name'", array ($array));
    }

    function resetUpgradeSettings()
    {
        require_once 'util.php';
        $seo = new addon_SEO_util();
        if ($seo) {
            $seo->resetUpgradeSettings();
        }
        return true;
    }

    function CreateTable()
    {
        if (!$this->tb_created) {
            $db = DataAccess::getInstance();
            $sql = "
			CREATE TABLE IF NOT EXISTS `geodesic_addon_seo_registry` (
			`index_key` varchar(255) NOT NULL,
			`addon_seo` varchar(128) NOT NULL,
			`val_string` varchar(255) NOT NULL,
			`val_text` text NOT NULL,
		 	`val_complex` text NOT NULL,
		 	KEY `index_key` (`index_key`),
			KEY `addon_seo` (`addon_seo`),
			KEY `val_string` (`val_string`)
				)";
            $db->Execute($sql);
            $this->tb_created = true;
        }
    }

    public function DropTable()
    {
        $db = DataAccess::getInstance();
        $sql = "DROP TABLE IF EXISTS `geodesic_addon_seo_registry`";
        $db->Execute($sql);
    }

    function common_tasks()
    {
        $this->CreateTable();
        require_once 'util.php';
        $seo = new addon_SEO_util();
        if ($seo) {
            $seo->RegisterSettings();
        }
        return true;
    }

    public static $registry = array();
    private static $_pending_changes = array();
    function initRegistry($optional_id = '')
    {
        if (!$this->registry_id && !$optional_id) {
            return false;
        }
        if (!$optional_id) {
            $optional_id = $this->registry_id;
        }
        if (isset(self::$registry[$optional_id]) && is_object(self::$registry[$optional_id])) {
            return;
        }
        self::$registry[$optional_id] = new geoRegistry();
        self::$registry[$optional_id]->setName('addon_seo');
        self::$registry[$optional_id]->setId($optional_id);
        self::$registry[$optional_id]->unSerialize();
    }
    function save()
    {
        foreach (self::$registry as $id => $reg) {
            if (is_object($reg) && self::$_pending_changes[$id]) {
                $reg->save();
                self::$_pending_changes[$id] = 0;
            }
        }
    }

    function get($setting, $optional_id = '')
    {
        if (!$this->registry_id && !$optional_id) {
            return false;
        }
        if (!$optional_id) {
            $optional_id = $this->registry_id;
        }
        $this->initRegistry($optional_id);
        return self::$registry[$optional_id]->get($setting);
    }
    function set($setting, $value, $optional_id = '')
    {
        if (!$this->registry_id && !$optional_id) {
            return false;
        }
        if (!$optional_id) {
            $optional_id = $this->registry_id;
        }
        $this->initRegistry($optional_id);
        self::$registry[$optional_id]->set($setting, $value);
        self::$_pending_changes[$optional_id] = 1;
    }
}
