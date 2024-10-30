<html>

<head>
	<title>IP-Logger: Map details</title>
	<link rel='stylesheet' href='/wp-admin/load-styles.php?c=1&amp;dir=ltr&amp;load=dashboard,plugin-install,global,wp-admin&amp;ver=6403d4cb3e6353f406fd43f1b0373ec2' type='text/css' media='all' />
	<link rel='stylesheet' id='thickbox-css' href='/wp-includes/js/thickbox/thickbox.css?ver=20090514' type='text/css' media='all' />

	<style>
	.MapDetails_td {
		border-bottom: 1px dotted #333333;
		padding-right: 10px;
	}
	.MapDetails_td_top {
		border-bottom: 2px solid #333333;
		padding-right: 10px;
	}
	</style>
</head>

<body>
<table>
<?php

include($_SERVER["DOCUMENT_ROOT"] . "/wp-config.php");

$link = mysql_connect(DB_HOST, DB_USER, DB_PASSWORD);
mysql_select_db(DB_NAME, $link);
mysql_query(sprintf("set names '%s'", DB_CHARSET));

$table_name = $table_prefix . "ip_logger_hits";
$domain = "ip-logger";

// All hits
$sql = sprintf("select stamp,ip_v4,url,user_agent,Provider,Code3,Country,Blocked,Ignored from $table_name 
		where Latitude=%s and Longitude=%s and Blocked = '%s'
		order by stamp asc limit 50",
		$_REQUEST["lat"],
		$_REQUEST["lon"],
		$_REQUEST["blocked"]);
$res = mysql_query($sql);

$header = false;
while ($row = mysql_fetch_assoc($res)) {

	if (!$header) {

		echo "<tr>";
		foreach ($row as $key => $val)
			echo sprintf("<td class='MapDetails_td_top'><b>%s</b></td>", $key);
		echo "</tr>";
		$header = true;
	}

	echo "<tr>";

	foreach ($row as $key => $val)
		echo sprintf("<td class='MapDetails_td'><nobr>%s</nobr></td>", $val);
	
	echo "</tr>\n";
}
?>
</table>
</body>
</html>