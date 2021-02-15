angular
	.module('app')
	.factory('pingMonitoringService', ['$http', '$localStorage', '$stateParams', '$q', '$state', function($http, $localStorage, $stateParams, $q, $state){
		
		var self = {};

		
		// Get Device by ID
		self.getDevice = function(host) {
			var defer = $q.defer();
			return $http.get('../api/pinghost/'+host)
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
