<?php

//session/destroy.php
##########GIT Build Data##########
##
## File Changed In GIT Commit:
## 
##    6.0.7-2-gc953682
##
##################################

if (!defined('IN_GEO_API')) {
    exit('No access.');
}

/*
 * Simple api call, destroys (or "logs out") a given session.  No validation needed, just kill it.
 */

$session_id = $args['session_id'];

if (strlen($session_id) != 32) {
    return $this->failure('Error: session_id not valid, it should be 32 char length.');
}

$this->session->closeSession($session_id);
return true;
