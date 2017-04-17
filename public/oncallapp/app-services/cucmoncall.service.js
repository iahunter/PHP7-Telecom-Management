angular
	.module('app')
	.factory('CUCMOncallService', ['$http', '$localStorage', '$stateParams', '$q', function($http, $localStorage, $stateParams, $q){
		
		var self = {};

		// Get Current CTI Route point name
		self.getctiroutepoint = function(name) {
			var defer = $q.defer();
			return $http.get('../api/cucm/ctiroutepoint/' + name)
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
		
		// Modify CTI Route Point
		
		
		return self

	}]);