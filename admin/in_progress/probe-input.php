<?php
/* Get things started */
include "header.php";
include "urlvars.php";
echo "
</head>
<body>";
$baseurl = 'index.php';
include 'admin-nav.php';
$probe_freezer_name = $_POST["probe_freezer_name"];
$probe_location_building = $_POST["probe_location_building"];
$probe_location_room = $_POST["probe_location_room"];
$probe_temp_range = $_POST["probe_temp_range"];
$probe_host = $_POST["probe_host"];
$probe_port = $_POST["probe_port"];
$probe_active = $_POST["probe_active"];
if ($probe_active != '1') {
    $probe_active = '0';
}
$probe_color = $_POST["probe_color"];
$freezer_id = $_POST["freezer_id"];
$probe_group = substr("$freezer_id", 0, 1);
$probe_hostport = $probe_host." ".$probe_port; 
$probe_location = $probe_location_building."<br>".$probe_location_room; 

$daena = mysql_connect("localhost", "tempurify_user", "idontcareaboutpasswordsrightnow");
		if (!$daena)
			{die("Cannot connect: " . mysql_error());}; 
      	
    mysql_select_db("tempurify");
        
$order = "INSERT INTO tempurify.probes (probe_freezer_name, probe_location, probe_temp_range, probe_hostport, probe_active, probe_color, freezer_id, probe_group) VALUES('$probe_freezer_name', '$probe_location', '$probe_temp_range', '$probe_hostport', '$probe_active', '$probe_color', '$freezer_id', '$probe_group')";
 
$result = mysql_query($order); 
if(!$result){
    echo("<div class='content'><h3>Input Failed!</h3>".mysql_error());
} 
else { 
     echo("<div class='content'>
<div class='probeinfo'>
<table>
<h3>Probe Input Succeeded</h3>
		        <tr>
		            <td>Probe Freezer Name:</td><td width='24'></td><td>".$probe_freezer_name."</td>
		        </tr>
		        <tr>
		            <td>Building:</td><td width='24'></td><td>".$probe_location_building."</td>
		        </tr>
		        <tr>
		            <td>Room:</td><td width='24'></td><td>".$probe_location_room."</td>
		        </tr>
		        <tr>
		            <td>Temperature Range:</td><td></td><td>".$probe_temp_range."</td>
		        </tr>
		        <tr>
		            <td>NTMS Host:</td><td></td><td>".$probe_host."</td>
		        </tr>
		        <tr>
		            <td>NTMS Port:</td><td></td><td>".$probe_port."</td>
		        </tr>
		        <tr>
		            <td>Active:</td><td></td><td>".$probe_active."</td>
		        </tr>
		        <tr>
		            <td>Graph Color:</td><td></td><td class='color'>".$probe_color."</td>
		        </tr>
		 		<tr>
		            <td>Probe ID:</td><td></td><td>".$freezer_id."</td>
		        </tr>
				</table>
			</div>
		</div>");
}

/* Wrap things up */
include "footer.php";
?>

