<?php

/* Get things started */
include 'assets/header.php';

/* Teach PHP how to read URL parameters, plus add defaults */
include 'assets/urlvars.php';

/* Define the HighChart */
echo "
<script type='text/javascript'>
        $(function () {
                Highcharts.setOptions({
                        global : {
                            useUTC : false
                        }
                    });
                $('#container').highcharts({
                    chart: {
                        renderTo: 'container',
                        defaultSeriesType: 'line',
                        zoomType: 'x',
                    },
                    title: { text: '".$group." Freezers <br>Location: ".$loc."<br>".$hours." Hour View | 1/".$skip." Density'},
                    subtitle: { text: ''},

                    xAxis: {
                        type: 'datetime',
                        dateTimeLabelFormats: {
                            hour: '%H:%M',
                            day: '%A',
                            week: '%A',
                            month: '%B',
                            year: '%Y'
                        },
                    },
                    yAxis: {
                        title: {
                            text: 'Temperature'
                        },
                        labels: {
                            formatter: function() {
                                return this.value / 1 +'°C';
                            }
                        }
                    },
                    tooltip: {
                        formatter: function() {
                        return  '<b>' + this.series.name +'</b><br/>' +
                            Highcharts.dateFormat('%H:%M',
                                                  new Date(this.x))
                        + ' | ' + this.y + ' °C';
                    },
                        pointFormat: '{series.name} reported <b>{point.y:,.0f}°C</b><br/>at {point.x}'
                    },
                    plotOptions: {
                            line: {
                                lineWidth: 6,
                                marker: {
                                    enabled: false
                                    }
                                }
                    },
                    series: [";

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
    $probequery = "SELECT temp,int_time FROM daena_db.data
    WHERE freezer_id='" . $freezer_id . "' AND int_time > ".$viewstart."
    ORDER BY int_time ASC";
	$proberesult = $daenaDB->query($probequery);


    /* Get ready to do stuff */
    $random_color = substr(md5(rand()), 0, 6);
    $badzero_a = "-00";
    $badzero_b = "-0";
    $re_neg = "-";

    /* Name and colorize each freezer */
    echo "
                    {name: '" . $freezer_name . "<br>" . $freezer_loc . "',
                    color: '#";
    if ($freezer_color != null) {
        echo $freezer_color;}
    else
        echo $random_color;

    /* Define each freezer graph */
    echo "',
                    dashStyle: 'solid',
                    pointInterval: ".$skip." * 60 * 1000,
                    data: [";
    /* Limit displayed points to within view window */
    if ($hours !='All') {
    $now = time();
    $timespan = $hours * 60 * 60 * 1000;
    $viewstop = $now - $timespan;}
    else $viewstop = 0;



    /* Actually get the data, clean up the strings, define density slices, and format the data for HighCharts */
    $i=1;
    while($probe = $proberesult->fetch_array()) {
        extract($probe, EXTR_PREFIX_SAME, "probe");
        if (isset($probe_temp)) {
        $probe_temp = str_replace($badzero_a, $re_neg, $probe_temp);
        $probe_temp = str_replace($badzero_b, $re_neg, $probe_temp);
        $probe_temp = ltrim($probe_temp, '+00');
        $probe_temp = ltrim($probe_temp, '+0');};
        if (isset($probe_int_time)) {
            $probe_minute = round($probe_int_time / 60) * 60;
            $bounce = $skip * 60;
            $time_slice = ($probe_minute / $bounce);
            $int_time_slice = intval($time_slice);
            $timequotient = $time_slice / $int_time_slice;
        };
        if (isset($probe_minute, $probe_temp)) {
        $timetemp = "[".$probe_minute.", ".$probe_temp."], ";
        if ($probe_minute != 0 && $probe_temp != "nodata" && $timequotient == 1 && $probe_minute > $viewstop){
            echo $timetemp;
        };
    };
};
echo "]},";
        };

/* Set up navigation for different graphs || TODO: groups table, dynamically generate || */
include "assets/url.php";
$url = curPageURL();
$baseurl = substr($url, 0, strpos($url, "?"));
echo "]
            });
        });
</script>
</head>
<body>";
include 'assets/navigation.php';

/* Actually draw the graph */
echo "
<script src='js/highcharts.js'></script>
<script src='js/modules/exporting.js'></script>
<div id='container'></div>";

/* Ask MySQL about which freeers exist and get their metadata */
$allfreezersquery = "SELECT SQL_CALC_FOUND_ROWS *
	FROM daena_db.freezers WHERE freezer_active = 1
	ORDER BY ABS(freezer_id)";
$allfreezers = $daenaDB->query($allfreezersquery);


/* Draw Alarm Mod Area */
echo "
<div id='container' class='content'>
	<div class='alarmbox table-responsive'>
		<table class='table'>
			<tr class='header'>
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
<br>
<br>
<br>
";

/* Wrap things up */
include "assets/footer.php";
?>
