angular
	.module('app')
	.factory('telecomInfrastructureService', ['$http', '$localStorage', '$stateParams', '$q', '$state', function($http, $localStorage, $stateParams, $q, $state){
		
		var self = {};

		
		// Get Devices
		self.getDevices = function() {
			var defer = $q.defer();
			return $http.get('../api/telecom_infrastructure')
				.then(function successCallback(response) {
					defer.resolve(response);
					
					// Must return the promise to the controller. 
					return defer.promise;
					
			  }, function errorCallback(response) {
					defer.resolve(response);
					return defer.promise;
			  });
		}

		// Get Device by ID
		self.getDevice = function(id) {
			var defer = $q.defer();
			return $http.get('../api/telecom_infrastructure/id/'+id)
				.then(function successCallback(response) {
					defer.resolve(response);
					
					// Must return the promise to the controller. 
					return defer.promise;
					
			  }, function errorCallback(response) {
					defer.resolve(response);
					return defer.promise;
			  });
		}
		
		
		// Create Device
		self.createDevice = function(site) {
			
			return $http.post('../api/telecom_infrastructure',site);
		}
		
		
		// Update Device by ID
		self.updateDevice = function(id, update) {
        
			return $http.put('../api/telecom_infrastructure/id/'+id, update).then(function(response) {

				var data = response.data;
				return data;

			 }, function(error) {return false;});
		}

		
		// Delete Device by ID
		self.deleteDevice = function(id) {
			console.log('Service - Deleting ID: '+ id);
			return $http.delete('../api/telecom_infrastructure/id/'+id, id).then(function(response) {

				var data = response.data;
				return data;

			 });
		}


		return self

	}]);
