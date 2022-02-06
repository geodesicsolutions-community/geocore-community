<?php

/**
 * Display the requirement step
 */

function mysqlCheck(&$text, $phpCheck)
{
    if (!function_exists('mysql_connect') && !function_exists('mysqli_connect')) {
        //mysql not even installed.
        $text .= 'MySQL not installed, or not configured to work properly with PHP.';
        return false;
    }
    $adodbLocation = '../classes/adodb/adodb.inc.php';
    if (file_exists('../config.php')) {
        include '../config.php';
    }
    if (
        $phpCheck &&
        isset($db_host) &&
        $db_host !== 'your_database_hostname' &&
        strlen($db_host) &&
        file_exists($adodbLocation)
    ) {
        //if config.php is already set up, attempt to get mysql server version.
        include_once($adodbLocation);
        $db = ADONewConnection($db_type);

        $db->Connect($db_host, $db_username, $db_password, $database);
        $info = $db->ServerInfo();
        if (is_array($info)) {
            $mysqlVersion = $info['version'];
            if (strlen($mysqlVersion)) {
                $version_comp = version_compare($mysqlVersion, '4.1.0');

                $text .= 'MySQL ' . $mysqlVersion;

                return version_compare($mysqlVersion, '4.1.0') >= 0;
            }
        }
    }
    $reason = '(database connection settings not configured in config.php) - Version will be checked at the db
        connection step.';
    if (!$phpCheck) {
        //not checked, since if before PHP 5, the mysql check will cause a fatal
        //syntax error.
        $reason = '(Not checked since PHP requirement failed)';
    }
    $text = 'MySQL - Version not known ' . $reason;
    //version not known, but mysql is at least installed, so proceed.
    return true;
}

//replace with images eventually, instead of text...
$failed = '<span class="failed"><img src="images/no.gif" alt="no" title="no"></span>';
$passed = '<span class="passed"><img src="images/yes.gif" alt="yes" title="yes"></span>';

$overallPass = '<p class="passed">All minimum requirements met.</p>';
$overallPass .= '<p><label><input type="checkbox" name="license" id="license" /> I have read and agree to the License Agreement</label></p>';

$overallFail = '<p class="body_txt1"><div style="text-align: left; background-color: #FFF; padding: 5px; border: 1px
    solid #EA1D25;"><span class="failed">IMPORTANT: As shown above, one or more of your server\'s minimum requirements
    have not been met.  These requirements must be met in order to continue with this installation.
    </p>';

$continuePass = '<div id="submit_button">
<input type="submit" value="Continue" class="theButton" />
</div>';

$continueFail = '';
if (defined('IAMDEVELOPER')) {
    // allow to keep going even if req fail, if developer..
    $continueFail = $continuePass;
    $overallFail .= '<p><label><input type="checkbox" name="license" id="license" /> I have read and agree to the
        License Agreement</label></p>';
}
//start out with passed message, then replace if one of the requirements fail.
$overall = $overallPass;
//start out with the continue as pass, then replace if one of the requirements fail.
$continue = $continuePass;


////PHP VERSION CHECK
$phpVersionText = phpversion();
$php = version_compare($phpVersionText, "5.4.0") >= 0;
$phpVersionResult = $php ? $passed : $failed;

if (!$php) {
    //php failed, and the requirement is PHP 5.4 so show message about
    //ability to use Geo 3.1
    $overall = $overallFail;
    $continue = $continueFail;
}


////MYSQL CHECK
$mysql = mysqlCheck($mysqlText, $php);
$mysqlResult = $mysql ? $passed : $failed;
if (!$mysql) {
    $overall = $overallFail;
    $continue = $continueFail;
}

?>
<form action="index.php?step=config.php" method="post" id="req_form">

    <div style="border: 2px solid #1382B7; padding: 3px; background-color:#FFF;">
      <table cellpadding="2" cellspacing="2">
        <thead>
            <tr>
                <th class="heading1" colspan="3">Server Minimum Requirements Check</th>
            </tr>
            <tr>
                <th width="12%" class="heading2">Req&nbsp;Met?</th>
                <th width="30%" class="heading2">Requirement</th>
                <th class="heading2a">Your Server's Settings:</th>
            </tr>
        </thead>
        <tbody>
            <tr style="background-color: #FFF;">
                <td class="result"><?= $phpVersionResult ?></td>
                <td class="req">PHP Version 7.4.0+</td>
                <td class="setting"><?= $phpVersionText ?></td>
            </tr>
            <tr style="background-color: #FFF;">
                <td class="result"><?= $mysqlResult ?></td>
                <td class="req">MySQL Version 4.1.0+</td>
                <td class="setting"><?= $mysqlText ?></td>
            </tr>
        </tbody>
      </table>
    </div>
    <br />
    <?= $overall ?>
    <?= $continue ?>
</form>
