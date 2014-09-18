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
  var data = new google.visualization.DataTable(['.2','9001':'24.4'},{'time':'1411051621870','1001':'-79.6','1002':'-76.2','1003':'-80.2','1004':'.9','1005':'-80.9','2001':'-60.2','2002':'-63.4','2003':'-65.8','3001':'-20.4','3002':'-17.7','3003':'-25.6','3004']);



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