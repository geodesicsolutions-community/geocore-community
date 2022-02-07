<?php

//make sure errors are not shown.
error_reporting(0);

define('MIN_MYSQL', '4.1.0');
define('MIN_PHP', '7.4.0');

// un-comment to see errors..
//ini_set('display_errors','stdout');

//make sure we have enough memory to load the upgrade script, since it takes
//more than normal.
require_once('../ini_tools.php');
//make sure it is at least 128 megs (more needed on 64bit servers)
geoRaiseMemoryLimit('128M');

require_once('../config.default.php');
if (!defined('PHP5_DIR')) {
    define('PHP5_DIR', 'php5_classes/');
}

require_once CLASSES_DIR . PHP5_DIR . 'smarty/Smarty.class.php';

##  Do a few checks:
if (!is_writable(GEO_BASE_DIR . 'templates_c/')) {
    die('Upgrade error: you need to make the directory <strong>' . GEO_BASE_DIR . 'templates_c/</strong> writable (CHMOD 777).');
}

/**
 * Checks the min requirements, needs to work with PHP4 and not be encoded,
 * so that it can properly display requirement check page even when requirements
 * are not met.
 *
 * @package Update
 */
class geoReq
{
    /**
     * Set to true to pretend a test failed (to test the test display)
     *
     * @var boolean
     */
    private $pretendTestFailed = false;

    /**
     * Set to true to pretend the test failed and it needs to use the "safe" stand-alone page.
     *
     * This will make it show requirement_check_php_fail.tpl.php
     *
     * @var boolean
     */
    private $pretentTestFailedAndNoSmarty = false;

    /**
     * Replaces the template body with the requirement check.
     * Uses repuirement_check.php and requirement_check.html.
     */
    public function reqCheck()
    {
        $this->step_text = 'Requirement Check';

        $failed = '<span class="failed"><img src="images/no.gif" alt="no" title="no"></span>';
        $passed = '<span class="passed"><img src="images/yes.gif" alt="yes" title="yes"></span>';
        $not_needed = '---';
        if ($this->pretendTestFailed) {
            $not_tested_debug = 'Not Tested, since PHP version check failed above.';
        }
        $overall_fail = '';
        $overall_pass = '<p class="passed">All minimum requirements met.</p>';
        //license agreement
        $checkbox = '
            <p>
                <label>
                    <input type="checkbox" name="license" id="license" /> Yes, I have read and agree to the Software
                    <a
                        href="https://github.com/geodesicsolutions-community/geocore-community/blob/42e315b06b57a3a42b1352713258866fc691be70/LICENSE"
                        target="_blank"
                    >License Agreement</a>.
                </label>
            </p>';

        if (!$this->pretendTestFailed && defined('IAMDEVELOPER')) {
            $overall_fail .= $checkbox;
        }
        $overall_pass .= $checkbox;
        //back up agreement
        $overall_pass .= '<p><label><input type="checkbox" name="backup_agree" id="backup_agree" /> Yes, I have <strong>backed up</strong> the entire database and all files.</label></p>';
        if (!$this->pretendTestFailed && defined('IAMDEVELOPER')) {
            $overall_fail .= '<p><label><input type="checkbox" name="backup_agree" id="backup_agree" /> Yes, I have <strong>backed up</strong> the entire database and all files.</label></p>';
        }

        $overall_fail .= '
            <p class="body_txt1">
                <div style="text-align: left; background-color: #FFF; padding: 5px; border: 1px solid #EA1D25;">
                    <span class="failed">
                        IMPORTANT: As shown above, one or more of your server\'s minimum requirements have not been
                        met.  These requirements must be met in order to continue with this installation.
	                </span>
                </div>
            </p>
	        <p>
                Please refer to the
                <a href="https://geodesicsolutions.org/wiki/update/start" class="login_link" target="_blank">
                    GeoCore CE User Manual
                </a>.
            </p>';

        $continue_pass = '<input type="submit" name="continue" value="Continue >>" />';
        $continue_fail = '';
        if (!$this->pretendTestFailed && defined('IAMDEVELOPER')) {
            //allow to keep going even if req fail, if developer..
            $continue_fail = $continue_pass;
        }
        //req text
        $php_version_req = 'PHP Version ' . MIN_PHP . '+';
        $mysql_req = 'MySQL Version ' . MIN_MYSQL . '+';

        //start out with passed message, then replace if one of the requirements fail.
        $overall = $overall_pass;
        //start out with the continue as pass, then replace if one of the requirements fail.
        $continue = $continue_pass;

        ////PHP VERSION CHECK
        $version_num = phpversion();

        $php = ($this->pretendTestFailed) ? false : version_compare($version_num, MIN_PHP, '>=');
        $php_text = 'PHP ' . $version_num;

        if (!$php) {
            $overall = $overall_fail;
            $continue = $continue_fail;
        }

        //replace php version text
        $this->tplVars['php_version_text'] = $php_text;
        //replace php version check result
        $this->tplVars['php_version_result'] = ($php) ? $passed : $failed;
        //replace php version req text
        $this->tplVars['php_version_req'] = $php_version_req;


        $this->tplVars['body_tpl'] = 'requirement_check.tpl';

        ////MYSQL CHECK
        $mysql = $this->mysqlCheck($text, $php);
        //replace mysql text
        $this->tplVars['mysql_text'] = $text;
        //replace mysql check result
        $this->tplVars['mysql_result'] = ($mysql) ? $passed : $failed;
        //replace php version req text
        $this->tplVars['mysql_req'] = $mysql_req;
        if (!$mysql) {
            $overall = $overall_fail;
            $continue = $continue_fail;
        }

        if ($this->pretendTestFailed) {
            //we are pretending PHP is failing, in order to re-generate the
            //requirement check php file..

            //mysql
            $this->tplVars['mysql_text'] = $not_tested_debug;
            $this->tplVars['mysql_result'] = $not_needed;
        }

        //replace overall text
        $this->tplVars['overall_result'] = $overall;
        //replace continue button yo.
        $this->tplVars['continue'] = $continue;

        //developer force version form
        if (defined('IAMDEVELOPER') && !$this->pretendTestFailed) {
            $developer = '<p>DEVELOPER FEATURE: Force upgrade to version: <input type="text" name="force_version" value="7.4.4" /><br /><input type="submit" value="Force Version >>" /></p>';
        } else {
            $developer = '';
        }
        $this->tplVars['developer_force_version'] = $developer;
    }
    /**
     * Takes the template, does tag substitution, and echos it.
     */
    public function display_page()
    {
        //replace the upgrade step with the text.
        $this->tplVars['upgrade_step'] = $this->step_text;
        //replace the header text
        $this->tplVars['head'] = $this->head_text;

        if ($this->pretentTestFailedAndNoSmarty || version_compare(phpversion(), MIN_PHP, '<')) {
            //does not meet PHP requrements, use dummy requirements page
            require 'templates/requirement_check_php_fail.tpl.php';
            return;
        }

        $tpl = new Smarty();
        $tpl->compile_dir = GEO_BASE_DIR . 'templates_c';
        $tpl->template_dir = GEO_BASE_DIR . 'upgrade/templates';

        //clear templates_c for sites that have weird timestamps, so it uses latest
        //update templates freshly compiled
        $tpl->clearCompiledTemplate();

        $tpl->assign($this->tplVars);

        $tpl->display('index.tpl');
    }

    public function mysqlCheck(&$text, $php_check)
    {
        if (!function_exists('mysql_connect') && !function_exists('mysqli_connect')) {
            //mysql not even installed.
            $text .= 'MySQL not installed, or not configured to work properly with PHP.';
            return false;
        }
        @include('../config.default.php');
        if ($php_check && isset($db_host) && $db_host != 'your_database_hostname' && strlen($db_host)) {
            //if config.php is already set up, attempt to get server version.
            //adodb should be included by now.
            include_once(CLASSES_DIR . 'adodb/adodb.inc.php');
            @$db =& ADONewConnection($db_type);

            @$db->Connect($db_host, $db_username, $db_password, $database);
            $info = $db->ServerInfo();
            if (is_array($info)) {
                $mysql_version = $info['version'];
                if (strlen($mysql_version)) {
                    $version_comp = version_compare($mysql_version, MIN_MYSQL);

                    $text .= 'MySQL ' . $mysql_version;

                    if ($version_comp == -1) {
                        //mysql is a less version.
                        return false;
                    } else {
                        return true;
                    }
                }
            }
        }
        $reason = '(database connection settings not configured in config.php) - Version will be checked at the db connection step.';
        if (!$php_check) {
            //not checked, since if before PHP 5, the mysql check will cause a fatal
            //syntax error.
            $reason = '(Not checked since PHP requirement failed)';
        }
        $text = 'MySQL - Version not known ' . $reason;
        //version not known, but mysql is at least installed, so proceed.
        return true;
    }
}

if (isset($_GET['resetProgress']) && $_GET['resetProgress']) {
    //reset the update progress, can be used when a previous update was not "finished"
    //resulting in error.
    require_once 'updateFactory.php';

    $upgrade = new geoUpdateFactory();
    $upgrade->removeUpgradeTables();
    //clear templates_c, but don't bother with main cache
    $upgrade->clearCache();
    //send them back to main page
    header('Location: index.php');
    exit;
}

if ($_GET['run'] == 'show_upgrades' && !isset($_POST['license'])) {
    die('You must agree to the License Agreement to proceed with the upgrade. Please <a href="index.php">go back</a>, read the License Agreement, and click the appropriate checkbox before continuing.');
} elseif ($_GET['run'] == 'show_upgrades' && !isset($_POST['backup_agree'])) {
    die('You must create a site backup to proceed with the upgrade. Once you have created a backup, please <a href="index.php">go back</a>, and click the appropriate checkbox before continuing.');
} elseif (isset($_GET['force_version']) && defined('IAMDEVELOPER')) {
    require_once 'updateFactory.php';

    $upgrade = new geoUpdateFactory();
    if ($upgrade->updateCurrentVersion($_GET['force_version'])) {
        echo "updated version to " . $_GET['force_version'] . "<br><a href=\"index.php\">Back to Upgrade Page</a>";
    }
} elseif (!isset($_GET['run'])) {
    // Do prereq check - note that this class works in PHP4 and without Ioncube or Zend...

    $checks = new geoReq();
    $checks->reqCheck();
    $checks->display_page();
} elseif ($_GET['run'] == 'show_log') {
    //output the log, don't do any processing to avoid
    //the log database being changed...
    require_once 'updateFactory.php';

    $upgrade = new geoUpdateFactory();

    $upgrade->unSerialize();
    $upgrade->showLog();
    $upgrade->display_page();
} else {
    // Run the upgrade(s)
    require_once 'updateFactory.php';

    $upgrade = new geoUpdateFactory();

    $result = $upgrade->factory();
    //var_dump($result);

    //show the results page
    $upgrade->show_results();
    $upgrade->display_page();
}
