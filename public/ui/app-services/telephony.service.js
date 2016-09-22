angular
	.module('app')
	.factory('telephonyService', ['$http', '$localStorage', '$stateParams', function($http, $localStorage, $stateParams){
		
		var self = {};

		self.GetDidblocks = GetDidblocks;

		function GetDidblocks(callback) {
			self.didblock = {};
			GetType(callback, 'didblock');
		}

		function GetType(callback, type) {
			self.didblock[type] = {};
			return $http.get('../api/' + type)
				.success(function (response) {
					self.didblocks = response.didblocks;
					callback(true);
				})
				// execute callback with false to indicate failed call
				.error(function() {
					callback(false);
				});
		}
		
		
		
				// Update Block by ID
		self.getDidblock = function(id) {
			return $http.get('../api/didblock/'+id)
				.success(function (response) {
					//console.log(response);
					self.didblock = response.didblock;
					
				})
				// execute callback with false to indicate failed call
				.error(function() {
				});
		}
		
		// Update Block by ID
		self.getDidblockDids = function(id) {
			return $http.get('../api/didblock/'+id+'/dids')
				.success(function (response) {
					//console.log(response);
					self.dids = response.dids;
					//console.log(self.dids);
				})
				// execute callback with false to indicate failed call
				.error(function() {
				});
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
		

		
		return self

	}]);
