angular
	.module('app')
	.controller('Telecom.Infrastructure.Controller', ['telecomInfrastructureService', 'cucmService', 'PageService', '$location', '$state', '$scope', '$interval','$stateParams', function(telecomInfrastructureService, cucmService, PageService, $location, $state, $scope, $interval, $stateParams) {
	
		var vm = this;
		
		// Match the window permission set in login.js and app.js - may want to user a service or just do an api call to get these. will decide later. 
		vm.permissions = window.telecom_mgmt_permissions;
		
		
		//initController();
		
		vm.siteForm = {};
		
		vm.refresh = function (){
			// jQuery Hack to fix body from the Model. 
					$(".modal-backdrop").hide();
					$('body').removeClass("modal-open");
					$('body').removeClass("modal-open");
					$('body').removeAttr( 'style' );
				// End of Hack */
			$state.reload();
		};

		vm.messages = 'Loading devices...';
		
		vm.loading = true;
		
		// Page Request
		vm.getpage = PageService.getpage('listdevices')
		
		if(!vm.permissions.read.Site){
			$location.path('/accessdenied');
		}
		
		
		function isInArrayNgForeach(field, arr) {
			var result = false;
			//console.log("HERRE")
			//console.log(field);
			//console.log(arr);
			
			angular.forEach(arr, function(value, key) {
				//console.log(value);
				if(field == value)
					result = true;
			});

			return result;
		}
		
		function getdevices() {
			telecomInfrastructureService.getDevices()
			
				.then(function(res){
					
					//console.log(res)
					// Check for errors and if token has expired. 
					if(res.data.message){
						//console.log(res);
						vm.message = res.data.message;
						console.log(vm.message);
						
						if(vm.message == "Token has expired"){
							// Send user to login page if token expired. 
							//alert("Token has expired, Please relogin");
							$state.go('logout');
						}

						return vm.message;
					}else{
						
						vm.devices = res.data.result;
										
						vm.loading = false;
						
					}
					
				}, function(err){
					console.log(err)
					alert(err);
				});
		}
		
		getdevices();
			
		var pulldevices = $interval(getdevices,30000); 
		
		$scope.$on('$destroy', function() {
			//console.log($scope);
            $interval.cancel(pulldevices);
		});
			
		
		var id = $stateParams.id;

		if(id != undefined){
			// Fix undefined site error on site list loading.
			vm.getDevice = telecomInfrastructureService.getDevice(id)
			.then(function(res){

				vm.deviceForm = res.data.result;
			
			}, function(err){
							//Error
			});
		}


		vm.submitDevice = function(form) {

			form.hostname = form.hostname.toUpperCase();
			//form.didrange = form.didrange.toUpperCase();
			console.log(form);
			
			telecomInfrastructureService.createDevice(angular.copy(form)).then(function(data) {
				alert("site Added Succesfully" + data);
				$state.go('infrastructure');
			}, function(error) {
				console.log(error)
				console.log(error.data.message)
				alert('Error: ' + error.data.message + " | Status: " + error.status);
			});

		}
		
		// Edit state for DID block Edit button. 
		vm.edit = {};
		
		// Update DID Block service called by the save button. 
		vm.update = function(device) {

			device.hostname = device.hostname.toUpperCase();
			
			telecomInfrastructureService.updateDevice(device.id, device).then(function(data) {
			  alert('Device Updated Successfully!')
			  $location.path('/infrastructure');
			}, function(error) {
				alert('An error occurred while updating the site')
			});
			//$state.reload();
		}
		
		
		// Delete DID Block 
		vm.delete = function(device) {
			telecomInfrastructureService.deleteDevice(device.id).then(function(data) {

			
				// jQuery Hack to fix body from the Model. 
					$(".modal-backdrop").hide();
					$('body').removeClass("modal-open");
					$('body').removeClass("modal-open");
					$('body').removeAttr( 'style' );
				// End of Hack */
			
				return $state.reload();
          }, function(error) {
				alert('An error occurred');
          });

		}
	}])
	
	// Be nice to use a directive at some point to help template HTML
	.directive('trRow', function ($compile) {

		return {
			template: '<tr><td ng-bind="row.id"></td><td><strong ng-bind="row.name"></strong></td><td ng-bind="row.description"></td></tr>'
		};
	});

