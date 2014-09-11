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


// show potential errors / feedback (from login object)
if (isset($login)) {
    if ($login->errors) {
        foreach ($login->errors as $error) {
            echo $error;
        }
    }
    if ($login->messages) {
        foreach ($login->messages as $message) {
            echo $message;
        }
    }
}
?>

<!-- login form box -->
<div class="content">
<form method="post" action="index.php" name="loginform">

    <label for="login_input_username">Username</label><br>
    <input id="login_input_username" class="login_input" type="text" name="user_name" required />
    <br><br>
    <label for="login_input_password">Password</label><br>
    <input id="login_input_password" class="login_input" type="password" name="user_password" autocomplete="off" required />
    <br><br>
    <input type="submit"  name="login" value="Log in" />

</form>
</div>
