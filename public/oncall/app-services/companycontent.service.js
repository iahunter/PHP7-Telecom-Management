angular
	.module('app')
	.factory('CompanyService', ['$http', '$localStorage', '$stateParams', '$q', function($http, $localStorage, $stateParams, $q){
		
		var self = {};

		// Get Current Team name and oncall numbers from json file
		self.getcompanycontent = function() {
			var defer = $q.defer();
			return $http.get('../oncall/company-content/company.json')
				.then(function successCallback(response) {
					defer.resolve(response);
					
					//console.log(response);
					// Must return the promise to the controller. 
					return defer.promise;
					
					/* Example: company.json
						{
							"sbcconfigs": "//servername/websvn/listing.php?repname=Sonus+Repository"
						}
					*/
					
			  }, function errorCallback(response) {
					defer.resolve(response);
					return defer.promise;
			  });
		}
		
		// Get Current Team name and oncall numbers from json file
		self.getgoogleanalyticsid = function() {
			var defer = $q.defer();
			return $http.get('../oncall/company-content/analytics.json')
				.then(function successCallback(response) {
					defer.resolve(response);
					
					//console.log(response);
					// Must return the promise to the controller. 
					return defer.promise;
					
					/* Example: analytics.json
						{
							"id": "UA-XXXXXXXX-X"
						}
					*/
					
			  }, function errorCallback(response) {
					defer.resolve(response);
					return defer.promise;
			  });
		}
		
		return self

	}]);