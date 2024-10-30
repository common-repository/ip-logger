<?php

// Add the field "ignored" to the logging table
// This field contains "Y" if the current visitor should be ignored
// Default = "N" (= visitor not ignored)
$wpdb->get_var("ALTER TABLE ".$wpdb->prefix."yhc_ip_logger " .
		"ADD ignored SET('Y','N') NOT NULL DEFAULT 'N'");

// Add the field "wp_user" to the logging table
// This contains the wp username of the current visitor (if user is logged in)
$wpdb->get_var("ALTER TABLE ".$wpdb->prefix."yhc_ip_logger " .
		"ADD wp_user VARCHAR(100) NULL DEFAULT NULL AFTER provider"); 

?>
