<?php
/* Get things started */
include "admin-header.php";
echo "
</head>
<body>";

$freezer_name = filter_input(INPUT_POST, 'freezer_name');
$freezer_location = filter_input(INPUT_POST, 'freezer_location');
$freezer_temp_range = filter_input(INPUT_POST, 'freezer_temp_range');
$freezer_id = filter_input(INPUT_POST, 'freezer_id');
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

/*   */
$freezerupdate = "UPDATE tempurify.freezers
SET freezer_active='" . $freezer_active . "', freezer_color='" . $freezer_color . "', freezer_location='" . $freezer_location . "', freezer_name='" . $freezer_name . "', freezer_temp_range='" . $freezer_temp_range . "'
WHERE freezer_id='" . $freezer_id . "'";

$onefreezer = mysql_query($freezerupdate);
if($onefreezer === FALSE) {
    die(mysql_error()); // TODO: better error handling
}
echo "Success!";


/* Wrap things up */
include '../footer.php';
?>