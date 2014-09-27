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


echo "
<div id='container'></div>
<div id='labels'></div>";
print_r($columnnames);
?>
