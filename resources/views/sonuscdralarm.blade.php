<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Sonus Alert</title>

        <!-- Fonts -->
        <link href="https://fonts.googleapis.com/css?family=Raleway:100,600" rel="stylesheet" type="text/css">
		<link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
		<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
   
    </head>
    <body>
		<div class="container">
		
			<div class="jumbotron">
			
				<legend>
					<h1>Sonus SBC - CDR Alert - {{$alarms_count}} Attempt Threshold of {{$configured_threshold}} % Met!</h1>
				</legend>
				
					Please click below to review the last 60 minutes of the Call Attempt Report.<br>
					<a href="{{ url('/telephony/ui/#/sonus/cdr/todays_calls_attempts') }}"><b>View Report</b></a>
				
					<h3>Thesholds Met</h3>
			
					@foreach ($thresholds as $key => $value)
						@if($key) 
							<div class="panel panel-default" style="box-shadow: 1px 1px 5px grey;">
								<div class="table-responsive">   
									<table class="table table-striped table-condensed table-bordered table-hover">
										<thead>
											<tr style="background-color: #aeb3b7; background-image: linear-gradient(#e4e6e7, #d6d9db 60%, #c9cccf)">
												<th>{{$key}}</span></th>
											</tr>
										</thead>
										<tbody style="font-size: 12px;">
											@foreach ($value as $key => $stat)
												<tr><td>{{$key}}: {{$stat}}</span></td></tr>
											@endforeach
										</tbody>
									</table>
								</div>
							</div>
						@endif
					@endforeach
					
					
					<h3>All Stats</h3>

					@foreach ($stats as $key => $value)
						@if($key) 
							<div class="panel panel-default" style="box-shadow: 1px 1px 5px grey;">
								<div class="table-responsive">   
									<table class="table table-striped table-condensed table-bordered table-hover">
										<thead>
											<tr style="background-color: #aeb3b7; background-image: linear-gradient(#e4e6e7, #d6d9db 60%, #c9cccf)">
												<th>Time: {{$key}} UTC</span></th>
											</tr>
										</thead>
										<tbody style="font-size: 12px;">
											@foreach ($value as $key => $stat)
												<tr><td>{{$key}}: {{$stat}}</span></td></tr>
											@endforeach
										</tbody>
									</table>
								</div>
							</div>
						@endif
					@endforeach
					
					<h3>Top 10 Attempt Counts by Called Number</h3>

					
							<div class="panel panel-default" style="box-shadow: 1px 1px 5px grey;">
								<div class="table-responsive">   
									<table class="table table-striped table-condensed table-bordered table-hover">
										<thead>
											<tr style="background-color: #aeb3b7; background-image: linear-gradient(#e4e6e7, #d6d9db 60%, #c9cccf)">
												<th>Called Number</th>
												<th>Attempt Count</th>
											</tr>
										</thead>
									@foreach ($top_numbers as $key => $value)
										@if($value) 
										<tbody style="font-size: 12px;">
											
												<tr>
													<td>{{$value['called_number']}}</td>
													<td>{{$value['total']}}</td>
												</tr>
											
										</tbody>
										@endif
									@endforeach
									
									</table>
									
									
								</div>
							</div>
						

			</div>
		</div>
    </body>
</html>
