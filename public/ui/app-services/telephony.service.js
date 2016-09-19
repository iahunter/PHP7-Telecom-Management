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
		
		
		// Delete Block by ID
		self.deleteDidblock = function(didblock) {
        
			return $http.delete('../api/didblock/'+didblock.id).then(function(response) {

				var data = response.data.events;
				return data;

			 });
		}
		
		
		// Update Block by ID
		self.updateDidblock = function(didblock) {
        
			return $http.put('../api/didblock/'+didblock.id).then(function(response) {

				var data = response.data.events;
				return data;

			 }, function(error) {return false;});
		}
		
		return self;

	}]);
