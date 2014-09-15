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
          	<img src='images/daena.png' class='daena-logo'>DAENA Monitoring System
          </a>
        </div>
        <div class='navbar-collapse collapse'>
             <ul class='nav navbar-nav'>
               <li><a href='".$baseurl."'>Home</a></li>
               <li class='dropdown'>
                 <a href='#' class='dropdown-toggle' data-toggle='dropdown'>Location<b class='caret'></b></a>
                 <ul class='dropdown-menu'>
                   <li><a href='".$baseurl."?hours=".$hours."&skip=".$skip."&group=".$group."&loc=All&type=".$type."'>All Locations</a></li>
                   <li><a href='".$baseurl."?hours=".$hours."&skip=".$skip."&group=".$group."&loc=5-063&type=".$type."'>Sanger 5-063</a></li>
                   <li><a href='".$baseurl."?hours=".$hours."&skip=".$skip."&group=".$group."&loc=5-072B&type=".$type."'>Sanger 5-072B</a></li>
                   <li><a href='".$baseurl."?hours=".$hours."&skip=".$skip."&group=".$group."&loc=6-038&type=".$type."'>Sanger 6-038</a></li>
                 </ul>
               </li>
               <li class='dropdown'>
                 <a href='#' class='dropdown-toggle' data-toggle='dropdown'>Group<b class='caret'></b></a>
                 <ul class='dropdown-menu'>
                   <li><a href='".$baseurl."?hours=".$hours."&skip=".$skip."&group=All&loc=".$loc."&type=".$type."'>All Groups</a></li>
                   <li><a href='".$baseurl."?hours=".$hours."&skip=".$skip."&group=Buck&loc=".$loc."&type=".$type."' class='green'>Buck</a></li>
                   <li><a href='".$baseurl."?hours=".$hours."&skip=".$skip."&group=NARF&loc=".$loc."&type=".$type."' class='violet'>NARF</a></li>
                   <li><a href='".$baseurl."?hours=".$hours."&skip=".$skip."&group=VMC&loc=".$loc."&type=".$type."' class='blue'>VMC</a></li>
                 </ul>
               </li>
               <li class='dropdown'>
                 <a href='#' class='dropdown-toggle' data-toggle='dropdown'>View<b class='caret'></b></a>
                 <ul class='dropdown-menu'>
                   <li><a href='".$baseurl."?hours=1&skip=".$skip."&group=".$group."&loc=".$loc."&type=".$type."'>One Hour</a></li>
                   <li><a href='".$baseurl."?hours=4&skip=".$skip."&group=".$group."&loc=".$loc."&type=".$type."'>Four Hours</a></li>
                   <li><a href='".$baseurl."?hours=8&skip=".$skip."&group=".$group."&loc=".$loc."&type=".$type."'>Eight Hours</a></li>
                   <li><a href='".$baseurl."?hours=24&skip=".$skip."&group=".$group."&loc=".$loc."&type=".$type."'>One Day</a></li>
                   <li><a href='".$baseurl."?hours=48&skip=".$skip."&group=".$group."&loc=".$loc."&type=".$type."'>Two Days</a></li>
                   <li><a href='".$baseurl."?hours=168&skip=".$skip."&group=".$group."&loc=".$loc."&type=".$type."'>One Week</a></li>
                   <li><a href='".$baseurl."?hours=All&skip=".$skip."&group=".$group."&loc=".$loc."&type=".$type."'>All Time</a></li>
                </ul>
               </li>
               <li class='dropdown'>
                 <a href='#' class='dropdown-toggle' data-toggle='dropdown'>Density<b class='caret'></b></a>
                 <ul class='dropdown-menu'>
                   <li><a href='".$baseurl."?hours=".$hours."&skip=1&group=".$group."&loc=".$loc."&type=".$type."'>All Points</a></li>
                   <li><a href='".$baseurl."?hours=".$hours."&skip=2&group=".$group."&loc=".$loc."&type=".$type."'>Half Density</a></li>
                   <li><a href='".$baseurl."?hours=".$hours."&skip=4&group=".$group."&loc=".$loc."&type=".$type."'>Quarter Density</a></li>
                   <li><a href='".$baseurl."?hours=".$hours."&skip=8&group=".$group."&loc=".$loc."&type=".$type."'>Eighth Density</a></li>
                   <li><a href='".$baseurl."?hours=".$hours."&skip=16&group=".$group."&loc=".$loc."&type=".$type."'>Sixteenth Density</a></li>
                 </ul>
               </li>
               <li class='dropdown'>
                 <a href='#' class='dropdown-toggle' data-toggle='dropdown'>Type<b class='caret'></b></a>
                 <ul class='dropdown-menu'>
                   <li><a href='".$baseurl."?hours=".$hours."&skip=".$skip."&group=".$group."&loc=".$loc."&type=All'>All Types</a></li>
                   <li><a href='".$baseurl."?hours=".$hours."&skip=".$skip."&group=".$group."&loc=".$loc."&type=-80'>-80 Freezers</a></li>
                   <li><a href='".$baseurl."?hours=".$hours."&skip=".$skip."&group=".$group."&loc=".$loc."&type=-20'>-20 Freezers</a></li>
                   <li><a href='".$baseurl."?hours=".$hours."&skip=".$skip."&group=".$group."&loc=".$loc."&type=4'>4 Fridge</a></li>
                 </ul>
               </li>
            </ul>
             <ul class='nav navbar-nav navbar-right'>
               <li><a href='about.php'>About</a></li>
               <li><a href='admin'>Admin</a></li>
             </ul>
        </div><!--/.nav-collapse -->
      </div>
    </div>
";
?>