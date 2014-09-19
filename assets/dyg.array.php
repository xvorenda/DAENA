<?php
echo"
<div id=graph></div>
<script  type='text/javascript'>
  new Dygraph(document.getElementById('graph'),
			".json_encode($json_chart)."
              );
</script>


"
?>