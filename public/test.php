<!DOCTYPE html>
<html>
<head>
<style type="text/css">
	
</style>
	<title></title>
	<script type="text/javascript" src="./jquery-3.2.1.min.js"></script>
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
var_dump('2019-08-20' - '2019-08-22');