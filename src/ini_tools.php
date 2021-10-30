<?php

/*
 * These tools are not complete yet.  This file is to create the means to easily change
 * ini settings, and in future versions, implement feature from fogbugz #507
 */

//not using classes for this part, since at the time this is run, the memory
//limit might be too small to do anything. (what use is a tool to raise the
// memory limit if it takes too much memory to run?)

/**
 * Raise the ini memory_limit to the specified value.  If the memory limit
 * is already above the specified limit, nothing is done.
 * @param String $to The new memory limit, using memory limit syntax.
 */
function geoRaiseMemoryLimit($to)
{
    //make sure we have enough memory to work with.
    if (function_exists('memory_get_usage')) {
        //if the function exists, chances are we are alowed to change the mem limit.

        //amounts for mb, gb, and kb.
        $kilobyte = 1024;
        $megabyte = 1048576;
        $gigabyte = 1073741824;


        $current_mem_limit = ini_get('memory_limit');
        $multiplyer = 1;

        //convert current memory limit to bytes.
        if (strpos($current_mem_limit, 'M') !== false) {
            //dealing with megabytes.
            $multiplyer = $megabyte;
        } elseif (strpos($current_mem_limit, 'K')) {
            //dealing with kb.
            $multiplyer = $kilobyte;
        } elseif (strpos($current_mem_limit, 'G')) {
            //you never know, this could be the future where gb limits are the norm...
            $multiplyer = $gigabyte;
        }
        $current_mem_limit = intval($current_mem_limit); //should strip off any M or K's'.
        $current_mem_limit = $current_mem_limit * $multiplyer; //convert to bytes

        //convert new memory limit to bytes.
        $multiplyer = 1;

        //convert current memory limit to bytes.
        if (strpos($to, 'M') !== false) {
            //dealing with megabytes.
            $multiplyer = $megabyte;
        } elseif (strpos($to, 'K')) {
            //dealing with kb.
            $multiplyer = $kilobyte;
        } elseif (strpos($to, 'G')) {
            //you never know, this could be the future where gb limits are the norm...
            $multiplyer = $gigabyte;
        }
        $to = intval($to); //should strip off any M or K's'.
        $to = $to * $multiplyer; //convert to bytes


        //now check to see if we think we have enough.
        if ($current_mem_limit < $to) {
            //it is less than 32 megabytes, so up the memory limit.
            //trigger_error('DEBUG UPGRADE INI: Probably not enough memory to proceed.  The current memory limit is '.$current_mem_limit.' Bytes.  Changing to '.$to.' bytes.');
            ini_set('memory_limit', $to);
        }
    }
}
/**
 * Raise PHP's maximum execution time to a set value, useful for running big upgrades on slow computers
 * Does nothing if new time is lower than current, or if it can't find the old value
 *
 * @param int $to the new max_execution_time, in seconds
 */
function geoRaiseExecutionTime($to)
{
    $from = ini_get('max_execution_time');
    if ($from && $from <= $to) {
        $result = ini_set('max_execution_time', $to);
        if ($result === false) {
            trigger_error('DEBUG UPGRADE INI: Failed to raise execution time');
        } else {
            trigger_error("DEBUG UPGRADE INI: Raised execution time from $result to $to");
        }
    } else {
        trigger_error("DEBUG UPGRADE INI: Did not raise execution time. Could not get old value, or old value greater than current");
    }
}
