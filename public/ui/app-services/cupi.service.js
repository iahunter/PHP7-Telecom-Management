angular
	.module('app')
	.factory('cupiService', ['$http', '$localStorage', '$stateParams', '$q', function($http, $localStorage, $stateParams, $q){
		
		var self = {};


		// Get Site Summary
		self.getsitesummary = function(name) {
			var defer = $q.defer();
			return $http.get('../api/cupi/site/summary/'+name)
				.then(function successCallback(response) {
					defer.resolve(response);
					
					// Must return the promise to the controller. 
					return defer.promise;
					
			  }, function errorCallback(response) {
					
			  });
		}
		
		// List User Templates
		self.listusertemplatesnames = function() {
			var defer = $q.defer();
			return $http.get('../api/cupi/templates/listusertemplatesnames')
				.then(function successCallback(response) {
					defer.resolve(response);
					
					// Must return the promise to the controller. 
					return defer.promise;
					
			  }, function errorCallback(response) {
					
			  });
		}
		
		// Get User
		self.getuser = function(alias) {
			var defer = $q.defer();
			return $http.get('../api/cupi/user/search/'+alias)
				.then(function successCallback(response) {
					defer.resolve(response);
					// Must return the promise to the controller. 
					return defer.promise;
					
			  }, function errorCallback(response) {
			
			});
		}
		
		// Get User
		self.getldapuser = function(alias) {
			var defer = $q.defer();
			return $http.get('../api/cupi/user/getLDAPUserbyAlias/'+alias)
				.then(function successCallback(response) {
					defer.resolve(response);
					// Must return the promise to the controller. 
					return defer.promise;
					
			  }, function errorCallback(response) {
			
			});
		}
		
		
		// Get User External Services / Unified Messaging
		self.getuserunifiedmessaging = function(id) {
			var defer = $q.defer();
			return $http.get('../api/cupi/user/getuserunifiedmessaging/'+id)
				.then(function successCallback(response) {
					defer.resolve(response);
					// Must return the promise to the controller. 
					return defer.promise;
					
			  }, function errorCallback(response) {
			
			});
		}
		
		
		// List External Services / Unified Messaging Services
		self.listexternalservices = function(id) {
			var defer = $q.defer();
			return $http.get('../api/cupi/listexternalservices')
				.then(function successCallback(response) {
					defer.resolve(response);
					// Must return the promise to the controller. 
					return defer.promise;
					
			  }, function errorCallback(response) {
			
			});
		}
		
		
		
		
		// Get CUCM Date Time Groups
		self.getcupidatetimegrps = function() {
			var defer = $q.defer();
			return $http.get('../api/cupi/dateandtime')
				.then(function successCallback(response) {
					defer.resolve(response);
					
					// Must return the promise to the controller. 
					return defer.promise;
					
			  }, function errorCallback(response) {
					
			  });
		}
		

		
		// Create Block
		self.createcupisite = function(site){
			return $http.post('../api/cupi/site', site);
		}
		
	
	
		// Delete Phone
		self.deletephone = function(name) {
			var defer = $q.defer();
			console.log('Service - Deleting ID: '+ name);
			return $http.delete('../api/cupi/phone/'+name, name)
				.then(function successCallback(response) {
					defer.resolve(response);
					// Must return the promise to the controller. 
					return defer.promise;
					
			  }, function errorCallback(response) {
			
			});
		}


		
		// Get CUCM Date Time Groups
		self.initiate_cupi_ldap_sync = function() {
			var defer = $q.defer();
			return $http.get('../api/cupi/ldap/start')
				.then(function successCallback(response) {
					defer.resolve(response);
					
					// Must return the promise to the controller. 
					return defer.promise;
					
			  }, function errorCallback(response) {
					
			  });
		}
		
		
		// Create Block
		self.createphone = function(phone){
			return $http.post('../api/cupi/phone', phone);
		}
		
		self.createphones = function(arr) {
			results = [];
			  if (angular.isArray(arr) && arr.length > 0) {
				console.log(arr);
				var postdata = arr[0];
				$http.post('../api/cupi/phone', postdata)
				  .then(
					  function(data) {
						results.push(data);
						console.log("Success.");
						arr.shift();
						self.createphones(arr);
						//console.log('After Shift');
						//console.log(arr);
					  },
					  function(data) {
						results.push(data);
						console.log("Failure.");
						// if you want to continue even if it fails:
						self.createphones(arr.shift());
					  }
				);
			  }
			console.log(results);
			return results;
		}
		
		
		
		return self

	}]);
