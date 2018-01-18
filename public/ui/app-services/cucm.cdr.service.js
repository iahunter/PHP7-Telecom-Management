angular
	.module('app')
	.factory('cucmCdrService', ['$http', '$localStorage', '$stateParams', '$q', function($http, $localStorage, $stateParams, $q){
		
		var self = {};


		
		// Get Site Summary
		self.list_last_24hr_calls_with_loss = function() {
			var defer = $q.defer();
			return $http.get('../api/cucmcdrs/list_last_24hr_calls_with_loss')
				.then(function successCallback(response) {
					defer.resolve(response);
					
					// Must return the promise to the controller. 
					return defer.promise;
					
			  }, function errorCallback(response) {
					defer.resolve(response);
					return defer.promise;
			  });
		}


		return self

	}]);
