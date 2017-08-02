angular
	.module('app')
	.controller('lineManager.Controller', ['telephonyService', 'LDAPService','sitePhonePlanService', 'siteService', 'cucmService', 'cupiService', 'PageService', 'cucmReportService', '$timeout', '$location', '$state', '$stateParams', function(telephonyService, LDAPService, sitePhonePlanService, siteService, cucmService, cupiService, PageService, cucmReportService, $timeout, $location, $state, $stateParams) {
		
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
		
		
		vm.firstlettertoupper = function(string){
			return string.charAt(0).toUpperCase() + string.slice(1);
		}
		
		vm.isArray = angular.isArray;

		vm.messages = 'Loading sites...';
		
		var id = $stateParams.id;
		vm.sitecode = id;
		
		vm.line = $stateParams.dn
		
		
		// Match the window permission set in login.js and app.js - may want to user a service or just do an api call to get these. will decide later. 
		vm.permissions = window.telecom_mgmt_permissions;

		if(!vm.permissions.read.Phoneplan){
			$location.path('/accessdenied');
		}

		vm.getavailablenumbers = telephonyService.getAvailableDidsbySitecode(id)
			.then(function(res){
				// Check if Token has expired. If so then direct them to login screen. 
				if(res.message == "Token has expired"){
					vm.tokenexpired = true;
					//alert("Token has expired, Please relogin");
					//alert(res.message);
					$state.go('logout');
				}

				vm.availablenumbers = res.data.response;
				
				
				//console.log(vm.availablenumbers);
				
				
			}, function(err){
				//Error
			});
		
		vm.getphonemodels = cucmReportService.phone_model_report()
			.then(function(res){
				// Check if Token has expired. If so then direct them to login screen. 
				if(res.message == "Token has expired"){
					vm.tokenexpired = true;
					//alert("Token has expired, Please relogin");
					//alert(res.message);
					$state.go('logout');
				}

				vm.phonemodels = res.data.response;
				
				
				//console.log(vm.phonemodels);
				
				
			}, function(err){
				//Error
			});
			
		vm.submitDevice = function(phone) {
			console.log("Submit Triggered!")
			
			if(phone.device == "IP Communicator"){
				phone.name = "CIPC_" + phone.dn;
			}
			console.log(phone)
			var path = '/phone/site/'+ id + '/create/'+ phone.device + '&' + phone.name + '&' + phone.dn
			$location.path(path);
			
		}
		
		vm.checkname = function(phone){
			if(phone.name){
				vm.nameinvalid = false;
				if(phone.device != "IP Communicator"){
					
					// Check if valid MAC
					var regexp = /^[0-9a-f]{1,12}$/gi;
					if(!phone.name.match(regexp)){
						console.log("NO REGEX MATCH FOUND ON NAME")
						vm.nameinvalid = true;
					}
					// If not it should be 12 digits long. 
					if(phone.name.length != 12){
						vm.nameinvalid = true;
					}
				}
				//console.log(vm.nameinvalid)
			}
			
			if(phone.name && !vm.nameinvalid){
				//console.log("Hitting Here")
				
				if(phone.device != "IP Communicator"){
					vm.checkphoneusage('SEP'+phone.name)
				}
				
			}
			
		}
		
		vm.check_ipcommunicator_usage = function(phone){
			if(phone.device == "IP Communicator" && phone.dn){
				vm.checkphoneusage('CIPC_'+phone.dn)
			}
		}
		
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
							//console.log(vm.phone)
						}else{
							vm.phone = false;
						}
						

					}, function(err){
						// Error
					});
			}
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
		
		vm.checklineusage = function(line){
			
			if(line){
				vm.lineinvalid = true
				//console.log(line)
				if((line > 1000000000) && (line < 9999999999)){
						//console.log(phone.dn)
						vm.lineinvalid = false;
						
				}
				if(vm.lineinvalid){
					console.log("Line Invalid")
					vm.linesummary = false;
				}
				else if(!vm.lineinvalid){
					cucmService.getNumberbyRoutePlan(line)
						.then(function(res){
							user = [];
							//console.log(res);
							//user.username = username;
							
							
							result = res.data.response;
							

							//console.log(result.length);

							// Must do the push inline inside the API Call or callbacks can screw you with black objects!!!! 
							if(result){
								if(result.length == 1){
									//console.log("Length = 1")
									//var blankline = false;
									angular.forEach(result, function(line) {
										//console.log(line)
										if(line.routeDetail == ""){
											//console.log("Hitting blank route details")
											//blankline = true;
											vm.nodevices = true;
											//vm.linesummary = result;
											
										}
										
									});
									
									
									vm.linesummary = result;
									
								}else{
									vm.linesummary = result;
									//console.log(vm.linesummary)
								}
								
							}else{
								vm.linesummary = false;
								vm.noline = true;
							}
							

						}, function(err){
							// Error
						});
					
					cucmService.getNumberandDeviceDetailsbyRoutePlan(line)
						.then(function(res){
							user = [];
							//console.log(res);
							//user.username = username;
							
							
							result = res.data.response;
							

							//console.log(result);

							// Must do the push inline inside the API Call or callbacks can screw you with black objects!!!! 
							if(result){
								vm.linedetails = result;
								//console.log(vm.linedetails)
							}
							

						}, function(err){
							// Error
						});
				}
				
			}
			
		}
		
		vm.checklineusage(vm.line)
		
		
		vm.checkAllcucmphones = function() {
			console.log("Hitting check all")
			angular.forEach(vm.cucmphones, function(phone) {
			  phone.select = vm.selectAll;
			  //console.log(phone);
			  //vm.cucmphoneselecttouched();
			});
		  };
		  
		
		vm.deletecucmphone = function(phone) {
			
			name = phone.name;
			cucmService.deletephone(name)
				.then(function(res) {
					
					
					if(res.data.deleted_uuid){
						console.log(name + " Successfully Deleted")
						phone = null;
					}
					//console.log(res)
			  }, function(error) {
					alert('An error occurred');
			  });
			
		}
		
		vm.cucmphonedeleteselected = function(phones){
			angular.forEach(phones, function(phone) {
				if(phone.select == true){
					//console.log(phone);
					vm.deletecucmphone(phone);
				}
				
			});
			
			$timeout(function(){
				vm.checklineusage(vm.deviceForm.dn)
			}, 2000);
				
		}
		
		vm.cucmphonecheckAll = function() {
			angular.forEach(vm.linedetails.device_details, function(phone) {
			  phone.select = vm.cucmphoneselectAll;
			  //console.log(phone);
			  //vm.selecttouched();
			});
		  };

		
	}])
	
	
