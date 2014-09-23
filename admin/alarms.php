<?php
/* Get things started */
include "assets/admin-header.php";
include 'assets/admin-nav.php';

if ($login->isUserLoggedIn() == true) 
{

	/* Start talking to MySQL and kill yourself if it ignores you */
	//include 'config/db.php';
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
	if($allprobes === FALSE) 
	{
		die(mysqli_error()); // TODO: better error handling
	}



	/* Ask MySQL about which freeers exist and get their metadata */
	$allfreezersquery = "SELECT SQL_CALC_FOUND_ROWS *
		FROM daena_db.freezers 
		ORDER BY ABS(freezer_id)";
	$allfreezers = $daenaDB->query($allfreezersquery);


	/* Draw Probe Mod Area */
	echo "
	<div class='alarmbox'>
	<table class='borderless'>
	<tr>
		<td>Freezer ID</td>
		<td>Freezer Name</td>
		<td>Alarm Level</td>
		<td>Last Temp</td>
		<td>Setpoint 1</td>
		<td>Setpoint 2</td>
		<td>Send Alarm</td>
		<td>&nbsp;</td>
	</tr>
	";
	while(($freezerdata = $allfreezers->fetch_assoc()))
	{
		$freezer_id = $freezerdata['freezer_id'];
		$freezer_name = $freezerdata['freezer_name'];
		$freezer_setpoint1 = $freezerdata['freezer_setpoint1'];
		$freezer_setpoint2 = $freezerdata['freezer_setpoint2'];
		$freezer_alarm_id = $freezerdata['freezer_alarm_id'];
		$freezer_send_alarm = $freezerdata['freezer_send_alarm'];


		echo "<tr class='borderless'>
				<form action='handlers/probe-mod.php' method='POST'>
				<td class='field-narrow'><input type='text' class='input-medium search-query' name='probe_id' value='".$probe_id."'/></td>
				<td><input type='text' class='input-medium search-query' name='freezer_id' value='".$freezer_id."'/></td>
				<td><input type='text' class='input-medium search-query' name='probe_type' value='".$probe_type."'/></td>
				<td><input type='text' class='input-medium search-query' name='probe_range' value='".$probe_range."'/></td>
				<td class='field-narrow'><input type='text' class='input-medium search-query ' name='probe_active' value='".$probe_active."'/></td>
				<td class='field-wide'><input type='text' class='input-medium search-query ' name='probe_hostport' value='".$probe_hostport."'/></td>
				<td class='field-narrow'><input type='text' class='input-medium search-query' name='probe_ntms_port' value='".$probe_ntms_port."'/></td>
				<td><input type='text' class='stealth' name='mysqlaction' value='modify'/><input type='submit' name='submit' class='btn' value='Modify'/></td></form>
			   </tr>";
	}

	echo "<tr class='borderless'>
			<form action='handlers/probe-mod.php' method='POST'>
			<td class='field-narrow'><input type='text' class='input-medium search-query' name='probe_id'/></td>
			<td><input type='text' class='input-medium search-query' name='freezer_id'/></td>
			<td><input type='text' class='input-medium search-query' name='probe_type'/></td>
			<td><input type='text' class='input-medium search-query' name='probe_range'/></td>
			<td class='field-narrow'><input type='text' class='input-medium search-query' name='probe_active'/></td>
			<td class='field-wide'><input type='text' class='input-medium search-query' name='probe_hostport'/></td>
			<td class='field-narrow'><input type='text' class='input-medium search-query' name='probe_ntms_port'/></td>
			<td><input type='text' class='stealth' name='mysqlaction' value='add'/><input type='submit' name='submit' class='btn' value='Add'/></td></form>
		 </tr>
	  </table></div></div>";	
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
	    
