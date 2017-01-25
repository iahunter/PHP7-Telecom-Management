angular
	.module('app')
	.controller('Sonus.IndexController', ['SonusService', '$location', '$timeout', '$state', '$scope', function(SonusService, $location, $timeout, $state, $scope) {
	
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
			
		initController();
		
		vm.siteForm = {};
		
		vm.refresh = function (){
			$state.reload();
		};

		vm.messages = 'Loading sites...';
		//vm.sites = [{}];
		vm.loading = true;
		
		function initController() {
			vm.getactivecalls = SonusService.listactivecalls()
				.then(function(res){
					var calls = res.data;
					vm.callcount = 0;
					// Create our blank simple array for datatimegrps 
					vm.callarray = [];
					
					//console.log(calls);
					// Loop thru and append to a simple array so we can do a simple select on it with ng-options.
					angular.forEach(calls, function(value, key) {
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
										  vm.noactivecalls = false;
									  });
								  }
							  
							});
					});
					
					
					//console.log(vm.callarray);
					
					
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
		
		/*
		function initController() {
			SonusService.listactivecalls(function (result) {
				console.log('callback from SonusService.listactivecalls responded ' + result);
				
				var calls = SonusService.result;
				console.log(calls);
				
				
				vm.loading = false;
				vm.messages = JSON.stringify(vm.sites, null, "    ");

			});
		}
		*/

	}])

