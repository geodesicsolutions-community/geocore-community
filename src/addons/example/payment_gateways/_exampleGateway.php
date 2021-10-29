<?php
//addons/example/payment_gateways/_exampleGateway.php
/**
 * Optional file, not used by system since it starts with underscore, but if
 * that was not the case it would be used as a payment gateway.
 * 
 * This file will contain no example payment gateway, instead we maintain developer
 * documentation in 2 main "payment gateway templates" located in the main
 * software at:
 * 
 * "Main" payment gateway template:
 * classes/payment_gateways/_template.php
 * 
 * "CC Collection" payment gateway template:
 * classes/payment_gateways/_cc_template.php
 * 
 * See those files for further documentation.  (if your payment gateway needs
 * to collect CC info, start from the _cc_template.php, if your payment does NOT
 * collect CC info, use _template.php)
 * 
 * @package ExampleAddon
 */

/**************************************************************************
Addon Created by Geodesic Solutions, LLC
Copyright (c) 2001-2013 Geodesic Solutions, LLC
All rights reserved
http://geodesicsolutions.com
see license attached to distribution
**************************************************************************/
##########GIT Build Data##########
## 
## File Changed In GIT Commit:
## ##    ccda4ac
## 
##################################

# Example Addon

/**
 * This file is empty, for a good reason: we will be maintaining 2 "template"
 * payment gateways in the "base" code, located at:
 * 
 * classes/payment_gateways/_template.php
 * classes/payment_gateways/_cc_template.php
 * 
 * Use the appropriate file above (_cc_template.php if collecting CC info in 
 * the software,_template.php if not) as a starting point for creating your own 
 * payment gateway. The files mentioned above will contain the most up to date
 * documentation for payment gateways, in order to reduce redundency we do not 
 * maintain seperate documentation in the example addon.
 * 
 * To be clear, you CAN create a payment gateway inside of an addon, by creating
 * a payment_gateways/ directory, and placing your custom payment gateway in
 * that directory.  You can even have multiple payment gateways added by a
 * single addon.
 * 
 * This file, by default, starts with an underscore, because when the system
 * is parsing addon's directories for payment gateways, it will skip over any
 * files that start with an underscore.  That is the same reason you will find
 * other files in the payment_gateways/ directory in the main software that
 * start with an underscore, such as the _template.php file.  Such files are
 * also "skipped over" when looking for payment gateways to be used in the
 * system.
 */

