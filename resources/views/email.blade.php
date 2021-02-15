<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Device Status Change</title>

        <!-- Fonts -->
        <link href="https://fonts.googleapis.com/css?family=Raleway:100,600" rel="stylesheet" type="text/css">
		<link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
		<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
   
    </head>
    <body>
		<div class="container">
			<legend>
				<h1>Telecom Management - Device Status Change!</h1>
				<h3>{{ $host->hostname}} is: {{$status}}</h3>
			</legend>
			<div class="jumbotron">
				<div class="panel panel-default" style="box-shadow: 1px 1px 5px grey;">
					<div class="table-responsive">    
						<table class="table table-striped table-condensed table-bordered table-hover">
							<tbody style="font-size: 12px;">
								<!--List View-->
								<tr><td><b>Time: </b> {{ $time}}</b></td></tr>
								<tr><td><b>Status: </b> {{ $status}}</b></td></tr>
								<tr><td><b>Hostname: </b> {{ $host->hostname}}</b></td></tr>
								<tr><td><b>Comment: </b>{{ $host->comment}}</td></tr>
								<tr><td><b>Function: </b>{{ $host->function}}</td></tr>
								<tr><td><b>Role: </b>{{ $host->role}}</a></td></tr>
								<tr><td><b>Manufacturer: </b>{{ $host->manufacturer}}</td></tr>
								<tr><td><b>Application: </b>{{ $host->application}}</td></tr>
								<tr><td><b>Software_version: </b>{{ $host->software_version}}</td></tr>
								<tr><td><b>OS: </b>{{ $host->os}}</td></tr>
								<tr><td><b>IP Address: </b>{{ $host->ip_address}}</td></tr>
								<tr><td><b>URL: </b>{{ $host->mgmt_url}}</td></tr>
								<tr><td><b>Location: </b>{{ $host->location}}</td></tr>
							</tbody>
						</table>
					</div>
				</div>
			</div>
		</div>
    </body>
</html>
