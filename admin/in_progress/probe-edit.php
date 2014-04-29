<?php
/* Get things started */
include "header.php";
include "urlvars.php";
echo "
</head>
<body>";
$baseurl = 'index.php';
include 'admin-nav.php';
echo "<div class='content'>";
/* Start talking to MySQL and kill yourself if it ignores you */
$daenaDB = mysql_connect("localhost", "tempurify_user", "idontcareaboutpasswordsrightnow");
if ($daenaDB === FALSE) {
    die(mysql_error()); // TODO: better error handling
}
mysql_select_db("tempurify");

/* Ask MySQL about which probes exist and get their metadata */
$allfreezersquery = "SELECT SQL_CALC_FOUND_ROWS *
FROM tempurify.freezers 
WHERE probe_id='1001'
ORDER BY ABS(freezer_id)";
$allfreezers = mysql_query($allfreezersquery);
if($allfreezers === FALSE) {
    die(mysql_error()); // TODO: better error handling
}
/* Count the active probes for density handling */
$countquery = "SELECT FOUND_ROWS()";
	$countraw = mysql_query($countquery);
	$countarray = mysql_fetch_assoc($countraw);
	$count = implode(",",$countarray);
$i = 0;
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
echo "<div class='probeinfo'>
            <table>
		        <h3>Add a Probe</h3>
		        <tr>
		            <td>Freezer Name:</td><td width='24'></td><td><input type='text' class='input-medium search-query' name='freezer_name'></input>".$freezer_name."</td>
		        </tr>
		        <tr>
		            <td>Building:</td><td width='24'></td><td><input type='text' class='input-medium search-query' name='freezer_location_building'></input>".$freezer_location_building."</td>
		        </tr>
		        <tr>
		            <td>Room:</td><td width='24'></td><td><input type='text' class='input-medium search-query' name='freezer_location_room'></input>".$freezer_location_room."</td>
		        </tr>
		        <tr>
		            <td>Temperature Range:</td><td></td><td><input type='text' class='input-medium search-query' name='freezer_temp_range'></input>".$freezer_temp_range."</td>
		        </tr>
		        <tr>
		            <td>NTMS Host:</td><td></td><td><input type='text' class='input-medium search-query' name='probe_host'></input>".$probe_host."</td>
		        </tr>
		        <tr>
		            <td>NTMS Port:</td><td></td><td><input type='text' class='input-medium search-query' name='probe_port'></input>".$probe_port."</td>
		        </tr>
		        <tr>
		            <td>Active:</td><td></td><td><input type='checkbox' class='input-medium search-query' name='probe_active' value='1' checked></input>".$freezer_active."</td>
		        </tr>
		        <tr>
		            <td>Graph Color:</td><td></td><td><input type='text' class='input-medium search-query color' name='freezer_color'></input>".$freezer_color."</td>
		        </tr>
		 		<tr>
		            <td>Probe ID:</td><td></td><td><input type='text' class='input-medium search-query' name='freezer_id'></input>".$freezer_id."</td>
		        </tr>
				</table>
</div></div>";};	    
/* Wrap things up */
include 'footer.php';
?>