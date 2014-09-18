<?php
echo"
<script type='text/javascript' src='https://www.google.com/jsapi'></script>
<script type='text/javascript'>

  // Load the Visualization API and the piechart package.
  google.load('visualization', '1.0', {'packages':['corechart']});
  
  // Set a callback to run when the Google Visualization API is loaded.
  google.setOnLoadCallback(drawChart);


  // Callback that creates and populates a data table, 
  // instantiates the pie chart, passes in the data and
  // draws it.
  function drawChart() {

  // Create the data table.
  var data = new google.visualization.arrayToDataTable(".json_encode($json_chart).");



  // Set chart options
//  var options = {'title':'How Much Pizza I Ate Last Night',
//				 'width':400,
//				 'height':300};

  // Instantiate and draw our chart, passing in some options.
  var chart = new google.visualization.LineChart(document.getElementById('chart_div'));
  chart.draw(data, options);
}
</script>
</head>
"
?>