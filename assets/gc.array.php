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
   var data = new google.visualization.arrayToDataTable([['time','1001','1002','1003','1004','1005','2001','2002','2003','3001','3002','3003','3004','9001'],[1411053181970,-80.7,-74.1,-80.6,3.3,-80.8,-60.1,-63.4,-65.8,-21.4,-18.9,-26.7,-15.6,24.4],[1411053661320,-80.7,-73.9,-80.2,4.0,-81.1,-60.2,-63.4,-65.8,-21.7,-19.2,-26.9,-13.3,24.4],[1411054141490,-80.9,-72.4,-80.2,4.4,-81.4,-60.1,-63.4,-65.9,-21.9,-19.5,-27.1,-11.8,24.4],[1411054621570,-80.9,-69.9,-80.4,3.0,-81.1,-60.1,-63.4,-65.9,-22.1,-19.7,-27.3,-10.9,24.5],[1411055101660,-81.0,-69.7,-80.6,1.4,-80.6,-60.1,-63.4,-65.8,-22.3,-20.1,-27.4,-10.1,24.5],[1411055581990,-81.1,-71.2,-80.6,1.1,-80.4,-60.1,-63.4,-65.9,-22.5,-20.3,-27.6,-9.6,24.6],[1411056061240,-80.9,-72.5,-80.1,1.9,-79.9,-60.1,-63.5,-65.9,-22.7,-20.6,-27.7,-9.1,24.6],[1411056541490,-80.1,-71.4,-80.2,2.8,-79.6,-60.2,-63.5,-65.9,-22.9,-20.8,-27.2,-8.6,24.6]]);



  // Set chart options
  var options = {'title':'Temperatures....',
	 			 'width':400,
				 'height':300};

  // Instantiate and draw our chart, passing in some options.
  var chart = new google.visualization.LineChart(document.getElementById('chart_div'));
  chart.draw(data, options);
}
</script>

"
?>