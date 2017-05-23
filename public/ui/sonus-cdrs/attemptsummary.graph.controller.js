angular
	.module('app')
	.controller('Sonus.AttemptSummary.CDR.Controller', ['SonusCDRService', '$interval', '$location', '$state', '$stateParams', '$scope', function(SonusCDRService, $interval, $location, $state, $stateParams, $scope) {
		
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
		
		list_todays_attempts_summary_report();
		
		function list_todays_attempts_summary_report() {
			SonusCDRService.list_todays_attempts_summary_report()
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
					console.log(vm.calls)
					vm.dayscallgraph = {};
					
					//console.log(block.stats);
					vm.dayscallgraph['chartlabels'] = [];
					vm.dayscallgraph['chartdata'] = [];
					vm.dayscallgraph['chartseries'] = [];
					
					vm.sbcs = [];
					vm.sbcs['totalCalls'] = [];
					
					angular.forEach(vm.calls, function(key, value) {
						//console.log(key)
						//console.log(value)
						//vm.sbcs['totalCalls'].push(key.totalCalls);
						//console.log(key.stats);
						angular.forEach(key, function(k, v) {
							console.log(v)
							// Create the SBC arrays for individual call counts. 
							if (vm.sbcs[v]){
								vm.sbcs[v].push(k);
							}else{
								vm.sbcs[v] = [];
								vm.sbcs[v].push(k);
							}
						});
						
						// Change time to local time. 
						var dateString = value;
						console.log(dateString)
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
		
		var pulldayscallstats = $interval(list_todays_attempts_summary_report,60000); 
		
		$scope.$on('$destroy', function() {
			//console.log($scope);
            $interval.cancel(pulldayscallstats);
		});
		

			
	}]);