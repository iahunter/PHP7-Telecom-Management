angular
	.module('app')
	.factory('west911EnableService', ['$http', '$localStorage', '$stateParams', '$q', '$state', function($http, $localStorage, $stateParams, $q, $state){
		
		var self = {};

		// Get Call Stats from the DB
		self.list_erls = function() {
			var defer = $q.defer();
			return $http.get('../api/egw/erls/all')
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
		
		
		// Get Call Stats from the DB
		self.list_erls_and_phone_counts = function() {
			var defer = $q.defer();
			return $http.get('../api/egw/list_erls_and_phone_count_by_erl')
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