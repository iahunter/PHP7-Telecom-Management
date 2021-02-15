﻿(function () {
    'use strict';

    angular
        .module('app')
        .factory('AuthenticationService', Service);

    function Service($http, $localStorage) {
        var service = {};

        service.Login = Login;
        service.Logout = Logout;
		
		service.Renew = Renew;

        

        function Login(username, password, callback) {
            $http.post('../api/authenticate', { username: username, password: password })								// *** Point the Authenticate URL to your API Location****
                .success(function (response) {
                    // login successful if there's a token in the response
                    if (response.token) {
                        // store username and token in local storage to keep user logged in between page refreshes
                        $localStorage.currentUser = { token: response.token };

                        // add jwt token to auth header for all requests made by the $http service
                        $http.defaults.headers.common.Authorization = 'Bearer ' + response.token;

                        // execute callback with true to indicate successful login
                        callback(response);
                    } else {
                        // we should get a token if the call succeeds
                        alert('login call succeeded but failed to return token');
                    }
                })
				// execute callback with false to indicate failed login
				.error(function(response) {
					callback(response);
				});
        }

        function Logout() {
            // remove user from local storage and clear http auth header
            delete $localStorage.currentUser;
            $http.defaults.headers.common.Authorization = '';
        }
		
		function Renew(token, callback) {
            $http.get('../api/authenticate/renew?token=' + token)								// *** Point the Authenticate URL to your API Location****
                .success(function (response) {
                    // login successful if there's a token in the response
                    if (response.token) {
                        // store username and token in local storage to keep user logged in between page refreshes
                        $localStorage.currentUser = { token: response.token };

                        // add jwt token to auth header for all requests made by the $http service
                        $http.defaults.headers.common.Authorization = 'Bearer ' + response.token;

                        // execute callback with true to indicate successful login
                        callback(response);
                    } else {
                        // we should get a token if the call succeeds
                        alert('login call succeeded but failed to return token');
                    }
                })
				// execute callback with false to indicate failed login
				.error(function(response) {
					callback(response);
				});
        }
		
		return service;
    }
})();
