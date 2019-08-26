<!DOCTYPE html>
<html>
<head>
<style type="text/css">
	
</style>
	<title></title>
	<script type="text/javascript" src="./jquery-3.2.1.min.js"></script>
	<!-- Matomo -->
<script type="text/javascript">
  var _paq = window._paq || [];
  /* tracker methods like "setCustomDimension" should be called before "trackPageView" */
  _paq.push(['trackPageView']);
  _paq.push(['enableLinkTracking']);
  (function() {
    var u="//47.95.226.232:8166/";
    _paq.push(['setTrackerUrl', u+'matomo.php']);
    _paq.push(['setSiteId', '1']);
    var d=document, g=d.createElement('script'), s=d.getElementsByTagName('script')[0];
    g.type='text/javascript'; g.async=true; g.defer=true; g.src=u+'matomo.js'; s.parentNode.insertBefore(g,s);
  })();
</script>
<!-- End Matomo Code -->
	<script type="text/javascript">
		// $.ajax({
		// 	url:"http://localhost/excel/import/test",
		// 	type:'post',
		// 	data:"",
		// 	success:function(r){
		// 		console.log(r)
		// 	}
		// })
	</script>
</head>
<body>
<a auth='test1'>1</a>
<h5 class="t1" auth='test2'>2</h5>
<p class="t2" auth='test3'>3</p>
<p class="t3" auth='test4'>4</p>
<p class="t4">5</p>
</body>
<script type="text/javascript">
	console.log($('*[auth]'))
</script>
</html>
<?php
echo 0x11;