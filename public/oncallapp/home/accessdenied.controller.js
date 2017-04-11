angular
	.module('app')
	.controller('AccessDenied.IndexController', ['UserService', 'PageService', '$location', '$state', '$timeout', '$http', '$localStorage', 'jwtHelper', 'AuthenticationService', function(UserService, PageService, $location, $state, $timeout, $http, $localStorage, jwtHelper, AuthenticationService) {
		var vm = this;

		vm.messages = 'Loading Userinfo...';
		
		vm.getpage = PageService.getpage('accessdenied');
	

	}]);
	
	