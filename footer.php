 <script type="text/javascript">
function pageload()
{
    var after = (new Date()).getTime();
    var sec = (after-before)/1000;
    var p = document.getElementById("loadingtime");
    p.innerHTML = "Page load: " + sec + " seconds.";
        
}
</script>
<script type="text/javascript">
    window.onload = function () 
    { 
        pageload();
    }
</script>
    <div class='navbar-fixed-bottom hidden-sm hidden-xs'>
        <p class='navbar-fixed-bottom-p'>Written for 
        <a href='http://www.vcu.edu/' target='_blank'>VCU</a> by 
        <a href='http://www.people.vcu.edu/~xvorenda/' target='_blank'>XVO</a> and 
	<a href='http://www.people.vcu.edu/~voegtlylj/' target='_blank'>LJV</a> using 
        <a href='http://www.python.org/' target='_blank'>Python</a>, 
        <a href='http://www.mysql.com/' target='_blank'>MySQL</a>, 
        <a href='http://www.highcharts.com/' target='_blank'>HighCharts</a>, and 
        <a href='http://getbootstrap.com/' target='_blank'>Bootstrap</a> for use with telnet-capable devices and probes.<br>
        DAENA is free software released under GNU/GPLv3 and/or <a href='http://creativecommons.org/licenses/by/3.0/'>CC BY 3.0</a>.</p>
        <p id = "loadingtime"></p>
    </div>  
  </body>
</html>
