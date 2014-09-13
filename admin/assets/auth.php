<?php

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
<div class="admin-auth">
<form method="post" action="index.php" name="loginform">

    <label for="login_input_username">Username</label><br>
    <input id="login_input_username" class="input-medium" type="text" name="user_name" required />
    <br><br>
    <label for="login_input_password">Password</label><br>
    <input id="login_input_password" class="input-medium" type="password" name="user_password" autocomplete="off" required />
    <br><br>
    <input class="btn" type="submit"  name="login" value="Log in" />

</form>
</div>
