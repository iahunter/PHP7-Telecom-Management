angular
	.module('app')
	.factory('LogService', ['$http', '$localStorage', '$stateParams', '$q', function($http, $localStorage, $stateParams, $q){
		
		var self = {};

		// Get SBC Call Summary
		self.getlast24hrlogs = function(state) {
			var defer = $q.defer();
			return $http.get('../api/activitylogs/last24hrs')
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
		
		
		// Get SBC Call Summary
		self.getlast24hrpagelogs = function(state) {
			var defer = $q.defer();
			return $http.get('../api/activitylogs/pagelogs/last24hrs')
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
