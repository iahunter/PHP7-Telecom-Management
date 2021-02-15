angular
	.module('app')
	.controller('Sonus.AlarmController', ['SonusService', '$location', '$timeout', '$interval', '$state', '$scope', function(SonusService, $location, $timeout, $interval, $state, $scope) {
	
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
			vm.getactivealarms = SonusService.listactivealarms()
				.then(function(res){

					
					// Check for errors and if token has expired. 
					if(res.data.message){
						//console.log(res);
						vm.message = res.data.message;
						//console.log(vm.message);
						
						if(vm.message == "Token has expired"){
							// Send user to login page if token expired. 
							//alert("Token has expired, Please relogin");
							$location.path('/logout');
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

								  if (value == null){
									  //alert("No Active Calls");
									  vm.loading = false;
								  }
								  
								  else{
									  angular.forEach(value, function(alarm, object) {
										  //console.log(alarm.alarmedNumber);
										  //console.log(alarm);
										  alarm['SBC'] = key;
										  vm.alarmarray.push(alarm);
										  vm.alarmcount = vm.alarmcount + 1;
										  vm.noactivealarms = false;
									  });
								  }
							  

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
					console.log(error)
					alert(err);
				});
		}
		
		var pull = $interval(initController,60000); 
		
		$scope.$on('$destroy', function() {
			//console.log($scope);
            $interval.cancel(pull);
		});
		
		
	}])

