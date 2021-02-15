angular
	.module('app')
	.factory('SonusService', ['$http', '$localStorage', '$stateParams', '$q', function($http, $localStorage, $stateParams, $q){
		
		var self = {};

		
		// Get SBC Call Summary
		self.activecallcounts= function() {
			var defer = $q.defer();
			return $http.get('../api/sonus/activecallcounts')
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
		self.listactivecalls = function() {
			var defer = $q.defer();
			return $http.get('../api/sonus/activecalls')
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
		self.listcallDetailStatus = function() {
			var defer = $q.defer();
			return $http.get('../api/sonus/listcallDetailStatus')
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
		self.listcallDetailStatus_Media = function() {
			var defer = $q.defer();
			return $http.get('../api/sonus/listcallDetailStatus_Media')
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
		
		
		// Get SBC Current Alarms
		self.listactivealarms = function() {
			var defer = $q.defer();
			return $http.get('../api/sonus/activealarms')
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
