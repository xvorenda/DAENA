<?php
/* Get things started */
include "assets/admin-header.php";
include 'assets/admin-nav.php';


/* Start talking to MySQL and kill yourself if it ignores you */
$daenaDB = mysql_connect("localhost", "daena_user", "idontcareaboutpasswordsrightnow");
if ($daenaDB === FALSE) {
    die(mysql_error()); // TODO: better error handling
}
mysql_select_db("daena_db");


/* Ask MySQL about which probes exist and get their metadata */
$allprobesquery = "SELECT SQL_CALC_FOUND_ROWS *
FROM daena_db.probes 
ORDER BY ABS(probe_id)";
$allprobes = mysql_query($allprobesquery);
if($allprobes === FALSE) {
    die(mysql_error()); // TODO: better error handling
    
/* Count the active probes for density handling */
$countquery = "SELECT FOUND_ROWS()";
	$countraw = mysql_query($countquery);
	$countarray = mysql_fetch_assoc($countraw);
	$count = implode(",",$countarray);
}
/* Ask MySQL about which freeers exist and get their metadata */
$allfreezersquery = "SELECT SQL_CALC_FOUND_ROWS *
FROM daena_db.freezers 
ORDER BY ABS(freezer_id)";
$allfreezers = mysql_query($allfreezersquery);
if($allfreezers === FALSE) {
    die(mysql_error()); // TODO: better error handling
}
/* Count the active freezers for density handling */
$countquery = "SELECT FOUND_ROWS()";
	$countraw = mysql_query($countquery);
	$countarray = mysql_fetch_assoc($countraw);
	$count = implode(",",$countarray);
$i = 0;

/* Draw Probe Mod Area */
echo "
<div class='probesbox'>
<table>
<tr><td>Probe ID</td><td>Probe Type</td><td>Probe Range</td><td>Active</td><td>Probe Hostport</td><td>Probe NTMS Port</td><td>&nbsp;</td></tr>
";
while(($probedata = mysql_fetch_assoc($allprobes))){
    $probe_id = $probedata['probe_id'];
    $probe_type = $probedata['probe_type'];
    $probe_range = $probedata['probe_range'];
    $probe_active = $probedata['probe_active'];
    $probe_ntms_port = $probedata['probe_ntms_port'];
    $probe_hostport = $probedata['probe_hostport'];

echo "<tr>
        <form action='probe-mod.php' method='POST'>
        <td><input type='text' class='input-medium search-query field-narrow' name='probe_id' value='".$probe_id."'/></td>
        <td><input type='text' class='input-medium search-query' name='probe_type' value='".$probe_type."'/></td>
        <td><input type='text' class='input-medium search-query' name='probe_range' value='".$probe_range."'/></td>
        <td class='field-narrow'><input type='text' class='input-medium search-query field-narrow' name='probe_active' value='".$probe_active."'/></td>
        <td class='field-wide'><input type='text' class='input-medium search-query field-wide' name='probe_hostport' value='".$probe_hostport."'/></td>
        <td class='field-narrow'><input type='text' class='input-medium search-query field-narrow' name='probe_ntms_port' value='".$probe_ntms_port."'/></td>
        <td><input type='text' class='stealth' name='mysqlaction' value='modify'/><input type='submit' name='submit' class='btn' value='Modify'/></td></form>
</tr>";}

echo "<tr>
        <form action='probe-mod.php' method='POST'>
        <td><input type='text' class='input-medium search-query field-narrow' name='probe_id'/></td>
        <td><input type='text' class='input-medium search-query' name='probe_type'/></td>
        <td><input type='text' class='input-medium search-query' name='probe_range'/></td>
        <td class='field-narrow'><input type='text' class='input-medium search-query field-narrow' name='probe_active'/></td>
        <td class='field-wide'><input type='text' class='input-medium search-query field-wide' name='probe_hostport'/></td>
        <td class='field-narrow'><input type='text' class='input-medium search-query field-narrow' name='probe_ntms_port'/></td>
        <td><input type='text' class='stealth' name='mysqlaction' value='add'/><input type='submit' name='submit' class='btn' value='Add'/></td></form>
    </tr>
</table></div></div>";	
   

/* Wrap things up */
include 'assets/admin-footer.php';
?>
	    
