<?php
/* Get things started */
include "assets/admin-header.php";
include 'assets/admin-nav.php';

if ($login->isUserLoggedIn() == true)
{

	/* Start talking to MySQL and kill yourself if it ignores you */
	//include 'config/db.php';
	$daenaDB = new mysqli(DB_HOST,DB_USER,DB_PASS,DB_NAME);
	// Check connection
	if (mysqli_connect_errno())
	  {
	  echo "Failed to connect to MySQL: " . mysqli_connect_error();
	  }

	/* Ask MySQL about which freeers exist and get their metadata */
	$allfreezersquery = "SELECT SQL_CALC_FOUND_ROWS *
		FROM daena_db.freezers WHERE freezer_active = 1
		ORDER BY ABS(freezer_id)";
	$allfreezers = $daenaDB->query($allfreezersquery);


	/* Draw Alarm Mod Area */
	echo "
	<div class='alarmbox table-responsive'>
	<table class='table'>
	<tr>
		<td>Freezer ID</td>
		<td>Freezer Name</td>
		<td>Alarm Level</td>
		<td>Alarm Time</td>
		<td>Last Temp</td>
		<td>Last Reading</td>
		<td>Silence Alarm</td>
		<td>Setpoint High Temp</td>
		<td>Setpoint Critical Temp</td>
		<td>Send Alarm</td>
		<td>&nbsp;</td>
	</tr>
	";
	while(($freezerdata = $allfreezers->fetch_assoc()))
	{
		$freezer_id = $freezerdata['freezer_id'];
		$freezer_name = $freezerdata['freezer_name'];
		$freezer_setpoint1 = $freezerdata['freezer_setpoint1'];
		$freezer_setpoint2 = $freezerdata['freezer_setpoint2'];
		$freezer_alarm_id = $freezerdata['freezer_alarm_id'];
		$freezer_send_alarm = $freezerdata['freezer_send_alarm'];
		
		if ($freezer_send_alarm == 0)
		{
			$freezer_send_alarm_checkbox = "unchecked";
		}
		else
		{
			$freezer_send_alarm_checkbox = "checked";
		}
		
		$alarm_query = "SELECT alarm_level, alarm_time FROM daena_db.alarm
			WHERE alarm_id='".$freezer_alarm_id."'";
		$alarmdata = $daenaDB->query($alarm_query);
		while($alarmrow = $alarmdata->fetch_assoc())
		{
			$alarm_level = $alarmrow['alarm_level'];
			$ms_epoch_time = $alarmrow['alarm_time'];
		};

		$epoch_time = round($ms_epoch_time/1000);
		$dt = new DateTime("@$epoch_time");
		$alarm_date_time = $dt->format('Y-m-d H:i:s');

		if ($alarm_level == 0)
		{
			$row_color = "success";
		}
		elseif($alarm_level==1 || $alarm_level==2 || $alarm_level==5)
		{
			$row_color="warning";
		}
		elseif($alarm_level==3 || $alarm_level==4)
		{
			$row_color="danger";
		}
		elseif($alarm_level==6 || $alarm_level==7)
		{
			$row_color="info";
		}

		$lasttempquery = "SELECT temp FROM daena_db.data
			WHERE freezer_id='".$freezer_id."'
			ORDER BY int_time DESC
			LIMIT 1";

		$lasttempdata = $daenaDB->query($lasttempquery);
		while($lasttemprow = $lasttempdata->fetch_assoc())
		{
			$last_reading = $lasttemprow['temp'];
		};

		$lasttempquery = "SELECT temp FROM daena_db.data
			WHERE freezer_id='".$freezer_id."' AND
			temp not REGEXP('nodata')
			ORDER BY int_time DESC
			LIMIT 1";

		$lasttempdata = $daenaDB->query($lasttempquery);
		while($lasttemprow = $lasttempdata->fetch_assoc())
		{
			$last_temp = $lasttemprow['temp'];
		};

		echo "<tr class='alarm-table-row'>
				<form action='handlers/alarm-mod.php' method='POST'>
					<td class='".$row_color." round-first'><input type='text' class='stealth' name='freezer_id' value='".$freezer_id."'/>".$freezer_id."</td>
					<td class='".$row_color."'>".$freezer_name."</td>
					<td class='".$row_color." field-narrow'>".$alarm_level."</td>
					<td class='".$row_color." field-wide'>".$alarm_date_time."</td>
					<td class='".$row_color."'>".$last_temp."</td>
					<td class='".$row_color." '>".$last_reading."</td>";
					if ($alarm_level==3 || $alarm_level==6)
					{
						echo"
					<input type='text' class='stealth' name='freezer_id' value='".$freezer_id."'/>
					<input type='text' class='stealth' name='alarm_level' value='".$alarm_level."'/>
					<td class='".$row_color." '>
						<input type='text' class='stealth' name='mysqlaction' value='silence'/>
						<input type='submit' name='silence' class='btn btn-danger' value='Silence'/>
					</td>";
					}
					else
					{
						echo"
					<td class='".$row_color." round-last'></td>";
					}
					echo"
					<td><input type='text' class='input-medium search-query' name='freezer_setpoint1' value='".$freezer_setpoint1."'/></td>
					<td><input type='text' class='input-medium search-query' name='freezer_setpoint2' value='".$freezer_setpoint2."'/></td>
					<td class='field-narrow'><input type='checkbox' class='input-medium' name='freezer_send_alarm' ".$freezer_send_alarm_checkbox." value='1'/></td>
					<td>
						<input type='text' class='stealth' name='mysqlaction' value='modify'/>
						<input type='submit' name='submit' class='btn' value='Modify'/>
					</td>
				</form>
			</tr>";
	}

	echo "
	  </table>
	</div>
</div>";
}
else
{
	echo "<div id='content'>"
		. "<h1>Unauthorized Access</h1>"
		. "<h3>Please <a href='index.php'>log in</a> to access this page.</h3>"
		. "</div>";
}
/* Wrap things up */
include 'assets/admin-footer.php';
?>
