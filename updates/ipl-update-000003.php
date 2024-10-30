<?php

// Add a new message field for informations within the backend
// If this option is empty, no message will be displayed
$wpdb->get_var("update ".$wpdb->prefix."options " .
		"set option_name = substring(option_name,5) " .
		"where option_name like 'yhc_%'");

?>
