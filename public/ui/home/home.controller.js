angular
	.module('app')
	.controller('Home.IndexController', ['UserService', 'PageService', '$location', '$state', '$timeout', '$http', '$localStorage', 'jwtHelper', 'AuthenticationService', function(UserService, PageService, $location, $state, $timeout, $http, $localStorage, jwtHelper, AuthenticationService) {
		var vm = this;
		
		// Attempt to renew token on page click - FYI - this gets called on every page for navbar. 
        if ($localStorage.currentUser) {
			//console.log('Found local storage login token: ' + $localStorage.currentUser.token);
			
			// Attempt to Renew Token
			AuthenticationService.Renew($localStorage.currentUser.token, function (result) {
				//console.log('Attempting to renew Token')
				if(result.token){
					
					//Permissions Checker/
					var tokenPayload = jwtHelper.decodeToken($localStorage.currentUser.token);
					window.telecom_mgmt_permissions = tokenPayload.permissions;

					
					// Look at checking date expire and renew automatically. 
					var date = jwtHelper.getTokenExpirationDate($localStorage.currentUser.token);
					
					console.log(date);
					
					if (jwtHelper.isTokenExpired($localStorage.currentUser.token)) {
						console.log('home.controller.js Cached token is expired, logging out');
						delete $localStorage.currentUser;
						$http.defaults.headers.common.Authorization = '';
						$location.path('/logout');
					}else{
						console.log('home.controller.js Cached token is still valid');
						$http.defaults.headers.common.Authorization = 'Bearer ' + $localStorage.currentUser.token;
					}
				}
			})
		}

		// Match the window permission set in login.js and app.js - may want to user a service or just do an api call to get these. will decide later. 
		vm.permissions = window.telecom_mgmt_permissions;

		vm.messages = 'Loading Userinfo...';
		vm.userinfo = {};
		
		vm.getpage = PageService.getpage('home');
		
		vm.gettest = PageService.gettest()
			.then(function(res){
				// Check for errors and if token has expired. 
				console.log(res)
				vm.test = res;

			}, function(err){
				console.log(err)
			});
			

		initController();
		
		
		function initController() {
			UserService.Getuserinfo(function (result) {
				//console.log('callback from UserService.userinfo responded ' + result);
				vm.userinfo = UserService.userinfo;
				//vm.username = vm.userinfo.cn[0];
				vm.username = vm.userinfo.cn[0];
				vm.title = vm.userinfo.title[0];
				vm.photo = vm.userinfo.thumbnailphoto[0];
				
				//console.log(vm.userinfo);
				vm.messages = JSON.stringify(vm.userinfo, null, "    ");
				//$scope.accounts = vm.accounts;
				

				
			});
		}
		

	}]);
	
	