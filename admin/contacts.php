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
						<div class='panel-body table-responsive'>
							<table class='table'>
								<tr><td>Contact ID</td><td>Name</td><td>Email</td><td>Alt Email</td><td>&nbsp;</td></tr>
								<tr>
									<form action='handlers/contact-info-mod.php' method='POST'>
										<td><input type='text' class='stealth' name='contact_id' value='".$contact_id."'/>".$contact_id."</td>
										<td><input type='text' class='input-large search-query' name='contact_name' value='".$contact_name."'/></td>
										<td><input type='text' class='input-large search-query' name='contact_email' value='".$contact_email."'/></td>
										<td><input type='text' class='input-large search-query' name='contact_alt_email' value='".$contact_alt_email."'/></td>
										<td><input type='text' class='stealth' name='mysqlaction' value='modify'/>
										<input type='submit' name='update_contact' class='btn' value='Modify'/></td>
									</form>
								</tr>
							</table>
							<h3> Select freezers and alarm levels for ".$contact_name." to get notifications. </h3>
							<form action='handlers/contact-alarm-mod.php' method='POST'>
								<table class='table table-striped table-hover'>
									<tr>
										<td class='field-medium'>Freezer Name</td>
										<td class='field-medium'>Freezer ID</td>
										<td class='field-medium'>Normal State</td>
										<td class='field-medium'>High Alarm 1</td>
										<td class='field-medium'>High Alarm 2</td>
										<td class='field-medium'>Critical Alarm</td>
										<td class='field-medium'>Silenced Critical Alarm</td>
										<td class='field-medium'>Critical to High Alarm</td>
										<td class='field-medium'>Com Alarm</td>
										<td class='field-medium'>Silenced Com Alarm</td>
									</tr>";
							
						
								$freezeralarmquery = "SELECT freezers.freezer_name, freezer_alarm_contacts.* 
									FROM freezer_alarm_contacts, freezers 
									WHERE contact_id = ".$contact_id." 
										AND freezers.freezer_id = freezer_alarm_contacts.freezer_id 
										AND freezers.freezer_active = 1 
									ORDER BY freezers.freezer_id";
								$allalarmdata = $daenaDB->query($freezeralarmquery);
								while(($alarmdata = $allalarmdata->fetch_assoc()))
								{
									$freezer_name = $alarmdata['freezer_name'];
									$freezer_id = $alarmdata['freezer_id'];
									if($alarmdata['alarm0']==0){$alarm0 = "unchecked";}else{$alarm0="checked";}
									if($alarmdata['alarm1']==0){$alarm1 = "unchecked";}else{$alarm1="checked";}
									if($alarmdata['alarm2']==0){$alarm2 = "unchecked";}else{$alarm2="checked";}
									if($alarmdata['alarm3']==0){$alarm3 = "unchecked";}else{$alarm3="checked";}
									if($alarmdata['alarm4']==0){$alarm4 = "unchecked";}else{$alarm4="checked";}
									if($alarmdata['alarm5']==0){$alarm5 = "unchecked";}else{$alarm5="checked";}
									if($alarmdata['alarm6']==0){$alarm6 = "unchecked";}else{$alarm6="checked";}
									if($alarmdata['alarm7']==0){$alarm7 = "unchecked";}else{$alarm7="checked";}
							
									echo"
									<tr class='alarm-table-row'>
										<input type='text' class='stealth' name='contact_id' value='".$contact_id."'/>
										<td>".$freezer_name."</td>
										<td><input type='text' class='stealth' name='freezer_id[]' value='".$freezer_id."'/>".$freezer_id."</td>
										<td class='success'><input type='checkbox'  name='".$freezer_id."alarm0' ".$alarm0." value='1'/></td>
										<td class='warning'><input type='checkbox'  name='".$freezer_id."alarm1' ".$alarm1." value='1'/></td>
										<td class='warning'><input type='checkbox'  name='".$freezer_id."alarm2' ".$alarm2." value='1'/></td>
										<td class='danger'><input type='checkbox'  name='".$freezer_id."alarm3' ".$alarm3." value='1'/></td>
										<td class='danger'><input type='checkbox'  name='".$freezer_id."alarm4' ".$alarm4." value='1'/></td>
										<td class='warning'><input type='checkbox'  name='".$freezer_id."alarm5' ".$alarm5." value='1'/></td>
										<td class='info'><input type='checkbox'  name='".$freezer_id."alarm6' ".$alarm6." value='1'/></td>
										<td class='info'><input type='checkbox'  name='".$freezer_id."alarm7' ".$alarm7." value='1'/></td>
									</tr>
									";
							
								}
								echo "
								</table>
								<input type='text' class='stealth' name='mysqlaction' value='modify'/>
								<input type='submit' name='update_alarm' class='btn' value='Modify'/>
							</form>
						</div>
					</div>
				</div>
			</div> 
		";
	} // end while loop
	echo"
			<div class='panel-group' >
				<div class='panel panel-default'>
					<div class='panel-heading'>
						<h2 class='panel-title'>
							<a data-toggle='collapse'  href='#newcontact'>
								Add Contact
							</a>
						</h2>
					</div>
					<div id='newcontact' class='panel-collapse collapse'>
						<div class='panel-body table-responsive'>
							<form action='handlers/contact-add-mod.php' method='POST'>
								<table class='table'>
									<tr><td>Name</td><td>Email</td><td>Alt Email</td></tr>
									<tr>
											<td><input type='text' class='input-large search-query' name='contact_name' /></td>
											<td><input type='text' class='input-large search-query' name='contact_email' /></td>
											<td><input type='text' class='input-large search-query' name='contact_alt_email' /></td>
									</tr>
								</table>
								<h3> Select freezers and alarm levels for the new person to get notifications. </h3>
								<table class='table table-striped table-hover'>
									<tr>
										<td class='field-medium'>Freezer Name</td>
										<td class='field-medium'>Freezer ID</td>
										<td class='field-medium'>Normal State</td>
										<td class='field-medium'>High Alarm 1</td>
										<td class='field-medium'>High Alarm 2</td>
										<td class='field-medium'>Critical Alarm</td>
										<td class='field-medium'>Silenced Critical Alarm</td>
										<td class='field-medium'>Critical to High Alarm</td>
										<td class='field-medium'>Com Alarm</td>
										<td class='field-medium'>Silenced Com Alarm</td>
									</tr>";
						
					
								$freezeralarmquery = "SELECT freezers.freezer_name, freezers.freezer_id 
									FROM freezers 
									WHERE freezers.freezer_active = 1 
									ORDER BY freezers.freezer_id";
								$allfreezerdata = $daenaDB->query($freezeralarmquery);
								while(($freezerdata = $allfreezerdata->fetch_assoc()))
								{
									$freezer_name = $freezerdata['freezer_name'];
									$freezer_id = $freezerdata['freezer_id'];

									echo"
									<tr class='alarm-table-row'>
										<td>".$freezer_name."</td>
										<td><input type='text' class='stealth' name='freezer_id[]' value='".$freezer_id."'/>".$freezer_id."</td>
										<td class='success'><input type='checkbox'  name='".$freezer_id."alarm0'  value='1'/></td>
										<td class='warning'><input type='checkbox'  name='".$freezer_id."alarm1'  value='1'/></td>
										<td class='warning'><input type='checkbox'  name='".$freezer_id."alarm2'  value='1'/></td>
										<td class='danger'><input type='checkbox'  name='".$freezer_id."alarm3'  value='1'/></td>
										<td class='danger'><input type='checkbox'  name='".$freezer_id."alarm4'  value='1'/></td>
										<td class='warning'><input type='checkbox'  name='".$freezer_id."alarm5'  value='1'/></td>
										<td class='info'><input type='checkbox'  name='".$freezer_id."alarm6'  value='1'/></td>
										<td class='info'><input type='checkbox'  name='".$freezer_id."alarm7'  value='1'/></td>
									</tr>
									";
						
								}
								echo "
								</table>
								<input type='text' class='stealth' name='mysqlaction' value='add'/>
								<input type='submit' name='add_contact' class='btn' value='Add Contact'/></td>
							</form>
						</div>
					</div>
				</div>
			</div> 
		";
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
