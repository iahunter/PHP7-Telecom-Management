<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Sonus Detected Loop Alert</title>

        <!-- Fonts -->
        <link href="https://fonts.googleapis.com/css?family=Raleway:100,600" rel="stylesheet" type="text/css">
		<link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
		<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
   
    </head>
    <body>
		<div class="container">
		
			<div class="jumbotron">
			
				<legend>
					<h1>CDR Alert - Routing Loop Detected!</h1>
				</legend>
				
					Please click below to review the last 60 minutes of the Call Attempt Report.<br>
					<a href="{{ url('/telephony/ui/#/sonus/cdr/todays_calls_attempts') }}"><b>View Report</b></a>
					
					<h3><i style="color:red;">WARNING!</i> Action was taken by Telecom Management System to mitigate a detected routing loop. </h3>
					
                    
					<ul>
						<li>Time: {{$time}}</li>
						<li>Detected Loop Count: {{$loops}}</li>
						<li>Fixed Loops: {{$fixed_loops}}</li>
						<li>Unfixed Loops: {{$unfixed_loops}}</li>
					</ul>
					
					<h5> Action Data </h5>
					<pre>{{$cdrs_json}}</pre>


			</div>
		</div>
    </body>
</html>
