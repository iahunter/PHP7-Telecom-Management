(function () {
    'use strict';

    angular
        .module('app', ['ui.router', 'ngMessages', 'ngStorage', 'angular-jwt'])
        .config(config)
        .run(run);

    function config($stateProvider, $urlRouterProvider) {
        // default route
        $urlRouterProvider.otherwise("/");

        // app routes
        $stateProvider
            .state('home', {
                url: '/',
                templateUrl: 'home/home.html',
                controller: 'Home.IndexController',
                controllerAs: 'vm'
            })
            .state('login', {
                url: '/login',
                templateUrl: 'login/login.html',
                controller: 'Login.IndexController',
                controllerAs: 'vm'
            })
			.state('didblock', {
                url: '/didblock',
                templateUrl: 'didblock/index.html',
                controller: 'Didblock.IndexController',
                controllerAs: 'vm'
            });
    }

    function run($rootScope, $http, $location, $localStorage, jwtHelper) {
        // keep user logged in after page refresh
        if ($localStorage.currentUser) {
			console.log('Found local storage login token: ' + $localStorage.currentUser.token);
			if (jwtHelper.isTokenExpired($localStorage.currentUser.token)) {
				console.log('Cached token is expired, logging out');
				delete $localStorage.currentUser;
				$http.defaults.headers.common.Authorization = '';
			}else{
				console.log('Cached token is still valid');
				$http.defaults.headers.common.Authorization = 'Bearer ' + $localStorage.currentUser.token;
			}
        }

        // redirect to login page if not logged in and trying to access a restricted page
        $rootScope.$on('$locationChangeStart', function (event, next, current) {
            var publicPages = ['/login'];
            var restrictedPage = publicPages.indexOf($location.path()) === -1;
            if (restrictedPage && !$localStorage.currentUser) {
                $location.path('/login');
            }
        });
    }
})();