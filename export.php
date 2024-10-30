<?php
header("Content-Type: plain/text");
header("Content-Disposition: Attachment; filename=ip-logger-export." . $_REQUEST["format"]);
header("Pragma: no-cache");

include($_SERVER["DOCUMENT_ROOT"] . "/wp-config.php");

function xml_character_encode($string, $trans='') {
  $trans = (is_array($trans)) ? $trans : get_html_translation_table(HTML_ENTITIES, ENT_QUOTES);
  foreach ($trans as $k=>$v)
    $trans[$k]= "&#".ord($k).";";

  return strtr($string, $trans);
} 

$link = mysql_connect(DB_HOST, DB_USER, DB_PASSWORD);
mysql_select_db(DB_NAME, $link);
mysql_query(sprintf("set names '%s'", DB_CHARSET));

$table_name = $table_prefix . "ip_logger_hits";
$domain = "ip-logger";

if ($_REQUEST["content"] == "long")
	$fields = "id,stamp,ip_v4,host,url,user_agent,accept_language,accept_encoding,accept_charset,http_accept,CountryCode,Code3,Country,Region,City,Latitude,Longitude,ZipCode,dmacode,areacode,provider,http_referer,ignored,blocked";
else
	$fields = "id,stamp,ip_v4,CountryCode,Code3,Country,Latitude,Longitude,provider,ignored,blocked";

$sql = sprintf("select %s from $table_name %s order by ID asc",
		$fields,
		$_REQUEST["zeitraum"] >= 0 ? sprintf("where stamp >= '%s'", date("Y-m-d", strtotime(sprintf("-%s days", $_REQUEST["zeitraum"])))) : "");
$res = mysql_query($sql);

$header = false;
$total = 0;
while ($row = mysql_fetch_assoc($res)) {

	if ($_REQUEST["format"] == "csv") {

		if (!$header) {
			foreach ($row as $key => $val)
				echo "$key;";

			echo "\n";
			$header = true;
		}

		foreach ($row as $key => $val)
			echo "$val;";
	
		echo "\n";
	}

	if ($_REQUEST["format"] == "xml") {

		if (!$header) {
			echo "<?xml version=\"1.0\" encoding=\"ISO-8859-1\" standalone=\"yes\"?>\n<visitors>\n";
			$header = true;
		}

		echo sprintf("<record id=\"%s\">\n", $total+1);

		foreach ($row as $key => $val)
			echo sprintf("<$key>%s</$key>\n", xml_character_encode($val));

		echo "</record>\n";
		$total++;
	}
}

if ($_REQUEST["format"] == "xml") {
	echo "</visitors>\n<!-- $total records written -->\n";
}
?>