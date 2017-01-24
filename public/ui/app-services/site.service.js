angular
	.module('app')
	.factory('siteService', ['$http', '$localStorage', '$stateParams', '$q', '$state', function($http, $localStorage, $stateParams, $q, $state){
		
		var self = {};

		self.Getsites = Getsites;

		function Getsites(callback) {
			self.sites = {};
			GetType(callback, 'site');
		}

		function GetType(callback, type) {
			self.sites[type] = {};
			return $http.get('../api/' + type)
				.success(function (response) {
					self.sites = response.sites;
					//console.log(self.sites);
					callback(true);
				})
				// execute callback with false to indicate failed call
				.error(function() {
					callback(false);
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
