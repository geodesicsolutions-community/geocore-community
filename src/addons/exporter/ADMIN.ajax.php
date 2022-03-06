<?php

// addons/exporter/ADMIN.ajax.php


if (class_exists('admin_AJAX') or die()) {
}

class addon_exporter_ADMIN_ajax extends admin_AJAX
{
    const SETTINGS_TABLE = '`geodesic_addon_exporter_settings`';

    public function saveSettings()
    {
        //save settings or something
        $data = array ();

        $save_name = trim($_POST['save_name']);

        if (!strlen($save_name)) {
            $data['error'] = 'Save name required to save export settings.';
            return $this->_return($data);
        }
        $force_save = (isset($_POST['force_save']) && $_POST['force_save']);
        $settings_exist = $this->_saveExists($save_name);
        if (!$force_save && $settings_exist) {
            //show confirm box
            $tpl = new geoTemplate(geoTemplate::ADDON, 'exporter');

            $tpl->assign('save_name', $save_name);

            $data['name_exists'] = $tpl->fetch('addon/exporter/admin/confirm_overwrite.tpl');
            return $this->_return($data);
        }

        //settings to save are pretty much the post values.
        $settings = $_POST;

        //but minus a few things we don't need to save.
        $dontSave = array ('auto_save', 'save_name', 'exportType',
            'force_save', 'delete_settings', 'filename');
        foreach ($dontSave as $nosave) {
            unset($settings[$nosave]);
        }

        //for now filename and copy_to not implemented
        $filename = $copy_to = '';

        $filename = preg_replace('/[^a-zA-Z0-9_]*/', '', $_POST['filename']);

        $export_type = $_POST['exportType'];
        //make sure it's valid
        if (!in_array($export_type, array ('xml','csv'))) {
            $data['error'] = 'Invalid export type, must be XML or CSV.';
            return $this->_return($data);
        }

        //created time
        $created = geoUtil::time();

        //insert into DB
        $db = DataAccess::getInstance();

        if ($force_save && $settings_exist) {
            $sql = "UPDATE " . self::SETTINGS_TABLE . " SET `filename`=?, `copy_to`=?,
				`export_type`=?, `settings`=?, `last_updated`=? WHERE `name`=?";

            $query_data = array(
                geoString::toDB($filename),
                geoString::toDB($copy_to),
                $export_type,
                geoString::toDB(serialize($settings)),
                $created,
                geoString::toDB($save_name)
            );
            $db->Execute($sql, $query_data);
            $data['message'] = 'Export settings updated!';
        } elseif (!$settings_exist) {
            $sql = "INSERT INTO " . self::SETTINGS_TABLE . " SET `name`=?, `filename`=?, `copy_to`=?,
				`export_type`=?, `settings`=?, `created`=?, `last_updated`=?";
            $query_data = array(
                geoString::toDB($save_name),
                geoString::toDB($filename),
                geoString::toDB($copy_to),
                $export_type,
                geoString::toDB(serialize($settings)),
                $created,
                $created
            );
            $db->Execute($sql, $query_data);
            $data['message'] = 'Export settings saved!';
        }
        $data['load_table'] = $this->_getLoadTable();


        return $this->_return($data);
    }

    public function loadSettings()
    {
        $data = array();
        $name = trim($_POST['name']);

        if (!$name) {
            $data['error'] = 'Name required to load settings.';
            return $this->_return($data);
        }
        $db = DataAccess::getInstance();
        $row = $db->GetRow("SELECT * FROM " . self::SETTINGS_TABLE . " WHERE
			`name`=?", array(geoString::toDB($name)));

        if (!$row) {
            $data['error'] = 'Error retrieving settings, refresh page and try again.';
            return $this->_return($data);
        }

        $settings = unserialize(geoString::fromDB($row['settings']));
        //make it use single-dimensional array
        $serialized = array();
        $this->_serialize($settings, $serialized, '');
        $settings = $serialized;

        $settings['save_name'] = geoString::fromDB($row['name']);
        $settings['filename'] = geoString::fromDB($row['filename']);
        $settings['copy_to'] = geoString::fromDB($row['copy_to']);
        $settings['exportType'] = $row['export_type'];

        $data['settings'] = $settings;
        return $this->_return($data);
    }

    public function deleteSettings()
    {
        $data = array();

        $delete_settings = $_POST['delete_settings'];

        if (!$delete_settings || !is_array($delete_settings)) {
            $data['error'] = 'No settings selected, nothing to delete!';
            return $this->_return($data);
        }

        $db = DataAccess::getInstance();
        foreach ($delete_settings as $setting) {
            $db->Execute("DELETE FROM " . self::SETTINGS_TABLE . " WHERE `name`=? LIMIT 1", array(geoString::toDB($setting)));
        }
        $data['message'] = 'Settings were deleted.';
        $data['load_table'] = $this->_getLoadTable();
        return $this->_return($data);
    }

    private function _getLoadTable()
    {
        $tpl = new geoTemplate(geoTemplate::ADDON, 'exporter');
        $tpl->assign('loadSettings', DataAccess::getInstance()->GetAll("SELECT * FROM " . self::SETTINGS_TABLE . " ORDER BY `last_updated`"));
        return $tpl->fetch('addon/exporter/admin/load_settings_table.tpl');
    }

    private function _saveExists($save_name)
    {
        $db = DataAccess::getInstance();

        $sql = "SELECT count(*) FROM " . self::SETTINGS_TABLE . " WHERE `name`=?";
        $count = (int)$db->GetOne($sql, array(geoString::toDB($save_name)));
        return $count > 0;
    }

    private function _serialize($data, &$serialized, $prefix = '')
    {
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                if (is_numeric($key)) {
                    //numeric array, don't serialize this one
                    $serialized[$prefix . '[]'] = $data;
                    break;
                }
                $pre = ($prefix) ? $prefix . '[' . $key . ']' : $key;
                $this->_serialize($value, $serialized, $pre);
            }
        } else {
            $serialized[$prefix] = $data;
        }
    }

    private function _return($data)
    {
        $this->jsonHeader();

        //echo '$_POST='.var_export($_POST,1);

        echo $this->encodeJSON($data);
    }
}
