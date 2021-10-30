<?php
//interactive.php


/**
 * How this file works:
 * 
 * 
 * 
 * return true:  Process this update.
 * Return false: do not process this update, the update will be listed as "in
 *   progress".
 *   
 * Anything that is echoed out will be displayed in a box on the update screen.
 * 
 * This file can be used to notify the user of something, but it's primary use
 * is to allow some interactive step in the update process.  See the working
 * example at the end of this file.
 * 
 * 
 */
ini_set('display_errors','On');

$records_at_once = 300;
$records = 0;

ob_start();
?>
Finished processing this batch. Next batch will proceed momentarily. Please wait.
<br /><br />
If you see any red errors above, make note of the errors and click "continue" below to proceed.<br />
<form action="" method="post" id="upgradeForm">
	<!-- If submitting, remember to have these hidden fields
		so that the update knows they have already gotten past
		the "requirement checking" stage.
	 -->
	<input type="hidden" name="license" value="on" />
	<input type="hidden" name="backup_agree" value="on" />
	<input type="hidden" name="licenseKey" value="<?php echo htmlspecialchars($_POST['licenseKey']); ?>" />
	<br />
	<input type="submit" value="Continue" id="cont_btn" onclick="$('upgradeForm').submit(); $(this).disable(); this.value='Processing next batch...';" />
</form>
<?php

$nextStep = <<<NEXT
	<script type="text/javascript">
		jQuery(function() {
			if (typeof errors_found != 'undefined' && errors_found) { return; }
			jQuery('#upgradeForm').submit();
			jQuery('#cont_btn').prop('disabled',true);
			jQuery('#cont_btn').val('Processing next batch. Please wait');
		});
	</script>
NEXT;

$continue_button = ob_get_clean();

function logU ($message, $isError = false)
{
	//change this to false to make it show all messages
	$silent = true;
	
	if ($isError||!$silent) {
		echo "<div".($isError? ' style="color: red;"':'').">$message<br /><br /></div>\n";
		if ($isError) {
			echo '<script>var errors_found = true;</script>';
		}
	}
}

/**
 * Function that inserts all the regions for the given listing
 * @param int $listing_id
 * @param int $region_id
 * @param ADODB $db
 * @return boolean
 */
function insertRegionsForListing($listing_id, $region_id, $db)
{
	//first if this is the top, remove any regions in the DB already set...
	$db->Execute("DELETE FROM `geodesic_listing_regions` WHERE `listing`=?", array($listing_id));
	
	$region_id = (int)$region_id;
	$listing_id = (int)$listing_id;
	
	if (!$listing_id) {
		//failsafe
		return false;
	}
	
	while ($region_id) {
		//get the region info
		$region = $db->GetRow("SELECT * FROM `geodesic_region` r, `geodesic_region_languages` l WHERE r.id=l.id AND l.language_id=1 AND r.`id`=?", array($region_id));
		
		if (!$region) {
			//something went wrong, abort!
			return false;
		}
		
		//insert for the region specified.
		$db->Execute("INSERT INTO `geodesic_listing_regions` SET `listing`=?, `region`=?, `level`=?, `primary_region`='yes', `default_name`=?",
					array($listing_id, $region['id'], $region['level'], $region['name']));
		
		$region_id = (int)$region['parent'];
	}
	
	//save region id in thingy to keep track that it was done.
	$db->Execute("UPDATE `geodesic_classifieds` SET `upgrade_region_id`=1 WHERE `id`=?", array($listing_id));
	return true;
}

/**
 * Inserts regions for a given user, starting from the lowest
 * @param int $user_id
 * @param int $region_id
 * @param ADODB $db
 * @return boolean
 */
function insertRegionsForUser($user_id, $region_id, $db)
{
	//clear any regions already set
	$db->Execute("DELETE FROM `geodesic_user_regions` WHERE `user`=?", array($user_id));
	
	//sanity checks
	$user_id = (int)$user_id;
	$region_id = (int)$region_id;
	if(!$user_id) {
		return false;
	}
	
	while($region_id) {
		//get the info for this region
		$region = $db->GetRow("SELECT * FROM `geodesic_region` r, `geodesic_region_languages` l WHERE r.id=l.id AND l.language_id=1 AND r.`id`=?", array($region_id));
		
		if (!$region) {
			//something went wrong, abort!
			return false;
		}
		
		//insert for the region specified.
		$db->Execute("INSERT INTO `geodesic_user_regions` SET `user`=?, `region`=?, `level`=?, `default_name`=?",
				array($user_id, $region['id'], $region['level'], $region['name']));
		
		$region_id = (int)$region['parent'];
	}
	
	//save region id in thingy to keep track that it was done.
	$db->Execute("UPDATE `geodesic_userdata` SET `upgrade_region_id`=1 WHERE `id`=?", array($user_id));
	return true;
}

function getStateId ($state, $parent_id, $db)
{
	$parent_id = (int)$parent_id;
	
	$parentCheck = '';
	if ($parent_id) {
		$parentCheck = "(`parent_id`=$parent_id OR `parent_id`=0) AND ";
	}
	
	//first check the abbreviation.  Can start with adding wildcard % before/after since abbreviation is
	//"supposed" to all be same length, should not have one be sub-string of another
	$state_id = (int)$db->GetOne("SELECT `state_id` FROM `geodesic_states` WHERE $parentCheck `abbreviation` LIKE ?)", array('%'.addcslashes($state, '%_').'%'));
	if ($state_id) {
		return $state_id;
	}
	//OK could not find match for abbreviation.  So check the normal name, but don't
	//add wildcards before/after initially
	$state_id = (int)$db->GetOne("SELECT `state_id` FROM `geodesic_states` WHERE $parentCheck `name` LIKE ?", array(addcslashes($state, '%_')));
	if ($state_id) {
		return $state_id;
	}
	//Gets this far, could not find it by name without wildcard before/after.  Go ahead
	//and check with wildcard before/after
	$state_id = (int)$db->GetOne("SELECT `state_id` FROM `geodesic_states` WHERE $parentCheck `name` LIKE ?", array('%'.addcslashes($state, '%_').'%'));
	//return it, we've checked everything so this will either be the actual state ID or will be 0.
	return $state_id;
}

/*
FOR TESTING: Here are queries to reset stuff:

TRUNCATE TABLE `geodesic_region`;
TRUNCATE TABLE `geodesic_region_languages`;
TRUNCATE TABLE `geodesic_listing_regions`;
TRUNCATE TABLE `geodesic_user_regions`;
UPDATE `geodesic_countries` SET `upgrade_region_id`=0;
UPDATE `geodesic_states` SET `upgrade_region_id`=0;
UPDATE `geodesic_addon_geographic_regions` SET `upgrade_region_id`=0;
UPDATE `geodesic_classifieds` SET `upgrade_region_id`=0;
UPDATE `geodesic_userdata` SET `upgrade_region_id`=0;

 */
if (!$this->fieldExists('geodesic_countries','upgrade_region_id')) {
	//Upgrade columns to process things in parts, have to run these here
	$this->_db->Execute("ALTER TABLE `geodesic_countries` ADD `upgrade_region_id` INT NOT NULL DEFAULT '0',
	ADD INDEX `upgrade_region_id` ( `upgrade_region_id` ) ");
	$this->_db->Execute("ALTER TABLE `geodesic_states` ADD `upgrade_region_id` INT NOT NULL DEFAULT '0',
	ADD INDEX `upgrade_region_id` ( `upgrade_region_id` ) ");
	$this->_db->Execute("ALTER TABLE `geodesic_addon_geographic_regions` ADD `upgrade_region_id` INT NOT NULL DEFAULT '0',
	ADD INDEX `upgrade_region_id` ( `upgrade_region_id` ) ");
	$this->_db->Execute("ALTER TABLE `geodesic_userdata` ADD `upgrade_region_id` INT NOT NULL DEFAULT '0',
			ADD INDEX `upgrade_region_id` ( `upgrade_region_id` ) ");
}
if (!$this->tableExists('geodesic_region')) {
	$this->_db->Execute("
	CREATE TABLE IF NOT EXISTS `geodesic_region` (
			`id` int(11) NOT NULL AUTO_INCREMENT,
			`parent` int(11) NOT NULL,
			`level` int(2) NOT NULL,
			`enabled` enum('yes','no') NOT NULL DEFAULT 'yes',
			`billing_abbreviation` varchar(255) NOT NULL,
			`unique_name` varchar(255) NOT NULL,
			`tax_percent` double NOT NULL DEFAULT '0',
			`tax_flat` double NOT NULL DEFAULT '0',
			`display_order` int(11) NOT NULL,
			PRIMARY KEY (`id`),
			KEY `parent` (`parent`),
			KEY `level` (`level`),
			KEY `enabled` (`enabled`),
			KEY `unique_name` (`unique_name`),
			KEY `display_order` (`display_order`)
	) AUTO_INCREMENT=1 ");
	
	$this->_db->Execute("
	CREATE TABLE IF NOT EXISTS `geodesic_region_languages` (
			`id` int(11) NOT NULL COMMENT 'corresponds to id in geodesic_region',
			`language_id` int(11) NOT NULL,
			`name` varchar(255) NOT NULL,
			PRIMARY KEY (`id`,`language_id`)
	)");
}

if (!$this->tableExists('geodesic_listing_regions')) {
	$this->_db->Execute("CREATE TABLE IF NOT EXISTS `geodesic_listing_regions` (
	  `listing` int(11) NOT NULL,
	  `region` int(11) NOT NULL,
	  `level` int(11) NOT NULL,
	  `primary_region` enum('yes','no') NOT NULL DEFAULT 'yes',
	  `default_name` varchar(255) NOT NULL,
	  PRIMARY KEY (`listing`,`region`),
	  KEY `level` (`level`),
	  KEY `primary_region` (`primary_region`)
	)");
}

if (!$this->fieldExists('geodesic_classifieds', 'upgrade_region_id')) {
	//insert upgrade region ID to keep track of what region is used for what listing
	$this->_db->Execute("ALTER TABLE `geodesic_classifieds` ADD `upgrade_region_id` INT NOT NULL DEFAULT '0',
			ADD INDEX `upgrade_region_id` ( `upgrade_region_id` ) ");
}

if(!$this->fieldExists('geodesic_classifieds', 'mapping_location')) {
	//add new mapping field
	$this->_db->Execute("ALTER TABLE `geodesic_classifieds` ADD `mapping_location` TEXT NOT NULL DEFAULT ''");
}

$country_count = (int)$this->_db->GetOne("SELECT COUNT(*) FROM `geodesic_countries` WHERE `upgrade_region_id`!=0");
$state_count = (int)$this->_db->GetOne("SELECT COUNT(*) FROM `geodesic_states` WHERE `upgrade_region_id`!=0");
$region_addon_count = (int)$this->_db->GetOne("SELECT COUNT(*) FROM `geodesic_addon_geographic_regions` WHERE `upgrade_region_id`!=0");
$listing_count = (int)$this->_db->GetOne("SELECT COUNT(*) FROM `geodesic_classifieds` WHERE `upgrade_region_id`!=0");
$user_count = (int)$this->_db->GetOne("SELECT COUNT(*) FROM `geodesic_userdata` WHERE `upgrade_region_id`!=0");

$processed = $country_count+$state_count+$region_addon_count+$listing_count+$user_count;

$country_total = (int)$this->_db->GetOne("SELECT COUNT(*) FROM `geodesic_countries`");
$state_total = (int)$this->_db->GetOne("SELECT COUNT(*) FROM `geodesic_states`");
$region_addon_total = (int)$this->_db->GetOne("SELECT COUNT(*) FROM `geodesic_addon_geographic_regions`");
$listing_total = (int)$this->_db->GetOne("SELECT COUNT(*) FROM `geodesic_classifieds`");
$user_total = (int)$this->_db->GetOne("SELECT COUNT(*) FROM `geodesic_userdata`");

$total = $country_total+$state_total+$region_addon_total+$listing_total+$user_total;
?>
Currently processing geographic regions. This may take some time. Please be patient and do not navigate away from this page.<br /><br />
Completed <?php echo round(100*$processed/$total,1);?>% (<?php echo $processed; ?> of <?php echo $total; ?>)<br /><br />

<?php if ($total > $processed) { ?>
Now processing the next <?php echo $records_at_once; ?> records. Please wait...<br /><br />
<?php } ?>
<?php 

$languages = $this->_db->GetAll("SELECT `language_id` FROM `geodesic_pages_languages`");

$country_map = array();
if ($country_count < $country_total) {
	$countries = $this->_db->Execute("SELECT * FROM `geodesic_countries` WHERE `upgrade_region_id`=0 ORDER BY `country_id` LIMIT $records_at_once");
	
	if ($countries) {
		foreach ($countries as $country) {
			//we need insert ID so must do it directly...
			$tax_flat = $tax_percent = 0;
			if ($country['tax_type']==0) {
				$tax_percent = $country['tax'];
			} else {
				$tax_flat = $country['tax'];
			}
			$result = $this->_db->Execute("INSERT INTO `geodesic_region` SET `parent`=0, `level`=1, `enabled`='yes', `billing_abbreviation`=?,
					`unique_name`=?, `tax_percent`=?, `tax_flat`=?, `display_order`=?",
					array(trim($country['abbreviation']), trim($country['subdomain']), $tax_percent, $tax_flat, (int)$country['display_order']));
			if (!$result) {
				logU("error: ".$this->_db->ErrorMsg(),true);
			}
			$country_id = $this->_db->Insert_Id();
			$country_map[$country['country_id']] = $country_id;
			foreach ($languages as $lang) {
				//add country/language name
				$this->_db->Execute("INSERT INTO `geodesic_region_languages` SET `id`=?, `language_id`=?, `name`=?", array ($country_id, $lang['language_id'], urlencode(trim($country['name']))));
			}
			//save it...
			$this->_db->Execute("UPDATE `geodesic_countries` SET `upgrade_region_id`=? WHERE `country_id`=?", array($country_id,$country['country_id']));
			$records++;
		}
	}
	if ($records >= $records_at_once || $this->_db->GetOne("SELECT COUNT(*) FROM `geodesic_countries` WHERE `upgrade_region_id`=0")>0) {
		//we did all the records we want at once...
		echo $continue_button;
		echo $nextStep;
		return false;
	}
}

//now do same for states
$limit = $records_at_once - $records;
$state_map = array();
if ($state_count<$state_total) {
	$states = $this->_db->Execute("SELECT * FROM `geodesic_states` WHERE `upgrade_region_id`=0 ORDER BY `state_id` LIMIT $limit");
	if ($states) {
		foreach ($states as $state) {
			//we need insert ID so must do it directly...
			$tax_flat = $tax_percent = 0;
			if ($state['tax_type']==0) {
				$tax_percent = $state['tax'];
			} else {
				$tax_flat = $state['tax'];
			}
			if ($state['parent_id'] && !isset($country_map[$state['parent_id']])) {
				//must have been done on previous thingy, get it from db
				$country_map[$state['parent_id']] = (int)$this->_db->GetOne("SELECT `upgrade_region_id` FROM `geodesic_countries` WHERE `country_id`=?",array($state['parent_id']));
			}
			$parent = $country_map[$state['parent_id']];
			$level = 2;
			if (!$parent) {
				//don't bother with this one, we don't know it
				if ($country_total > 0) {
					logU("Did not find parent country for state {$state['name']}, so putting it in the first country..");
					$parent = 1;
				} else {
					logU("Did not find parent country, it seems you have no countries in the system!");
					$parent = 0;
					//set level to 1 since there are no countries
					$level = 1;
				}
			}
			$this->_db->Execute("INSERT INTO `geodesic_region` SET `parent`=?, `level`={$level}, `enabled`='yes', `billing_abbreviation`=?,
					`unique_name`=?, `tax_percent`=?, `tax_flat`=?, `display_order`=?",
					array($parent, trim($state['abbreviation']), trim($state['subdomain']), $tax_percent, $tax_flat, (int)$state['display_order']));
			
			$state_id = $this->_db->Insert_Id();
			$state_map[$state['state_id']] = $state_id;
			foreach ($languages as $lang) {
				//add country/language name
				$this->_db->Execute("INSERT INTO `geodesic_region_languages` SET `id`=?, `language_id`=?, `name`=?", array ($state_id, $lang['language_id'], urlencode(trim($state['name']))));
			}
			//save it...
			$this->_db->Execute("UPDATE `geodesic_states` SET `upgrade_region_id`=? WHERE `state_id`=?", array($state_id,$state['state_id']));
			$records++;
		}
	}
	if ($records >= $records_at_once || $this->_db->GetOne("SELECT COUNT(*) FROM `geodesic_states` WHERE `upgrade_region_id`=0")>0) {
		//we did all the records we want at once...
		echo $continue_button;
		echo $nextStep;
		return false;
	}
}

//Now for geographic navigation regions...
$limit = $records_at_once - $records;
$region_map = array();
if ($region_addon_count < $region_addon_total) {
	$regions = $this->_db->Execute("SELECT * FROM `geodesic_addon_geographic_regions` WHERE `upgrade_region_id`=0 ORDER BY `parent_region`, `id` LIMIT $limit");
	
	if ($regions) {
		foreach ($regions as $region) {
			//we need insert ID so must do it directly...
			
			if ($region['parent_state']) {
				if (!isset($state_map[$region['parent_state']])) {
					//must have been done on previous thingy, get it from db
					$state_map[$region['parent_state']] = (int)$this->_db->GetOne("SELECT `upgrade_region_id` FROM `geodesic_states` WHERE `state_id`=?",array($region['parent_state']));
				}
				$parent = (int)$state_map[$region['parent_state']];
				$level = 3;
			} else if ($region['parent_region']) {
				if (!isset($region_map[$region['parent_region']])) {
					//must have been done on previous thingy, get it from db
					$region_map[$region['parent_region']] = (int)$this->_db->GetOne("SELECT `upgrade_region_id` FROM `geodesic_addon_geographic_regions` WHERE `id`=?",array($region['parent_region']));
				}
				$parent = (int)$region_map[$region['parent_region']];
				if (!isset($levels[$parent])) {
					$levels[$parent] = $this->_db->GetOne("SELECT `level` FROM `geodesic_region` WHERE `id`=?",array($parent));
				}
				$level = $levels[$parent] + 1;
			} else {
				//top level region
				$parent = 0;
				$level = 1;
			}
			
			
			$this->_db->Execute("INSERT INTO `geodesic_region` SET `parent`=?, `level`=?, `enabled`='yes',
					`unique_name`=?, `display_order`=?",
					array($parent, $level, trim($region['subdomain']), (int)$region['display_order']));
	
			$region_id = $this->_db->Insert_Id();
			$region_map[$region['id']] = $region_id;
			//clean the label
			$label = urldecode($region['label']);
			$label = trim($label);
			$label = urlencode($label);
			foreach ($languages as $lang) {
				//add country/language name
				$this->_db->Execute("INSERT INTO `geodesic_region_languages` SET `id`=?, `language_id`=?, `name`=?", array ($region_id, $lang['language_id'], $label));
			}
			//save it...
			$this->_db->Execute("UPDATE `geodesic_addon_geographic_regions` SET `upgrade_region_id`=? WHERE `id`=?", array($region_id,$region['id']));
			$records++;
		}
	}
	if ($records >= $records_at_once || $this->_db->GetOne("SELECT COUNT(*) FROM `geodesic_addon_geographic_regions` WHERE `upgrade_region_id`=0")>0) {
		//we did all the records we want at once...
		echo $continue_button;
		echo $nextStep;
		return false;
	}
}

if ($listing_count < $listing_total) {
	$listings = $this->_db->Execute("SELECT * FROM `geodesic_classifieds` WHERE `upgrade_region_id`=0 ORDER BY `id` LIMIT $limit");
	if ($listings) {
		$check_addon_regions = (int)$this->_db->Execute("SELECT COUNT(*) FROM `geodesic_addon_geographic_listings`");
		foreach ($listings as $listing) {
			//first, check just to make sure this listing doesn't already have a level 1 region
			//set because if it does, then it already has regions!
			$hasRegions = $this->_db->GetOne("SELECT COUNT(*) FROM `geodesic_listing_regions` WHERE `listing`=? AND `level`=1", array($listing['id']));
			if ($hasRegions) {
				//hey, this one already has regions set!  Good thing we checked!
				$this->_db->Execute("UPDATE `geodesic_classifieds` SET `upgrade_region_id`=1 WHERE `id`=?", array($listing['id']));
				$records++;
				continue;
			}
			
			//cheat a bit and tack on mapping updates here, so they can process in steps
			$mapping_location = array();
			if($listing['mapping_address']) {
				$mapping_location[] = $listing['mapping_address'];
			}
			if($listing['mapping_city']) {
				$mapping_location[] = $listing['mapping_city'];
			}
			if($listing['mapping_state']) {
				$mapping_location[] = $listing['mapping_state'];
			}
			if($listing['mapping_zip']) {
				$mapping_location[] = $listing['mapping_zip'];
			}
			if($listing['mapping_country']) {
				$mapping_location[] = $listing['mapping_country'];
			}
			$mapping_location = implode(" ",$mapping_location);
			$this->_db->Execute("UPDATE `geodesic_classifieds` SET `mapping_location` = ? WHERE `id` = ?", array($mapping_location, $listing['id']));
			
			
			if ($check_addon_regions) {
				//see if it has any regions...
				$addon_region_id = (int)$this->_db->GetOne("SELECT `region_id` FROM `geodesic_addon_geographic_listings` WHERE `listing`=? ORDER BY `level` DESC", array($listing['id']));
				if ($addon_region_id) {
					//use this as the region to use.
					if (!isset($region_map[$addon_region_id])) {
						//must have been done on previous thingy, get it from db
						$region_map[$addon_region_id] = (int)$this->_db->GetOne("SELECT `upgrade_region_id` FROM `geodesic_addon_geographic_regions` WHERE `id`=?",array($addon_region_id));
					}
					if (insertRegionsForListing($listing['id'], $region_map[$addon_region_id], $this->_db)) {
						//successfully inserted regions for the listing, don't attempt
						//the rest of this as we already have our region.
						logU("Listing {$listing['id']} added by region...");
						$records++;
						continue;
					}
				}
			}
			
			//see if can find the state/country
			$state = trim(urldecode($listing['location_state']));
			$country = trim(urldecode($listing['location_country']));
			$country_id = $state_id = 0;
			
			if ($country_total==0) {
				//special case, if there is no countries for the installation
				if (!$state) {
					$this->_db->Execute("UPDATE `geodesic_classifieds` SET `upgrade_region_id`=1 WHERE `id`=?",array($listing['id']));
					logU("Listing {$listing['id']} no state set...");
					$records++;
					continue;
				}
				
				$state_id = getStateId($state, 0, $this->_db);
				if ($state_id) {
					//insert regions for this listing according to state...
					if (!isset($state_map[$state_id])) {
						//must have been done on previous thingy, get it from db
						$state_map[$state_id] = (int)$this->_db->GetOne("SELECT `upgrade_region_id` FROM `geodesic_states` WHERE `state_id`=?",array($state_id));
					}
					if (insertRegionsForListing($listing['id'], $state_map[$state_id], $this->_db)) {
						//woot!  got it inserted by the state ID
						logU("Listing {$listing['id']} added by state...");
						$records++;
						continue;
					}
				}
				//no country or state or region, just update it to be updated
				logU("NOTE:  Listing {$listing['id']} failed to add by state...", true);
				$this->_db->Execute("UPDATE `geodesic_classifieds` SET `upgrade_region_id`=1 WHERE `id`=?",array($listing['id']));
				$records++;
			} else {
				//normal... add country and state
				if (!$country) {
					//can't properly attempt to get country/state if country not known...
					//Set it to 1 so we don't try over and over to set it for this listing
					$this->_db->Execute("UPDATE `geodesic_classifieds` SET `upgrade_region_id`=1 WHERE `id`=?",array($listing['id']));
					logU("Listing {$listing['id']} no country set...");
					$records++;
					continue;
				}
				
				//try to figure out country ID
				$country_id = (int)$this->_db->GetOne("SELECT `country_id` FROM `geodesic_countries` WHERE `name`=? OR `abbreviation`=?", array($country, $country));
				//echo "Country: '".urlencode($country)."'...<br />";
				if (!$country_id) {
					//could not find the country!
					$this->_db->Execute("UPDATE `geodesic_classifieds` SET `upgrade_region_id`=1 WHERE `id`=?",array($listing['id']));
					logU("Listing {$listing['id']} could not find country...");
					$records++;
					continue;
				}
				
				if ($state) {
					$state_id = getStateId($state, $country_id, $this->_db);
					if ($state_id) {
						//insert regions for this listing according to state...
						if (!isset($state_map[$state_id])) {
							//must have been done on previous thingy, get it from db
							$state_map[$state_id] = (int)$this->_db->GetOne("SELECT `upgrade_region_id` FROM `geodesic_states` WHERE `state_id`=?",array($state_id));
						}
						if (insertRegionsForListing($listing['id'], $state_map[$state_id], $this->_db)) {
							//woot!  got it inserted by the state ID
							logU("Listing {$listing['id']} added by state...");
							$records++;
							continue;
						}
					}
				}
				//last thing to try: insert by the country id
				if (!isset($country_map[$country_id])) {
					//must have been done on previous thingy, get it from db
					$country_map[$country_id] = (int)$this->_db->GetOne("SELECT `upgrade_region_id` FROM `geodesic_countries` WHERE `country_id`=?",array($country_id));
				}
				if (!insertRegionsForListing($listing['id'], $country_map[$country_id], $this->_db)) {
					//we failed!  Oh well we tried our best, mark that listing was upgraded,
					//since if the insertion failed it won't do this for us
					logU("Listing {$listing['id']} failed to add by country...",true);
					$this->_db->Execute("UPDATE `geodesic_classifieds` SET `upgrade_region_id`=1 WHERE `id`=?",array($listing['id']));
				} else {
					logU("Listing {$listing['id']} added by country...");
				}
				$records++;
			}
		}
	}
	if ($records >= $records_at_once || $this->_db->GetOne("SELECT COUNT(*) FROM `geodesic_classifieds` WHERE `upgrade_region_id`=0")>0) {
		//we did all the records we want at once...
		echo $continue_button;
		echo $nextStep;
		return false;
	}
}

if($user_count < $user_total) {
	$users = $this->_db->Execute("SELECT * FROM `geodesic_userdata` WHERE `upgrade_region_id`=0 ORDER BY `id` LIMIT $limit");
	if($users) {
		//see if addon regions are in use at all...if not, save time by skipping them
		$check_addon_regions = (int)$this->_db->Execute("SELECT COUNT(*) FROM `geodesic_addon_geographic_users`");
		foreach($users as $user) {
			//first, check just to make sure this user doesn't already have a level 1 region
			//set because if it does, then it already has regions!
			$hasRegions = $this->_db->GetOne("SELECT COUNT(*) FROM `geodesic_user_regions` WHERE `user`=? AND `level`=1", array($user['id']));
			if ($hasRegions) {
				//hey, this one already has regions set!  Good thing we checked!
				$this->_db->Execute("UPDATE `geodesic_userdata` SET `upgrade_region_id`=1 WHERE `id`=?", array($user['id']));
				$records++;
				continue;
			}
			
			
			//now see if this user has any addon regions set
			if($check_addon_regions) {
				$addon_region_id = (int)$this->_db->GetOne("SELECT `region_id` FROM `geodesic_addon_geographic_users` WHERE `user`=? ORDER BY `level` DESC", array($user['id']));
				if($addon_region_id) {
					//got the lowest-level region from this user. use it!
					if (!isset($region_map[$addon_region_id])) {
						//must have not been done on previous thingy, get it from db
						$region_map[$addon_region_id] = (int)$this->_db->GetOne("SELECT `upgrade_region_id` FROM `geodesic_addon_geographic_regions` WHERE `id`=?",array($addon_region_id));
					}
					if(insertRegionsForUser($user['id'], $region_map[$addon_region_id], $this->_db)) {
						//success! complete for this region, so move on!
						logU("User {$user['id']} added by region...");
						$records++;
						continue;
					}
				}
			}
			
			//no regions set in the addon, so let's try to get state/country from the userdata table
			
			$state = trim($user['state']);
			$country = trim($user['country']);
			$country_id = $state_id = 0;
			if ($country_total==0) {
				//special case, there are no countries...  just try state
				if (!$state) {
					//can't properly attempt to get country/state if state not known...
					//Set it to 1 so we don't try over and over to set it for this listing
					$this->_db->Execute("UPDATE `geodesic_userdata` SET `upgrade_region_id`=1 WHERE `id`=?",array($user['id']));
					logU("User {$user['id']} no state set...");
					$records++;
					continue;
				}
				
				$state_id = getStateId($state,0,$this->_db);
				
				if ($state_id) {
					//insert regions for this listing according to state...
					if (!isset($state_map[$state_id])) {
						//must not have been done on previous thingy, get it from db
						$state_map[$state_id] = (int)$this->_db->GetOne("SELECT `upgrade_region_id` FROM `geodesic_states` WHERE `state_id`=?",array($state_id));
					}
					if (insertRegionsForUser($user['id'], $state_map[$state_id], $this->_db)) {
						//woot!  got it inserted by the state ID
						logU("User {$user['id']} added by state...");
						$records++;
						continue;
					}
				}
			
				$this->_db->Execute("UPDATE `geodesic_userdata` SET `upgrade_region_id`=1 WHERE `id`=?",array($user['id']));
				if ($user['id']>1) {
					logU("Note: User {$user['id']} could not set state (state was probably renamed or removed, could not use state '{$state}')...");//, true);
				}
				$records++;
			} else {
				if (!$country) {
					//can't properly attempt to get country/state if country not known...
					//Set it to 1 so we don't try over and over to set it for this listing
					$this->_db->Execute("UPDATE `geodesic_userdata` SET `upgrade_region_id`=1 WHERE `id`=?",array($user['id']));
					logU("User {$user['id']} no country set...");
					$records++;
					continue;
				}
					
				//try to figure out country ID
				$country_id = (int)$this->_db->GetOne("SELECT `country_id` FROM `geodesic_countries` WHERE `name`=? OR `abbreviation`=?", array($country, $country));
				
				if (!$country_id) {
					//could not find the country!
					$this->_db->Execute("UPDATE `geodesic_userdata` SET `upgrade_region_id`=1 WHERE `id`=?",array($user['id']));
					if($user['id'] != 1) {
						//don't show errors about the admin user not having a country, since it almost always doesn't have a country
						logU("User {$user['id']} could not find country...");
					}
					$records++;
					continue;
				}
	
				if ($state) {
					$state_id = getStateId($state, $country_id, $this->_db);
					if ($state_id) {
						//insert regions for this listing according to state...
						if (!isset($state_map[$state_id])) {
							//must not have been done on previous thingy, get it from db
							$state_map[$state_id] = (int)$this->_db->GetOne("SELECT `upgrade_region_id` FROM `geodesic_states` WHERE `state_id`=?",array($state_id));
						}
						if (insertRegionsForUser($user['id'], $state_map[$state_id], $this->_db)) {
							//woot!  got it inserted by the state ID
							logU("User {$user['id']} added by state...");
							$records++;
							continue;
						}
					}
				}
				//last thing to try: insert by the country id
				if (!isset($country_map[$country_id])) {
					//must not have been done on previous thingy, get it from db
					$country_map[$country_id] = (int)$this->_db->GetOne("SELECT `upgrade_region_id` FROM `geodesic_countries` WHERE `country_id`=?",array($country_id));
				}
				if (!insertRegionsForUser($user['id'], $country_map[$country_id], $this->_db)) {
					//we failed!  Oh well we tried our best, mark that listing was upgraded,
					//since if the insertion failed it won't do this for us
					if ($user['id']>1) {
						logU("User {$user['id']} failed to add by country...",true);
					}
					$this->_db->Execute("UPDATE `geodesic_userdata` SET `upgrade_region_id`=1 WHERE `id`=?",array($listing['id']));
				} else {
					logU("User {$user['id']} added by country...");
				}
				$records++;
			}
		}
	}
	if ($records >= $records_at_once || $this->_db->GetOne("SELECT COUNT(*) FROM `geodesic_userdata` WHERE `upgrade_region_id`=0")>0) {
		//we did all the records we want at once...
		echo $continue_button;
		echo $nextStep;
		return false;
	}
}

echo "<br /><span style='color: #009900;'>Import 100% Complete! Proceed with the Upgrade.</span><br />";



return true; //short-circuit the example below, comment this line for a working example.

//for example of adding new pages (or modules), see interactive.php from 3.1_to_4.0

/**
 * ########## WORKING EXAMPLE ############
 * ######### DO NOT REMOVE!!!! ###########
 * 
 * Always keep the below working example at the bottom of the interactive
 * file, to use as a template for future updates.  Just return before it
 * reaches this section of the code, or you can see this in action by temporarily
 * commenting out everything above this part of the file.
 */

if (!isset($_POST['favColor'])) {
	//We have not yet displayed the selection, so display it and let them
	//choose what their favorite color is.
	?>
Hello!!!  This is an interactive portion of the update process.  Normally we would
ask some important question we need the answer to in order to do the update, but
this is just an example, so we're going to ask what is your favorite color.
<br /><br />
<form action="" method="post">
	<!-- If submitting, remember to have these hidden fields
		so that the update knows they have already gotten past
		the "requirement checking" stage.
	 -->
	<input type="hidden" name="license" value="on" />
	<input type="hidden" name="backup_agree" value="on" />
	<input type="hidden" name="licenseKey" value="<?php echo htmlspecialchars($_POST['licenseKey']); ?>" />
	<label>
		What is your favorite color in the rainbow? 
		<select name="favColor">
			<option>Red</option>
			<option>Orange</option>
			<option>Yellow</option>
			<option>Green</option>
			<option>Blue</option>
			<option>Indigo</option>
			<option>Violet</option>
		</select>
	</label>
	<input type="submit" value="Continue" />
</form>
<?php 
	//Now we return false, to tell the update script to not process 
	//the update yet.
	return false;
	
} else {
	/**
	 * VERY IMPORTANT:  Check inputs!  Until the admin deletes the update
	 * directory, this update process is publically accessible, so do NOT
	 * trust any inputs from the user!
	 */
	$allowed_colors = array (
		'Red','Orange','Yellow','Green','Blue','Indigo','Violet'
	);
	if (!in_array($_POST['favColor'], $allowed_colors)) {
		//Show critical error, which does not allow to proceed.
		$this->criticalError('Invalid input specified!');
	}
	
	//They have selected their favorite color.  Display a message to them
	// (this message is optional of course)
?>
Thank you for selecting your favorite color, <?php echo $_POST['favColor'];?>, 
that is my favorite color too!!  Proceeding with the update.
<?php 
	
	/**
	 * Note: You would do any queries based on the POST data provided during
	 * the normal conditional_sql.php or even arrays.php files.
	 * 
	 * If you do, remember to never trust user input since the update is public
	 * accessible.  NEVER use un-cleaned input as part of an SQL query, but I
	 * don't have to tell you that :)
	 * 
	 */

	//Return true to indicate to proceed with this update.
	return true;
}