angular
	.module('app')
	.factory('siteMigrationService', ['$http', '$localStorage', '$stateParams', '$q', '$state', function($http, $localStorage, $stateParams, $q, $state){
		
		var self = {};

		// Get Sites
		self.listSiteMigrations = function() {
			var defer = $q.defer();
			return $http.get('../api/site_migration')
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
		self.getSiteMigration = function(id) {
			var defer = $q.defer();
			return $http.get('../api/site_migration/'+id)
				.then(function successCallback(response) {
					defer.resolve(response);
					
					// Must return the promise to the controller. 
					return defer.promise;
					
			  }, function errorCallback(response) {
					defer.resolve(response);
					return defer.promise;
			  });
		}
		
		// List Migrations by Sitecode
		self.listSiteMigrationsBySitecode = function(sitecode) {
			var defer = $q.defer();
			return $http.get('../api/site_migrations/'+sitecode)
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
		self.createSiteMigration = function(migration) {
			
			return $http.post('../api/site_migration',migration);
		}
		
		
		// Update Block by ID
		self.updateSiteMigration = function(id, update) {
        
			return $http.put('../api/site_migration/'+id, update).then(function(response) {

				var data = response.data;
				return data;

			 }, function(error) {return false;});
		}

		
		// Delete Block by ID
		self.deleteSiteMigration = function(id) {
			console.log('Service - Deleting ID: '+ id);
			return $http.delete('../api/site_migration/'+id, id).then(function(response) {

				var data = response.data;
				return data;

			 }, function(error) {return false;});
		}
		
		// Create Block
		self.getSiteMigrationSummary = function(migration) {
			var defer = $q.defer();
			return $http.post('../api/cucm/site/migration/summary',migration)
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
		self.runMigration = function(migration) {
			var defer = $q.defer();
			return $http.post('../api/cucm/site/migration/run',migration)
			.then(function successCallback(response) {
					defer.resolve(response);
					
					// Must return the promise to the controller. 
					return defer.promise;
					
			  }, function errorCallback(response) {
					defer.resolve(response);
					return defer.promise;
			  });
		}

		return self

	}]);
