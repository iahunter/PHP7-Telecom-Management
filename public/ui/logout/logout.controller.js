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
            AuthenticationService.Logout();
        };

    }

})();