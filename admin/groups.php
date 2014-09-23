<?php
/* Get things started */
include "assets/admin-header.php";
include 'assets/admin-nav.php';

if ($login->isUserLoggedIn() == true) {

/* Start talking to MySQL and kill yourself if it ignores you */
include 'config/db.php';
$daenaDB = new mysqli(DB_HOST,DB_USER,DB_PASS,DB_NAME);
// Check connection
if ($daenaDB->connect_errno) {
    printf("Connect failed: %s\n", $daenaDB->connect_error);
    exit();
}


/* Ask MySQL about which probes exist and get their metadata */
$allgroupsquery = "SELECT SQL_CALC_FOUND_ROWS *
FROM daena_db.groups
ORDER BY ABS(group_id)";
$allgroups = $daenaDB->query($allgroupsquery);


/* Draw Freezer Mod Area */
echo "
<div class='groupsbox'>
<table class='table'>
<tr><td>Group ID</td><td>Group Name</td><td class='td-wide'>Group Description</td><td>&nbsp;</td></tr>
";
while(($groupdata = $allgroups->fetch_assoc())){
    $group_name = $groupdata['group_name'];
    $group_id = $groupdata['group_id'];
    $group_desc = $groupdata['group_desc'];

echo "<tr>
        <form action='handlers/group-mod.php' method='POST'>
        <td><input type='text' class='input-medium search-query' name='group_id' value='".$group_id."'/></td>
        <td><input type='text' class='input-medium search-query' name='group_name' value='".$group_name."'/></td>
        <td><input type='text' class='input-wide search-query' name='group_desc' value='".$group_desc."'/></td>
        <td><input type='text' class='stealth' name='mysqlaction' value='modify'/><input type='submit' name='submit' class='btn' value='Modify'/></td></form>
    </tr>";};

echo "<tr>
        <form action='handlers/group-mod.php' method='POST'>
        <td><input type='text' class='input-medium search-query' name='group_id'/></td>
        <td><input type='text' class='input-medium search-query' name='group_name' value='New Group'/></td>
        <td><input type='text' class='input-wide search-query' name='group_desc'/></td>
        <td><input type='text' class='stealth' name='mysqlaction' value='add'/><input type='submit' name='submit' class='btn' value='Add'/></form></td>
    </tr>
</table>
</div></div>";
}else {
echo "<div id='content'>"
    . "<h1>Unauthorized Access</h1>"
    . "<h3>Please <a href='index.php'>log in</a> to access this page.</h3>"
    . "</div>";
}
/* Wrap things up */
include 'assets/admin-footer.php';
?>
