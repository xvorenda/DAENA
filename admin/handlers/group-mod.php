<?php
/* Get things started */

$mysqlaction = filter_input(INPUT_POST, 'mysqlaction');
$group_id = filter_input(INPUT_POST, 'group_id');
$group_name = filter_input(INPUT_POST, 'group_name');
$group_desc = filter_input(INPUT_POST, 'group_desc');

/* Start talking to MySQL and kill yourself if it ignores you */
include 'config/db.php';
$daenaDB = new mysqli(DB_HOST,DB_USER,DB_PASS,DB_NAME);
// Check connection
if (mysqli_connect_errno())
  {
  echo "Failed to connect to MySQL: " . mysqli_connect_error();
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

if ($mysqlaction = "modify") {

$onegroup = $daenaDB->query($groupupdate);
if($onegroup === FALSE) {
    die(mysqli_error()); // TODO: better error handling
}
echo "Modification Success!";
/*echo '<script>window.location.replace("';
$pageURL = 'http://';
if ($_SERVER["SERVER_PORT"] != "80") {
  $pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"];
 } else {
  $pageURL .= $_SERVER["SERVER_NAME"];
 }
 echo $pageURL;
 echo '/admin/groups.php");</script>';*/
}

if ($mysqlaction = "add") {

$onegroup = $daenaDB->query($groupadd);
if($onegroup === FALSE) {
    die(mysqli_error()); // TODO: better error handling
}
echo 'Addition Success!';
echo '<script>window.location.replace("';
$pageURL = 'http://';
if ($_SERVER["SERVER_PORT"] != "80") {
  $pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"];
 } else {
  $pageURL .= $_SERVER["SERVER_NAME"];
 }
 echo $pageURL;
 echo '/admin/groups.php");</script>';
}
?>