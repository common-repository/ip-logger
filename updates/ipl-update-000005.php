<?php

// Rename the IP Logger tables
$wpdb->get_var("ALTER TABLE ".$wpdb->prefix."yhc_ip_logger RENAME ".$wpdb->prefix."ip_logger_hits");
$wpdb->get_var("ALTER TABLE ".$wpdb->prefix."yhc_ip_logger_block RENAME ".$wpdb->prefix."ip_logger_block");

?>
