<?php

// Add field "ignored"
$wpdb->get_var("ALTER TABLE ".$wpdb->prefix."ip_logger_hits ADD ignored varchar(1) NOT NULL default 'N'");

?>
