(function () {
    'use strict';

    angular
        .module('app')
        .controller('Logout.IndexController', Controller);

    function Controller($location, AuthenticationService) {
        var vm = this;

        initController();

        function initController() {
            // reset login status
			window.telecom_mgmt_permissions = {};
			
			// Custom token Claim variable set in App\User
			window.telecom_user = {};
            AuthenticationService.Logout();
        };

    }

})();