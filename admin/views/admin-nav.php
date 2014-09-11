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
          <a class='navbar-brand white' href='index.php' title='Data Aggregation and Emergency Notifications for Appliances'>
          <img src='../images/daena.png' class='daena-logo'>DAENA Monitoring System
          </a>
        </div>";
if ($login->isUserLoggedIn() == true) {
    echo "
        <div class='navbar-collapse collapse'>
            <ul class='nav navbar-nav'>
               <li class='dropdown'>
                 <a href='#' class='dropdown-toggle' data-toggle='dropdown'>Probes<b class='caret'></b></a>
                 <ul class='dropdown-menu'>
                   <li><a href='probe-view.php'>All Probes</a></li>
                   <li><a href='probe-del.php'>Delete a Probe</a></li>
                 </ul>
               </li>
               <li class='dropdown'>
                 <a href='#' class='dropdown-toggle' data-toggle='dropdown'>Freezers<b class='caret'></b></a>
                 <ul class='dropdown-menu'>
                   <li><a href='freezer-view.php'>All Freezers</a></li>
                   <li><a href='freezer-del.php'>Delete a Freezer</a></li>
                 </ul>
               </li>
               <li class='dropdown'>
                 <a href='#' class='dropdown-toggle' data-toggle='dropdown'>Groups<b class='caret'></b></a>
                 <ul class='dropdown-menu'>
                   <li><a href='group-view.php'>All Groups</a></li>
                   <li><a href='group-del.php'>Delete a Group</a></li>
                 </ul>
               </li>
            </ul>
             <ul class='nav navbar-nav navbar-right'>
               <li><a href='../about.php'>About</a></li>
                <li><a href='index.php?logout'>Logout</a></li>";
}else {
    echo "<div class='navbar-collapse collapse'>
            <ul class='nav navbar-nav navbar-right'>
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
