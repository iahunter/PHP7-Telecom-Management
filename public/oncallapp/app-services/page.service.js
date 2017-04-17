angular
	.module('app')
	.factory('PageService', ['$http', '$localStorage', '$stateParams', '$q', function($http, $localStorage, $stateParams, $q){
		
		var self = {};

		// Get SBC Call Summary
		self.getpage = function(name) {
			var defer = $q.defer();
			return $http.get('../api/page/request/' + name)
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
		self.getpagestate = function(state) {
			var defer = $q.defer();
			return $http.get('../api/page/request' + state)
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
		

		// Test
		self.gettest = function() {
			var defer = $q.defer();
			return $http.get('../api/page/test')
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