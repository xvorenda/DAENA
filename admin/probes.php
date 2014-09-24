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


	/* Ask MySQL about which probes exist and get their metadata */
	$allprobesquery = "SELECT SQL_CALC_FOUND_ROWS *
		FROM daena_db.probes
		ORDER BY ABS(freezer_id)";
	$allprobes = $daenaDB->query($allprobesquery);


	/* Draw Probe Mod Area */
	echo "
	<div class='probesbox table-responsive'>
	<table class='table'>
	<tr><td>Probe ID</td><td>Freezer ID</td><td>Probe Type</td><td>Probe Range</td><td>Active</td><td>Probe Hostport</td><td>Probe NTMS Port</td><td>&nbsp;</td></tr>
	";
	while(($probedata = $allprobes->fetch_assoc()))
	{
		$probe_id = $probedata['probe_id'];
		$probe_type = $probedata['probe_type'];
		$probe_range = $probedata['probe_range'];
		$probe_active = $probedata['probe_active'];
		$probe_ntms_port = $probedata['probe_ntms_port'];
		$probe_hostport = $probedata['probe_hostport'];
		$freezer_id = $probedata['freezer_id'];
		
		if ($probe_active == 0)
		{
			$probe_active_checkbox = "unchecked";
		}
		else
		{
			$probe_active_checkbox = "checked";
		}

		echo "<tr class='borderless'>
				<form action='handlers/probe-mod.php' method='POST'>
				<td class='field-narrow'><input type='text' class='input-medium search-query' name='probe_id' value='".$probe_id."'/></td>
				<td><input type='text' class='input-medium search-query' name='freezer_id' value='".$freezer_id."'/></td>
				<td><input type='text' class='input-medium search-query' name='probe_type' value='".$probe_type."'/></td>
				<td><input type='text' class='input-medium search-query' name='probe_range' value='".$probe_range."'/></td>
				<td class='field-narrow'><input type='checkbox' class='input-medium-centered' name='probe_active' ".$probe_active_checkbox." value='1'/></td>
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
			<td class='field-narrow'><input type='checkbox' class='input-medium-centered' name='probe_active' checked value='1'/></td>
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
