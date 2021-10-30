<?php

/*
 *	Copyright (c) 2004 Geodesic Solutions, LLC
 *	GeoInstaller
 *	All rights reserved
 *	http://www.geodesicsolutions.com
 *
 *	Module:		SQL Module
 *	Filename:	sql.php
 */

function sql($db, &$template)
{
    include("product.php");
    // Check for how many sql files are in the directory
    if ($handle = opendir('../sql/')) {
        $file_array = array();
        while (false !== ($file = readdir($handle))) {
            if ($file != "." && $file != ".." && preg_match("/^[a-z0-9_]+\.sql$/i", $file)) {
                $file_array[] = '../sql/' . $file;
            }
        }

        if (!sort($file_array)) {
            echo "Internal Error.  Couldnt sort filenames.<br>\n";
            return 1;
        }

        reset($file_array);

        // Open the files one by one and execute the queries.
        // *    This loop simply runs through every single file
        // *    and runs every query in the file and checks them.
        $key_to_files = isset($_REQUEST["key"]) ? $_REQUEST["key"] : 0;

        for ($i = 0; $i < $key_to_files; $i++) {
            next($file_array);
        }

        if ($key_to_files < count($file_array)) {
            if (current($file_array)) {
                //echo current($file_array).'<br>';
                //echo is_string(current($file_array)).'<br>';
                splitSqlFile(current($file_array), $db);
                //redirect to self
                $url_path = str_replace(INSTALL, "install_redirect.php", $_SERVER["PHP_SELF"]);
                $redirect_url = "http://" . $_SERVER["HTTP_HOST"] . $url_path . "?key=" . ($key_to_files + 1) . "&install=" . INSTALL . "&total=" . (count($file_array));
                header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
                header('Pragma: no-cache');
                header("Location: " . $redirect_url);
                echo $template;
                exit;
            }
        } else {
            return 1;
        }

        // Traverse to the next element in the array
        reset($file_array);
    } else {
        echo "Error opening sql directory.  Place Check your permissions on this directory and that it exists.<bR>\n";
        return 1;
    }

    return 0;
}

function splitSqlFile($filename, $db)
{
    $handle = fopen($filename, 'r');
    if ($handle) {
        $buffer = '';
        while (!feof($handle)) {
            $this_buffer = fgets($handle, 4096);
            //$this_buffer = rtrim($buffer);
            if (substr(ltrim($this_buffer), 0, 1) == '#' || substr(ltrim($this_buffer), 0, 2) == '--') {
                //comment line
                continue;
            }
            $buffer .= $this_buffer;
            //$buffer = rtrim($buffer);
            if (substr(rtrim($buffer), -1) == ';') {
                //end of query, add query
                $result = $db->Execute($buffer);
                if (!$result) {
                    //do not continue.
                    die('<span style="color:red; font-weight:bold;">Critical Installation Error:</span> The SQL query below produced an error.  The setup cannot continue until the problem has been fixed.  Contact Geo Support if you require assistance.<br />
		<strong>Query:</strong> ' . $buffer . '<br />
		<strong>DB Error Message: </strong>' . $db->ErrorMsg() . '<br /><br />');
                }
                $buffer = '';
            }
        }
    }
}

function run_upgrade($template, $filename)
{
    //the upgrade step is no longer done!

    include("../config.php");
    include("product.php");

    if (isset($product_type) && $product_type == 3 && file_exists("../" . $install[$product_type]["upgrade"]) && file_exists("../upgrade/index.php")) {
        echo "Fatal Error: You must remove ../" . $install[$product_type]["upgrade"] . " or ../upgrade";
        exit;
    }

    $nextButton = "
		<form action=\"" . INSTALL . "?a=site\" method=\"POST\">
			<input type=\"submit\" name=\"continue\" value='Continue >>'>
		</form>";

    /* if(file_exists("../".$install[$product_type]["upgrade"])) {
        $replace = array(
            "(!MAINBODY!)" => file_get_contents("upgrade.html"),
            "(!UPGRADE_IFRAME!)" => "<iframe src=\"".$filename."\" width=\"90%\"></iframe>",
            "(!UPGRADE_MESSAGE!)" => "Your software is being upgraded.  Please wait for a completion message to appear in the box below before pressing continue.",
            "(!CONTINUE!)" => $nextButton
            );
        return str_replace(array_keys($replace), array_values($replace), $template);
    } else*/ if (file_exists("../upgrade/index.php")) {
        $embedUpgrade = true;
        include("../upgrade/index.php");
        $nextButton = "
			<form action=" . INSTALL . "?a=site method=post>
				<input type=submit name=continue value='Continue >>' id='nextButton' disabled>
			</form>";
        $upgrade = new Upgrade("../upgrade/", $nextButton);

        $upgrade->doToCAELatest();
        $upgrade->body .= "
			Updating software<br />
			<br />
			" . $upgrade->addLoadingBar("doToCAELatest", $upgrade->totalQueries) . $nextButton;
        $replace = array(
            "(!header!)" => $upgrade->header,
            "(!MAINBODY!)" => $upgrade->body
            );
        return str_replace(array_keys($replace), array_values($replace), $template);
}
}
