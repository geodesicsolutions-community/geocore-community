<?php
//interactive.php
##########GIT Build Data##########
## 
## File Changed In GIT Commit:
## 
##    7.5.3-36-gea36ae7
## 
##################################

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

$records_at_once = 100;
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
	<input type="submit" value="Continue" id="cont_btn" onclick="jQuery('#upgradeForm').submit(); jQuery(this).prop('disabled',true); this.value='Processing next batch...';" />
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
 * Function that inserts all the categories for the given listing
 * @param int $listing_id
 * @param int $region_id
 * @param ADODB $db
 * @return boolean
 */
function insertCategoriesForListing($listing_id, $category_id, $db)
{
	$category_id = (int)$category_id;
	$listing_id = (int)$listing_id;
	
	//first if this is the top, remove any regions in the DB already set...
	$db->Execute("DELETE FROM `geodesic_listing_categories` WHERE `listing`=?", array($listing_id));


	if (!$listing_id) {
		//failsafe
		return false;
	}
	$top_category = $category_id;
	while ($category_id) {
		//get the region info
		$category = $db->GetRow("SELECT * FROM `geodesic_categories` r, `geodesic_categories_languages` l WHERE r.category_id=l.category_id AND l.language_id=1 AND r.`category_id`=?", array($category_id));
		
		if (!$category) {
			//if it gets here, the listing is assigned to a category that doesn't exist
			//update it to be "done" so that the upgrade knows not to keep trying
			$db->Execute("UPDATE `geodesic_classifieds` SET `upgrade_category_id`=1 WHERE `id` = ?",array($listing_id));
			logU('Listing #'.$listing_id.': category data invalid. Skipping that one.');
			return false;
		}
		$is_terminal = ($category_id === $top_category)? 'yes' : 'no';
		//insert for the region specified.
		$result = $db->Execute("INSERT INTO `geodesic_listing_categories` SET `listing`=?, `category`=?, `level`=?, `category_order`='0', `default_name`=?, `is_terminal`=?",
				array($listing_id, $category_id, $category['level'], $category['category_name'], $is_terminal));
		if (!$result) {
			logU('error: '.$db->ErrorMsg(),true);
		}
		
		$category_id = (int)$category['parent_id'];
	}

	//save region id in thingy to keep track that it was done.
	$db->Execute("UPDATE `geodesic_classifieds` SET `upgrade_category_id`=1 WHERE `id`=?", array($listing_id));
	return true;
}

/*
FOR TESTING: Here are queries to reset stuff:
ALTER TABLE `geodesic_categories`
  DROP `level`,
  DROP `enabled`;
TRUNCATE TABLE `geodesic_listing_categories`;

UPDATE `geodesic_classifieds` SET `upgrade_category_id`=0;

 */
if (!$this->fieldExists('geodesic_classifieds', 'upgrade_category_id')) {
	//insert upgrade region ID to keep track of what region is used for what listing
	$this->_db->Execute("ALTER TABLE `geodesic_classifieds` ADD `upgrade_category_id` INT NOT NULL DEFAULT '0',
			ADD INDEX `upgrade_category_id` ( `upgrade_category_id` ) ");
}
if (!$this->fieldExists('geodesic_categories','level')) {
	//Upgrade columns to process things in parts, have to run these here
	$this->_db->Execute("ALTER TABLE  `geodesic_categories` ADD  `level` INT NOT NULL DEFAULT  '1' AFTER  `parent_id` ,
		ADD  `enabled` ENUM(  'yes',  'no' ) NOT NULL DEFAULT  'yes' AFTER  `level`,
		ADD INDEX `level` (`level`),
		ADD INDEX `enabled` (`enabled`)");
	$this->_db->Execute("UPDATE `geodesic_categories` SET `level`=0"); //set all pre-existing categories to level=0 so that the stuff below gets run
}
if (!$this->tableExists('geodesic_categories_languages')) {
	//rename table
	$this->_db->Execute("RENAME TABLE `geodesic_classifieds_categories_languages` TO `geodesic_categories_languages`");
}
if (!$this->fieldExists('geodesic_categories_languages', 'category_image')) {
	//add category_image to the languages table
	$this->_db->Execute("ALTER TABLE `geodesic_categories_languages` ADD `category_image` VARCHAR( 255 ) NOT NULL DEFAULT '' AFTER `description`");
}
if (!$this->tableExists('geodesic_category_exclude_list_types')) {
	//create table
	$this->_db->Execute("CREATE TABLE IF NOT EXISTS `geodesic_category_exclude_list_types` (
	  `category_id` int(11) NOT NULL,
	  `listing_type` varchar(128) NOT NULL,
	  PRIMARY KEY (`category_id`,`listing_type`)
	)");
}

if (!$this->tableExists('geodesic_listing_categories')) {
	$this->_db->Execute("CREATE TABLE IF NOT EXISTS `geodesic_listing_categories` (
	  `listing` int(11) NOT NULL,
	  `category` int(11) NOT NULL,
	  `level` int(11) NOT NULL,
	  `category_order` int(11) NOT NULL DEFAULT '0',
	  `default_name` varchar(255) NOT NULL,
	  `is_terminal` enum('yes','no') NOT NULL DEFAULT 'no',
	  PRIMARY KEY (`listing`,`category`),
	  KEY `level` (`level`),
	  KEY `category_order` (`category_order`),
	  KEY `is_terminal` (`is_terminal`)
	)");
}

$cat_count = (int)$this->_db->GetOne("SELECT COUNT(*) FROM `geodesic_categories` WHERE `level`!=0");
$listing_count = (int)$this->_db->GetOne("SELECT COUNT(*) FROM `geodesic_classifieds` WHERE `upgrade_category_id`!=0 OR `category`=0");

$processed = $cat_count+$listing_count;

$cat_total = (int)$this->_db->GetOne("SELECT COUNT(*) FROM `geodesic_categories`");
$listing_total = (int)$this->_db->GetOne("SELECT COUNT(*) FROM `geodesic_classifieds`");

$total = $cat_total+$listing_total;
?>
Currently processing categories. This may take some time. Please be patient and do not navigate away from this page.<br /><br />
Completed <?php echo round(100*$processed/$total,1);?>% (<?php echo $processed; ?> of <?php echo $total; ?>)<br /><br />

<?php if ($total > $processed) { ?>
Now processing the next <?php echo $records_at_once; ?> records. Please wait...<br /><br />
<?php } ?>
<?php 

//$languages = $this->_db->GetAll("SELECT `language_id` FROM `geodesic_pages_languages`");

$category_map = array();
if ($cat_count < $cat_total) {
	$categories = $this->_db->Execute("SELECT * FROM `geodesic_categories` WHERE `level`=0 ORDER BY `parent_id`, `category_id` LIMIT $records_at_once");
	
	if ($categories) {
		foreach ($categories as $category) {
			if (strlen(trim($category['category_image']))>0) {
				//insert category image into languages table
				
				$category_image = urlencode(trim($category['category_image']));
				$this->_db->Execute("UPDATE `geodesic_categories_languages` SET `category_image`=? WHERE `category_id`=?",array($category_image, $category['category_id']));
			}
			if ($category['listing_types_allowed']>0) {
				//listing_types_allowed - insert entries in new table geodesic_category_exclude_list_types
				//1 = "classifieds only", so would add "auctions" to list of types to exclude for that category...
				$exclude = ((int)$category['listing_types_allowed']===1)? 'auctions' : 'classifieds';
				
				$this->_db->Execute("INSERT INTO `geodesic_category_exclude_list_types` SET `category_id`=?, `listing_type`=?",array($category['category_id'], $exclude));
			}
			
			if (!isset($category_map[$category['parent_id']])) {
				//get the parent level
				$category_map[$category['parent_id']] = $category['parent_id'] ? (int)$this->_db->GetOne("SELECT `level` FROM `geodesic_categories` WHERE `category_id`={$category['parent_id']}") : 0;
			}
			
			$category_map[$category['category_id']] = $level = (int)($category_map[$category['parent_id']]+1);
			if (!$level) {
				logU('level is 0!',true);
				return false;
			}
			$result = $this->_db->Execute("UPDATE `geodesic_categories` SET `level`={$level} WHERE `category_id`={$category['category_id']} LIMIT 1");
			
			if (!$result) {
				logU("error: ".$this->_db->ErrorMsg(),true);
			}
			
			$records++;
		}
	}
	if ($records >= $records_at_once || $this->_db->GetOne("SELECT COUNT(*) FROM `geodesic_categories` WHERE `level`=0")>0) {
		//we did all the records we want at once...
		echo $continue_button;
		echo $nextStep;
		return false;
	}
}

//now do same for states
$limit = $records_at_once - $records;
if ($listing_count < $listing_total) {
	$listings = $this->_db->Execute("SELECT * FROM `geodesic_classifieds` WHERE `upgrade_category_id`=0 AND `category`>0 ORDER BY `id` LIMIT $limit");
	if ($listings) {
		foreach ($listings as $listing) {
			insertCategoriesForListing($listing['id'], $listing['category'], $this->_db);
			
			$records++;
		}
	}
	if ($records >= $records_at_once || $this->_db->GetOne("SELECT COUNT(*) FROM `geodesic_classifieds` WHERE `upgrade_category_id`=0 AND `category`>0")>0) {
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