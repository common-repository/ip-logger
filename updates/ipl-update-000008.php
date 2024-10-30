<?php

// Create new IP Logger table ("ip_logger_statistics")
// This table will contain the casic data for the charts
$wpdb->get_var("CREATE TABLE ".$wpdb->prefix."ip_logger_statistics (" .
		"day date NOT NULL," .
		"category VARCHAR(100) NOT NULL," .
		"count float DEFAULT 0 NOT NULL)");

// Set the PK (primary keys) to avoid duplicate entries for the same object
$wpdb->get_var("ALTER TABLE ".$wpdb->prefix."ip_logger_statistics " .
		"ADD PRIMARY KEY (day,category)");
?>
