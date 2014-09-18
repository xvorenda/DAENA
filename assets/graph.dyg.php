<?PHP

/* Start talking to MySQL and kill yourself if it ignores you */
include 'admin/config/db.php';
$daenaDB = new mysqli(DB_HOST,DB_USER,DB_PASS,DB_NAME);
// Check connection
if (mysqli_connect_errno())
  {
  echo "Failed to connect to MySQL: " . mysqli_connect_error();
  }


/* Ask MySQL which freezers are active */
$freezerquery = "SELECT freezer_id,freezer_name,freezer_color,freezer_location 
FROM daena_db.freezers 
WHERE freezer_active='1'
".$groupfilter."
".$locfilter."
".$typefilter."
ORDER BY ABS(freezer_id)";

$columnnames = array();
array_push($columnnames,"Time");
$freezers = $daenaDB->query($freezerquery);
while ($freezerrow = $freezers->fetch_assoc()) {
    $freezername = $freezerrow["freezer_id"];
    array_push($columnnames,$freezername);
}
$columnheader = implode ("', '",$columnnames);

echo "<div id='container'></div>
<script type='text/javascript'>
  g = new Dygraph(

    // containing div
    document.getElementById('container'),";

        
/* Ask MySQL for some number of minutes worth of ping data */
$pingquery = "(SELECT DISTINCT int_time FROM daena_db.data 
    ORDER BY int_time DESC " . $viewfilter . ") ORDER BY int_time ASC";

$pings = $daenaDB->query($pingquery);


$badneg_a = "-00";
$badneg_b = "-0";
$re_neg = "-";

while ($pingrow = $pings->fetch_assoc()) {
      $pingtime = $pingrow["int_time"];
      $dataquery = "
          SELECT temp
          FROM daena_db.data
          WHERE int_time = ".$pingtime."
          ORDER BY freezer_id";
      
      
      
      echo "['".$pingtime."'";
      $data = $daenaDB->query($dataquery);
      $freezercount = count($freezers);
      $datacount = count($data);
      
      
      if ($datacount == $freezercount){
      while ($datarow = $data->fetch_assoc()) {
          $datatemp = $datarow["temp"];
          $datatemp = str_replace($badneg_a, $re_neg, $datatemp);
          $datatemp = str_replace($badneg_b, $re_neg, $datatemp);
          $datatemp = ltrim($datatemp, '+00');
          $datatemp = ltrim($datatemp, '+0');
          if ($datatemp == "nodata"){
          $datatemp = "null";}
          echo ", ".$datatemp;
          

      }
      echo "],\n";
}}
echo "],
              {
                labels: ['".$columnheader."']
              });
</script>
</body>
</html>";
?>