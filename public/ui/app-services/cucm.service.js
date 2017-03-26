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
		
		// Get Object by Type and Name
		self.get_object_type_by_name = function(name, type) {
			var defer = $q.defer();
			return $http.get('../api/cucm/search/'+type+'/'+name)
				.then(function successCallback(response) {
					defer.resolve(response);
					// Must return the promise to the controller. 
					return defer.promise;
					
			  }, function errorCallback(response) {
			
			});
		}
		
		// Get Object by Type and Name
		self.get_object_type_by_uuid = function(uuid, type) {
			var defer = $q.defer();
			return $http.get('../api/cucm/searchuuid/'+type+'/'+uuid)
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


		
		//  Start LDAP Sync Process
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
		
		// Get LDAP Sync Status
		self.get_cucm_ldap_sync_status = function() {
			var defer = $q.defer();
			return $http.get('../api/cucm/ldap/status')
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
		
		
		// Create phones - Had to make the posts in series or Informix was throwing errors. 
		
		self.clearphoneadds = function(){
			return self.results = [];
		}
		
		
		self.results = [];
		self.createphones = function(phones, ignoreexisting) {
				if (angular.isArray(phones) && phones.length > 0) {
					console.log("PHONES")
					console.log(phones);
					var postdata = phones[0];
					if (ignoreexisting == true){
						if (postdata.inuse == false){
							//phone.inuse = false
							$http.post('../api/cucm/phone', postdata)
							  .then(
								  function(data) {
									self.results.push(data.data.response);
									console.log("Success.");
									phones.shift();
									self.createphones(phones, ignoreexisting);
									//console.log('After Shift');
									//console.log(phones);
								  },
								  function(data) {
									self.results.push(data.data.response);
									console.log("Failure.");
									// if you want to continue even if it fails:
									
									self.createphones(phones.shift(), ignoreexisting);
								  }
							);
						}else{
							var phone = {};
							phone.skipped = true;
							phone.Phone = {};
							phone.Phone.request = postdata;
							phone.Phone.skipped = true;
							phone.Line = {};
							phone.Line.skipped = true;
							self.results.push(phone);
							phones.shift();
							self.createphones(phones, ignoreexisting);
						}
					}else{
						//phone.inuse = true
						$http.post('../api/cucm/phone', postdata)
						  .then(
							  function(data) {
								self.results.push(data.data.response);
								console.log("Success.");
								phones.shift();
								self.createphones(phones, ignoreexisting);
								//console.log('After Shift');
								//console.log(phones);
							  },
							  function(data) {
								self.results.push(data.data.response);
								console.log("Failure.");
								// if you want to continue even if it fails:
								self.createphones(phones.shift(), ignoreexisting );
							  }
						);
					}
					
					
				}
			
			console.log(self.results);
			return self.results;
		}
		
		
		
		return self

	}]);
