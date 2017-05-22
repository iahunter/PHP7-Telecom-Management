angular
	.module('app')
	.factory('SonusCDRService', ['$http', '$localStorage', '$stateParams', '$q', function($http, $localStorage, $stateParams, $q){
		
		var self = {};


		// Get SBC Call Summary
		self.list_calls_by_date_range = function(query) {
			var defer = $q.defer();
			return $http.post('../api/sonus5kcdrs/calls_with_loss_by_daterange', query)
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
		
		// Get Todays packet loss calls
		self.list_todays_calls_with_packetloss = function() {
			var defer = $q.defer();
			return $http.get('../api/sonus/list_todays_calls_with_loss')
				.then(function successCallback(response) {
					defer.resolve(response);
					
					// Must return the promise to the controller. 
					return defer.promise;
					
			  }, function errorCallback(response) {
					defer.resolve(response);
					return defer.promise;
			  });
		}
		
		
		// Get Todays Attempt Records
		self.list_todays_attempts = function() {
			var defer = $q.defer();
			return $http.get('../api/sonus/list_todays_attempts')
				.then(function successCallback(response) {
					defer.resolve(response);
					
					// Must return the promise to the controller. 
					return defer.promise;
					
			  }, function errorCallback(response) {
					defer.resolve(response);
					return defer.promise;
			  });
		}
		
		
		// Get Todays packet loss calls
		self.list_todays_attempts_summary_report = function() {
			var defer = $q.defer();
			return $http.get('../api/sonus/list_todays_attempts_summary_report')
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
