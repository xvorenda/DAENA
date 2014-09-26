<?php

/* Get things started */
include 'assets/header.php';

/* Teach PHP how to read URL parameters, plus add defaults */
include 'assets/urlvars.php';

/* Draw the GoogleChart */

/* Start talking to MySQL and kill yourself if it ignores you */
include 'admin/config/db.php';
$daenaDB = new mysqli(DB_HOST,DB_USER,DB_PASS,DB_NAME);
// Check connection
if (mysqli_connect_errno())
  {
  echo "Failed to connect to MySQL: " . mysqli_connect_error();
  }


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
$columnheader = implode ("', '",$columnnames);
$freezercount = count($columnnames) - 1;

echo "
<script type='text/javascript' src='https://www.google.com/jsapi'></script>
    <script type='text/javascript'>
      google.load('visualization', '1', {packages:['corechart']});
      google.setOnLoadCallback(drawChart);
      function drawChart() {
        var data = google.visualization.arrayToDataTable([";

echo "['".$columnheader."'],\n";

/* Ask MySQL for some number of minutes worth of ping data */
$pingquery = "SELECT DISTINCT int_time FROM daena_db.data
                  WHERE int_time > ".$viewstart."
                  ORDER BY int_time ASC";

$pings = $daenaDB->query($pingquery);


$badneg_a = "-00";
$badneg_b = "-0";
$re_neg = "-";

while ($pingrow = $pings->fetch_assoc()) {
      $pingtime = $pingrow["int_time"];
      $pingetime = $pingtime/1000;
      $dataquery = "
          SELECT temp
          FROM daena_db.data
          WHERE int_time = ".$pingtime."
          ORDER BY freezer_id";

      $data = $daenaDB->query($dataquery);

      $datacount = $data->num_rows;

      if ($datacount == $freezercount){
        echo "['";
        echo date('Y/m/d H:i:s', $pingetime);
        echo "'";
      while ($datarow = $data->fetch_assoc()) {
          $datatemp = $datarow["temp"];
          $datatemp = str_replace($badneg_a, $re_neg, $datatemp);
          $datatemp = str_replace($badneg_b, $re_neg, $datatemp);
          $datatemp = ltrim($datatemp, '+00');
          $datatemp = ltrim($datatemp, '+0');
          if ($datatemp == "nodata"){
              $datatemp = "null";}
          echo ", ".$datatemp;
      }
      echo "],\n";
  }
}
echo "]);

        var options = {
          title: '".$group." Freezers  | Location: ".$loc." | ".$hours." Hour View | 1/".$skip." Density',
          chartArea: {width: \"90%\", height: \"100%\"}
            };

        var chart = new google.visualization.LineChart(document.getElementById('container'));

        chart.draw(data, options);
      }
    </script>
  </head>
<body>";

/* Set up navigation for different graphs */
include "assets/url.php";
$url = curPageURL();
$baseurl = substr($url, 0, strpos($url, "?"));

include 'assets/navigation.php';

/* Actually draw the graph */
echo "<script type='text/javascript' src='https://www.google.com/jsapi?autoload={'modules':[{'name':'visualization','version':'1','packages':['corechart']}]}'></script>
       <div id='container'></div>";

/* Wrap things up */
include "assets/footer.php";
?>
