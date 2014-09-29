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
<div class='container'>
<div id='graph'></div>
<div id='data'></div>


<div id='legend'></div>
            <div>";
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
    echo "<div class='freezer-box box-active'>
            <label class='click-label' for=\"".$i."\">
              <div class='box-spacer'>&nbsp;</div>
              <span style='color: #".$colorname."'>".$freezername."</span>
              <br>".$freezerlocation."
            </label>
            <input class='line-toggle' type=checkbox id=".$i." onClick=\"change(this)\" checked>
          </div>
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
</div>
<script type='text/javascript'>
  Dygraph.Interaction.endTouch = Dygraph.Interaction.moveTouch = Dygraph.Interaction.startTouch = function() {};
  chart = new Dygraph(

    // containing div
    document.getElementById('graph'),
        [\n";


/* Ask MySQL for unique ping times */
$pingquery = "SELECT DISTINCT int_time FROM daena_db.data
              WHERE int_time > ".$viewstart."
              ORDER BY int_time ASC";

$pings = $daenaDB->query($pingquery);


$freezergroups = implode(',', $freezerids);
$visiblelist = implode(',', $visibility);


/* Use Unique Ping Times to Query for Data */
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
                showRoller: false,
                hideOverlayOnMouseOut: false
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
              $('.freezer-box').addClass('box-active');
              }
</script>";


/* Position Legend Semi-Dynamically */
echo "<script type='text/javascript'>
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
      top: 500
    });
  });

  // call `resize` to center elements
  $(window).resize();
});
</script>";

/* Start talking to MySQL and report the error if it ignores you */
	$daenaDB = new mysqli(DB_HOST,DB_USER,DB_PASS,DB_NAME);
	// Check connection
	if (mysqli_connect_errno())
	  {
	  echo "Failed to connect to MySQL: " . mysqli_connect_error();
	  }

	/* Ask MySQL about which freeers exist and get their metadata */
	$allfreezersquery = "SELECT *
		FROM daena_db.freezers WHERE freezer_active = 1
		ORDER BY ABS(freezer_id)";
	$allfreezers = $daenaDB->query($allfreezersquery);


	/* Draw Alarm Mod Area */
	echo "
<div class='container-responsive'>
<div class='alarmbox table-responsive'>
	<table class='table'>
		<tr>
			<td>Freezer ID</td>
			<td>Freezer Name</td>
			<td>Alarm Level</td>
			<td>Alarm Time</td>
			<td>Last Temp</td>
			<td>Last Reading</td>
			<td>Silence Hourly Alarm</td>
			<td>Setpoint High Temp</td>
			<td>Setpoint Critical Temp</td>
			<td>Send Alarm</td>
			<td>&nbsp;</td>
		</tr>
	";
	while(($freezerdata = $allfreezers->fetch_assoc()))
	{
		$freezer_id = $freezerdata['freezer_id'];
		$freezer_name = $freezerdata['freezer_name'];
		$freezer_setpoint1 = $freezerdata['freezer_setpoint1'];
		$freezer_setpoint2 = $freezerdata['freezer_setpoint2'];
		$freezer_alarm_id = $freezerdata['freezer_alarm_id'];
		$freezer_send_alarm = $freezerdata['freezer_send_alarm'];

		if ($freezer_send_alarm == 0)
		{
			$freezer_send_alarm_checkbox = "unchecked";
		}
		else
		{
			$freezer_send_alarm_checkbox = "checked";
		}

		$alarm_query = "SELECT alarm_level, alarm_time FROM daena_db.alarm
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
		$alarm_date_time = $dt->format('Y-m-d H:i:s');

		if ($alarm_level == 0)
		{
			$row_color = "success";
		}
		elseif($alarm_level==1 || $alarm_level==2 || $alarm_level==5)
		{
			$row_color="warning";
		}
		elseif($alarm_level==3 || $alarm_level==4)
		{
			$row_color="danger";
		}
		elseif($alarm_level==6 || $alarm_level==7)
		{
			$row_color="info";
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
			LIMIT 1";

		$lasttempdata = $daenaDB->query($lasttempquery);
		while($lasttemprow = $lasttempdata->fetch_assoc())
		{
			$last_temp = $lasttemprow['temp'];
		};

		echo "<tr class='alarm-table-row'>
				<form action='admin/handlers/alarm-mod.php' method='POST'>
					<td class='".$row_color." round-first'><input type='text' class='stealth' name='freezer_id' value='".$freezer_id."'/>".$freezer_id."</td>
					<td class='".$row_color."'>".$freezer_name."</td>
					<td class='".$row_color." field-narrow'>".$alarm_level."</td>
					<td class='".$row_color." field-wide'>".$alarm_date_time."</td>
					<td class='".$row_color."'>".$last_temp."</td>
					<td class='".$row_color." '>".$last_reading."</td>";
					if ($alarm_level==3 || $alarm_level==6)
					{
						echo"
					<input type='text' class='stealth' name='freezer_id' value='".$freezer_id."'/>
					<input type='text' class='stealth' name='alarm_level' value='".$alarm_level."'/>
					<td class='".$row_color." round-last'>
						<button type='submit' name='silence' class='btn btn-danger'/>Silence</button>
					</td>";
					}
					else
					{
						echo"
					<td class='".$row_color." round-last'>No Hourly Alarms</td>";
					}
					echo"
					<td><input type='text' class='input-medium search-query' name='freezer_setpoint1' value='".$freezer_setpoint1."'/></td>
					<td><input type='text' class='input-medium search-query' name='freezer_setpoint2' value='".$freezer_setpoint2."'/></td>
					<td class='field-narrow'><input type='checkbox' class='input-medium' name='freezer_send_alarm' ".$freezer_send_alarm_checkbox." value='1'/></td>
					<input type='hidden' name='searchUrl' value='".$_SERVER["REQUEST_URI"]."' />
					<td>
						<button type='submit' name='modify' class='btn'/>Modify</button>
					</td>
				</form>
			</tr>";
	}

	echo "
	  </table>
	</div>
	</div>
</div>";

/* Wrap things up */
include 'assets/footer.php';
?>
