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
include 'assets/graph.dyg.php';

/* Wrap things up */
include 'assets/footer.php';
?>
