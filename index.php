<?php

/* Get things started */
include "header.php";

/* Teach PHP how to read URL parameters, plus add defaults */
include "urlvars.php";

/* Define the HighChart */
include 'highcharts.php';

/* Start talking to MySQL and kill yourself if it ignores you */
$daenaDB = mysql_connect("localhost", "tempurify_user", "idontcareaboutpasswordsrightnow");
if ($daenaDB === FALSE) {
    die(mysql_error()); // TODO: better error handling
}
mysql_select_db("tempurify");

/* Ask MySQL how many active probes total for density adjustments */
$freezercountquery = "SELECT SQL_CALC_FOUND_ROWS * 
FROM tempurify.freezers 
WHERE freezer_active='1'";
$countfreezers = mysql_query($freezercountquery);
if($countfreezers === FALSE) {
    die(mysql_error()); // TODO: better error handling
}
/* Count the active probes for density handling */
$countquery = "SELECT FOUND_ROWS()";
        	$countraw = mysql_query($countquery);
        	$countarray = mysql_fetch_assoc($countraw);
        	$count = implode(",",$countarray);

/* Ask MySQL about which probes exist and get their metadata */
$allfreezersquery = "SELECT freezer_id,freezer_name,freezer_color,freezer_location 
FROM tempurify.freezers 
WHERE freezer_active='1'
".$groupfilter."
".$locfilter."
ORDER BY ABS(freezer_id)";
$allfreezers = mysql_query($allfreezersquery);
if($allfreezers === FALSE) {
    die(mysql_error()); // TODO: better error handling
}


/* Ask MySQL for X hours of data on each probe */
while(($freezerdata = mysql_fetch_assoc($allfreezers))){
    $freezer_id = $freezerdata['freezer_id'];
    $freezer_name = $freezerdata['freezer_name'];
    $freezer_color = $freezerdata['freezer_color'];
    $freezer_loc = $freezerdata['freezer_location'];
    $probequery = "(SELECT temp,time,ping_id FROM tempurify.data 
    WHERE freezer_id='" . $freezer_id . "'
    ORDER BY time DESC " . $viewfilter . ") ORDER BY time ASC";
	$proberesult = mysql_query($probequery);
	if($proberesult === FALSE) {
        die(mysql_error()); // TODO: better error handling
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
    while($probe = mysql_fetch_array($proberesult)) {
        extract($probe, EXTR_PREFIX_SAME, "probe");
        if (isset($probe_temp)) {
        $probe_temp = str_replace($badzero_a, $re_neg, $probe_temp);
        $probe_temp = str_replace($badzero_b, $re_neg, $probe_temp);
        $probe_temp = ltrim($probe_temp, '+00');
        $probe_temp = ltrim($probe_temp, '+0');};
        if (isset($probe_ping_id)) { 
            $slicemod = intval($probe_ping_id / $count);
            $int_time_slice = intval($time_slice);
            $timequotient = $time_slice / $int_time_slice;};
        if (isset($probe_time)) {$probe_time *= 1000;};
        if (isset($probe_time, $probe_temp)) {         	   
        $timetemp = "[".$probe_time.", ".$probe_temp."], ";
        if ($probe_time != 0 && $probe_temp != "nodata" && $timequotient == 1 && $probe_time > $viewstop){
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

/* Wrap things up */
include "footer.php";
?>