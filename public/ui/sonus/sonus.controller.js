angular
	.module('app')
	.controller('Sonus.IndexController', ['SonusService', '$location', '$state', function(SonusService, $location, $state) {
	
		var vm = this;
		
		//initController();
		
		vm.siteForm = {};
		
		vm.refresh = function (){
			$state.reload();
		};

		vm.messages = 'Loading sites...';
		//vm.sites = [{}];
		vm.loading = true;
		

		vm.getdidblocks = SonusService.listactivecalls()
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
									  console.log(call.calledNumber);
									  console.log(call);
									  call['SBC'] = key;
									  vm.callarray.push(call);
									  vm.callcount = vm.callcount + 1;
								  });
							  }
						  
						});
				});
				
				
				console.log(vm.callarray);
				
				if(vm.callarray.length == 0){
					// If no active calls returned then set the noactivecalls variable to display the message to the user. 
					vm.noactivecalls = true;
				}
				
				// Stop Loading 
				vm.loading = false;
					
					
			}, function(err){
				alert(err);
			});
		
		
		function initController() {
			SonusService.listactivecalls(function (result) {
				console.log('callback from SonusService.listactivecalls responded ' + result);
				
				var calls = SonusService.result;
				console.log(calls);
				
				
				vm.loading = false;
				vm.messages = JSON.stringify(vm.sites, null, "    ");

			});
		}
		

	}])

