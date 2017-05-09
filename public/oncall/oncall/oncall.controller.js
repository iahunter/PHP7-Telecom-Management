angular
	.module('app')
	.controller('Oncall.IndexController', ['TeamService', 'CUCMOncallService', 'UserService', 'PageService', '$location', '$state', '$timeout', '$http', '$localStorage', '$stateParams', 'jwtHelper', 'AuthenticationService', function(TeamService, CUCMOncallService, UserService, PageService, $location, $state, $timeout, $http, $localStorage, $stateParams, jwtHelper, AuthenticationService) {
		var vm = this;

		vm.permissions = window.telecom_mgmt_permissions;
		vm.messages = 'Loading Userinfo...';
		vm.userinfo = {};
		var pattern = $stateParams.id;
		
		vm.getpage = PageService.getpage('oncall - ' + pattern);

		function isInArrayNgForeach(field, arr) {
			var result = false;
			angular.forEach(arr, function(value, key) {
				if(field == value)
					result = true;
			});
			return result;
		}

		vm.getteamnumbers = TeamService.getteamnumbers()
			.then(function(res){
				// Check for errors and if token has expired. 
				if(res.data.message){
					//console.log(res);
					vm.message = res.data.message;
					//console.log(vm.message);
					if(vm.message == "Token has expired"){
						// Send user to login page if token expired. 
						alert(vm.message);
						$state.go('logout');
					}
					return vm.message;
				}
				
				vm.numbers = res.data.numbers;
				//console.log(vm.numbers);

				// Check if hte number exists inside the available team numbers to change. 
				if(isInArrayNgForeach(pattern, vm.numbers)){
					vm.getline = CUCMOncallService.getline(partition,pattern)
						.then(function(res){
							// Check for errors and if token has expired. 
							if(res.data.message){
								//console.log(res);
								vm.message = res.data.message;
								//console.log(vm.message);
								if(vm.message == "Token has expired"){
									// Send user to login page if token expired. 
									alert(vm.message);
									$state.go('logout');
								}
								if(vm.message == "You are not authorized"){
								// Send user to access denied page if they do not have permissions. 
									$location.path('/accessdenied');
								}
								return vm.message;
							}
							//console.log(res);
							vm.teamnumber = res.data.response;
							
							// Formatting for team number. 
							vm.teamnumber.formattedpattern = angular.copy(vm.teamnumber.pattern);
							vm.teamnumber.formattedpattern = vm.teamnumber.formattedpattern.replace(/(\d{3})(\d{3})(\d{4})/, '($1) $2-$3')
							
							// Get Team number and strip +1
							vm.teamnumber.currentoncallnum = angular.copy(vm.teamnumber.callForwardAll.destination);
							vm.teamnumber.currentoncallnum = vm.teamnumber.currentoncallnum.replace(/^\+1/,"")
							
							vm.loading = false;
							return vm.teamnumber
						}, function(err){
							vm.loading = false;
						});
				}else{
					//SEND TO PERMISSION DENIED PAGE
					$location.path('/accessdenied');
				}
				vm.loading = false;
				return vm.getteamnumbers
			}, function(err){
				vm.loading = false;
			});
			
		var partition = 'Global-All-Lines';

		vm.updateline = function(line) {
			//console.log(line)
			// Put the variable that we need into an array to send. We only ant to send name, carrier and comment for updates. 
			var line_update = {};
			line_update.cfa_destination = {};
			line_update.cfa_destination = line.callForwardAll.newoncallnum;
			line_update.pattern = line.pattern;

			CUCMOncallService.updateline(line_update)
			.then(function(res) {
				//console.log(res)
				vm.message = res.data.message;
				if(vm.message != ""){
					alert(vm.message);
				}else{
					return $state.reload();
				}
			}, function(error) {
				alert('An error occurred while updating the event')	
				}
			);
		}
	}]);
