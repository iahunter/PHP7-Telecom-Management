angular
	.module('app')
	.factory('siteService', ['$http', '$localStorage', '$stateParams', '$q', '$state', function($http, $localStorage, $stateParams, $q, $state){
		
		var self = {};

		
		// Get Sites
		self.Getsites = function() {
			var defer = $q.defer();
			return $http.get('../api/site')
				.then(function successCallback(response) {
					defer.resolve(response);
					
					// Must return the promise to the controller. 
					return defer.promise;
					
			  }, function errorCallback(response) {
					defer.resolve(response);
					return defer.promise;
			  });
		}

		// Update Block by ID
		self.getsite = function(id) {
			var defer = $q.defer();
			return $http.get('../api/site/'+id)
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
		self.getsitephones = function(id) {
			var defer = $q.defer();
			return $http.get('../api/site/'+id+'/phones')
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
		self.getsitephoneplans = function(id) {
			var defer = $q.defer();
			return $http.get('../api/site/'+id+'/phoneplans')
				.then(function successCallback(response) {
					defer.resolve(response);
					// Must return the promise to the controller. 
					return defer.promise;
					
			  }, function errorCallback(response) {
					defer.resolve(response);
					return defer.promise;
			});
		}


		// Needs work! 
		self.getdidblocks = function() {
			var defer = $q.defer();
			return $http.get('../api/didblock')
				.then(function successCallback(response) {
					defer.resolve(response);
					
					// Must return the promise to the controller. 
					return defer.promise;
					
			  }, function errorCallback(response) {
					defer.resolve(response);
					return defer.promise;
			  });
		}

	
		
		
		// Create Block
		self.createsite = function(site) {
			
			return $http.post('../api/site',site);
		}
		
		
		// Update Block by ID
		self.updatesite = function(id, update) {
        
			return $http.put('../api/site/'+id, update).then(function(response) {

				var data = response.data;
				return data;

			 }, function(error) {return false;});
		}

		
		// Delete Block by ID
		self.deletesite = function(id) {
			console.log('Service - Deleting ID: '+ id);
			return $http.delete('../api/site/'+id, id).then(function(response) {

				var data = response.data;
				return data;

			 });
		}


		return self

	}]);
