<?php

// Fill the statistics table with values from the hits table
// (a) All visitors
$wpdb->get_var("INSERT INTO ".$wpdb->prefix."ip_logger_statistics (day,category,count) " .
		"SELECT date_format(stamp,'%Y-%m-%d'), 'VisitorsTotal', count(id) " . 
		"FROM ".$wpdb->prefix."ip_logger_hits " .
		"GROUP BY date_format(stamp,'%Y-%m-%d')");
	
// (b) Blocked visitors	
$wpdb->get_var("INSERT INTO ".$wpdb->prefix."ip_logger_statistics (day,category,count) " .
		"SELECT date_format(stamp,'%Y-%m-%d'), 'VisitorsBlocked', count(id) " . 
		"FROM ".$wpdb->prefix."ip_logger_hits " .
		"WHERE blocked = 'Y' " .
		"GROUP BY date_format(stamp,'%Y-%m-%d')");

// (c) Ignored visitors
$wpdb->get_var("INSERT INTO ".$wpdb->prefix."ip_logger_statistics (day,category,count) " .
		"SELECT date_format(stamp,'%Y-%m-%d'), 'VisitorsIgnored', count(id) " . 
		"FROM ".$wpdb->prefix."ip_logger_hits " .
		"WHERE ignored = 'Y' " .
		"GROUP BY date_format(stamp,'%Y-%m-%d')");
		
// (d) Mozilla users (Browser = "Firefox/*") 
// Pls note that this selection is quite basic and will not consider all Firefox versions
$wpdb->get_var("INSERT INTO ".$wpdb->prefix."ip_logger_statistics (day,category,count) " .
		"SELECT date_format(stamp,'%Y-%m-%d'), 'BrowserFirefox', count(id) " . 
		"FROM ".$wpdb->prefix."ip_logger_hits " .
		"WHERE user_agent like '% Firefox/%' " .
		"GROUP BY date_format(stamp,'%Y-%m-%d')");
		
// (e) IE users (Browser = "Mozilla *") 
// Pls note that this selection is quite basic and will not consider all IE versions
$wpdb->get_var("INSERT INTO ".$wpdb->prefix."ip_logger_statistics (day,category,count) " .
		"SELECT date_format(stamp,'%Y-%m-%d'), 'BrowserIE', count(id) " . 
		"FROM ".$wpdb->prefix."ip_logger_hits " .
		"WHERE user_agent like '% MSIE %' " .
		"GROUP BY date_format(stamp,'%Y-%m-%d')");
		
// (f) Other browsers (not Firefox and not IE)
// Pls note that this selection is quite basic and will not consider all Browser versions
$wpdb->get_var("INSERT INTO ".$wpdb->prefix."ip_logger_statistics (day,category,count) " .
		"SELECT date_format(stamp,'%Y-%m-%d'), 'BrowserOther', count(id) " . 
		"FROM ".$wpdb->prefix."ip_logger_hits " .
		"WHERE user_agent not like '% MSIE %' " .
		"AND user_agent not like '% Firefox%' " .
		"GROUP BY date_format(stamp,'%Y-%m-%d')");
?>
