<?php

/* Start talking to MySQL and kill yourself if it ignores you */
include 'admin/config/db.php';
$daenaDB = new mysqli(DB_HOST,DB_USER,DB_PASS,DB_NAME);
// Check connection
if (mysqli_connect_errno())
  {
  echo "Failed to connect to MySQL: " . mysqli_connect_error();
  }

/* Limit displayed points to within view window */
  if ($hours !='All') 
        {$timespan = $hours * 3600 + 1;
        $limit = "LIMIT ".$timespan;
        }
  else 
        {$limit = "";}

/* Ask MySQL which freezers are active */
$freezerquery = "SELECT freezer_id,freezer_name,freezer_color,freezer_location 
FROM daena_db.freezers 
WHERE freezer_active='1'
".$groupfilter."
".$locfilter."
".$typefilter."
ORDER BY ABS(freezer_id)";

$columnnames = array();
array_push($columnnames,"Time");
$freezers = $daenaDB->query($freezerquery);
while ($freezerrow = $freezers->fetch_assoc()) {
    $freezername = $freezerrow["freezer_id"];
    array_push($columnnames,$freezername);
}
print_r($columnnames);
        
/* Ask MySQL for X number of minutes worth of ping data */
$pingquery = "
    SELECT time
FROM (
   (SELECT DISTINCT time, @rowNumber:=@rowNumber+ 1 rn
   FROM daena_db.data
      JOIN (SELECT @rowNumber:= 0) r
) ORDER BY time DESC) t 
WHERE rn % ".$skip." = 1
".$limit;

echo $pingquery;

$pings = $daenaDB->query($pingquery);

while ($pingrow = $pings->fetch_assoc()) {
      $pingtime = $pingrow["time"];
      $dataquery = "
          SELECT temp
          FROM daena_db.data
          WHERE time = ".$pingtime."
          ORDER BY freezer_id";
      echo $pingtime;
      $data = $daenaDB->query($dataquery);
      while ($datarow = $data->fetch_assoc()) {
          $datatemp = $datarow["temp"];
          
          echo $datatemp."\n";


      }
}

/* Count the active probes for density handling
$countquery = "SELECT FOUND_ROWS()";
        	$countraw = $daenaDB->query($countquery);
        	$countarray = $countraw->fetch_assoc();
        	$count = implode(",",$countarray);

/* Ask MySQL about which probes exist and get their metadata
$allfreezersquery = "SELECT freezer_id,freezer_name,freezer_color,freezer_location 
FROM daena_db.freezers 
WHERE freezer_active='1'
".$groupfilter."
".$locfilter."
".$typefilter."
ORDER BY ABS(freezer_id)";
$allfreezers = $daenaDB->query($allfreezersquery);


/* Ask MySQL for X hours of data on each probe
while(($freezerdata = $allfreezers->fetch_assoc())){
    
    
    
    $freezer_id = $freezerdata['freezer_id'];
    $freezer_name = $freezerdata['freezer_name'];
    $freezer_color = $freezerdata['freezer_color'];
    $freezer_loc = $freezerdata['freezer_location'];
    $probequery = "(SELECT temp,time FROM daena_db.data 
    WHERE freezer_id='" . $freezer_id . "'
    ORDER BY time DESC " . $viewfilter . ") ORDER BY time ASC";
	$proberesult = $daenaDB->query($probequery);

        
    /* Name and colorize each freezer

    if ($freezer_color == null) {
        $freezer_color = substr(md5(rand()), 0, 6);
        }
    };

echo "
<script type='text/javascript' src='https://www.google.com/jsapi'></script>
    <script type='text/javascript'>
      google.load('visualization', '1', {packages:['corechart']});
      google.setOnLoadCallback(drawChart);
      function drawChart() {
        var data = google.visualization.arrayToDataTable(".$datatable."
            );

        var options = {
          title: '".$group." Freezers <br>Location: ".$loc."<br>".$hours." Hour View | 1/".$skip." Density'
            };

        var chart = new google.visualization.LineChart(document.getElementById('chart_div'));

        chart.draw(data, options);
      }
    </script>"; 
 */
</script>
</head>
<body>"
?>