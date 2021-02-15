angular
	.module('app')
	.factory('cucmReportService', ['$http', '$localStorage', '$stateParams', '$q', function($http, $localStorage, $stateParams, $q){
		
		var self = {};

		
		// Get Call Stats from the DB
		self.listsitesummary = function() {
			var defer = $q.defer();
			return $http.get('../api/reports/sites')
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
		
		// Get Site Report from the DB
		self.getsitesummary = function(sitecode) {
			var defer = $q.defer();
			return $http.get('../api/reports/site/'+sitecode)
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
		
		// Get Site Report from the DB
		self.getsitephones = function(sitecode) {
			var defer = $q.defer();
			return $http.get('../api/reports/phones/'+sitecode)
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
		
		// Get Site Report from the DB
		self.getphone = function(name) {
			var defer = $q.defer();
			return $http.get('../api/reports/phone/'+name)
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
		self.listsitetrunkingreport = function() {
			var defer = $q.defer();
			return $http.get('../api/reports/siteE911TrunkingReport')
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
		
		// Get Phone Models in Use
		self.phone_model_report = function() {
			var defer = $q.defer();
			return $http.get('../api/reports/get_phone_models_inuse')
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
		self.line_cleanup_report = function() {
			var defer = $q.defer();
			return $http.get('../api/reports/linecleanup')
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
		
		
		// Get Site Report from the DB
		self.get_phones_by_erl = function(erl) {
			var defer = $q.defer();
			return $http.get('../api/reports/phonesbyerl/'+erl)
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
		
		// Get DevicePool Site from Phones ERL in DB
		self.get_devicepool_from_phones_in_erl = function(erl) {
			var defer = $q.defer();
			return $http.get('../api/reports/devicepoolfromerl/'+erl)
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

		return self

	}]);
