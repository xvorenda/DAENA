<?php

/* Get things started */
include "header.php";

/* Teach PHP how to read URL parameters, plus add defaults */
include "urlvars.php";

/* Define the HighChart */
include 'highcharts.php';

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


/* Ask MySQL for X hours of data on each probe */
while(($freezerdata = $allfreezers->fetch_assoc())){
    $freezer_id = $freezerdata['freezer_id'];
    $freezer_name = $freezerdata['freezer_name'];
    $freezer_color = $freezerdata['freezer_color'];
    $freezer_loc = $freezerdata['freezer_location'];
    $starttime = microtime();
    $probequery = "(SELECT temp,time FROM daena_db.data 
    WHERE freezer_id='" . $freezer_id . "'
    ORDER BY time DESC " . $viewfilter . ") ORDER BY time ASC";
	$proberesult = $daenaDB->query($probequery);
	// Check connection
if (mysqli_connect_errno())
  {
  echo "Failed to connect to MySQL: " . mysqli_connect_error();
  }
                            	
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
                    dashStyle: 'ShortDash',
                    pointInterval: ".$skip." * 60 * 1000,
                    data: [";
    /* Limit displayed points to within view window */
    if ($hours !='All') {
    $now = time() * 1000;
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
        if (isset($probe_time)) {
            $probe_minute = round($probe_time / 60) * 60 * 1000;
            $bounce = $skip * 60 * 1000;
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
include "url.php";
$url = curPageURL();
$baseurl = substr($url, 0, strpos($url, "?"));
echo "]            
            }); 
        });
</script>
</head>
<body>";
include 'navigation.php';

/* Actually draw the graph */
include "graph.php";

$endtime = microtime();
$mysqltime = $starttime - $endtime;

/* Wrap things up */
include "footer.php";
?>