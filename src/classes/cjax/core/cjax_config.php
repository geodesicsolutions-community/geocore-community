<?php

/*
    CJAX FRAMEWORK
    ajax made easy with cjax

    -- DO NOT REMOVE THIS --
    -- AUTHOR COPYRIGHT MUST REMAIN INTACT --
    CJAX FRAMEWORK 2.1
    Written by: Carlos Galindo
    Website: @WEBSITE@
    Email: cjxxi@msn.com
    Date: @DATE@
    Last Updated:  05/22/2008
*/
#define('JSDIR','http://yoursite.com/cjax'); //Enter the url where CJAX is located


require_once 'classes/std.class.php';
require_once 'classes/cjax.class.php';
require_once 'classes/extension.class.php';
if (!defined('CJAX_CORE_DIR')) {
    define('CJAX_CORE_DIR', dirname(__file__));
}
if (!defined('CJAX_DIR')) {
    define('CJAX_DIR', dirname(CJAX_CORE_DIR));
}
if (defined('JSDIR')) {
    $JS_DIR = JSDIR;
} elseif (defined('IN_SAMPLES')) {
    $JS_DIR = '';
    $dir = $_SERVER['PHP_SELF'];
    while (strrpos($dir, 'cjax/')  !== false || $dir == '') {
            $dir = substr($dir, 0, strlen($dir) - 1);
    }

    CJAX_FRAMEWORK::path('http://' . $_SERVER['HTTP_HOST'] . $dir);
} else {
    $JS_DIR  = "cjax/core/js/";
    if (is_dir('core/js/')) {
        $JS_DIR  = "core/js/";
    }
    if (CJAX_FRAMEWORK::OS() == 'WIN') {
        //$JS_DIR  = "cjax\\core\\js\\";
    }

    if (!file_exists($JS_DIR)) {
        $array = debug_backtrace();
        $file = $array[0]['file'];
        $caller = $array[1]['file'];
        $caller_pieces = array();
        $local_pieces = array();
        if (isset($caller) && isset($file) && !empty($caller) && !empty($file)) {
            if (CJAX_FRAMEWORK::OS() == 'WIN') {
                $local_pieces = explode('\\', $file);
                $caller_pieces = explode('\\', $caller);
            } else {
                $pieces = explode("/", $file);
                $caller = explode("/", $caller_pieces);
            }
            $caller_pieces = count($caller_pieces);
            $loca_count = count($local_pieces);
            $i = 0;
            $e = 0;
            $dots = '';
            $last = '';
            foreach ($local_pieces as $find_dir) {
                $dir = '';
                $i++;

                if (isset($local_pieces[$i]) && $local_pieces[$i] == $caller_pieces[$i] && $pieces[$i] != '' && $caller_pieces[$i] != '') {
                    $e++;
                    $dir = $local_pieces[$i];
                    $last = $dir;
                }
                if ($i == $loca_count) {
                    $dir_deep = $loca_count[$e + 1] . '/';
                    $dir = $last;
                }
                //$find_dir = $caller[]
            }
             $dif = $i - $e - 3;


            if ($dif > 0) {
                while ($dif != 0) {
                    $dif--;
                    $dots .= "../";
                }
            } elseif ($dif == 0 || $dir_deep == 'cjax') {
                $dots .= "../";
                $dir_deep = '';
            }
            //die(DIF.$dif);

            $JS_DIR = $dots . $dir_deep . "cjax/core/js/";
        }
    }
}

if (!class_exists('CJAX')) {
    abstract class CJAX extends CJAX_FRAMEWORK
    {

        function __construct()
        {
            return self::initciate();
        }
        /**
         * get an instance of the CJAX object (alias)
         *
         * @return CJAX OBJECT
         */
        function init($echo = false)
        {
            return self::initciate();
        }

        /**
         * get an instance of the CJAX object (alias)
         *
         * @return CJAX OBJECT
         */
        function getInstance()
        {
            return self::initciate();
        }

        /**
         * An alias to get an instance of the CJAX object
         *
         * @return $CJAX
         */
        function start()
        {
            return self::initciate();
        }

        /**
         * get an instance of the CJAX object
         * with singleton patterm
         * @return CJAX OBJECT
         */
        public static function initciate()
        {
            return singleton::getInstance('CJAX_FRAMEWORK');
        }
    }
}
    $CJAX = CJAX::initciate();
    $CJAX->JSdir($JS_DIR);
    $CJAX->version = $engine['version'];
