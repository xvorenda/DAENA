<?php
/* Get things started */
include "admin-header.php";
include "../urlvars.php";
echo "
</head>
<body>";
$baseurl = '../index.php';
include 'admin-nav.php';
/* Start talking to MySQL and kill yourself if it ignores you */
$daenaDB = mysql_connect("localhost", "tempurify_user", "idontcareaboutpasswordsrightnow");
if ($daenaDB === FALSE) {
    die(mysql_error()); // TODO: better error handling
}
mysql_select_db("tempurify");


/* Ask MySQL about which probes exist and get their metadata */
$allprobesquery = "SELECT SQL_CALC_FOUND_ROWS *
FROM tempurify.probes 
ORDER BY ABS(probe_id)";
$allprobes = mysql_query($allprobesquery);
if($allprobes === FALSE) {
    die(mysql_error()); // TODO: better error handling
    
/* Count the active probes for density handling */
$countquery = "SELECT FOUND_ROWS()";
	$countraw = mysql_query($countquery);
	$countarray = mysql_fetch_assoc($countraw);
	$count = implode(",",$countarray);
}
/* Ask MySQL about which freeers exist and get their metadata */
$allfreezersquery = "SELECT SQL_CALC_FOUND_ROWS *
FROM tempurify.freezers 
ORDER BY ABS(freezer_id)";
$allfreezers = mysql_query($allfreezersquery);
if($allfreezers === FALSE) {
    die(mysql_error()); // TODO: better error handling
}
/* Count the active freezers for density handling */
$countquery = "SELECT FOUND_ROWS()";
	$countraw = mysql_query($countquery);
	$countarray = mysql_fetch_assoc($countraw);
	$count = implode(",",$countarray);
$i = 0;

/* Draw Probe Mod Area */
echo "
<div class='probesbox'>
<table>
<tr><td>Probe ID</td><td>Probe Type</td><td>Probe Range</td><td>Active</td><td>Probe Hort</td><td>Probe NTMS Port</td><td>&nbsp;</td></tr>
";
while(($probedata = mysql_fetch_assoc($allprobes))){
    $probe_id = $probedata['probe_id'];
    $probe_type = $probedata['probe_type'];
    $probe_range = $probedata['probe_range'];
    $probe_active = $probedata['probe_active'];
    $probe_ntms_port = $probedata['probe_ntms_port'];
    $probe_hostport = $probedata['probe_hostport'];

echo "<tr>
        <form action='probe-mod.php' method='POST'>
        <td><input type='text' class='input-medium search-query' name='probe_id' value='".$probe_id."'/></td>
        <td><input type='text' class='input-medium search-query' name='probe_type' value='".$probe_type."'/></td>
        <td><input type='text' class='input-medium search-query' name='probe_range' value='".$probe_range."'/></td>
        <td><input type='text' class='input-medium search-query' name='probe_active' value='".$probe_active."'/></td>
        <td><input type='text' class='input-medium search-query' name='probe_hostport' value='".$probe_hostport."'/></td>
        <td><input type='text' class='input-medium search-query field-narrow' name='probe_ntms_port' value='".$probe_ntms_port."'/></td>
        <td><input type='text' class='stealth' name='mysqlaction' value='modify'/><input type='submit' name='submit' class='btn' value='Modify'/></td></form>
</tr>";}

echo "<tr>
        <form action='probe-mod.php' method='POST'>
        <td><input type='text' class='input-medium search-query' name='probe_type'/></td>
        <td><input type='text' class='input-medium search-query' name='probe_range'/></td>
        <td><input type='text' class='input-medium search-query' name='probe_active'/></td>
        <td><input type='text' class='input-medium search-query' name='probe_port'/></td>
        <td><input type='text' class='input-medium search-query field-narrow' name='probe_ntms_port'/></td>
        <td><input type='text' class='input-medium search-query' name='probe_hostport'/></td>
        <td><input type='text' class='stealth' name='mysqlaction' value='add'/><input type='submit' name='submit' class='btn' value='Add'/></td></form>
    </tr>
</table>";




/* Draw Freezer Mod Area */
echo "
<div class='freezersbox'>
<table>
<tr><td>Freezer Name</td><td>Building</td><td>Room Number</td><td>Temperature Range</td><td>NTMS Host</td><td>NTMS Port</td><td>Active</td><td>Graph Color</td><td>Freezer ID</td><td>&nbsp;</td></tr>
";
while(($freezerdata = mysql_fetch_assoc($allfreezers))){
    $freezer_name = $freezerdata['freezer_name'];
    $freezer_location = $freezerdata['freezer_location'];
    $freezer_temp_range = $freezerdata['freezer_temp_range'];
    $freezer_id = $freezerdata['freezer_id'];
    $freezer_active = $freezerdata['freezer_active'];
    $freezer_color = $freezerdata['freezer_color'];
    $location = explode("<br>", $freezer_location);
        $freezer_location_building = $location[0];
        $freezer_location_room = $location[1];
    $probequery = "SELECT probe_hostport FROM tempurify.probes 
    WHERE freezer_id='" . $freezer_id . "'";
    $proberesult = mysql_query($probequery);
    while($probe = mysql_fetch_array($proberesult)) {
    $probe_hostport = $probe['probe_hostport'];
    $hostport = explode(" ", $probe_hostport);
        $probe_host = $hostport[0];
        $probe_port = $hostport[1]; };

echo "<tr>
        <form action='freezer-mod.php' method='POST'>
        <td><input type='text' class='input-medium search-query' name='freezer_name' value='".$freezer_name."'/></td>
        <td><input type='text' class='input-medium search-query' name='freezer_location_building' value='".$freezer_location_building."'/></td>
        <td><input type='text' class='input-medium search-query' name='freezer_location_room' value='".$freezer_location_room."'/></td>
        <td><input type='text' class='input-medium search-query' name='freezer_temp_range' value='".$freezer_temp_range."'/></td>
        <td><input type='text' class='input-medium search-query' name='probe_host' value='".$probe_host."'/></td>
        <td><input type='text' class='input-medium search-query' name='probe_port' value='".$probe_port."'/></td>
        <td><input type='text' class='input-medium search-query field-narrow' name='freezer_active' value='".$freezer_active."'/></td>
        <td><input type='text' class='input-medium search-query color' name='freezer_color' value='".$freezer_color."'/></td>
        <td><input type='text' class='input-medium search-query' name='freezer_id' value='".$freezer_id."'/></td>
        <td><input type='text' class='stealth' name='mysqlaction' value='modify'/><input type='submit' name='submit' class='btn' value='Modify'/></td></form>
    </tr>";
		$i++;};

echo "<tr>
        <form action='freezer-mod.php' method='POST'>
        <td><input type='text' class='input-medium search-query' name='freezer_name' value='New Freezer'/></td>
        <td><input type='text' class='input-medium search-query' name='freezer_location_building'/></td>
        <td><input type='text' class='input-medium search-query' name='freezer_location_room'/></td>
        <td><input type='text' class='input-medium search-query' name='freezer_temp_range'/></td>
        <td><input type='text' class='input-medium search-query' name='probe_host'/></td>
        <td><input type='text' class='input-medium search-query' name='probe_port'/></td>
        <td><input type='text' class='input-medium search-query field-narrow' name='probe_active'/></td>
        <td><input type='text' class='input-medium search-query color' name='freezer_color'/></td>
        <td><input type='text' class='input-medium search-query' name='freezer_id'/></td>
        <td><input type='text' class='stealth' name='mysqlaction' value='add'/><input type='submit' name='submit' class='btn' value='Add'/></form></td>
    </tr>
</table>
</div></div>";	
   

/* Wrap things up */
include '../footer.php';
?>
	    
