<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Routing Loop Detected</title>

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
				
					<h3><i style="color:red;">WARNING!</i> Action was taken by Telecom Management System to mitigate a detected routing loop. </h3>
					
					<h4><i style="color:orange;">WARNING!</i>The provider may block this number because of the detected loop. Please check with the provider to see if the numbers are blocked via the web portal.</h4>
					
					
                    <div class="panel panel-default" style="box-shadow: 1px 1px 5px grey;">
						<h2>Loop Detection Summary</h2>
						<div class="table-responsive">   
							<table class="table table-striped table-condensed table-bordered table-hover">
								<tbody style="font-size: 12px;">
									<tr><td>Time: {{$time}}</td></tr>
									<tr><td>Detected Loop Count: {{$loops}}</td></tr>
									<tr><td>Fixed Loops: {{$fixed_loops}}</td></tr>
									<tr><td>Unfixed Loops: {{$unfixed_loops}}</td></tr>									
								</tbody>
							</table>
						</div>
					</div>

					<div class="panel panel-default" style="box-shadow: 1px 1px 5px grey;">
						<h2>Top Attempt Counts by Called Number</h2>
						<div class="table-responsive">   
							<table class="table table-striped table-condensed table-bordered table-hover">
								<thead>
									<tr style="background-color: #aeb3b7; background-image: linear-gradient(#e4e6e7, #d6d9db 60%, #c9cccf)">
										<th>Called Number</span></th>
										<th>Attempt Counts</span></th>
									</tr>
								</thead>

								<tbody style="font-size: 12px;">
								
								@foreach ($attempt_counts as $attempt)
									@if($attempt) 
									<tr>
									
										<td>{{$attempt['called_number']}}</td>
										<td>{{$attempt['total']}}</td>
									</tr>
									@endif
								@endforeach
									
								</tbody>
							</table>
						</div>
					</div>
					
					<h2> Action Data </h2>
					<pre>{{$cdrs_json}}</pre>


			</div>
		</div>
    </body>
</html>
