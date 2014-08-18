<!DOCTYPE html>
<?php
include 'header.php';
$baseurl = "index.php";
?>
<script type='text/javascript' src='https://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js'></script>
<script src='js/bootstrap.min.js'></script>
</head>
<body>
    <div class='navbar navbar-inverse navbar-fixed-top' role='navigation'>
      <div class='container-fluid'>
        <div class='navbar-header'>
          <button type='button' class='navbar-toggle' data-toggle='collapse' data-target='.navbar-collapse'>
            <span class='sr-only'>Navigation</span>
            <span class='icon-bar'></span>
            <span class='icon-bar'></span>
            <span class='icon-bar'></span>
          </button>
          <a class='navbar-brand white' href='".$baseurl."' title='Data Aggregation and Emergency Notifications for Appliances'>DAENA Monitoring System</a>
        </div>
     
     <div class='navbar-collapse collapse'>
          <ul class='nav navbar-nav'>
            <li class='dropdown'>
              <a href='#' class='dropdown-toggle' data-toggle='dropdown'>Group<b class='caret'></b></a>
              <ul class='dropdown-menu'>
                <li><a href='".$baseurl."?group=All'>All</a></li>
                <li><a href='".$baseurl."?group=Buck' class='green'>Buck</a></li>
                <li><a href='".$baseurl."?group=NARF' class='violet'>NARF</a></li>
                <li><a href='".$baseurl."?group=VMC' class='blue'>VMC</a></li>
              </ul>
            </li>
            <li class='dropdown'>
              <a href='#' class='dropdown-toggle' data-toggle='dropdown'>View<b class='caret'></b></a>
              <ul class='dropdown-menu'>
                <li><a href='".$baseurl."?hours=1'>One Hour</a></li>
                <li><a href='".$baseurl."?hours=4'>Four Hours</a></li>
                <li><a href='".$baseurl."?hours=8'>Eight Hours</a></li>
                <li><a href='".$baseurl."?hours=24'>One Day</a></li>
                <li><a href='".$baseurl."?hours=48'>Two Days</a></li>
                <li><a href='".$baseurl."?hours=168'>One Week</a></li>
             </ul>
            </li>
            <li class='dropdown'>
              <a href='#' class='dropdown-toggle' data-toggle='dropdown'>Density<b class='caret'></b></a>
              <ul class='dropdown-menu'>
                <li><a href='".$baseurl."?skip=1'>Plot All Points</a></li>
                <li><a href='".$baseurl."?skip=2'>Half Density</a></li>
                <li><a href='".$baseurl."?skip=4'>Quarter Density</a></li>
                <li><a href='".$baseurl."?skip=8'>Eighth Density</a></li>
                <li><a href='".$baseurl."?skip=16'>Sixteenth Density</a></li>
              </ul>
            </li>
            <li><a href='".$baseurl."'>Reset</a></li>
         </ul>
          <ul class='nav navbar-nav navbar-right'>
            <li><a href='about.php'>About</a></li>
            <li><a href='contact.php'>Contact</a></li>
          </ul>
        </div><!--/.nav-collapse -->
      </div>
    </div>
        <div id='about' style='margin:96px 24px'>
        <h1>Contact Us</h1>
        <p>If you have questions, comments, or suggestions, please email us at <a href='mailto:xvorenda@vcu.edu'>xvorenda@vcu.edu</a> and/or  <a href='mailto:voegtlylj@vcu.edu'>voegtlylj@vcu.edu</a>.</p>
        </div>
<?php    
include "footer.php";
?>