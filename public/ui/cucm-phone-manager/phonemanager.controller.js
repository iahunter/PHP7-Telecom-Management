angular
	.module('app')
	.controller('phoneManager.Controller', ['telephonyService', 'LDAPService','sitePhonePlanService', 'siteService', 'cucmService', 'cupiService', 'PageService', 'cucmReportService', '$timeout', '$location', '$state', '$stateParams', function(telephonyService, LDAPService, sitePhonePlanService, siteService, cucmService, cupiService, PageService, cucmReportService, $timeout, $location, $state, $stateParams) {
		
		// This controller does planning and systems provisioning. 
		
		var vm = this;

		vm.refresh = function (){
			
			// jQuery Hack to fix body from the Model. 
			$(".modal-backdrop").hide();
			$('body').removeClass("modal-open");
			$('body').removeClass("modal-open");
			$('body').removeAttr( 'style' );
			// End of Hack */
			//console.log(vm.newphones);
			$state.reload();
		};

		
		vm.isArray = angular.isArray;

		vm.messages = 'Loading sites...';
		
		var id = $stateParams.id;
		vm.sitecode = id;
		
		var phone = $stateParams.name;
		vm.name = $stateParams.name
		
		// Match the window permission set in login.js and app.js - may want to user a service or just do an api call to get these. will decide later. 
		vm.permissions = window.telecom_mgmt_permissions;

		if(!vm.permissions.read.PhoneMACD){
			$location.path('/accessdenied');
		}
		
		vm.resetphone = function(phone){
			if(phone){
				console.log(phone)
				
				cucmService.resetphone(phone.name)
					.then(function(res){
						result = res.data.response;
						

						console.log(result);

						// Must do the push inline inside the API Call or callbacks can screw you with black objects!!!! 
						if(result){
							
							console.log(vm.phone)			
							alert('Phone has been reset: '+ phone.name)
							
						}else{
							vm.phone = false;
							vm.nophone = true;
						}
						
					}, function(err){
						// Error
					});
			}
		}


		vm.checkphoneusage = function(phone){
			if(phone){
				//console.log(phone)
				
				cucmService.getphone(phone)
					.then(function(res){
						result = res.data.response;
						

						//console.log(result);

						// Must do the push inline inside the API Call or callbacks can screw you with black objects!!!! 
						if(result){
							vm.phonereviewed = false;
							vm.phone = result;
							
							
							cucmReportService.getphone(phone)
								.then(function(res){
									result = res.data.response;
									

									//console.log(result);

									// Must do the push inline inside the API Call or callbacks can screw you with black objects!!!! 
									if(result){
										
										vm.phone.erl = result.erl;
										vm.phone.ipv4address = result.ipv4address;
										vm.phone.risdb_ipv4address = result.risdb_ipv4address;
										vm.phone.risdb_registration_status = result.risdb_registration_status;

										console.log(vm.phone)
									}
									
								}, function(err){
									// Error
								});
							

							//console.log(vm.phone)
						}else{
							vm.phone = false;
							vm.nophone = true;
						}
						
					}, function(err){
						// Error
					});
			}
		}
		
		
		vm.checkphoneusage(phone);
		
		
		// Get active Call Forward I(nfo )
		vm.getcallforwardinfo = function(uuid){
			//console.log(uuid)
			var forward = false;
			if(uuid){
				angular.forEach(vm.phone.line_details, function(value,key) {
					if(key == uuid){
						//console.log("CallForward:")
						//console.log(value.callForwardAll.destination)
						forward = value.callForwardAll.destination
						return forward
						
					}
				});
			}
			//console.log(forward)
			return forward
			
		}
		
		vm.deletephone = function(phone){
			if(phone){
				console.log("Deleting Phone")
				console.log(phone)
				var phonename = angular.copy(phone)
				console.log(phonename)
				
				cucmService.deletephone(phone)
					.then(function(res){
						result = res.data.response;
						
						if(result){
							vm.phone = ""
							vm.checkphoneusage(phonename)
							console.log(phonename)
						}

					}, function(err){
						// Error
					});
			}
		}


		
	}])
	
	
