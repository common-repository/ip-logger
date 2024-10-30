<?php

// Include the current wp-config file to get the accessdata for the database
include($_SERVER["DOCUMENT_ROOT"] . "/wp-config.php");

// Connect to the used wp database
$link = mysql_connect(DB_HOST, DB_USER, DB_PASSWORD);
mysql_select_db(DB_NAME, $link);
mysql_query(sprintf("set names '%s'", DB_CHARSET));

// Define some variables
$table_name = $table_prefix . "ip_logger_hits";
$days = 30;
$mem = array();

for ($n = $days; $n > 0; $n--) {

	$start = strtotime("-$n days");

	$mem[date("Y-m-d", $start)][ID]		= $days-$n;
	$mem[date("Y-m-d", $start)][Hits]	= 0;
	$mem[date("Y-m-d", $start)][Block]	= 0;
	$mem[date("Y-m-d", $start)][Ignored]= 0;
	$mem[date("Y-m-d", $start)][Datum]	= date("d.m.Y", $start);
}

// Get the hit statistics
$sql = sprintf(	"SELECT * " .
				"FROM %s " .
				"WHERE day >= '%s' " .
				"ORDER BY day ASC",
				$table_prefix . "ip_logger_statistics",
				date("Y-m-d", strtotime("-$days days")));
$data = $wpdb->get_results($sql);

// Transfer the found data to an array
foreach ($data as $entry) {
	if ($entry->category == "VisitorsTotal") 	$mem[$entry->day][Hits]    = $entry->count;
	if ($entry->category == "VisitorsBlocked") 	$mem[$entry->day][Block]   = $entry->count;
	if ($entry->category == "VisitorsIgnored") 	$mem[$entry->day][Ignored] = $entry->count;
}

// Close connection to the database
mysql_close($link);

/*** Generating charts ***/

include("open-flash-chart.php");

$data_hits 	= array();
$data_blck 	= array();
$x_labels 	= array();
$max 		= 0;
$count		= 1;
$domain 	= "ip-logger";

foreach ($mem as $entry) {
	// Sometimes the date field is empty
	// Then set text to "missing date"
	$entry[Datum] = empty($entry[Datum]) ? "missing date" : $entry[Datum];
	
	// For this chart:
	// Subtract the number og ignored hits from the number of hits in total.
	// The ignored hits should not be displayed on this chart
	$hits = $entry[Hits] - $entry[Ignored];
	$max = max($hits,$max);

	$data_hits[$entry[ID]] = new bar_value($hits);
	if (in_array(date("w", strtotime($entry[Datum])), array(0,6)))
		$data_hits[$entry[ID]]->set_colour('#ff0000');
	$data_hits[$entry[ID]]->set_tooltip(sprintf('%s<br>#val# %s<br>%s<br>%s', 
					$entry[Datum], 
					__("Hits", $domain), 
					$entry["Block"] . " Blocked", 
					$entry["Ignored"] . " Ignored"));
	
	$x_labels[] = ($count == 1 ? sprintf("%s", date(" d.\nM.", strtotime($entry[Datum]))) : "");
	$count = ($count == 3 ? $count = 1 : $count+1);
}

// Define the chart hint layout
$default_dot = new dot();
$default_dot->size(3)->colour('#DFC329')->tooltip("#x_label#:#val#");

// Add the bars for the allowed hits
$bar_hits = new bar_glass();
$bar_hits->set_values($data_hits);

// Set Y axis (interval, maximum)
$y = new y_axis();
$interval = ($max > 10 ? 10 : 1);
$interval = ($max > 100 ? 100 : 10);

// Calculate the next highest interval for the y-axis
$maxInterval = 0;
do $maxInterval += $interval;
while ($maxInterval < $max);

$y->set_range(0, $maxInterval, $interval);

$x = new x_axis();
$x->set_labels_from_array($x_labels);
$x->set_steps(1);

// Create the chart
$chart = new open_flash_chart();
$chart->set_bg_colour("#FFFFFF");
$chart->set_y_axis($y);
$chart->set_x_axis($x);
$chart->add_element($bar_hits);

// Echo the chart data as JSON string
// To get a more readable string, use ->toPrettyString();
echo $chart->toString();
?>