<?php

//addons/security_image/security_image.php

# Security Image Addon

# Stand-alone file that pushes the security image to the browser.

header('Location: ../../index.php?a=ap&addon=security_image&page=image&no_ssl_force=1', true, 301);
exit;

//LOL not so stand-alone after all!  This has been converted to use the addon pages functionality,
//so that it can be called using the above URL location.
