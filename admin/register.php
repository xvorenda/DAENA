<?php
/* Get things started */
require_once("libraries/password_compatibility_library.php");
// include the configs / constants for the database connection
require_once("config/db.php");
// load the login class
require_once("classes/Login.php");
// create a login object. when this object is created, it will do all login/logout stuff automatically
// so this single line handles the entire login process. in consequence, you can simply ...
$login = new Login();
include "views/admin-header.php";
include 'views/admin-nav.php';


// load the registration class
require_once("classes/Registration.php");

// create the registration object. when this object is created, it will do all registration stuff automatically
// so this single line handles the entire registration process.
$registration = new Registration();

// show the register view (with the registration form, and messages/errors)
include("views/register.php");
