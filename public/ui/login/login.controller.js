(function () {
    'use strict';

    angular
        .module('app')
        .controller('Login.IndexController', Controller);

    function Controller($location, AuthenticationService) {
        var vm = this;

        vm.login = login;

        initController();

        function initController() {
            // reset login status
            AuthenticationService.Logout();
        };

        function login() {
            vm.loading = true;
            AuthenticationService.Login(vm.username, vm.password, function (result) {
                if (result === true) {
                    $location.path('/');
                } else {
                    vm.error = 'Username or password is incorrect';
                    vm.loading = false;
                }
            });
        };

		// Attempt to auto-authenticate
		vm.loading = true;
		AuthenticationService.Login('', '', function (result) {
			if (result === true) {
				$location.path('/');
			} else {
				vm.error = 'Automatic certificate authentication failed, please login with LDAP credentials';
				vm.loading = false;
			}
		});
    }

})();