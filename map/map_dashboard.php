lat	lon	title	description	icon	iconSize	iconOffset
<?php
function getLevelArrayPosition($anzahl = 1) {
	global $green;

	$n = 0;
	while ($green[$n+1]<=$anzahl && isset($green[$n+1])) $n++;
	return $n;
}

include($_SERVER["DOCUMENT_ROOT"] . "/wp-config.php");

$link = mysql_connect(DB_HOST, DB_USER, DB_PASSWORD);
mysql_select_db(DB_NAME, $link);
mysql_query(sprintf("set names '%s'", DB_CHARSET));

$table_name = $table_prefix . "ip_logger_hits";

$files = scandir($_SERVER["DOCUMENT_ROOT"] . "/wp-content/plugins/ip-logger/map/icons/");
foreach ($files as $file) {
	$parts = pathinfo($file);
	if (substr($file,0,5) == "green") {
		$green[] 	= substr($parts["filename"],6,5);
	}
}
sort($green);
foreach ($green as $id => $icon) {

	$info = getimagesize($_SERVER["DOCUMENT_ROOT"] . "/wp-content/plugins/ip-logger/map/icons/green_$icon.png");
	$green_sz[$id] = sprintf("%s,%s", $info[0], $info[1]);

}

// Filter all stamps before the 1st of Nov/2009
// This plugin didn't exist before that date - so there cannot be serious hits
$sql =	"SELECT Longitude,Latitude,City,Region,blocked,Code3,max(stamp) AS Latest,count(id) AS Anzahl " .
		"FROM $table_name " .
		"WHERE stamp > '2009-11-01' " .
		"AND ignored = 'N' " .
		"AND (longitude <> 0 AND latitude <> 0)" .
		"GROUP BY Longitude,Latitude,blocked " .
		"ORDER BY count(id) DESC";
$res = mysql_query($sql);
while ($row = mysql_fetch_assoc($res)) {

	$n = getLevelArrayPosition($row["Anzahl"]);
	echo sprintf("%s\t%s\t%s\t%s:&nbsp;%s<br>%s:&nbsp;%s<br><br>%s:&nbsp;%s<br>%s:&nbsp;%s<br>%s:&nbsp;%s<br><br>%s:&nbsp;%s<br>%s:&nbsp;%s<br>%s:&nbsp;%s<br/><a href='%s' target='ip-map-details'>More details</a>\t%s\t%s\t%s\n", 
		$row["Latitude"],
		$row["Longitude"],
		$row["City"],
		
		__("Longitude", $domain),
		$row["Longitude"],
		__("Latitude", $domain),
		$row["Latitude"],
		__("City", $domain),
		$row["City"],
		__("Region", $domain),
		$row["Region"],
		__("Country", $domain),
		$row["Code3"],
		
		__("Hits", $domain),
		$row["Anzahl"],
		__("Blocked", $domain),
		($row["blocked"] == "Y" ? "Yes" : "No"),
		__("Last&nbsp;Access", $domain),
		date("d.m.Y H:i", strtotime($row["Latest"])),
		sprintf("/wp-content/plugins/ip-logger/map-details.php?lat=%s&lon=%s&blocked=%s", $row["Latitude"], $row["Longitude"], $row["blocked"]),
		($row["blocked"] == "Y" ? 
			"/wp-content/plugins/ip-logger/map/icons/red_".$green[$n].".png" : 
			"/wp-content/plugins/ip-logger/map/icons/green_".$green[$n].".png"),
		$green_sz[$n],
		"0,0");
}

mysql_close($link);
?>
