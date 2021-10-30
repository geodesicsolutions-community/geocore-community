<?php

//addons/debugger_log/admin.php

# Debugger Log Addon - for debugging/logging messages to file

class addon_debugger_log_admin extends addon_debugger_log_info
{
    var $tables;
    var $body;

    /**
     * Add to this array, to be able to turn on logging for that "keyword" in the admin page.  For instance, right now there is 'session' as one of them,
     * so in the admin, you see boxes to enable logging for Session.  (it upper-cases it in the admin to look pretty)
     *
     * @var array
     */
    var $types = array ('session', 'robot', 'transaction', 'recurring', 'sendmail');

    function __construct()
    {
        //constructor
        $this->body = '';
        if (!is_writable(ADDON_DIR . 'debugger_log/log.php')) {
            Notifications::addCheck(array ('addon_debugger_log_admin','getErrorMsg'));
            $this->body .= '<div style="text-align:left;" class="medium_error_font">LOGGING DISABLED:: The file "' . ADDON_DIR . 'debugger_log/log.php" is not found, or is not writable.  Please make sure the file exists, and that it is CHMOD 777.</div>';
        }
        if (geoCrypt::DEBUG) {
            //debug crypt is turned on, so allow logging crypt messages
            $this->types[] = 'crypt';
        }
    }
    function getErrorMsg()
    {
        $txt = '';
        if (!is_writable(ADDON_DIR . 'debugger_log/log.php')) {
            $txt = '<strong>Notice:</strong>  The <strong>Debugging Logger</strong> addon is enabled, but cannot be used because the file <strong>' . ADDON_DIR . 'debugger_log/log.php</strong> is not found, or is <strong>not writable</strong>.  If you wish to turn debug logging off, disable the addon.  Otherwise, please make sure the file exists, and that it is CHMOD 777.';
        }
        return $txt;
    }
    //function to initialize pages, to let the page loader know the pages exist.
    //this will only get run if the addon is installed and enabled.
    function init_pages()
    {
        //menu_page::addonAddPage($index, $parent, $title, $addon_name, $image, $type);
        //add to admin tools&settings
        menu_page::addonAddPage('addon_debugger_log_config', '', 'Debug Log Settings', 'debugger_log', $this->icon_image);
    }


    //display functions, to display the admin settings.
    function display_addon_debugger_log_config()
    {
        $db = true;
        include(GEO_BASE_DIR . 'get_common_vars.php');
        $err_tags = explode('|', $db->get_site_setting('addon_debugger_log_error_tags'));
        $debug_tags = explode('|', $db->get_site_setting('addon_debugger_log_debug_tags'));
        //see if session boxes should be checked.

        //Add new types to log here by adding to $this->types array
        $types = $this->types;

        $tmpl = '
	<div class="form-group">
		<label class="control-label col-xs-12 col-sm-5">(!TYPE_TITLE!) Logging:</label>
		<div class="col-xs-12 col-sm-6">
			<input type="checkbox" name="debug_(!TYPE!)" value="1"(!DEBUG_CHECKED!) /> Debug
			<br />
			<input type="checkbox" name="error_(!TYPE!)" value="1"(!ERROR_CHECKED!) /> Error
			</label>
		</div>
	</div>';

        $this->body .= '
<form action="" method="POST" class="form-horizontal">
<fieldset class="medium_font">
	<legend>Actions to be Logged</legend>';

        $search = array ('(!ROW!)','(!TYPE!)','(!TYPE_TITLE!)','(!DEBUG_CHECKED!)','(!ERROR_CHECKED!)');
        foreach ($types as $type) {
            $row = ($row == 1) ? 2 : 1;
            $debug_checked = (in_array(strtoupper($type), $debug_tags)) ? ' checked="checked"' : '';
            $err_checked = (in_array(strtoupper($type), $err_tags)) ? ' checked="checked"' : '';
            $replace = array ($row, $type, ucfirst($type), $debug_checked, $err_checked);
            $this->body .= str_replace($search, $replace, $tmpl);
        }
        $require_cookie_check = ($db->get_site_setting('addon_debugger_log_require_cookie')) ? 'checked="checked"' : '';
        $link_to_turn_on_script = str_replace($db->get_site_setting('classifieds_file_name'), '', $db->get_site_setting('classifieds_url')) . 'addons/debugger_log/logme.php';
        $this->body .= '
</fieldset>
<fieldset>
	<legend>Log Settings</legend>
	
	<div class="form-group">
		<label class="control-label col-xs-12 col-sm-5">
			Require debug_log cookie?<br />
			<span class="small_font">Can use stand-alone script linked below to turn on cookie.<br />This feature is designed to be used for session logging, and may prevent other types of logging like transaction logging.
			<br />
			<a href="' . $link_to_turn_on_script . '">' . $link_to_turn_on_script . '</a></span>
		</label>
		<div class="col-xs-12 col-sm-6">
			<input type="checkbox" name="require_cookie" value="1"' . $require_cookie_check . ' />
		</div>
	</div>
</fieldset>
<fieldset>
	<legend>Location of Log</legend>
	<div class="medium_font">
		All log messages are logged to the following file:<br />
		<strong>' . ADDON_DIR . 'debugger_log/log.php</strong>
	</div>
</fieldset>
<div style="text-align:center;"><input type="submit" name="auto_save" value="Save Settings" /></div>
</form>';
        //render the whole page.
        if (class_exists('geoView')) {
            //Note: don't need to display page when on 4.0...
            $view = geoView::getInstance();
            $view->addBody(geoAdmin::m() . $this->body);
        } else {
            $admin = Singleton::getInstance('adminPageAutoload');
            adminPageAutoload::display_page($admin->getUserMessages() . $this->body);
        }
    }

    function update_addon_debugger_log_config()
    {
        $db = true;
        include(GEO_BASE_DIR . 'get_common_vars.php');
        $err_tags = array();
        $debug_tags = array();

        //add additional types to $this->types
        $types = $this->types;
        foreach ($types as $type) {
            if (isset($_POST['debug_' . $type]) && $_POST['debug_' . $type]) {
                $debug_tags[] = strtoupper($type);
            }
            if (isset($_POST['error_' . $type]) && $_POST['error_' . $type]) {
                $err_tags[] = strtoupper($type);
            }
        }


        $debug_tags = (count($debug_tags)) ? implode('|', $debug_tags) : false;
        $err_tags = (count($err_tags)) ? implode('|', $err_tags) : false;
        $require_cookie = (isset($_POST['require_cookie']) && $_POST['require_cookie']) ? '1' : false;
        //save the settings.
        $db->set_site_setting('addon_debugger_log_debug_tags', $debug_tags);
        $db->set_site_setting('addon_debugger_log_error_tags', $err_tags);
        $db->set_site_setting('addon_debugger_log_require_cookie', $require_cookie);
        return true;
    }
}
