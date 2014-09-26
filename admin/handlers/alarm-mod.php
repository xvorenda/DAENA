<?php
/* Get things started */
require_once($_SERVER['DOCUMENT_ROOT']."/admin/libraries/password_compatibility_library.php");
require_once($_SERVER['DOCUMENT_ROOT']."/admin/config/db.php");
require_once($_SERVER['DOCUMENT_ROOT']."/admin/classes/Login.php");
$login = new Login();
if ($login->isUserLoggedIn() == true)
{
	$error = 0;
	if(isset($_POST['modify'])) 
	{
		$searchUrl = filter_input(INPUT_POST, 'searchUrl');
		$freezer_id = filter_input(INPUT_POST, 'freezer_id');
		$freezer_setpoint1 = filter_input(INPUT_POST, 'freezer_setpoint1');
		$freezer_setpoint2 = filter_input(INPUT_POST, 'freezer_setpoint2');
		//$freezer_send_alarm = filter_input(INPUT_POST, 'freezer_send_alarm');
		$freezer_send_alarm = (isset($_POST['freezer_send_alarm'])) ? 1 : 0;


		/* Start talking to MySQL and kill yourself if it ignores you */
		//include('../config/db.php');
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

	
		if (!$daenaDB->query($alarmupdate)) 
		{
			printf("Errormessage: %s\n", $daenaDB->error);
			$error=1;
		}
		else
		{
			if($error==0)
			{
				echo "Silence Success!";
				//echo '<script>window.location.replace("';
				$pageURL = 'http://';
				if ($_SERVER["SERVER_PORT"] != "80") 
				{
				  $pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$searchUrl;
			  
				} 
				else 
				{
				  $pageURL .= $_SERVER["SERVER_NAME"].":".$searchUrl;
				}
				 //echo $pageURL;
				 //echo '/admin/alarms.php");</script>';
				 echo $pageURL;
				 //header("Location: ".$pageURL);
			}
		}
	}


	elseif(isset($_POST['silence']))
	{
		$freezer_id = filter_input(INPUT_POST, 'freezer_id');
		$alarm_level = filter_input(INPUT_POST, 'alarm_level');


		$sendemail = "".$_SERVER['DOCUMENT_ROOT']."/py/silenceAlarm.py -f ".$freezer_id." -a ".$alarm_level;
		//$output = shell_exec($command);
		if (!shell_exec($sendemail)) 
		{
			printf("Error in Python Execution");
			$error=1;
		}

		
		if($error==0)
		{
			echo "Silence Success!";
			//echo '<script>window.location.replace("';
			$pageURL = 'http://';
			if ($_SERVER["SERVER_PORT"] != "80") 
			{
			  $pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$searchUrl;
			  
			} 
			else 
			{
			  $pageURL .= $_SERVER["SERVER_NAME"].":".$searchUrl;
			}
			 //echo $pageURL;
			 //echo '/admin/alarms.php");</script>';
			 echo $pageURL;
			 //header("Location: ".$pageURL);
		}
	}
}
else
{

	echo "<div id='content'>"
		. "<h1>Unauthorized Access</h1>"
		. "<h3>Please <a href='http://".$_SERVER["SERVER_NAME"]."/admin/index.php'>log in</a> to access this page.</h3>'"
		. "</div>";
	include 'assets/admin-footer.php';
}