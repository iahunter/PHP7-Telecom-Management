angular
	.module('app')
	.factory('telephonyService', ['$http', '$localStorage', '$stateParams', '$q', function($http, $localStorage, $stateParams, $q){
		
		var self = {};

		/*
		self.GetDidblocks = GetDidblocks;

		function GetDidblocks(callback) {
			self.didblocks = {};
			GetType(callback, 'didblock');
		}

		function GetType(callback, type) {
			self.didblocks[type] = {};
			return $http.get('../api/' + type)
				.success(function (response) {
					self.didblocks = response;
					callback(true);
				})
				// execute callback with false to indicate failed call
				.error(function() {
					callback(false);
				});
		}
		
		*/
		
		// Update Block by ID
		self.didblocks = function() {
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

		// Update Block by ID
		self.getDidblock = function(id) {
			var defer = $q.defer();
			return $http.get('../api/didblock/'+id)
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
		self.getDidblockbySitecode = function(sitecode) {
			var defer = $q.defer();
			return $http.get('../api/didblock/sitecode/'+sitecode)
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
		self.getDidblockDids = function(id) {
			var defer = $q.defer();
			return $http.get('../api/didblock/'+id+'/dids')
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
		self.getDid = function(number) {
			var defer = $q.defer();
			return $http.get('../api/did/number/'+number)
				.then(function successCallback(response) {
					defer.resolve(response);
					// Must return the promise to the controller. 
					return defer.promise;
					
			  }, function errorCallback(response) {
					defer.resolve(response);
					return defer.promise;
			});
		}
		
		// Get DID Numbers from Array
		self.searchDidNumbersinArray = function(numbers) {
			
			return $http.post('../api/did/searchDidNumbersinArray',numbers);
		}
		

		// Get DID Numbers from Array
		self.searchDidblockNumbersinArray = function(numbers) {
			
			return $http.post('../api/did/searchDidblockNumbersinArray',numbers);
		}
			  
		
		
		// Create Block
		self.createDidblock = function(didblock) {
			
			return $http.post('../api/didblock',didblock);
		}
		
		
		// Update Block by ID
		self.updateDidblock = function(id, update) {
        
			return $http.put('../api/didblock/'+id, update).then(function(response) {

				var data = response.data;
				return data;

			 }, function(error) {return false;});
		}

		
		// Delete Block by ID
		self.deleteDidblock = function(id) {
			console.log('Service - Deleting ID: '+ id);
			return $http.delete('../api/didblock/'+id, id).then(function(response) {

				var data = response.data;
				return data;

			 });
		}
		
		// Update Block by ID
		self.updateDid = function(id, update) {
        
			return $http.put('../api/did/'+id, update).then(function(response) {

				var data = response.data;
				return data;

			 }, function(error) {return false;});
		}
		

		
		return self

	}]);
