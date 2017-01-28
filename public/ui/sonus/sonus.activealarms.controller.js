angular
	.module('app')
	.controller('Sonus.AlarmController', ['SonusService', '$location', '$timeout', '$interval', '$state', '$scope', function(SonusService, $location, $timeout, $interval, $state, $scope) {
	
		var vm = this;
		

		initController();
		
		/*
			Need to fix polling after leaving the page. 
		
		*/
		
		
		vm.siteForm = {};
		
		vm.refresh = function (){
			$state.reload();
		};

		vm.messages = 'Loading Alarms...';
		//vm.sites = [{}];
		vm.loading = true;
		
		function initController() {
			vm.getactivealarms = SonusService.listactivealarms()
				.then(function(res){
					
					// Check for errors and if token has expired. 
					if(res.data.message){
						console.log(res);
						vm.message = res.data.message;
						console.log(vm.message);
						
						if(vm.message == "Token has expired"){
							// Send user to login page if token expired. 
							alert("Token has expired, Please relogin");
							$state.go('login');
						}

						return vm.message;
					}
					
					var alarms = res.data;

					vm.alarmcount = 0;
					// Create our blank simple array for datatimegrps 
					vm.alarmarray = [];
					
					//console.log(alarms);
					// Loop thru and append to a simple array so we can do a simple select on it with ng-options.
					angular.forEach(alarms, function(value, key) {
						  angular.forEach(value, function(v, k) {
								  if (v == null){
									  //alert("No Active Calls");
									  vm.loading = false;
								  }
								  
								  else{
									  angular.forEach(v, function(alarm, object) {
										  //console.log(alarm.alarmedNumber);
										  //console.log(alarm);
										  alarm['SBC'] = key;
										  vm.alarmarray.push(alarm);
										  vm.alarmcount = vm.alarmcount + 1;
										  vm.noactivealarms = false;
									  });
								  }
							  
							});
					});
					
					
					//console.log(vm.alarmarray);
					
					
					if(vm.alarmarray.length == 0){
						// If no active alarms returned then set the noactivealarms variable to display the message to the user. 
						vm.noactivealarms = true;
					}
					
					// Stop Loading 
					vm.loading = false;
					
					//$timeout(initController,5000); 
					
						
				}, function(err){
					alert(err);
				});
		}
		
		pull = $interval(initController,60000); 
		
		/*
		element.on('$destroy', function() {
            $interval.cancel(stopTime);
          });*/
	}])

