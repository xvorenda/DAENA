<?php

/* Start talking to MySQL and kill yourself if it ignores you */
include 'admin/config/db.php';
$daenaDB = new mysqli(DB_HOST,DB_USER,DB_PASS,DB_NAME);
// Check connection
if (mysqli_connect_errno())
  {
  echo "Failed to connect to MySQL: " . mysqli_connect_error();
  }

/* Ask MySQL how many active probes total for density adjustments */
$freezercountquery = "SELECT SQL_CALC_FOUND_ROWS * 
FROM daena_db.freezers 
WHERE freezer_active='1'";
$countfreezers = $daenaDB->query($freezercountquery);
  
/* Count the active probes for density handling */
$countquery = "SELECT FOUND_ROWS()";
        	$countraw = $daenaDB->query($countquery);
        	$countarray = $countraw->fetch_assoc();
        	$count = implode(",",$countarray);

/* Ask MySQL about which probes exist and get their metadata */
$allfreezersquery = "SELECT freezer_id,freezer_name,freezer_color,freezer_location 
FROM daena_db.freezers 
WHERE freezer_active='1'
".$groupfilter."
".$locfilter."
".$typefilter."
ORDER BY ABS(freezer_id)";
$allfreezers = $daenaDB->query($allfreezersquery);


/* Ask MySQL for X hours of data on each probe */
while(($freezerdata = $allfreezers->fetch_assoc())){
    
    
    
    $freezer_id = $freezerdata['freezer_id'];
    $freezer_name = $freezerdata['freezer_name'];
    $freezer_color = $freezerdata['freezer_color'];
    $freezer_loc = $freezerdata['freezer_location'];
    $probequery = "(SELECT temp,time FROM daena_db.data 
    WHERE freezer_id='" . $freezer_id . "'
    ORDER BY time DESC " . $viewfilter . ") ORDER BY time ASC";
	$proberesult = $daenaDB->query($probequery);

        
    /* Get ready to do stuff */
    $random_color = substr(md5(rand()), 0, 6);
    $badzero_a = "-00";
    $badzero_b = "-0";
    $re_neg = "-";

    /* Name and colorize each freezer */

    if ($freezer_color != null) {
        echo $freezer_color;}
    else 
        echo $random_color;


    /* Limit displayed points to within view window */
    if ($hours !='All') {
    $now = time() * 1000;
    $timespan = $hours * 60 * 60 * 1000;
    $viewstop = $now - $timespan;}
    else $viewstop = 0;


    
    
    };
};
echo "
<script type='text/javascript' src='https://www.google.com/jsapi'></script>
    <script type="text/javascript">
      google.load("visualization", "1", {packages:["corechart"]});
      google.setOnLoadCallback(drawChart);
      function drawChart() {
        var data = google.visualization.arrayToDataTable(".$datatable.");";
        };


 /*       ['EpochTime', 'Freezer1001', 'Freezer1002', FreezerXXXX'],
          ['1410799002',  -80.2,       -21.4,           2.3],
          ['1410799062',  -80.4,       -20.8            0.4],
          ['1410799122',  -80.2,       -19.9           -4.1],
          ['1410799182',  -80.4,            ,          -8.4] */


        var options = {
          title: '".$group." Freezers <br>Location: ".$loc."<br>".$hours." Hour View | 1/".$skip." Density'
        };

        var chart = new google.visualization.LineChart(document.getElementById('chart_div'));

        chart.draw(data, options);
      }
    </script>";
?>