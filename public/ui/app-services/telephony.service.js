angular
	.module('app')
	.factory('telephonyService', ['$http', '$localStorage', function($http, $localStorage){
		
		var self = {};

		self.GetDidblock = GetDidblock;

		function GetDidblock(callback) {
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
		
		// Create Block
		self.createDidblock = function(didblock) {
			
			return $http.post('../api/didblock',didblock);
		}
		
		
		// Update Block by ID
		self.updateDidblock = function(id, update) {
        
			return $http.put('../api/didblock/'+id, update).then(function(response) {

				var data = response.data.events;
				return data;

			 }, function(error) {return false;});
		}

		
		// Delete Block by ID
		self.deleteDidblock = function(id) {
			console.log('Service - Deleting ID: '+ id);
			return $http.delete('../api/didblock/'+id, id).then(function(response) {

				var data = response.data.events;
				return data;

			 });
		}
		
		return self

	}]);
