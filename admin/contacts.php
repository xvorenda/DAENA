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
