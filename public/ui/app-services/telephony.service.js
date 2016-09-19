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
		
		self.createDidblock = function(didblock) {
			
			return $http.post('../api/didblock',didblock);
		}
		
		return self;

	}]);
