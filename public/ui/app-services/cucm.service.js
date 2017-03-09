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
		self.getsitephones = function(name) {
			var defer = $q.defer();
			return $http.get('../api/cucm/site/summary/'+name)
				.then(function successCallback(response) {
					defer.resolve(response);
					// Must return the promise to the controller. 
					return defer.promise;
					
			  }, function errorCallback(response) {
			
			});
		}
		
		// Get Dids by Block ID
		self.getphone = function(name) {
			var defer = $q.defer();
			return $http.get('../api/cucm/phone/'+name)
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
		self.createcucmsite = function(site){
			return $http.post('../api/cucm/site', site);
		}
		
	
	
		// Delete Phone
		self.deletephone = function(name) {
			var defer = $q.defer();
			console.log('Service - Deleting ID: '+ name);
			return $http.delete('../api/cucm/phone/'+name, name)
				.then(function successCallback(response) {
					defer.resolve(response);
					// Must return the promise to the controller. 
					return defer.promise;
					
			  }, function errorCallback(response) {
			
			});
		}


		
		// Get CUCM Date Time Groups
		self.initiate_cucm_ldap_sync = function() {
			var defer = $q.defer();
			return $http.get('../api/cucm/ldap/start')
				.then(function successCallback(response) {
					defer.resolve(response);
					
					// Must return the promise to the controller. 
					return defer.promise;
					
			  }, function errorCallback(response) {
					
			  });
		}
		
		
		// Create Block
		self.createphone = function(phone){
			return $http.post('../api/cucm/phone', phone);
		}
		
		
		// Create phones - Had to make the posts inline or Informix was throwing errors. 
		self.results = [];
		self.createphones = function(arr) {
			  if (angular.isArray(arr) && arr.length > 0) {
				console.log(arr);
				var postdata = arr[0];
				$http.post('../api/cucm/phone', postdata)
				  .then(
					  function(data) {
						self.results.push(data.data.response);
						console.log("Success.");
						arr.shift();
						self.createphones(arr);
						//console.log('After Shift');
						//console.log(arr);
					  },
					  function(data) {
						self.results.push(data.data.response);
						console.log("Failure.");
						// if you want to continue even if it fails:
						self.createphones(arr.shift());
					  }
				);
			  }
			console.log(self.results);
			return self.results;
		}
		
		
		
		return self

	}]);
