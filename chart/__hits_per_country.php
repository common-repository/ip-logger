<?php
include($_SERVER["DOCUMENT_ROOT"] . "/wp-config.php");
include("open-flash-chart.php");

$link = mysql_connect(DB_HOST, DB_USER, DB_PASSWORD);
mysql_select_db(DB_NAME, $link);
mysql_query(sprintf("set names '%s'", DB_CHARSET));

$table_name = $table_prefix . "ip_logger_hits";
$domain = "ip-logger";

// Get the top 10 of accessing countries
$sql = "SELECT Code3, count(id) AS Anzahl " .
	   "FROM $table_name " .
	   "GROUP BY Code3 " .
	   "ORDER BY Anzahl DESC " .
	   "LIMIT 10";
$res = mysql_query($sql);

$data_hits = array();
$axis_X = array();

$tags = new ofc_tags();
$tags->font("Verdana", 10)->colour("#000000")->align_x_center()->text('#y#');

$n = 0;
while ($row = mysql_fetch_assoc($res)) {

	$hits = $row["Anzahl"];
	$max = (max($hits,0) > $max ? max($hits,0) : $max);

	$data_hits[$n] = new bar_value($hits);
	$data_hits[$n]->set_tooltip(sprintf('%s<br>#val# %s', $row["Code3"], __("Hits", $domain)));
	$tags->append_tag(new ofc_tag($n, empty($hits) ? "0" : $hits));

	$axis_X[] = $row["Code3"];

	$n++;
}

mysql_close($link);

//
$x = new x_axis();
$x->set_labels_from_array($axis_X);

// Set Y axis (interval, maximum)
$y = new y_axis();
$interval = ($max > 10 ? 10 : 1);
$interval = ($max > 100 ? 100 : 10);

// Calculate the next highest interval for the y-axis
$maxInterval = 0;
do $maxInterval += $interval;
while ($maxInterval < $max);

$y->set_range(0, $maxInterval, $interval);

$bar_hits = new bar_glass();
$bar_hits->set_values($data_hits);

$chart = new open_flash_chart();
$chart->set_bg_colour("#FFFFFF");
$chart->set_x_axis($x);
$chart->set_y_axis($y);
$chart->add_element($bar_hits);

// Echo the chart data as JSON string
// To get a more readable string, use ->toPrettyString();
echo $chart->toString();
?>
