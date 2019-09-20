<!DOCTYPE html>
<html>
<head>
<style type="text/css">
	
</style>
	<title></title>
	<script type="text/javascript" src="./jquery-3.2.1.min.js"></script>
	<script type="text/javascript">
		$(document).ajaxStart(function(){
        	console.log('start')
        })

		$(document).ajaxSend(function(){
        	console.log('start')
        })
        $(document).ajaxSuccess(function(){
        	console.log('success')
        })
		$(document).ajaxComplete(function(){
        	console.log('complete')
        })
		$(document).ajaxStop(function(){
        	console.log('stop')
        })

        $.ajax({
			url: "http://localhost/auth/project/login",
			type: "POST",
			data: {
				username: '房明明00001',
				password: '123456'
			},
			success: function(r) {
				console.log(r)
			},
			error: function(XMLHttpRequest, textStatus, errorThrown) {
				console.log(XMLHttpRequest)
			}
		})
	</script>
</head>
<body>
</body>
</html>
