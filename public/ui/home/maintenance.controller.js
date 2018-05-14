angular
	.module('app')
	.controller('Maintenance.IndexController', ['UserService', 'PageService', '$location', '$state', '$timeout', '$http', '$localStorage', 'jwtHelper', 'AuthenticationService', function(UserService, PageService, $location, $state, $timeout, $http, $localStorage, jwtHelper, AuthenticationService) {
		var vm = this;

		vm.messages = 'Loading Userinfo...';
		
		

	}]);
	
	