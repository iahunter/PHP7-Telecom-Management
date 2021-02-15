<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Sonus Alarm</title>

        <!-- Fonts -->
        <link href="https://fonts.googleapis.com/css?family=Raleway:100,600" rel="stylesheet" type="text/css">
		<link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
		<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
   
    </head>
    <body>
		<div class="container">
		
			<div class="jumbotron">
			
				<legend>
					<h1>Sonus SBC Alarm Update!</h1>
					@if($status === "Alarm")
						<h3>{{ $hostname}} Status: <span style="color:red"><b>{{$status}}</b></span></h3>
					@elseif($status === "Alarm Cleared")
						<h3>{{ $hostname}} Status: <span style="color:green"><b>{{$status}}</b></span></h3>
					@endif
				</legend>
			
			
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
			
			

					@foreach ($alarms as $alarm)
						@if($alarm)
							<div class="panel panel-default" style="box-shadow: 1px 1px 5px grey;">
								<div class="table-responsive">   
									<table class="table table-striped table-condensed table-bordered table-hover">
										<thead>
											<tr style="background-color: #aeb3b7; background-image: linear-gradient(#e4e6e7, #d6d9db 60%, #c9cccf)">
											@if ($alarm['severity'] === "Minor")
												<th>Alarm: <span style="color:green">{{$alarm['severity']}}</span></th>
											@elseif ($alarm['severity'] === "Major")
												<th>Alarm: <span style="color:orange">{{$alarm['severity']}}</span></th>
											@elseif ($alarm['severity'] === "Critical")
												<th>Alarm: <span style="color:red">{{$alarm['severity']}}</span></th>
											@else
												<th>Alarm: {{$alarm['severity']}}</span></th>
											@endif
											</tr>
										</thead>
										<tbody style="font-size: 12px;">
											@foreach ($alarm as $key => $value)
												<!--Check Criticality of Alarm and color code-->
												@if ($value === "Minor")
												<tr><td><b>{{$key}}: <span style="color:green">{{ $value}}</span></b></td></tr>
												@elseif ($value === "Major")
												<tr><td><b>{{$key}}:  <span style="color:orange">{{ $value}}</span></b></td></tr>
												@elseif ($value === "Critical")
												<tr><td><b>{{$key}}:  <span style="color:red">{{ $value}}</span></b></td></tr>
												@else
												<tr><td><b>{{$key}}: {{ $value}}</b></td></tr>
												@endif
											@endforeach
										</tbody>
									</table>
								</div>
							</div>
						@endif
					@endforeach

				</div>
			</div>
		</div>
    </body>
</html>
