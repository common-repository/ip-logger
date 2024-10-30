<?php

// Create a random PIN
$pin = "";
for ($n=1;$n<9;$n++) $pin .= rand(0,9);

// Save the PIN to protect the new IP Logger Analyzer (IPLA) communication API
add_option("ip_logger_ipla_PIN", $pin);

// Default: IPLA communication is disabled
add_option("ip_logger_ipla_enabled", "0");

?>
