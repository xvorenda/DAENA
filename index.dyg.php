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
$freezercolors = array();
$freezerids = array();
$visibility = array();
array_push($columnnames,"Time");
$freezers = $daenaDB->query($freezerquery);
while ($freezerrow = $freezers->fetch_assoc()) {
    $freezername = $freezerrow["freezer_name"];
    $freezerid = $freezerrow["freezer_id"];
    $colorname = $freezerrow["freezer_color"];
    array_push($columnnames,$freezername);
    array_push($freezerids,$freezerid);
    array_push($freezercolors,$colorname);
    array_push($visibility,"true");
}
$columnheader = implode ("\", \"",$columnnames);
$colorlist = implode ("', '#",$freezercolors);
$freezercount = count($columnnames) - 1;

$namearray = explode ('\"',$colunmheader);
echo "
<div id='container'></div>
<div id='labels'></div>
<p><b>Display: </b>";

foreach ($namearray as $value) {

  print_r($namearray);
};
echo "
</p>
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
$freezergroups = implode(',', $freezerids);
$visiblelist = implode(',', $visibility);

while ($pingrow = $pings->fetch_assoc()) {
      $pingtime = $pingrow["int_time"];
      $pingepoch = $pingtime/1000;
      $dataquery = "
          SELECT temp,freezer_id
          FROM daena_db.data
          WHERE int_time = ".$pingtime."
          AND freezer_id IN (".$freezergroups.")
          ORDER BY freezer_id";

      $data = $daenaDB->query($dataquery);

      $datacount = $data->num_rows;

      if ($datacount == $freezercount){
        echo "            [ new Date(\"";
        echo date('Y/m/d H:i:s', $pingepoch);
        echo "\")";
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
                title: '".$group." Freezers  | Location: ".$loc." | ".$hours." Hour View | 1/".$skip." Density',
                labels: [\"".$columnheader."\"],
                labelsDiv: document.getElementById('labels'),
                legend: 'always',
                colors: ['#".$colorlist."'],
                visibility: [".$visiblelist."],
                strokeWidth: 4,
                drawXGrid: false,
                axisLineColor: 'white'
              });
              function change(el) {
                chart.setVisibility(el.id, el.checked);
              }
</script>";

/* Wrap things up */
include 'assets/footer.php';
?>
