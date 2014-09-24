<?php
/* Get things started */

$mysqlaction = filter_input(INPUT_POST, 'mysqlaction');
$contact_name = filter_input(INPUT_POST, 'contact_name');
$contact_email = filter_input(INPUT_POST, 'contact_email');
$contact_alt_email = filter_input(INPUT_POST, 'contact_alt_email');
/* Start talking to MySQL and kill yourself if it ignores you */
include('../config/db.php');
$daenaDB = new mysqli(DB_HOST,DB_USER,DB_PASS,DB_NAME);
// Check connection
if ($daenaDB->connect_errno) 
{
    printf("Connect failed: %s\n", $daenaDB->connect_error);
    exit();
}

  /* Add a Contact */
/*
$groupadd = "INSERT INTO daena_db.groups 
    (group_id, group_name, group_desc)
	VALUES
    ('".$group_id."', '".$group_name."', '".$group_desc."')";
*/
/* Mod a Contact */
$contactadd = "INSERT INTO daena_db.contacts
	SET name='".$contact_name."', 
		email='".$contact_email."', 
		alt_email=".$contact_alt_email."'";

$error = 0;

if ($mysqlaction == "add") 
{
	if (!$daenaDB->query($contactupdate)) 
	{
		printf("Errormessage: %s\n", $daenaDB->error);
		$error=1;
	}
	$contact_id = $daenaDB->insert_id;

	foreach( $_POST['freezer_id'] as $current_freezer_id) 
	{
		$alarm0 = (isset($_POST[$current_freezer_id.'alarm0'])) ? 1 : 0;
		$alarm1 = (isset($_POST[$current_freezer_id.'alarm1'])) ? 1 : 0;
		$alarm2 = (isset($_POST[$current_freezer_id.'alarm2'])) ? 1 : 0;
		$alarm3 = (isset($_POST[$current_freezer_id.'alarm3'])) ? 1 : 0;
		$alarm4 = (isset($_POST[$current_freezer_id.'alarm4'])) ? 1 : 0;
		$alarm5 = (isset($_POST[$current_freezer_id.'alarm5'])) ? 1 : 0;
		$alarm6 = (isset($_POST[$current_freezer_id.'alarm6'])) ? 1 : 0;
		$alarm7 = (isset($_POST[$current_freezer_id.'alarm7'])) ? 1 : 0;
	

		/* Mod a Contact */
		$alarmcontactinsert = "INSERT INTO daena_db.freezer_alarm_contacts
			SET freezer_id='".$current_freezer_id."',
				contact_id='".$contact_id."',
				alarm0='".$alarm0."', 
				alarm1='".$alarm1."', 
				alarm2='".$alarm2."', 
				alarm3='".$alarm3."', 
				alarm4='".$alarm4."', 
				alarm5='".$alarm5."', 
				alarm6='".$alarm6."', 
				alarm7='".$alarm7."'";
	
		if (!$daenaDB->query($alarmcontactinsert)) 
		{
			printf("Errormessage: %s\n", $daenaDB->error);
			$error = 1;
		}
	}
	if($error==0)
	{
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
		 echo '/admin/contacts.php");</script>';
	}
}


?>