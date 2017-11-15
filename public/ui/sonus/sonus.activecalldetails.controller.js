angular
	.module('app')
	.controller('Sonus.CallDetailsController', ['SonusService', '$location', '$timeout', '$interval', '$state', '$scope', function(SonusService, $location, $timeout, $interval, $state, $scope) {
	
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
			vm.getactivecalls = SonusService.listcallDetailStatus_Media()
				.then(function(res){
					vm.message = "";
					// Check for errors and if token has expired. 
					if(res.data.message){
						console.log(res);
						vm.message = res.data.message;
						console.log(vm.message);
						
						if(vm.message == "Token has expired"){
							// Send user to login page if token expired. 
							$location.path('/logout');
						}

						return vm.message;
					}
					
					
					var calls = res.data;

					vm.callcount = 0;
					// Create our blank simple array for datatimegrps 
					vm.callarray = [];
					vm.callcountsummary = {};
					
					//console.log(calls);
					// Loop thru and append to a simple array so we can do a simple select on it with ng-options.
					angular.forEach(calls, function(value, key) {
						  vm.callcountsummary[key] = 0;
						  angular.forEach(value, function(v, k) {
								  if (v == null){
									  //alert("No Active Calls");
									  vm.loading = false;
								  }
								  
								  else{
									  angular.forEach(v, function(call, object) {
										  //console.log(call.calledNumber);
										  //console.log(call);
										  call['SBC'] = key;
										  vm.callarray.push(call);
										  vm.callcount = vm.callcount + 1;
										  vm.callcountsummary[key] = vm.callcountsummary[key] + 1;
										  vm.noactivecalls = false;
									  });
								  }
							  
							});
					});
					
					
					//console.log(vm.callarray);
					
					//console.log(vm.callcountsummary);
					
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
		}
		
		var pull = $interval(initController,30000); 
		
		$scope.$on('$destroy', function() {
			//console.log($scope);
            $interval.cancel(pull);
		});

	}])

