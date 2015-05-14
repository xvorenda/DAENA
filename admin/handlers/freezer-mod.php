<?php
/* Get things started */

$mysqlaction = filter_input(INPUT_POST, 'mysqlaction');
$freezer_name = filter_input(INPUT_POST, 'freezer_name');
$freezer_location = filter_input(INPUT_POST, 'freezer_location');
$freezer_temp_range = filter_input(INPUT_POST, 'freezer_temp_range');
$freezer_id = filter_input(INPUT_POST, 'freezer_id');
$freezer_group_id = substr($freezer_id, 0, 1);
//$freezer_active = filter_input(INPUT_POST, 'freezer_active');
$freezer_active = (isset($_POST['freezer_active'])) ? 1 : 0;
$freezer_color = filter_input(INPUT_POST, 'freezer_color');
$freezer_location_building = filter_input(INPUT_POST, 'freezer_location_building');
$freezer_location_room = filter_input(INPUT_POST, 'freezer_location_room');
$freezer_location = $freezer_location_building."<br>".$freezer_location_room;


/* Start talking to MySQL and kill yourself if it ignores you */
include('../config/db.php');
$daenaDB = mysql_connect(DB_HOST,DB_USER,DB_PASS);
if ($daenaDB === FALSE) {
    die(mysql_error()); // TODO: better error handling
}
mysql_select_db("daena_db");


/* Add a Freezer */
$freezeradd = "INSERT INTO daena_db.freezers
    (freezer_active, freezer_color, freezer_location,
    	freezer_name, freezer_temp_range, freezer_id, freezer_group_id)
	VALUES
    ('".$freezer_active."', '".$freezer_color."', '".$freezer_location."', '"
    	.$freezer_name."', '".$freezer_temp_range."', '".$freezer_id."', '".$freezer_group_id."')";

/* Mod a Freezer */
$freezerupdate = "UPDATE daena_db.freezers
	SET freezer_active='" . $freezer_active . "', freezer_color='" . $freezer_color . "',
		freezer_location='" . $freezer_location . "', freezer_name='" . $freezer_name . "',
		freezer_temp_range='" . $freezer_temp_range . "'
	WHERE freezer_id='" . $freezer_id . "'";

if ($mysqlaction = "modify")
{
  session_start();
	$freezeraddquery = mysql_query($freezerupdate);
  if(mysql_errno()){
    $_SESSION['notification'] = "MySQL error ".mysql_errno().": "
           .mysql_error()."\n<br>When executing <br>\n$freezeraddquery\n<br>";
  } else {
    $_SESSION['notification'] = "Modification Successful";
  }
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
	 echo '/admin/freezers.php");</script>';
}

if ($mysqlaction = "add")
{
  session_start();
	$freezeraddquery = mysql_query($freezeradd);
  if(mysql_errno()){
    $_SESSION['notification'] = "MySQL error ".mysql_errno().": "
           .mysql_error()."\n<br>When executing <br>\n$freezeraddquery\n<br>";
  } else {
    $_SESSION['notification'] = "Addition Successful";
  }
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
   echo '/admin/freezers.php");</script>';
  }/admin/freezers.php");</script>';
}

/* Wrap things up */
//include '../assets/admin-footer.php';
?>
