<?php
require_once("libraries/password_compatibility_library.php");
// include the configs / constants for the database connection
require_once("config/db.php");
// load the login class
require_once("classes/Login.php");
// create a login object. when this object is created, it will do all login/logout stuff automatically
// so this single line handles the entire login process. in consequence, you can simply ...
$login = new Login();
echo "
<html>
<head>
<title>DAENA | Data Aggregation and Emergency Notifications for Appliances</title>
<link rel='shortcut icon' href='../images/daena.png'/>
<meta charset='utf-8'>
<meta http-equiv='X-UA-Compatible' content='IE=edge'>
<meta name='viewport' content='width=device-width, initial-scale=1'>
<link href='../css/bootstrap.css' rel='stylesheet'>
<link href='../css/bootstrap.daena.css' rel='stylesheet'>


    <!--[if lt IE 9]>
      <script src='https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js'></script>
      <script src='https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js'></script>
    <![endif]-->

<script type='text/javascript' src='https://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js'></script>
<script type='text/javascript' src='../js/bootstrap.min.js'></script>
<script type='text/javascript' src='../js/jscolor/jscolor.js'></script>
</head>
";
?>
