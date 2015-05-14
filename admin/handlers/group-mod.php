<?php
/* Get things started */

$mysqlaction = filter_input(INPUT_POST, 'mysqlaction');
$group_id = filter_input(INPUT_POST, 'group_id');
$group_name = filter_input(INPUT_POST, 'group_name');
$group_desc = filter_input(INPUT_POST, 'group_desc');

/* Start talking to MySQL and kill yourself if it ignores you */
include('../config/db.php');
$daenaDB = new mysqli(DB_HOST,DB_USER,DB_PASS,DB_NAME);
// Check connection
if ($daenaDB->connect_errno)
{
    printf("Connect failed: %s\n", $daenaDB->connect_error);
    exit();
}

  /* Add a Group */
$groupadd = "INSERT INTO daena_db.groups
    (group_id, group_name, group_desc)
	VALUES
    ('".$group_id."', '".$group_name."', '".$group_desc."')";

/* Mod a Freezer */
$groupupdate = "UPDATE daena_db.groups
	SET group_name='" . $group_name . "', group_desc='" . $group_desc . "'
	WHERE group_id='" . $group_id . "'";

if ($mysqlaction == "modify")
{

	$onegroup = $daenaDB->query();
	if (!$daenaDB->query("$groupupdate"))
	{
		printf("Errormessage: %s\n", $daenaDB->error);
	}

	echo "Modification Success!";
	echo '<script>window.location.replace("';
  $pageURL = 'https://';
  if ($_SERVER["SERVER_PORT"] != "443")
  {
    $pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"];
  }
  else
  {
    $pageURL .= $_SERVER["SERVER_NAME"];
  }
   echo $pageURL;
	 echo '/admin/groups.php");</script>';
}

if ($mysqlaction == "add")
{

	if (!$daenaDB->query($groupadd)) {
		printf("Errormessage: %s\n", $daenaDB->error);
	}
	echo 'Addition Success!';
	echo '<script>window.location.replace("';
  $pageURL = 'https://';
  if ($_SERVER["SERVER_PORT"] != "443")
  {
    $pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"];
  }
  else
  {
    $pageURL .= $_SERVER["SERVER_NAME"];
  }
   echo $pageURL;
	 echo '/admin/groups.php");</script>';
}
?>
