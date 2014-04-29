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
          <a class='navbar-brand white' href='".$baseurl."' title='Data Aggregation and Emergency Notifications for Appliances'>
          <img src='../images/daena.png' class='daena-logo'>DAENA Monitoring System
          </a>
        </div>
        <div class='navbar-collapse collapse'>
            <ul class='nav navbar-nav'>
               <li class='dropdown'>
                 <a href='#' class='dropdown-toggle' data-toggle='dropdown'>Probes<b class='caret'></b></a>
                 <ul class='dropdown-menu'>
                   <li><a href='index.php'>List All Probes</a></li>
                   <li><a href='probe-add.php'>Add a Probe</a></li>
                   <li><a href='probe-del.php'>Delete a Probe</a></li>
                 </ul>
               </li>
               <li class='dropdown'>
                 <a href='#' class='dropdown-toggle' data-toggle='dropdown'>Groups<b class='caret'></b></a>
                 <ul class='dropdown-menu'>
                   <li><a href='group-list.php'>List All Groups</a></li>
                   <li><a href='group-add.php'>Add a Group</a></li>
                   <li><a href='group-del.php'>Delete a Group</a></li>
                 </ul>
               </li>
            </ul>
             <ul class='nav navbar-nav navbar-right'>
               <li><a href='../about.php'>About</a></li>
             </ul>
           </div><!--/.nav-collapse -->
         </div>
       </div>
    </div>
    <div id='container' class='content'>";
?>
