<?php
echo "
    <div class='navbar navbar-inverse navbar-fixed-top' role='navigation'>
      <div class='container-fluid'>
        <div class='navbar-header'>
          <button type='button' class='navbar-toggle' data-toggle='collapse' data-target='.navbar-collapse'>
            <span class='sr-only'>Navigation</span>
            <span class='icon-bar'></span>
            <span class='icon-bar'></span>
            <span class='icon-bar'></span>
          </button>
          <a class='navbar-brand white' href='../index.php' title='Data Aggregation and Emergency Notifications for Appliances'>
          <img src='../images/daena.png' class='daena-logo'>DAENA</a>&nbsp;|&nbsp;
          <a class='white' href='index.php'>Admin</a>
        </div>";
if ($login->isUserLoggedIn() == true) {
    echo "
        <div class='navbar-collapse collapse'>
            <ul class='nav navbar-nav'>
                   <li><a href='probes.php'>Probes</a></li>
             
                   <li><a href='freezers.php'>Freezers</a></li>

                   <li><a href='groups.php'>Groups</a></li>
               
                   <li><a href='alarms.php'>Alarms</a></li>
                   
                   <li><a href='users.php'>Users</a></li>
            </ul>
             <ul class='nav navbar-nav navbar-right'>
                <li><a href='index.php?logout'>Logout</a></li>";
}else {
    echo "<div class='navbar-collapse collapse'>
            <ul class='nav navbar-nav navbar-right'>
            <li><a href='register.php'>Register</a></li>
            <li><a href='index.php'>Login</a></li>";
}
echo "
             </ul>
           </div><!--/.nav-collapse -->
         </div>
       </div>
    </div>
    <div id='container' class='content'>";
?>
