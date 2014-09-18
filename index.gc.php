<?php

/* Get things started */
include 'assets/header.php';

/* Teach PHP how to read URL parameters, plus add defaults */
include 'assets/urlvars.php';

/* Define the HighChart */
include 'assets/gc.php';
        
/* Set up navigation for different graphs || TODO: groups table, dynamically generate || */
include "assets/url.php";
$url = curPageURL();
$baseurl = substr($url, 0, strpos($url, "?"));

include 'assets/navigation.php';

/* Actually draw the graph */
include "assets/graph.gc.php";

/* Wrap things up */
include "assets/footer.php";
?>