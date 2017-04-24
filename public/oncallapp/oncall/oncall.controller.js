angular
	.module('app')
	.controller('Oncall.IndexController', ['CUCMOncallService', 'UserService', 'PageService', '$location', '$state', '$timeout', '$http', '$localStorage', '$stateParams', 'jwtHelper', 'AuthenticationService', function(CUCMOncallService, UserService, PageService, $location, $state, $timeout, $http, $localStorage, $stateParams, jwtHelper, AuthenticationService) {
		var vm = this;

		// Match the window permission set in login.js and app.js - may want to user a service or just do an api call to get these. will decide later. 
		vm.permissions = window.telecom_mgmt_permissions;

		vm.messages = 'Loading Userinfo...';
		vm.userinfo = {};
		
		vm.getpage = PageService.getpage('oncallapp-home');
		
		var pattern = $stateParams.id; //angular module that stores shit, it's just the value of pattern. in our case, it's going to a phone number
		
		var partition = 'Global-All-Lines';
		
		vm.getline = CUCMOncallService.getline(partition,pattern)
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
				console.log(res);
				vm.teamnumber = res.data.response;
				
				vm.teamnumber.currentoncallnum = angular.copy(vm.teamnumber.callForwardAll.destination);

				vm.loading = false;
				return vm.teamnumber
				
				
			}, function(err){
				vm.loading = false;
			});
			
			
			vm.updateline = function(line) {
				console.log(line)
			
			// Put the variable that we need into an array to send. We only want to send name, carrier and comment for updates. 
			var line_update = {};
			line_update.cfa_destination = {};
			line_update.cfa_destination = line.callForwardAll.destination;
			line_update.pattern = line.pattern;
			
			console.log(line_update)
			
			
			//  and the updated x to the update service. 
			CUCMOncallService.updateline(line_update)
				.then(function(data) {
					return $state.reload();
				}, function(error) {
					alert('An error occurred while updating the event')
				});
		}		
			
		
	}]);	