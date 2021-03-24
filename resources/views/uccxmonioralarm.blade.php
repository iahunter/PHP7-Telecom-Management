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
				
					<a href="{{ url($url.':8445/finesse/api/SystemInfo') }}"><b>View Status</b></a>
				
					<h3>Device Status</h3>
					

					<h4>{{$status}}</h4>
					

			</div>
			
			<pre>{{$response}}</pre>
		</div>
    </body>
</html>
