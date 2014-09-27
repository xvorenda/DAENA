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


/* Ask MySQL which freezers are active */
$freezerquery = "SELECT freezer_id,freezer_name,freezer_color,freezer_location
FROM daena_db.freezers
WHERE freezer_active='1'
".$groupfilter."
".$locfilter."
".$typefilter."
ORDER BY ABS(freezer_id)";

/* Print Freezer Legend, Data View, and Toggles */
echo "
<div id='container'></div>
<div id='data'></div>
<div id='legend'>
            ";
$i = 0;
$columnnames = array();
$freezercolors = array();
$freezerids = array();
$namearray = array();
$visibility = array();
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
    echo "<div class='freezer-box'>
            <label for=\"".$i."\">
              <span style='color: #".$colorname."'>".$freezername."</span>
              <br>".$freezerlocation."
            </label><br>
            <input class='line-toggle' type=checkbox id=".$i." onClick=\"change(this)\" checked>
          </div>
            ";
    $i++;
}


$columnlist = implode ("\", \"",$columnnames);
$colorlist = implode ("', '#",$freezercolors);
$freezercount = count($columnnames) - 1;




echo "
</div>
<script type='text/javascript'>
  chart = new Dygraph(

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
                title: '".$group." Freezers  | Location: ".$loc." | ".$hours." Hour View',
                labels: [\"".$columnlist."\"],
                labelsDiv: document.getElementById('data'),
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
              function unzoomGraph() {
                chart.updateOptions({
                  dateWindow: null,
                  valueRange: null
                });
              }
</script>
<script>
$(document).ready(function()
{
  $(window).resize(function()
  {
    $('#legend').css(
    {
      position: 'absolute'
    });

    $('#legend').css(
    {
      left: ($(window).width() - $('#legend').outerWidth()) / 2,
      top: ($(window).height() - $('#legend').outerHeight()) -40,
      width: ($(window).width() - $('#legend').outerWidth()) / 2
    });
  });

  // call `resize` to center elements
  $(window).resize();
});
</script>";

/* Wrap things up */
include 'assets/footer.php';
?>
