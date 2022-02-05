<?php

require_once(CLASSES_DIR . PHP5_DIR . 'Cron.class.php');

class AdminCronManage
{
    function update_cron_config()
    {
        $db = true;
        include(GEO_BASE_DIR . 'get_common_vars.php');
        if (PHP5_DIR) {
            $menu_loader = geoAdmin::getInstance();
        } else {
            $menu_loader =& geoAdmin::getInstance();
        }
        //clean inputs
        $cron_disable_heartbeat = (isset($_POST['cron_method']) && $_POST['cron_method'] == 'cron') ? 1 : false;
        $cron_deadlock = (isset($_POST['cron_deadlock_time_limit']) && $_POST['cron_deadlock_time_limit'] > 5) ? intval($_POST['cron_deadlock_time_limit']) : 1800;

        //save settings
        $db->set_site_setting('cron_disable_heartbeat', $cron_disable_heartbeat);
        $db->set_site_setting('cron_deadlock_time_limit', $cron_deadlock);
        $cron = geoCron::getInstance();
        $cron->resetKey(trim($_POST['cron_key']));
        $menu_loader->userSuccess('Settings Saved.'); //success
        if ($cron_disable_heartbeat) {
            $menu_loader->userNotice('Warning: Auto heartbeat disabled!  This requires a manual cron job to be run on the server, otherwise listings may never close!');
        }
        if (!(isset($_POST['cron_deadlock_time_limit']) && $_POST['cron_deadlock_time_limit'] > 5)) {
            $menu_loader->userNotice('Setting "Time before lock removed" needs to be at least 6 seconds.');
        }
    }

    function display_cron_config()
    {
        $db = true;
        $product_configuration = true;
        include(GEO_BASE_DIR . 'get_common_vars.php');
        if (PHP5_DIR) {
            $menu_loader = geoAdmin::getInstance();
        } else {
            $menu_loader =& geoAdmin::getInstance();
        }
        $cron_key = geoString::specialChars($db->get_site_setting('cron_key'));
        $cron_deadlock = $db->get_site_setting('cron_deadlock_time_limit');
        if (strlen(trim($cron_key)) == 0) {
            $cron = geoCron::getInstance();
            $cron->resetKey();
            $cron_key = geoString::specialChars($db->get_site_setting('cron_key'));
        }
        $cron_url = substr($db->get_site_setting('classifieds_url'), 0, strpos($db->get_site_setting('classifieds_url'), $db->get_site_setting('classifieds_file_name')));
        $cron_url .= 'cron.php?action=cron&cron_key=' . $cron_key;
        $cron_command = GEO_BASE_DIR . 'cron.php --help';
        $html = $menu_loader->getUserMessages() . "
<div class=\"page_note_error\"><strong>Warning</strong>: Changing settings on this page can have drastic effects if your server
is not configured correctly.  It is important that you <strong>consult the user manual</strong>, so that you may fully understand what is happening, before changing any settings on this page.</div>

<fieldset>
	<legend>Cron Settings</legend>
	<div class='form-group'>
	<form method=\"post\" class=\"form-horizontal form-label-left\" action=\"\">

	<div class='form-group'>
		<label class='control-label col-md-5 col-sm-5 col-xs-12'>Run &quot;Heartbeat&quot;: </label>
		<div class='col-md-6 col-sm-6 col-xs-12'>
			<input type=\"radio\" name=\"cron_method\" " . (($db->get_site_setting('cron_disable_heartbeat')) ? '' : 'checked="true" ') . "value=\"ajax\" /> Automatically when page loads (Default)<br />
			<input type=\"radio\" name=\"cron_method\" " . (($db->get_site_setting('cron_disable_heartbeat')) ? 'checked="true" ' : '') . "value=\"cron\" /> Manually with Cron Job (Requires Manual Server-Side Cron Jobs)
		</div>
	</div>

	<div class='form-group'>
		<label class='control-label col-md-5 col-sm-5 col-xs-12'>Cron Security Key: </label>
		<div class='col-md-6 col-sm-6 col-xs-12'>
			<input type=\"text\" name=\"cron_key\" class=\"form-control col-md-7 col-xs-12\" value=\"{$cron_key}\" />
		</div>
	</div>

	<div class='form-group'>
		<label class='control-label col-md-5 col-sm-5 col-xs-12'>Time before lock removed:<br /><span class='small_font'>(Deadlock prevention)</span> </label>
		<div class='col-md-6 col-sm-6 col-xs-12 input-group'>
			<input type=\"text\" name=\"cron_deadlock_time_limit\" class=\"form-control col-md-7 col-xs-12\" value=\"{$cron_deadlock}\" /><div class='input-group-addon'>Seconds</div>
		</div>
	</div>

	<div style=\"text-align: center\"><input type=\"submit\" name=\"auto_save\" value=\"Save\" /></div>
	</form>
	</div>
</fieldset>
<fieldset>
	<legend>Cron Task Information</legend>
	<div>
<div class=\"col_hdr\">Available Tasks/Heartbeat Schedule Info</div>

		" . $this->getStats() . "
<br /><br />


<div class=\"col_hdr\" style=\"margin-bottom: 10px;\">Cron Job Examples</div>
<div class=\"row_color1\">
	<div class=\"leftColumn\">Manually Run Heartbeat every minute</div><div class=\"rightColumn\">The heartbeat runs tasks according to the built-in intervals listed above.</div>
	<div class=\"medium_font\" style=\"clear: both; white-space: pre; border: thin solid black; padding: 3px; margin: 10px; text-align: left;\">*\t*\t*\t*\t*\tlynx $cron_url</div>
	<div class=\"clearColumn\"></div>
</div>
<div class=\"row_color2\">
	<div class=\"leftColumn\">Run the made-up tasks task1 and task2<br />every Monday at 1AM:</div><div class=\"rightColumn\" style=\"text-align:left;\">
		Ignores heartbeat schedule, and manually runs only the<br />
		task(s) specified. Each task is separated by a plus(+),<br />
		if running more than 1 task at once.  Each task can be<br />
		manually run like this independently of the other tasks.<br />
		This is a good way to manually run each task using<br />
		your own schedule instead of using the built-in heartbeat<br />
		schedule.
	</div>
	<div class=\"medium_font\" style=\"clear: both;white-space: pre; border: thin solid black; padding: 3px; margin: 10px; text-align: left;\">0\t1\t*\t*\t1\tlynx $cron_url&tasks=<strong>task1+task2</strong>
	</div>
	<div class=\"clearColumn\"></div>
</div>
<div class=\"row_color1\">
	<div class=\"leftColumn\">SSH Command Line Options</div>
	<div class=\"rightColumn\" style=\"text-align: left;\">To see configuration options for running<br />
		cron.php using php instead of a command line browser, ssh<br />
		to your site and run the following command.
	</div>

	<div class=\"medium_font\" style=\"clear: both;white-space: pre; border: thin solid black; padding: 3px; margin: 10px; text-align: left;\">php $cron_command</div>
	<div class=\"clearColumn\"></div>
</div>
</div>
</fieldset>";
        geoAdmin::display_page($html);
    }

    function getStats()
    {
        $db = true;
        include GEO_BASE_DIR . 'get_common_vars.php';

        $cron = geoCron::getInstance();
        $cron->verbose = false;
        $cron->load();
        $html = '';//<div class="leftColumn">Cron Tasks:</div><div class="rightColumn">Task Info</div>';
        $row = 'row_color1';

        $cron_key = geoString::specialChars($db->get_site_setting('cron_key'));

        $cron_url = substr($db->get_site_setting('classifieds_url'), 0, strpos($db->get_site_setting('classifieds_url'), $db->get_site_setting('classifieds_file_name')));
        $cron_url .= 'cron.php?action=cron&cron_key=' . $cron_key . '&verbose=1&tasks=';

        foreach ($cron->tasks as $task => $data) {
            $verbose_link = $cron_url . $task;

            $html .= '
			<div class="' . $row . '">
				<div class="leftColumn">
					' . $task . '<br />
					<span class=\"small_font\">[ <a href="' . $verbose_link . '" target="_blank">Test Run</a> ]</span>
				</div>
				<div class="rightColumn" style="white-space:pre; text-align:left;">' . "<em>Last Run</em>: " . $this->niceTime($data['last_run'], false) .
                '<br /><em>Run Every</em>: ' . $this->niceTime($data['interval']) .
                '<br /><em>Next Scheduled Run</em>: ' . $this->niceTime($data['interval'] + $data['last_run'], false) .
                '</div>
				<div class="clearColumn"></div>
			</div>
';
            $row = ($row == 'row_color1') ? 'row_color2' : 'row_color1';
        }
        return $html;//.'</div>';
    }

    /**
     * prints the time in a pretty format.
     *
     * @param int $time
     */
    function niceTime($time, $interval = true)
    {
        $time = intval($time);
        if (!$interval) {
            //is most likely a date
            if ($time < 2) {
                //never has been run..
                return 'Never';
            }
            return date('g:i:sa F d, Y', $time);
        }
        //An interval
        $year = 60 * 60 * 24 * 365;
        $day = 60 * 60 * 24;
        $hour = 60 * 60;
        $minute = 60;
        $html = array();
        if ($time == -1) {
            //never run in heartbeat
            return '-1 (Can only run manually)';
        }
        if ($time >= $year) {
            $years = floor($time / $year);
            $time = $time - ($years * $year);
            $html[] = $years . ' year' . (($years > 1) ? 's' : '');
        }
        if ($time >= $day) {
            $days = floor($time / $day);
            $time = $time - ($days * $day);
            $html[] = $days . ' day' . (($days > 1) ? 's' : '');
        }
        if ($time >= $hour) {
            $hours = floor($time / $hour);
            $time = $time - ($hours * $hour);
            $html[] = $hours . ' hour' . (($hours > 1) ? 's' : '');
        }
        if ($time >= $minute) {
            $minutes = floor($time / $minute);
            $time = $time - ($minutes * $minute);
            $html[] = $minutes . ' minute' . (($minutes > 1) ? 's' : '');
        }
        if ($time > 0) {
            $html [] = $time . ' second' . (($time != 1) ? 's' : '');
        }
        return implode(' ', $html);
    }
}
