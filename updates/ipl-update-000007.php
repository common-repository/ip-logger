<?php

// Create new IP Logger table ("ip_logger_ignore")
// This table contains all conditions for visitors to be ignored within the charts
$wpdb->get_var("CREATE TABLE ".$wpdb->prefix."ip_logger_ignore (" .
		"id bigint(11) NOT NULL auto_increment," .
		"target VARCHAR(100) NOT NULL," .
		"filter VARCHAR(100) NOT NULL," .
		"PRIMARY KEY(id))");

// Add all known WordPress users to the filter
// If this module is selected, no geographic details will be loaded for the visitors
$wpdb->get_var("INSERT INTO ".$wpdb->prefix."ip_logger_ignore (target,filter) " .
		"SELECT 'WP Backend User', user_login " .
		"FROM ".$wpdb->prefix."users");

// Default: "Ignorationfilter" is disabled
add_option("ip_logger_ignore_filter_enabled", "0");

?>
