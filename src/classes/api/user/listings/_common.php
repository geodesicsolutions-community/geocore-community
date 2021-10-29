<?php

//_common.php
##########GIT Build Data##########
##
## File Changed In GIT Commit:
## 
##    7.5.3-36-gea36ae7
##
##################################

if (!defined('IN_GEO_API')) {
    exit('No access.');
}

/**
 * This is a "common" file, used by different "user listing api's" so as to avoid
 * duplicate code where possible.
 */

if (!isset($query)) {
    //this shouldn't be possible since filename starts with underscore, but
    //just in case...  we need the query object to already exist.
    exit('No access.');
}

//pagination, default to 50 limit and page 1
$limit = (isset($args['limit']) && $args['limit'] >= 0) ? (int)$args['limit'] : 50;
$page = (isset($args['page']) && $args['page'] >= 1) ? (int)$args['page'] : 1;

if ($page > 1 && $limit > 0) {
    $query->limit((($page - 1) * $limit), $limit);
} elseif ($limit > 0) {
    $query->limit($limit);
}

//put together the results.
$return = array();
if (isset($args['format_results']) && $args['format_results']) {
    //push listing results through geoBrowse class
    $browse = new geoBrowse();

    //common text, hard-coded, client can change if desired
    if (isset($args['text']) && is_array($args['text'])) {
        $text = $args['text'];
    } else {
        $text = array(
            'item_type' => array (
                'classified' => 'classified',
                'auction' => 'auction',
            ),
            'business_type' => array(
                1 => 'ind',
                2 => 'bus',
            ),
            'time_left' => array(
                'weeks' => 'weeks',
                'days' => 'days',
                'hours' => 'hours',
                'minutes' => 'minutes',
                'seconds' => 'seconds',
                'closed' => 'closed',
            )
        );
    }

    $rows = $this->db->Execute('' . $query);
    $return['listings'] = array();

    foreach ($rows as $row) {
        $return['listings'][] = $browse->commonBrowseData($row, $text);
    }
} else {
    //return raw results
    $return['listings'] = $this->db->GetAll('' . $query);
}

$return['page'] = $page;
$return['limit'] = $limit;
$return['total_count'] = $this->db->GetOne('' . $query->getCountQuery());

return $return;
