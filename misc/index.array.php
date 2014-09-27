<?php

/* Get things started */
include "assets/header.php";


/* Teach PHP how to read URL parameters, plus add defaults */
include "assets/urlvars.php";

/* Start talking to MySQL and kill yourself if it ignores you */
$daenaDB = new mysqli("localhost", "daena_user", "idontcareaboutpasswordsrightnow", "daena_db");
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
// Check connection
if (mysqli_connect_errno())
  {
  echo "Failed to connect to MySQL: " . mysqli_connect_error();
  }
  
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
// Check connection
if (mysqli_connect_errno())
  {
  echo "Failed to connect to MySQL: " . mysqli_connect_error();
  }

/* Variables to prepare data */
$badzero_a = "-00";
$badzero_b = "-0";
$re_neg = "-";

/* Limit displayed points to within view window 
if ($hours !='All') {
	$now = time() * 1000;
	$timespan = $hours * 60 * 60 * 1000;
	$viewstop = $now - $timespan;}
else $viewstop = 0;
*/
$arraytime = array();

# $final_minute = round(time()/60);

/* Determine what the times will be */
if ($hours == "All")
{
/* Determine what the times will be */
$firsttime = (time()*1000)-($hours*60*60*1000);
//echo $firsttime;
$gettimequery = "SELECT DISTINCT int_time 
	FROM daena_db.data 
	ORDER BY int_time ASC";
}
else
{
	$firsttime = (time()*1000)-($hours*60*60*1000);
	//echo $firsttime;
	$gettimequery = "SELECT DISTINCT int_time 
		FROM daena_db.data where int_time >= ". $firsttime. "
		ORDER BY int_time ASC";
}
$timeresult = $daenaDB->query($gettimequery);
if (mysqli_connect_errno())
{
	echo "Failed to connect to MySQL: " . mysqli_connect_error();
}


$skipcount=0;
/* Populate arraytime with times */
while($time = $timeresult->fetch_array())
{
	# Skip every x time
	if($skipcount % $skip == 0)
	{
		# load data into arraytime which will hold data for time
		$arraytime[] = (int)$time[0];
	}
	$skipcount ++;
}
//print_r( $arraytime);
$freezer_array = array();

/* Ask MySQL for X hours of data on each probe and prepare data for graph*/
while($freezerdata = $allfreezers->fetch_assoc())
{

    $freezer_id = $freezerdata['freezer_id'];
    $freezer_name = $freezerdata['freezer_name'];
    $freezer_color = $freezerdata['freezer_color'];
    $freezer_loc = $freezerdata['freezer_location'];
      	
	$freezer_array['id'][]=$freezerdata['freezer_id'];
	$freezer_array['name'][]= $freezerdata['freezer_name'];
	$freezer_array['color'][]= $freezerdata['freezer_color'];
	$freezer_array['location'][]= $freezerdata['freezer_location'];
}

				  
$json_chart = array();
//$json_row[] = "time";
$labelsarray[] = 'time';
foreach ($freezer_array['id'] as $freezerid)
{
	$labelsarray[] = $freezerid;
	//$json_row[] = $freezerid;
}
$labels = implode(",", $labelsarray);
//$json_chart[]=$json_row;

foreach ($arraytime as $datatime)
{
	$freezertemp = array();
	$freezertemp[] = $datatime;
	foreach ($freezer_array['id'] as $freezerid)
	{
		$tempquery = "SELECT data.temp FROM daena_db.data
			WHERE freezer_id= ". $freezerid ." AND int_time = ".$datatime;

		if($tempresult = $daenaDB->query($tempquery))
		{
			$temparray = $tempresult->fetch_array();
			$tempdata = $temparray[0];
			if($tempdata == 'nodata')
			{
				$freezertemp[] = "null";
			}
			else
			{
				$freezertemp[] = (float)$tempdata;
			}
			$tempresult->close();
		}
		else
		{	
			$freezertemp[] = "null";
		}
	}
	array_push($json_chart, $freezertemp);
}


//print_r($json_chart);

//###############################################################################
// $chart = array();
// 
// # $final_minute = round(time()/60);
// 
// /* Determine what the times will be */
// if ($hours == "All")
// {
// /* Determine what the times will be */
// $firsttime = (time()*1000)-($hours*60*60*1000);
// //echo $firsttime;
// $gettimequery = "SELECT DISTINCT int_time 
// 	FROM daena_db.data 
// 	ORDER BY int_time ASC";
// }
// else
// {
// 	$firsttime = (time()*1000)-($hours*60*60*1000);
// 	//echo $firsttime;
// 	$gettimequery = "SELECT DISTINCT int_time 
// 		FROM daena_db.data where int_time >= ". $firsttime. "
// 		ORDER BY int_time ASC";
// }
// $timeresult = $daenaDB->query($gettimequery);
// if (mysqli_connect_errno())
// {
// 	echo "Failed to connect to MySQL: " . mysqli_connect_error();
// }
// 
// /* Ask MySQL for X hours of data on each probe and prepare data for graph*/
// while($freezerdata = $allfreezers->fetch_assoc())
// {
// 
//     $freezer_id = $freezerdata['freezer_id'];
//     $freezer_name = $freezerdata['freezer_name'];
//     $freezer_color = $freezerdata['freezer_color'];
//     $freezer_loc = $freezerdata['freezer_location'];
//       	
// 	$freezer_array['id'][]=$freezerdata['freezer_id'];
// 	$freezer_array['name'][]= $freezerdata['freezer_name'];
// 	$freezer_array['color'][]= $freezerdata['freezer_color'];
// 	$freezer_array['location'][]= $freezerdata['freezer_location'];
// 
// 
// 
//  
// 	 /* Order Desc, then Limit number of rows, then final output ASCENDING*/
// 	 $probequery = "SELECT temp, int_time FROM daena_db.data 
// 		WHERE freezer_id='" . $freezer_id . " and int_time >= ". $firsttime. " 
// 		ORDER BY time ASC";
// 	 $proberesult = $daenaDB->query($probequery);
// 	 // Check connection
// 	 if (mysqli_connect_errno())
// 	   {
// 	   echo "Failed to connect to MySQL: " . mysqli_connect_error();
// 	   }
//  
// 	 /* Actually get the data, clean up the strings, define density slices, and format the data for chart */
// 	 $i=1;
// 	 $charttimeindex = 0;
// 	 while($probe = $proberesult->fetch_array()) 
// 	 {
// 		extract($probe, EXTR_PREFIX_SAME, "probe");
// 	
// 		if(isset($probe_time))
// 		{
// 			$probe_minute = $probe_time; 
// 			/* 
// 				Time on the chart is greater than the probe time 
// 				continue to the next iteration but do not increment 
// 				the chart time index or freezer data index.
// 			*/ 
// 			if($chart['time'][$charttimeindex] > $probe_minute)
// 			{
// 				continue;
// 			}
// 			/* 
// 				Time on the chart is less than the probe time 
// 				insert null data for freezer at that chart index time
// 				increment the chart time index continue to 
// 				the next iteration.
// 			*/ 
// 			elseif($chart['Time'][$charttimeindex] < $probe_minute)
// 			{
// 				$chart [$freezer_id][$charttimeindex]=null;
// 				$charttimeindex = $charttimeindex+1;
// 				continue;
// 			}
// 			/* 
// 				Time on the chart equals the probe time 
// 				check for temperature data and add it to the chart
// 				increment the chart time index continue to 
// 				the next iteration.
// 			*/ 
// 			elseif($chart['Time'][$charttimeindex] == $probe_minute)
// 			{
// 				if(isset($probe_temp))
// 				{
// //					$probe_temp = str_replace($badzero_a, $re_neg, $probe_temp);
// //					$probe_temp = str_replace($badzero_b, $re_neg, $probe_temp);
// //					$probe_temp = ltrim($probe_temp, '+00');
// //					$probe_temp = ltrim($probe_temp, '+0');
// 					if ($probe_temp == "nodata")
// 					{
// 						$chart [$freezer_id][$charttimeindex]=null;
// 						$charttimeindex = $charttimeindex+1;
// 						continue;
// 					}
// 					else
// 					{
// 						$floattemp = (float) $probe_temp;
// 						$chart [$freezer_id][$charttimeindex]=$floattemp;
// 						$charttimeindex = $charttimeindex+1;
// 						continue;
// 					}
// 				}
// 			}
// 		} /* End of if probetime is set */
// 	
// 	
// 		/*
// 		if (isset($probe_temp)) 
// 		{
// 			$probe_temp = str_replace($badzero_a, $re_neg, $probe_temp);
// 			$probe_temp = str_replace($badzero_b, $re_neg, $probe_temp);
// 			$probe_temp = ltrim($probe_temp, '+00');
// 			$probe_temp = ltrim($probe_temp, '+0');
// 		};
// 		if (isset($probe_time)) 
// 		{
// 	
// 			/* Round the probe time to the nearest minute */
// 		/*
// 			$probe_minute = round($probe_time / 60) * 60 * 1000;  
// 		*/           
// 			/* Determine how many minutes to skip */
// 		/*
// 			$bounce = $skip * 60 * 1000;
// 			$time_slice = ($probe_minute / $bounce);
// 			$int_time_slice = intval($time_slice);
// 			$timequotient = $time_slice / $int_time_slice;
// 		};
// 		if (isset($probe_minute, $probe_temp)) 
// 		{         	   
// 			$timetemp = "[".$probe_minute.", ".$probe_temp."], ";
// 			if ($probe_minute != 0 && $probe_temp != "nodata" && $timequotient == 1 && $probe_minute > $viewstop){
// 				echo $timetemp;
// 			};
// 		};
// 		*/
// 	 } # End while loop to fetch data
//  # echo "], dashStyle: 'solid'},";
//  } # End while loop to fetch each freezer

/* Define the HighChart */
// $index = 0;
// include 'assets/highcharts.array.php';       
// include "assets/gc.array.php";
/* Set up navigation for different graphs || TODO: groups table, dynamically generate || */


include "assets/url.php";
$url = curPageURL();
$baseurl = substr($url, 0, strpos($url, "?"));
/*
echo "]            
            }); 
        });
</script>
*/
echo"
<script type='text/javascript' src='js/dygraph-combined.js'></script>
</head>

<body>
";
include 'assets/navigation.php';
//echo"
//<!--Div that will hold the  chart-->
//    <div id='chart_div' style='width:400; height:300'></div>
//";

include "assets/dyg.array.php";

/* Actually draw the graph */
include "assets/graph.php";

/* Wrap things up */
include "assets/footer.php";
?>