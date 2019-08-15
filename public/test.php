<!DOCTYPE html>
<html>
<head>
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

</body>
</html>
<?php
function datePeriod($startTime, $endTime, $period, $periodFormat)
{
    $result = [];
    $start = new \DateTime($startTime);
    $end = new \DateTime($endTime);
    $interval = \DateInterval::createFromDateString($period);//可以是'1 month','1day','4day'
    $period = new \DatePeriod($start, $interval, $end);
    foreach ($period as $dt) {
        $result[$dt->format($periodFormat)] = '';
    }
    return $result;
}


echo json_encode(datePeriod('2019-04-01','2019-07-20','1 month','Y-m'));