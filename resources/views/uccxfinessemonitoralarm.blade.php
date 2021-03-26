<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>UCCX Alert</title>

        <!-- Fonts -->
        <link href="https://fonts.googleapis.com/css?family=Raleway:100,600" rel="stylesheet" type="text/css">
		<link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
		<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
   
    </head>
    <body>
		<div class="container">
		
			<div class="jumbotron">
			
				<legend>
					<h1>UCCX Alert</h1>
				</legend>
				
				
					<h3>UCCX Finesse Agent Monitor</h3>
					

					<h4>Agent State is {{$state}}. Expected NOT_READY after login attempt.</h4>
					
					<h5> This could indicate a login failure. 
					
			</div>
			
			<div class="well">
				<h4>Login Log.</h4>
				<pre>{{$login_response}}</pre>
			</div>
			
			<div class="well">
				<h4>Get Agent Status</h4>
				<pre>{{$response}}</pre>
			</div>
		</div>
    </body>
</html>
