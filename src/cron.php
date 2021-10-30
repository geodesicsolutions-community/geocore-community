<?php

/**
 * This is the cron backend, a procedural "index" file (that should be accessed
 * directly) for running cron routines.
 *
 * This file is located at the base directory, and relies on {@link geoCron}.
 *
 * The cron backend gives the ability to load tasks 3 different ways:
 * - Default way - Heartbeat called using ajax call - The cron.php file is called using an ajax call, every time someone does a page load (like the close routine used to be called). This can be turned off on Enterprise versions 3.1 and up.
 * - Heartbeat run using cron job - requires setting up a cron job manually on the server side, that calls the cron.php using lynx or other command-line web browser.  See below for examples of how to do this.
 * - Specific task(s) run using cron job - ignores heartbeat schedule and runs whatever tasks are specified at the time the cron job is called.  Requires setting up a cron job or jobs manually for each task, and allows the admin to do things like archive listings every second monday of the month at 1AM, or whatever schedule they want, since they control when the cron job is scheduled.
 *
 * The cron heartbeat runs a cron task according to if it has been run in x amount of time since the
 * last time it was run, x given by the interval set for that task.
 *
 * Heartbeat can be run by accessing the url http://mylistingsite.com/cron.php?action=cron or in a cron job by using
 * - * * * * * lynx http://mylistingsite.com/cron.php?action=cron # Run heartbeat every minute
 *
 * Tasks can be run by passing the security cron key (seen in admin) using cron_key=CRON_KEY, and
 * also specifying which task or tasks by tasks=task_list+seperated+by+plusses.  Example of runnin
 * 2 tasks, task1 and task2:
 * - 5 * * * * lynx http://mylistingsite.com/cron.php?action=cron&cron_key=CRON_KEY&tasks=task1+task2 # runs task1 and task2 at 5 minutes past the hour, every hour
 *
 * More examples are given on the admin page "Admin Tools & Settings > BETA Tools > Cron Jobs" (Enterprise only,
 * 3.1 or higher)
 *
 * The beta part of it, is the ability to be able to call cron.php using php from the command line
 * or in cron job.  We are calling it beta for now, until it has been tested on more server configurations.
 *
 * To see syntax for calling cron.php directly using php on the command line, use ssh to call the following
 * command:
 * - php /path/to/cron.php --help
 *
 * This will display the info for using cron.php on the command line.  If you don't want to have to use
 * php to execute this, then chmod this file to be executable, and add something like this
 * to the first line (change to match location of php executable):
 * - #!/usr/bin/php
 *
 *
 * @package System
 * @since Version 3.1.0
 */

//keep from redirecting
if (!defined('IS_ROBOT')) {
    /**
     * Treat this like it is a robot, meaning no cookies sent, no redirectin, etc.
     *
     */
    define('IS_ROBOT', 1);
}
if (!defined('GEO_CRON_RUN')) {
    /**
     * Define this to be used by cron tasks, to make sure the task's file is
     * being called from this file, and not being called directly.
     *
     */
    define('GEO_CRON_RUN', 1);
}
/**
 * Make sure all the needed stuff is initialized.
 */
require('app_top.common.php');

/**
 * Needed for the {@link geoCron} to do stuff.
 */
require_once CLASSES_DIR . PHP5_DIR . 'Cron.class.php';

$key = 'heartbeat';
$cron = geoCron::getInstance();

$argv = false;
if (isset($_GET['tasks']) && strlen(trim($_GET['tasks'])) > 0 && isset($_GET['cron_key']) && strlen(trim($_GET['cron_key'])) > 0) {
    //get key and tasks from the request var
    $tasks = explode(' ', $_GET['tasks']);
    $key = $_GET['cron_key'];
} elseif (isset($_SERVER['argv']) && count($_SERVER['argv']) > 0) {
    //get key and tasks from the command line
    //first one will be the cron key, second+ will be tasks to run
    $argv = $_SERVER['argv'];
    if (count($argv) > 1) {
        $key = $argv[1];
        $tasks = array();
        for ($i = 2; $i < count($argv); $i++) {
            if (strpos($argv[$i], '=') === false && strpos($argv[$i], '-') !== 0) {
                //only if it has no = in it, and does not start with -, cuz if so, it's probably some setting used
                //by one of the tasks.
                $tasks[] = $argv[$i];
            }
        }
    }
}

//check the security of the key
$keyCheck = $db->get_site_setting('cron_key');
if (strlen(trim($keyCheck)) == 0) {
    $cron->resetKey(); //reset the key if it's not set yet
}
if ($key != 'heartbeat' && ($tasks[0] != 'heartbeat' || count($tasks) > 1)) {
    if (strlen(trim($keyCheck)) == 0 || $keyCheck !== $key) {
        //key is not valid, so only run a heartbeat, not what was requested.
        if ($argv && !(in_array('-h', $argv) || in_array('--help', $argv))) {
            echo 'Error: Cron Security Key is not specified or is invalid, defaulting to heartbeat task.
';
        }
        $key = 'heartbeat';
    }
}

if ($key == 'heartbeat') {
    //key wasn't specified, or tasks weren't specified, or key was
    //invalid, so only task to run is the heartbeat.
    $tasks = array('heartbeat');
}

//--- options

//display help message
if ($argv && ((count($argv) == 1 && strpos($argv[0], '=') === false) || in_array('-h', $argv) || in_array('--help', $argv))) {
    echo <<<verbose
NOTE: Calling cron.php from command line is still BETA, and 
needs to be thoroughly tested before relying on it using cron 
jobs.

Usage:	cron.php heartbeat [options]
	cron.php [cron_security_key] [task1] [task2] [options]
	cron.php --help

Heartbeat:
Runs tasks that need to be run according to the last time they
have been run, and the time interval they should be run at.

[options]:
-v	--verbose	Makes the tasks talkative about what they are doing.
				Note that some verbose messages may be formatted for
				viewing in a browser, so they may have HTML tags.
				This is normal behavior.
-h	--help		Displays this help message

Other options may be possible with 3rd party addons, all options
must start with - or -- or use "=", otherwise they may be 
treated as a task.

[cron_security_key]:
Can be viewed/changed in the admin on the page 

Site Setup > Cron Jobs

If the security key does not match, behavior will default to the
heartbeat task only.

[tasks]:
Any number of tasks can be specified, each seperated by a space.
The task name must match was is reported on the Cron Jobs page
in the admin (Enterprise edition only).


verbose;
    $tasks = array('heartbeat');
    //exit;     //do not exit, go ahead and run the heartbeat, just
                // in case this was supposed to be a heartbeat call.
}

//Verbose option
if ((isset($_GET['verbose']) && $key == $keyCheck) || ($argv && (in_array('verbose=1', $argv) || in_array('-v', $argv) || in_array('--verbose', $argv)))) {
    //turn on verbose data
    $cron->verbose = 1;
    $task_txt = (($tasks[0] == 'heartbeat') ? '' : 'task' . ((count($tasks) > 1) ? 's ' : ' ')) . implode(', ', $tasks);
    echo "<pre>\n" . date('[F d, Y :: H:i:s] -- ') . 'Running ' . $task_txt . ' with verbose output ON.

';
}

//run the tasks.
$cron->run($tasks);
//not calling app_bottom here (because it does other stuff we don't care about), but be sure to close the db connection
$db->Close();
