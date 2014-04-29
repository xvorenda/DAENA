<?php
/* Get things started */
include 'header.php';
echo "
</head>
<body>";
$baseurl = 'index.php';
include 'admin-nav.php';
echo "	<div class='content'>
        <div class='probeinfo'>
		<form action='probe-input.php' method='POST'>
		    <table>
		        <tr>
		            <td>Freezer Name:</td>
		            <td><input type='text' class='input-medium search-query' name='freezer_name'/></td>
		        </tr>
		        <tr>
		            <td>Building:</td>
		            <td><input type='text' class='input-medium search-query' name='freezer_location_building'/></td>
		        </tr>
		        <tr>
		            <td>Room:</td>
		            <td><input type='text' class='input-medium search-query' name='freezer_location_room'/></td>
		        </tr>
		        <tr>
		            <td>Temp Range:</td>
		            <td><input type='text' class='input-medium search-query' name='freezer_temp_range'/></td>
		        </tr>
		        <tr>
		            <td>NTMS Host:</td>
		            <td><input type='text' class='input-medium search-query' name='probe_host'/></td>
		        </tr>
		        <tr>
		            <td>NTMS Port:</td>
		            <td><input type='text' class='input-medium search-query' name='probe_port'/></td>
		        </tr>
		        <tr>
		            <td>Active:</td>
		            <td><input type='checkbox' class='input-medium search-query' name='probe_active' value='1' checked /></td>
		        </tr>
		        <tr>
		            <td>Graph Color:</td>
		            <td><input type='text' class='input-medium search-query color' name='freezer_color'/></td>
		        </tr>
		 		<tr>
		            <td>Probe ID:</td>
		            <td><input type='text' class='input-medium search-query' name='freezer_id'/></td>
		        </tr>
				</table>
                <center>
                    <input type='submit' name='submit' class='btn' value='Submit'>
                </center>
        </form>
    </div>  
</div>";
    
/* Wrap things up */
include 'footer.php';
?>

