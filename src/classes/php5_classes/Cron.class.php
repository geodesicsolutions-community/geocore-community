<?php

//Cron.class.php
/**
 * This is the cron backend, for running cron routines in.
 *
 * This file works with {@link cron.php} to run cron tasks.  See that file
 * for more information on cron tasks.
 *
 * @package System
 * @since Version 3.1.0
 */




/**
 * This is the cron job backend, it handles all the communication and security
 * and all that.
 *
 * @package System
 * @since Version 3.1.0
 */
class geoCron
{

    /**
     * To be used for 2nd parameter in call to method {@link geoCron::set()}, in
     * order to indicate that the cron type is a "main" cron task.
     * @var string
     * @since Version 4.1.0
     */
    const TYPE_MAIN = 'main';

    /**
     * To be used for 2nd parameter in call to method {@link geoCron::set()}, in
     * order to indicate that the cron type is a "main" cron task.
     * @var string
     * @since Version 4.1.0
     */
    const TYPE_ADDON = 'addon';



    /**
     * Database object, can be used by cron tasks by using $this->db
     *
     * @var DataAccess
     */
    public $db;

    /**
     * Session object, can be used by cron tasks by using $this->session
     *
     * @var Session
     */
    public $session;

    /**
     * geoPC object, can be used by cron tasks by using $this->product_configuration
     *
     * @var geoPC
     */
    public $product_configuration;

    /**
     * Addon object, can be used by cron tasks by using $this->addon
     *
     * @var Addon
     */
    public $addon;

    /**
     * Used to store the task info about each task
     * @access private
     */
    public $tasks;

    /**
     * If tasks should be talkative or not, if false they should be quiet. If
     * true they should echo out info.
     *
     * @var boolean
     */
    public $verbose = 0;

    /**
     * The singlton instance of the geoCron object.
     * @var geoCron
     * @internal
     */
    private static $_instance;
    /**
     * Constructor, initializes all the class vars and loads the tasks.
     *
     * @return geoCron
     */
    protected function __construct()
    {
        $this->db = DataAccess::getInstance();
        $this->session = geoSession::getInstance();
        $this->product_configuration = geoPC::getInstance();
        $this->addon = geoAddon::getInstance();

        $this->load();
    }

    /**
     * Get an instance of the cron task.
     *
     * @return geoCron
     */
    public static function getInstance()
    {
        if (!isset(self::$_instance)) {
            $c = __class__;
            self::$_instance = new $c();
        }
        return self::$_instance;
    }

    /**
     * Loads the info for any tasks that can be run.
     *
     */
    public function load()
    {
        if (is_array($this->tasks)) {
            //already loaded
            return ;
        }
        $this->tasks = array();
        //get the task settings from the db
        $sql = 'SELECT * FROM `geodesic_cron`';
        $result = $this->db->Execute($sql);
        if (!$result) {
            $this->logSystem('DB Error, SQL: ' . $sql . ' Error: ' . $this->db->ErrorMsg(), __line__);
            return false;
        }
        while ($row = $result->FetchRow()) {
            if (isset($this->tasks[$row['task']])) {
                //already added.
                break;
            }
            if ($row['type'] == 'addon') {
                //this is addon task.  syntax for task name is ADDON-NAME:TASK
                $info = explode(':', $row['task']);
                if (count($info) != 2) {
                    //invalid specification
                    continue;
                }
                $location = ADDON_DIR . $info[0] . '/cron/' . $info[1] . '.php';
                if (!$this->addon->isEnabled($info[0])) {
                    //do not load a cron for an addon that is not enabled.
                    continue;
                }
            } else {
                $location = CRON_DIR . $row['task'] . '.php';
            }
            if (!file_exists($location)) {
                //do not load if file does not exist.
                continue;
            }
            $this->tasks[$row['task']] = array (
                'location' => $location,
                'last_run' => $row['last_run'],
                'interval' => $row['interval'],
                'type' => $row['type'],
                'run' => false
            );
        }
    }

    /**
     * Gets information about a specific task, mainly used for getting info in the
     * admin.
     *
     * @since 4.0.0 RC11
     * @param string $task task name to get info for
     * @return array|bool an associative array of info about the task, or false if task
     *  not found.
     */
    public function getTaskInfo($task)
    {
        $this->load();
        if (isset($this->tasks[$task])) {
            return $this->tasks[$task];
        }
        return false;
    }

    /**
     * Runs the specified tasks.  Special case task is heartbeat, which will
     * run tasks that need to be run if they havn't been run in a while.
     *
     * @param array $tasks
     */
    public function run($tasks)
    {
        //assumes the cron key has already been checked for security.
        if ($tasks[0] == 'heartbeat') {
            //special case, run the heartbeat, not individual tasks
            return $this->heartbeat();
        }

        foreach ($tasks as $task) {
            //make sure its a valid task.
            if (!isset($this->tasks[$task]) || $this->tasks[$task]['run']) {
                //the task is not valid, or has already been run once, so skip it.
                continue;
            }

            if (!$this->lockTask($task)) {
                //the task may already be running through another page load,
                //do not start the task again while it is still running.
                $this->logSystem("Task locked, skipping: $task", __line__);
                continue;
            }
            $this->logSystem("Running task: $task", __line__);

            //run the task by requiring it.
            if (require($this->tasks[$task]['location'])) {
                //task returned true, so set the last_run
                $this->touch($task);
            }
            $this->logSystem("Finished running task: $task", __line__);
            $this->lockTask($task, false); //unlock the task
        }
    }

    /**
     * Runs tasks on a given interval for each task, as set using {@link geoCron::set()}
     *
     */
    public function heartbeat()
    {
        $time = time();
        $run_tasks = array(); //array to keep track of tasks to run
        foreach ($this->tasks as $task => $vals) {
            if ($vals['run']) {
                continue;
            }

            if ($vals['interval'] != -1 && $time > ($vals['last_run'] + $vals['interval'])) {
                //time to run again,
                $run_tasks[] = $task;
            }
        }

        if (count($run_tasks)) {
            $this->run($run_tasks);
        }
    }

    /**
     * Adds or edits the given task and sets the repeat interval.  A task will
     * not be visible to the cron system until it has been added using this
     * method.
     *
     * @param string $task task name, following specific format: if type is main, the task
     *  is the filename without the .php, if type is addon the task is ADDON_NAME:TASK where TASK
     *  is the filename without the .php, and ADDON_NAME is the addon name (same as addon folder name)
     * @param string $type addon or main, if addon the file is located at addons/ADDON_NAME/cron/
     *  and if main, the task is located in classes/cron/
     * @param int $interval in seconds
     * @return boolean True if successful, false otherwise.
     */
    public function set($task, $type, $interval)
    {
        if (strlen($task) == 0) {
            //invalid input.
            return false;
        }
        if ($type != self::TYPE_MAIN && $type != self::TYPE_ADDON) {
            //invalid input
            return false;
        }
        $last_run = 0;
        if (isset($this->tasks[$task]['last_run'])) {
            $last_run = intval($this->tasks[$task]['last_run']);
        }
        $interval = intval($interval);
        $sql = 'REPLACE INTO `geodesic_cron` SET `task`=?, `type`=?, `last_run`=?, `interval`=?';
        $result = $this->db->Execute($sql, array($task, $type, $last_run, $interval));
        if ($result) {
            //also update local
            $this->tasks[$task]['interval'] = $interval;
        }
        return $result;
    }

    /**
     * Removes a task from the task system.
     *
     * @param string $task must be same as what was used for {@link geoCron::set()}
     * @return boolean result of removal of task from system.
     */
    public function rem($task)
    {
        $sql = 'DELETE FROM `geodesic_cron` WHERE `task`=? LIMIT 1';
        $result = $this->db->Execute($sql, array('' . $task));
        return $result;
    }

    /**
     * Sets the last time the task was run to the current time.
     *
     * @param string $task
     * @return boolean true if succeeds, false otherwise.
     */
    public function touch($task)
    {
        if (!isset($this->tasks[$task]) || strlen(trim($task)) == 0) {
            return false; //invalid input.
        }
        $time = time();
        $sql = 'UPDATE `geodesic_cron` SET `last_run`=? WHERE `task`=? LIMIT 1';
        $result = $this->db->Execute($sql, array($time, $task));
        return $result;
    }

    /**
     * Marks a task as currently being executed, so that the task is not
     * run multiple times at once.  This does not run the task, it only
     * "locks" the task.
     *
     * @param string $task
     * @param boolean $lock If true, will lock the task, if false, will unlock the task.  Defaults to
     *  true to lock the task.
     * @return boolean If $lock is true, but the task is already locked, then this returns
     *  false to indicate that the task is already being run.  Otherwise returns true.
     */
    public function lockTask($task, $lock = true)
    {
        if (!isset($this->tasks[$task]) || strlen(trim($task)) == 0) {
            return false;
        }
        if ($lock) {
            //prevent same task from being run multiple times in one heartbeat
            $this->tasks[$task]['run'] = true;
            //see if task is currently locked
            $sql = 'SELECT `running` FROM `geodesic_cron` WHERE `task`=?';
            $result = $this->db->Execute($sql, array($task));
            if (!$result) {
                $this->logSystem('DB Error when checking for lock on task.  Error returned: ' . $this->db->ErrorMsg(), __line__);
                return false;//error with sql query
            }
            $row = $result->FetchRow();
            //ensure that the task is not run again before it is finished
            //but do not blindly lock the task, and not run it again until
            //it is unlocked again.  Instead, if it is still locked after
            //30 minutes (or whatever time is set in the admin), then assume
            //the task failed and run the task anyways.
            $deadlock_time_limit = intval($this->db->get_site_setting('cron_deadlock_time_limit'));
            $deadlock_time_limit = ($deadlock_time_limit) ? $deadlock_time_limit : (60 * 30);//default to 30 minutes if not set
            $deadlock = time() - $deadlock_time_limit;

            if ($deadlock < $row['running']) {
                //this task is already locked.  Return false to indicate that it
                //should not be run right now.
                return false;
            }
        }

        //If it gets this far, then the task needs to be updated to lock or unlock it
        //depending on the $lock passed.
        $sql = 'UPDATE `geodesic_cron` SET `running`=? WHERE `task`=? LIMIT 1';
        //do not rely on geoUtil::time() for the lock, in case the shifted time is changed.
        $time = ($lock) ? time() : 0;
        $result = $this->db->Execute($sql, array ($time, $task));
        if (!$result) {
            $this->logSystem('DB Error when checking for lock on task.  Error returned: ' . $this->db->ErrorMsg(), __line__);
            if (!$lock) {
                //only return false if unlocking the task.  If query failed to lock the task,
                //do not return false because that will cause the task to possibly never get run.
                return false;
            }
        }

        return true;
    }

    /**
     * Resets the cron security key
     *
     * @param (optional)string $newKey if blank, a random key is generated.
     */
    public function resetKey($newKey = '')
    {
        if (strlen(trim($newKey)) == 0) {
            //generate random key
            $to = rand(10, 15); //num chars is random, between 10 and 15
            // define possible characters
            $possible = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ_-.";
            for ($i = 0; $i < $to; $i++) {
                $newKey .= substr($possible, mt_rand(0, strlen($possible) - 1), 1);
            }
        }
        $this->db->set_site_setting('cron_key', $newKey);
    }

    public function time()
    {
        //NOTE: this is used as an alias for geoUtil::time in a couple of actual cron tasks
        //DO NOT use this within this file to determine when to run the tasks themselves!
        return geoUtil::time();
    }

    /**
     * For use in cron jobs, to display a message.  This automatically checks if
     * verbose is turned on or not, and if so, it echos the message and the line number.
     *
     * @param string $message
     * @param int $line
     * @since 4.0.0
     */
    public function log($message, $line = 0)
    {
        if ($this->verbose) {
            echo "$line - $message\n";
        }
    }

    /**
     * For use in geoCron class directly, DO NOT use in cron jobs.  Use normal log() function
     * from inside actual cron jobs.  This works just like the log() function, except that
     * this will add some extra decoration to the log message to make it stand out from the
     * normal log messages.
     *
     * @param string $message
     * @param int $line
     * @since 4.0.0
     */
    private function logSystem($message, $line)
    {
        //add extra newline before, and extra newline after, and add some extra stuff
        //before the line number, so it stands out nicely.
        $line = "\n_______ Cron System :: $line";
        $this->log("$message\n", $line);
    }
}
