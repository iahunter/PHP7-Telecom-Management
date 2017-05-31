(function () {
    'use strict';

    angular
        .module('app', ['ui.router', 'ngMessages', 'ngStorage', 'angular-jwt', 'chart.js', 'ngAnimate', 'ngSanitize', 'bootstrapSubmenu'])
        .config(config)
        .run(run);

		
	function run(PageService, CompanyService, $rootScope, $window, $http, $location, $localStorage, jwtHelper, AuthenticationService) {
        
		// keep user logged in after page refresh
        if ($localStorage.currentUser) {
			//console.log('Found local storage login token: ' + $localStorage.currentUser.token);
			

			//Permissions Checker/
			var tokenPayload = jwtHelper.decodeToken($localStorage.currentUser.token);
			window.telecom_mgmt_permissions = tokenPayload.permissions;
			window.telecom_user = tokenPayload.user;
			
			// Look at checking date expire and renew automatically. 
			var date = jwtHelper.getTokenExpirationDate($localStorage.currentUser.token);
			
			console.log(date);
			
			if (jwtHelper.isTokenExpired($localStorage.currentUser.token)) {
				//console.log('Cached token is expired, logging out');
				delete $localStorage.currentUser;
				$http.defaults.headers.common.Authorization = '';
				$location.path('/logout');
			}else{
				//console.log('app.js Cached token is still valid');
				$http.defaults.headers.common.Authorization = 'Bearer ' + $localStorage.currentUser.token;
				
				// Attempt to Renew Token
				AuthenticationService.Renew($localStorage.currentUser.token, function (result) {
					//console.log('Attempting to renew Token')
					if(result.token){
						
						//Permissions Checker/
						var tokenPayload = jwtHelper.decodeToken($localStorage.currentUser.token);
						window.telecom_mgmt_permissions = tokenPayload.permissions;
						window.telecom_user = tokenPayload.user;
						
						// Look at checking date expire and renew automatically. 
						var date = jwtHelper.getTokenExpirationDate($localStorage.currentUser.token);
						
						//console.log(date);
						
						if (jwtHelper.isTokenExpired($localStorage.currentUser.token)) {
							//console.log('Cached token is expired, logging out');
							delete $localStorage.currentUser;
							$http.defaults.headers.common.Authorization = '';
							$location.path('/logout');
						}else{
							//console.log('Cached token is still valid');
							$http.defaults.headers.common.Authorization = 'Bearer ' + $localStorage.currentUser.token;
						}
					}
				})
				
				
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
		
		// Get Google Analytics ID from Company Content Folder
		var getcompanycontent = CompanyService.getgoogleanalyticsid()
			.then(function(res){
				
				var google = res.data.id;
				
				// Get user info
				var dimensionValue = window.telecom_user;
				if(dimensionValue){
					dimensionValue = dimensionValue.toString();
					// Set our google analytics id
					$window.ga('create', google, {'userId': dimensionValue});
				}

				
				$rootScope.$on('$stateChangeSuccess', function (event) {
					
					// Log our Page Requests to our database
					var application_name = "oncall";
					var location = $location.path()
					location = location.split('/').join('~~~')	// replace / with ~~~ so we can send in url
					
					if($location.path() != '/login' && $location.path() != '/logout'){
						PageService.getpage(application_name + "&" + location)
					}
					
										
					//console.log("Send Google Analytics")
					if(dimensionValue){
						$window.ga('set', 'dimension1', dimensionValue);
					}
					
					$window.ga('send', 'pageview', $location.path());

					
				});

			}, function(err){
				vm.loading = false;
			});
    }
	
    function config($stateProvider, $urlRouterProvider) {
        // default route
        $urlRouterProvider.otherwise("/");

        // app routes
        $stateProvider
            .state('home', {
                url: '/',
				views: {
					'' : {
						templateUrl: 'home/home.html',
						controller: 'Home.IndexController',
						controllerAs: 'vm',
					},
				}
            })
            .state('login', {
                url: '/login',
                templateUrl: 'login/login.html',
                controller: 'Login.IndexController',
                controllerAs: 'vm'
            })
			.state('logout', {
                url: '/logout',
                templateUrl: 'logout/logout.html',
                controller: 'Logout.IndexController',
                controllerAs: 'vm'
            })
			.state('denied', {
                url: "/accessdenied",
                templateUrl: 'home/accessdenied.html',
                controller: 'AccessDenied.IndexController',
                controllerAs: 'vm'
            })
			.state('teams', {
                url: '/teams/edit/{id}',
                templateUrl: 'oncall/oncall.html',
                controller: 'Oncall.IndexController',
                controllerAs: 'vm'
            });
    }
})();