<?php

/* Get things started */
include 'assets/header.php';

/* Teach PHP how to read URL parameters, plus add defaults */
include 'assets/urlvars.php';

/* Define the Dygraph */
echo "
<script type='text/javascript' src='js/dygraph-combined.js'></script>
</head>
<body>";

/* Set up navigation for different graphs || TODO: groups table, dynamically generate || */
include 'assets/url.php';
$url = curPageURL();
$baseurl = substr($url, 0, strpos($url, "?"));

include 'assets/navigation.php';

/* Actually draw the graph */

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
$columnheader = implode ("\", \"",$columnnames);
$freezercount = count($columnnames) - 1;


echo "<div id='container'></div>
<script type='text/javascript'>
  g = new Dygraph(

    // containing div
    document.getElementById(\"container\"),
        [\n";


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
      $pingtime = date('Y-m-d H:i:s', $pingtime/1000);
      $dataquery = "
          SELECT temp
          FROM daena_db.data
          WHERE int_time = ".$pingtime."
          ORDER BY freezer_id";

      $data = $daenaDB->query($dataquery);

      $datacount = $data->num_rows;

      if ($datacount == $freezercount){
        echo "            [".$pingtime;
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
}}
echo "        ],
              {
                labels: [\"".$columnheader."\"]
              });
</script>
";

/* Wrap things up */
include 'assets/footer.php';
?>
