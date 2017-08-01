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
					defer.resolve(response);
					return defer.promise;
			  });
		}
		
		// List User Templates
		self.listusertemplatesnames = function() {
			var defer = $q.defer();
			return $http.get('../api/cupi/usertemplates/names')
				.then(function successCallback(response) {
					defer.resolve(response);
					
					// Must return the promise to the controller. 
					return defer.promise;
					
			  }, function errorCallback(response) {
					defer.resolve(response);
					return defer.promise;
			  });
		}
		
		// List User Templates by Sitecode
		self.listusertemplatesbysite = function(sitecode) {
			var defer = $q.defer();
			return $http.get('../api/cupi/usertemplates/listusertemplatesbysite/'+sitecode)
				.then(function successCallback(response) {
					defer.resolve(response);
					
					// Must return the promise to the controller. 
					return defer.promise;
					
			  }, function errorCallback(response) {
					defer.resolve(response);
					return defer.promise;
			  });
		}
		
		// List User Templates by Sitecode
		self.listtimezones = function() {
			var defer = $q.defer();
			return $http.get('../api/cupi/timezones')
				.then(function successCallback(response) {
					defer.resolve(response);
					
					// Must return the promise to the controller. 
					return defer.promise;
					
			  }, function errorCallback(response) {
					defer.resolve(response);
					return defer.promise;
			  });
		}
		
		// Create User Template
		self.createusertemplate = function(template){
			return $http.post('../api/cupi/usertemplate/create', template);
		}
		
		// Create User Template
		self.createusertemplatesforsite = function(template){
			return $http.post('../api/cupi/usertemplate/site', template);
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
					defer.resolve(response);
					return defer.promise;
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
					defer.resolve(response);
					return defer.promise;
			});
		}
		
		
		// Get User mailbox by extension
		self.getmailboxbyextension = function(extension) {
			var defer = $q.defer();
			return $http.get('../api/cupi/user/extension/'+extension)
				.then(function successCallback(response) {
					defer.resolve(response);
					// Must return the promise to the controller. 
					return defer.promise;
					
			  }, function errorCallback(response) {
					defer.resolve(response);
					return defer.promise;
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
					defer.resolve(response);
					return defer.promise;
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
					defer.resolve(response);
					return defer.promise;
			});
		}
		
		

		
		// Create Block
		self.createuser = function(user){
			return $http.post('../api/cupi/user/create', user);
		}
		
		// Create Block
		self.importldapuser = function(user){
			return $http.post('../api/cupi/user/ldapimport', user);
		}
		
		
		// Create Block
		self.deleteuser = function(user){
			return $http.delete('../api/cupi/user/delete/' + user);
		}
		
		
		
		return self

	}]);
