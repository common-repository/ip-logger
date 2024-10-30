<?php

// Rename the IP Logger tables
$wpdb->get_var("ALTER TABLE ".$wpdb->prefix."ip_logger RENAME ".$wpdb->prefix."ip_logger_hits");

?>
