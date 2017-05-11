angular
	.module('app')
	.controller('Sonus.CDRController', ['SonusCDRService', '$location', '$timeout', '$interval', '$state', '$scope', function(SonusCDRService, $location, $timeout, $interval, $state, $scope) {
	
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
			vm.getactivecalls = SonusCDRService.list_todays_calls_with_packetloss()
				.then(function(res){
					
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
					
					var callarray = res.data.result;
					vm.callarray = callarray
					//console.log(vm.callarray)

					// Convert DB Timestamp to local PC Time. 
					angular.forEach(callarray, function(call) {
						var date = new Date(call.start_time + " UTC");
						date = date.toLocaleString()
						call.start_time = date;
						
						var date = new Date(call.disconnect_time + " UTC");
						date = date.toLocaleString()
						call.disconnect_time = date;
					});
					

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
		
		var pull = $interval(initController,60000); 
		
		// Stop polling when you leave the page. 
		$scope.$on('$destroy', function() {
			//console.log($scope);
            $interval.cancel(pull);
		});

	}])

