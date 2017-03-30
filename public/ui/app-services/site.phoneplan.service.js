angular
	.module('app')
	.factory('sitePhonePlanService', ['$http', '$localStorage', '$stateParams', '$q', '$state', function($http, $localStorage, $stateParams, $q, $state){
		
		var self = {};


		// Get Dids by Block ID
		self.getsitephoneplans = function(id) {
			var defer = $q.defer();
			return $http.get('../api/site/'+id+'/phoneplans')
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
		self.getphoneplan = function(id) {
			var defer = $q.defer();
			return $http.get('../api/phoneplan/id/'+id)
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
		self.getphoneplanphones = function(id) {
			var defer = $q.defer();
			return $http.get('../api/phoneplan/id/'+id+'/phones')
				.then(function successCallback(response) {
					defer.resolve(response);
					
					// Must return the promise to the controller. 
					return defer.promise;
					
			  }, function errorCallback(response) {
					defer.resolve(response);
					return defer.promise;
			  });
		}


		// Create
		self.createphoneplan = function(phoneplan) {
			
			return $http.post('../api/phoneplan', phoneplan);
		}
		
		
		// Update by ID
		self.updatephoneplan = function(id, update) {
        
			return $http.put('../api/phoneplan/'+id, update).then(function(response) {

				var data = response.data;
				return data;

			 }, function(error) {return false;});
		}

		
		// Delete by ID
		self.deletephoneplan = function(id) {
			console.log('Service - Deleting ID: '+ id);
			return $http.delete('../api/phoneplan/'+id, id).then(function(response) {

				var data = response.data;
				return data;

			 });
		}
		
		
		
		
		// Create
		self.createphone = function(phone) {
			
			return $http.post('../api/phone', phone);
		}
		
		
		// Update by ID
		self.updatephone = function(id, update) {
        
			return $http.put('../api/phone/'+id, update).then(function(response) {

				var data = response.data;
				return data;

			 }, function(error) {return false;});
		}

		
		// Delete by ID
		self.deletephone = function(id) {
			console.log('Service - Deleting ID: '+ id);
			return $http.delete('../api/phone/'+id, id).then(function(response) {

				var data = response.data;
				return data;

			 });
		}


		return self

	}]);
