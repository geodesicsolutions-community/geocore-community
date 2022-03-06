<?php

###########################################
## Auto-generated file
## It is not recommended to edit
## this file directly, but you can
## if you want.
##
## Generated: Dec 14, 2009 23:04:42
###########################################

$return = array();

###########################################
## Template Attachments
###########################################

/**
 * Template Attachment(s)  Syntax:
 *
 * Category (or site-wide/default) Attachments:
 * $return [language_id(int)][category_id(int)] = template filename (string);
 *
 * Affiliate Group-Specific Template Attachments:
 * $return ['affiliate_group'] [language_id(int)] [group_id(int)] = template filename (string);
 *
 * Extra Page {main_body} sub-template attachments:
 * $return ['extra_page_main_body'] [language_id(int)] [0] = template filename (string);
 *
 * Note: Template assignment for language 1 category 0 should always be specified,
 * in order to avoid template not found errors.
 */


$return [1] [0] = 'basic_page.tpl';
$return ['extra_page_main_body'] [1] [0] = 'extra_pages/about_us.tpl';

return $return;
