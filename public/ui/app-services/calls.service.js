angular
	.module('app')
	.factory('CallService', ['$http', '$localStorage', '$stateParams', '$q', function($http, $localStorage, $stateParams, $q){
		
		var self = {};

		
		// Get Call Stats from the DB
		self.listcallstats = function() {
			var defer = $q.defer();
			return $http.get('../api/calls/listcallstats')
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
		self.dayscallstats = function() {
			var defer = $q.defer();
			return $http.get('../api/calls/dayscallstats')
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
		self.weekscallstats = function() {
			var defer = $q.defer();
			return $http.get('../api/calls/weekscallstats')
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
		self.monthcallstats = function() {
			var defer = $q.defer();
			return $http.get('../api/calls/monthcallstats')
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
		self.monthdailypeakcallstats = function() {
			var defer = $q.defer();
			return $http.get('../api/calls/monthdailypeakcallstats')
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
		self.threemonthdailypeakcallstats = function() {
			var defer = $q.defer();
			return $http.get('../api/calls/threemonthdailypeakcallstats')
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
		
		// Get Call Stats from the DB - Fast but doesn't give sbc stats for each. 
		self.threemonthdailypeakcallstats_sql = function() {
			var defer = $q.defer();
			return $http.get('../api/calls/threemonthdailypeakcallstats_sql')
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
