angular
	.module('app')
	.factory('PermissionsService', ['$http', '$localStorage', '$stateParams', '$q', function($http, $localStorage, $stateParams, $q){
		
		var self = {};

		// Get SBC Call Summary
		self.getuserspermissions = function(state) {
			var defer = $q.defer();
			return $http.get('../api/bouncer/permissions')
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
