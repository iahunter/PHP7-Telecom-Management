angular
	.module('app')
	.controller('Sonus.CallController', ['SonusService', '$location', '$timeout', '$interval', '$state', '$scope', function(SonusService, $location, $timeout, $interval, $state, $scope) {
	
		var vm = this;
		
		initController();
		
		vm.siteForm = {};
		
		vm.refresh = function (){
			$state.reload();
		};
		
		// Match the window permission set in login.js and app.js - may want to user a service or just do an api call to get these. will decide later. 
		vm.permissions = window.telecom_mgmt_permissions;

		if(!vm.permissions.read.Sonus5k){
			$location.path('/accessdenied');
		}

		vm.messages = 'Loading...';

		vm.loading = true;
		
		function initController() {
			
			vm.getcallcounts = SonusService.activecallcounts()
				.then(function(res){
					
					// Check for errors and if token has expired. 
					if(res.data.message){
						//console.log(res);
						vm.message = res.data.message;
						console.log(vm.message);
						
						if(vm.message == "Token has expired"){
							// Send user to login page if token expired. 
							$location.path('/logout');
						}

						return vm.message;
					}
					
					
					var calls = res.data;
					
					//console.log(calls)
					
					vm.totalcallcount = calls.totalCalls

					// Create our blank simple array for datatimegrps 
					vm.callarray = [];
					vm.callcounts = {};
					
					//console.log(calls);
					// Loop thru and append to a simple array so we can do a simple select on it with ng-options.
					angular.forEach(calls.stats, function(value, key) {

						  //console.log(key)
						  vm.callcounts[key] = value.totalCalls
					});
					
					
					//console.log(vm.callarray);
					
					//console.log(vm.totalcallcount);
					//console.log(vm.callcounts);
					
					if(vm.callarray.length == 0){
						// If no active calls returned then set the noactivecalls variable to display the message to the user. 
						vm.noactivecalls = true;
					}
					
					// Stop Loading 
					vm.loading = false;
					
					//$timeout(initController,5000); 
						
				}, function(err){
					alert(err);
				});
			
			vm.getactivecalls = SonusService.listactivecalls()
				.then(function(res){
					
					// Check for errors and if token has expired. 
					if(res.data.message){
						//console.log(res);
						vm.message = res.data.message;
						//console.log(vm.message);
						
						if(vm.message == "Token has expired"){
							// Send user to login page if token expired. 
							$location.path('/logout');
						}

						return vm.message;
					}
					
					
					var calldetails = res.data;

					//vm.callcount = 0;
					// Create our blank simple array for datatimegrps 
					vm.calldetailsarray = [];
					vm.callcountsummary = {};
					console.log("CallDetails:");
					console.log(calldetails);
					// Loop thru and append to a simple array so we can do a simple select on it with ng-options.
					angular.forEach(calldetails, function(value, key) {
						  //vm.callcountsummary[key] = 0;
						  //console.log(key); 

								  if (key == null){
									  //alert("No Active Calls");
									  vm.loading = false;
								  }
								  
								  else{
									  angular.forEach(value, function(call, object) {
										  //console.log(call.calledNumber);
										  //console.log(call);
										  call['SBC'] = key;
										  vm.calldetailsarray.push(call);
										  vm.noactivecalls = false;
									  });
								  }
							  

					});
					
					
					//console.log(vm.callarray);
					
					console.log(vm.calldetailsarray);
					
					if(vm.calldetailsarray.length == 0){
						// If no active calls returned then set the noactivecalls variable to display the message to the user. 
						vm.noactivecalls = true;
					}
					
					// Stop Loading 
					vm.loading = false;
					
					//$timeout(initController,5000); 
						
				}, function(err){
					alert(err);
				});
		}
		
		var pull = $interval(initController,5000); 
		
		$scope.$on('$destroy', function() {
			//console.log($scope);
            $interval.cancel(pull);
		});

	}])

