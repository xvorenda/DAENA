<?php
/* Get things started */

$mysqlaction = filter_input(INPUT_POST, 'mysqlaction');
$contact_id = filter_input(INPUT_POST, 'contact_id');
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
$contactupdate = "UPDATE daena_db.contacts
	SET name='".$contact_name."', email='".$contact_email."', 
		alt_email='".$contact_alt_email."'
	WHERE contact_id='" . $contact_id . "'";

if ($mysqlaction == "modify") 
{
	if (!$daenaDB->query($contactupdate)) 
	{
		printf("Errormessage: %s\n", $daenaDB->error);
	}

	echo "Modification Success!";
	echo '<script>window.location.replace("';
	$pageURL = 'https://';
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

if ($mysqlaction == "add") 
{

	if (!$daenaDB->query($contactadd)) {
		printf("Errormessage: %s\n", $daenaDB->error);
	}
	echo 'Addition Success!';
	echo '<script>window.location.replace("';
	$pageURL = 'https://';
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
?>