<?php

function dbtest(&$template)
{
    $errors = 0;
    $file = file_get_contents("dbtest.html");
    $file = "<form name=\"db_test\" action=\"index.php?a=sql\" method=\"post\">$file</form>";
    $template = str_replace("(!MAINBODY!)", $file, $template);
    $template = str_replace("(!BACK!)", "<input type=button name=back value=\"<< Back\" onClick=\"history.go(-1)\">",
        $template);

    if (!file_exists('../config.php')) {
        $template = str_replace("(!DRIVER_ERROR!)", "ERROR!!! No config.php file detected.
            Be sure to complete the previous step in full before continuing.", $template);
        $errors++;
    } else {
        include('../config.php');
        $db = ADONewConnection(DB_TYPE);
        if (!$db) {
            $template = str_replace("(!DRIVER_ERROR!)", "ERROR!!!  Unable to initialize database driver.<br>Check your
                installation of database drivers.", $template);
            $errors++;
        } else {
            $template = str_replace("(!DRIVER_ERROR!)", "", $template);
        }

        if ($persistent_connections) {
            //echo " Persistent Connection <bR>";
            if (!($db->PConnect($db_host, $db_username, $db_password, $database))) {
                $template = str_replace("(!CONNECT_ERROR!)", "ERROR!!!  Could not connect to database.<br>Please check
                    that your database login information in config.php is correct.", $template);
                $errors++;
            }
        } else {
            //echo " No Persistent Connection <bR>";
            if (!($db->Connect($db_host, $db_username, $db_password, $database))) {
                $template = str_replace("(!CONNECT_ERROR!)", "ERROR!!!  Could not connect to database.<br>Please check
                    that your database login information in config.php is correct.<br><br>", $template);
                $errors++;
            }
        }

        if ($result = $db->Execute("SHOW TABLES")) {
            while ($tableResult = $result->FetchRow()) {
                if (
                    "geodesic_classifieds_logins" == $tableResult[0] ||
                    "geodesic_auctions_logins" == $tableResult[0] ||
                    "geodesic_classifieds" == $tableResult[0]
                ) {
                    $template = str_replace("(!VERSION_ERROR!)", "<br>The Wizard has detected that there is already an
                        older version of the software installed in the database.<br><br>", $template);
                    $template = str_replace("(!SUCCESS!)", "Please either follow the
                        <a href='https://geodesicsolutions.org/wiki/update/start' class='login_link'
                        target='_blank'> upgrade instructions</a> to preserve your old data
                        or manually remove the old tables from your database and restart this Wizard to continue with
                        a fresh install.<br /><br />If you are upgrading from an installation of GeoAuctions
                        Premier 2.0.4, GeoClassifieds Premier 2.0.4, or GeoClassifieds Basic 2.0.4 please
                        start the <a href='../pre_setup/' class='login_link'>pre-setup</a> routine.<br /><br />",
                        $template);
                    $errors++;
                    break;
                }
            }
        }
    }

    if (!$errors) {
        $template = str_replace(
            "(!SUCCESS!)",
            "<span style='font-weight: bold; size: 16px;'>Database Connection Successful!</span><br><br>\n\t" .
            "The Wizard will now create the tables in your MySQL database as well as populate those tables with the
            default data needed for your software installation. <br><br>
            This may take a minute or two, so please be patient.",
            $template
        );
        $template = str_replace("(!CONTINUE!)", '<div id="submit_button"><a href="index.php?a=sql"
            style="padding-top:.25em;">Continue</a></div>', $template);
        $template = str_replace("(!DRIVER_ERROR!)", "", $template);
        $template = str_replace("(!CONNECT_ERROR!)", "", $template);
    } else {
        $template = str_replace("(!SUCCESS!)", "After fixing these errors, please refresh your browser window to have
            the Wizard recheck your database connection.<br><br>", $template);
        $template = str_replace("(!CONTINUE!)", "", $template);
    }
}
