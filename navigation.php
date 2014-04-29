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
               <li class='dropdown'>
                 <a href='#' class='dropdown-toggle' data-toggle='dropdown'>Location<b class='caret'></b></a>
                 <ul class='dropdown-menu'>
                   <li><a href='".$baseurl."?hours=".$hours."&skip=".$skip."&group=".$group."&loc=All'>All Locations</a></li>
                   <li><a href='".$baseurl."?hours=".$hours."&skip=".$skip."&group=".$group."&loc=5-063'>Sanger 5-063</a></li>
                   <li><a href='".$baseurl."?hours=".$hours."&skip=".$skip."&group=".$group."&loc=5-072B'>Sanger 5-072B</a></li>
                   <li><a href='".$baseurl."?hours=".$hours."&skip=".$skip."&group=".$group."&loc=6-038'>Sanger 6-038</a></li>
                 </ul>
               </li>
               <li class='dropdown'>
                 <a href='#' class='dropdown-toggle' data-toggle='dropdown'>Group<b class='caret'></b></a>
                 <ul class='dropdown-menu'>
                   <li><a href='".$baseurl."?hours=".$hours."&skip=".$skip."&group=All&loc=".$loc."'>All Groups</a></li>
                   <li><a href='".$baseurl."?hours=".$hours."&skip=".$skip."&group=Buck&loc=".$loc."' class='green'>Buck</a></li>
                   <li><a href='".$baseurl."?hours=".$hours."&skip=".$skip."&group=NARF&loc=".$loc."' class='violet'>NARF</a></li>
                   <li><a href='".$baseurl."?hours=".$hours."&skip=".$skip."&group=VMC&loc=".$loc."' class='blue'>VMC</a></li>
                 </ul>
               </li>
               <li class='dropdown'>
                 <a href='#' class='dropdown-toggle' data-toggle='dropdown'>View<b class='caret'></b></a>
                 <ul class='dropdown-menu'>
                   <li><a href='".$baseurl."?hours=1&skip=".$skip."&group=".$group."&loc=".$loc."'>One Hour</a></li>
                   <li><a href='".$baseurl."?hours=4&skip=".$skip."&group=".$group."&loc=".$loc."'>Four Hours</a></li>
                   <li><a href='".$baseurl."?hours=8&skip=".$skip."&group=".$group."&loc=".$loc."'>Eight Hours</a></li>
                   <li><a href='".$baseurl."?hours=24&skip=".$skip."&group=".$group."&loc=".$loc."'>One Day</a></li>
                   <li><a href='".$baseurl."?hours=48&skip=".$skip."&group=".$group."&loc=".$loc."'>Two Days</a></li>
                   <li><a href='".$baseurl."?hours=168&skip=".$skip."&group=".$group."&loc=".$loc."'>One Week</a></li>
                   <li><a href='".$baseurl."?hours=All&skip=".$skip."&group=".$group."&loc=".$loc."'>All Time</a></li>
                </ul>
               </li>
               <li class='dropdown'>
                 <a href='#' class='dropdown-toggle' data-toggle='dropdown'>Density<b class='caret'></b></a>
                 <ul class='dropdown-menu'>
                   <li><a href='".$baseurl."?hours=".$hours."&skip=1&group=".$group."&loc=".$loc."'>All Points</a></li>
                   <li><a href='".$baseurl."?hours=".$hours."&skip=2&group=".$group."&loc=".$loc."'>Half Density</a></li>
                   <li><a href='".$baseurl."?hours=".$hours."&skip=4&group=".$group."&loc=".$loc."'>Quarter Density</a></li>
                   <li><a href='".$baseurl."?hours=".$hours."&skip=8&group=".$group."&loc=".$loc."'>Eighth Density</a></li>
                   <li><a href='".$baseurl."?hours=".$hours."&skip=16&group=".$group."&loc=".$loc."'>Sixteenth Density</a></li>
                 </ul>
               </li>
               <li><a href='".$baseurl."'>Reset</a></li>
            </ul>
             <ul class='nav navbar-nav navbar-right'>
               <li><a href='admin'>Admin</a></li>
               <li><a href='about.php'>About</a></li>

             </ul>
           </div><!--/.nav-collapse -->
         </div>
       </div>
    </div>";
?>