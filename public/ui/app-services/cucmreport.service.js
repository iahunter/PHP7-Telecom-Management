angular
	.module('app')
	.factory('cucmReportService', ['$http', '$localStorage', '$stateParams', '$q', function($http, $localStorage, $stateParams, $q){
		
		var self = {};

		
		// Get Call Stats from the DB
		self.listsitesummary = function() {
			var defer = $q.defer();
			return $http.get('../api/reports/sites')
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
		self.listsitetrunkingreport = function() {
			var defer = $q.defer();
			return $http.get('../api/reports/siteE911TrunkingReport')
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
