<?php
/* Get things started */
include "assets/admin-header.php";
include 'assets/admin-nav.php';


/* Make sure user is logged in */
if ($login->isUserLoggedIn() == true) {

    
/* Start talking to MySQL */
include 'admin/config/db.php';
$daenaDB = new mysqli(DB_HOST,DB_USER,DB_PASS,DB_NAME);
// Check connection
if (mysqli_connect_errno())
  {
  echo "Failed to connect to MySQL: " . mysqli_connect_error();
  }


/* Ask MySQL about which probes exist and get their metadata */
$allprobesquery = "SELECT SQL_CALC_FOUND_ROWS *
	FROM daena_db.probes 
	ORDER BY ABS(probe_id)";
$allprobes = $daenaDB->query($allprobesquery);
    

/* Ask MySQL about which freeers exist and get their metadata */
$allfreezersquery = "SELECT SQL_CALC_FOUND_ROWS *
	FROM daena_db.freezers 
	ORDER BY ABS(freezer_id)";
$allfreezers = $daenaDB->query($allfreezersquery);


/* Draw Freezer Display/Mod Area */
echo "<div class='freezersbox'>
    <table>
        <tr>
            <td>Freezer Name</td>
            <td>Building</td>
            <td>Room Number</td>
            <td>Temperature Range</td>
            <td>NTMS Host</td>
            <td>NTMS Port</td>
            <td>Active</td>
            <td>Graph Color</td>
            <td>Freezer ID</td>
            <td>&nbsp;</td>
        </tr>";

while(($freezerdata = $allfreezers->fetch_assoc()))
{
    $freezer_name = $freezerdata['freezer_name'];
    $freezer_location = $freezerdata['freezer_location'];
    $freezer_temp_range = $freezerdata['freezer_temp_range'];
    $freezer_id = $freezerdata['freezer_id'];
    $freezer_active = $freezerdata['freezer_active'];
    $freezer_color = $freezerdata['freezer_color'];
    
    $location = explode("<br>", $freezer_location);
    $freezer_location_building = $location[0];
    $freezer_location_room = $location[1];
    
    $probequery = "SELECT probe_hostport FROM daena_db.probes 
    WHERE freezer_id='" . $freezer_id . "'";
    $proberesult = $daenaDB->query($probequery);
    
    while($probe = $proberesult->fetch_array()) 
    {
    	$probe_hostport = $probe['probe_hostport'];
    	$hostport = explode(" ", $probe_hostport);
            $probe_host = $hostport[0];
            $probe_port = $hostport[1]; 
    }

	echo "<tr>
			<form action='handlers/freezer-mod.php' method='POST'>
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
}

echo "<tr>
		<form action='handlers/freezer-mod.php' method='POST'>
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
</div>
</div>";	
} 
else 
{
	echo "<div id='content'>"
		. "<h1>Unauthorized Access</h1>"
		. "<h3>Please <a href='index.php'>log in</a> to access this page.</h3>"
		. "</div>";   
}
/* Wrap things up */
include 'assets/admin-footer.php';
?>