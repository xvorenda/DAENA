<?php
/* Teach PHP how to read URL parameters and connect to the database, plus add defaults */
include 'admin/config/db.php';
include 'assets/urlvars.php';

/* Get things started */
include 'assets/header.php';

/* Define Navbar */
include 'assets/navigation.php';



/* Start talking to MySQL and report the error if it ignores you */
$daenaDB = new mysqli(DB_HOST,DB_USER,DB_PASS,DB_NAME);
// Check connection
if (mysqli_connect_errno())
  {
  echo "Failed to connect to MySQL: " . mysqli_connect_error();
  }


/* Ask MySQL which freezers are active, from filters */
$freezerquery = "SELECT freezer_id,freezer_name,freezer_color,freezer_location
FROM daena_db.freezers
WHERE freezer_active='1'
".$groupfilter."
".$locfilter."
".$typefilter."
ORDER BY ABS(freezer_id)";

/* Define Variables and Arrays */
$i = 0;
$columnnames = array();
$freezercolors = array();
$freezerids = array();
$namearray = array();
$visibility = array();
$badneg_a = "-00";
$badneg_b = "-0";
$re_neg = "-";


/* Print Container Div for Graph, Data View, and Freezer-Box Toggles */
echo "
<style>.dygraph-title{font-size:12px!important;margin:auto!important;width:115%!important}</style>
<div id='container' class='status-graph'></div>
<div id='data' class='stealth'></div>
<div id='legend' class='status-legend'>
<table class='status-table'>
<tr><th class='status-table-freezername'>Freezer Name</th><th class='status-table-freezerloc'>Location</th></tr>
            ";

array_push($columnnames,"Time");
$freezers = $daenaDB->query($freezerquery);
while ($freezerrow = $freezers->fetch_assoc()) {
    $freezername = $freezerrow["freezer_name"];
    $freezerid = $freezerrow["freezer_id"];
    $colorname = $freezerrow["freezer_color"];
    $freezerlocation = $freezerrow["freezer_location"];
    array_push($columnnames,$freezername);
    array_push($namearray,$freezername);
    array_push($freezerids,$freezerid);
    array_push($freezercolors,$colorname);
    array_push($visibility,"true");
    echo "<tr>
              <td class='status-table-freezername'><span style='color: #".$colorname."'>".$freezername."</span></td>
              <td class='status-table-freezerloc'>".$freezerlocation."</td>
          </tr>
            ";
    $i++;
}

/* Format Freezer Names and Colors, Get Count */
$columnlist = implode ("\", \"",$columnnames);
$colorlist = implode ("', '#",$freezercolors);
$freezercount = count($columnnames) - 1;



/* Start Defining DyGraph */
echo "
</div>
<script type='text/javascript'>
  Dygraph.Interaction.endTouch = Dygraph.Interaction.moveTouch = Dygraph.Interaction.startTouch = function() {};
  chart = new Dygraph(

    // containing div
    document.getElementById(\"container\"),
        [\n";


/* Select Unique Times from MySQL Ping Data */
$pingtimequery = "SELECT DISTINCT int_time FROM daena_db.data
              WHERE int_time > ".$viewstart."
              ORDER BY int_time ASC";

$pingtimes = $daenaDB->query($pingtimequery);


$freezergroups = implode(',', $freezerids);
$visiblelist = implode(',', $visibility);


/* Use Unique Ping Times to Query for Data */
while ($pingrow = $pingtimes->fetch_assoc()) {
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

/* If the number of datapoints matches the number of freezers, print data row*/
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
                title: '".$group." Freezers  | Location: ".$loc." | ".$hours." Hour View',
                labels: [\"".$columnlist."\"],
                labelsDiv: document.getElementById('data'),
                legend: 'always',
                colors: ['#".$colorlist."'],
                visibility: [".$visiblelist."],
                drawGapEdgePoints: true,
                strokeWidth: 4,
                drawXGrid: false,
                axisLineColor: 'white',
                rollPeriod: ".$roll.",
                showRoller: false
              });
              function change(el) {
                chart.setVisibility(el.id, el.checked);
                $(el.parentElement).toggleClass('box-active')
              }
              function resetGraph()
              {
                chart.updateOptions(
                {
                  dateWindow: null,
                  valueRange: null,
                  visibility: [".$visiblelist."]
                });
              }
</script>";

/* Wrap things up */
include 'assets/footer.php';
?>
