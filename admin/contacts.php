<?php
/* Get things started */
include "assets/admin-header.php";
include 'assets/admin-nav.php';

if ($login->isUserLoggedIn() == true) 
{

	/* Start talking to MySQL and kill yourself if it ignores you */
	include 'config/db.php';
	$daenaDB = new mysqli(DB_HOST,DB_USER,DB_PASS,DB_NAME);
	// Check connection
	if ($daenaDB->connect_errno) 
	{
		printf("Connect failed: %s\n", $daenaDB->connect_error);
		exit();
	}


	/* Ask MySQL about which probes exist and get their metadata */
	$allcontactsquery = "SELECT SQL_CALC_FOUND_ROWS *
	FROM daena_db.contacts
	ORDER BY ABS(contact_id)";
	$allcontacts = $daenaDB->query($allcontactsquery);


	/* Draw Freezer Mod Area */
	/*
	echo "
	<div class='contactsbox'>
	<table class='table'>
	<tr><td>Contact ID</td><td>Name</td><td>Email</td><td>Alt Email</td><td>&nbsp;</td></tr>
	";
	*/
	while(($contactdata = $allcontacts->fetch_assoc()))
	{
		$contact_name = $contactdata['name'];
		$contact_id = $contactdata['contact_id'];
		$contact_email = $contactdata['email'];
		$contact_alt_email = $contactdata['alt_email'];

		echo"
			<div class='panel-group' >
				<div class='panel panel-default'>
					<div class='panel-heading'>
						<h2 class='panel-title'>
							<a data-toggle='collapse'  href='#".$contact_id."'>
							".$contact_name."
							</a>
						</h2>
					</div>
					<div id='".$contact_id."' class='panel-collapse collapse'>
						<div class='panel-body'>
							<table class='table'>
								<tr><td>Contact ID</td><td>Name</td><td>Email</td><td>Alt Email</td><td>&nbsp;</td></tr>
								<tr>
									<form action='handlers/contact-mod.php' method='POST'>
									<td><input type='text' class='input-medium search-query' name='contact_id' value='".$contact_id."'/></td>
									<td><input type='text' class='input-medium search-query' name='contact_name' value='".$contact_name."'/></td>
									<td><input type='text' class='input-wide search-query' name='contact_email' value='".$contact_email."'/></td>
									<td><input type='text' class='input-wide search-query' name='contact_alt_email' value='".$contact_alt_email."'/></td>
									<td><input type='text' class='stealth' name='mysqlaction' value='modify'/><input type='submit' name='submit' class='btn' value='Modify'/></td></form>
								</tr>
							</table>
							<h3> Select freezers and alarm levels for ".$contact_name." to get notifications. </h3>
							<table class='table'>
								<tr>
									<td>Freezer Name</td>
									<td>Freezer ID</td>
									<td>Normal State</td>
									<td>High Alarm 1</td>
									<td>High Alarm 2</td>
									<td>Critical Alarm</td>
									<td>Silenced Critical Alarm</td>
									<td>Critical to High Alarm</td>
									<td>Com Alarm</td>
									<td>Silenced Com Alarm</td>
									<td>&nbsp;</td>
								</tr>";
								
							
							$freezeralarmquery = "SELECT freezers.freezer_name, freezer_alarm_contacts.* 
								FROM freezer_alarm_contacts, freezers 
								WHERE contact_id = ".$contact_id." 
									AND freezers.freezer_id = freezer_alarm_contacts.freezer_id 
									AND freezers.freezer_active = 1 
								ORDER BY freezers.freezer_id";
							while(($alarmdata = $freezeralarmquery->fetch_assoc()))
							{
								$freezer_name = $alarmdata['freezer_name'];
								$freezer_id = $alarmdata['freezer_id'];
								if($alarmdata['alarm0']==0){$alarm0 = "checked";}else{$alarm0="unchecked";}
								if($alarmdata['alarm1']==0){$alarm1 = "checked";}else{$alarm1="unchecked";}
								if($alarmdata['alarm2']==0){$alarm2 = "checked";}else{$alarm2="unchecked";}
								if($alarmdata['alarm3']==0){$alarm3 = "checked";}else{$alarm3="unchecked";}
								if($alarmdata['alarm4']==0){$alarm4 = "checked";}else{$alarm4="unchecked";}
								if($alarmdata['alarm5']==0){$alarm5 = "checked";}else{$alarm5="unchecked";}
								if($alarmdata['alarm6']==0){$alarm6 = "checked";}else{$alarm6="unchecked";}
								if($alarmdata['alarm7']==0){$alarm7 = "checked";}else{$alarm7="unchecked";}
								
								echo"
								<tr class='alarm-table-row'>
									<form action='handlers/contacts-mod.php' method='POST'>
									<td>".$freezer_name."</td>
									<td>".$freezer_id."</td>
									<td class='field-narrow'><input type='checkbox' class='input-medium' name='alarm0' ".$alarm0." value='1'/></td>
									<td class='field-narrow'><input type='checkbox' class='input-medium' name='alarm1' ".$alarm1." value='1'/></td>
									<td class='field-narrow'><input type='checkbox' class='input-medium' name='alarm2' ".$alarm2." value='1'/></td>
									<td class='field-narrow'><input type='checkbox' class='input-medium' name='alarm3' ".$alarm3." value='1'/></td>
									<td class='field-narrow'><input type='checkbox' class='input-medium' name='alarm4' ".$alarm4." value='1'/></td>
									<td class='field-narrow'><input type='checkbox' class='input-medium' name='alarm5' ".$alarm5." value='1'/></td>
									<td class='field-narrow'><input type='checkbox' class='input-medium' name='alarm6' ".$alarm6." value='1'/></td>
									<td class='field-narrow'><input type='checkbox' class='input-medium' name='alarm7' ".$alarm7." value='1'/></td>
								</tr>
								";
								
							}
							echo "
							</table>
						</div>
					</div>
				</div>
			</div>
		";
	}
		/*
		echo "<tr>
				<form action='handlers/contact-mod.php' method='POST'>
				<td><input type='text' class='input-medium search-query' name='contact_id' value='".$contact_id."'/></td>
				<td><input type='text' class='input-medium search-query' name='contact_name' value='".$contact_name."'/></td>
				<td><input type='text' class='input-wide search-query' name='contact_email' value='".$contact_email."'/></td>
				<td><input type='text' class='input-wide search-query' name='contact_alt_email' value='".$contact_alt_email."'/></td>
				<td><input type='text' class='stealth' name='mysqlaction' value='modify'/><input type='submit' name='submit' class='btn' value='Modify'/></td></form>
			</tr>";
	};

	echo "<tr>
			<form action='handlers/contact-mod.php' method='POST'>
			<td><input type='text' class='input-medium search-query' name='contact_id'/></td>
			<td><input type='text' class='input-medium search-query' name='contact_name' value='New Contact'/></td>
			<td><input type='text' class='input-wide search-query' name='contact_email'/></td>
			<td><input type='text' class='input-wide search-query' name='contact_alt_email'/></td>
			<td><input type='text' class='stealth' name='mysqlaction' value='add'/><input type='submit' name='submit' class='btn' value='Add'/></form></td>
		</tr>
	</table>
	</div></div>";
	*/
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
