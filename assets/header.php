<?PHP $starttime = microtime();?>
<!DOCTYPE html>
<html>
<head>
<script type="text/javascript">
before = (new Date()).getTime();
</script>
<script type="text/javascript">
	function pageload()
	{
		var after = (new Date()).getTime();
		var sec = (after-before)/1000;
		var div = document.getElementById("loadingtime");
		div.innerHTML = "Javascript page load time: " + sec + " seconds.";
		
	}
</script>
<title>DAENA | Data Aggregation and Emergency Notifications for Appliances</title>
<link rel='shortcut icon' href='images/daena.png'/>
<meta charset='utf-8'>
<meta http-equiv='X-UA-Compatible' content='IE=edge'>
<meta name='viewport' content='width=device-width, initial-scale=1'>
<link href='css/bootstrap.css' rel='stylesheet'>
<link href='css/bootstrap.daena.css' rel='stylesheet'>
<script type='text/javascript' src='https://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js'></script>
<script src='js/bootstrap.min.js'></script>
