<?php

// Create new IP Logger table ("ip_logger_localization")
// This table contains all available modules to get geoInfos for the visitors
$wpdb->get_var("CREATE TABLE ".$wpdb->prefix."ip_logger_localization (" .
		"code varchar(20) NOT NULL," .
		"name VARCHAR(100) NOT NULL," .
		"include_filename VARCHAR(100) NULL," .
		"author_name VARCHAR(100) NOT NULL," .
		"author_website VARCHAR(200) NULL," .
		"author_email VARCHAR(200) NULL," .
		"information text," .
		"active SET('Y','N') NOT NULL DEFAULT 'Y')");

// Add the option "None" to the table
// If this module is selected, no geographic details will be loaded for the visitors
if ($wpdb->get_var("select count(*) from ".$wpdb->prefix."ip_logger_localization where code='none'") == 0)
$wpdb->get_var("INSERT INTO ".$wpdb->prefix."ip_logger_localization (" .
		"code,name,include_filename,author_name,author_website,author_email,information,active) VALUES (" .
		"'none','None','none','M. Retzlaff','www.mretzlaff.com/','info@mretzlaff.com','No geographic details will be read and saved for your visitors','Y')");

// Add the currently used module ("IP Details class") to the table
if ($wpdb->get_var("select count(*) from ".$wpdb->prefix."ip_logger_localization where code='ipdetails'") == 0)
$wpdb->get_var("INSERT INTO ".$wpdb->prefix."ip_logger_localization (" .
		"code,name,include_filename,author_name,author_website,author_email,information,active) VALUES (" .
		"'ipdetails','IP Details','class.ipdetails','Chetan Mendhe',NULL,'xtrmcoder@gmail.com','The visitors IP will be sent to an 3rd party server to get more details about those IP (eg. geographic details like Country and City). You will need \"curl\" support for this method.','Y')");

// Add the currently used module ("IP Details class") to the table
if ($wpdb->get_var("select count(*) from ".$wpdb->prefix."ip_logger_localization where code='pidgets'") == 0)
$wpdb->get_var("INSERT INTO ".$wpdb->prefix."ip_logger_localization (" .
		"code,name,include_filename,author_name,author_website,author_email,information,active) VALUES (" .
		"'pidgets','GeoIP by Pidgets','pidgets','Rasmus Lerdorf','pidgets.com',null,'The visitors IP will be sent to an 3rd party server to get more details about those IP (eg. geographic details like Country and City)','Y')");

// Add the WP option to remember the selected localization module
add_option("ip_logger_active_localization_module", "ipdetails");

?>
