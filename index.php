<?php
/*
Plugin Name: IP-Logger
Plugin URI: http://www.mretzlaff.com/freeware/
Description: Logs the IP of all your blog visitors & enables you to protect your WordPress Website against unwantet visitors (IP Logger Block option). The new "IP Logger Analyzer" (IPLA) enables you to download all log informations to your PC. There you can save your logs unlimited and run several other analysis. 
Version: 3.0
Author: M. Retzlaff
Author URI: http://www.mretzlaff.com/
Update Server: http://www.mretzlaff.com/mySoftware/wp/ip-logger/
Min WP Version: 2.0
Max WP Version: 2.9.2
*/

/*
This plugin is (c) Copyright 2009,2010 by Malte Retzlaff (www.mretzlaff.com).
Changes, copies and reproductions are not allowed without written permission by the author.
All rights reserved. Alle Rechte vorbehalten.
Some components are copyright by their authors and not under effect of this plugin.
Your donation (http://www.mretzlaff.com/donate/) will help to upgrade and update this plugin.
All your ideas and wishes regarding this plugin can be written to: "ip-logger@mretzlaff.com"
*/

$domain = "ip-logger";

load_plugin_textdomain($domain, WP_PLUGIN_URL."/ip-logger/languages/", "ip-logger/languages/");

add_action("wp_head", "ipl_LogVisitor");
add_action("wp_dashboard_setup", "ip_dashboard_init");
add_action("wp_head", "ipl_metatag");
add_action("admin_head", "addHeaderCode");
add_action("admin_menu", "ip_dashboard_admin");
add_filter("plugin_row_meta", "ipl_ShowBackendPluginLinks", 10, 2);

register_activation_hook(__FILE__, "ip_install");
register_deactivation_hook(__FILE__, "ip_uninstall");

// Always check the saved PIN
ipl_CheckPIN();

$avail_flds = array("ip_v4","user_agent","Code3","fullhost");
$Filter4IgnoreSystem = array("WP Backend User;WP Backend User","ip_v4;IP x.x.x.x (V4)","Code3;Code3","fullhost;Full hostname");

/*** Functions  ***/

function ip_install() {
	global $wpdb;

	$table_name = $wpdb->prefix . "ip_logger";

	if ($wpdb->get_var("show tables like '$table_name'") != $table_name) {
      
		add_option("ip_logger_version", get_option("ip_logger_version"));
		add_option("ip_logger_installed", date("Y-m-d H:i:s"));
		add_option("ip_logger_hits_archived", 0);
		add_option("ip_logger_blck_archived", 0);
		add_option("ip_logger_db_enabled", 1);
		add_option("ip_logger_db_hits", 1);
		add_option("ip_logger_db_cty", 1);
		add_option("ip_logger_db_map", 1);
		add_option("ip_logger_cb_enabled", 1);
		add_option("ip_logger_cb_mail_notice", 1);
		add_option("ip_logger_infomail", get_option("admin_email"));
		add_option("ip_logger_cb_auto_delete", 0);
		add_option("ip_logger_delete_days", 30); 
		add_option("ip_logger_cb_mail_blockedinfo", 0);
		add_option("ip_logger_blocked_infomail", get_option("admin_email"));
		add_option("ip_logger_cb_block_enabled", 0);
		add_option("ip_logger_cb_block_without_cty", 0);

	 	$sql = "CREATE TABLE " . $table_name . " (
		  id bigint(11) NOT NULL AUTO_INCREMENT,
		  stamp timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
		  ip_v4 varchar(15) NOT NULL,
		  host varchar(200) NOT NULL,
		  url varchar(200) NOT NULL,
		  user_agent varchar(200),
		  accept_language varchar(50),
		  accept_encoding varchar(20),
		  accept_charset varchar(50),
		  http_accept varchar(100),
		  http_referer varchar(100),

		  CountryCode varchar(5),
		  Code3 varchar(3),
		  Country varchar(50),
		  Region varchar(50),
		  City varchar(100),
		  Latitude decimal(10,4),
		  Longitude decimal(10,4),
		  ZipCode varchar(20),
		  dmacode varchar(20),
		  areacode varchar(20),
		  provider varchar(100),
		  blocked varchar(1) NOT NULL default 'N',
		  
		  PRIMARY KEY (id)
		) ENGINE = MYISAM CHARACTER SET utf8 COLLATE utf8_general_ci;";
	      $wpdb->get_results($sql);

	      $sql = "CREATE TABLE " . $table_name . "_block (
		  id bigint(11) NOT NULL AUTO_INCREMENT,
		  stamp timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
		  ip_v4 varchar(15),
		  host varchar(200),
		  url varchar(200),
		  user_agent varchar(200),
		  accept_language varchar(50),
		  accept_encoding varchar(20),
		  accept_charset varchar(50),
		  http_accept varchar(100),
		  http_referer varchar(100),

		  CountryCode varchar(5),
		  Code3 varchar(3),
		  Country varchar(50),
		  Region varchar(50),
		  City varchar(100),
		  Latitude decimal(10,4),
		  Longitude decimal(10,4),
		  ZipCode varchar(20),
		  dmacode varchar(20),
		  areacode varchar(20),
		  provider varchar(100),
		  fullhost varchar(100),

		  PRIMARY KEY (id)
		) ENGINE = MYISAM CHARACTER SET utf8 COLLATE utf8_general_ci;";
	      $wpdb->get_results($sql);

		if ($wpdb->get_var("select count(id) from ".$table_name."_block where agent='mediamirror'") == 0) {

			$agents = array('<unknown user agent>','0.1 noone@example.org','abot','aipbot','Anonymous','AtlocalBot','Avant Browser','BigCliqueBOT','combine',
						'Cuasarbot','EmailSiphon','EmeraldShield.com','HLoader','Holmes','HTMLParser','ichiro','Java/','Ken','larbin','LMQueueBot',
						'MediaMirror','Missigua','mogren','BorderManager','Cerberian','DigExt','shrubbery','pradipjadav@gmail','MVAClient','noxtrumbot',
						'Offline Explorer','OmniExplorer','POE','RPT-HTTPClient','sbSrer33n','SeznamBot','ssquidagent','telnet','test/0.1','updated/0.1beta',
						'crawler','W3CRobot','Web Downloader','Wget','Xenu Link','Zeus','Spider','Webster');

			foreach ($agents as $agent) {
				$sql = sprintf("insert into %s_block (user_agent) value ('%s')", $table_name, strtolower($agent));
				$wpdb->get_results($sql);
			}
		}
	}
}

function ip_uninstall() {
	global $wpdb;

   	$wpdb->get_results(sprintf("DROP TABLE %s", $wpdb->prefix . "ip_logger"));
	$wpdb->get_results(sprintf("DROP TABLE %s", $wpdb->prefix . "ip_logger_block"));
	$wpdb->get_results(sprintf("DROP TABLE %s", $wpdb->prefix . "ip_logger_ignore"));
	$wpdb->get_results(sprintf("DROP TABLE %s", $wpdb->prefix . "ip_logger_localization"));

	delete_option("ip_logger_version");
	delete_option("ip_logger_installed");
	delete_option("ip_logger_hits_archived");
	delete_option("ip_logger_blck_archived");
	delete_option("ip_logger_db_enabled");
	delete_option("ip_logger_db_hits");
	delete_option("ip_logger_db_cty");
	delete_option("ip_logger_db_map");
	delete_option("ip_logger_cb_enabled");
	delete_option("ip_logger_cb_mail_notice");
	delete_option("ip_logger_infomail");
	delete_option("ip_logger_cb_auto_delete");
	delete_option("ip_logger_delete_days"); 
	delete_option("ip_logger_cb_mail_blockedinfo");
	delete_option("ip_logger_blocked_infomail");
	delete_option("ip_logger_cb_block_enabled");
	delete_option("ip_logger_cb_block_without_cty");
}

function ip_save_settings() {
	global $wpdb;
	
	$data = array();
	$data[] = array("ip_logger_chart_countries" => get_option("ip_logger_chart_countries"));

	$fh = fopen($_SERVER["DOCUMENT_ROOT"] . "/wp-content/plugins/ip-logger/settings.txt", "w");
	fwrite($fh, serialize($data));
	fclose($fh);
}

function ip_dashboard_admin() {
	add_options_page('IP Logger', 'IP Logger', 10, __FILE__, 'ip_dashboard_admin_options');
}

function ipl_GetBlockRules() {
	global $wpdb;
	global $domain;
	global $avail_flds;

	$table_name = $wpdb->prefix . "ip_logger_block";
	$ignore_fld = array("id","stamp");	

	$sql = "select * from $table_name order by id";
	$data = $wpdb->get_results($sql);

	if (!empty($data))
	foreach ($data as $entry) {

		$keys = "";
		$vals = "";
		$flds = "";
		foreach ($entry as $key => $val) {
			if (in_array($key, $avail_flds))
				$flds .= sprintf("<option name='%s'>%s</option>", $key, $key);

			if (!in_array($key, $ignore_fld) && !empty($val)) {
				$keys .= sprintf("%s<br/>", $key);
				$vals .= sprintf("%s<br/>", htmlspecialchars($val));
			}
		}

		echo sprintf("<tr valign='top'><td style='padding-right:30px;'>%s</td><td style='padding-right:30px;'>%s</td><td style='padding-right:30px;'>%s</td><td>%s</td></tr>", 
				$entry->id, $keys, $vals,
				"<a href='/wp-admin/options-general.php?page=ip-logger/index.php&act=delete&table_name=".$table_name."&id=".$entry->id."'>".__('Delete Filter', $domain)."</a>");

	} else
	{
		echo sprintf("<tr valign='top'><td style='padding-right:30px;' colspan='4'>%s<br /><br /></td></tr>", 
				__("No data found.", $domain));
	}

	return $flds;
}

function ipl_metatag() {
	// Add the meta tag "IP Logger" to the html header
	echo "<meta name=\"IP Logger\" content=\"http://www.mretzlaff.com/wordpress-plugins/wp-plugin-ip-logger/\" />\n";
}

function ipl_GetIgnoreRules() {
	global $wpdb;
	global $domain;
	
	$tablename = $wpdb->prefix . "ip_logger_ignore";
	$sql = "SELECT * FROM $tablename ORDER BY id";
	$data = $wpdb->get_results($sql);

	if (!empty($data))
	foreach ($data as $entry) {
	
		echo sprintf("<tr valign='top'><td style='padding-right:30px;'>%s</td><td style='padding-right:30px;'>%s</td><td style='padding-right:30px;'>%s</td><td>%s</td></tr>", 
				$entry->id, 
				$entry->target, 
				$entry->filter,
				"<a href='/wp-admin/options-general.php?page=ip-logger/index.php&act=delete&table_name=".$tablename."&id=".$entry->id."'>".__('Delete Filter', $domain)."</a>");
		
	} else
	{
		echo sprintf("<tr valign='top'><td style='padding-right:30px;' colspan='4'>%s<br /><br /></td></tr>", 
				__("No data found.", $domain));
	}
}

function ipl_GetLastVisitors4IgnoreFilter() {
	global $Filter4IgnoreSystem;
	global $domain;
	
	// General option: Add all existing backend users to the "ignore" filter
	echo sprintf("<option value='%s'>%s</option>", "AllWpBackendUsers", __("Add WP Backend users (if not already in filter)", $domain));
	
	foreach ($Filter4IgnoreSystem as $filter) {
		echo sprintf("<option value='%s'>%s</option>", 
				substr($filter,0,strpos($filter,";")), 
				substr($filter,strpos($filter,";")+1,200));
	}
}

function ipl_GetFilters4IgnoreFilter() {
	global $Filter4IgnoreSystem;
	global $domain;
	
	foreach ($Filter4IgnoreSystem as $filter) {
		echo sprintf("<option value='%s'>%s</option>", 
				substr($filter,0,strpos($filter,";")), 
				__(substr($filter,strpos($filter,";")+1,200), $domain));
	}
}

function ipl_GetLastVisitors() {
	global $wpdb;
	global $domain;
	global $avail_flds;
	
	$table_name = $wpdb->prefix . "ip_logger_hits";

	foreach ($avail_flds as $field) {

		$sql = "select distinct $field from $table_name limit 50";
		$data = $wpdb->get_results($sql);
		
		foreach ($data as $entry)
		foreach ($entry as $key => $val)
			echo "<option value='$field: $val'>$field: $val</option>";
	}
}

function ipl_ShowBackendPluginLinks($links, $file) {
	global $domain;

	$plugin = plugin_basename(__FILE__);
 	if ($file == $plugin) {
		return array_merge(
			$links,
			array(sprintf('<a href="options-general.php?page=%s">%s</a>', $plugin, __('Settings'))),
			array(sprintf('<a href="http://www.mretzlaff.com/forum/" target="mre_website">%s</a>', __("Help (Forum)", $domain))),
			array(sprintf('<a href="http://www.mretzlaff.com/freeware/donate/" target="mre_website">%s</a>', __("Donate", $domain))),
			array(sprintf('<a href="mailto:ip-logger@mretzlaff.com">%s</a>', __("Contact us", $domain)))
		);
	}
 
	return $links;
}

function ip_dashboard_admin_options() {
	global $wpdb;
	global $domain;
	global $current_user;

	// Delete a rule/filter
	if ($_REQUEST["act"] == "delete") {
		$wpdb->get_var("delete from ".$_REQUEST["table_name"]." where id=" . $_REQUEST["id"]);
	}

	// Add a new block rule/filter
	if ($_REQUEST["act"] == "add") {
		if (!empty($_REQUEST["filter"]))
			$wpdb->get_var("insert into ".$_REQUEST["table_name"]." (".$_REQUEST["target"].") values ('".$_REQUEST["filter"]."')");
		else
		if (!empty($_REQUEST["filter_selection"])) {
			$target = substr($_REQUEST["filter_selection"], 0, strpos($_REQUEST["filter_selection"],":"));
			$filter = substr($_REQUEST["filter_selection"], strpos($_REQUEST["filter_selection"],":")+2, 200);
			$wpdb->get_var("insert into ".$_REQUEST["table_name"]." (".$target.") values ('".$filter."')");
		}
	}
	
	// Add a new ignore rule/filter
	if ($_REQUEST["act"] == "add_filter") {
		if (!empty($_REQUEST["filter"]))
			$wpdb->get_var("insert into ".$_REQUEST["table_name"]." (target,filter) values('".$_REQUEST["target"]."','".$_REQUEST["filter"]."')");
		else
		if ($_REQUEST["filter_selection"] == "AllWpBackendUsers") {
			$users = $wpdb->get_results("select distinct user_login from ".$wpdb->prefix."users where user_status=0");
			foreach ($users as $user) {
				if ($wpdb->get_var(sprintf("select id from ".$wpdb->prefix."ip_logger_ignore where target='WP Backend User' and filter='%s'", $user->user_login)) == "")
					$wpdb->get_var(sprintf("insert into ".$wpdb->prefix."ip_logger_ignore (target,filter) values ('WP Backend User','%s')", $user->user_login));			
			}
		} else
		if (!empty($_REQUEST["filter_selection"])) {
			$target = substr($_REQUEST["filter_selection"], 0, strpos($_REQUEST["filter_selection"],":"));
			$filter = substr($_REQUEST["filter_selection"], strpos($_REQUEST["filter_selection"],":")+2, 200);
			$wpdb->get_var("insert into ".$_REQUEST["table_name"]." (".$target.") values ('".$filter."')");
		}
	}
	
	// Clear the message field (= hide the message window)
	if ($_REQUEST["act"] == "CloseBackendMessageInfo") {
		update_option("ip_logger_backend_message", "");
	}

	// Get some statistics
	$table_name = $wpdb->prefix . "ip_logger_hits";
	
	$hits_saved = $wpdb->get_var("select count(id) from $table_name");
	$hits_total = $hits_saved + get_option("ip_logger_totalcounter_hits_saved");
	$hits_today = $wpdb->get_var("select count(id) from $table_name where date(stamp) = date(now())");

	$blck_saved = $wpdb->get_var("select count(id) from $table_name where blocked = 'Y'");
	$blck_total = $blck_saved + get_option("ip_logger_totalcounter_hits_blocked");
	$blck_today = $wpdb->get_var("select count(id) from $table_name where blocked = 'Y' and date(stamp) = date(now())");

	$ignored_saved = $wpdb->get_var("select count(id) from $table_name where ignored = 'Y'");
	$ignored_total = $ignored_saved + get_option("ip_logger_totalcounter_hits_ignored");
	$ignored_today = $wpdb->get_var("select count(id) from $table_name where ignored = 'Y' and date(stamp) = date(now())");

	get_currentuserinfo();
	?>
	<link type='text/css' rel='stylesheet' href='/wp-content/plugins/ip-logger/css/layout.css' />
	
	<div class="wrap">
		<h2>IP Logger</h2>
	
		<?php if (strlen(get_option("ip_logger_backend_message")) > 0) { ?>
		<div class="updated" style="padding:7px;">
			<?php echo get_option("ip_logger_backend_message"); ?>
			<a href="/wp-admin/options-general.php?page=ip-logger/index.php&act=CloseBackendMessageInfo">OK, close this message</a>
		</div>
		<?php } ?>
	
		<div id="poststuff" class="metabox-holder has-right-sidebar">
			<div class="inner-sidebar">
				<div id="side-sortables" class="meta-box-sortabless ui-sortable" style="position: relative;">
					<div id="yhc_admin_sb_1" class="postbox">
						<h3 class="hndle"><?php _e("About this plugin", $domain); ?></h3>
						<div class="inside">
							<a class="yhc_button yhc_pluginHome" href="http://www.mretzlaff.com/wordpress-plugins/wp-plugin-ip-logger/" target="mre_website"><?php _e("Plugin Homepage  (Download)", $domain); ?></a>
							<a class="yhc_button yhc_pluginHome" href="http://www.mretzlaff.com/forum/" target="mre_website"><?php _e("Propose a new feature", $domain); ?></a>
							<a class="yhc_button yhc_pluginHome" href="http://www.mretzlaff.com/forum/" target="mre_website"><?php _e("Help (Forum)", $domain); ?></a>
							<a class="yhc_button yhc_pluginHome" href="http://www.mretzlaff.com/forum/" target="mre_website"><?php _e("Report bug", $domain); ?></a>
							<a class="yhc_button yhc_pluginHome" href="http://www.mretzlaff.com/freeware/donate/" target="mre_website"><?php _e("Donate", $domain); ?></a>
							<a class="yhc_button yhc_pluginHome" href="mailto:info@mretzlaff.com"><?php _e("Contact us", $domain); ?></a>
						</div>
					</div>
	
					<div id="yhc_admin_sb_2" class="postbox">
						<h3 class="hndle"><?php _e("News", $domain); ?></h3>
						<div class="inside">
						<iframe src="http://www.mretzlaff.com/myTools/ip-logger/current-iframe.php?version=<?php echo get_option("ip_logger_version"); ?>&lang=<?php echo WPLANG; ?>" name="current_info"></iframe>
						</div>
					</div>
	
					<div id="yhc_admin_sb_2" class="postbox">
						<h3 class="hndle"><?php _e("Your ideas & comments", $domain); ?></h3>
						<div class="inside">
						<form name="ip-logger-command-form" method="post" action="http://www.mretzlaff.com/myTools/ip-logger/getComment.php" target="comment_win">
						<table border="1">
						<tr><td><?php _e("Your name", $domain); ?></td>
							<td><input type="text" name="name" value="<?php 
							echo (empty($current_user->first_name) ? $current_user->user_login : $current_user->first_name . " " .$current_user->last_name); ?>" style="width:160px"></td>
							</tr>
						<tr><td><?php _e("Your E-Mail", $domain); ?>&nbsp;&nbsp;</td>
							<td><input type="text" name="email" value="<?php echo $current_user->user_email; ?>" style="width:160px"></td>
							</tr>
						<tr><td valign="top" style="padding-top:7px"><?php _e("Comment", $domain); ?></td>
							<td><textarea name="comment" rows="5" style="width:160px"></textarea></td>
							</tr>
						<tr><td>&nbsp;</td>
							<td><input type="submit" value="<?php _e("Send", $domain); ?>"></td>
							</tr>
						</table>
						</form>
						</div>
					</div>
					
					<div id="yhc_admin_sb_2" class="postbox">
						<h3 class="hndle"><?php _e("Export", $domain); ?></h3>
						<div class="inside">
						<form name="ip-logger-export" method="post" action="/wp-content/plugins/ip-logger/export.php" target="export_win">
						<table border="1">
						<tr><td><?php _e("Format", $domain); ?></td>
							<td><select name="format" style="width:150px;">
								<option value="csv">CSV</option>
								<option value="xml">XML</option>
							    </select></td>
							</tr>
						<tr><td><?php _e("Timespan", $domain); ?></td>
							<td><select name="zeitraum" style="width:150px;">
								<option value="-1"><?php _e("All data", $domain); ?></option>
								<option value="60"><?php _e("Last 60 days", $domain); ?></option>
								<option value="30"><?php _e("Last 30 days", $domain); ?></option>
								<option value="14"><?php _e("Last 14 days", $domain); ?></option>
								<option value="7"><?php _e("Last 7 days", $domain); ?></option>
								<option value="1"><?php _e("Only today", $domain); ?></option>
							    </select></td>
							</tr>
						<tr><td><?php _e("Content", $domain); ?></td>
							<td><select name="content" style="width:150px;">
								<option value="short"><?php _e("Short", $domain); ?></option>
								<option value="long"><?php _e("Long", $domain); ?></option>
							    </select></td>
							</tr>
						<tr><td>&nbsp;</td>
							<td><input type="submit" value="<?php _e("Export", $domain); ?>"></td>
							</tr>
						</table>
						</form>
						</div>
					</div>
	
					<div id="yhc_admin_sb_2" class="postbox">
						<h3 class="hndle"><?php echo sprintf(__("Donations for", $domain)." IP Logger %s", get_option("ip_logger_version")); ?></h3>
						<div class="inside">
						<ul>
							<li><?php _e("Anonymous", $domain); ?><br /><i><?php echo __("Small Amount", $domain); ?></i></li>
							<hr size="1" />
							<li>D. Brandis<br /><i><?php _e("Small Amount", $domain); ?></i><br /><a href='http://www.yourhelpcenter.de' target='sponsor_website'>www.yourhelpcenter.de</a></li>
							<hr size="1" />
							<li><?php _e("Anonymous", $domain); ?><br /><i><?php echo __("Small Amount", $domain); ?></i></li>
							<hr size="1" />
							<li><b><?php _e("Many thanks !", $domain); ?></b></li>
						</ul>
						</div>
					</div>
	
					<div id="yhc_admin_sb_2" class="postbox">
						<h3 class="hndle"><?php _e("Translators", $domain); ?></h3>
						<div class="inside">
						<ul>
							<a class="yhc_button yhc_german" href="http://www.mretzlaff.com/" target="translator_website">M. Retzlaff</a>
							<a class="yhc_button yhc_sweden" href="http://emil.isberg.eu/" target="translator_website">E. Isberg</a>
						</ul>
						</div>
					</div>
		
				</div>
			</div>
	
			<div class="has-sidebar sm-padded">
				<div id="post-body-content" class="has-sidebar-content">
					<div class="meta-box-sortabless">
						<div id="yhc_admin_1" class="postbox">
							<h3 class="hndle"><span><?php _e("Overview", $domain); ?></span></h3>
							<div class="inside" style="padding:5px;">
							<ul>
								<li><?php echo sprintf(__("You're using IP Logger <b>Version %s</b>.", $domain), get_option("ip_logger_version")); ?> &copy; Copyright by <a href="http://www.mretzlaff.com/" target="mre">Malte Retzlaff</a></li>
								<li><?php echo sprintf(__("Plugin installed: <b>%s</b>", $domain), date("d.m.Y H:i", strtotime(get_option("ip_logger_installed")))); ?></li>
								<li><?php echo sprintf(__("Since the installation <b>%s Hits</b> has been logged, <font color='#ff0000'><b>%s Hits</b></font> blocked and <b>%s Hits</b> ignored.", $domain), number_format($hits_total), number_format($blck_total), number_format($ignored_total)); ?></li>
								<li><?php echo sprintf(__("Currently <b>%s hits</b> are saved in the database, thereof <b>%s hits</b> from today.", $domain), number_format($hits_saved), number_format($hits_today)); ?></li>
								<li><?php echo sprintf(__("Currently <font color='#ff0000'><b>%s hits</b></font> has been blocked, thereof <b>%s hits</b> today.", $domain), number_format($blck_saved), number_format($blck_today)); ?></li>
								<li><?php echo sprintf(__("Currently <b>%s hits</b> has been ignored, thereof <b>%s hits</b> today.", $domain), number_format($ignored_saved), number_format($ignored_today)); ?></li>
								<?php if (get_option("ip_logger_ipla_LastContact_Stamp") != "") { ?>
								<li><?php echo sprintf(__("Last <a href='%s' target='ipla'>IPLA</a> communication with IP <b>%s</b> at <b>%s</b>.", $domain), "http://www.mretzlaff.com/ip-logger-analyzer/", get_option("ip_logger_ipla_LastContact_IP"), date("d.m.Y H:i:s", strtotime(get_option("ip_logger_ipla_LastContact_Stamp")))); ?></li>
								<?php } ?>
							</ul>
						</div>
					</div>
	
					<form method="post" action="options.php">
					<input type="hidden" name="action" value="update" />
					<input type="hidden" name="page_options" value="ip_logger_cb_auto_delete,ip_logger_infomail,ip_logger_delete_days,ip_logger_cb_enabled,ip_logger_cb_mail_notice,ip_logger_blocked_infomail,ip_logger_db_hits,ip_logger_cb_mail_blockedinfo,ip_logger_db_cty,ip_logger_db_map,ip_logger_db_enabled,ip_logger_cb_block_enabled,ip_logger_cb_block_without_cty,ip_logger_ignore_filter_enabled,ip_logger_active_localization_module,ip_logger_dashboard_display_level" />
					<?php wp_nonce_field('update-options'); ?>
	    				<div class="meta-box-sortabless">
						<div id="yhc_admin_2" class="postbox">
							<h3 class="hndle"><span><?php _e("Settings", $domain); ?></span></h3>
							<div class="inside">
							<table class="iploggertable">
							<tr><td width="220px"><?php _e("IP Logger enabled ?", $domain); ?></td>
								<td><input type="checkbox" name="ip_logger_cb_enabled" <?php if (get_option("ip_logger_cb_enabled")==1) echo 'checked="checked""' ?> value="1" /> <?php _e("Yes, log visitors", $domain); ?>.
									<?php _e("If this option is disabled, the whole plugin is disabled.", $domain); ?></td>
								</tr>
							<?php if (get_option("ip_logger_cb_enabled") == 1) { ?>
							<tr><td><?php _e("Block unwantet visitors ?", $domain); ?></td>
								<td><input type="checkbox" name="ip_logger_cb_block_enabled" <?php if (get_option("ip_logger_cb_block_enabled")==1) echo 'checked="checked""' ?> value="1" /> <?php _e("Yes, check visitors against my filters", $domain); ?></td>
								</tr>
							<tr><td><?php _e("Block unlocatable visitors ?", $domain); ?></td>
								<td><input type="checkbox" name="ip_logger_cb_block_without_cty" <?php if (get_option("ip_logger_cb_block_without_cty")==1) echo 'checked="checked""' ?> value="1" /> <?php _e("Yes, block unlocateble visitors", $domain); ?></td>
								</tr>
							<tr><td><?php _e("Ignore filter enabled ?", $domain); ?></td>
								<td><input type="checkbox" name="ip_logger_ignore_filter_enabled" <?php if (get_option("ip_logger_ignore_filter_enabled")==1) echo 'checked="checked""' ?> value="1" /> <?php _e("Yes, ignore filtering enabled", $domain); ?></td>
								</tr>
								
							<tr><td colspan="2"><hr size="1" /></td>
								</tr>
								
							<tr><td><?php _e("IP Logger on the Dashboard ?", $domain); ?></td>
								<td><input type="checkbox" name="ip_logger_db_enabled" <?php if (get_option("ip_logger_db_enabled")==1) echo 'checked="checked""' ?> value="1" /> <?php _e("Yes, show IP Logger on Dashboard", $domain); ?></td>
								</tr>
							<tr><td><?php _e("Infos on the Dashboard", $domain); ?></td>
								<td><input type="checkbox" name="ip_logger_db_hits" <?php if (get_option("ip_logger_db_hits")==1) echo 'checked="checked""' ?> value="1" /> <?php _e("Show chart 'Hits'", $domain); ?></td>
								</tr>
							<tr><td style="margin-bottom:0;">&nbsp;</td>
								<td style="margin-bottom:0;"><input type="checkbox" name="ip_logger_db_cty" <?php if (get_option("ip_logger_db_cty")==1) echo 'checked="checked""' ?> value="1" /> <?php _e("Show chart 'Countries'", $domain); ?></td>
								</tr>
							<tr><td>&nbsp;</td>
								<td><input type="checkbox" name="ip_logger_db_map" <?php if (get_option("ip_logger_db_map")==1) echo 'checked="checked""' ?> value="1" /> <?php _e("Show 'Map'", $domain); ?></td>
								</tr>
							<tr><td><?php _e("Display panel for role", $domain); ?></td>
								<td><select name="ip_logger_dashboard_display_level" id="ip_logger_dashboard_display_level">
									<?php
									// Get the highest/primary role for this user
									// Waiting for a WP function like "wp_get_user_role()"
									$user_roles = $profileuser->roles;
									$user_role = array_shift($user_roles);
									
									// Print the full list of roles with the primary one selected.
									wp_dropdown_roles(get_option("ip_logger_dashboard_display_level"));
									?>
									</select>
									<?php _e("and above", $domain); ?></td>
								</tr>
							
							<tr><td colspan="2"><hr size="1" /></td>
								</tr>
								
							<tr><td><?php _e("E-Mail Notification ?", $domain); ?></td>
								<td><input type="checkbox" name="ip_logger_cb_mail_notice" <?php if (get_option("ip_logger_cb_mail_notice")==1) echo 'checked="checked""' ?> value="1" /> <?php _e("Yes, notify me", $domain); ?>:
								    <input type="text" class="regular-text" value="<?php echo get_option("ip_logger_infomail"); ?>" id="ip_logger_infomail" name="ip_logger_infomail" style="width:250px" /></td>
								</tr>
							<tr><td><?php _e("Automatically delete records ?", $domain); ?></td>
								<td><input type="checkbox" name="ip_logger_cb_auto_delete" <?php if (get_option("ip_logger_cb_auto_delete")==1) echo 'checked="checked""' ?> value="1" /> 
									<?php _e("Yes"); ?>, <?php _e("delete after", $domain); ?> <input type="text" class="regular-text" value="<?php echo get_option("ip_logger_delete_days"); ?>" id="ip_logger_delete_days" name="ip_logger_delete_days" style="width:50px;" /> 
								    <?php _e("days", $domain); ?></td>
								</tr>
							<tr><td><?php _e("E-Mail Notification on Blocking ?", $domain); ?></td>
								<td><input type="checkbox" name="ip_logger_cb_mail_blockedinfo" <?php if (get_option("ip_logger_cb_mail_blockedinfo")==1) echo 'checked="checked""' ?> value="1" /> <?php _e("Yes, notify me", $domain); ?>:
								    <input type="text" class="regular-text" value="<?php echo get_option("ip_logger_blocked_infomail"); ?>" id="ip_logger_blocked_infomail" name="ip_logger_blocked_infomail" style="width:250px" /></td>
								</tr>
							
							<tr><td colspan="2"><hr size="1" /></td>
								</tr>
								
							<tr><td valign="top">IP details request method</td>
								<td><table class="iploggertable">
									<?php
									$options = $wpdb->get_results("select * from " . $wpdb->prefix . "ip_logger_localization where active = 'Y'");
									$i = 1;
									foreach ($options as $tmp) {
										$linkTemplate = (empty($tmp->author_website) ? "%s" : "<a href='http://".$tmp->author_website."' target='author_site'>%s</a>");
										
										echo "<tr><td style='line-height: 10px;padding:0px;margin-bottom:0px;'><input type=\"radio\" name=\"ip_logger_active_localization_module\" ". (get_option("ip_logger_active_localization_module") == $tmp->code ? "checked" : "") ." value=\"" . $tmp->code . "\" /></td>";
										echo "<td style='line-height: 10px;padding:0px;margin-bottom:0px;'><b>" . $tmp->name . "</b></td>";
										echo "</tr>\n<tr><td></td><td style='padding:0px'>";
										echo (!empty($tmp->information) ? $tmp->information . "<br />" : "" ) .
											 "Author: " . sprintf($linkTemplate, $tmp->author_name);
										if (!empty($tmp->author_email))
											echo sprintf(" (<a href=\"mailto:%s\">E-Mail</a>)", $tmp->author_email);
										echo ($i < count($options) ? "<br /><br />" : "") . "</td></tr>\n";
										
										$i++;		
									}
									?></table></td>
								</tr>	
							<?php } else { 
								
								$fields = array("ip_logger_cb_auto_delete",
												"ip_logger_infomail",
												"ip_logger_delete_days",
												"ip_logger_cb_mail_notice",
												"ip_logger_blocked_infomail",
												"ip_logger_db_hits",
												"ip_logger_cb_mail_blockedinfo",
												"ip_logger_db_cty",
												"ip_logger_db_map",
												"ip_logger_db_enabled",
												"ip_logger_cb_block_enabled",
												"ip_logger_cb_block_without_cty",
												"ip_logger_ignore_filter_enabled",
												"ip_logger_dashboard_display_level",
												"ip_logger_active_localization_module");
								foreach ($fields as $field)
									echo '<input type="hidden" name="'.$field.'" value="'. get_option($field) .'" />';
								?>
								<tr><td colspan="2"><br /><?php _e("To display the other options, please enable the IP Logger:", $domain); ?><br />
									<?php _e("Check the box above and click on \"Save Changes\".", $domain); ?></td>
									</tr>
							<?php } ?>
							</table>
							<p class="submit">
								<input type="submit" name="Submit" value="<?php _e('Save Changes', $domain) ?>" />
							</p>
							</div>
						</div>
					</div>
					</form>
	
					<form method="post" action="options.php">
					<input type="hidden" name="action" value="update" />
					<input type="hidden" name="page_options" value="ip_logger_ipla_enabled,ip_logger_ipla_PIN" />
					<?php wp_nonce_field('update-options'); ?>
					<div class="meta-box-sortabless">
						<div id="yhc_admin_1" class="postbox">
							<h3 class="hndle"><span><?php _e("Settings: IP Logger Analyzer (IPLA)", $domain); ?></span></h3>
							<div class="inside">
							<table class="iploggertable">
							<tr><td colspan="2"><?php _e("You can use our free software <b>IP Logger Analyzer (IPLA)</b> to download your IP Logger logs to your local computer.", $domain); ?> 
									<font color="#ff0000"><?php _e("To protect your logs from other users or spys, you have to define a PIN.", $domain); ?>
									<b><?php _e("Keep this PIN a secret.", $domain); ?></b></font>
									<a href="http://www.mretzlaff.com/wordpress-plugins/wp-plugin-ip-logger/ip-logger-handbuch-de/" target="mre"><?php _e("Read more ...", $domain); ?></a></td>
								</tr>
							<tr><td colspan="2">&nbsp;</td>
								</tr>
							<tr><td width="220px"><?php _e("IP Logger Analyzer allowed ?", $domain); ?></td>
								<td><input type="checkbox" name="ip_logger_ipla_enabled" <?php if (get_option("ip_logger_ipla_enabled")==1) echo 'checked="checked""' ?> value="1" /> <?php _e("Yes, allow IPLA to communicate with this plugin", $domain); ?></td>
								</tr>
							<tr><td><?php _e("Your Communication PIN", $domain); ?></td>
								<td><input type="text" class="regular-text" value="<?php echo get_option("ip_logger_ipla_PIN"); ?>" id="ip_logger_ipla_PIN" name="ip_logger_ipla_PIN" style="width:150px;" />
									<?php _e("Must not be empty or shorter than 5 chars.", $domain); ?></td>
								</tr>
							<tr><td><?php _e("Current IPLA version", $domain); ?></td>
								<td><a href="http://www.mretzlaff.com/ip-logger-analyzer/" target="mre">http://www.mretzlaff.com/ip-logger-analyzer/</a></td>
								</tr>
							</table>
							<p class="submit">
								<input type="submit" name="Submit" value="<?php _e('Save Changes', $domain) ?>" />
							</p>
						</div>
					</div>
					</form>
	
					<?php if (get_option("ip_logger_cb_enabled") == 1) { ?>
					<div class="meta-box-sortabless">
						<div id="yhc_admin_1" class="postbox">
							<h3 class="hndle"><span><?php _e("Settings: Visitor filters", $domain); ?></span></h3>
							<div class="inside">
							<form name="add_filter" action="/wp-admin/options-general.php?page=ip-logger/index.php" method="post">
							<input type="hidden" name="act" value="add">
							<input type="hidden" name="table_name" value="<?php echo $wpdb->prefix.'ip_logger_block'; ?>">
							<table class="iploggertable">
							<tr><td colspan="4"><?php _e("In this group you can define filters for the <b>IP Logger Blocker</b>. This feature enables you to shield your WordPress sites from unwanted visitors.", $domain); ?> 
									<a href="http://www.mretzlaff.com/wordpress-plugins/wp-plugin-ip-logger/ip-logger-handbuch-de/" target="mre"><?php _e("Read more ...", $domain); ?></a></td>
								</tr>
							<tr><td colspan="4">&nbsp;</td>
								</tr>
							<tr><td><b><?php _e("Rule", $domain); ?></b></td>
								<td><b><?php _e("Target", $domain); ?></b></td>
								<td><b><?php _e("Filter", $domain); ?></b></td>
								<td><b><?php _e("Action", $domain); ?></b></td>
								</tr>
							<tr><td colspan="4"><hr size="1" /></td>
								</tr>
							<?php $fields = ipl_GetBlockRules(); ?>
							<tr><td colspan="4"><hr size="1" /></td>
								</tr>
							<tr><td>&nbsp;</td>
								<td><select name="target"><?php echo $fields; ?></select></td>
								<td><input type="text" name="filter" size="30"></td>
								<td rowspan="2"><input type="submit" value="<?php _e('Save Filter', $domain) ?>"></td>
								</tr>
							<tr><td><?php _e("or", $domain) ?></td>
								<td colspan="2"><select name="filter_selection" style="width: 300px;">
								<?php ipl_GetLastVisitors(); ?>
								</select></td>
								</tr>
							</table>
							</form>
							</div>
						</div>
					</div>
					
					<div class="meta-box-sortabless">
						<div id="yhc_admin_1" class="postbox">
							<h3 class="hndle"><span><?php _e("Ignore filter (ignore visitors in the charts)", $domain); ?></span></h3>
							<div class="inside">
							<form name="add_filter" action="/wp-admin/options-general.php?page=ip-logger/index.php" method="post">
							<input type="hidden" name="act" value="add_filter">
							<input type="hidden" name="table_name" value="<?php echo $wpdb->prefix.'ip_logger_ignore'; ?>">
							<table class="iploggertable">
							<tr><td colspan="4"><?php _e("Here you can define filters for visitors, which should be logged but not be considered within the charts.", $domain); ?> 
									<a href="http://www.mretzlaff.com/wordpress-plugins/wp-plugin-ip-logger/ip-logger-handbuch-de/" target="mre"><?php _e("Read more ...", $domain); ?></a></td>
								</tr>
							<tr><td colspan="4">&nbsp;</td>
								</tr>
							<tr><td><b><?php _e("Rule", $domain); ?></b></td>
								<td><b><?php _e("Target", $domain); ?></b></td>
								<td><b><?php _e("Filter", $domain); ?></b></td>
								<td><b><?php _e("Action", $domain); ?></b></td>
								</tr>
							<tr><td colspan="4"><hr size="1" /></td>
								</tr>
							<?php $fields = ipl_GetIgnoreRules(); ?>
							<tr><td colspan="4"><hr size="1" /></td>
								</tr>
							<tr><td>&nbsp;</td>
								<td><select name="target"><?php ipl_GetFilters4IgnoreFilter(); ?></select></td>
								<td><input type="text" name="filter" size="30"></td>
								<td rowspan="2"><input type="submit" value="<?php _e('Save Filter', $domain) ?>"></td>
								</tr>
							<tr><td><?php _e("or", $domain) ?></td>
								<td colspan="2"><select name="filter_selection" style="width: 300px;">
								<?php ipl_GetLastVisitors4IgnoreFilter(); ?>
								</select></td>
								</tr>
							</table>
							</form>
							</div>
						</div>
					</div>
					<?php } ?>
					
				</div>
			</div>
		</div>
	</div><?php
}

function ipl_getMailFrom() {
	return "From: IP Logger Plugin <".get_option("admin_email").">";
}

// Check, if the current user should be blocked (or not)
function ipl_Check2BlockUser($myip, $fullhost) {
	global $wpdb;
	global $domain;

	$table_name = $wpdb->prefix . "ip_logger_block";

	$found_agent = $wpdb->get_var("select count(id) from ".$table_name." where agent like '%" . strtolower($_SERVER["HTTP_USER_AGENT"]) . "%'");
	$found_host  = $wpdb->get_var("select count(id) from ".$table_name." where fullhost like '%" . strtolower($fullhost) . "%'");
	$found_code3 = $wpdb->get_var("select count(id) from ".$table_name." where Code3 = '" . strtoupper($myip->get_code3()) . "'");
	$found_ip_v4 = $wpdb->get_var("select count(id) from ".$table_name." where ip_v4 = '" . $_SERVER["REMOTE_ADDR"] . "'");

	$blocked = ((get_option("ip_logger_cb_block_without_cty") == 1 && ($myip->get_code3() == "")) ||
			($found_agent + $found_host + $found_code3 + $found_ip_v4 > 0) 
			);

	return $blocked;
}

function ipl_CreateNewPIN() {
	global $domain;
	
	// Shake the random generator
	mt_srand((double) microtime() * 1000000); 
	
	// Creates a new (secure) PIN for your IPLA API
	$set = "ABCDEFGHIKLMNPQRSTUVWXYZ123456789";
	$pin = "";
	
	// The new PIN will have 10 random chars
	for ($n=1;$n<=10;$n++)
		$pin .= $set[mt_rand(0,(strlen($set)-1))]; 
	
	// Send notification e-mail (if enabled)
	if (get_option("ip_logger_cb_mail_notice") == 1) {
		$info = __(sprintf("Dear User,\n\ndue to the security of your IP Logger plugin, your IPLA (IP Logger Analyzer) PIN has been updated.\n" .
				   "The old PIN was empty or/and not secure any more.\n\n" .
				   "You'll find your new PIN for IPLA in your IP Logger settings panel.\n" .
				   "Please log in with administrations rights an get your new PIN.\n\n" .
				   "A correct PIN must not be empty and longer than 5 chars.\n\n" .
				   "For more Informations about this, please read the plugin manual:\n" .
				   "%s\n\n" .
				   "This e-mail has been created automatically from your IP Logger plugin.",
				   "http://www.mretzlaff.com"), $domain);
		
		mail(get_option("ip_logger_infomail"), "IP Logger: " . __("Your PIN has been updated", $domain), $info, ipl_getMailFrom());
	}
	
	return $pin;
}

function ipl_CheckPIN() {
	
	// Guarantee for a high security level:
	// The saved PIN will be checked every plugin call to protect you from hacking or spy attacks
	// or just forgetting to set a (new) PIN. If this function detects an empty or too short PIN,
	// it will directly create a new one for you. You'll receive an e-mail about this. Due to the
	// security level, of course this notification e-mail will NOT contain your new PIN.
	
	$PIN = get_option("ip_logger_ipla_PIN");
	if ($PIN == "" || strlen($PIN) < 6) {
		
		$PIN = ipl_CreateNewPIN();
		update_option("ip_logger_ipla_PIN", $PIN);
		update_option("ip_logger_backend_message", "Your PIN has been updated. " . get_option("ip_logger_backend_message"));
		
	}
}

function ipl_Check2IgnoreUser($Code3 = "", $fullhost = "") {
	global $current_user;
	global $wpdb;
	
	// Read details about the current WP user (here: loginname)
	get_currentuserinfo(); 
	
	// Create the SQL statement
	$sql = sprintf(	"SELECT count(id) " .
					"FROM " . $wpdb->prefix . "ip_logger_ignore " .
					"WHERE (target='WP Backend User' AND filter='%s') " .
					"OR (target='ip_v4' AND filter='%s') " .
					"OR (target='Code3' AND filter='%s') " .
					"OR (target='fullhost' AND filter='%s')",
					$current_user->user_login,
					$_SERVER["REMOTE_ADDR"],
					$Code3,
					$fullhost);
	$found = $wpdb->get_var($sql);
	
	return	(!empty($current_user->user_login) && $found > 0);
}

// Update system
function ipl_Check4Updates() {
	global $wpdb;
	global $domain;
	
	// Get the ID of the last executed update
	$currentUpdateID = get_option("ip_logger_update_current_id");
	
	// No "currentUpdateID" option found ?
	// Set currentUpdateID to "0" (Zero)
	$currentUpdateID  = empty($currentUpdateID) ? 0 : $currentUpdateID;
	 	
	// Logging
	$log = "";
	 
	// Read all available update in the "update" folder
	// Only "*.php" files will be acceppted
	$path = dirname(__FILE__) . "/updates/";
	$dh = opendir($path);
	$NewUpdates = array();
	while ($fn = readdir($dh)) 
	if (substr($fn,0,1) != "." && substr($fn,strlen($fn)-4,4) == ".php") if (substr($fn,strrpos($fn,"-")+1,6) > $currentUpdateID)
		$NewUpdates[] = $fn;
	closedir($dh);
	
	// If updates (update files) have been found, "execute" them
	if (count($NewUpdates) > 0) {
		sort($NewUpdates);
		
		foreach ($NewUpdates as $RunUpdate) {
			
			// Import & execute that update file
			include($path.$RunUpdate);
			$log .= "Update: $RunUpdate\n";
			update_option("ip_logger_update_current_id", substr($RunUpdate,strrpos($RunUpdate,"-")+1,6));
			
			// Enable a message for the WP Backend user
			update_option("ip_logger_backend_message", "Your IP Logger plugin has been updated.");
		}
		
		$log = __("Dear User,\n\nthe following IP Logger updates have been installed on your server:", $domain) . "\n\n" . 
			   $log . "\n\n" . 
			   __("This is a notification e-mail only.", $domain) . "\n\n" .
			   __("To stop the notifications, please disable the option in your IP Logger settings.", $domain) . "\n" .
			   __("There you can also define the receiver of these notifications.", $domain) . "\n\n" .
			   __("For more informations, please visit our website:", $domain) . "\n" .
			   "http://www.mretzlaff.com/wordpress-plugins/wp-plugin-ip-logger/";
	}
	
	// Send nofification email to user (if enabled in the settings)
	if (get_option("ip_logger_cb_mail_notice") == "1" && !empty($log)) 	
		mail(get_option("ip_logger_infomail"), "IP Logger: " . __("Update notification", $domain), $log, ipl_getMailFrom());
}

function ipl_UpdateStatistics() {
	global $wpdb;
	
	// If the last statistic has not been calculated for yesterday (at least), 
	// then run start the statistics calculation now 
	if (get_option("ip_logger_last_statistics_calculation") != date("Y-m-d",strtotime("yesterday"))) {
		
		// Get all dates with saved hits from the hits table (after the last saved day)
		$sql = 	"SELECT distinct DATE_FORMAT(stamp,'%Y-%m-%d') AS stamp " .
			   	"FROM " . $wpdb->prefix . "ip_logger_hits " .
			   	"WHERE date(stamp) <= DATE_ADD(CURDATE(), INTERVAL -1 DAY) " .
			   	(get_option("ip_logger_last_statistics_calculation") != "" ? "AND date(stamp) > '".get_option("ip_logger_last_statistics_calculation")."' " : "") .
			   	"ORDER BY 1 asc";
		$data = $wpdb->get_results($sql);
		
		// Save (or update) the hits for each day found
		foreach ($data as $day) {
			
			// Save hits total
			$sql = sprintf("SELECT count(*) AS anzahl " .
					"FROM " . $wpdb->prefix . "ip_logger_hits " .
					"WHERE DATE_FORMAT(stamp,'%%Y-%%m-%%d') = '%s'",
					$day->stamp);
			$hits = $wpdb->get_var($sql);
			
			$sql = sprintf("INSERT INTO " . $wpdb->prefix . "ip_logger_statistics " .
					"(day,category,count) VALUES ('%s','%s',%s) " .
					"ON DUPLICATE KEY UPDATE count = %s", 
					$day->stamp,
					"VisitorsTotal",
					$hits,
					$hits);
			$wpdb->get_var($sql);
			
			// Save blocked hits
			$sql = sprintf("SELECT count(*) AS anzahl " .
					"FROM " . $wpdb->prefix . "ip_logger_hits " .
					"WHERE DATE_FORMAT(stamp,'%%Y-%%m-%%d') = '%s' " .
					"AND blocked = 'Y'",
					$day->stamp);
			$hits = $wpdb->get_var($sql);
			
			$sql = sprintf("INSERT INTO " . $wpdb->prefix . "ip_logger_statistics " .
					"(day,category,count) VALUES ('%s','%s',%s) " .
					"ON DUPLICATE KEY UPDATE count = %s", 
					$day->stamp,
					"VisitorsBlocked",
					$hits,
					$hits);
			$wpdb->get_var($sql);
			
			// Save ignored hits
			$sql = sprintf("SELECT count(*) AS anzahl " .
					"FROM " . $wpdb->prefix . "ip_logger_hits " .
					"WHERE DATE_FORMAT(stamp,'%%Y-%%m-%%d') = '%s' " .
					"AND ignored = 'Y'",
					$day->stamp);
			$hits = $wpdb->get_var($sql);
			
			$sql = sprintf("INSERT INTO " . $wpdb->prefix . "ip_logger_statistics " .
					"(day,category,count) VALUES ('%s','%s',%s) " .
					"ON DUPLICATE KEY UPDATE count = %s", 
					$day->stamp,
					"VisitorsIgnored",
					$hits,
					$hits);
			$wpdb->get_var($sql);
			
			// Set the "last saved statistics day" to the just saved day
			update_option("ip_logger_last_statistics_calculation", $day->stamp);
		}
	}
}

function ipl_LogVisitor() {
	global $wpdb;
	global $domain;

	// Check for old IP Logger settings (like "IP Logger enabled")
	// Reason: Within the update to V3.0, the option names have been changed
	// Due to this, the IP Logger would be disabled by default after the update
	if (get_option("yhc_ip_logger_cb_enabled") != "") {
		add_option("ip_logger_cb_enabled", get_option("yhc_ip_logger_cb_enabled"));
		delete_option("yhc_ip_logger_cb_enabled");
	}

	// Is the "IP Logger" plugin enabled ? 
	// If not, this plugin-script ends here
	if (get_option("ip_logger_cb_enabled") != 1) 
		return;

	// Update(s) available ?
	ipl_Check4Updates();
	
	// Update internal statistics if necessary
	ipl_UpdateStatistics();

	// Optionally:
	// Get some details about the current visitor (Location, Lon, Lat, Country, Code3, ...)
	// The way to get those details can be selected in the plugin settings in the WP Backend
	$moduleFile = $wpdb->get_var(sprintf("select include_filename " .
								 "from " . $wpdb->prefix . "ip_logger_localization " .
								 "where code = '%s'", get_option("ip_logger_active_localization_module")));
	include("localization/$moduleFile.php");
	
	$ip = $_SERVER["REMOTE_ADDR"];
	$myip = new ipdetails($ip);
	$myip->scan();

	$fullhost = gethostbyaddr($ip);
	$host = preg_replace("/^[^.]+./", "*.", $fullhost);

	// Block the current visitor ?
	$blocked = (get_option("ip_logger_cb_block_enabled") == 1 ? ipl_Check2BlockUser($myip, $fullhost) : false);

	// Filter visitor (ignore this visit in the IP Logger charts) ?
	$ignored = (get_option("ip_logger_ignore_filter_enabled") == 1 ? ipl_Check2IgnoreUser($myip->get_code3(),$fullhost) : false);

	// Log this visit
	$sql = sprintf("insert into %s (ip_v4,url,host,user_agent,accept_language,accept_encoding,accept_charset,http_accept,
			    CountryCode,Code3,Country,Region,City,Latitude,Longitude,ZipCode,dmacode,areacode,provider,http_referer,blocked,ignored) 
			    values ('%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s')",
			$wpdb->prefix . "ip_logger_hits",
			$_SERVER["REMOTE_ADDR"],
			$_SERVER["REQUEST_URI"],
			$_SERVER["HTTP_HOST"],
			$_SERVER["HTTP_USER_AGENT"],
			$_SERVER["HTTP_ACCEPT_LANGUAGE"],
			$_SERVER["HTTP_ACCEPT_ENCODING"],
			$_SERVER["HTTP_ACCEPT_CHARSET"],
			$_SERVER["HTTP_ACCEPT"],
			$myip->get_countrycode(),
			$myip->get_code3(),
			htmlentities($myip->get_country()),
			htmlentities($myip->get_region()),
			htmlentities($myip->get_city()),
			$myip->get_latitude(),
			$myip->get_longitude(),
			$myip->get_postalcode(),
			$myip->get_dmacode(),
			$myip->get_areacode(),
			$fullhost,
			$_SERVER["HTTP_REFERER"],
			$blocked ? "Y" : "N",
			$ignored ? "Y" : "N");

	$wpdb->get_results($sql);
	$myip->close(); 

	// Clean the database
	if (get_option("ip_logger_cb_auto_delete") == 1) {
		$sql = sprintf("delete from %s where datediff(stamp, now()) < -%s", $wpdb->prefix . "ip_logger", get_option("ip_logger_delete_days"));
		$wpdb->get_results($sql);
	}

	// Show "Access denied" screen for blocked visitors
	if ($blocked) {
		if (get_option("ip_logger_cb_mail_blockedinfo") == 1)
			mail(get_option("ip_logger_blocked_infomail"), "Info: Blocked access on ".$_SERVER["HTTP_HOST"], "IP '".$_SERVER["REMOTE_ADDR"]."' blocked.", ipl_getMailFrom());
	
		wp_die(__("Sorry, but you cannot access this website.", $domain));
	}
}

function addHeaderCode() {
	echo "<link type='text/css' rel='stylesheet' href='/wp-content/plugins/ip-logger/js/tabs.css' />
		<script src='/wp-content/plugins/ip-logger/js/OpenLayers.js' type='text/javascript'></script>
		<script src='/wp-content/plugins/ip-logger/js/OpenStreetMap.js' type='text/javascript'></script>
		<script src='/wp-content/plugins/ip-logger/js/tabs.js' type='text/javascript'></script>
		<script src='/wp-content/plugins/ip-logger/js/swfobject.js' type='text/javascript'></script>\n";
}

function ipl_getRoleMaxLevel($role) {
	
	// Get the highest level for the given role
	$roles = get_option("wp_user_roles");
	$max = 0;
	for ($n=0;$n<11;$n++) 
		if ($roles[$role]["capabilities"]["level_$n"] == 1) $max = $n;
	
	// Return the max. found level
	return $max;
	
}

function ip_dashboard() {
	global $wpdb;
	global $domain;

	// Display the IP Logger dashboard only if this feature has been enabled
	if (get_option("ip_logger_cb_enabled") == "1") {

		echo "<div class='tabber' id='ip_logger_tab'>\n";
	
		if (get_option("ip_logger_db_hits") == 1)
		echo "<div class='tabbertab'>
				<h2>".__("Hits", $domain)."</h2>
				<p><div id='hit_chart'></div></p>
			</div>\n";
	
		if (get_option("ip_logger_db_cty") == 1)
		echo "<div class='tabbertab'>
				<h2>".__("Countries", $domain)."</h2>
				<p><div id='cty_chart'></div></p>
			</div>\n";
	
		if (get_option("ip_logger_db_map") == 1) {
		echo "<div class='tabbertab'>
				<h2>".__("Map", $domain)."</h2>
				<p><div style='width:100%;height:400px;border:1px solid #000;' id='map'></div>
				<script type='text/javascript'>
				var lat=52.0;
				var lon=10.0;
				var zoom=4;
				var map;
	 
				map = new OpenLayers.Map('map', {
					controls:[
						new OpenLayers.Control.Navigation(),
						new OpenLayers.Control.PanZoomBar(),
						new OpenLayers.Control.LayerSwitcher(),
						new OpenLayers.Control.Attribution()],
						maxExtent: new OpenLayers.Bounds(-20037508.34,-20037508.34,20037508.34,20037508.34),
						maxResolution: 156543.0399,
						numZoomLevels: 8,
						units: 'm',
						projection: new OpenLayers.Projection('EPSG:900913'),
						displayProjection: new OpenLayers.Projection('EPSG:4326')});
	
				layerMapnik = new OpenLayers.Layer.OSM.Mapnik('Map');
				map.addLayer(layerMapnik);
	
				var lonLat = new OpenLayers.LonLat(lon,lat).transform(new OpenLayers.Projection('EPSG:4326'), map.getProjectionObject());
				map.setCenter(lonLat, zoom);
	 
				var pois = new OpenLayers.Layer.Text('Hits (normal & blocked)',{location:'/wp-content/plugins/ip-logger/map/map_dashboard.php', projection: map.displayProjection});
		            map.addLayer(pois);
				</script>\n";
	
		echo "</p>
			</div>\n";
		}
	
		// Get some statistic informations
		$table_name = $wpdb->prefix . "ip_logger_hits";
	
		$hits_saved = $wpdb->get_var("select count(id) from $table_name");
		$hits_total = $hits_saved + get_option("ip_logger_totalcounter_hits_saved");
		
		$blck_saved = $wpdb->get_var("select count(id) from $table_name where blocked = 'Y'");
		$blck_total = $blck_saved + get_option("ip_logger_totalcounter_hits_blocked");
		
		$ignored_saved = $wpdb->get_var("select count(id) from $table_name where ignored = 'Y'");
		$ignored_total = $ignored_saved + get_option("ip_logger_totalcounter_hits_ignored");
			
		echo "<div class='tabbertab'>
				<h2>".__("About IP Logger", $domain)."</h2>
				<p><div style='width:100%;height:400px;' id='ip_logger_copyright'>
					".sprintf(__("This WordPress Plugin is &copy; Copyright %s by", $domain), "2009,2010").
					" Malte Retzlaff.<br />" .
					__("All rights reserved.", $domain)."<br /><br />" .
					"Please visit my websites: <a href='http://www.mretzlaff.com' target='mretzlaff'>www.mretzlaff.com</a><br />" .
					__("You're using", $domain)." <b>IP Logger V".get_option("ip_logger_version")."</b><br /><br /> " .
					__("Saved hits by IP Logger", $domain).": $hits_total<br />" .
					__("Blocked hits by IP Logger", $domain).": <font color='red'>$blck_total</font><br />" .
					__("Ignored hits by IP Logger", $domain).": $ignored_total<br /><br />
					<table border='0'>
					<tr><td style='width:90px;'>".__("Homepage", $domain).":</td><td><a href='http://www.mretzlaff.com/wordpress-plugins/wp-plugin-ip-logger/' target='ip_logger_website'>http://www.mretzlaff.com</a></td></tr>
					<tr><td>".__("Forum", $domain).":</td><td><a href='http://www.mretzlaff.com/forum/' target='ip_logger_forum'>http://www.mretzlaff.com/forum/</a></td></tr>
					<tr><td>&nbsp;</td></tr>
					<tr><td>".__("Your Ideas", $domain).":</td><td><a href='mailto:ip-logger@mretzlaff.com'>ip-logger@mretzlaff.com</a></td></tr>
					<tr><td>".__("Donate", $domain).":</td><td><a href='http://www.mretzlaff.com/donate/' target='ip_logger_forum'>http://www.mretzlaff.com/wordpress-plugins/donate/</a></td></tr>
					</table>
					<br />
					Used 3rd party components (with * marked = optional)<br />
					<table border='0' cellspacing='0'>
					<tr><td>&nbsp;</td>
						</tr>
					<tr><td style='width:90px;'>Charts</td>
						<td><a href='http://teethgrinder.co.uk/open-flash-chart/' target='ofc'>Open Flash Charts</a> &copy; by John Glazebrook</td>
						</tr>
					<tr><td>IP Details*</td>
						<td><a href='http://www.chetanweb.com/' target='ipd'>Class IP Details</a> &copy; by Chetan Mendhe</td>
						</tr>
					<tr><td>GeoIP*</td>
						<td><a href='http://www.pidgets.com/' target='gip'>GeoIP by Pidgets</a> &copy; by Rasmus Lerdorf</td>
						</tr>
					</table>
				</div></p>
			</div>
	
			</div>
	
			<script type='text/javascript'>
				var width = document.getElementById('hit_chart').offsetWidth-20;
				swfobject.embedSWF('/wp-content/plugins/ip-logger/chart/open-flash-chart.swf', 'hit_chart', width, '400', '9.0.0', 'expressInstall.swf', 
					{'data-file':'/wp-content/plugins/ip-logger/chart/__hits_total.php'});
	
				swfobject.embedSWF('/wp-content/plugins/ip-logger/chart/open-flash-chart.swf', 'cty_chart', width, '400', '9.0.0', 'expressInstall.swf', 
					{'data-file':'/wp-content/plugins/ip-logger/chart/__hits_per_country.php'});
			</script>\n";
		
	} else {
		
		_e("This plugin is disabled.", $domain);
		
		$plugin = plugin_basename(__FILE__);
		echo " <a href=''>" . sprintf('<a href="options-general.php?page=%s">%s</a>', $plugin, __('Settings')) . "</a>";
		
	}

}

function ip_dashboard_init() {
	global $current_user;
	global $domain;
	global $wpdb;
	
	// Read the current user informations
	get_currentuserinfo();
	
	// Get the role of the current user
	$capName = $wpdb->prefix . "capabilities"; 
	$myRole = array_keys($current_user->data->$capName);
	$myRole = $myRole[0];
	
	// "Explanation" link
	$explain = sprintf("<a href='http://www.mretzlaff.com/wordpress-plugins/wp-plugin-ip-logger/ip-logger-handbuch-de/' target='manual' class='edit-box open-box'>%s</a>", 
				__("Chart explanation", $domain));
	
	// Configuration Link
	$plugin = plugin_basename(__FILE__);
	$configure = sprintf('<a href="options-general.php?page=%s" class="edit-box open-box">%s</a>', $plugin, __('Settings'));
	
	// Display the IP Logger dashboard if:
	// (a) the dashboard visibility has been enabled within the IP Logger settings and
	// (b) the current user role is higher (or equal) than the defined min. user role (in the IP Logger settings)
	if (get_option("ip_logger_db_enabled") == 1 && 
		ipl_getRoleMaxLevel(get_option("ip_logger_dashboard_display_level")) <= ipl_getRoleMaxLevel($myRole))
		wp_add_dashboard_widget("ip_dashboard", "IP Logger" . "<span class=\"postbox-title-action\">".$explain." ".$configure."</span>", "ip_dashboard");
}

?>