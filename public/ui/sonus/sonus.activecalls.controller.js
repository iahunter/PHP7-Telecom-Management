angular
	.module('app')
	.controller('Sonus.CallController', ['SonusService', '$location', '$timeout', '$state', '$scope', function(SonusService, $location, $timeout, $state, $scope) {
	
		var vm = this;
		
		
		/* Don't think we need this anymore. Keeping for example watch. 
		$scope.$watch(function() {
            return vm.callarray;
        }, function(current, original) {
			
			vm.callarray = current;
            console.log('vm.callarray was %s', original);
            console.log('vm.callarray is now %s', current);

        });
		*/
		
		
		/*
			Need to fix polling after leaving the page. 
		
		*/
			
		initController();
		
		vm.siteForm = {};
		
		vm.refresh = function (){
			$state.reload();
		};

		vm.messages = 'Loading...';

		vm.loading = true;
		
		function initController() {
			vm.getactivecalls = SonusService.listactivecalls()
				.then(function(res){
					
					// Check for errors and if token has expired. 
					if(res.data.message){
						console.log(res);
						vm.message = res.data.message;
						console.log(vm.message);
						
						if(vm.message == "Token has expired"){
							// Send user to login page if token expired. 
							alert(vm.message);
							$state.go('logout');
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
					
					$timeout(initController,5000); 
						
				}, function(err){
					alert(err);
				});
		}
		

	}])

