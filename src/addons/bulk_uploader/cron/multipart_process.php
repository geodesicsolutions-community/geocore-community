<?php
//addons/bulk_uploader/cron/mutlipart_process.php


/**************************************************************************
Addon Created by Geodesic Solutions, LLC
Copyright (c) 2001-2018 Geodesic Solutions, LLC
All rights reserved
http://geodesicsolutions.com
see license attached to distribution
**************************************************************************/
##########GIT Build Data##########
## 
## File Changed In GIT Commit:
## ##    7.5.3-36-gea36ae7
## 
##################################

if (!defined('GEO_CRON_RUN')){
	die('NO ACCESS');
}

//look for multipart uploads that need processing
$sql = "SELECT * FROM `geodesic_addon_bulk_uploader_multipart` WHERE `last_run` + `gap` <= ?";
$result = $this->db->Execute($sql, array(geoUtil::time()));


if($result->RecordCount() == 0) {
	$this->log('found no multiparts to process right now. exiting.', __line__);
	return true;
}

$toDo = array();
while($row = $result->FetchRow()) {
	$toDo[] = $row['id'];
}

$this->log('these multipart sessions need more uploads done: '.print_r($toDo,1), __line__);

require_once(ADDONS_DIR . 'bulk_uploader/admin.php');
$bulkAdmin = new addon_bulk_uploader_admin();
if(!is_object($bulkAdmin)) {
	$this->log('failed to get the bulk uploader admin object. cannot continue', __line__);
	return true;
}

//loop through, check cache, inject session data, call upload function
$settings = array();
foreach($toDo as $id) {
	$this->log('starting work on multipart session: '.$id, __line__);
	$settings['multipart_id'] = $id;
	
	//***call the main bulk upload function and make it do all the heavy lifting***
	
	$this->log('about to call main uploader', __line__);
	//we just pass in settings['multipart_id'] here -- insertCSV will grab the rest of the settings from the db and load them up 'automagically'
	$bulkAdmin->insertCSV($settings);
	
	//main function will update last_run in db. nothing left to do here but go on to the next set...
}
 
$this->log('done. exiting.', __line__);

return true;