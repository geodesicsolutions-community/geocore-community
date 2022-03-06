<?php

/*
 * This is where conditional queries go.
 * For cases where an sql query might not be run, in the
 * case that it is not run, add an empty string
 * for the query.
 */

/*
 * There needs to be the same number of sql queries generated, no
 * matter what, otherwise the sql index will be off from the database.
 * That is the reason to use an empty string in cases where an "optional" query
 * is not run.
 */

//conditional sql queries.
$sql_strict = array (
//array of sql queries, if one of these fail, it
//does not continue!

);

$sql_not_strict = array (
//array of sql queries, if one of these fail, it
//just ignores it and keeps chugin along.

);

//Add queries like this...
#$sql_not_strict[] = "SQL QUERY";
#$sql_strict[] = "SQL QUERY";

$sql_not_strict[] = "ALTER TABLE `geodesic_classifieds_sell_questions_languages` ADD `search_as_numbers` TINYINT(1) NOT NULL DEFAULT '0'";
