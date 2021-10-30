<?php

//addons/debugger_log/util.php

# Log license activities Addon

class addon_debugger_log_util
{

    public $tags;

    public $_init = false;

    /**
     * Function called when the system generates an error, or debug message.
     * @param Array $error_data
     */
    function core_errorhandle($err_data)
    {
        if (!$this->_init) {
            //not started up yet, don't proceed
            return;
        }
        if (!$this->tags) {
            //nothing to log
            return false;
        }
        $message = $err_data['errstr'];
        $file = $err_data['errfile'];
        $log_this = false;
        $endTags = strpos($message, ':');
        $endTags = ($endTags === false) ? 0 : $endTags; //make sure compare is evaluated correctly
        if (is_array($this->tags['debug']) && strpos($message, 'DEBUG') !== false && count($this->tags['debug'])) {
            foreach ($this->tags['debug'] as $tag) {
                $loc = @strpos($message, strtoupper($tag)); //suppress error output, for some reason this causes error thrown sometimes
                if ($loc !== false && $loc < $endTags) {
                    //only log if the tag is turned on, and the tag happens before the first :
                    $log_this = true;
                    break;
                }
            }
        }
        if (!$log_this && is_array($this->tags['error']) && strpos($message, 'ERROR') !== false && count($this->tags['error'])) {
            foreach ($this->tags['error'] as $tag) {
                $loc = @strpos($message, strtoupper($tag)); //suppress error output, for some reason this causes error thrown sometimes
                if ($loc !== false && $loc < $endTags) {
                    //only log if the tag is turned on, and the tag happens before the first :
                    $log_this = true;
                    break;
                }
            }
        }
        if ($log_this) {
            //log the message
            $date = date('[F d, Y :: H:i:s] -- ');
            $line = $err_data['errline'];
            $line = ($line) ? "Line {$line} -- " : ' ';
            //add cookie info if present
            $line .= (isset($_COOKIE['debug_log'])) ? 'debug_log: ' . substr($_COOKIE['debug_log'], 0, 4) . ' -- ' : '';
            $line .= (isset($_SERVER['HTTPS'])) ? 'HTTPS=' . $_SERVER['HTTPS'] . ' -- ' : 'HTTPS=N/A -- ';
            $message = $date . "{$line} [ F: {$file} ] {$message} \n";
            //make sure $message does not close the comment.
            $message = str_replace('*/', '*[slash]', $message);
            $file = ADDON_DIR . 'debugger_log/log.php';
            file_put_contents($file, $message, FILE_APPEND);
        }
    }
}
