angular
	.module('app')
	.controller('Home.IndexController', ['TeamService','UserService', 'PageService', '$location', '$state', '$timeout', '$http', '$localStorage', 'jwtHelper', 'AuthenticationService', function(TeamService, UserService, PageService, $location, $state, $timeout, $http, $localStorage, jwtHelper, AuthenticationService) {
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
					
					// Custom token Claim variable set in App\User
					window.telecom_user = tokenPayload.user;
					
					// Look at checking date expire and renew automatically. 
					var date = jwtHelper.getTokenExpirationDate($localStorage.currentUser.token);

					//console.log(date);

					if (jwtHelper.isTokenExpired($localStorage.currentUser.token)) {
						//console.log('home.controller.js Cached token is expired, logging out');
						delete $localStorage.currentUser;
						$http.defaults.headers.common.Authorization = '';
						$location.path('/logout');
					}else{
						//console.log('home.controller.js Cached token is still valid');
						$http.defaults.headers.common.Authorization = 'Bearer ' + $localStorage.currentUser.token;
					}
				}
			})
		}

		// Match the window permission set in login.js and app.js - may want to user a service or just do an api call to get these. will decide later. 
		vm.permissions = window.telecom_mgmt_permissions;
		vm.messages = 'Loading Userinfo...';
		vm.userinfo = {};
		vm.getpage = PageService.getpage('oncallapp-home');

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

			//Old way of displaying teams statically
			/*
			{
				"display": "Teams",
				"href": "#",
				"children": [
				{
					"display": "Team1",
					"href": "#",
					"children": [
						{
							"display": "Primary",
							"href": "#/teams/edit/5551234567",
							"children": []
						},
						{
							"display": "Secondary",
							"href": "#/teams/edit/5557891234",
							"children": []					
						}
					]
				}
			}
			*/

		//New way of displaying teams, by pulling in json file with team names and number/s
		vm.getmenuItems = TeamService.getteamsnavbardata()
			.then(function(res){
				// Check for errors and if token has expired. 
				if(res.data.message){
					//console.log(res); //This prints out the array of teams and phone numbers
					vm.message = res.data.message;
					console.log(vm.message);

					if(vm.message == "Token has expired"){
						// Send user to login page if token expired. 
						alert(vm.message);
						$state.go('logout');
					}

					return vm.message;
				}
				vm.menuItems = [res.data];
				//console.log(vm.menuItems);

				vm.loading = false;
				return vm.menuItems

			}, function(err){
				vm.loading = false;
			});
	}]);