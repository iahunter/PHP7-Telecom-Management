angular
	.module('app')
	.factory('LDAPService', ['$http', '$localStorage', '$stateParams', '$q', function($http, $localStorage, $stateParams, $q){
		
		var self = {};

		
		// Get Call Stats from the DB
		self.getusername = function(username) {
			var defer = $q.defer();
			return $http.get('../api/ldap/user/get/'+username)
				.then(function successCallback(response) {
					defer.resolve(response);
					
					//console.log(response);
					// Must return the promise to the controller. 
					return defer.promise;
					
			  }, function errorCallback(response) {
					defer.resolve(response);
					return defer.promise;
			  });
		}

		return self

	}]);