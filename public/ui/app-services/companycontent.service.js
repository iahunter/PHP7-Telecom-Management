angular
	.module('app')
	.factory('CompanyService', ['$http', '$localStorage', '$stateParams', '$q', function($http, $localStorage, $stateParams, $q){
		
		var self = {};

		// Get Current Team name and oncall numbers from json file
		self.getcompanycontent = function() {
			var defer = $q.defer();
			return $http.get('../ui/company-content/company.json')
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
		return self

	}]);