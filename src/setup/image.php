<?php

function image(&$template, $product_id, $error = 0)
{
    $file = file_get_contents("image.html");
    $file = "<form name=save action=index.php?a=image_save method=post>" . $file;
    $template = str_replace("(!MAINBODY!)", $file, $template);
    $template = str_replace(
        "(!BACK!)",
        "<input type=button name=back value=\"<< Back\" onClick=\"history.go(-1)\">",
        $template
    );

    // Use GD library imagecreatetruecolor
    $template = str_replace(
        "(!GD_LABEL!)",
        "<b>imagecreatetruecolor (GD Library 2.0+) switch:</b><br>Select \"Do not use\" if gdLibrary 2.0+ is not
            installed on your server.",
        $template
    );
    $template = str_replace(
        "(!GD_VALUE_0!)",
        "<input type=radio name=config[gdlib] value=0 checked>Default, use imagecreatruecolor",
        $template
    );
    $template = str_replace(
        "(!GD_VALUE_1!)",
        "<input type=radio name=config[gdlib] value=1>Do not use imagecreatruecolor",
        $template
    );

    // URL path to image upload directory
    $template = str_replace(
        "(!URL_PATH_LABEL!)",
        "<b>URL path to image directory if allow uploaded to a file:</b><br>If you save uploaded images then the
            software will need a place to store these images.  Please specify it here.",
        $template
    );
    $string = "<input name=config[url_image_directory] size=40 type=text ";
    if (strlen(trim($error['url_image_directory_value'])) != 0) {
        $string .= "value=" . $error['url_image_directory_value'] . ">";
    } else {
        // Check for specific image directory
        if ($product_id == 1) {
            $string .= "value=aucimages/>";
        } elseif ($product_id == 2) {
            $string .= "value=user_images/>";
        } elseif ($product_id == 4) {
            $string .= "value=images/>";
        } else {
            $string .= "value=user_images/>";
        }
    }
    $template = str_replace("(!URL_PATH!)", $string, $template);
    if ((strlen(trim($error['url_image_directory'])) != 0) || (strlen(trim($error['file_perm'])) != 0)) {
        $template = str_replace("(!URL_PATH_ERROR!)", $error['file_perm'], $template);
    } else {
        $template = str_replace("(!URL_PATH_ERROR!)", "", $template);
    }

    // Discover the file upload path
    $upload_path = $_SERVER["SCRIPT_FILENAME"];
    $path = pathinfo($upload_path);
    if (preg_match("|[a-z]*geoinstall[a-z]*[/]*|i", $path['dirname'])) {
        $path['dirname'] = preg_replace('|[a-z]*geoinstall[a-z]*[/]*|i', '', $path['dirname']);
    } elseif (preg_match("|/[a-z]*install[a-z]*[/]*|i", $path['dirname'])) {
        $path['dirname'] = preg_replace('|[a-z]*install[a-z]*[/]*|i', '', $path['dirname']);
    } elseif (preg_match("|/[a-z]*setup[a-z]*[/]*|i", $path['dirname'])) {
        $path['dirname'] = preg_replace('|[a-z]*setup[a-z]*[/]*|i', '', $path['dirname']);
    }
    // Check for different products
    if ($product_id == 1) {
        $upload_path = $path['dirname'] . 'aucimages/';
    } elseif ($product_id == 2) {
        $upload_path = $path['dirname'] . 'user_images/';
    } else {
        $upload_path = $path['dirname'] . 'user_images/';
    }

    // Directory path to images directory
    $template = str_replace(
        "(!DIR_PATH_LABEL!)",
        "<b>absolute directory path to image directory if allow uploaded to a file:</b><br>If you allow uploading of
            images, the absolute path of that directory must be <br>specified.  Make sure that there is a trailing
            slash on this pathname. We have <br>prepopulated this field with what appears to be the correct path to
            your default<br> \"images\" folder.  However, you may need to correct this path as some servers <br>will
            not provide us with the exact data needed to populate the field below.<br><b>Yahoo Hosting</b> customers
            should enter the same path used above in the<br> \"URL path to image directory\" field. For example, both
            fields should be identical.",
        $template
    );
    $string = "<input name=config[abs_image_directory] size=55 type=text ";
    if (strlen(trim($error['abs_image_directory_value'])) != 0) {
        $string .= "value=\"" . $error['abs_image_directory_value'] . "\">";
    } else {
        $string .= "value=\"" . $upload_path . "\">";
    }
    $template = str_replace("(!DIR_PATH!)", $string, $template);
    if (strlen(trim($error['abs_image_directory'])) != 0) {
        $template = str_replace("(!DIR_PATH_ERROR!)", $error['abs_image_directory'], $template);
    } else {
        $template = str_replace("(!DIR_PATH_ERROR!)", "", $template);
    }

    // Max size of uploaded files
    $template = str_replace(
        "(!UPLOAD_SIZE_LABEL!)",
        "<b>Maximum size of uploaded file:</b><br>Set the maximum file size (in bytes) that a user may upload.",
        $template
    );
    $string = "<input name=config[maximum_upload_size] type=text ";
    if (strlen($error['maximum_upload_size']) != 0) {
        $string .= "value = " . $error['maximum_upload_size_value'] . "> bytes";
        $template = str_replace("(!UPLOAD_SIZE_ERROR!)", $error['maximum_upload_size'], $template);
    } else {
        // If no value then set to 100000000
        $string .= "value=10000000> bytes";
        $template = str_replace("(!UPLOAD_SIZE_ERROR!)", "", $template);
    }
    $template = str_replace("(!UPLOAD_SIZE!)", $string, $template);

    $template = str_replace(
        "(!SUBMIT!)",
        "<div id='submit_button'><input type='submit' class='theButton' value='Save'></div>",
        $template
    );
    $template = str_replace(
        "(!SKIP!)",
        "<a href=index.php?a=registration class='skip_link'>Skip this step</a></form>",
        $template
    );

    return 0;
}

function image_save($db, $product, $config)
{
    // Supress warnings so when error occurs all hell doesnt break loose
    error_reporting(E_ERROR | E_PARSE);

    $errors = 0;

    if ((strlen(trim($config['url_image_directory'])) == 0)) {
        $error_code['url_image_directory'] = 'Please enter a valid directory.<br>';
        $errors++;
    }
    if (is_dir('../' . $config['url_image_directory'])) {
        if (strlen($error_code['url_image_directory']) == 0) {
            $error_code['url_image_directory'] = '';
        }

        // Check file permissions
        if (!is_readable("../config.php") && !is_writable("../config.php")) {
            if (!@chmod("../config.php", 0777)) {
                $error_code["file_perm"] = "Unable to set file permissions.  Please change permissions on the
                    images directory manually to 777 for Unix/Linux Servers or Read/Write/Execute for Windows Servers
                    and then press skip below.";
                $errors++;
            }
        }
    } else {
        // Reaches here if directory is not valid
        $error_code['url_image_directory'] = 'The filename you entered is not a valid directory.  Please enter a valid
            directory.<br>';
        $error_code['url_image_directory_value'] = $config['url_image_directory'];
        $errors++;
    }

    if ((strlen(trim($config['abs_image_directory'])) == 0)) {
        $error_code['abs_image_directory'] = 'Please enter a valid directory.<br>';
        $errors++;
    }
    if (!is_dir($config['abs_image_directory'])) {
        // Reaches here if directory is not valid
        $error_code['abs_image_directory'] = 'The filename you entered is not a valid directory.  Please enter a valid
            directory.<br>';
        $error_code['abs_image_directory_value'] = $config['abs_image_directory'];
        $errors++;
    }

    if ($config['maximum_upload_size'] <= 0) {
        // Gets here if negative or 0 max upload size
        $error_code['maximum_upload_size'] = '<br>The maximum upload size cannot be less than or equal to zero.';
        $error_code['maximum_upload_size_value'] = $config['maximum_upload_size'];
        $errors++;
    }

    if ($errors > 0) {
        return $error_code;
    }

    $sql_query = "UPDATE `" . $product['ad_config'] . "` SET " . $product['upload'] . " = 2, "
        . $product['url_image_directory'] . " = \"" . $config['url_image_directory'] . "\", "
        . $product['maximum_upload_size'] . " = " . $config['maximum_upload_size'] . ", "
        . $product['abs_image_directory'] . " = \"" . $config['abs_image_directory'] . "\"";
    $result = $db->Execute($sql_query);
    //echo $sql_query.'<br>';
    if (!$result) {
        echo "Error executing query: " . $sql_query . '<br>';
        return 1;
    } else {
        return 0;
    }
}
