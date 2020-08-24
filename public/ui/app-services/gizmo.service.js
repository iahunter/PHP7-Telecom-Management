angular
	.module('app')
	.factory('gizmoService', ['$http', '$localStorage', '$stateParams', '$q', function($http, $localStorage, $stateParams, $q){
		
		var self = {};


		// Get Site Summary
		self.getTeamsUserbyNumber = function(countrycode, number) {
			var defer = $q.defer();
			return $http.get('../api/gizmo/teams/number/'+countrycode+'/'+number)
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
