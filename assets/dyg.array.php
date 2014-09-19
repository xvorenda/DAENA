<?php
echo"
<script  type='text/javascript'>
  new Dygraph(document.getElementById('graph'),
			".json_encode($json_chart)."
              );
</script>
<div id=graph></div>

"
?>