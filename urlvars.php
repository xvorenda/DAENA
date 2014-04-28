<?php
if (isset($_GET['hours'])) {
$hours = $_GET['hours'];}
else $hours = 24;
$minutes = $hours * 60;
if (isset($_GET['skip'])) {
$skip = $_GET['skip'];}
else $skip = 8;
if (isset($_GET['loc'])) {
$loc = $_GET['loc'];}
else $loc = "All";
if (isset($_GET['group'])) {
$group = $_GET['group'];}
else $group = "All";
if (strpos($hours,'All') !== false) {
    $viewfilter = "";
} else $viewfilter = "LIMIT $minutes";

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
?>