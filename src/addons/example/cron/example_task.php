<?php

//addons/example/cron/example_task.php
/**
 * Optional file, file name can be anything. The cron task name
 * will be addon_name:file_name
 *
 * See contents of function {@link addon_example_setup::install()} for example of how
 * to add this task to the cron job heartbeat schedule.  Without adding this task
 * using {@link geoCron::set()} the task will not be able to be run from the cron system.
 *
 * This file will be included when the task is scheduled to run, and is expected
 * to return true if the task completed, or false if not.  If the file returns
 * false (or nothing), the last_run timestamp for this task will not be updated,
 * so the task will be run again on the next heartbeat (unless interval
 * is -1, which means the task will only run by calling it manually using a cron job)
 *
 * For example cron tasks, see the files in the folder "classes/cron/".
 *
 * When the task is run, this file is included inside of {@link geoCron::run()}, and will
 * be able to access any of the already initialized class vars, which are:
 * - $this->db - database object
 * - $this->session - session object
 * - $this->product_configuration - geoPC object
 * - $this->addon - addon object
 * - $this->verbose - boolean, if true, expecting to echo out information.
 *
 * See the {@link geoCron} class for more information.
 *
 * @author Geodesic Solutions, LLC
 * @package ExampleAddon
 */

/**************************************************************************
Addon Created by Geodesic Solutions, LLC
Copyright (c) 2001-2013 Geodesic Solutions, LLC
All rights reserved
http://geodesicsolutions.com
see license attached to distribution
**************************************************************************/

##########GIT Build Data##########
##
## File Changed In GIT Commit:
## ##    ccda4ac
##
##################################

# Example Addon

/**
 * Make sure that this file is being accessed from cron.php.  Since cron tasks are
 * prodedural in nature, these lines should be at the top of all cron task files
 * to ensure the file is not accessed directly by calling it from a browser.
 *
 * (normal files do not need this, since most normal files use classes, nothing
 *  happens if one is called directly in the browser)
 */
if (!defined('GEO_CRON_RUN')) {
    die('NO ACCESS');
}

//this cron task does not do anything, but if it did, it would do it here.

//lets "log" some info just to show how that is done.
$this->log('Currently inside the example addon\'s cron task.  This task does 
not do anything, except if verbose is turned on, it displays this message.', __line__);

//Don't forget to return true!  Or the cron task will run every single time, as
//returning anying except for true signals that the cron task did not finish
//successfully, so it will be run next time no matter what.
return true;
