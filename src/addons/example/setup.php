<?php

//addons/example/setup.php
/**
 * Optional file, used to specify custom routines to run in addition to
 * the built-in addon system management.
 *
 * Can be used for the routines Install/Uninstall, Upgrade, and Enable/Disable
 *
 * Remember to rename the class name, replacing "example" with
 * the folder name for your addon.
 *
 * @package ExampleAddon
 */


# Example Addon

/**
 * This class is not required. If it, and the function for a particular
 * routine exist, then that function will be called IN ADDITION TO the
 * automated routines of the addon framework.
 *
 * @package ExampleAddon
 */
class addon_example_setup
{

    /**
     * Optional, remove function if not needed.  This is run when an addon is installed.
     *
     * View the source to see an example that
     * sets up a dummy database table.
     *
     * @return bool True to continue installing the addon, False to halt
     *  installation
     */
    public function install()
    {
        //script to install a fresh copy.
        //for demonstration, this script sets up a dummy database table.

        //get $db connection, $addon object (to display messages), and $cron object - use get_common_vars.php to be forward compatible
        //see that file for documentation.
        $db = $cron = $admin = true;
        include(GEO_BASE_DIR . 'get_common_vars.php');


        //To avoid table name conflicts, make sure to prefix any tables with
        //the addon name.

        // if you do need to create more than one table then  just duplicate the next statement and add
        // more sql statement to the array $sql[] =

        $sql[] = "
		CREATE TABLE IF NOT EXISTS `geodesic_addon_example_data` (
			`example_id` int(11) NOT NULL default '0',
			`userID` int(11) NOT NULL default '0',
			`not_used` varchar(20) NOT NULL default ''
		)";

        //When there are addon fields to use, it is recommended to add default
        //fields to use to the table if you want it to be enabled by default.
        //The below query makes our field enabled and editable by default.
        $sql[] = "INSERT INTO " . geoTables::fields . " (`field_name`, `is_enabled`, `is_required`, `can_edit`, `field_type`, `text_length`)
			VALUES ('addon_example_widget', 1, 0, 1, 'other', 0)";

        foreach ($sql as $q) {
            $result = $db->Execute($q);
            if (!$result) {
                $fail[] = $db->ErrorMsg();
            }
        }
        if (!empty($fail)) {
            //query failed, display message and return false.
            foreach ($fail as $f) {
                $admin->userError('Database execution error, installation failed.' . $f);
            }
            return false;
        } else {
            //Normally, you do not need to give this much info, or any info at all, this is
            //just used to demonstrate the use of the userNotice, userError, and userSuccess methods.
            $admin->userNotice('Database tables created successfully.');
        }



        /*$result = $db->Execute($sql);
        if (!$result){
            //query failed, display message and return false.
            $admin->userError('Database execution error, installation failed.');
            return false;
        } */

        // Normally, you do not need to give this much info, or any info at all, this is
        //just used to demonstrate the use of the userNotice, userError, and userSuccess methods.
        $admin->userNotice('The table geodesic_addon_example_data created successfully.');


        //add a cron task, so that the file cron/example_task.php can be run as a cron task.

        $task = 'example:example_task';     //The task name.  Must be ADDON_NAME:TASK where the TASK is the
                                                //filename without the .php.  The task name cannot be more than
                                                //128 chars.

        $type = 'addon';    //2 types, addon or main.  Difference is that for addon type, the file is located
                            //in addons/ADDON_NAME/cron/ directory, and main type is located
                            //in classes/cron/ directory.

        $interval = 3600;   //interval is in seconds, in this case we set it to 1 hour.
                            //This is used when running the heartbeat cron, the
                            //heartbeat will see which tasks have not been run in the amount of time specified
                            //by the task's interval, and run the tasks that need to be run.
                            //You can also force it to only be run if specifically
                            //called in a manually set cron job, by setting the interval to -1.

        //now set the task, meaning we are adding it to the cron tasks that will be run.
        //Note that the task will only be run if        $task = 'geographic_navigation:geographic_navigation_task';     //The task name.  Must be ADDON_NAME:TASK where the TASK is the
        // the proper file exists, and the addon is enabled.
        //it is a good idea to also remove the task during the uninstall routine.
        $cron_add = $cron->set($task, $type, $interval);

        if (!$cron_add) {
            //failed to add task, display message and return false.
            $admin->userError('Cron task failed to be added.');
            return false;
        } else {
            //let user know cron task addition was successful
            $admin->userNotice('Cron task for example addon was added.');
        }
        //If it made it all the way, then the installation was a success...
        $admin->userSuccess('The example addon installation script completed.');
        return true;
    }

    /**
     * Optional, remove function if not needed.  This is run when an addon is un-installed.
     *
     * View the source to see an example that
     * removes the dummy database table that was
     * created in the example install routine.
     *
     * @return boolean True to continue un-installing the addon, False to halt
     *  un-install
     */
    public function uninstall()
    {
        //script to uninstall the example addon.

        //get $db connection, $admin object (to display messages), and $cron object - use get_common_vars.php to be forward compatible
        //see that file for documentation.
        $db = $cron = $admin = true;
        include(GEO_BASE_DIR . 'get_common_vars.php');

        // adding the statements for tables to be droped on the database
        $sql[] = 'DROP TABLE IF EXISTS `geodesic_addon_example_data`';

        //Good idea to remove field settings upon un-install so that the settings
        //get reset when addon is uninstalled
        $sql[] = "DELETE FROM " . geoTables::fields . " WHERE `field_name`='addon_example_widget'";

        foreach ($sql as $q) {
            $result = $db->Execute($q);
            if (!$result) {
                $fail[] = $db->ErrorMsg();
            }
        }
        if (!empty($fail)) {
            //query failed, display message and return false.
            foreach ($fail as $f) {
                $admin->userError('Database execution error, installation failed.' . $f);
            }
            return false;
        } else {
            //Normally, you do not need to give this much info, or any info at all, this is
            //just used to demonstrate the use of the userNotice, userError, and userSuccess methods.
            $admin->userNotice('Database tables created successfully.');
        }

        //Remove the cron task we added in the installation.

        $task = 'example:example_task';     //The task, which should be ADDON_NAME:TASK where TASK is the
                                                //filename without the .php.

        //remove the cron task
        $remove_task_result = $cron->rem($task);
        if (!$remove_task_result) {
            //failed to add task, display message and return false.
            $admin->userError('Error removing example cron task, un-install failed.');
            return false;
        } else {
            //let user know cron task addition was successful
            $admin->userNotice('Cron task for example addon successfully removed.');
        }
        //If it made it all the way, then the installation was a success...
        //Note that displaying a message is not necessary, this is used to demonstrate success messages.
        $admin->userSuccess('Addon un-install script completed.');
        return true;
    }

    /**
     * Optional, remove function if not needed.  This is run when an addon is upgraded.
     *
     * Note that the built-in addon upgrade routine will automatically
     * change the version number.
     *
     * @param string $from_version String of the old version, that is being
     *  upgraded from.  This is passed in by the addon system.
     * @return boolean True to continue upgrading the addon, False to halt
     *  the upgrade.
     */
    function upgrade($from_version = false)
    {
        //Get an instance of the geoAdmin object, so we can use it
        //to display messages, and get instance of geoCron object so
        //we can add new cron tasks to the system
        $admin = $cron = 1;
        include GEO_BASE_DIR . 'get_common_vars.php';


        //use this only if upgrade is compatible with any all older version
        //example of how to do the upgrade if upgrade needs a version lower than current version
        if ($from_version) {
            //this is assuming $from_version is  1.0
            $version = version_compare($from_version, "1.1", "<");

            if ($version) {
                //do upgrade if addon is lower than this current version
                $admin->userSuccess('Upgrade completed with no problems.');
            }
            return true;
        }

        //upgrade from the version passed in.
        switch ($from_version) {
            case '0.5':
                //do upgrade from 0.5 to current, 1.0.0
                $admin->userNotice('Upgrading from version 0.5, a version that never existed.');
                //do not stop here, go through all the upgrade scripts.
            case '1.0.0':
                $admin->userNotice('Running upgrade from version 1.0.0.  See the docs/ for documentation
					on all the new features available for addons to use.');

                //for more info on vars passed, see contents of install script
                $cron_add = $cron->set('example:example_task', 'addon', 3600);
                if (!$cron_add) {
                    //failed to add task, display message and return false.
                    $admin->userError('Cron task failed to be added, upgrade failed.');
                    return false;
                } else {
                    //let user know cron task addition was successful
                    $admin->userNotice('Cron task for example addon was added.');
                }

                //break to keep from going down to default
                break;
            case '1.1.0':
                $admin->userNotice('Running upgrade from version 1.1.0.  See the docs/ for documentation
					on all the new features available for addons to use.');

                break;
            default:
                $admin->userError('Error, version ' . $from_version . ' is not known.');
                break;
        }
        //note that the current database version is automatically handled
        //by the addon framework.  No need to update the version number in the
        //database.
        $admin->userSuccess('Upgrade completed with no problems.');
        //if upgrade is successful, return true.
        return true;
    }

    /**
     * Optional, remove function if not needed.  This is run when the
     * addon is enabled, in addition to the
     * normal stuff that is done by the addon back end.
     *
     * @return boolean True to finish enableing the addon, false to not
     *  enable the addon.
     */
    function enable()
    {
        //not required.

        //function to change status from disabled to enabled.
        //Typically, this function is not needed, as the addon framework does
        //the work automatically.

        //If you wanted, you could display notices, errors, or success messages like the install() function does.
        return true;
    }

    /**
     * Optional, remove function if not needed.  This is run when
     * the addon is disabled, in addition to the
     * normal stuff that is done by the addon back end.
     *
     * @return boolean True to finish disabling the addon, false to not
     *  disable the addon.
     */
    function disable()
    {
        //not required, unless enable function is used.

        //function to change status from enabled to disabled.
        //Typically, this function is not needed, as the addon framework does
        //the work automatically.

        //If you wanted, you could display notices, errors, or success messages like the install() function does.
        return true;
    }
}
