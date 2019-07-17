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
					defer.resolve(response);
					return defer.promise;
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
					defer.resolve(response);
					return defer.promise;
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
					defer.resolve(response);
					return defer.promise;
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
					defer.resolve(response);
					return defer.promise;
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
					defer.resolve(response);
					return defer.promise;
			});
		}
		
		// Reset Phone by Name
		self.resetphone = function(name) {
			var defer = $q.defer();
			return $http.get('../api/cucm/resetphone/'+name)
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
		self.getphone = function(name) {
			var defer = $q.defer();
			return $http.get('../api/cucm/phone/'+name)
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
		self.searchphones = function(key,search) {
			var defer = $q.defer();
			return $http.get('../api/cucm/phone_search_by_key/'+key+'/'+search)
				.then(function successCallback(response) {
					defer.resolve(response);
					// Must return the promise to the controller. 
					return defer.promise;
					
			  }, function errorCallback(response) {
					defer.resolve(response);
					return defer.promise;
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
					defer.resolve(response);
					return defer.promise;
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
					defer.resolve(response);
					return defer.promise;
			});
		}
		
		// Delete Line by UUID
		self.deletelinebyuuid = function(uuid) {
			var defer = $q.defer();
			console.log('Service - Deleting ID: '+ uuid);
			return $http.delete('../api/cucm/line/'+uuid, uuid)
				.then(function successCallback(response) {
					defer.resolve(response);
					// Must return the promise to the controller. 
					return defer.promise;
					
			  }, function errorCallback(response) {
					defer.resolve(response);
					return defer.promise;
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
					defer.resolve(response);
					return defer.promise;
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
					defer.resolve(response);
					return defer.promise;
			  });
		}
		
		// Get Number usage from Route Plan Report
		self.getNumberbyRoutePlan = function(number) {
			var defer = $q.defer();
			return $http.get('../api/cucm/routeplan/summary/'+number)
				.then(function successCallback(response) {
					defer.resolve(response);
					// Must return the promise to the controller. 
					return defer.promise;
					
			  }, function errorCallback(response) {
					defer.resolve(response);
					return defer.promise;
			});
		}
		
		// Get Number usage from Route Plan Report
		self.getNumberandDeviceDetailsbyRoutePlan = function(number) {
			var defer = $q.defer();
			return $http.get('../api/cucm/routeplan/details/'+number)
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
		self.createphone = function(phone){
			return $http.post('../api/cucm/phone', phone);
		}
		
		// Create Block
		self.createline = function(line){
			return $http.post('../api/cucm/line', line);
		}
		
		// Create Block
		self.phonecheck = function(phones){
			return $http.post('../api/cucm/phonecheck', phones);
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
							$http.post('../api/cucm/phone_and_line', postdata)
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
						$http.post('../api/cucm/phone_and_line', postdata)
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
		
		
		self.updatephones = function(phones) {
			
			if (angular.isArray(phones) && phones.length > 0) {
				console.log("PHONES")
				console.log(phones);
				var postdata = {};
				postdata.phone = phones[0]
				
				//phone.inuse = true
				$http.put('../api/cucm/phone', postdata)
				  .then(
					  function(data) {
						
						var phone = data.data
						
						self.results.push(phone);
						
						console.log("Success.");
						phones.shift();
						self.updatephones(phones);
						//console.log('After Shift');
						//console.log(phones);
					  },
					  function(data) {
						self.results.push(data.data);
						console.log("Failure.");
						console.log(data)
						// if you want to continue even if it fails:
						self.updatephones(phones.shift());
					  }
				);
			}
			
			console.log(self.results);
			return self.results;
		}
		
		self.lineresults = [];
		self.updatelines = function(lines) {
			//console.log('service')
			//console.log(lines);
			if (angular.isArray(lines) && lines.length > 0 ) {
				console.log("LINES")
				console.log(lines);
				var postdata = {};
				postdata.line = lines[0]
				
				//phone.inuse = true
				$http.put('../api/cucm/line', postdata)
				  .then(
					  function(data) {
						self.lineresults.push(data.data);
						
						console.log("Success.");
						lines.shift();
						self.updatelines(lines);
						//console.log('After Shift');
						//console.log(lines);
					  },
					  function(data) {
						self.lineresults.push(data.data);
						console.log("Failure.");
						console.log(data)
						// if you want to continue even if it fails:
						self.updatelines(lines.shift());
					  }
				);
			}
			
			console.log(self.lineresults);
			return self.lineresults;
		}
		
		// Get Call Stats from the DB
		self.daysgatewaycallstats = function() {
			var defer = $q.defer();
			return $http.get('../api/cucm/gatewaycalls/dayscallstats')
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
		self.weeksgatewaycallstats = function() {
			var defer = $q.defer();
			return $http.get('../api/cucm/gatewaycalls/weekscallstats')
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
		
		// Get Number usage from Route Plan Report
		self.getLocalUser = function(username) {
			var defer = $q.defer();
			return $http.get('../api/cucm/user/'+username)
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
