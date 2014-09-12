<?php

// if you are using PHP 5.3 or PHP 5.4 you have to include the password_api_compatibility_library.php
// (this library adds the PHP 5.5 password hashing functions to older versions of PHP)
require_once("libraries/password_compatibility_library.php");

// include the configs / constants for the database connection
require_once("config/db.php");

// load the login class
require_once("classes/Login.php");

// create a login object. when this object is created, it will do all login/logout stuff automatically
// so this single line handles the entire login process. in consequence, you can simply ...
$login = new Login();

// ... ask if we are logged in here:
if ($login->isUserLoggedIn() == true) {
    // the user is logged in. you can do whatever you want here.
    // for demonstration purposes, we simply show the "you are logged in" view.
    /* Get things started */
    include "assets/admin-header.php";
    include "assets/admin-nav.php";
    include "assets/home.php";
    include "assets/admin-footer.php";

} else {
    // the user is not logged in. you can do whatever you want here.
    // for demonstration purposes, we simply show the "you are not logged in" view.
    include "assets/admin-header.php";
    include "assets/admin-nav.php";
    include "assets/auth.php";
    include "assets/admin-footer.php";
}
