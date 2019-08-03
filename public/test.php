<!DOCTYPE html>
<html>
<head>
	<title></title>
	<script type="text/javascript" src="jquery-3.2.1.min.js"></script>
</head>
<body>
<script type="text/javascript">
	$.ajax({
		url:"http://192.168.10.172/auth/login",
		type:"post",
		data:{
			username:"test1",
			password:"1234567"
		},
		success:function(r){
			console.log(r)
		},
		error:function(XMLHttpRequest){
			console.log(XMLHttpRequest)
		}
	})
</script>
</body>
</html>