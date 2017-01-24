angular
	.module('app')
	.factory('SonusService', ['$http', '$localStorage', '$stateParams', '$q', function($http, $localStorage, $stateParams, $q){
		
		var self = {};

		
		// Get Site Summary
		self.listactivecalls = function() {
			var defer = $q.defer();
			return $http.get('../api/sonus/activecalls')
				.then(function successCallback(response) {
					defer.resolve(response);
					
					//console.log(response);
					// Must return the promise to the controller. 
					return defer.promise;
					
			  }, function errorCallback(response) {
					
			  });
		}

		return self

	}]);
