<?php

// Create an unique GUID for this plugin installation
// This GUID will be used for the communication with the IPLA
$token = md5(uniqid(mt_rand(), true));
add_option("ip_logger_ipla_GUID", $token);

// Fields to save the last communication with an IPLA client
add_option("ip_logger_ipla_LastContact_Stamp", "");
add_option("ip_logger_ipla_LastContact_IP", "");
?>
