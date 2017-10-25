angular
	.module('app')
	.controller('CallGraph.IndexController', ['CallService', 'cucmService', '$interval', '$location', '$state', '$stateParams', '$scope', function(CallService, cucmService, $interval, $location, $state, $stateParams, $scope) {
		
		var vm = this;
		
		vm.refresh = function (){
			$state.reload();
		};

		vm.error = false;
		
		// Match the window permission set in login.js and app.js - may want to user a service or just do an api call to get these. will decide later. 
		vm.permissions = window.telecom_mgmt_permissions;

		if(!vm.permissions.read.Calls){
			$location.path('/accessdenied');
		}
		
		dayscallstats();
		weekscallstats();
		daysgatewaycallstats();
		weeksgatewaycallstats();
		
		function dayscallstats() {
			CallService.dayscallstats()
				.then(function(res){
					
					// Check for errors and if token has expired. 
					if(res.data.message){
						//console.log(res);
						vm.message = res.data.message;
						//console.log(vm.message);
						
						if(vm.message == "Token has expired"){
							// Send user to login page if token expired. 
							//alert(vm.message);
							$state.go('logout');
						}
						vm.error = true;
						return vm.message;
					}

					vm.calls = res.data.result;

					vm.dayscallgraph = {};
					
					//console.log(block.stats);
					vm.dayscallgraph['chartlabels'] = [];
					vm.dayscallgraph['chartdata'] = [];
					vm.dayscallgraph['chartseries'] = [];
					
					vm.sbcs = [];
					vm.sbcs['totalCalls'] = [];
					
					angular.forEach(vm.calls, function(key, value) {
						vm.sbcs['totalCalls'].push(key.totalCalls);
						//console.log(key.stats);
						angular.forEach(key.stats, function(k, v) {
							
							// Create the SBC arrays for individual call counts. 
							if (vm.sbcs[v]){
								vm.sbcs[v].push(k.totalCalls);
							}else{
								vm.sbcs[v] = [];
								vm.sbcs[v].push(k.totalCalls);
							}
						});
						
						// Change time to local time. 
						var dateString = key.created_at;
						var created_at = moment().utc().format(dateString);
						created_at = moment.utc(created_at).toDate();
						key.created_at = created_at.toLocaleString()
						
						// Push date and time onto callgraph array for x axis labels. 
						vm.dayscallgraph['chartlabels'].push(key.created_at);
					});
					
					// Push data onto the chartgraph array
					for (key in vm.sbcs){
						//console.log(key);
						var value = vm.sbcs[key];
						//console.log(value);
						vm.dayscallgraph['chartseries'].push(key);
						vm.dayscallgraph['chartdata'].push(value);
					}
					
					// Enable the Options to be generated for the chart. 
					vm.dayscallgraph.chartoptions = { responsive: true, legend: { display: true}, title: {display:false, text:'SBC Call Summary'}};
					
					//console.log(vm.dayscallgraph);

					return vm.calls;
					
				}, function(err){
					//Error
				});
		}
		
		var pulldayscallstats = $interval(dayscallstats,600000); 
		
		$scope.$on('$destroy', function() {
			//console.log($scope);
            $interval.cancel(pulldayscallstats);
		});
			
		function weekscallstats() {
			CallService.weekscallstats()
				.then(function(res){
					
					// Check for errors and if token has expired. 
					if(res.data.message){
						//console.log(res);
						vm.message = res.data.message;
						//console.log(vm.message);
						
						if(vm.message == "Token has expired"){
							// Send user to login page if token expired. 
							//alert(vm.message);
							$state.go('logout');
						}
						vm.error = true;
						return vm.message;
					}

					vm.calls = res.data.result;

					vm.weekscallgraph = {};
					
					//console.log(block.stats);
					vm.weekscallgraph['chartlabels'] = [];
					vm.weekscallgraph['chartdata'] = [];
					vm.weekscallgraph['chartseries'] = [];
					
					vm.sbcs = [];
					vm.sbcs['totalCalls'] = [];
					
					angular.forEach(vm.calls, function(key, value) {
						vm.sbcs['totalCalls'].push(key.totalCalls);
						//console.log(key.stats);
						angular.forEach(key.stats, function(k, v) {
							
							// Create the SBC arrays for individual call counts. 
							if (vm.sbcs[v]){
								vm.sbcs[v].push(k.totalCalls);
							}else{
								vm.sbcs[v] = [];
								vm.sbcs[v].push(k.totalCalls);
							}
						});
						
						// Change time to local time. 
						var dateString = key.created_at;
						var created_at = moment().utc().format(dateString);
						created_at = moment.utc(created_at).toDate();
						key.created_at = created_at.toLocaleString()
						
						// Push date and time onto callgraph array for x axis labels. 
						vm.weekscallgraph['chartlabels'].push(key.created_at);
					});
					
					// Push data onto the chartgraph array
					for (key in vm.sbcs){
						//console.log(key);
						var value = vm.sbcs[key];
						//console.log(value);
						vm.weekscallgraph['chartseries'].push(key);
						vm.weekscallgraph['chartdata'].push(value);
					}
					
					// Enable the Options to be generated for the chart. 
					vm.weekscallgraph.chartoptions = { responsive: true, legend: { display: true}, title: {display:false, text:'SBC Call Summary'}};
					
					//console.log(vm.weekscallgraph);

					return vm.calls;
				
				}, function(err){
					//Error
				});
		}
		
		var pullweekscallstats = $interval(weekscallstats,600000); 
		
		$scope.$on('$destroy', function() {
			//console.log($scope);
            $interval.cancel(pullweekscallstats);
		});

		// Overall Call stats. Can get large and unmanageable. May want to make this into a selectable time graph and do some type of summarization on backend. 
		
		CallService.threemonthdailypeakcallstats()
			.then(function(res){
				
				// Check for errors and if token has expired. 
				if(res.data.message){
					console.log(res);
					vm.message = res.data.message;
					console.log(vm.message);
					
					if(vm.message == "Token has expired"){
						// Send user to login page if token expired. 
						//alert(vm.message);
						$state.go('logout');
					}
					vm.error = true;
					return vm.message;
				}

				vm.calls = res.data.result;

				vm.callgraph = {};
				
				//console.log(block.stats);
				vm.callgraph['chartlabels'] = [];
				vm.callgraph['chartdata'] = [];
				vm.callgraph['chartseries'] = [];
				
				vm.sbcs = [];
				vm.sbcs['totalCalls'] = [];
				
				angular.forEach(vm.calls, function(key, value) {
					vm.sbcs['totalCalls'].push(key.totalCalls);
					//console.log(key.stats);
					angular.forEach(key.stats, function(k, v) {
						
						// Create the SBC arrays for individual call counts. 
						if (vm.sbcs[v]){
							vm.sbcs[v].push(k.totalCalls);
						}else{
							vm.sbcs[v] = [];
							vm.sbcs[v].push(k.totalCalls);
						}
					});
					
					
					// Change time to local time. 
					var dateString = key.created_at;
					var created_at = moment().utc().format(dateString);
					created_at = moment.utc(created_at).toDate();
					key.created_at = created_at.toLocaleString();
					
					
					// Push date and time onto callgraph array for x axis labels. 
					vm.callgraph['chartlabels'].push(key.created_at);
				});
				
				// Push data onto the chartgraph array
				for (key in vm.sbcs){
					//console.log(key);
					var value = vm.sbcs[key];
					//console.log(value);
					vm.callgraph['chartseries'].push(key);
					vm.callgraph['chartdata'].push(value);
				}
				
				// Enable the Options to be generated for the chart. 
				vm.callgraph.chartoptions = { responsive: true, legend: { display: true}, title: {display:false, text:'SBC Call Summary'}};
				
				console.log(vm.callgraph);

				return vm.calls;
				
			}, function(err){
				//Error
			});
			
		/*
		// Cisco Gateway Call Stats
		function daysgatewaycallstats() {
			cucmService.daysgatewaycallstats()
				.then(function(res){
					
					// Check for errors and if token has expired. 
					if(res.data.message){
						//console.log(res);
						vm.message = res.data.message;
						//console.log(vm.message);
						
						if(vm.message == "Token has expired"){
							// Send user to login page if token expired. 
							//alert(vm.message);
							$state.go('logout');
						}
						vm.error = true;
						return vm.message;
					}

					vm.calls = res.data.result;

					vm.daysgatewaycallgraph = {};
					
					//console.log(block.stats);
					vm.daysgatewaycallgraph['chartlabels'] = [];
					vm.daysgatewaycallgraph['chartdata'] = [];
					vm.daysgatewaycallgraph['chartseries'] = [];
					
					vm.sbcs = [];
					//vm.sbcs['totalCalls'] = [];
					
					angular.forEach(vm.calls, function(key, value) {
						//vm.sbcs['totalCalls'].push(key.totalCalls);
						//console.log(key.stats);
						angular.forEach(key.stats, function(k, v) {
							
							// Create the SBC arrays for individual call counts. 
							if (vm.sbcs[v]){
								vm.sbcs[v].push(k);
							}else{
								vm.sbcs[v] = [];
								vm.sbcs[v].push(k);
							}
						});
						
						// Change time to local time. 
						var dateString = key.created_at;
						var created_at = moment().utc().format(dateString);
						created_at = moment.utc(created_at).toDate();
						key.created_at = created_at.toLocaleString()
						
						// Push date and time onto callgraph array for x axis labels. 
						vm.daysgatewaycallgraph['chartlabels'].push(key.created_at);
					});
					
					// Push data onto the chartgraph array
					for (key in vm.sbcs){
						//console.log(key);
						var value = vm.sbcs[key];
						//console.log(value);
						vm.daysgatewaycallgraph['chartseries'].push(key);
						vm.daysgatewaycallgraph['chartdata'].push(value);
					}
					
					// Enable the Options to be generated for the chart. 
					vm.daysgatewaycallgraph.chartoptions = { responsive: true, legend: { display: true}, title: {display:false, text:'H323 Gateway Call Summary'}};
					
					console.log(vm.daysgatewaycallgraph);

					return vm.calls;
					
				}, function(err){
					//Error
				});
		}
		
		*/
		
		// Cisco Gateway Call Stats
		function daysgatewaycallstats() {
			cucmService.daysgatewaycallstats()
				.then(function(res){
					
					// Check for errors and if token has expired. 
					if(res.data.message){
						//console.log(res);
						vm.message = res.data.message;
						//console.log(vm.message);
						
						if(vm.message == "Token has expired"){
							// Send user to login page if token expired. 
							//alert(vm.message);
							$state.go('logout');
						}
						vm.error = true;
						return vm.message;
					}

					vm.calls = res.data.result;

					vm.daysgatewaycallgraph = {};
					
					//console.log(block.stats);
					vm.daysgatewaycallgraph['chartlabels'] = [];
					vm.daysgatewaycallgraph['chartdata'] = [];
					vm.daysgatewaycallgraph['chartseries'] = [];
					
					vm.sbcs = [];
					vm.sbcs['totalCalls'] = [];

					angular.forEach(vm.calls, function(key, value) {
						
						vm.sbcs['totalCalls'].push(key.totalCalls);
						//console.log(key.stats);

						// Change time to local time. 
						var dateString = key.created_at;
						var created_at = moment().utc().format(dateString);
						created_at = moment.utc(created_at).toDate();
						key.created_at = created_at.toLocaleString()
						
						// Push date and time onto callgraph array for x axis labels. 
						vm.daysgatewaycallgraph['chartlabels'].push(key.created_at);
					});
					
					// Push data onto the chartgraph array
					for (key in vm.sbcs){
						//console.log(key);
						var value = vm.sbcs[key];
						//console.log(value);
						vm.daysgatewaycallgraph['chartseries'].push(key);
						vm.daysgatewaycallgraph['chartdata'].push(value);
					}
					
					// Enable the Options to be generated for the chart. 
					vm.daysgatewaycallgraph.chartoptions = { responsive: true, legend: { display: true}, title: {display:false, text:'H323 Gateway Call Summary'}};
					console.log("gateway")
					console.log(vm.daysgatewaycallgraph);

					return vm.calls;
					
				}, function(err){
					//Error
				});
		}
		
		var pulldaysgatewaycallstats = $interval(daysgatewaycallstats,600000); 
		
		$scope.$on('$destroy', function() {
			//console.log($scope);
            $interval.cancel(pulldaysgatewaycallstats);
		});
		
		
		
		function weeksgatewaycallstats() {
			cucmService.weeksgatewaycallstats()
				.then(function(res){
					
					// Check for errors and if token has expired. 
					if(res.data.message){
						//console.log(res);
						vm.message = res.data.message;
						//console.log(vm.message);
						
						if(vm.message == "Token has expired"){
							// Send user to login page if token expired. 
							//alert(vm.message);
							$state.go('logout');
						}
						vm.error = true;
						return vm.message;
					}

					vm.calls = res.data.result;

					vm.weeksgatewaycallgraph = {};
					
					//console.log(block.stats);
					vm.weeksgatewaycallgraph['chartlabels'] = [];
					vm.weeksgatewaycallgraph['chartdata'] = [];
					vm.weeksgatewaycallgraph['chartseries'] = [];
					
					vm.sbcs = [];
					vm.sbcs['totalCalls'] = [];
					
					angular.forEach(vm.calls, function(key, value) {
						vm.sbcs['totalCalls'].push(key.totalCalls);

						// Change time to local time. 
						var dateString = key.created_at;
						var created_at = moment().utc().format(dateString);
						created_at = moment.utc(created_at).toDate();
						key.created_at = created_at.toLocaleString()
						
						// Push date and time onto callgraph array for x axis labels. 
						vm.weeksgatewaycallgraph['chartlabels'].push(key.created_at);
					});
					
					// Push data onto the chartgraph array
					for (key in vm.sbcs){
						//console.log(key);
						var value = vm.sbcs[key];
						//console.log(value);
						vm.weeksgatewaycallgraph['chartseries'].push(key);
						vm.weeksgatewaycallgraph['chartdata'].push(value);
					}
					
					// Enable the Options to be generated for the chart. 
					vm.weeksgatewaycallgraph.chartoptions = { responsive: true, legend: { display: true}, title: {display:false, text:'SBC Call Summary'}};
					
					//console.log(vm.weeksgatewaycallgraph);

					return vm.calls;
				
				}, function(err){
					//Error
				});
		}
		
		var pullweeksgatewaycallstats = $interval(weeksgatewaycallstats,600000); 
		
		$scope.$on('$destroy', function() {
			//console.log($scope);
            $interval.cancel(pullweeksgatewaycallstats);
		});

			
	}]);