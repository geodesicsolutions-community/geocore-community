<?php
##########GIT Build Data##########
## 
## File Changed In GIT Commit:
## 
##    16.09.0-22-g019d772
## 
##################################

include_once('app_top.upgrade.php');

define("CAEtoLatest", 1);
define("oldCEtoCAE", 2);
define("oldAEtoCAE", 4);
define("prepareOldCE", 8);

define("GEOCLASSAUCTIONS", 1);
define("CLASSAUCTIONS", 1);

define("GEOAUCTIONS", 2);
define("AUCTIONS", 2);

define("GEOCLASSIFIEDS", 4);
define("CLASSIFIEDS", 4);

$ignoredDBErrorCodes = array(
	-18,
	-19,
	-5
);

/** Hides unknown errors
$ignoredDBErrorCodes = array(
	-18,
	-19,
	-1,
	-5
);
*/

/**
 * This is the class used to bring users software to the current version of the new Classified/Auction/Classauction codebase
 * Script execution typically begins at the default case of the switch found in Upgrade::init(). This shows a menu for the user to select
 * what upgrades they would like to perform. Every optional upgrade found in the upgrade homepage has a seperate function associated with it, and
 * eventually, every main upgrade will too. After the user chooses their upgrades and submits the form, their choices are stored in session variables
 * (see the setUpgradePath switch in Upgrade::init()). After this, each session variable directs the script to the appropriate pages until the session
 * variables are empty.
 *
 * <ul>
 * <li>
 * 	<b>Loading bars:</b>
 * 		If a function uses a loading bar, you MUST use Upgrade::Execute to run SQL queries for the bar to work effectively.
 * 		If you need to run a select statement to check for something in the database, send `true` as the second parameter to
 * 		Upgrade::Execute
 * </li>
 *
 * <li>
 * 	<b>Inserting new upgrade statements in major upgrade sections:</b>
 * 		The major update/upgrade functions are:
 * 			doToCAELatest()	-	ClassAuction/Classifieds/Auctions to latest (will eventually be broken into it's individual parts
 * </li>
 *
 * <li>
 * 	<b>Creating new main upgrades/updates:</b>
 * 		If the appropriate function does not exist, create a new function with an intuitive name, and make sure it has "do" at the beginning.
 * 		This is to easily keep track of all the actual upgrade/update routines
 *
 * 		Because these functions use a loading bar, for the bar to work effectively you MUST use the Upgrade::Execute function for any SQL queries.
 * 		Please read the comments for this function BEFORE using it.
 * </li>
 *
 * <li><b>Inserting upgrade statements in optional upgrade sections:</b>
 * 		Find the appropriate function, and add the appropriate code to the switch statement in Upgrade::runUpgrades().
 * 		At the time of this class's creation, no optional upgrades used a loading bar, so you can use Upgrade::$db::Execute to run queries.
 * </li>
 *
 * <li><b>Using Upgrade::tableExists and Upgrade::fieldExists:</b>
 * 		Should you ever need to check if a table exists in the database or if a field exists in a table, these two functions should come in handy
 * </li>
 *
 * <li><b>Using the overloaded Execute() function:</b>
 * 		Upgrade::Execute is used on top of the ADODB Execute() function to allow the loading bars to estimate the number of queries that will be run
 * 		After construction, Upgrade::state is set to "calculating". In this state, any calls to Upgrade::Execute() (that don't pass true as the
 * 		second parameter) will NOT return a result, but WILL increment the Upgrade::totalQueries property. The loading bar uses AJAX and the
 * 		Upgrade::Execute function to estimate how many queries it needs to run, then PHP switches Upgrade::state to "running" and actually runs
 * 		the SQL queries. Should you need to check for a condition in the database (eg: the values in a row), use Upgrade::Execute() with true
 * 		as the second parameter.
 * </li>
 *
 * <li>
 * 	<b>Connecting the database:</b>
 * 		Any new functions that need to access the database should call Upgrade::connectDB on the first line of the function. Upgrade::connectDB
 * 		checks if the ADODB db object exists, if it doesn't, it creates one as the 'db' property of Upgrade (Upgrade::$db)
 * </li>
 * </ul>
 * @package Upgrade
 * @todo Add backup/restore functionality
 */
class Upgrade {

	/**
	 * Latest version number of the unified codebase
	 *
	 * @var string
	 */
	var $versionNumber = "2.0.10b";
	/**
	 * Holds the ADODB db object
	 *
	 * @var object
	 */
	var $db;
	/**
	 * HTML code to be placed in the <head></head> tags
	 *
	 * @var string
	 */
	var $header;
	/**
	 * HTML code to be placed inside the onload='' attribute in the <body> tag
	 *
	 * @var string
	 */
	var $onload;
	/**
	 * HTML code placed in the <body></body> tags (may be encapsulated by some sort of template)
	 *
	 * @var string
	 */
	var $body;
	/**
	 * Determines if AJAX is calculating the number of queries it will run ("calculating"), or if it is actually running
	 * queries ("running").
	 *
	 * @var string
	 */
	var $state = "calculating";
	/**
	 * Estimated number of SQL queries that an upgrade will require. Used by AJAX to set the scale of the loading bar
	 *
	 * @var integer
	 */
	var $totalQueries = 0;
	/**
	 * Index of the current query in the total amount of queries. Initially 0 of {Upgrade::totalQueries}, eventually
	 * {Upgrade::totalQueries} of {Upgrade::totalQueries}
	 *
	 * @var integer
	 */
	var $currentQuery = 0;
	/**
	 * User ID used for authentication (which is currently disabled)
	 *
	 * @var integer
	 */
	var $userID = 0;
	/**
	 * Determines if the loading bar HTML still needs to be included
	 *
	 * @var bool
	 */
	var $loadingBarIncluded = false;
	/**
	 * Determines if the HTML for tooltips has already been included
	 *
	 * @var bool
	 */
	var $showingTooltips = false;
	/**
	 * Old product user is upgrading from
	 *
	 * @var CONST AUCTIONS, CLASSIFIEDS, or CLASSAUCTIONS
	 */
	var $oldProduct;
	/**
	 * New product user is upgrading to
	 *
	 * @var CONST AUCTIONS, CLASSIFIEDS, or CLASSAUCTIONS
	 */
	var $newProduct;
	/**
	 * Relative path to index.php of the upgrade. Used when embedding an upgrade in some other page (like setup)
	 *
	 * @var string
	 */
	var $relativePath = null;
	/**
	 * HTML code to use for the "Next button". May be ignored if an empty string.
	 *
	 * @var string
	 */
	var $nextButton = "";
	/**
	 * Is the script acting as an AJAX backend?
	 *
	 * @var bool
	 */
	var $ajax = false;
	
	/**
	 * Use this to redirect where PHP headers cannot
	 * 
	 * @var string
	 */
	var $redirect = "";


	/**
	 * Upgrade constructor.
	 *
	 * @param string $relativePath relative location of Upgrade when embedding the upgrade elsewhere
	 * @param string $nextButton HTML code used for the "Next" button at the end of each page
	 *
	 * @uses Upgrade::authenticate()
	 * @uses Upgrade::$relativePath
	 * @uses Upgrade::$nextButton
	 */
	function Upgrade($relativePath = "", $nextButton = "") {
		if($relativePath) {
		 	$this->relativePath = $relativePath;
			$this->nextButton = $nextButton;
		}
		if (defined('PHP5_DIR')){
			if (strlen(PHP5_DIR)>0){
				$this->db = DataAccess::getInstance();
			} else {
				$this->db =& DataAccess::getInstance();
			}
		} //else {
			//var_dump(PHP5_DIR);
		//}
	}

	/**
	 * Call this function to include an AJAX-based loading bar
	 *
	 * @return string HTML code that needs to be included for the loading bar
	 * @uses Upgrade::$loadingBarIncluded
	 * @uses Upgrade::$onload
	 * @uses Upgrade::$header
	 */
	function addLoadingBar($upgrade, $totalQueries) {
		if(!$this->loadingBarIncluded) {
			$this->onload .= 'sndReq();';
			$this->header = "
				<script type='text/javascript'>
					var debug = false;

					var intervalId;
					var pos = -50;
					var increment = 2;


					function createRequestObject() {
					    var ro;
					    var browser = navigator.appName;
					    if(browser == 'Microsoft Internet Explorer'){
					        ro = new ActiveXObject('Microsoft.XMLHTTP');
					    } else {
					        ro = new XMLHttpRequest();
					    }
					    return ro;
					}

					var http = createRequestObject();

					function sndReq() {
					    http.open('get', '".@$this->relativePath."index.php?step=ajax&upgrade=".$upgrade."&totalQueries=".$totalQueries."');
					    if('Microsoft Internet Explorer' == navigator.appName) {
			    			document.getElementById('progressBar').innerHTML = '<img src=\"".@$this->relativePath."progressBarIE.gif\">';
			    			document.getElementById('totalCounter').style.display = 'none';
			    			http.onreadystatechange = handleIEResponse;
					    } else {
					    	http.onreadystatechange = handleResponse;
					    }
					    http.send(null);
					}

					function handleResponse() {
					    if(3 == http.readyState && undefined == intervalId) {
			    			intervalId = setInterval('progressBar()', 500);
					    } else if(4 == http.readyState) {
					    	catchAJAXErrors(http.responseText);
					    	if(debug)
					    		alert(http.responseText);
					    	clearInterval(intervalId);
					   		setFinalState('100%');
					    }
					}

					function handleIEResponse() {
						if (4 == http.readyState) {
					    	catchAJAXErrors(http.responseText);
					    	clearInterval(intervalId);
					    	document.getElementById('progressBar').style.backgroundImage = 'url(versions/pre_2.0.10/progressBar.gif)';
					    	setFinalState('Done!');
					   	}
					}

					function catchAJAXErrors(output) {
						if(output.match(/[^|]/))
							document.getElementById('upgradeMsgBox').innerHTML += '<strong style=\'color: red;\'>Warning(s):</strong><br />\\n\\t'+output.replace(/\|/g, '');
					}
					function progressBar() {
						currentQuery = http.responseText.length;
					   	progress = 200-(currentQuery/$totalQueries)*200;
					   	percent = (currentQuery / {$this->totalQueries}) * 100;
					   	percent = percent > 100 ? 100 : percent;
					   	document.getElementById('progressBar').innerHTML =  percent.toFixed(0) + '%';
					   	document.getElementById('progressBar').style.backgroundPosition = '-'+progress+'px 0';
					   	total = document.getElementById('counter');
					   	if(currentQuery <= {$this->totalQueries})
					   		total.innerHTML = currentQuery;
					}

					function setFinalState(progressBarText) {
				    	document.getElementById('progressBar').style.backgroundPosition = '0 0';
				    	document.getElementById('progressBar').innerHTML = progressBarText;
				   		document.getElementById('counter').innerHTML = {$this->totalQueries};
						document.getElementById('nextButton').disabled = false;

					}
				</script>";
		}
		return "
			<span id='totalCounter'><span id='counter'>0</span> of {$this->totalQueries} queries</span>
			<div class=\"body_div\" id='progressBar' style='height:17px;border: 1px solid black;width:200px;margin:auto;background-image: url(".@$this->relativePath."progressBar.gif); background-color: #888; background-position: -200px 0;background-repeat:  no-repeat; color:black;'></div>
			<div class=\"body_div\" id='upgradeMsgBox' style='text-align: left;'></div>";
	}
	/**
	 * Auto-attach modules to pages found in templates
	 *
	 * @param integer $page_id page to attach the module to
	 * @param integer $language_id
	 * @param integer $template_id
	 * @uses Upgrade::connectDB()
	 * @uses Upgrade::$db::Execute()
	 * @uses Upgrade::$db::ErrorMsg()
	 */
	function attach_modules($page_id, $language_id, $template_id)	{
		$this->connectDB();

		$result = $this->db->Execute("select time_shift from geodesic_classifieds_configuration") or $this->$this->DBError(__LINE__);
		$row = $result->FetchRow();
		$shifted_time = time() + $row["time_shift"];

		// Attach modules that arent yet attached
		$query = "select template_code from geodesic_templates where template_id = $template_id";
		$result = $this->db->Execute($query) or $this->DBError(__LINE__);
		$current_template_info = $result->FetchRow();

		$query = "select module_replace_tag, page_id, name from geodesic_pages where module = 1";
		$result = $this->db->Execute($query) or $this->DBError(__LINE__);

		// Holds the IDs of the html modules attached
		$html_modules = array();

		// Get all module tags
		$module_replace_tag = $result->GetArray();
		for($i = 0; $i < sizeof($module_replace_tag); $i++)
		{
			if(eregi($module_replace_tag[$i]['module_replace_tag'], $current_template_info["template_code"]))
			{
				// First check if it is a HTML module
				if(eregi("HTML", $module_replace_tag[$i]['name']))
				{
					// If it is a HTML module add it to the html_modules array
					$html_modules[] = $module_replace_tag[$i]['page_id'];
				}

				// If replace tag is found in template then lets find out if its attached or not
				$query = "select * from geodesic_pages_modules where page_id = '$page_id' and module_id = ".$module_replace_tag[$i]['page_id'];
				$result = $this->db->Execute($query);
				if($result->RecordCount() == 0)
				{
					// If not attached insert into proper table to attach it
					$query = "insert into geodesic_pages_modules (module_id, page_id, time) values ({$module_replace_tag[$i]['page_id']}, '$page_id', ".$shifted_time.")";
					$this->db->Execute($query) or $this->DBError(__LINE__);
				}
				else
					continue;
			}
		}

		if(sizeof($html_modules) > 0)
		{
			// If any HTML Modules were found then lets check them out also.
			for($i = 0; $i < sizeof($module_replace_tag); $i++)
			{
				for($module_num = 0; $module_num < sizeof($html_modules); $module_num++)
				{
					$query = "select module_logged_in_html, module_logged_out_html from geodesic_pages where page_id = ".$html_modules[$module_num];
					$result = $this->db->Execute($query) or $this->DBError(__LINE__);

					$module_code = $result->FetchRow();

					// Check the logged in HTML
					if(eregi($module_replace_tag[$i]['module_replace_tag'], $module_code["module_logged_in_html"]))
					{
						// If replace tag is found in template then lets find out if its attached or not
						$query = "select * from geodesic_pages_modules where page_id = $page_id and module_id = ".$module_replace_tag[$i]['page_id'];
						$result = $this->db->Execute($query) or $this->DBError(__LINE__);
						if($result->RecordCount() == 0)
						{
							// If not attached insert into proper table to attach it
							$query = "insert into geodesic_pages_modules (module_id, page_id, time) values (".$module_replace_tag[$i]['page_id'].", '$page_id', ".$shifted_time.")";
							$this->db->Execute($query) or $this->DBError(__LINE__);
						}
					}

					// Check logged out HTML
					if(eregi($module_replace_tag[$i]['module_replace_tag'], $module_code["module_logged_out_html"]))
					{
						// If replace tag is found in template then lets find out if its attached or not
						$query = "select * from geodesic_pages_modules where page_id = '$page_id' and module_id = ".$module_replace_tag[$i]['page_id'];
						$result = $this->db->Execute($query) or $this->DBError(__LINE__);
						if($result->RecordCount() == 0)
						{
							// If not attached insert into proper table to attach it
							$query = "insert into geodesic_pages_modules (module_id, page_id, time) values (".$module_replace_tag[$i]['page_id'].", '$page_id', ".$shifted_time.")";
							$this->db->Execute($query) or $this->DBError(__LINE__);
						}
					}
				}
			}
		}
	}
	
	/**
	 * Execute multiple SQL queries
	 * $sql should be a string of SQL queries, one per line, each line ending with a semicolon
	 * Lines without semicolons as the last character will be ignored
	 *
	 * @param string $sql String of queries
	 */
	function batchExecute(&$sql) {
		$queries = array();
		preg_match_all("/([^;]+);([\n\r\t ]|$)+/S", $sql, $queries);

		foreach($queries[1] as $query) {
			if(!$this->Execute($query))
				$this->DBError(__LINE__);
		}
	}
	/**
	 * Connect the ADODB database at Upgrade::$db
	 * 
	 * @uses DataAccess::getInstance now uses the data access class for all db access.
	 */
	function connectDB()
	{
		include_once("../../../app_top.common.php");
		if(!isset($this->db))
		{
			if (strlen(PHP5_DIR)>0)
			{
				$this->db = DataAccess::getInstance();
			} else
			{
				$this->db =& DataAccess::getInstance();
			}
		}
		return $this->db;
	}
	/**
	 * Check that a priceplan exists for both Auctions and Classifieds
	 *
	 * @return bool
	 */
	function verifyPricePlans() {
		/*if(!$this->tableExists("geodesic_classifieds_price_plans"))
			die("Price plans SQL table doesn't exist");
		if(!$this->fieldExists("geodesic_classifieds_price_plans", "applies_to"))
			die("Price plans could not be verified");*/

		$query = "select count(*) as count from geodesic_classifieds_price_plans where applies_to = 1 limit 1";
		$result = $this->Execute($query, true);

		if($result === false)
			return false;

		$row = $result->FetchRow();
		if($row["count"] < 1)
			return false;

		$query = "select count(*) as count from geodesic_classifieds_price_plans where applies_to = 2 limit 1";
		$result = $this->Execute($query, true);

		if($result === false)
			return false;

		$row = $result->FetchRow();
		if($row["count"] < 1)
			return false;

		return true;
	}
	/**
	 * Add a price plan
	 * $type is a constant specifying what kind of priceplan to add
	 *
	 * @param CONST $type
	 */
	function addPricePlan($type) {
		if($type & AUCTIONS) {
			$this->Execute("insert into `geodesic_classifieds_price_plans` (name,description,type_of_billing, max_ads_allowed, applies_to) values ('general auction price plan','general auction price plan',1, 1000, 2)");
			$id = $this->db->Insert_ID();
			if(!$id && $this->state != "calculating")
				die("Could not determine the ID");
			$this->Execute("update geodesic_groups set auction_price_plan_id = $id");
			$this->Execute("update geodesic_user_groups_price_plans set auction_price_plan_id = $id");
		}

		if($type & CLASSIFIEDS) {
			$this->Execute("insert into `geodesic_classifieds_price_plans` (name,description,type_of_billing, max_ads_allowed, applies_to) values ('general auction price plan','general classified price plan',1, 1000, 1)") or $this->DBError(__LINE__);
			$id = $this->db->Insert_ID();
			if(!$id && $this->state != "calculating")
				die("Could not determine the price plan ID");
			$this->Execute("update geodesic_groups set price_plan_id = $id");
			$this->Execute("update geodesic_user_groups_price_plans set price_plan_id = $id");
		}
	}
	/**
	 * Add the 'license' field to the geodesic_classifieds_configuration table
	 *
	 * @uses Upgrade::$db::Execute()
	 */
	function addLicenseField() {
		$result = $this->Execute('alter table `geodesic_classifieds_configuration` add `license` text not null') or $this->DBError(__LINE__);
	}
	/**
	 * Upgrade classifieds 2.0.5+, Auctions 1.0.6+, or Classauctions to the latest version of Classauctions.
	 *
	 * @uses Upgrade::connectDB()
	 * @uses Upgrade::splitSqlFile()
	 * @uses Upgrade::Execute()
	 * @uses Upgrade::$state
	 * @uses Upgrade::encrypt()
	 * @uses Upgrade::$versionNumber
	 * @uses Upgrade::addPricePlans()
	 */
	function doToCAELatest($type=1) 
	{
		$this->connectDB();

		$this->prepareForNew();

		$sql_query = file_get_contents("sql/caeToLatest.sql");
		$pieces = array();
		$this->splitSqlFile($pieces, $sql_query);
		//Split SQL file already, executes each query so following isn't needed
		/*for ($i = 0; $i < count($pieces); $i++)
		 {
		          //$a_sql_query = $pieces[$i];
		          //echo $a_sql_query." is the query run<br>\n";
		          $result = $this->Execute($a_sql_query);
		} // end for
	*/
		//set the default smtp host
		$default_smtp_host = ini_get('SMTP');
		if ($default_smtp_host!=='localhost'){
			$default_type = 'smtp_standard';
		} else {
			$default_type = 'sendmail';
		}
		$default_smtp_port = ini_get('smtp_port');
		$sql_query ="ALTER TABLE `geodesic_classifieds_configuration` ADD `email_SMTP_server` VARCHAR( 64 ) NOT NULL default '$default_smtp_host' AFTER `email_configuration` ,
		ADD `email_username` VARCHAR( 64 ) NOT NULL AFTER `email_SMTP_server` ,
		ADD `email_password` VARCHAR( 64 ) NOT NULL AFTER `email_username` ;
		ALTER TABLE `geodesic_classifieds_configuration` ADD `email_server_type` ENUM( \"sendmail\", \"smtp_standard\", \"smtp_tls\", \"smtp_ssl\", \"smtp_auth_standard\", \"smtp_auth_tls\", \"smtp_auth_ssl\" ) NOT NULL DEFAULT '$default_type' AFTER `email_configuration` ;
		ALTER TABLE `geodesic_classifieds_configuration` ADD `email_SMTP_port` INT( 5 ) NOT NULL DEFAULT '$default_smtp_port' AFTER `email_SMTP_server` ;
"; //needs to end with ;\n or the last query will not work.
		
		if (!$this->db->get_site_setting('required_upgraded'))
		{	
			$this->db->set_site_setting('required_upgraded',1);		
		}
		
		$pieces = array();
		
		$this->splitSqlFile($pieces, $sql_query);//>batchExecute($sql_query);
		//var_dump($pieces);
		//echo "<span class = nongreen>General updates to database - </span><span class = green>success!</span><br>\n";

		////////////////////////
		include_once "arrays/insertFontTextAndMessages.php";

		reset ($insert_font_array);
		foreach (array_keys($insert_font_array) as $key)
		{
			$sql_query = "select * from geodesic_pages_fonts where element_id = ".$insert_font_array[$key][0];
			$test_result = $this->Execute($sql_query, true);
			if($this->state != "calculating") {
				if (false === $test_result)
					die("Error on ".__LINE__."query: $sql_query");
				elseif ($test_result->RecordCount() == 0)
				{
					$sql_query = "INSERT INTO geodesic_pages_fonts
						(element_id,page_id,element,name,description,font_family,font_size,font_style,font_weight,color,text_decoration,
						background_color,background_image,text_align,display_order)
						values
						(".$insert_font_array[$key][0].",".$insert_font_array[$key][1].",\"".$insert_font_array[$key][2]."\",\"".$insert_font_array[$key][3]."\",
						\"".$insert_font_array[$key][4]."\",\"".$insert_font_array[$key][5]."\",\"".$insert_font_array[$key][6]."\",\"".$insert_font_array[$key][7]."\"
						,\"".$insert_font_array[$key][8]."\",\"".$insert_font_array[$key][9]."\",\"".$insert_font_array[$key][10]."\",\"".$insert_font_array[$key][11]."\"
						,\"".$insert_font_array[$key][12]."\",\"".$insert_font_array[$key][13]."\",\"".$insert_font_array[$key][14]."\")";
					$insert_result = $this->Execute($sql_query);
					if("calculating" !== $this->state && !$insert_result)
						echo "Error on ".__LINE__;
				}
			}
			//else
			//	echo "font element key ".$insert_font_array[$key][0]." or ".$insert_font_array[$key][3]." - for page ".$insert_font_array[$key][1]." -  already exists - ".$test_result->RecordCount()."<bR>\n";
		}

		//echo "<span class = nongreen>Font additions - </span><span class = green>success!</span><br>";

		///////////////////

		//array (3213, 'Login+to+view+this+ad', 'Message+appearing+when+a+non-logged+in+user+tries+to+view+an+ad+when+the+only+subscribed+user+views+ads+is+activated.', '', 1, 0, 0),

		reset ($insert_text_array);
		foreach(array_keys($insert_text_array) as $key)
		{
			$sql_query = "select * from geodesic_pages_messages where message_id = ".$insert_text_array[$key][0];
			$test_result = $this->Execute($sql_query, true);
			if($this->state != "calculating") {
				if (!$test_result)
					die("Error on ".__LINE__);
				elseif ($test_result->RecordCount() == 0)
				{
					if (@strlen(trim($insert_text_array[$key][7])) == 0)
						$insert_text_array[$key][7] = 0;
					$sql_query = "insert into geodesic_pages_messages
						(message_id,name,description,text,page_id,display_order,classauctions)
						values
						(".$insert_text_array[$key][0].",\"".$insert_text_array[$key][1]."\",\"".$insert_text_array[$key][2]."\",\"".$insert_text_array[$key][3]."\",
						\"".$insert_text_array[$key][4]."\",\"".$insert_text_array[$key][5]."\",\"".$insert_text_array[$key][6]."\")";
					$insert_result = $this->Execute($sql_query);

					if ("calculating" !== $this->state && !$insert_result)
						die("Error on ".__LINE__);
				}
			}
			//else
				//echo "geodesic_pages_messages key ".$insert_text_array[$key][0]." of ".$insert_text_array[$key][1]." - already exists<bR>\n";
		}

		$sql_query = "update geodesic_pages_messages set
			name = \"Please choose a main category\"
			where message_id = 462";
		$update_result = $this->Execute($sql_query);
		if ("calculating" !== $this->state && !$update_result)
			die("Error on ".__LINE__);

		$sql_query = "update geodesic_pages_messages set
			name = \"category has\",
			description = \"Used in the statement explaining that the category chosen has subcategories so would the user choose one of the subcategories or affirm placement in the current category.\"
			where message_id = 463";
		$update_result = $this->Execute($sql_query);
		if ("calculating" !== $this->state && !$update_result)
			die("Error on ".__LINE__);

		/////////////////////////////

		include_once "arrays/messages.php";

		//array (1, 3213, 1, 'Please+login+first+to+view+this+ad.'),

		reset ($upgrade_array);
		$sql_query = "select language_id from geodesic_pages_languages";
		$language_result = $this->Execute($sql_query, true);
		if($this->state != "calculating") {
			if (!$language_result)
				die("Error on ".__LINE__);
			else
			{
				while ($show_language = $language_result->FetchRow())
				{
					reset($upgrade_array);
					foreach(array_keys($upgrade_array) as $key)
					{
						$sql_query = "select * from geodesic_pages_messages_languages where text_id = ".$upgrade_array[$key][1]." and language_id = ".$show_language["language_id"];
						$test_result = $this->Execute($sql_query, true);
						if (!$test_result)
							die("Error on ".__LINE__);
						elseif ($test_result->RecordCount() == 0)
						{
							$sql_query = "insert into geodesic_pages_messages_languages
								(page_id, text_id,language_id,text)
								values
								(".$upgrade_array[$key][0].",".$upgrade_array[$key][1].",\"".$show_language["language_id"]."\",\"".$upgrade_array[$key][3]."\")";
							$result = $this->Execute($sql_query);
							if ("calculating" !== $this->state && !$result)
								die("Error on ".__LINE__);
						}
						//else
						//	echo "geodesic_pages_messages_languages key ".$upgrade_array[$key][1]." of language ".$upgrade_array[$key][2]." - already exists<bR>\n";

					}
				}
			}
		}
		
		//storefront message tweaks
		$sql_query = "update geodesic_pages_messages set
			name = \"Please choose a main category\"
			where message_id = 462";
		$update_result = $this->Execute($sql_query);
		$sql_query = "update geodesic_pages_messages set
			name = \"category has\",
			description = \"Used in the statement explaining that the category chosen has subcategories so would the user choose one of the subcategories or affirm placement in the current category.\"
			where message_id = 463";
		$update_result = $this->Execute($sql_query);
		
		
		//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
		//ENSURE ALL TABLES CONTAINING CREDIT CARD NUMBERS ARE ENCRYPTED . . . IF NOT, EXTRACT, ENCRYPT
		//AND THEN RE-INSERT BOTH THE ENCRYPTED CC_NUMBER AND IT'S DECRYPTION_KEY
		//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
		//require_once '../classes/site_class.php';

		$cc_sql_query = "SELECT decryption_key FROM geodesic_classifieds_sell_session";
		$cc_result = $this->Execute($cc_sql_query);
		if ("calculating" !== $this->state && $cc_result == FALSE)
		{
			$cc_sql_query = "ALTER TABLE geodesic_classifieds_sell_session ADD decryption_key TINYTEXT NOT NULL; ";//echo $cc_sql_query." is query<br>";
			$sell_result = $this->Execute($cc_sql_query);
			if ("calculating" !== $this->state && $sell_result == FALSE)
		         break;
		}
		$cc_sql_query = "SELECT cc_number FROM geodesic_classifieds_sell_session";
		$select_result = $this->Execute($cc_sql_query);
		$cc_sql_query = "";
		if ("calculating" !== $this->state && $select_result->RecordCount() > 0)
		{
			while ($show = $select_result->FetchRow())
			{
				$unique_key = substr(md5(uniqid(rand(),1)), 0,strlen($show["cc_number"]));
				$encrypted_cc_num = $this->encrypt($show["cc_number"], $unique_key);
				$cc_sql_query .= "
					UPDATE geodesic_classifieds_sell_session SET
						cc_number = '".$encrypted_cc_num."',
						decryption_key = '".$unique_key."'
						WHERE cc_number = '".$show["cc_number"]."'; ";
			}
			$pieces = array();
			$this->splitSqlFile($pieces, $cc_sql_query);
		}

		//ADD THE DECRYPTION_KEY FIELD TO ALL TRANSACTION TABLES
		$cc_sql_query = "SELECT cc_transaction_table FROM geodesic_credit_card_choices";
		$cc_trans_result = $this->Execute($cc_sql_query);
		$cc_sql_query = "";

		if ("calculating" !== $this->state && $cc_trans_result->RecordCount() > 0)
		{
			while ($trans = $cc_trans_result->FetchRow())
			{
				$sel_sql_query = "SELECT card_num FROM ".$trans["cc_transaction_table"];
				$card_num_result = $this->Execute($sel_sql_query);
				$sel_sql_query = "SELECT decryption_key FROM ".$trans["cc_transaction_table"];
				$decrypt_key_result = $this->Execute($sel_sql_query);
				if ($card_num_result && !$decrypt_key_result)
				{
					$cc_sql_query .= "ALTER TABLE ".$trans["cc_transaction_table"]." ADD decryption_key TINYTEXT NOT NULL;\n";
					if ($card_num_result->RecordCount() > 0)
					{
						//THIS TABLE HAS THE card_num FIELD
						while ($show = $card_num_result->FetchRow())
						{
							//THIS RECORD HAS A CREDIT CARD NUMBER IN THE card_num FIELD
							$unique_key = substr(md5(uniqid(rand(),1)), 0,strlen($show["card_num"]));
							$encrypted_cc_num = $this->encrypt($show["card_num"], $unique_key);
							$cc_sql_query .= "
								UPDATE ".$trans["cc_transaction_table"]." SET
									card_num = '".$encrypted_cc_num."',
									decryption_key = '".$unique_key."'
									WHERE card_num = '".$show["card_num"]."';\n";
						}
					}
				}
			}
			$pieces = array();
			$this->splitSqlFile($pieces, $cc_sql_query);
		}
		//END 'ADD DECRYPTION_KEY' SCRIPT
		//////////////////////////////////////////////////////////////////

		//////////////////////
		//add Internet Secure payment processing
		$sql_query = "select * from geodesic_credit_card_choices where cc_id = 5";
		$bitel_result = $this->Execute($sql_query);
		if ("calculating" !== $this->state && $bitel_result->RecordCount() == 0)
		{
			$sql_query = "INSERT INTO geodesic_credit_card_choices (cc_id, chosen_cc, name, explanation, cc_table, cc_transaction_table, cc_initiate_file, cc_process_file, cc_admin_file) VALUES (5, 0, 'Internet Secure', 'This allows the use of the Internet Secure payment gateway', 'geodesic_cc_internetsecure', 'geodesic_cc_internetsecure_transactions', 'cc_initiate_internetsecure.php', '', 'admin_cc_internetsecure.php')";
			$bitel_result = $this->Execute($sql_query);
		}
		$sql_query = "select * from geodesic_cc_internetsecure";
		$internetsecure_result = $this->Execute($sql_query);
		if (!$internetsecure_result)
		{
			$sql_query = "CREATE TABLE `geodesic_cc_internetsecure` (
					`merchantnumber` tinytext NOT NULL default '',
					`language` tinytext NOT NULL default '',
					`demo_mode` tinytext NOT NULL default '',
					`canadian_tax_method` tinytext NOT NULL default ''
				) TYPE=MyISAM";
			$internetsecure_result = $this->Execute($sql_query);

			$sql_query = "INSERT INTO `geodesic_cc_internetsecure` VALUES ('###', 'english', 'http://www.yoursite.com', '')";
			$internetsecure_result = $this->Execute($sql_query);
		}

		$sql_query = "select * from geodesic_cc_internetsecure_transactions";
		$internetsecure_select_result = $this->Execute($sql_query);
		if (!$internetsecure_select_result)
		{
			$sql_query = "CREATE TABLE `geodesic_cc_internetsecure_transactions` (
					`internetsecure_transaction_id` int(11) NOT NULL auto_increment,
					`classified_id` int(11) NOT NULL default '0',
					`userID` int(11) NOT NULL default '0',
					`credit_card_processed` int(11) NOT NULL default '0',
					`first_name` tinytext NOT NULL default '',
					`last_name` tinytext NOT NULL default '',
					`address` tinytext NOT NULL default '',
					`city` tinytext NOT NULL default '',
					`state` tinytext NOT NULL default '',
					`country` tinytext NOT NULL default '',
					`zip` varchar(15) NOT NULL default '',
					`email` tinytext NOT NULL default '',
					`card_num` varchar(25) NOT NULL default '',
					`decryption_key` varchar(25) NOT NULL default '',
					`exp_date` varchar(10) NOT NULL default '',
					`cvv2_code` varchar( 4 ) NOT NULL default '',
					`tax` double(5,2) NOT NULL default '0.00',
					`amount` double(5,2) NOT NULL default '0.00',
					`phone` varchar(20) NOT NULL default '',
					`fax` varchar(20) NOT NULL default '',
					`company` tinytext NOT NULL default '',
					`description` tinytext NOT NULL default '',
					`merchantnumber` tinytext NOT NULL default '',
					`currency` tinytext NOT NULL default '',
					`salesordernumber` tinytext NOT NULL default '',
					`receipt_number` tinytext NOT NULL default '',
					`approvalcode` tinytext NOT NULL default '',
					`verbage` tinytext NOT NULL default '',
					`niceverbage` tinytext NOT NULL default '',
					`cvv2result` tinytext NOT NULL default '',
					`avsresponsecode` tinytext NOT NULL default '',
					`products` tinytext NOT NULL default '',
					`doublecolonproducts` tinytext NOT NULL default '',
					`language` tinytext NOT NULL default '',
					`keysize` tinytext NOT NULL default '',
					`secretkeysize` tinytext NOT NULL default '',
					`useragent` tinytext NOT NULL default '',
					`entrytimestamp` tinytext NOT NULL default '',
					`unixtimestamp` tinytext NOT NULL default '',
					`timestamp` tinytext NOT NULL default '',
					`live` tinytext NOT NULL default '',
					`refererurl` tinytext NOT NULL default '',
					`ipaddress` tinytext NOT NULL default '',
					`returnurl` tinytext NOT NULL default '',
					`returncgi` tinytext NOT NULL default '',
					`var1` tinytext NOT NULL default '',
					`var2` tinytext NOT NULL default '',
					`var3` tinytext NOT NULL default '',
					`var4` tinytext NOT NULL default '',
					`var5` tinytext NOT NULL default '',
					`ad_placement` int(11) NOT NULL default '0',
					`renew` int(11) NOT NULL default '0',
					`bolding` int(11) NOT NULL default '0',
					`better_placement` int(11) NOT NULL default '0',
					`featured_ad` int(11) NOT NULL default '0',
					`featured_ad_2` int(11) NOT NULL default '0',
					`featured_ad_3` int(11) NOT NULL default '0',
					`featured_ad_4` int(11) NOT NULL default '0',
					`featured_ad_5` int(11) NOT NULL default '0',
					`attention_getter` int(11) NOT NULL default '0',
					`attention_getter_choice` int(11) NOT NULL default '0',
					`renewal_length` int(11) NOT NULL default '0',
					`use_credit_for_renewal` int(11) NOT NULL default '0',
					`subscription_renewal` int(11) NOT NULL default '0',
					`price_plan_id` int(11) NOT NULL default '0',
					`account_balance` int(11) NOT NULL default '0',
					UNIQUE KEY `internetsecure_transaction_id` (`internetsecure_transaction_id`)
				) TYPE=MyISAM AUTO_INCREMENT=1 ";

			$internetsecure_result = $this->Execute($sql_query);
		}

		//echo "<br><b>Internet Secure Transaction Ability Installed<br>";

		//////////////////////
		//add cc payflow pro payment processing
		$sql_query = "select * from geodesic_credit_card_choices where cc_id = 6";
		$cc_choices_payflow_pro_result = $this->Execute($sql_query);
		if ("calculating" !== $this->state && $cc_choices_payflow_pro_result->RecordCount() == 0)
		{
			$sql_query = "

				INSERT INTO geodesic_credit_card_choices (cc_id, chosen_cc, name, explanation, cc_table, cc_transaction_table, cc_initiate_file, cc_process_file, cc_admin_file) VALUES (6, 0, 'Verisign Payflow Pro', 'This allows the use of the Payflow Pro for credit card processing.', 'geodesic_cc_payflow_pro', 'geodesic_cc_payflow_pro_transactions', 'cc_initiate_payflow_pro.php', '', 'admin_cc_payflow_pro.php')";

			$cc_choices_payflow_pro_result = $this->Execute($sql_query);
		}
		$sql_query = "select * from geodesic_cc_payflow_pro";
		$payflow_result = $this->Execute($sql_query);
		if (!$payflow_result)
		{
			$sql_query = "CREATE TABLE `geodesic_cc_payflow_pro` (
				  `partner` tinytext NOT NULL,
				  `vendor` tinytext NOT NULL,
				  `user` tinytext NOT NULL,
				  `password` tinytext NOT NULL,
				  `demo_mode` int(11) NOT NULL default '0'
				) TYPE=MyISAM";
			$this->Execute($sql_query);

			$sql_query = "INSERT INTO `geodesic_cc_payflow_pro` (`partner`,`vendor`,`user`,`password`,`demo_mode`) VALUES ('VeriSign', '###', '###','###',1)";
			$this->Execute($sql_query);
		}
		$sql_query = "select * from geodesic_cc_payflow_pro_transactions";
		$payflow_select_result = $this->Execute($sql_query);
		if (!$payflow_select_result)
		{
			$sql_query = "CREATE TABLE `geodesic_cc_payflow_pro_transactions` (
					`payflow_pro_transaction_id` int(11) NOT NULL auto_increment,
					`classified_id` int(11) NOT NULL default '0',
					`userID` int(11) NOT NULL default '0',
					`first_name` tinytext NOT NULL default '',
					`last_name` tinytext NOT NULL default '',
					`address` tinytext NOT NULL default '',
					`city` tinytext NOT NULL default '',
					`state` tinytext NOT NULL default '',
					`country` tinytext NOT NULL default '',
					`zip` varchar(15) NOT NULL default '',
					`email` tinytext NOT NULL default '',
					`card_num` varchar(25) NOT NULL default '',
					`decryption_key` varchar(25) NOT NULL default '',
					`exp_date` varchar(10) NOT NULL default '',
					`tax` double(5,2) NOT NULL default '0.00',
					`amount` double(5,2) NOT NULL default '0.00',
					`fax` varchar(20) NOT NULL default '',
					`company` tinytext NOT NULL default '',
					`description` tinytext NOT NULL default '',
					`pnref` tinytext NOT NULL default '',
					`result` int(11) NOT NULL default '0',
					`respmsg` tinytext NOT NULL default '',
					`authcode` tinytext NOT NULL default '',
					`avsaddr` tinytext NOT NULL default '',
					`avszip` tinytext NOT NULL default '',
					`trans_id` tinytext NOT NULL default '',
					`ad_placement` int(11) NOT NULL default '0',
					`renew` int(11) NOT NULL default '0',
					`bolding` int(11) NOT NULL default '0',
					`better_placement` int(11) NOT NULL default '0',
					`featured_ad` int(11) NOT NULL default '0',
					`featured_ad_2` int(11) NOT NULL default '0',
					`featured_ad_3` int(11) NOT NULL default '0',
					`featured_ad_4` int(11) NOT NULL default '0',
					`featured_ad_5` int(11) NOT NULL default '0',
					`attention_getter` int(11) NOT NULL default '0',
					`attention_getter_choice` int(11) NOT NULL default '0',
					`renewal_length` int(11) NOT NULL default '0',
					`use_credit_for_renewal` int(11) NOT NULL default '0',
					`subscription_renewal` int(11) NOT NULL default '0',
					`price_plan_id` int(11) NOT NULL default '0',
					`account_balance` int(11) NOT NULL default '0',
					`pay_invoice` int(11) NOT NULL default '0',
					UNIQUE KEY `payflow_pro_transaction_id` (`payflow_pro_transaction_id`)
				) TYPE=MyISAM";

			$this->Execute($sql_query);
		}

		//////////////////////
		//add cc paypal payment processing
		$sql_query = "select * from geodesic_credit_card_choices where cc_id = 7";
		$cc_choices_paypal_result = $this->Execute($sql_query);
		if ("calculating" !== $this->state && $cc_choices_paypal_result->RecordCount() == 0)
		{
			$sql_query = "

				INSERT INTO geodesic_credit_card_choices (cc_id, chosen_cc, name, explanation, cc_table, cc_transaction_table, cc_initiate_file, cc_process_file, cc_admin_file) VALUES (7, 0, 'PayPal Pro', 'This allows the use of the PayPal Website Payments Pro for credit card processing.', 'geodesic_cc_paypal', 'geodesic_cc_paypal_transactions', 'cc_initiate_paypal.php', '', 'admin_cc_paypal.php')";

			$cc_choices_paypal_result = $this->Execute($sql_query);
		}
		$sql_query = "select * from geodesic_cc_paypal_transactions";
		$cc_paypal_trans_select_result = $this->Execute($sql_query);
		if (!$cc_paypal_trans_select_result)
		{
			$sql_query = "CREATE TABLE `geodesic_cc_paypal_transactions` (
					`transaction_id` int( 11 ) NOT NULL AUTO_INCREMENT ,
					`classified_id` int( 11 ) NOT NULL default '0',
					`userID` int( 11 ) NOT NULL default '0',
					`first_name` tinytext NOT NULL ,
					`last_name` tinytext NOT NULL ,
					`address` tinytext NOT NULL ,
					`city` tinytext NOT NULL ,
					`state` tinytext NOT NULL ,
					`country` tinytext NOT NULL ,
					`zip` varchar( 15 ) NOT NULL default '',
					`email` tinytext NOT NULL ,
					`card_num` varchar( 25 ) NOT NULL default '',
					`decryption_key` varchar(25) NOT NULL default '',
					`exp_date` varchar( 10 ) NOT NULL default '',
					`tax` double( 5, 2 ) NOT NULL default '0.00',
					`fax` varchar( 20 ) NOT NULL default '',
					`company` tinytext NOT NULL ,
					`description` tinytext NOT NULL ,
					`amount` double( 5, 2 ) NOT NULL default '0.00',
					`avs_code` varchar( 4 ) NOT NULL default '',
					`cvv2_code` varchar( 4 ) NOT NULL default '',
					`trans_id` tinytext NOT NULL ,
					`timestamp` tinytext NOT NULL ,
					`ack` tinytext NOT NULL default '',
					`version` tinytext NOT NULL default '',
					`build` tinytext NOT NULL default '',
					`error_short_msg` tinytext NOT NULL default '',
					`error_long_msg` mediumtext NOT NULL default '',
					`error_code` tinytext NOT NULL default '',
					`error_severity_code` tinytext NOT NULL default '',
					`ad_placement` int( 11 ) NOT NULL default '0',
					`renew` int( 11 ) NOT NULL default '0',
					`bolding` int( 11 ) NOT NULL default '0',
					`better_placement` int( 11 ) NOT NULL default '0',
					`featured_ad` int( 11 ) NOT NULL default '0',
					`featured_ad_2` int( 11 ) NOT NULL default '0',
					`featured_ad_3` int( 11 ) NOT NULL default '0',
					`featured_ad_4` int( 11 ) NOT NULL default '0',
					`featured_ad_5` int( 11 ) NOT NULL default '0',
					`attention_getter` int( 11 ) NOT NULL default '0',
					`attention_getter_choice` int( 11 ) NOT NULL default '0',
					`renewal_length` int( 11 ) NOT NULL default '0',
					`use_credit_for_renewal` int( 11 ) NOT NULL default '0',
					`subscription_renewal` int( 11 ) NOT NULL default '0',
					`price_plan_id` int( 11 ) NOT NULL default '0',
					`account_balance` int( 11 ) NOT NULL default '0',
					`pay_invoice` int( 11 ) NOT NULL default '0',
					`auction_id` int( 11 ) NOT NULL default '0',
					UNIQUE KEY `transaction_id` ( `transaction_id` )
				) TYPE = MYISAM";
			$this->Execute($sql_query);
		}
		$sql_query = "select * from geodesic_cc_paypal";
		$cc_paypal_select_result = $this->Execute($sql_query);
		if (!$cc_paypal_select_result)
		{
			$sql_query = "CREATE TABLE `geodesic_cc_paypal` (
					`api_username` tinytext NOT NULL ,
					`api_password` tinytext NOT NULL ,
					`certfile` tinytext NOT NULL ,
					`currency_id` tinytext NOT NULL ,
					`charset` tinytext NOT NULL
				) TYPE = MYISAM";
			$this->Execute($sql_query);

			$sql_query = "INSERT INTO `geodesic_cc_paypal`
				(api_username, api_password, certfile, currency_id,	charset) VALUES
				('', '', '', 'USD', 'iso-8859-1')";
			$this->Execute($sql_query);
		}
		//echo "<br><b>Internet CC PayPal Installed<br>";

		/////////////////////
		//add cc nochex payment processing
		$sql_query = "select * from geodesic_payment_choices where payment_choice_id = 8";
		$payment_choices_nochex_result = $this->Execute($sql_query);
		if ("calculating" !== $this->state && $payment_choices_nochex_result->RecordCount() == 0)
		{
			$sql_query = "
				INSERT INTO `geodesic_payment_choices` ( `payment_choice_id` , `name` , `explanation` , `accepted` , `type` )
					VALUES ('8', 'NOCHEX', 'Use NOCHEX to accept payments. (For UK customers only)', '0', '8')";
			$payment_choices_nochex_result = $this->Execute($sql_query);
		}
		$sql_query = "select * from geodesic_nochex";
		$nochex_result = $this->Execute($sql_query);
		if (!$nochex_result)
		{
			$sql_query = "CREATE TABLE `geodesic_nochex` (
					`demo_mode` int( 1 ) NOT NULL default '0',
					`logo_path` tinytext NOT NULL,
					`email` tinytext NOT NULL
				) TYPE=MyISAM";
			$this->Execute($sql_query);

			$sql_query = "INSERT INTO `geodesic_nochex`
				(demo_mode,logo_path,email) VALUES
				('0','/path/to/your/logo','nochex_accnt_holder@somedomain.com')";
			$this->Execute($sql_query);

		}
		$sql_query = "select * from geodesic_nochex_transactions";
		$nochex_select_result = $this->Execute($sql_query);
		if (!$nochex_select_result)
		{
			$sql_query = "CREATE TABLE `geodesic_nochex_transactions` (
					`nochex_transaction_id` int(11) NOT NULL auto_increment,
					`classified_id` int(11) NOT NULL default '0',
					`userID` int(11) NOT NULL default '0',
					`first_name` tinytext NOT NULL default '',
					`last_name` tinytext NOT NULL default '',
					`address` tinytext NOT NULL default '',
					`city` tinytext NOT NULL default '',
					`state` tinytext NOT NULL default '',
					`country` tinytext NOT NULL default '',
					`zip` varchar(15) NOT NULL default '',
					`email` tinytext NOT NULL default '',
					`tax` double(5,2) NOT NULL default '0.00',
					`amount` double(5,2) NOT NULL default '0.00',
					`fax` varchar(20) NOT NULL default '',
					`company` tinytext NOT NULL default '',
					`description` tinytext NOT NULL default '',
					`response` tinytext NOT NULL default '',
					`security_key` tinytext NOT NULL default '',
					`transaction_date` tinytext NOT NULL default '',
					`ad_placement` int(11) NOT NULL default '0',
					`renew` int(11) NOT NULL default '0',
					`bolding` int(11) NOT NULL default '0',
					`better_placement` int(11) NOT NULL default '0',
					`featured_ad` int(11) NOT NULL default '0',
					`featured_ad_2` int(11) NOT NULL default '0',
					`featured_ad_3` int(11) NOT NULL default '0',
					`featured_ad_4` int(11) NOT NULL default '0',
					`featured_ad_5` int(11) NOT NULL default '0',
					`attention_getter` int(11) NOT NULL default '0',
					`attention_getter_choice` int(11) NOT NULL default '0',
					`renewal_length` int(11) NOT NULL default '0',
					`use_credit_for_renewal` int(11) NOT NULL default '0',
					`subscription_renewal` int(11) NOT NULL default '0',
					`price_plan_id` int(11) NOT NULL default '0',
					`account_balance` int(11) NOT NULL default '0',
					`pay_invoice` int(11) NOT NULL default '0',
					UNIQUE KEY `nochex_transaction_id` (`nochex_transaction_id`)
				) TYPE=MyISAM";

			$this->Execute($sql_query);
		}

		/////////////////////
		//add cc MANUAL payment processing
		$sql_query = "select * from geodesic_credit_card_choices where cc_id = 9";
		$payment_choices_nochex_result = $this->Execute($sql_query);
		if ("calculating" !== $this->state && $payment_choices_nochex_result->RecordCount() == 0)
		{
			$sql_query = "

				INSERT INTO geodesic_credit_card_choices (cc_id, chosen_cc, name, explanation, cc_table, cc_transaction_table, cc_initiate_file, cc_process_file, cc_admin_file) VALUES (9, 0, 'Manual', 'This will simply save the transaction details as well as store the credit data for future use.', '', 'geodesic_cc_manual_transactions', 'cc_initiate_manual.php', '', 'admin_cc_manual.php')";

			$payment_choices_nochex_result = $this->Execute($sql_query);
		}

		$sql_query = "select * from geodesic_cc_manual_transactions";
		$nochex_select_result = $this->Execute($sql_query);
		if (!$nochex_select_result)
		{
			$sql_query = "CREATE TABLE `geodesic_cc_manual_transactions` (
					`manual_transaction_id` int(11) NOT NULL auto_increment,
					`classified_id` int(11) NOT NULL default '0',
					`userID` int(11) NOT NULL default '0',
					`card_num` varchar(25) NOT NULL default '',
					`decryption_key` varchar(25) NOT NULL default '',
					`exp_date` varchar(10) NOT NULL default '',
					`first_name` tinytext NOT NULL default '',
					`last_name` tinytext NOT NULL default '',
					`address` tinytext NOT NULL default '',
					`city` tinytext NOT NULL default '',
					`state` tinytext NOT NULL default '',
					`country` tinytext NOT NULL default '',
					`zip` varchar(15) NOT NULL default '',
					`email` tinytext NOT NULL default '',
					`tax` double(5,2) NOT NULL default '0.00',
					`amount` double(5,2) NOT NULL default '0.00',
					`fax` varchar(20) NOT NULL default '',
					`company` tinytext NOT NULL default '',
					`description` tinytext NOT NULL default '',
					`ad_placement` int(11) NOT NULL default '0',
					`renew` int(11) NOT NULL default '0',
					`bolding` int(11) NOT NULL default '0',
					`better_placement` int(11) NOT NULL default '0',
					`featured_ad` int(11) NOT NULL default '0',
					`featured_ad_2` int(11) NOT NULL default '0',
					`featured_ad_3` int(11) NOT NULL default '0',
					`featured_ad_4` int(11) NOT NULL default '0',
					`featured_ad_5` int(11) NOT NULL default '0',
					`attention_getter` int(11) NOT NULL default '0',
					`attention_getter_choice` int(11) NOT NULL default '0',
					`renewal_length` int(11) NOT NULL default '0',
					`use_credit_for_renewal` int(11) NOT NULL default '0',
					`subscription_renewal` int(11) NOT NULL default '0',
					`price_plan_id` int(11) NOT NULL default '0',
					`account_balance` int(11) NOT NULL default '0',
					`pay_invoice` int(11) NOT NULL default '0',
					UNIQUE KEY `manual_transaction_id` (`manual_transaction_id`)
				) TYPE=MyISAM";

			$manual_create_result = $this->Execute($sql_query);
			if ("calculating" !== $this->state && !$manual_create_result) echo "<br><font color=red>error in creating - geodesic_cc_manual_transactions</font><br>QUERY - $sql_query<BR>";
		}
		
		////////add paymentexpress.com
		//////////////////////
		//add cc paypal payment processing
		$sql_query = "select * from geodesic_credit_card_choices where cc_id = 10";
		$cc_choices_paypal_result = $this->Execute($sql_query);
		if ("calculating" !== $this->state && $cc_choices_paypal_result->RecordCount() == 0)
		{
			$sql_query = "

				INSERT INTO geodesic_credit_card_choices (cc_id, chosen_cc, name, explanation, cc_table, cc_transaction_table, cc_initiate_file, cc_process_file, cc_admin_file) VALUES (10, 0, 'Paymentexpress.com', 'This allows the use of the Paymentexpress.com for credit card processing.', 'geodesic_cc_paymentexpress', 'geodesic_cc_paymentexpress_transactions', 'cc_initiate_paymentexpress.php', '', 'admin_cc_paymentexpress.php')";

			$cc_choices_paypal_result = $this->Execute($sql_query);
		}
		$sql_query = "select * from geodesic_cc_paymentexpress_transactions";
		$cc_paypal_trans_select_result = $this->Execute($sql_query);
		if (!$cc_paypal_trans_select_result)
		{
			$sql_query = "CREATE TABLE `geodesic_cc_paymentexpress_transactions` (
				  `transaction_id` int(11) NOT NULL auto_increment,
				  `classified_id` int(11) NOT NULL default '0',
				  `user_id` int(11) NOT NULL default '0',
				  `first_name` tinytext NOT NULL,
				  `last_name` tinytext NOT NULL,
				  `address` tinytext NOT NULL,
				  `city` tinytext NOT NULL,
				  `state` tinytext NOT NULL,
				  `country` tinytext NOT NULL,
				  `zip` varchar(15) NOT NULL default '',
				  `email` tinytext NOT NULL,
				  `card_num` varchar(25) NOT NULL default '',
				  `decryption_key` tinytext NOT NULL,
				  `exp_date` varchar(10) NOT NULL default '',
				  `tax` double(5,2) NOT NULL default '0.00',
				  `amount` double(5,2) NOT NULL default '0.00',
				  `fax` varchar(20) NOT NULL default '',
				  `company` tinytext NOT NULL,
				  `description` tinytext NOT NULL,
				  `response_code` tinyint(4) NOT NULL default '0',
				  `response_subcode` tinytext NOT NULL,
				  `response_reason_code` tinytext NOT NULL,
				  `response_reason_text` tinytext NOT NULL,
				  `auth_code` int(11) NOT NULL default '0',
				  `avs_code` varchar(4) NOT NULL default '',
				  `trans_id` tinytext NOT NULL,
				  `md5_hash` tinytext NOT NULL,
				  `ad_placement` int(11) NOT NULL default '0',
				  `renew` int(11) NOT NULL default '0',
				  `bolding` int(11) NOT NULL default '0',
				  `better_placement` int(11) NOT NULL default '0',
				  `featured_ad` int(11) NOT NULL default '0',
				  `featured_ad_2` int(11) NOT NULL default '0',
				  `featured_ad_3` int(11) NOT NULL default '0',
				  `featured_ad_4` int(11) NOT NULL default '0',
				  `featured_ad_5` int(11) NOT NULL default '0',
				  `attention_getter` int(11) NOT NULL default '0',
				  `attention_getter_choice` int(11) NOT NULL default '0',
				  `renewal_length` int(11) NOT NULL default '0',
				  `use_credit_for_renewal` int(11) NOT NULL default '0',
				  `subscription_renewal` int(11) NOT NULL default '0',
				  `price_plan_id` int(11) NOT NULL default '0',
				  `account_balance` int(11) NOT NULL default '0',
				  `pay_invoice` int(11) NOT NULL default '0',
				  `auction_id` int(11) NOT NULL default '0',
				  UNIQUE KEY `transaction_id` (`transaction_id`)
				) TYPE=MyISAM AUTO_INCREMENT=1 ;";
			$this->Execute($sql_query);
		}
		$sql_query = "select * from geodesic_cc_paymentexpress";
		$cc_paypal_select_result = $this->Execute($sql_query);
		if (!$cc_paypal_select_result)
		{
			$sql_query = "CREATE TABLE `geodesic_cc_paymentexpress` (
  				`userid` tinytext NOT NULL,
 				`access_key` tinytext NOT NULL default '',
  				`mac_key` tinytext NOT NULL default '',
  				`currency_type` tinytext NOT NULL,
  				`email_address` tinytext NOT NULL
				) TYPE=MyISAM";
			$this->Execute($sql_query);

			$sql_query = "INSERT INTO `geodesic_cc_paymentexpress`
				(userid, access_key, mac_key, currency_type,	email_address) VALUES
				('', '', '', 'USD', '')";
			$this->Execute($sql_query);
		}
		//echo "<br><b>Internet CC Paymentexress Installed<br>";		

		// Check for feedback icons
		$sql_query = "select * from geodesic_auctions_feedback_icons";
		$result = $this->Execute($sql_query);
		if("calculating" !== $this->state && $result->RecordCount() == 0)
		{
			//insert icons
			$sql_query = "INSERT INTO `geodesic_auctions_feedback_icons` VALUES ('images/feedback/star_red2_glow.gif', 9, 10000, 100000000);
				INSERT INTO `geodesic_auctions_feedback_icons` VALUES ('images/feedback/star_orange2_glow.gif', 8, 7500, 9999);
				INSERT INTO `geodesic_auctions_feedback_icons` VALUES ('images/feedback/star_blue2_glow.gif', 7, 5000, 7499);
				INSERT INTO `geodesic_auctions_feedback_icons` VALUES ('images/feedback/star_green2_glow.gif', 6, 2500, 4999);
				INSERT INTO `geodesic_auctions_feedback_icons` VALUES ('images/feedback/star_red2.gif', 5, 1000, 2499);
				INSERT INTO `geodesic_auctions_feedback_icons` VALUES ('images/feedback/star_orange2.gif', 4, 500, 999);
				INSERT INTO `geodesic_auctions_feedback_icons` VALUES ('images/feedback/star_blue2.gif', 3, 250, 499);
				INSERT INTO `geodesic_auctions_feedback_icons` VALUES ('images/feedback/star_green2.gif', 2, 100, 249);
				INSERT INTO `geodesic_auctions_feedback_icons` VALUES ('images/feedback/star_gray2.gif', 0, 0, 0);
				INSERT INTO `geodesic_auctions_feedback_icons` VALUES ('images/feedback/star_black2.gif', 0, -1, 0);
				INSERT INTO `geodesic_auctions_feedback_icons` VALUES ('images/feedback/star_purple2.gif', 1, 50, 99);
				INSERT INTO `geodesic_auctions_feedback_icons` VALUES ('images/feedback/star_ltblue2.gif', 0, 1, 49);";
		$pieces = array();
		$this->splitSqlFile($pieces, $sql_query);

		}
		else
		{
			//icons already there...do not insert any
		}
		
		//set default site wide settings.
		$this->setDefaultSiteSettings();
		
		// Check for version number
		//$sql_query = "select * from geodesic_version";
		//$result = $this->Execute($sql_query);
		$old_version = $this->getOldVersion();
		if("calculating" !== $this->state && !$old_version)
		{
			$sql_query = "insert into geodesic_version (db_version) values (\"".$this->versionNumber."\")";
			$this->Execute($sql_query);
		}
		else
		{
			$sql_query = "update geodesic_version set db_version = \"".$this->versionNumber."\"";
			$this->Execute($sql_query);
		}





		// ------------------------------------------------------------------------------------------------
		// ------------------------------------------------------------------------------------------------
		// Classifieds -> ClassAuctions Update statements
		// ------------------------------------------------------------------------------------------------
		// ------------------------------------------------------------------------------------------------

		$this->fixListingTypes($type);
		//echo "<span class = nongreen>Converting old data - </span>";

		// ------------------------------------------------------------------------------------------------
		// ------------------------------------------------------------------------------------------------
		// ------------------------------------------------------------------------------------------------
		// ------------------------------------------------------------------------------------------------

		/*echo "<span class = green>success!</span>";
		echo "<span class = nongreen><br>Database/Software version is now <b>".$this->versionNumber."</b></span><br>";
		echo "<br><b><span class = green>UPGRADE COMPLETE</span></b><br>";*/
		if(!$this->verifyPricePlans())
			$this->addPricePlan(AUCTIONS);
		$this->addNewPages();
		if(!$this->fieldExists("geodesic_classifieds_configuration", "license"))
			$this->addLicenseField();
	} //end of doToCAELatest
	
	/**
	 * Main function for Auctions to ClassAuctions upgrade
	 *
	 * @uses AuctionsUpgrade
	 */
	function doOldAEToCAE() {
		$upgrade = new AuctionsUpgrade($this->connectDB());
		$upgrade->state = $this->state;
		$upgrade->totalQueries = $this->totalQueries;
		$upgrade->ajax = $this->ajax;
		$upgrade->convertStructure();
		$upgrade->mapText();
		$upgrade->mapPages();
		$upgrade->mapFonts();
		$upgrade->mapSections();
		$upgrade->rewriteTemplates();
		$upgrade->rewritePages();
		$upgrade->rewriteText();
		$upgrade->renumberModuleSections();
		$upgrade->resetPassword();
		if(!$upgrade->verifyPricePlans())
			$upgrade->addPricePlan(CLASSIFIEDS);
		$upgrade->insertData();
		$upgrade->cleanup();
		$upgrade->doToCAELatest(2);
		$this->addNewPages();
		if(!$this->fieldExists("geodesic_classifieds_configuration", "license"))
			$this->addLicenseField();
		$this->totalQueries += $upgrade->totalQueries;
	}
	/**
	 * Let the user attach templates to the new Classauction pages.
	 *
	 * @return bool TRUE if this function needs to be run again
	 * @uses Upgrade::connectDB()
	 * @uses Upgrade::$body
	 * @uses Upgrade::$db::Execute()
	 * @uses Upgrade::$db::ErrorMsg()
	 * @uses Upgrade::attach_modules()
	 */
	function doAddCATemplates() {
		$this->connectDB();

		$bg_color = "#A5CCEB";
		$dark_border_color = "#000";

		$this->body .= "
					<p style='background-color:$bg_color;border:1px solid $dark_border_color;-moz-border-radius:5px;'>
						Add new ClassAuction templates
					</p>";

		if(isset($_POST["action"]) && isset($_POST['templates']))
		{
			$messages = "";
			foreach($_POST["templates"] as $language_id => $templates_array)
			{
				foreach($templates_array as $page_id => $template_id)
				{
					if($page_id && $language_id && $template_id)
					{
						$query = "select template_id from geodesic_pages_templates where page_id = '$page_id' and language_id = '$language_id'";
						$result = $this->db->Execute($query) or $this->DBError(__LINE__);
						if($result->RecordCount() == 1 && is_numeric($template_id))
						{
							$query = "update geodesic_pages_templates set language_id = '$language_id', template_id = '$template_id' where page_id = '$page_id'";
							$this->db->Execute($query) or $this->DBError(__LINE__);
							$messages .= "Updated entry: page_id = $page_id, language_id = $language_id, template_id = $template_id<br/>";
							$this->attach_modules($page_id, $language_id, $template_id);
						}
						elseif(!$result->RecordCount() && is_numeric($template_id))
						{
							$query = "insert into geodesic_pages_templates (page_id, language_id, template_id) values ('$page_id', '$language_id', '$template_id')";
							$this->db->Execute($query) or $this->DBError(__LINE__);
							$messages .= "Added entry: page_id = $page_id, language_id = $language_id, template_id = $template_id<br/>";
							$this->attach_modules($page_id, $language_id, $template_id);
						}
						elseif(is_numeric($template_id))
							$messages .= "Duplicate templates exist in database for page $page_id<br/>";
					}
				}
			}
			$this->body .= "
				<p style='font-weight:bold;'>
					Done!
				</p>
				<p>
					$messages
				</p>";
			return false;
		}
		else
		{
			$result = $this->db->Execute("select template_id,name from geodesic_templates order by name asc");
			$template_options = "";
			while($row = $result->FetchRow())
			{
				$template_options .= "
					<option value='{$row["template_id"]}'>".htmlspecialchars($row["name"])."</option>";
			}
			$sql = "select language_id,language from geodesic_pages_languages";
			$result = $this->db->Execute($sql) or $this->DBError(__LINE__, "Could not fetch languages");
			$languages = array();
			while($row = $result->FetchRow())
				$languages[$row["language"]] = $row["language_id"];
			$new_pages = array(
				199,
				10157,
				10158,
				10159,
				10160,
				10161,
				10162,
				10163,
				10164,
				10165,
				10171
				);

			$pages = "";
			foreach($new_pages as $value)
			{
				$pages_result = $this->db->Execute("select name,description from geodesic_pages where page_id = $value") or $this->DBError(__LINE__);
				$templates_result = $this->db->Execute("select * from geodesic_pages_templates where page_id = $value and template_id != '' limit 1") or $this->DBError(__LINE__);

				$auctionTemplates = array(
					"auctions_user_extra_template",
					"auctions_user_checkbox_template",
					"ad_detail_print_friendly_template",
					"auctions_user_ad_template"
				);

				$newTemplates = array();
				foreach($auctionTemplates as $field) {
					$result = $this->db->Execute("select count(".$field.") as count from geodesic_classifieds_ad_configuration where ".$field." <= 0") or $this->DBError(__LINE__);
					$newTemplates[] = $result->RecordCount();
				}

				foreach($newTemplates as $id) {
					if($id <= 0) {
						$pages .= "
							<tr>
								<td style='background-color:white;padding:5px;'><b>{$row["name"]}</b><br/>
									{$row["description"]}
								</td>
								<td style='background-color:$bg_color;padding:5px;'>";
						foreach ($languages as $language_name => $languages_id)
							$pages .= "
									<select name='templates[$languages_id][$value]' style='width:20em;'>
										<option> -- $language_name -- </option>
										$template_options
									</select>";

						$pages .= "
								</td>
							</tr>";
					}
				}
				if($pages_result->RecordCount() && !$templates_result->RecordCount()) {
					$row = $pages_result->FetchRow();
					$pages .= "
						<tr>
							<td style='background-color:white;padding:5px;'><b>{$row["name"]}</b><br/>
								{$row["description"]}
							</td>
							<td style='background-color:$bg_color;padding:5px;'>";
					foreach ($languages as $language_name => $languages_id)
						$pages .= "
								<select name='templates[$languages_id][$value]' style='width:20em;'>
									<option> -- $language_name -- </option>
									$template_options
								</select>";

					$pages .= "
							</td>
						</tr>";
				}
			}
			if($pages == "") {
					$this->body .= "No new pages<br />";
					return false;
			}
			$this->body .= "
					<form action='index.php?step=doMainUpgrade' method='post'>
					<input type='hidden' name='action' value='templates'/>
					<p>
						Please choose a template to use for the following pages. Most new pages use the basic page template.
						It is best to assign templates now, and change them later as needed.
					</p>
					<table cellspacing='0' style='background-color:black;position:relative;left:2%;width:96%;' border='1' cellspacing='1'>
					$pages
					</table>
					<input type='submit' value='Assign'>
					</form>";
			return true;
		}
	}
	/**
	 * Remove any optional fields greater than 20
	 *
	 * @return bool TRUE if this script needs to be run again
	 * @uses Upgrade::connectDB()
	 * @uses Upgrade::$body
	 * @uses Upgrade::$db::Execute()
	 */
	function doRemoveOptionalFields() {
		$this->connectDB();

		$bg_color = "#A5CCEB";
		$dark_border_color = "#000";

		$sql_pattern = "[[:<:]]optional[[:>:]].*[[:<:]](2[1-9]|3[0-9])[[:>:]]";

		$this->body .= "
				<p style='background-color:$bg_color;border:1px solid $dark_border_color;-moz-border-radius:5px;'>
					Remove Un-Used Text Fields
				</p>";
		if(isset($_POST["action"])) {
			$messages = "";
			if(!isset($_POST['uber_gone_messages']) || !count($_POST["uber_gone_messages"]))
				$messages = "No messages were removed";
			else
			{
				foreach($_POST["uber_gone_messages"] as $value)
				{
					$query1 = "delete from geodesic_pages_messages where message_id = '$value' limit 1";
					$query2 = "delete from geodesic_pages_messages_languages where text_id = '$value' limit 1";
					$this->db->Execute($query1) or $this->DBError(__LINE__);
					$this->db->Execute($query2) or $this->DBError(__LINE__);
					$messages .= "Removed message #$value<br>";
				}
			}
			$this->body .= "
				<p style='font-weight:bold;'>
					Done!
				</p>
				<p>
					$messages
				</p>";
			return false;
		} else {
			$result = $this->db->Execute("select * from geodesic_pages_messages as labels, geodesic_pages_messages_languages as messages where (name regexp '$sql_pattern' or description regexp '$sql_pattern') and messages.text_id = labels.message_id");
			if($result->RecordCount()) {
				$this->body .= "
					<form action='{$_SERVER['PHP_SELF']}?".$this->implode_with_keys($_GET, "&", "=")."' method='post'>
					<input type='hidden' name='action' value='remove'/>
					<p>
						Please verify that you want to remove the following un-used messages
				<br /><input type='submit' value='Confirm'/>
					</p>
					<table style='margin-left:2%;border-width:0;width:96%' cellspacing='0'>";
			} else {
				$this->body .= "No optional fields found higher than 20<br />";
				return false;
			}
			while($row = $result->FetchRow())
			{
				$this->body .= "
					<tr>
						<td style='padding:5px;border:1px solid $dark_border_color;background-color:white;width:50%;'>
							<p style='text-indent:-10px;margin-left:10px;font-size:75%;'>
								<b>{$row["name"]}</b><br>
								<b>Message ID:</b> {$row["message_id"]}<br>
								<b>Page ID:</b> {$row["page_id"]}
							</p>
						</td>
						<td style='width:50%;overflow:auto;background-color:$bg_color;text-align:left;padding:5px;border-width:1px 1px 1px 0;border-style:solid;border-color:$dark_border_color;-moz-border-radius-bottomright:5px;-moz-border-radius-topright:5px;font-size:75%;'>
							{$row["text"]}
						<input type='hidden' name='uber_gone_messages[{$row["message_id"]}]' value='{$row["message_id"]}'>
						</td>
					</tr>";
			}
			$this->body .= "
				</table>
				</form>";
			return true;
		}
	}
	/**
	 * Replace 'classified', 'ad', and 'auction' with generic 'listing'
	 *
	 * @return bool true if this function needs to be run again
	 * @uses Upgrade::connectDB()
	 * @uses return_appropriate()
	 * @uses replace()
	 * @uses Upgrade::$body
	 * @uses Upgrade::$db::Execute()
	 */
	function doReplaceWords() {
		$this->connectDB();

		$bg_color = "#A5CCEB";
		$dark_border_color = "#000";

		// RegEx Patterns
		$sql_pattern = "([[:<:]]ads?[[:>:]]|[[:<:]]classifieds?[[:>:]])";

		/**
		 * Returns the appropriate version of $matches depending on case and whether or not it is plural or singular
		 *
		 * @param string $matches Strings to be converted
		 * @return string Appropriate version (caplitalization, plural/singular) of $matches
		 */
		function return_appropriate($matches)
		{
			$new_string = strtolower($matches[1]) == $matches[1] ? "listing" : "Listing";
			if(substr($matches[1], -1, 1) == "s")
				$new_string .= "s";
			return $new_string;
		}
		/**
		 * Replaces 'ads' or 'classifieds' with 'listings'
		 *
		 * @param string $string Haystack to replace strings in
		 * @return string Corrected string
		 */
		function replace($string)
		{
			$string = preg_replace_callback("/\b(?:%22)*(ads?)(?:%5C)*\b/i", "return_appropriate", $string);
			$string = preg_replace("/\b(?:%22|%5C)*(classifieds?)(?:%22|%5C|\+)*\b/i", "", $string);
			return $string;
		}
		$this->body .= "
			<p style='background-color:$bg_color;border:1px solid $dark_border_color;-moz-border-radius:5px;'>
				Replace 'Classified' and/or 'Auction' with 'Listing'
			</p>";
		$result = $this->db->Execute("select message_id,name,description from geodesic_pages_messages where message_id < 100000 and (name regexp '$sql_pattern' or description regexp '$sql_pattern')") or $this->DBError(__LINE__);
		if(!$result->RecordCount()) {
			$this->body .= "No messages using 'classified' or 'auction'<br />";
			return false;
		}
		while($row = $result->FetchRow())
		{
			$name = replace($row["name"]);
			$description = replace($row["description"]);
			$message_id = $row["message_id"];

			$this->body .= "
				<p>
					<div style='text-align:left;text-indent:15px;background-color:#FCC;text-decoration:line-through;'><b>{$row["name"]} ($message_id):</b> {$row["description"]}</div>
					<div style='text-align:left;text-indent:15px;background-color:#CCF;'><b>$name ($message_id):</b> $description</div>";
			if(isset($_POST['update']))
			{
				$this->db->Execute("update geodesic_pages_messages set name = '".addslashes($name)."', description = '".addslashes($description)."' where message_id = $message_id") or $this->DBError(__LINE__);
				$this->body .= "
					<div style='text-indent:15px;background-color:#CFC;'><b>Updated!</b></div>";
			}
			$this->body .= "
				</p>";
		}
		if(!isset($_POST['update'])) {
			$this->body .= "
					<form action='{$_SERVER['PHP_SELF']}?".$this->implode_with_keys($_GET, "&", "=")."' method='POST'>
						<input type='submit' name='update' value='Replace'>
					</form>";
			return true;
		} else {
			return false;
		}
	}
	/**
	 * Used to encrypt credit card information
	 *
	 * @param string $string String to be encoded
	 * @param string $key Key used to encode
	 * @return string String encoded with key
	 */
	function encrypt($string, $key) {
		$result = '';
		for($i=0; $i<strlen($string); $i++) {
		    $char = substr($string, $i, 1);
		    $keychar = substr($key, ($i % strlen($key))-1, 1);
		    $char = chr(ord($char)+ord($keychar));
		    $result .= $char;
	   	}
   		return base64_encode($result);
  	}
  	/**
	 * Overloaded ADODB database function. This is used with AJAX loading bars. If Upgrade::state == 'calculating',
	 * this will not actually run the query, but instead increment Upgrade::totalQueries. If Upgrade::state == 'running'
	 * or $override == true, this function WILL run the query and return a result.
	 *
	 * @param string $query SQL query to run
	 * @param bool $override Run a query even if Upgrade::state == "calculating"
	 * @return resource Result of the query if Upgrade::state == "running" or $override == true
	 * @uses Upgrade::$state
	 * @uses Upgrade::$totalQueries
	 * @uses Upgrade::$currentQuery
	 * @uses Upgrade::$db::Execute()
	 * @uses Upgrade::setTimeLimit()
	 */
	function Execute($query, $override=false) {
		$this->connectDB();
		
		$bufferChar = $this->ajax ? "|" : ' ';
		if ($this->state == 'calculating'){
			$this->totalQueries = isset($this->totalQueries) ? ($this->totalQueries+1) : 1;
		} else  {
			$this->currentQuery = isset($this->currentQuery) ? ($this->currentQuery+1) : 1;
		}
		
		if ($this->state != "calculating"){
			// There has [NOT]been 1% progress
			//$this->lastPercent = intval(($this->currentQuery/$this->totalQueries) * 100 );
			echo $bufferChar;
			ob_flush();
			flush();
			$this->setTimeLimit(30);
		}

		if($override || $this->state != "calculating"){
			return $this->db->Execute($query);
		}
		else{
			return true;
		}
	}
	/**
	 * Get the type of product that the user is starting with. Store the result in Upgrade::oldProduct also.
	 *
	 * @return CONST CLASSAUCTIONS, CLASSIFIEDS, or AUCTIONS
	 * @uses Upgrade::$oldProduct
	 * @uses Upgrade::connectDB()
	 * @uses Upgrade::fieldExists()
	 * @uses Upgrade::tableExists()
	 */
	function getOldProduct() {
		if(!isset($this->oldProduct)) {
			$this->connectDB();

			$type = null;
			if($this->fieldExists("geodesic_classifieds", "item_type"))
				$type = CLASSAUCTIONS;
			elseif($this->tableExists("geodesic_classifieds"))
				$type = CLASSIFIEDS;
			elseif($this->tableExists("geodesic_auctions"))
				$type = AUCTIONS;
			$this->oldProduct = $type;
		}
		return $this->oldProduct;
	}
	/**
	 * Get the name of product that the user is starting with. Calls Upgrade::getOldProduct()
	 *
	 * @return string "GeoAuctions", "GeoClassifieds", "ClassAuctions", or "Error"
	 * @uses Upgrade::$oldProduct
	 */
	function getOldProductName() {
		if(!isset($this->oldProduct))
			$this->getOldProduct();
		if(AUCTIONS == $this->oldProduct)
			return "GeoAuctions";
		elseif (CLASSIFIEDS == $this->oldProduct)
			return "GeoClassifieds";
		elseif(CLASSAUCTIONS == $this->oldProduct)
			return "ClassAuctions";
		else
			return "Error";
	}
	/**
	 * Get the license
	 *
	 * @todo Everything
	 * @uses Upgrade::connectDB()
	 */
	function getLicense() {
		$this->connectDB();
	}
	/**
	 * Checks if there is a license in geodesic_classifieds_configuration table
	 *
	 * @return bool whether there was a license or not
	 * @uses Upgrade::connectDB()
	 * @uses Upgrade::$db::Execute
	 */
	function licenseIsSet() {
		$this->connectDB();
		$result = $this->db->Execute('select * from `geodesic_classifieds_configuration` limit 1') or $this->DBError(__LINE__);
		$row = $result->FetchRow();
		return @$row['license'] ? true : false;
	}
	/**
	 * Add the license key to the database. If necessary, add the field also
	 *
	 * @param string $key User's product key
	 * @uses Upgrade::connectDB()
	 * @uses Upgrade::$db::Execute()
	 * @uses Upgrade::addLicenseField()
	 */
	function setLicense($key) {
		$this->connectDB();
		// use a standard sql statement to check if geodesic_classifieds_configuration.license exists
		$result = $this->db->Execute('select * from `geodesic_classifieds_configuration` limit 1') or $this->DBError(__LINE__);
		$row = $result->FetchRow();
		if(!in_array("license", array_keys($row)))
			$this->addLicenseField();
		$this->db->Execute("update geodesic_classifieds_configuration set license = '$key'") or $this->DBError(__LINE__);
	}
	/**
	 * Prepare older products for the upgrade to the Classauctions codeset
	 *
	 * @uses Upgrade::splitSqlFile()
	 * @uses Upgrade::Execute()
	 * @uses Upgrade::$db::ErrorMsg()
	 */
	function prepareForNew() {
		include_once "arrays/provisionalMaps.php";
		//insert text
		foreach ($insert_text_array as $key => $value)
		{
			$sql_query = "select * from geodesic_pages_messages where message_id = ".$insert_text_array[$key][0];
			$test_result = $this->Execute($sql_query, true) or $this->DBError(__LINE__);
			if ($test_result->RecordCount() == 0)
			{
				if (isset($insert_text_array[$key][7]) && strlen(trim($insert_text_array[$key][7])) == 0)
					$insert_text_array[$key][7] = 0;
				elseif(!isset($insert_text_array[$key][7]))
					$insert_text_array[$key][7] = 0;
				$sql_query = "insert into geodesic_pages_messages
					(message_id,name,description,text,page_id,display_order,classauctions)
					values
					(".$insert_text_array[$key][0].",\"".$insert_text_array[$key][1]."\",\"".$insert_text_array[$key][2]."\",\"".$insert_text_array[$key][3]."\",
					\"".$insert_text_array[$key][4]."\",\"".$insert_text_array[$key][5]."\",\"".$insert_text_array[$key][7]."\")";
				$this->Execute($sql_query);
			}
		}
		//insert ?
		reset ($upgrade_array);
		$sql_query = "select language_id from geodesic_pages_languages";
		$language_result = $this->Execute($sql_query, true) or $this->DBError(__LINE__);
		while ($show_language = $language_result->FetchNextObject())
		{
			reset($upgrade_array);
			foreach (array_keys($upgrade_array) as $key)
			{
				$sql_query = "select * from geodesic_pages_messages_languages where text_id = ".$upgrade_array[$key][1]." and language_id = ".$show_language->LANGUAGE_ID;
				$test_result = $this->Execute($sql_query, true) or $this->DBError(__LINE__);
				if ($test_result->RecordCount() == 0)
				{
					$sql_query = "insert into geodesic_pages_messages_languages
						(page_id, text_id,language_id,text)
						values
						(".$upgrade_array[$key][0].",".$upgrade_array[$key][1].",\"".$show_language->LANGUAGE_ID."\",\"".$upgrade_array[$key][3]."\")";
					$result = $this->Execute($sql_query);
				}
			}
		}
	}

	/**
	 * Split SQL string into individual queries
	 *
	 * @param array $ret String to store individual
	 * @param string $sql String of delimitted SQL queries to be split
	 * @return array Split appart SQL statements
	 */
	function splitSqlFile(&$ret, $sql) {

	    $sql          = trim($sql);
	    //remove windows returns
		$sql = str_replace("\r",'',$sql);
	    $sql_len      = strlen($sql);
	    $char         = '';
	    $string_start = '';
	    $in_string    = FALSE;
	    $time0        = time();
		
		for ($i = 0; $i < $sql_len; ++$i) {
	        $char = $sql[$i];

	        // We are in a string, check for not escaped end of strings except for
	        // backquotes that can't be escaped
	        if ($in_string) {
	            for (;;) {
	                $i         = strpos($sql, $string_start, $i);
	                // No end of string found -> add the current substring to the
	                // returned array
	                if (!$i) {
	                    $ret[] = $sql;
	                    return TRUE;
	                } else if ($string_start == '`' || $sql[$i-1] != '\\') {
	                	// Backquotes or no backslashes before quotes: it's indeed the
	                	// end of the string -> exit the loop
	                    $string_start      = '';
	                    $in_string         = FALSE;
	                    break;
	                } else {
	                	// one or more Backslashes before the presumed end of string...
	                    // ... first checks for escaped backslashes
	                    $j                     = 2;
	                    $escaped_backslash     = FALSE;
	                    while ($i-$j > 0 && $sql[$i-$j] == '\\') {
	                        $escaped_backslash = !$escaped_backslash;
	                        $j++;
	                    }
	                    // ... if escaped backslashes: it's really the end of the
	                    // string -> exit the loop
	                    if ($escaped_backslash) {
	                        $string_start  = '';
	                        $in_string     = FALSE;
	                        break;
	                    }
	                    // ... else loop
	                    else {
	                        $i++;
	                    }
	                } // end if...elseif...else
	            } // end for
	        } else if ($char == ';' && ($i+1>=$sql_len||$sql[$i+1]=="\n")) {
	        	// We are not in a string, first check for delimiter...
	       		//echo "found delimiter<Br>\n";
	            // if delimiter found, add the parsed part to the returned array
	            $ret[]      = substr($sql, 0, $i);
	            $current      = substr($sql, 0, $i);
	            $this->Execute($current);
	            //	echo $current."<Br>\n";
	            $sql        = ltrim(substr($sql, min($i + 1, $sql_len)));
	            $sql_len    = strlen($sql);
	            if ($sql_len) {
	                $i      = -1;
	            } else {
	                // The submited statement(s) end(s) here
	                return TRUE;
	            }
	        } // end else if (is delimiter)

	        // loic1: send a fake header each 30 sec. to bypass browser timeout
	        $time1     = time();
	        if ($time1 >= $time0 + 30) {
	            $time0 = $time1;
	            //header('X-pmaPing: Pong');//echo " ";
	            flush();
	            ob_flush();

	        } // end if
	    } // end for

	    if (!empty($sql) && ereg('[^[:space:]]+', $sql)) {
	   		// add any rest to the returned array
	        $ret[] = $sql;
	    }

	    return true;
	}
	/**
	 * Set $_SESSION variables for the Upgrade process to decide what to do
	 *
	 * @return bool sucess/failure
	 */
	function setUpgradePath() {
		if(!isset($_SESSION))
			session_start();
		if(isset($_SESSION['upgradePath'])) {
			unset($_SESSION['upgradePath']);
			session_unregister("upgradePath");
		}

		$_SESSION['upgradePath'] = array();

		if(isset($_POST['main'])) {
			$_SESSION['upgradePath'][$_POST['main']] = 'run';
			unset($_POST['main']);
		}

		foreach (array_keys($_POST) as $key)
			$_SESSION['upgradePath'][$key] = 'run';
		return true;
	}
	/**
	 * Convert an associative array to a string using it's keys AND values
	 *
	 * @param array $array Source array
	 * @param string $element_glue Delimiter to use between key/value pairs
	 * @param string $key_value_glue Delimiter to use between keys and values
	 * @return string Imploded array as a string
	 */
	function implode_with_keys($array, $element_glue, $key_value_glue) {
		$half_imploded = array();
		foreach($array as $key => $value) {
			array_push($half_imploded, $key.$key_value_glue.$value);
		}
		return implode($element_glue, $half_imploded);
	}
	/**
	 * Checks if a table exists in the database
	 *
	 * @param string $tableName Table to check for
	 * @return bool The table exists or doesn't exist
	 * @uses Upgrade::connectDB()
	 * @uses Upgrade::$db::Execute()
	 */
	function tableExists($tableName) {
		$this->connectDB();

		$result = $this->db->Execute("show tables");
		while($row = $result->FetchRow()) {
			if(in_array($tableName, $row))
				return true;
		}
		return false;
	}
	/**
	 * Checks if a field exists in a table
	 *
	 * @param string $tableName Table to look for field in
	 * @param string $fieldName Field to check for
	 * @return bool The field exists or doesn't exist
	 * @uses Upgrade::connectDB()
	 * @uses Upgrade::$db::Execute()
	 */
	function fieldExists($tableName, $fieldName) {
		Upgrade::connectDB();

		$result = $this->db->Execute("SHOW COLUMNS FROM $tableName");
		if(!$result) {
			//echo "$tableName does not exist";
			return false;
		}
		else
		{
			while($row = $result->FetchRow()) {
				if(in_array($fieldName, $row))
					return true;
			}
		}
		return false;
	}
	/**
	 * Set a session variable to return to $destination after the user clicks a next button
	 *
	 * @param string $destination A case in Upgrade::runUpgrades()'s switch. This is NOT a URL.
	 */
	function returnToCurrentLocation($destination) {
		array_unshift($_SESSION['upgradePath'], $destination);
	}
	/**
	 * Have the user select templates for pages without templates assigned to them
	 *
	 * @param string $currentLocation The location to return to after the user clicks the submit button. See Upgrade::returnToCurrentLocation()
	 */
	function doMapNewPages($currentLocation) {
		if(isset($_POST["action"])) {
			$this->state = "running";
			$this->totalQueries = 1;
			$map = $this->parsePageMapData();
			$this->mapAllPages($map);
			$this->body .= "<br />
				<input type='button' value='Next' id='nextButton' onclick='javascript:location.href=\"index.php?step=doMainUpgrade\"'>";
		} else {
			$this->state = "calculating";
			$pages = $this->getUnmappedPages();
			$detailPages = $this->getUnmappedDetailPages();
			if($this->chooseTemplates($pages + $detailPages))
				$this->returnToCurrentLocation($currentLocation);

		}
	}
	/**
	 * Return an array of the pages without templates assigned to them
	 *
	 * @return array Pages without templates
	 */
	function getUnmappedPages() {
		$this->connectDB();

		// Get all pages that ARE mapped
		$mapped = $this->db->GetAssoc("select pages.page_id,pages.name,pages.description from geodesic_pages_templates as map, geodesic_pages as pages where map.page_id = pages.page_id order by pages.page_id asc") or $this->DBError(__LINE__);

		// Get all pages.
		$all = $this->db->GetAssoc("select page_id,name,description from geodesic_pages where name != '' order by page_id asc") or $this->DBError(__LINE__);

		// Figure out which pages aren't mapped
		$diffs = array_diff(array_keys($all), array_keys($mapped));

		// Structure array correctly
		$newPages = array();
		foreach($diffs as $id) {
			$newPages[$id] = array(
				"name" => $all[$id]["name"],
				"description" => $all[$id]["description"]
			);
		}
		return count($newPages) > 0 ? $newPages : array();
	}
	/**
	 * Find the listing-details pages that don't have templates assigned to them. These are found in the geodesic_classifieds_ad_configuration SQL table.
	 *
	 * @return array Details pages without templates
	 */
	function getUnmappedDetailPages() {
		// Pages to check with their name and description
		$newTemplates = array(
			"auctions_user_ad_template" => array(
				"name" => "Auction Details Template",
				"description" => ""
			),
			"auction_detail_print_friendly_template" => array(
				"name" => "Auction print friendly template",
				"description" => ""
			),
			"auctions_user_extra_template" => array(
				"name" => "Auction Extra Question Template",
				"description" => ""
			),
			"auctions_user_checkbox_template" => array(
				"name" => "Auction Checkbox Template",
				"description" => ""
			),
			"user_ad_template" => array(
				"name" => "Ad Details Template",
				"description" => ""
			),
			"user_extra_template" => array(
				"name" => "Ad Extra Question Template",
				"description" => ""
			),
			"user_checkbox_template" => array(
				"name" => "Ad Checkbox Template",
				"description" => ""
			),
			"ad_detail_print_friendly_template" => array(
				"name" => "Ad print friendly template",
				"description" => ""
			)
		);

		// Check which details pages need template assignment
		$result = $this->Execute("
			select ".implode(",", array_keys($newTemplates))." from	geodesic_classifieds_ad_configuration", true) or die("Could not fetch templates configuration");

		$row = $result->FetchRow();

		$unmapped = array();
		foreach($row as $field => $value) {
			if($value > 0 || is_numeric($field))
				continue;
			$unmapped[$field] = $newTemplates[$field];
		}
		return $unmapped;
	}
	/**
	 * The let the user choose what templates to use for pages
	 *
	 * @param array $unmappedPages Pages to map templates to
	 * @return bool False if there are no pages to assign templates to. True otherwise.
	 */
	function chooseTemplates($unmappedPages) {
		$this->connectDB();

		/**
		 * $unmappedPages prototype:
		 * 		id	=> array(
		 * 			name => "name"
		 * 			description => "name"
		 * 		);
		 */

		$bg_color = "#A5CCEB";


		if(!count($unmappedPages)) {
			$this->body .= "All of your pages have templates assigned to them.
			<form action='index.php?step=doMainUpgrade' method='post'>
			<input type='submit' value='Next'>
			</form>";
			return false;
		}

		// Fetch all templates
		$result = $this->Execute("select template_id,name from geodesic_templates order by name asc", true);
		$template_options = "";
		while($row = $result->FetchRow())
		{
			$selected = $row["template_id"] == 25 ? " selected" : "";
			$template_options .= "
				<option value='{$row["template_id"]}'$selected>".htmlspecialchars($row["name"])."</option>";
		}

		// Fetch all languages
		$result = $this->Execute("select language_id,language from geodesic_pages_languages", true) or $this->DBError(__LINE__);
		$languages = array();
		while($row = $result->FetchRow())
			$languages[$row["language"]] = $row["language_id"];

		// Display form
		$pages = "";
		foreach($unmappedPages as $key => $value) {
			$pages .= "
				<tr>
					<td style='background-color:white;padding:5px;'><b>{$value["name"]}</b><br/>
						{$value["description"]}
					</td>
					<td style='background-color:$bg_color;padding:5px;'>";
			if(is_numeric($key)) {
				foreach ($languages as $language_name => $languages_id) {
					$pages .= "
							<select name='templates[$languages_id][$key]' style='width:20em;'>
								<option> -- $language_name -- </option>
								$template_options
							</select>";
				}
			} else {
				$pages .= "
					<select name='templates[1][$key]' style='width:20em;'>
						<option> -- $language_name -- </option>
						$template_options
					</select>";
			}
			$pages .= "
					</td>
				</tr>";
		}

		$this->body .= "
			<form action='index.php?step=doMainUpgrade' method='post'>
			<input type='hidden' name='action' value='map'>
			<p>
				Please choose a template to use for the following pages. Most new pages, except for listing details, use the basic page template.
				It is best to assign templates now, and change them later as needed.
			</p>
			<table cellspacing='0' style='background-color:black;position:relative;left:2%;width:96%;' border='1' cellspacing='1'>
			$pages
			</table>
			<input type='submit' value='Assign'>
			</form>";
		return true;
	}
	/**
	 * Validate the user's choices for template assignment
	 *
	 * @return array Valid array of template-page assignments
	 */
	function parsePageMapData() {
		if(!isset($_POST["templates"]) || !count($_POST["templates"]))
			die("No templates selected");
		foreach($_POST["templates"] as $languageId => $templates) {
			if(!is_numeric($languageId))
				die("Invalid language");
			foreach ($templates as $templateId) {
				if(!is_numeric($templateId))
					die("Invalid template ID");
			}
		}
		return $_POST["templates"];
	}
	/**
	 * Map templates to pages
	 *
	 * @param array $pages List of templates and pages assignments
	 */
	function mapAllPages($pages) {
		/**
		 *	$pages prototype:
		 * 		language => array(
		 *	 		page => template
		 * 			 .
		 * 			 .
		 * 			 .
		 * 		);
		 */

		$details = array();
		$mapped = array();

		$this->body .= "Mapping pages<br />";
		foreach($pages as $language => $data) {
			foreach($data as $page => $template) {
				if(is_numeric($page)) {
					$mapped += $this->mapPage($page, $template, $language);
				} else {
					$details[$page] = $template;
				}
			}
		}

		if(count($details))
			$this->mapDetailsPages($details);
		$mapped += $details;
		$this->body .= "Mapped ".count($mapped)." pages<br />Done!";
	}
	/**
	 * Map a page that is NOT a listing details page
	 *
	 * @param int $page Page ID
	 * @param int $template Template ID
	 * @param int $language Language ID
	 * @return array
	 */
	function mapPage($page, $template, $language=1) {
		$this->connectDB();
		$this->Execute("insert into geodesic_pages_templates set page_id = ".$page.", template_id = ".$template.", language_id = ".$language) or $this->DBError(__LINE__);
		return array($page => $template);
	}
	/**
	 * Map listing detail pages
	 *
	 * @param array $pages List of template-page assignments
	 * @return bool
	 */
	function mapDetailsPages($pages) {
		$this->connectDB();
		/**
		 *	$pages prototype:
		 * 		page => template
		 */

		$fields = array();
		foreach($pages as $page => $template) {
			$fields[] = $page." = ".$template;
		}
		$query = "update geodesic_classifieds_ad_configuration set ".implode(", ", $fields);
		$this->Execute($query) or $this->DBError(__LINE__);
		return true;
	}
	/**
	 * Add new Classauction pages to old products
	 *
	 */
	function addNewPages() {
		$this->Execute("
			insert into
				geodesic_pages
			set
				page_id = 199,
				section_id = 2,
				name = 'Choose Listing Type Page',
				description = 'Page in place an add process where user chooses what kind of item he is placing.',
				photo_or_icon = 2,
				applies_to = 4
		");

		$chooseListingTypeModules = array(
			"53",
			"75",
			"76",
			"100",
			"114",
			"165",
			"166",
			"171"
		);

		foreach($chooseListingTypeModules as $module_id)
			$this->Execute("insert into geodesic_pages_modules set module_id = $module_id, page_id = 199");
	}
	function fixListingTypes($type) {
		if(!is_numeric($type))
			die("Wrong listing type specified");
		$this->Execute("update geodesic_classifieds set item_type = ".$type." where item_type = 0");
		$this->Execute("update geodesic_classifieds_expired set item_type = ".$type." where item_type = 0");
		$this->Execute("update geodesic_classifieds_sell_session set type = ".$type." where type = 0");
		$this->Execute("update geodesic_classifieds_price_plans set applies_to = ".$type." where applies_to = 0");
		$this->Execute("update geodesic_group_attached_price_plans set applies_to = ".$type." where applies_to = 0");

	}
	/**
	 * Switch used to run individual upgrades/updates after the user has selected what to run
	 *
	 * @uses Upgrade::$relativePath
	 * @uses Upgrade::$nextButton
	 * @uses Upgrade::$body
	 * @uses Upgrade::doToCAELatest()
	 * @uses Upgrade::addLoadingBar()
	 * @uses Upgrade::doRemoveOptionalFields()
	 * @uses Upgrade::getOldProduct()
	 * @uses Upgrade::doAddCATemplates()
	 * @uses Upgrade::doReplaceWords()
	 */
	function runUpgrades() {
		$nextStageText = "Database updated to 2.0.10b.  <input type=\"submit\" onclick=\"javascript:location.href='../../index.php?run=continue'\" value=\"Next &gt;\" />";
		$runUpgradesAgain = '<a href=\"index.php\"><strong>Run 2.0.10b Upgrades Again</strong></a>';
		
		$ajax_link = 'index.php?step=ajax&amp;upgrade=doToCAELatest&amp;totalQueries=2000';
		$forceAjax = '<a href="'.$ajax_link.'"> &nbsp; </a>';
		session_start();

		$endOfUpgradesNavigation = null !== $this->relativePath ? $this->nextButton : "
				<!-- <hr />
				<p  style='margin-left:25%;text-indent:-20px;text-align:left;'>
					Goto:<br>
						<a href='../admin/index.php'>Administration homepage</a><br>
						<a href='../index.php'>Frontend homepage</a><br> 
						<a href='index.php'>Run pre-2.0.10b upgrades From Beginning</a><br />
						<a href=\"../../index.php\"><strong>Continue to Main Upgrades</strong></a> -- Complete  once pre-2.0.10 upgrades complete successfully.
				</p> -->";
		if(!isset($_SESSION['upgradePath'])) {
			if ($this->getOldVersion() == $this->versionNumber){
				$this->body .= $nextStageText;
			} else {
				$this->body .= $forceAjax.$runUpgradesAgain;
			}
			//$this->body .= "
			//	<p>
			//		No upgrades selected
			//	</p>
			//	$endOfUpgradesNavigation";
			return;
		}
		if(!count($_SESSION['upgradePath'])) {
			unset($_SESSION['upgradePath']);
			if(isset($_SESSION['type'])) {
				$this->body .= ("classified" == $_SESSION['type'])?	Upgrade::showNotes() : AuctionsUpgrade::showNotes();
			}
			$this->body .= $endOfUpgradesNavigation;
			session_destroy();
			return;
		}

		reset($_SESSION['upgradePath']);

		$location = key($_SESSION['upgradePath']);

		switch($location) {
			case "oldCEtoCE":
			case "oldCEtoCAE":
			case "oldCAEtoCAE":
			case "oldCEtoCAE":
				$_SESSION['type'] = "classified";
				// Upgrade 2.0.5+ Classified Enterprise to latest ClassAuctions
				/*if(CLASSIFIEDS != $this->getOldProduct()) {
					echo "You do not have the correct product to run this upgrade";
					exit;
				}*/
				$this->state = "calculating";
				$this->doToCAELatest();

				$this->state = "running";
				$this->body .= "Upgrading software<br>
					<em style='font-size: small;'>This may take a few minutes. Please be patient.</em><br>
					".$this->addLoadingBar("", $this->totalQueries)."
					<input type='button' value='Next' id='nextButton' onclick='javascript:location.href=\"index.php?step=doMainUpgrade\"' disabled>";//../../index.php?run=continue\"' disabled>"; 
				break;

			case "oldAEtoCAE":
				$_SESSION['type'] = "auction";
				$this->state = "calculating";
				$this->doOldAEToCAE();

				$this->state = "running";
				$this->body .= "Upgrading software<br>
					<em style='font-size: small;'>This may take a few minutes. Please be patient.</em><br>
					".$this->addLoadingBar("doOldAEToCAE", $this->totalQueries)."
					<input type='button' value='Next' id='nextButton' onclick='javascript:location.href=\"index.php?step=doMainUpgrade\"' disabled>";//index.php?run=continue\"' disabled>"; 
				break;

			case "removeOptional":
				if($this->doRemoveOptionalFields())
					$this->returnToCurrentLocation($location);
				else
					$this->body .= "<input type='button' value='Next' onclick='javascript:location.href=\"index.php?step=doMainUpgrade\"'>";
				break;

			case "addNewTemplates":
				if(AUCTIONS == $this->getOldProduct())
					die("You do not have the correct product to run this upgrade");
				if($this->doAddCATemplates())
					$this->returnToCurrentLocation($location);
				else
					$this->body .= "<input type='button' value='Next' onclick='javascript:location.href=\"index.php?step=doMainUpgrade\"'>";
				break;

			case "changeToListing":
				if($this->doReplaceWords())
					$this->returnToCurrentLocation($location);
				else
					$this->body .= "<input type='button' value='Next' onclick='javascript:location.href=\"index.php?step=doMainUpgrade\"'>";
				break;

			case "doMapNewPages":
				$this->doMapNewPages($location);
				break;
		}
		array_shift($_SESSION['upgradePath']);
		return;
	}

	function verifySiteSettingTableExists() {
		$this->connectDB();
		$sql = "CREATE TABLE if not exists `geodesic_site_settings` (
		`setting` VARCHAR( 255 ) NOT NULL ,
		`value` VARCHAR( 255 ) NOT NULL ,
		UNIQUE (`setting`))";
		$this->Execute($sql, true);
	}
	
	/**
	 * First function called after construction and setting specific variables.
	 *
	 * @uses Upgrade::$body
	 * @uses Upgrade::runUpgrades()
	 * @uses Upgrade::setLicense()
	 * @uses Upgrade::$totalQueries
	 * @uses Upgrade::$state
	 * @uses Upgrade::doToCAELatest()
	 * @uses Upgrade::fieldExists()
	 * @uses Upgrade::addLicenseField()
	 * @uses Upgrade::setUpgradePath()
	 * @uses Upgrade::showTooltips()
	 */
	function init() {
		
		$step = (isset($_GET['step'])) ? $_GET['step'] : "";
		
		$nextStageText = "Database updated to 2.0.10b.  <input type=\"submit\" onclick=\"javascript:location.href='../../index.php?run=continue'\" value=\"Next &gt;\" />";
		$runUpgradesAgain = '<a href=\"index.php\"><strong>Run 2.0.10b Upgrades Again</strong></a>';
		
		$ajax_link = 'index.php?step=ajax&amp;upgrade=doToCAELatest&amp;totalQueries=2000';
		$forceAjax = '<a href="'.$ajax_link.'"> &nbsp; </a>';
		
		
		switch($step) {				
			case "doMainUpgrade":
				include('config_tools.php');
				$config_tools = new config_tools();
				$config_tools->update_beta_settings();
				
				// This case is recursively called as long as the user hits the "Next" button and
				// there are elements in $_SESSION['upgradePath']

				$this->body .= "
					<div class=\"body_div\" style='margin:0;text-align:center;'>";
				$this->verifySiteSettingTableExists();
				$this->runUpgrades();

				$this->body .= "
					</div>";
				break;

			case "ajax":
				header('Cache-Control: no-cache');
				header('Content-Type: text/html');
				if(!isset($_GET["upgrade"]))
					die("No upgrade selected");

				$fn = "";
				switch ($_GET["upgrade"]) {
					case "doOldAEToCAE":
						$fn = "doOldAEToCAE";
						break;
					default:
						$fn = "doToCAELatest";
						break;
				}
				$this->totalQueries = $_GET["totalQueries"];
				$this->currentQuery = 0;
				$this->state = "running";
				$this->ajax = true;
				$this->$fn();
				exit; // DON'T TOUCH!!!
				break;

			case "setUpgradePath":
				// Set all session variables used to direct the upgrade path
				if($this->setUpgradePath())
					header("Location: index.php?step=doMainUpgrade");
				else{
					if ($this->getOldVersion() == $this->versionNumber){
						$this->body .= $nextStageText;
					} else {
						$this->body .= $forceAjax.$runUpgradesAgain;
						//$this->body .= "No upgrades selected";
					}
				}
				break;

			default:
				
				
				//check the config.php file to see if it is up to date.
				include('config_tools.php');
				$config_tools = new config_tools();
				$err_msg = array();
				if (!$config_tools->check_config_file()){
					$err_msg[] = 'Your config.php needs to be updated, please follow <a href="config_tools.php?update_config_instructions=true" target="_blank"> these instructions</a> carefully to proceed. (missing new variables)';
				}
				
				if (!$config_tools->check_xss_compat()){
					$err_msg[] = 'Make sure you do NOT include the file xss_filter_inputs.php, it is not compatible with this version.  This software has its own customized xss security fix.  Please follow <a href="config_tools.php?update_config_instructions=true" target="_blank"> these instructions</a> carefully to proceed.';
				}
				
				//check the rest of the requirements.
				$no_show_tests = true;
				include_once ('requirement_test.php');
				if (!check_products_upload()){
					//products.php is not binary
					//$err_msg[] = 'products.php was NOT uploaded in BINARY mode.  You must re-upload the products.php file in BINARY mode to continue. (be sure to use BINARY mode, not AUTO or simular)';
				}
				if (!check_data_access_upload()){
					//products.php is not binary
					//$err_msg[] = 'classes/DataAccess.class.php and/or classes/php5_dir/DataAccess.class.php were NOT uploaded in BINARY mode.  You must re-upload those files in BINARY mode to continue. (be sure to use BINARY mode, not AUTO or simular)';
				}
				// Shows the main update/upgrade form.
				$this->showTooltips();
				
				
				$this->body = "
					<script type='text/javascript'>
						function showOptionals(mainUpgrade) {
							if(mainUpgrade == 'CAEupgrade')
							{ 
								document.getElementById('changeToListing').style.display = 'none';
								document.getElementById('doMapNewPages').style.display = 'none';
								document.getElementById('changeToListingCB').disabled = true;
								document.getElementById('doMapNewPagesCB').disabled = true;
							}

							if(mainUpgrade == 'oldCEtoCAE')
							{
								document.getElementById('doMapNewPages').style.display = 'none';
								document.getElementById('changeToListing').style.display = 'block';
								document.getElementById('doMapNewPagesCB').disabled = true;
								document.getElementById('changeToListingCB').disabled = false;
							}
							if(mainUpgrade == 'oldAEtoCAE')
							{
								document.getElementById('doMapNewPages').style.display = 'block';
								document.getElementById('changeToListing').style.display = 'none';
								document.getElementById('doMapNewPagesCB').disabled = false;
								document.getElementById('changeToListingCB').disabled = true;
							}

						}
					</script>
					";
				
				
				if (count($err_msg)>0)
				{
					$err_msg[] = 'Once you have made the changes needed above, come back to this page and hit "refresh" on your browser, to run the upgrade script.';
					foreach ($err_msg as $error)
					{
						$this->body .= '<div class="err_msg" style="color:red; border:thin solid red; margin:5px; padding:5px;">'.$error.'</div>';
					}
				}
				else
				{
					$validUpgrades = $this->checkValidUpgrades();
					if(IAMDEVELOPER==1) $validUpgrades = 4;
					$this->body .= "
					<form action='index.php?step=setUpgradePath' method='post'>
						<p style='margin:0;'>
							<b>Main updates/upgrades:</b>";

								switch($validUpgrades)
								{
									case 1:
										$this->body .= "<br /><label><input type='radio' name='main' value='oldAEtoCAE' checked='checked'> Geodesic Auctions Enterprise(old platform) Update</label>";
										break;
									case 2:
										$this->body .= "<br /><label><input type='radio' name='main' value='oldCEtoCAE' checked='checked'> Geodesic Classifieds Enterprise(old platform) Update</label>";
										break;
									case 3:
										$this->body .= "<br /><label><input type='radio' name='main' value='oldCAEtoCAE' checked='checked'> Geodesic Software Update</label>";;
										break;
									case 4: // IAMDEVELOPER
										$this->body .= "<br /><label><input type='radio' name='main' value='oldAEtoCAE' onClick=\"showOptionals('oldAEtoCAE'); return true;\" \> Geodesic Auctions Enterprise(old platform) Update to 2.0.10b</label><br />";
										$this->body .= "<label><input type='radio' name='main' value='oldCEtoCAE' onClick=\"showOptionals('oldCEtoCAE'); return true;\" \> Geodesic Classifieds Enterprise(old platform) Update to 2.0.10b</label><br />";
										$this->body .= "<label><input type='radio' name='main' value='oldCAEtoCAE' onClick=\"showOptionals('CAEupgrade'); return true;\" checked='checked' \> Geodesic Software Update to 2.0.10b</label><br />";;
										$this->onload .= ' javascript:showOptionals("CAEupgrade");';
										break;
										
									default:
										$this->body .= '<div class="err_msg" style="color:red; border:thin solid red; margin:5px; padding:5px;">The version you are attempting to update is not compatible with this update routine. Please contact the Geodesic Solutions Support Team.</div>';
								}
								
					$this->body .= "
						</p>
						<p style='margin:0;'>";
					
					switch($validUpgrades)
					{
						case 1:
							$this->body .= "
								<!--<br /><label id='doMapNewPages'>
									<input type='checkbox' id='doMapNewPagesCB' name='doMapNewPages'> Assign templates to new pages
									<img src='images/tooltip.gif' tooltip='ONLY USED FOR UPGRADES FROM OTHER PRODUCTS TO THE CLASSAUCTIONS PRODUCT. This upgrade will allow you to assign a template to pages associated with new auction or classified functionality that are being added to your software. If you choose not to assign templates with this update, you can do it in the Admin after the main upgrade is complete.'/>
								<br /></label>
								<label><input type='checkbox' name='removeOptional' /> Remove Un-Used Text Fields
									<img src='images/tooltip.gif' tooltip='This update will remove any optional field text in your database that is not used. (Maintenance only update)'/>
								</label>-->
								<p style='text-align:center;'>
									<input type='submit' value='Run pre 2.0.10 Upgrade!'>
								</p>
							</form>";
							break;
						case 2:
							$this->body .= "
								<!--<br /><label id='changeToListing'>
									<input type='checkbox' id='changeToListingCB' name='changeToListing'> Replace 'classified' and/or 'auction' with 'listing'
									<img src='images/tooltip.gif' tooltip='ONLY USED FOR UPGRADES FROM OTHER PRODUCTS TO THE CLASSAUCTIONS PRODUCT. This update will change \"Classifieds\" and/or \"Auction\" instructional text in the Admin to \"Listing\". Intended to make Admin use more intuitive.'/>
								<br /></label>
								<label>
									<input type='checkbox' name='removeOptional' /> Remove Un-Used Text Fields
									<img src='images/tooltip.gif' tooltip='This update will remove any optional field text in your database that is not used. (Maintenance only update)'/>
								</label>-->
								<p style='text-align:center;'>
									<input type='submit' value='Run pre 2.0.10 Upgrade!'>
								</p>
							</form>";
							break;
						case 3:
							$this->body .= "
								<!--<br /><label>
									<input type='checkbox' name='removeOptional'> Remove Un-Used Text Fields
									<img src='images/tooltip.gif' tooltip='This update will remove any optional field text in your database that is not used. (Maintenance only update)'/>
								</label>-->
								<p style='text-align:center;'>
									<input type='submit' value='Run pre 2.0.10 Upgrade!'>
								</p>
							</form>";
							break;
						case 4: //IAMDEVELOPER
							$this->body .= "
								<br /><label id='doMapNewPages'>
									<input type='checkbox' id='doMapNewPagesCB' name='doMapNewPages'> Assign templates to new pages
									<img src='images/tooltip.gif' tooltip='ONLY USED FOR UPGRADES FROM OTHER PRODUCTS TO THE CLASSAUCTIONS PRODUCT. This upgrade will allow you to assign a template to pages associated with new auction or classified functionality that are being added to your software. If you choose not to assign templates with this update, you can do it in the Admin after the main upgrade is complete.'/>
								<br /></label>
								<label id='changeToListing'>
									<input type='checkbox' id='changeToListingCB' name='changeToListing'> Replace 'classified' and/or 'auction' with 'listing'
									<img src='images/tooltip.gif' tooltip='ONLY USED FOR UPGRADES FROM OTHER PRODUCTS TO THE CLASSAUCTIONS PRODUCT. This update will change \"Classifieds\" and/or \"Auction\" instructional text in the Admin to \"Listing\". Intended to make Admin use more intuitive.'/>
								<br /></label>
								<label>
									<input type='checkbox' name='removeOptional'> Remove Un-Used Text Fields
									<img src='images/tooltip.gif' tooltip='This update will remove any optional field text in your database that is not used. (Maintenance only update)'/>
								<br /></label>
								<p style='text-align:center;'>
									<input type='submit' value='Run pre 2.0.10 Upgrade!'>
								</p>
							</form>";

						default: break;
					}
				}
				break;
		}
	}
	/**
	 * Adds the HTML required by the tooltips if it hasnn't been included already
	 *
	 * @uses Upgrade::$showingTooltips
	 * @uses Upgrade::$header
	 * @uses Upgrade::$onload
	 */
	function showTooltips() {
		if(!$this->showingTooltips)
			$this->showingTooltips = true;
		else
			return;
		$this->header .= "
			<script type='text/javascript'>
			var tipbox;

			function initTooltips() {
				tipbox = document.getElementById('tipbox');
				img = document.getElementsByTagName('img');
				for(i = 0; i < img.length; i++) {
					if(img[i].getAttribute('tooltip')) {
						img[i].onmouseover = showTooltip;
						img[i].onmousemove = updateTooltip;
						img[i].onmouseout = hideTooltip;
					}
				}
			}

			function showTooltip(e) {
				if(!e) e = event;
				img = e.target ? e.target : e.srcElement;
				caption = img.getAttribute('tooltip');
				tipbox.style.display = '';
				tipbox.innerHTML = caption;
				updateTooltip(e);
			}

			function updateTooltip(e) {
				if(!e) e = event;
				offsetX = 10;
				offsetY = 0;
				tipbox.style.top = e.pageY ? (e.pageY+offsetY)+'px' : (e.clientY+offsetY)+'px';
				tipbox.style.left = e.pageX ? (e.pageX+offsetX)+'px' : (e.clientX+offsetX)+'px';
			}

			function hideTooltip() {
				tipbox.style.display = 'none';
			}
			</script>";
		$this->onload .= "javascript:initTooltips();";
	}
	/**
	 * Safely resets the max_execution_time of the script to provide timeouts.
	 * This does NOT work when safe_mode is On, and will silently fail to prevent AJAX errors
	 *
	 * @param integer $seconds How many seconds to set max_execution_time to
	 */
	function setTimeLimit($seconds) {
		$safeMode = strtolower(ini_get("safe_mode"));
		if($safeMode == "off" || !$safeMode)
			set_time_limit($seconds);
	}
	
	function DBError($line, $msg="") {
		global $ignoredDBErrorCodes;
		if(!in_array($this->db->MetaError(), $ignoredDBErrorCodes)) {
			echo "<span style='color: red;'>Error: </span>".$this->db->ErrorMsg()."<br />";
		}
	}
	
	function showNotes() {
		return "Database updated to 2.0.10b.  <input type=\"submit\" onclick=\"javascript:location.href='../../index.php?run=continue'\" value=\"Next &gt;\" />
";	
	}
	
	/**
	 * Sets the default site settings, but only for the old version to this one.
	 * Add new default settings to setDefaultSettings.
	 */
	function setDefaultSiteSettings(){
		$old_version = $this->getOldVersion();
		include 'arrays/setDefaultSettings.php';
		foreach ($defaultSiteSettings as $ver => $settings){
			if ($ver == $old_version){
				//we have done all the needed default changes.
				break;
			}
			
			foreach ($settings as $setting => $value){
				//manually add to counter since we aren't going through execute.
				if ($this->state == 'calculating'){
					$this->totalQueries = isset($this->totalQueries) ? ($this->totalQueries+1) : 1;
				} else  {
					$this->currentQuery = isset($this->currentQuery) ? ($this->currentQuery+1) : 1;
				}
				
				if ($this->state != "calculating"){
					// There has [NOT]been 1% progress
					//$this->lastPercent = intval(($this->currentQuery/$this->totalQueries) * 100 );
					echo '|';
					ob_flush();
					flush();
					$this->setTimeLimit(30);
					
					$this->db->set_site_setting($setting, $value, true);
				}
			}
		}
	}
	function getOldVersion()
	{
		//gets the old version.
		$this->connectDB();
		$sql = 'SELECT * FROM geodesic_version';
		$result = $this->db->Execute($sql);
		if (!$result){
			return false;
		}
		$version = $result->FetchRow();
		return ($version['db_version']);
	}

	
	function checkValidUpgrades()
	/**
	 * @return 0 if version incompatible with all upgrades
	 * @return 1 if version compatible with only Auctions to ClassAuctions upgrade
	 * @return 2 if version compatible with only Classifieds to Classauctions upgrade
	 * @return 3 if version compatible with only Update ClassAuctions Enterprise upgrade
	 */
	{
		$oldVersion = $this->getOldVersion();
		//$oldVersion = "2.0.8b";
					
		//detect Premiere, Basic, or Lite versions
		if($this->tableExists("geodesic_classifieds_logins") || $this->tableExists("geodesic_auctions_logins"))
			return 0;
		
		//detect auctions enterprise
		if($this->tableExists("geodesic_auctions_filters"))
		{
			if(version_compare($oldVersion, "1.0.6", "<="))
				return 0; // version 1.0.0 - 1.0.6
			else if (version_compare($oldVersion, "1.0.7", "=="))
				return 1; // version 1.0.7
			else
				return 3; // version 1.0.7+
		}
		
		//detect classifieds enterprise
		if($this->tableExists("geodesic_classifieds") && $this->tableExists("geodesic_logins") && !$this->tableExists("geodesic_auctions_bids"))
		{
			if(version_compare($oldVersion, "2.0.5.2", "<"))
				return 0; // version < 2.0.5.2
			else if (version_compare($oldVersion, "2.0.5.2", "=="))
				return 2; // version 2.0.5.2
			else
				return 3; // version 2.0.5.2+
		}
		
		//detect classauctions
		if($this->tableExists("geodesic_classifieds") && $this->tableExists("geodesic_logins") && $this->tableExists("geodesic_auctions_bids"))
		{
			if (version_compare($oldVersion, "1.0.0", ">="))
				return 3; // version 1.0.0+
			else return 0;
		}
		
		return 0; // no valid upgrades found		
	}
		
	
}

/**
 * Upgrade class used to upgrade old Auctions products to the Classauctions codebase
 * This class is used in Upgrade::doOldAEToCAE()
 *
 */
class AuctionsUpgrade extends Upgrade {
	var $db;

	/**
	 * Constructor
	 *
	 * @param ADOConnection $db Database connection
	 * @return AuctionsUpgrade
	 */
	function AuctionsUpgrade(&$db) {
		$this->db =& $db;
	}
	/**
	 * Convert the old Auctions database structure to the new Classauctions structure
	 *
	 */
	function convertStructure() {
		$sql = @file_get_contents("sql/convertStructure.sql");
		if(!strlen($sql)) {
			die("Couldn't find sql/convertStructure.sql");
		}
		$this->batchExecute($sql);
	}
	/**
	 * Insert new Classauction data
	 * This should not contain user data, only configuration data
	 *
	 */
	function insertData() {
		$sql = "
			insert into geodesic_classifieds_price_increments set price_plan_id = 1, category_id = 0, low = 50.01, high = 100.00, charge = 20.00, renewal_charge = 15.00;
			insert into geodesic_classifieds_price_increments set price_plan_id = 1, category_id = 0, low = 10.01, high = 50.00, charge = 10.00, renewal_charge = 8.00;
			insert into geodesic_classifieds_price_increments set price_plan_id = 1, category_id = 0, low = 0.01, high = 10.00, charge = 5.00, renewal_charge = 4.00;
			insert into geodesic_classifieds_price_increments set price_plan_id = 1, category_id = 0, low = 100.01, high = 100000000.00, charge = 30.00, renewal_charge = 25.00;
			insert into geodesic_classifieds_sell_question_choices set value_id = 96, type_id = 23, value = 'Poor', display_order = 1;
			insert into geodesic_classifieds_sell_question_choices set value_id = 97, type_id = 23, value = 'Fair', display_order = 3;
			insert into geodesic_classifieds_sell_question_choices set value_id = 98, type_id = 23, value = 'Good', display_order = 5;
			insert into geodesic_classifieds_sell_question_choices set value_id = 99, type_id = 23, value = 'Excellent', display_order = 6;
			insert into geodesic_classifieds_sell_question_choices set value_id = 100, type_id = 23, value = 'New', display_order = 8;
			insert into geodesic_classifieds_sell_question_choices set value_id = 101, type_id = 23, value = 'Mint', display_order = 10;
			insert into geodesic_classifieds_sell_question_types set type_id = 23, type_name = 'Condition', explanation ='';
			insert into geodesic_storefront_display set display_business_type = 1, use_site_default = 1, display_photo_icon = 1, display_price = 1, display_browsing_zip_field = 1, display_browsing_city_field = 1, display_browsing_state_field = 1, display_browsing_country_field = 1, display_entry_date = 1, display_optional_field_1 = 1, display_optional_field_2 = 1, display_optional_field_3 = 1, display_optional_field_4 = 1, display_optional_field_5 = 1, display_optional_field_6 = 1, display_optional_field_7 = 1, display_optional_field_8 = 1, display_optional_field_9 = 1, display_optional_field_10 = 1, display_optional_field_11 = 1, display_optional_field_12 = 1, display_optional_field_13 = 1, display_optional_field_14 = 1, display_optional_field_15 = 1, display_optional_field_16 = 1, display_optional_field_17 = 1, display_optional_field_18 = 1, display_optional_field_19 = 1, display_optional_field_20 = 1, display_ad_description = 1, display_ad_description_where = 0, display_all_of_description = 0, display_ad_title = 1, display_number_bids = 1, display_time_left = 1;";
		$this->batchExecute($sql);
	}
	/**
	 * Map the old Auctions text to the new Classauctions text
	 *
	 */
	function mapText() {
		if(!file_exists("arrays/textMap.php")) {
			die("Couldn't find arrays/textMap.php");
		}
		include_once "arrays/textMap.php";

		foreach ($map as $classauctId => $auctionId) {
			$query = "update geodesic_pages_messages set message_id = '".$classauctId."' where message_id = '".$auctionId."'";
			$this->Execute($query) or $this->DBError(__LINE__);
			$query = "update geodesic_pages_messages_languages set text_id = '".$classauctId."' where text_id = '".$auctionId."'";
			$this->Execute($query) or $this->DBError(__LINE__);
		}
	}
	/**
	 * Map the old Auctions pages to the new Classauction pages
	 *
	 */
	function mapPages() {
		if(!file_exists("arrays/pageMap.php")) {
			die("Couldn't find arrays/pageMap.php");
		}
		include_once "arrays/pageMap.php";

		foreach ($map as $classauctId => $auctionId) {
			$query = "update geodesic_pages set page_id = ".$classauctId." where page_id = ".$auctionId;
			$this->Execute($query) or $this->DBError(__LINE__);
			$query = "update geodesic_pages_messages set page_id = ".$classauctId." where page_id = ".$auctionId;
			$this->Execute($query) or $this->DBError(__LINE__);
			$query = "update geodesic_pages_messages_languages set page_id = ".$classauctId." where page_id = ".$auctionId;
			$this->Execute($query) or $this->DBError(__LINE__);
			$query = "update geodesic_pages_fonts set page_id = ".$classauctId." where page_id = ".$auctionId;
			$this->Execute($query) or $this->DBError(__LINE__);
			$query = "update geodesic_pages_modules set page_id = ".$classauctId." where page_id = ".$auctionId;
			$this->Execute($query) or $this->DBError(__LINE__);
			$query = "update geodesic_pages_modules set module_id = ".$classauctId." where module_id = ".$auctionId;
			$this->Execute($query) or $this->DBError(__LINE__);
			$query = "update geodesic_pages_templates set page_id = ".$classauctId." where page_id = ".$auctionId;
			$this->Execute($query) or $this->DBError(__LINE__);
		}


		$this->Execute("update geodesic_pages set applies_to = 2 where page_id in (".implode(", ", $changeAppliesToField).")") or $this->DBError(__LINE__);
		$this->Execute("update geodesic_classifieds_ad_configuration set auctions_user_ad_template = user_ad_template") or $this->DBError(__LINE__);
	}
	/**
	 * Map the old Auctions CSS fonts to the new Classauctions fonts
	 *
	 */
	function mapFonts() {
		/**
		 * $map format:
		 * 		Classauction ID => Auction ID
		 */
	
		if(!file_exists("arrays/fontMap.php")) {
			die("Couldn't find arrays/fontMap.php");
		}
		include_once "arrays/fontMap.php";

		foreach ($map as $auctionId => $new) {
			$query = "update geodesic_pages_fonts set element_id = ".$new["element_id"].", element = '".$new["element"]."' where element_id = ".$auctionId;
			$this->Execute($query) or $this->DBError(__LINE__);
		}
	}
	/**
	 * Map the old Auctions sections to the new Classauctions sections
	 *
	 */
	function mapSections() {
		$this->Execute("update geodesic_pages_modules_sections set section_id = section_id - 10000 where section_id > 10000");
		$this->Execute("update geodesic_pages_modules_sections set parent_section = parent_section - 10000 where parent_section > 10000");
		$this->Execute("update geodesic_pages_sections set section_id = section_id - 10000 where section_id > 10000");
		$this->Execute("update geodesic_pages_sections set parent_section = parent_section - 10000 where parent_section > 10000");
		$this->Execute("update geodesic_pages set module_type = module_type - 10000 where module_type > 10000");
	}
	/**
	 * Clean any page IDs or parent section IDs that are higher than 10000 or 1000
	 *
	 */
	function cleanup() {
		//$this->Execute("update geodesic_pages_modules set page_id = page_id - 10000 where page_id > 10000") or $this->DBError(__LINE__);
		$this->Execute("update geodesic_pages_messages set page_id = page_id - 10000 where page_id >= 10000") or $this->DBError(__LINE__);
		$this->Execute("update geodesic_pages_sections set parent_section = parent_section - 1000 where parent_section > 1000") or $this->DBError(__LINE__);
		//$this->Execute("update geodesic_pages_templates set page_id = page_id - 10000 where page_id > 10000") or $this->DBError(__LINE__);
		//$this->Execute("update geodesic_pages set page_id = page_id - 10000 where page_id > 10000") or $this->DBError(__LINE__);
	}
	/**
	 * Rewrite the module tags in the templates to the new Classauction module tags
	 *
	 */
	function rewriteTemplates() {
		if(!file_exists("arrays/rewriteTemplates.php")) {
			die("Couldn't find arrays/rewriteTemplates.php");
		}
		include_once "arrays/rewriteTemplates.php";

		$result = $this->Execute("select template_code from geodesic_templates where template_id = 133", true) or $this->DBError(__LINE__);
		$row = $result->FetchRow();
		$code = str_replace($userManagement["search"], $userManagement["replace"], $row["template_code"]);
		$this->Execute("update geodesic_templates set template_code = '".addslashes($code)."' where template_id = 133");
		
		$query = "select template_id,template_code from geodesic_templates";
		$result = $this->Execute($query, true) or $this->DBError(__LINE__);

		while($row = $result->FetchRow()) {
			$code = $row['template_code'];

			$code = str_replace($search, $replace, $code);

			$code = preg_replace_callback("/index\.php\?a=(1[0-9]{3})/", function($matches){return "index.php?a=".($matches[1] > 1000 ? $matches[1] - 1000 : $matches[1]);}, $code);
			$code = preg_replace_callback("/index\.php\?a=28&b=(1[0-9]{4})/", function($matches){return "index.php?a=28&b=".($matches[1] > 10000 ? $matches[1] - 10000 : $matches[1]);}, $code);

			$query = "update geodesic_templates set template_code = '".addslashes($code)."' where template_id = '".$row['template_id']."'";
			$this->Execute($query) or $this->DBError(__LINE__);
		}
	}
	/**
	 * Rewrite pages to use the new Classauction modules
	 *
	 */
	function rewritePages() {
		if(!file_exists("arrays/filenamesAndTags.php")) {
			die("Couldn't find arrays/filenamesAndTags.php");
		}
		include_once "arrays/filenamesAndTags.php";

		$query = "select page_id,module_replace_tag,module_file_name,module_logged_in_html,module_logged_out_html from geodesic_pages where
			module_file_name != '' or
			module_logged_in_html != '' or
			module_replace_tag != ''";
		$result = $this->Execute($query, true) or $this->DBError(__LINE__);

		while($row = $result->FetchRow()) {
			$filename = $row['module_file_name'];
			$logged_in = $row['module_logged_in_html'];
			$logged_out = $row['module_logged_out_html'];
			$tag = $row['module_replace_tag'];

			if(in_array($tag, $auctionReplaceTags)) {
				$tag = str_replace("_AUCTIONS>", ">", $tag);
				$tag = str_replace("_AUCTIONS_", "_ADS_", $tag);
			}
			$tag = str_replace("<MODULE_HOTTEST_AUCTIONS>", "<MODULE_HOTTEST_ADS>", $tag);
			if(in_array($filename, $moduleFilenames)) {
				$filename = str_replace("auctions", "ads", $filename);
			} else {
				$filename = preg_replace("/^(.*)_auctions\.php$/", "\\1.php", $filename);
			}
			
			$logged_in = preg_replace_callback("/index\.php\?a=(1[0-9]{3})/", function($matches){
				return "index.php?a=".($matches[1] > 1000 ? $matches[1] - 1000 : $matches[1]);
			}, $logged_in);
			$logged_in = preg_replace_callback("/index\.php\?a=28&b=(1[0-9]{4})/", function($matches){
				return "index.php?a=28&b=".($matches[1] > 10000 ? $matches[1] - 10000 : $matches[1]);
			}, $logged_in);
			
			$logged_out = preg_replace_callback("/index\.php\?a=(1[0-9]{3})/", function($matches){
				return "index.php?a=".($matches[1] > 1000 ? $matches[1] - 1000 : $matches[1]);
			}, $logged_out);
			$logged_out = preg_replace_callback("/index\.php\?a=28&b=(1[0-9]{4})/", function($matches){
				return "index.php?a=28&b=".($matches[1] > 10000 ? $matches[1] - 10000 : $matches[1]);
			}, $logged_out);
			

			$query = "update geodesic_pages set module_replace_tag = '".$tag."', module_logged_in_html = \"".addslashes($logged_in)."\", module_logged_out_html = \"".addslashes($logged_out)."\", module_file_name = '".$filename."' where page_id = '".$row['page_id']."'";
			$this->Execute($query) or $this->DBError(__LINE__);
		}
		$this->Execute("update geodesic_pages set module_file_name = 'module_title_ads.php' where module_file_name = 'module_title.php'") or $this->DBError(__LINE__);
	}
	/**
	 * Rewrite text in geodesic_pages_messages_languages table to use new Classauction URLs
	 *
	 */
	function rewriteText() {
		$query = "select text from `geodesic_pages_messages_languages` where `text` like '%index.php%' or `text` like '%place_an_auction_details_fields%'";
		$result = $this->Execute($query, true) or $this->DBError(__LINE__);

		while($row = $result->FetchRow()) {
			$text = urldecode($row['text']);

			$text = preg_replace_callback("/index\.php\?a=(1[0-9]{3})/", function($matches){
				return "index.php?a=".($matches[1] > 1000 ? $matches[1] - 1000 : $matches[1]);
			}, $text);
			$text = preg_replace_callback("/index\.php\?a=28&b=(1[0-9]{4})/", function($matches){
				return "index.php?a=28&b=".($matches[1] > 10000 ? $matches[1] - 10000 : $matches[1]);
			}, $text);
			
			$text = str_replace("index.php?a=4&b=15", "index.php?a=4&b=22", $text);
			$text = str_replace("index.php?a=4&b=16", "index.php?a=4&b=21", $text);
			$text = str_replace("index.php?a=4&b=17", "index.php?a=4&b=19", $text);
			$text = str_replace("index.php?a=4&b=18", "index.php?a=4&b=20", $text);
			$text = str_replace("place_an_auction_details_fields", "place_an_ad_details_fields", $text);


			$query = "update geodesic_pages_messages_languages set text = '".$text."' where text = '".$row['text']."'";
			$this->Execute($query) or $this->DBError(__LINE__);
		}
	}
	/**
	 * Renumber sections that are higher than 1000
	 *
	 */
	function renumberModuleSections() {
		$query = "update geodesic_pages set section_id = section_id - 1000 where section_id > 1000";
		$this->Execute($query);
		$query = "update geodesic_pages_sections set section_id = section_id - 1000 where section_id > 1000";
		$this->Execute($query);
	}
	/**
	 * Set the password back to 'geodesic' since it isn't encrypted in Classauctions, but was in Auctions
	 *
	 */
	function resetPassword() {
		$this->connectDB();
		$query = "update geodesic_logins set password = 'geodesic' where id = 1";
		$this->db->Execute($query) or $this->DBError(__LINE__);
	}
	/**
	 * Show notes that pertain to the Auctions upgrade
	 *
	 */
	function showNotes() {
		return "
			<strong>You have successfully updated your software to 2.0.10b.  <i>The upgrade is not yet finished.</i></strong><br />
			<div style='text-align: left; width: 100%;'>
				Although we've done all we can do to upgrade your software to 2.0.10b, there are a few things you may have to do yourself once the entire upgrade
					process is complete.  Please take a moment to review
				the following notes, and save them somewhere for future reference:<br />
				<ul>
					<li style='text-indent: -1em; padding-left: 1em;'>
						To use a security image with the login form, you will need to add an input field
						(<em>&lt;input name='b[securityCode]' maxlength='4' size='4' type='text'&gt;</em>)
						and the security image (<em>&lt;img src='security_image.php'&gt;</em>) in the logged-out portion of your <em>Display Logged In/Out HTML - 3</em> template.
					</li>
					<li style='text-indent: -1em; padding-left: 1em;'>
						If any of your templates have the word 'auction' in them, you may want to change this to 'listing' or remove it altogether if users are going
						to be placing both auctions and classifieds. We've done this for you where we can, but you'll have to do it yourself in your templates and modules.
					</li>
					<li style='text-indent: -1em; padding-left: 1em;'>
						Remove &lt;MAIN_CLASSIFIED_LEVEL_NAVIGATION_1&gt; tag from the bottom of BASIC PAGE TEMPLATE - 1 if it exists. This prevents the category navigation
						list from being displayed at the bottom of the certain pages.
					</li>
					<li style='text-indent: -1em; padding-left: 1em;'>
						Attach a template to the Listing Process &raquo; Choose Listing Type Page if you are upgrading to ClassAuctions. This is a new page
						that allows users to choose which type of listing they would like to place -- auction or classified.
					</li>
					<li style='text-indent: -1em; padding-left: 1em;'>
						Assign templates for auction-/classified-specific listing display pages
					</li>
					<li style='text-indent: -1em; padding-left: 1em;'>
						You may need to create and attach classified-specific templates for listing display pages.
					</li>
				</ul>
			</div><br />
<input type=\"submit\" onclick=\"javascript:location.href='../../index.php?run=continue'\" value=\"Next &gt;\" />";
	}
}





// Stop execution in this file if $embedUpgrade is set. Used in the setup script to embed the main upgrade.
if(isset($embedUpgrade))
	return;

$upgrade = new Upgrade();

//make sure it is not already updated.
if ($upgrade->getOldVersion() == $upgrade->versionNumber){
	
}

$upgrade->init();


?><!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>Geodesic Update Routine</title>
<link rel="stylesheet" href="../../css/install.css" type="text/css" />
<?php echo $upgrade->redirect; ?>

<?php echo $upgrade->header; ?>
</head>
<body onload='<?php echo $upgrade->onload; ?>'>

	<div id="outerBox">
		<div id="login_box">
			<div id="login_sub">
				<div id="login_left">
					<div id="login_left_list"></div>
					<ul>
						<li style="list-style-image: none; list-style: none;">&nbsp;</li>
						<li><a href="http://geodesicsolutions.com/support/wiki/" onclick="window.open(this.href); return false;">User Manual</a></li>
						<li><a href="http://geodesicsolutions.com/geo_user_forum/index.php" onclick="window.open(this.href); return false;">User Forum</a></li>
						<li><a href="http://geodesicsolutions.com/support/helpdesk/kb" onclick="window.open(this.href); return false;">Knowledgebase</a></li>
						<li><a href="https://geodesicsolutions.com/geo_store/customers" onclick="window.open(this.href); return false;">Client Area</a></li>
						<li><a href="http://geodesicsolutions.com/resources.html" onclick="window.open(this.href); return false;">Resources</a></li>
					</ul>
				</div>
				<div id="login_right">
					<h1 id="login_product_name">&nbsp;</h1>
					<h2 id="login_software_type">&nbsp;</h2>
					<div id="login_form_fields">
<p class="failed" style="text-align:left; font-weight:normal;"><br><br>Be sure to read the update instructions in the <a href="http://geodesicsolutions.com/support/wiki/update/start" target="_blank">user manual</a>, for important additional steps after the upgrade wizard is finished.</p>
<span style="text-align: left;"><?php echo $upgrade->body; ?></span>
					</div>
					<div id="login_copyright">Copyright 2001-2011. <a class="login_link" href="http://geodesicsolutions.com" onclick="window.open(this.href); return false;">Geodesic Solutions, LLC.</a><br />All Rights Reserved.</div>
				</div>
				<div style="clear: both;"></div>
			</div>
		</div>
	</div>

</body>
</html>