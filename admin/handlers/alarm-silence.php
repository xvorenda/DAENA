<?php
/* Get things started */
if ($login->isUserLoggedIn() == true)
{
	$freezer_id = filter_input(INPUT_POST, 'freezer_id');
	$alarm_level = filter_input(INPUT_POST, 'alarm_level');

	if($alarm_level == 3)
	{
		$new_alarm_level = 4;
	}
	elseif($alarm_level==6)
	{
		$new_alarm_level = 7;
	}

	$alarm_time = time()*1000;

	/* Start talking to MySQL and kill yourself if it ignores you */
	include('../config/db.php');
	$daenaDB = new mysqli(DB_HOST,DB_USER,DB_PASS,DB_NAME);
		// Check connection
		if (mysqli_connect_errno())
		  {
		  echo "Failed to connect to MySQL: " . mysqli_connect_error();
		  }

	$newalarm = "INSERT INTO alarm 
		SET freezer_id = '".$freezer_id."', 
			alarm_level = '".$new_alarm_level."', 
			alarm_time = '".$alarm_time."'";

	$error = 0;
	if ($mysqlaction == "add") 
	{
		if (!$daenaDB->query($newalarm)) 
		{
			printf("Errormessage: %s\n", $daenaDB->error);
			$error=1;
		}
		else
		{
			$new_alarm_ID = $daenaDB->insert_id;
	
			$updatefreezeralarm = "UPDATE freezers 
				SET freezer_alarm_id = '".$new_alarm_ID."' 
				where freezer_id = '".$freezer_id."'";
	
			if (!$daenaDB->query($updatefreezeralarm)) 
			{
				printf("Errormessage: %s\n", $daenaDB->error);
				$error=1;
			}
		}
	}
	if($error==0)
	{
		echo "Modification Success!";
		/*
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
		 echo '/admin/alarms.php");</script>';
		 */
	}
}
else
{
	include "assets/admin-header.php";
	include 'assets/admin-nav.php';
	echo "<div id='content'>"
		. "<h1>Unauthorized Access</h1>"
		. "<h3>Please <a href='index.php'>log in</a> to access this page.</h3>"
		. "</div>";
	include 'assets/admin-footer.php';
}