angular
	.module('app')
	.factory('TeamService', ['$http', '$localStorage', '$stateParams', '$q', function($http, $localStorage, $stateParams, $q){
		
		var self = {};

		// Get Current Team name and oncall numbers from json file
		self.getteamsnavbardata = function() {
			var defer = $q.defer();
			return $http.get('../oncallapp/company-content/teams.json')
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