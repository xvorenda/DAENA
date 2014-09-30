<?php
if (isset($_GET['hours'])) {
	$hours = $_GET['hours'];}
else $hours = 24;
	$minutes = $hours * 60;
if (isset($_GET['roll'])) {
	$roll = $_GET['roll'];}
else $roll = 1;
if (isset($_GET['loc'])) {
	$loc = $_GET['loc'];}
else $loc = "All";
if (isset($_GET['group'])) {
	$group = $_GET['group'];}
else $group = "All";
if (isset($_GET['type'])) {
	$type = $_GET['type'];}
else $type = "All";

if (strpos($hours,'All') !== false) {
    $viewfilter = "";
} else $viewfilter = "LIMIT $minutes";

if (strpos($hours,'All') !== false) {
		$viewwindow = "0";
} else {
	$now = microtime(true)*1000;
	$viewspan = $hours * 60 * 60 * 1000;
	$viewstart = $now - $viewspan;
};

if (strpos($group,'All') !== false) {
    $groupfilter = "";
};
if (strpos($group,'VMC') !== false) {
    $groupfilter = "AND freezer_group_id='1'";
};
if (strpos($group,'Buck') !== false) {
    $groupfilter = "AND freezer_group_id='2'";
};
if (strpos($group,'NARF') !== false) {
    $groupfilter = "AND freezer_group_id='3'";
};
if (strpos($group,'Test') !== false) {
    $groupfilter = "AND freezer_group_id='9'";
};
if (strpos($loc,'All') !== false) {
    $locfilter = "";
};
if (strpos($loc,'6-038') !== false) {
    $locfilter = "AND freezer_location='Sanger<br>6-038'";
};
if (strpos($loc,'5-063') !== false) {
    $locfilter = "AND freezer_location='Sanger<br>5-063'";
};
if (strpos($loc,'5-072B') !== false) {
    $locfilter = "AND freezer_location='Sanger<br>5-072B'";
};
if (strpos($type,'All') !== false) {
    $typefilter = "";
};
if (strpos($type,'-80') !== false) {
    $locfilter = "AND freezer_temp_range='-80'";
};
if (strpos($type,'-20') !== false) {
    $typefilter = "AND freezer_temp_range='-20'";
};
if (strpos($type,'4') !== false) {
    $typefilter = "AND freezer_temp_range='4'";
};
function curPageURL() {
$pageURL = 'http';
/*if ($_SERVER["HTTPS"] == "on") {$pageURL .= "s";}*/
$pageURL .= "://";
if ($_SERVER["SERVER_PORT"] != "80") {
	$pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
} else {
	$pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
}
return $pageURL;
}
$url = curPageURL();
$baseurl = substr($url, 0, strpos($url, "?"));
?>
