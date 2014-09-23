<?php
/* Get things started */

$freezer_id = filter_input(INPUT_POST, 'freezer_id');
$freezer_setpoint1 = filter_input(INPUT_POST, 'freezer_setpoint1');
$freezer_setpoint2 = filter_input(INPUT_POST, 'freezer_setpoint2');
$freezer_send_alarm = filter_input(INPUT_POST, 'freezer_send_alarm');


/* Start talking to MySQL and kill yourself if it ignores you */
include('../config/db.php');
$daenaDB = new mysqli(DB_HOST,DB_USER,DB_PASS,DB_NAME);
	// Check connection
	if (mysqli_connect_errno())
	  {
	  echo "Failed to connect to MySQL: " . mysqli_connect_error();
	  }



/* Mod an Alarm */
$alarmupdate = "UPDATE daena_db.freezers
	SET freezer_setpoint1='" . $freezer_setpoint1 . "', freezer_setpoint2='" . $freezer_setpoint2 . "', 
		freezer_send_alarm='" . $freezer_send_alarm . "'
	WHERE freezer_id='" . $freezer_id . "'";


	$freezeralarm = $daenaDB->query($alarmupdate);
	
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