angular
	.module('app')
	.factory('cucmService', ['$http', '$localStorage', '$stateParams', '$q', function($http, $localStorage, $stateParams, $q){
		
		var self = {};


		// Get Site Summary
		self.getsitesummary = function(name) {
			var defer = $q.defer();
			return $http.get('../api/cucm/site/summary/'+name)
				.then(function successCallback(response) {
					defer.resolve(response);
					
					// Must return the promise to the controller. 
					return defer.promise;
					
			  }, function errorCallback(response) {
					
			  });
		}
		
		// Get Site Summary
		self.listcucmsites = function() {
			var defer = $q.defer();
			return $http.get('../api/cucm/sites')
				.then(function successCallback(response) {
					defer.resolve(response);
					
					// Must return the promise to the controller. 
					return defer.promise;
					
			  }, function errorCallback(response) {
					
			  });
		}
		
		// Get Dids by Block ID
		self.getsitephones = function(id) {
			var defer = $q.defer();
			return $http.get('../api/cucm/site/summary/'+name)
				.then(function successCallback(response) {
					defer.resolve(response);
					// Must return the promise to the controller. 
					return defer.promise;
					
			  }, function errorCallback(response) {
			
			});
		}
		
		
		// Get CUCM Date Time Groups
		self.getcucmdatetimegrps = function() {
			var defer = $q.defer();
			return $http.get('../api/cucm/dateandtime')
				.then(function successCallback(response) {
					defer.resolve(response);
					
					// Must return the promise to the controller. 
					return defer.promise;
					
			  }, function errorCallback(response) {
					
			  });
		}
		

		
		// Create Block
		self.createcucmsite = function(site) {
			
			return $http.post('../api/cucm/site', site);
		}
		
	

		/*
		// Delete Block by ID
		self.deletesite = function(id) {
			console.log('Service - Deleting ID: '+ id);
			return $http.delete('../api/site/'+id, id).then(function(response) {

				var data = response.data;
				return data;

			 });
		}
		*/
		
		
		return self

	}]);
