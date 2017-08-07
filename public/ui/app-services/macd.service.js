angular
	.module('app')
	.factory('macdService', ['$http', '$localStorage', '$stateParams', '$q', function($http, $localStorage, $stateParams, $q){
		
		// Service for Phone MACDs. 
		var self = {};

		// Get all MACD Parents and children for the week
		self.list_macds_week = function() {
			var defer = $q.defer();
			return $http.get('../api/cucm/macd/list/week')
				.then(function successCallback(response) {
					defer.resolve(response);
					
					// Must return the promise to the controller. 
					return defer.promise;
					
			  }, function errorCallback(response) {
					defer.resolve(response);
					return defer.promise;
			  });
		}
		// Get all MACD Parents for the week
		self.list_macds_week = function() {
			var defer = $q.defer();
			return $http.get('../api/cucm/macd/parentlist/week')
				.then(function successCallback(response) {
					defer.resolve(response);
					
					// Must return the promise to the controller. 
					return defer.promise;
					
			  }, function errorCallback(response) {
					defer.resolve(response);
					return defer.promise;
			  });
		}
		
		// Get all MACDs Parents and Children for the week for user
		self.list_macds_week_by_user = function(name) {
			var defer = $q.defer();
			return $http.get('../api/cucm/macd/list/week/user')
				.then(function successCallback(response) {
					defer.resolve(response);
					// Must return the promise to the controller. 
					return defer.promise;
					
			  }, function errorCallback(response) {
					defer.resolve(response);
					return defer.promise;
			});
		}
		
		// Get  MACD Parents for the week for user
		self.list_my_macd_parents_for_week = function(name) {
			var defer = $q.defer();
			return $http.get('../api/cucm/macd/parentlist/week/user')
				.then(function successCallback(response) {
					defer.resolve(response);
					// Must return the promise to the controller. 
					return defer.promise;
					
			  }, function errorCallback(response) {
					defer.resolve(response);
					return defer.promise;
			});
		}
		
		// Get Dids by Block ID
		self.list_macd_and_children_by_id = function(id) {
			var defer = $q.defer();
			return $http.get('../api/cucm/macd/list/tasks/' + id)
				.then(function successCallback(response) {
					defer.resolve(response);
					// Must return the promise to the controller. 
					return defer.promise;
					
			  }, function errorCallback(response) {
					defer.resolve(response);
					return defer.promise;
			});
		}

		// Create
		self.create_macd_add = function(data){
			return $http.post('../api/cucm/macd/add', data);
		}

		return self

	}]);
