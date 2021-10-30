<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
<head>
<title>Geodesic Update Routine</title>
<link rel="stylesheet" href="css/install.css" type="text/css" />
<style type=text/css>
body {
    background-color:white;
    font-family: arial;
}
</style>
<?php

function debug($msg)
{
    $debug = 0;

    if ($debug) {
        echo $msg . "<br />";
    }
}

//ALTER TABLE `` RENAME ``
//SHOW TABLES LIKE 'GEO%'

include_once('../ini_tools.php');
//make sure it is at least 32 megs.
//geoRaiseMemoryLimit('32M');

include_once('../config.default.php');
include_once(CLASSES_DIR . 'adodb/adodb.inc.php');

class preSetup
{
    /**
     * Holds the ADODB db object
     *
     * @var object
     */
    var $db;
    function preSetup()
    {
        $this->_connectDB();
    }
    function _connectDB()
    {
        if (!(isset($this->db) && is_object($this->db))) {
            include(GEO_BASE_DIR . 'config.default.php');
            $this->db =& ADONewConnection($db_type);

            if (isset($persistent_connections) && $persistent_connections) {
                if (!$this->db->PConnect($db_host, $db_username, $db_password, $database)) {
                    echo 'Could not connect to database (persistent connection).';
                    exit;
                }
            } else {
                if (!$this->db->Connect($db_host, $db_username, $db_password, $database)) {
                    echo "Could not connect to database.";
                    exit;
                }
            }
        }
        //fix SQL strict mode.
        $this->db->Execute('SET SESSION sql_mode=\'\'');
        $this->db->SetFetchMode(ADODB_FETCH_ASSOC) ;
    }
    function checkVersion()
    {
        $result = $this->db->Execute("SELECT db_version FROM geodesic_version");
        if ($result) {
            $row = $result->FetchRow();
            if ($row[0] == "2.0.4") {
                return true;
            }
        }
        return false;
    }
    function getTables()
    {
        $all = $this->db->GetAll("SHOW TABLES");
        $tableArray = array();
        foreach ($all as $row) {
            foreach ($row as $table) {
                if (strpos($table, "geodesic_") === 0) {
                    //prevent dups, use table name as index.
                    $tableArray[$table] = $table;
                }
            }
        }

        return $tableArray;
    }
    function renameTable($table, $newName)
    {
        $result = $this->db->Execute("ALTER TABLE `" . $table . "` RENAME `" . $newName . "`");
        if ($result) {
            return true;
        } else {
            return false;
        }
    }
    function displayPage($page, $error = false)
    {
        switch ($page) {
            case "version":
                    $body .= "
					<form action='index.php' method='post' onSubmit='if(!document.getElementById(\"confimBackup\").checked){alert(\"Please confirm that you have backed up your database.\");return false;}'>
						<p style='margin:0;'>
							<b>Version Error</b>
							Your current version is out of date for this upgrade.  Please contact Geodesic Solutions for further instructions.
						</p>
					</form>";
                break;
            case "3":
                    $body .= "
					<form action='index.php' method='post' onSubmit='if(!document.getElementById(\"confimBackup\").checked){alert(\"Please confirm that you have backed up your database.\");return false;}'>
						<p style='margin:0;'>
							<b><br><br>Upgrade Complete!</b>
							Please login to your <a href='../admin' class='login_link'>Admin Panel</a> and check to see that all of your settings transferred over correctly.
							Enjoy your new upgrade!<br><br><br>
						</p>
					</form>";
                break;
            case "2":
                    $body .= "
					<form action='index.php' method='post' onSubmit='if(!document.getElementById(\"confimBackup\").checked){alert(\"Please confirm that you have backed up your database.\");return false;}'>
						<p style='margin:0;'>
							<b>Pre-Setup Complete!</b>
							You can now execute the standard SETUP for your Geodesic Product.<br><br>
							Step 1: <a href='../setup/' target=_blank class='login_link'>Standard Setup</a>
						</p>
						<br />
						<p style='margin:0;'>
							<b>IMPORTANT: After finishing the Standard Setup, please return to this window so the Wizard can transfer your old data to the new installation.</b>
							<br><br> Step 2: <a href='index.php?moveData' class='login_link'>Transfer my Old Data</a>
						</p>
					</form>";
                break;
            default:
            case "1":
                $body .= "
							<form action='index.php' method='post' onSubmit='if(!document.getElementById(\"confimBackup\").checked){alert(\"Please confirm that you have backed up your database.\");return false;}'>";
                if ($error !== false) {
                    $body .= "
								<p style='margin:0;'>
									<b>Error:</b>
									There was an error while trying to change your table's names for the upgrade.
									The upgrade stopped at the table `" . $error . "`.
								</p>
						 ";
                }
                $body .= "
								<p style='margin:0;'>
									<b>Pre-Setup:</b>
									The Pre-Setup process prepares your old installation for the upgrade.  It is important that you make back ups of your database before you start this process.
								</p>
								<br />
								<p style='margin:0;margin-top:10px;'>
									<b>Beware:</b>
									Please backup your database before allowing this script to alter your database. 
								</p>
								<br />
								<p style='text-align:center;'>
									<span style='font-size:12px;'>By checking this box you confirm that your database<br>
									has been preserved before executing this script.</span><input type='checkbox' id='confimBackup' name='confimBackup' value=1><br>
									<input type='submit' value='Upgrade!'>
								</p>
							</form>";
                break;
        }
        return $body;
    }
}
$preSetup = new preSetup();
if (isset($_REQUEST["moveData"])) {
    echo "<script>window.alert('This operation will take a few minutes')</script>";

    include("upgrade.php");
    $upgrade = new upgrade();
    $errorString = "";

    if ($upgrade->getUpgradeType() == "auctions") {
        $upgrade->initiateAuctionUpgrade();
    } else {
        $upgrade->initiateClassifiedUpgrade();
    }
    $errorString = false;
    foreach ($upgrade->tablesToCopy as $tableName) {
        set_time_limit(60);
        $oldTableName = isset($upgrade->renamedTables[$tableName]) ? $upgrade->renamedTables[$tableName] : "old_" . $tableName;
        debug(__line__ . 'old: ' . $oldTableName . ' new: ' . $tableName);
        $columnsToCopy = $upgrade->getColumnsToCopy($upgrade->getColumnNames($tableName), $upgrade->getColumnNames($oldTableName));
        if ($columnsToCopy) {
            $oldDataSql = $upgrade->getSelectSQL($oldTableName, $columnsToCopy);
            if ($tableName == 'geodesic_classifieds_price_plans') {
                //order it by the price plan ID so id 1 is first
                $oldDataSql .= " ORDER BY `price_plan_id`";
            }
            $oldDataResult = $upgrade->db->Execute($oldDataSql);
            if ($upgrade->getUpgradeType() == "auctions" && in_array("auction_id", $columnsToCopy)) {
                /*change auction_id to classified_id*/
                $id_key = array_search("auction_id", $columnsToCopy);
                $columnsToCopy[$id_key] = "classified_id";
            }
            if ($oldDataResult && $oldDataResult->RecordCount() > 0) {
                if (in_array($tableName, $upgrade->updateOnly)) {
                    while ($oldValues = $oldDataResult->FetchRow()) {
                        if ($upgrade->getUpgradeType() == "auctions" && array_key_exists("auction_id", $oldValues)) {
                            /*change auction_id to classified_id*/
                            $oldValues["classified_id"] = $oldValues["auction_id"];
                            unset($oldValues["auction_id"]);
                        }
                        $sql = $upgrade->getUpdateSQL($tableName, $columnsToCopy, $upgrade->getAssoc($oldValues));
                        if (!$upgrade->db->Execute($sql)) {
/*$errorString .= */debug(__line__ . "Error when updating $tableName ( $sql )<br>\n");
                        }
                    }
                } else {
                    $sql = $upgrade->getTruncateSQL($tableName);
                    if (!$upgrade->db->Execute($sql)) {
                        $errorString .= "Error when truncating " . $tableName . "<br>\n";
                    }

                    if ($tableName == 'geodesic_classifieds_sell_questions') {
                        $sql = $upgrade->getTruncateSQL("geodesic_classifieds_sell_questions_languages");
                        if (!$upgrade->db->Execute($sql)) {
                            $errorString .= "Error when truncating " . $tableName . "<br>\n";
                        }
                    }
                    //whether or not the default price plans have been configured yet or not.
                    $defaultPlansFinished = false;

                    while ($oldValues = $oldDataResult->FetchRow()) {
                        $oldPricePlanId = 0;
                        //price_plan fix
                        if ($tableName == "geodesic_classifieds_price_plans") {
                            if (!in_array('applies_to', $columnsToCopy)) {
                                $columnsToCopy[] = "applies_to";
                            }

                            if (!is_array($oldValues)) {
                                //need to see where this comes from
                                throw new Exception('Error, oldvalues not array, its: ' . print_r($oldValues, 1));
                            }

                            if (isset($oldValues['price_plan_id'])) {
                                $oldPricePlanId = $oldValues["price_plan_id"];
                                unset($oldValues["price_plan_id"]);

                                if (!$defaultPlansFinished) {
                                    //this is a default price plan, at least we'll pretend it is
                                    if ($upgrade->getUpgradeType() == 'auctions') {
                                        $oldValues['price_plan_id'] = 5;

                                        //insert default classified price plan
                                        $insertDefaultSql = "INSERT INTO `geodesic_classifieds_price_plans` (`price_plan_id`, `charge_per_ad_type`, `name`, `description`, `price_plan_expires_into`, `type_of_billing`, `charge_per_ad`, `featured_ad_price`, `featured_ad_price_2`, `featured_ad_price_3`, `featured_ad_price_4`, `featured_ad_price_5`, `bolding_price`, `attention_getter_price`, `charge_per_picture`, `better_placement_charge`, `ad_renewal_cost`, `subscription_billing_period`, `subscription_billing_charge_per_period`, `free_subscription_period_upon_registration`, `expiration_type`, `expiration_from_registration`, `max_ads_allowed`, `ad_and_subscription_expiration`, `instant_cash_renewals`, `instant_money_order_renewals`, `instant_check_renewals`, `allow_credits_for_renewals`, `use_featured_ads`, `use_featured_ads_level_2`, `use_featured_ads_level_3`, `use_featured_ads_level_4`, `use_featured_ads_level_5`, `use_bolding`, `use_better_placement`, `use_attention_getters`, `num_free_pics`, `invoice_max`, `initial_site_balance`, `buy_now_only`, `charge_percentage_at_auction_end`, `roll_final_fee_into_future`, `applies_to`, `delayed_start_auction`) VALUES
(1, 0, 'Silver Classifieds Plan', 'Classifieds Price Plan', 0, 1, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 1, 0.00, 0, 0, 0, 1000, 0, 0, 0, 0, 0, 1, 0, 0, 0, 0, 1, 1, 1, 0, 0.00, 0.00, 0, 0, 0, 1, 0)";
                                    } else {
                                        $oldValues['price_plan_id'] = 1;

                                        //insert default auction price plan
                                        $insertDefaultSql = "INSERT INTO `geodesic_classifieds_price_plans` (`price_plan_id`, `charge_per_ad_type`, `name`, `description`, `price_plan_expires_into`, `type_of_billing`, `charge_per_ad`, `featured_ad_price`, `featured_ad_price_2`, `featured_ad_price_3`, `featured_ad_price_4`, `featured_ad_price_5`, `bolding_price`, `attention_getter_price`, `charge_per_picture`, `better_placement_charge`, `ad_renewal_cost`, `subscription_billing_period`, `subscription_billing_charge_per_period`, `free_subscription_period_upon_registration`, `expiration_type`, `expiration_from_registration`, `max_ads_allowed`, `ad_and_subscription_expiration`, `instant_cash_renewals`, `instant_money_order_renewals`, `instant_check_renewals`, `allow_credits_for_renewals`, `use_featured_ads`, `use_featured_ads_level_2`, `use_featured_ads_level_3`, `use_featured_ads_level_4`, `use_featured_ads_level_5`, `use_bolding`, `use_better_placement`, `use_attention_getters`, `num_free_pics`, `invoice_max`, `initial_site_balance`, `buy_now_only`, `charge_percentage_at_auction_end`, `roll_final_fee_into_future`, `applies_to`, `delayed_start_auction`) VALUES
(5, 0, 'Silver Auctions Plan', 'Auctions Price Plan', 0, 1, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 1, 0.00, 0, 0, 0, 1000, 0, 0, 0, 0, 0, 1, 0, 0, 0, 0, 1, 1, 1, 0, 0.00, 0.00, 0, 0, 1, 2, 0)";
                                    }
                                    if (!in_array('price_plan_id', $columnsToCopy)) {
                                        //for this first one, need to insert price plan ID.
                                        $columnsToCopy[] = "price_plan_id";
                                    }
                                    $defaultPlansFinished = 1;
                                } else {
                                    unset($columnsToCopy[array_search("price_plan_id", $columnsToCopy)]);
                                }
                                debug(__line__ . " upgrade type: {$upgrade->getUpgradeType()} old price plan: $oldPricePlanId<br />");
                                if ($upgrade->getUpgradetype() == 'auctions') {
                                    $oldValues["applies_to"] = "2";
                                    $oldValues["name"] = "Auctions " . $oldValues["name"];
                                } else {
                                    $oldValues["applies_to"] = "1";
                                    $oldValues["name"] = "Classified " . $oldValues["name"];
                                }
                            }
                        } elseif ($tableName == 'geodesic_classifieds_sell_questions') {
                            //need to copy for each of the languages...
                            $upgrade->fixQuestionLanguages($oldValues);
                        }

                        if ($upgrade->getUpgradeType() == "auctions" && array_key_exists("auction_id", $oldValues)) {
                            /*change auction_id to classified_id*/
                            $oldValues["classified_id"] = $oldValues["auction_id"];
                            unset($oldValues["auction_id"]);
                        }

                        $sql = $upgrade->getInsertSQL($tableName, $columnsToCopy, $oldValues);
                        if (!$upgrade->db->Execute($sql)) {
                            /* $errorString .=*/
                            debug(__line__ . " <strong>Error</strong> when inserting into $tableName Error: " . $upgrade->db->ErrorMsg() . "<br>\n");
                            $newPricePlanId = 0;
                        } else {
                            $newPricePlanId = $upgrade->db->Insert_Id();
                        }

                        if ($oldPricePlanId) {
                            debug(__line__ . " new id: $newPricePlanId");
                            $upgrade->pricePlanMap[$oldPricePlanId] = $newPricePlanId;

                            if ($insertDefaultSql) {
                                //run the "opposite" price plan to make sure it's in there.
                                if (!$upgrade->db->Execute($insertDefaultSql)) {
                                    debug(__line__ . " <strong>Error</strong> when inserting ALTERNATE PRICE PLAN into $tableName Error: " . $upgrade->db->ErrorMsg() . "<br>\n");
                                }
                                $insertDefaultSql = '';//unset it, don't need to keep running it over and over
                            }
                        }
                    }
                }
            }
        }
    }
    // in_statement fix
    debug(__line__);
    $dataResult = $upgrade->db->Execute($upgrade->getSelectSQL("geodesic_categories", array("*")));
    if ($dataResult->RecordCount() > 0) {
        while ($oldValues = $dataResult->FetchRow()) {
            $in_statement = $upgrade->get_sql_in_statement($oldValues["category_id"]);
            $updateStatement = $upgrade->getUpdateSQL("geodesic_categories", array("in_statement"), array("in_statement" => $in_statement), array("category_id",$oldValues["category_id"]));
            if (!$upgrade->db->Execute($updateStatement)) {
                $errorString .= "Error when updating geodesic_categories in_statements<br>\n";
            }
        }
    }
    //re-map price plans
    if (!isset($upgrade->pricePlanMap[0])) {
        //make it map price plan of 0 to the default "alternate" price plan
        $upgrade->pricePlanMap[0] = ($upgrade->getUpgradeType() == "auctions") ? 5 : 1;
    }
    // user subscription fix
    $dataResult = $upgrade->db->Execute($upgrade->getSelectSQL("geodesic_classifieds_user_subscriptions", array("*")));
    if ($dataResult->RecordCount() > 0) {
        while ($subscriptionValues = $dataResult->FetchRow()) {
            $subscriptionValues["price_plan_id"] = $upgrade->pricePlanMap[$subscriptionValues["price_plan_id"]];
            $updateStatement = $upgrade->getInsertSQL("geodesic_classifieds_user_subscriptions", array("price_plan_id","user_id","subscrption_expire","notice_sent"), $upgrade->getAssoc($subscriptionValues));
            if (!$upgrade->db->Execute($updateStatement)) {
                $errorString .= "Error when updating geodesic_classifieds_user_subscriptions<br>\n";
            }
        }
    }

    // group price plan fix
    $dataResult = $upgrade->db->Execute($upgrade->getSelectSQL("geodesic_groups", array("*")));
    if ($dataResult->RecordCount() > 0) {
        $dataArray = $dataResult->GetAll();
        foreach ($dataArray as $groupValues) {
            if ($upgrade->getUpgradeType() == "auctions") {
                $classId = 1;
                $aucId = $upgrade->pricePlanMap[$groupValues["price_plan_id"]];
                if (!$aucId) {
                    $aucId = 5;
                }
            } else {
                $classId = $upgrade->pricePlanMap[$groupValues["price_plan_id"]];
                if (!$classId) {
                    $classId = 1;
                }
                $aucId = 5;
            }
            $groupId = (int)$groupValues["group_id"];
            if (!$groupId) {
                $groupId = 1;
            }
            $updateStatement = $upgrade->getUpdateSQL("geodesic_groups", array("price_plan_id","auction_price_plan_id"), array("price_plan_id" => $classId, "auction_price_plan_id" => $aucId), array("group_id",$groupId));
            if (!$upgrade->db->Execute($updateStatement)) {
                $errorString .= "Error when updating geodesic_classifieds_user_subscriptions<br>\n";
            }
        }
    }

    // attach alternate price plan to groups
    $dataResult = $upgrade->db->Execute($upgrade->getSelectSQL("geodesic_user_groups_price_plans", array("*")));
    if ($dataResult->RecordCount() > 0) {
        $dataArray = $dataResult->GetAll();
        foreach ($dataArray as $groupValues) {
            if ($upgrade->getUpgradeType() == "auctions") {
                $classId = 1;
                $aucId = $upgrade->pricePlanMap[$groupValues["price_plan_id"]];
                if (!$aucId) {
                    $aucId = 5;
                }
            } else {
                $classId = $upgrade->pricePlanMap[$groupValues["price_plan_id"]];
                $aucId = 5;
                if (!$classId) {
                    $classId = 1;
                }
            }
            $updateStatement = $upgrade->getUpdateSQL("geodesic_user_groups_price_plans", array("price_plan_id","auction_price_plan_id"), array("price_plan_id" => $classId, "auction_price_plan_id" => $aucId), array("id",$groupValues["id"]));

            if (!$upgrade->db->Execute($updateStatement)) {
                $errorString .= "Error when updating geodesic_user_groups_price_plans<br>\n";
            }
        }
    }

    // attach base subscription periods
    $dataResult = $upgrade->db->Execute($upgrade->getSelectSQL("geodesic_classifieds_price_plans", array("*")));
    if ($dataResult->RecordCount() > 0) {
        $dataArray = $dataResult->GetAll();
        //truncate the table in case we're running this again
        if (!$upgrade->db->Execute($upgrade->getTruncateSQL('geodesic_classifieds_subscription_choices'))) {
            debug(__line__ . ' error: ' . $upgrade->db->ErrorMsg());
        }
        foreach ($dataArray as $pricePlanValues) {
            $updateStatement = $upgrade->getInsertSQL("geodesic_classifieds_subscription_choices", array("price_plan_id","display_value","value","amount"), array("price_plan_id" => $pricePlanValues["price_plan_id"],"display_value" => $pricePlanValues["subscription_billing_period"],"value" => $pricePlanValues["subscription_billing_period"],"amount" => $pricePlanValues["subscription_billing_charge_per_period"]));
            if (!$upgrade->db->Execute($updateStatement)) {
                $errorString .= "Error when updating geodesic_classifieds_subscription_choices<br>\n";
            }
        }
    }

    //add attention getters
    if (!$upgrade->fixAttentionGetters()) {
        $errorString .= "Error when updating Attention Getters<br>\n";
    }

    $preSetup->body = $preSetup->displayPage("3", $errorString);
} elseif (isset($_POST["confimBackup"])) {
    foreach ($preSetup->getTables() as $table) {
        if (!$preSetup->renameTable($table, "old_" . $table)) {
            $preSetup->body = $preSetup->displayPage("1", $table);
            break;
        }
    }
    $preSetup->body = $preSetup->displayPage("2");
} else {
    if (!$preSetup->checkVersion()) {
        $preSetup->body = $preSetup->displayPage("version");
    }
    $preSetup->body = $preSetup->displayPage("1");
}
?>
<?php echo $preSetup->header; ?>
</head>
<body style='margin-top:15%;' onload='<?php echo $preSetup->onload; ?>'>

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
                    <?php echo $preSetup->body; ?>
                    </div>
                    <div id="login_copyright">Copyright 2001-2011. <a class="login_link" href="http://geodesicsolutions.com" onclick="window.open(this.href); return false;">Geodesic Solutions, LLC.</a><br />All Rights Reserved.</div>
                </div>
                <div style="clear: both;"></div>
            </div>
        </div>
    </div>

</body>
</html>
