<?php

function config_check(&$template, $error = 0)
{
    // Replace (!MAINBODY!) with file template
    $file = file_get_contents("config.html");
    $file = "<form name=version_comp action=index.php?a=config.php_check method=post>\n\t" . $file;
    $template = str_replace("(!MAINBODY!)", $file, $template);
}

function write_config($config, $product_type)
{
    // Function deprecated till fixed
    return 0;
}
