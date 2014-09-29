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
$freezerquery = "SELECT *
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
/* Draw Alarm Mod Area */
echo "
<style>.dygraph-title{font-size:14px!important;margin:auto!important;width:100%!important}</style>
<div id='container' class='status-graph'></div>
<div id='data' class='stealth'></div>
<div class='status-legend'>
<table class='status-table'>
<tr>
  <td>Freezer</td>
  <td>Warning</td>
  <td>Danger</td>
  <td>Last</td>
  <td>Conn</td>
  <td>Alarm</td>
  <td>Hush</td>
</tr>";

array_push($columnnames,"Time");
$freezers = $daenaDB->query($freezerquery);
while ($freezerrow = $freezers->fetch_assoc()) {
    $freezer_id = $freezerrow['freezer_id'];
    $freezer_name = $freezerrow['freezer_name'];
    $freezer_setpoint1 = $freezerrow['freezer_setpoint1'];
    $freezer_setpoint2 = $freezerrow['freezer_setpoint2'];
    $freezer_alarm_id = $freezerrow['freezer_alarm_id'];
    $colorname = $freezerrow["freezer_color"];
    $freezerlocation = $freezerrow["freezer_location"];
    array_push($columnnames,$freezer_name);
    array_push($namearray,$freezer_name);
    array_push($freezerids,$freezer_id);
    array_push($freezercolors,$colorname);
    array_push($visibility,"true");
    $freezer_loc = str_replace("<br>"," ",$freezerlocation);

          $alarm_query = "SELECT alarm_level, alarm_time
            FROM daena_db.alarm
            WHERE alarm_id='".$freezer_alarm_id."'";
          $alarmdata = $daenaDB->query($alarm_query);
          while($alarmrow = $alarmdata->fetch_assoc())
            {
              $alarm_level = $alarmrow['alarm_level'];
              $ms_epoch_time = $alarmrow['alarm_time'];
            };

          $epoch_time = round($ms_epoch_time/1000);
          $dt = new DateTime("@$epoch_time", (new DateTimeZone('UTC')));

          date_timezone_set($dt, timezone_open('America/New_York'));
          $alarm_date_time = $dt->format('m-d H:i:s');

          if ($alarm_level == 0)
          {
            $row_color = "status-success-bg";
            $icon = "glyphicon glyphicon-ok status-success";
          }
          elseif($alarm_level==1 || $alarm_level==2 || $alarm_level==5)
          {
            $row_color="status-warning-bg";
            $icon = "glyphicon glyphicon-exclamation-sign status-warning";
          }
          elseif($alarm_level==3 || $alarm_level==4)
          {
            $row_color="status-danger-bg";
            $icon = "glyphicon glyphicon-fire-sign status-danger";
          }
          elseif($alarm_level==6 || $alarm_level==7)
          {
            $row_color="status-info-bg";
            $icon = "glyphicon glyphicon-info-sign status-info";
          }

          $lasttempquery = "SELECT temp FROM daena_db.data
            WHERE freezer_id='".$freezer_id."'
            ORDER BY int_time DESC
            LIMIT 1";

          $lasttempdata = $daenaDB->query($lasttempquery);
          while($lasttemprow = $lasttempdata->fetch_assoc())
          {
            $last_reading = $lasttemprow['temp'];
          };

          $lasttempquery = "SELECT temp FROM daena_db.data
            WHERE freezer_id='".$freezer_id."' AND
            temp not REGEXP('nodata')
            ORDER BY int_time DESC
            LIMIT 2";
          $j = 1;
          $lasttempdata = $daenaDB->query($lasttempquery);
          while($lasttemprow = $lasttempdata->fetch_assoc())
          {
            $last_temp[$j] = $lasttemprow['temp'];
            $j++;
          };
          $last_temp1 = str_replace($badneg_a, $re_neg, $last_temp1);
          $last_temp1 = str_replace($badneg_b, $re_neg, $last_temp1);
          $last_temp1 = ltrim($last_temp1, '+00');
          $last_temp1 = ltrim($last_temp1, '+0');
          $last_temp2 = str_replace($badneg_a, $re_neg, $last_temp1);
          $last_temp2 = str_replace($badneg_b, $re_neg, $last_temp1);
          $last_temp2 = ltrim($last_temp1, '+00');
          $last_temp2 = ltrim($last_temp1, '+0');

          echo "<tr class='alarm-table-row'>
              <form action='handlers/alarm-mod.php' method='POST'>
                <td style='color:#".$colorname."'>".$freezer_name."</td>
                <td>".$freezer_setpoint1." &deg;C</td>
                <td>".$freezer_setpoint2." &deg;C</td>";
                if ($last_temp1 > $last_temp2) {
                echo "<td>".$last_temp1." &deg;C <span class='glyphicon glyphicon-arrow-up'></span></td>";
              } elseif ($last_temp1 < $last_temp2) {
                echo "<td>".$last_temp1." &deg;C <span class='glyphicon glyphicon-arrow-down'></span></td>";
              } elseif ($last_temp1 == $last_temp2) {
                echo "<td>".$last_temp[1]." &deg;C <span class='glyphicon glyphicon-minus'></span></td>";
              }
                if ($last_temp1 == $last_reading){
                  echo "<td><span class='glyphicon glyphicon-eye-open blue'></span>";
                } else {
                  echo "<td><span class='glyphicon glyphicon-eye-close status-warning'></span>";
                }
                echo "
                <td class='field-narrow'><span class='".$icon."' title='".$alarm_date_time."'></span></td>

";
                if ($alarm_level==3 || $alarm_level==6)
                {
                  echo"
                <input type='text' class='stealth' name='freezer_id' value='".$freezer_id."'/>
                <input type='text' class='stealth' name='alarm_level' value='".$alarm_level."'/>
                <td>
                  <button type='submit' name='silence' class='btn btn-danger'/>Hush</button>
                </td>";
                }
                else
                {
                  echo"
                <td>&nbsp;</td>";
                }
                echo"
                <input type='hidden' name='searchUrl' value='".$_SERVER["REQUEST_URI"]."' />
              </form>
            </tr>";
            $i++;};
echo "
  </table>
</div>";


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
