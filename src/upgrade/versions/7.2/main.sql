#All queries in this file will be run.  Comments should be
# on lines by themselves, and start with # or --
# Queries should end with ;
# All queries in this file are considered required, if it
# fails, the upgrade will stop.  If you dont want this,
# see the conditional_sql.php file.

# NOTE: These queries should be able to be imported to PHPMyAdmin
# if a manual upgrade is needed.

INSERT IGNORE INTO `geodesic_cron` (`task`,`type`,`last_run`,`running`,`interval`) VALUES ('send_new_listing_alert_emails','main','0','0','3600');