﻿(function () {
    'use strict';

    angular
        .module('app', ['ui.router', 'ngMessages', 'ngStorage', 'angular-jwt', 'chart.js', 'ngAnimate'])
        .config(config)
        .run(run);

		
	function run(PageService, CompanyService, $rootScope, $window, $http, $location, $localStorage, jwtHelper, AuthenticationService) {
        // keep user logged in after page refresh
        if ($localStorage.currentUser) {
			console.log('Found local storage login token: ' + $localStorage.currentUser.token);
			

			//Permissions Checker/
			var tokenPayload = jwtHelper.decodeToken($localStorage.currentUser.token);
			window.telecom_mgmt_permissions = tokenPayload.permissions;
			window.telecom_user = tokenPayload.user;

			
			// Look at checking date expire and renew automatically. 
			var date = jwtHelper.getTokenExpirationDate($localStorage.currentUser.token);
			
			console.log(date);
			
			if (jwtHelper.isTokenExpired($localStorage.currentUser.token)) {
				console.log('Cached token is expired, logging out');
				delete $localStorage.currentUser;
				$http.defaults.headers.common.Authorization = '';
				$location.path('/logout');
			}else{
				console.log('app.js Cached token is still valid');
				$http.defaults.headers.common.Authorization = 'Bearer ' + $localStorage.currentUser.token;
				
				// Attempt to Renew Token
				AuthenticationService.Renew($localStorage.currentUser.token, function (result) {
					//console.log('Attempting to renew Token')
					if(result.token){
						
						//Permissions Checker/
						var tokenPayload = jwtHelper.decodeToken($localStorage.currentUser.token);
						window.telecom_mgmt_permissions = tokenPayload.permissions;

						
						// Look at checking date expire and renew automatically. 
						var date = jwtHelper.getTokenExpirationDate($localStorage.currentUser.token);
						
						//console.log(date);
						
						if (jwtHelper.isTokenExpired($localStorage.currentUser.token)) {
							console.log('Cached token is expired, logging out');
							delete $localStorage.currentUser;
							$http.defaults.headers.common.Authorization = '';
							$location.path('/logout');
						}else{
							console.log('Cached token is still valid');
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
				dimensionValue = dimensionValue.toString();
				
				//console.log('User: ' + dimensionValue)
				
					

				// Set our google analytics id
				$window.ga('create', google, {'userId': dimensionValue});
				
				$rootScope.$on('$stateChangeSuccess', function (event) {
					
					//console.log("Send Google Analytics")
					$window.ga('set', 'dimension1', dimensionValue);
					$window.ga('send', 'pageview', $location.path());
					
					// Log our Page Requests to our database
					var application_name = "ui";
					var location = $location.path()
					location = location.split('/').join('~~~')	// replace / with ~~~ so we can send in url
					//console.log(location)
					PageService.getpage(application_name + "&" + location)
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

					/*
					Add Sonus Alarms to Home View
					'sonussplashpage@home': {
						templateUrl: 'sonus/sonus.splashpage.html',
						
						// Removed, included controler as inside html splashpage
						//controller: 'Sonus.AlarmController',
						//controllerAs: 'vm'
					}
					*/
					
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
			.state('didblock', {
                url: '/didblock',
                templateUrl: 'didblock/didblock.html',
                controller: 'Didblock.IndexController',
                controllerAs: 'vm'
            })
			.state('didblockcreate', {
                url: '/didblock/create',
                templateUrl: 'didblock/createdidblock.html',
                controller: 'Didblock.IndexController',
                controllerAs: 'vm'
            })
			.state('getdidblock', {
                url: "/didblock/{id}",
                templateUrl: 'didblock/getdidblock.html',
                controller: 'getDidblock.IndexController',
                controllerAs: 'vm'
            })
			.state('importdidblock', {
                url: "/importdidblock",
                templateUrl: 'didblock/bulkdidblock.html',
                controller: 'bulkdidblock.IndexController',
                controllerAs: 'vm'
            })
			.state('checkdidusage', {
                url: "/checkdidusage",
                templateUrl: 'didblock/usage/checkdidusage.html',
                controller: 'checkdidusage.IndexController',
                controllerAs: 'vm'
            })
			.state('checkdidblockusage', {
                url: "/checkdidblockusage",
                templateUrl: 'didblock/usage/checkdidblockusage.html',
                controller: 'checkdidblockusage.IndexController',
                controllerAs: 'vm'
            })
			.state('site', {
                url: '/site',
                templateUrl: 'siteplanning/site.html',
                controller: 'Site.IndexController',
                controllerAs: 'vm'
            })
			.state('sitecreate', {
                url: '/site/create',
                templateUrl: 'siteplanning/createsite.html',
                controller: 'Site.IndexController',
                controllerAs: 'vm'
            })
			.state('siteedit', {
                url: '/site/edit/{id}',
                templateUrl: 'siteplanning/editsite.html',
                controller: 'Site.IndexController',
                controllerAs: 'vm'
            })
			.state('getsite', {
                url: '/site/{id}',
                templateUrl: 'siteplanning/getsite.html',
                controller: 'getSite.IndexController',
                controllerAs: 'vm'
            })
			.state('sonusconfigs', {
                url: '/sonus/configrepo',
                templateUrl: 'company-content/sonusconfigrepo.html',
                //controller: 'siteTrunking911Report.IndexController',
                controllerAs: 'vm'
            })
			.state('reportshome', {
                url: '/reports',
                templateUrl: 'reports/reports.home.html',
                //controller: 'siteTrunking911Report.IndexController',
                controllerAs: 'vm'
            })
			.state('trunking911report', {
                url: '/reports/site-trunking-911-report',
                templateUrl: 'reports/site-trunking-911-report.html',
				controller: 'siteTrunking911Report.IndexController',
                controllerAs: 'vm'
            })
			.state('getphoneplan', {
                url: '/phoneplan/{id}',
                templateUrl: 'siteplanning/phoneplans/getphoneplan.html',
                controller: 'getPhonePlan.IndexController',
                controllerAs: 'vm'
            })
			.state('phoneplancreate', {
                url: '/phoneplan/create',
                templateUrl: 'siteplanning/phoneplans/createphoneplan.html',
                controller: 'getPhonePlan.IndexController',
                controllerAs: 'vm'
            })
			.state('importphones', {
                url: '/phoneplan/{id}/importphones',
                templateUrl: 'siteplanning/phoneplans/importphones.html',
                controller: 'importphones.IndexController',
                controllerAs: 'vm'
            })
			.state('admin', {
                url: "/admin",
                templateUrl: 'admin/admin.html',
                controller: 'Admin.IndexController',
                controllerAs: 'vm'
            })
			.state('infrastructure', {
                url: '/infrastructure',
                templateUrl: 'telecom-infrastructure/infrastructure.html',
                controller: 'Telecom.Infrastructure.Controller',
                controllerAs: 'vm'
            })
			.state('infrastructurecreate', {
                url: '/infrastructure/create',
                templateUrl: 'telecom-infrastructure/createdevice.html',
                controller: 'Telecom.Infrastructure.Controller',
                controllerAs: 'vm'
            })
			.state('infrastructureedit', {
                url: '/infrastructure/edit/{id}',
                templateUrl: 'telecom-infrastructure/editdevice.html',
                controller: 'Telecom.Infrastructure.Controller',
                controllerAs: 'vm'
            })
			.state('sonus/activecalls', {
                url: "/sonus/activecalls",
                templateUrl: 'sonus/sonus.activecalls.html',
                controller: 'Sonus.CallController',
                controllerAs: 'vm'
            })
			.state('sonus/activecalldetails', {
                url: "/sonus/activecalldetails",
                templateUrl: 'sonus/sonus.activecalldetails.html',
                controller: 'Sonus.CallDetailsController',
                controllerAs: 'vm'
            })
			.state('sonus/activealarms', {
                url: "/sonus/activealarms",
                templateUrl: 'sonus/sonus.activealarms.html',
                controller: 'Sonus.AlarmController',
                controllerAs: 'vm'
            })
			.state('sonus/callstats', {
                url: "/sonus/callstats",
                templateUrl: 'calls/calls.graph.html',
                controller: 'CallGraph.IndexController',
                controllerAs: 'vm'
            })
			.state('sonus/cdr/todays_calls_attempts', {
                url: "/sonus/cdr/todays_calls_attempts",
                templateUrl: 'sonus-cdrs/sonus.todays.attempts.cdr.html',
                controller: 'Sonus.Attempts.CDR.Controller',
                controllerAs: 'vm'
            })
			.state('sonus/cdr/todays_calls_with_pkt_loss', {
                url: "/sonus/cdr/todays_calls_with_pkt_loss",
                templateUrl: 'sonus-cdrs/sonus.today.pktloss.cdr.html',
                controller: 'Sonus.Pktloss.CDR.Controller',
                controllerAs: 'vm'
            })
			.state('sonus/cdr/todays_attempt_summary', {
                url: "/sonus/cdr/todays_attempt_summary",
                templateUrl: 'sonus-cdrs/attemptsummary.graph.html',
                controller: 'Sonus.AttemptSummary.CDR.Controller',
                controllerAs: 'vm'
            });

    }
	

    
})();