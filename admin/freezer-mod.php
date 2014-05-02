<?php
/* Get things started */
include "admin-header.php";
echo "
</head>
<body>";

$mysqlaction = filter_input(INPUT_POST, 'mysqlaction');
$freezer_name = filter_input(INPUT_POST, 'freezer_name');
$freezer_location = filter_input(INPUT_POST, 'freezer_location');
$freezer_temp_range = filter_input(INPUT_POST, 'freezer_temp_range');
$freezer_id = filter_input(INPUT_POST, 'freezer_id');
$freezer_group_id = substr($freezer_id, 0, 1);
$freezer_active = filter_input(INPUT_POST, 'freezer_active');
$freezer_color = filter_input(INPUT_POST, 'freezer_color');
$freezer_location_building = filter_input(INPUT_POST, 'freezer_location_building');
$freezer_location_room = filter_input(INPUT_POST, 'freezer_location_room');
$freezer_location = $freezer_location_building."<br>".$freezer_location_room;
$probe_host = filter_input(INPUT_POST, 'probe_host');
$probe_port = filter_input(INPUT_POST, 'probe_port');
$probe_hostport = $probe_host." ".$probe_port;
$baseurl = '../index.php';
include 'admin-nav.php';
/* Start talking to MySQL and kill yourself if it ignores you */
$daenaDB = mysql_connect("localhost", "tempurify_user", "idontcareaboutpasswordsrightnow");
if ($daenaDB === FALSE) {
    die(mysql_error()); // TODO: better error handling
}
mysql_select_db("tempurify");

/* Add a Freezer */
$freezeradd = "INSERT INTO tempurify.freezers 
    (freezer_active, freezer_color, freezer_location, freezer_name, freezer_temp_range, freezer_group_id)
VALUES
    (".$freezer_active.$freezer_color.$freezer_location.$freezer_name.$freezer_temp_range.$freezer_group_id.")";

/* Mod a Freezer */
$freezerupdate = "UPDATE tempurify.freezers
SET freezer_active='" . $freezer_active . "', freezer_color='" . $freezer_color . "', freezer_location='" . $freezer_location . "', freezer_name='" . $freezer_name . "', freezer_temp_range='" . $freezer_temp_range . "'
WHERE freezer_id='" . $freezer_id . "'";

if ($mysqlaction = "modify") {

$onefreezer = mysql_query($freezerupdate);
if($onefreezer === FALSE) {
    die(mysql_error()); // TODO: better error handling
}
echo "Modification Success!";}

if ($mysqlaction = "add") {

$onefreezer = mysql_query($freezeradd);
if($onefreezer === FALSE) {
    die(mysql_error()); // TODO: better error handling
}
echo "Addition Success!";}

/* Wrap things up */
include '../footer.php';
?>