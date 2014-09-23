<?php
/* Get things started */

$mysqlaction = filter_input(INPUT_POST, 'mysqlaction');
$probe_id = filter_input(INPUT_POST, 'probe_id');
$probe_type = filter_input(INPUT_POST, 'probe_type');
$probe_range = filter_input(INPUT_POST, 'probe_range');
$freezer_id = filter_input(INPUT_POST, 'freezer_id');
$probe_active = filter_input(INPUT_POST, 'probe_active');
$probe_hostport = filter_input(INPUT_POST, 'probe_hostport');
$probe_ntms_port = filter_input(INPUT_POST, 'probe_ntms_port');


/* Start talking to MySQL and kill yourself if it ignores you */
include '../config/db.php';
$daenaDB = new mysqli(DB_HOST,DB_USER,DB_PASS,DB_NAME);
if ($daenaDB === FALSE) 
{
    die(mysql_error()); // TODO: better error handling
}
mysql_select_db("daena_db");

/* Add a Probe */
$probeadd = "INSERT INTO daena_db.probes 
    (probe_type, probe_range, freezer_id, probe_active, probe_hostport, probe_ntms_port)
VALUES
    ('".$probe_type."', '".$probe_range."', '".$freezer_id."', '".$probe_active."', 
    '".$probe_hostport."', '".$probe_ntms_port."')";

/* Mod a Probe */
$probeupdate = "UPDATE daena_db.probes
SET probe_type='" . $probe_type . "', probe_range='" . $probe_range . "', 
	freezer_id='" . $freezer_id . "', probe_active='" . $probe_active . "', 
	probe_ntms_port='" . $probe_ntms_port . "'
	WHERE probe_id='" . $probe_id . "'";

if ($mysqlaction = "modify") 
{

	$onefreezer = mysql_query($probeupdate);
	if($onefreezer === FALSE) 
	{
		die(mysql_error()); // TODO: better error handling
	}	
	echo "Modification Success!";
	echo '<script>window.location.replace("';
	$pageURL = 'http://';
	if ($_SERVER["SERVER_PORT"] != "80") 
	{
	  $pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"];
	} 
	else 
	{
	  $pageURL .= $_SERVER["SERVER_NAME"];
	}
	 echo $pageURL;
	 echo '/admin/freezers.php");</script>';
}

if ($mysqlaction = "add") 
{

	$onefreezer = mysql_query($probeadd);
	if($onefreezer === FALSE) 
	{
		die(mysql_error()); // TODO: better error handling
	}
	echo 'Addition Success!';
	echo '<script>window.location.replace("';
	$pageURL = 'http://';
	if ($_SERVER["SERVER_PORT"] != "80") 
	{
	  $pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"];
	} 
	else 
	{
	  $pageURL .= $_SERVER["SERVER_NAME"];
	}
	 echo $pageURL;
	 echo '/admin/freezers.php");</script>';
}

/* Wrap things up */
//include '../assets/admin-footer.php';
?>