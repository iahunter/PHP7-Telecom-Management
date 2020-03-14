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

		if(!vm.permissions.read.PhoneMACD){
			$location.path('/accessdenied');
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
											console.log(vm.nodevices)
											
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

								if(vm.linedetails.line_details){

									if(vm.linedetails.line_details.callForwardAll.destination == ""){
										console.log("No Call Forward Active")
										//console.log(vm.linedetails.line_details.callForwardAll)
										vm.noCallForwardAll = true;
									}
									
									if(vm.linedetails.device_details){
										angular.forEach(vm.linedetails.device_details, function(device) {
											
											
											cucmReportService.getphone(device.name)
												.then(function(res){
													result = res.data.response;
													

													//console.log(result);

													// Must do the push inline inside the API Call or callbacks can screw you with black objects!!!! 
													if(result){
														
														device.erl = result.erl;
														device.ipv4address = result.ipv4address;

														console.log(device)
													}
													
												}, function(err){
													// Error
												});
											
											
										});
										
									}
									
									
									
								}
							}
							
							//console.log(vm.noCallForwardAll)

						}, function(err){
							// Error
						});

				}
				
			}
			
		}
		
		vm.checklineusage(vm.line)
		
		vm.cucmphonecheckAll = function() {
			angular.forEach(vm.linedetails.device_details, function(phone) {
			  phone.select = vm.cucmphoneselectAll;
			  //console.log(phone);
			  //vm.selecttouched();
			});
		  };
		  

		vm.deletecucmline = function(uuid) {
			console.log("Deleting UUID: " + vm.linedetails.uuid)
			cucmService.deletelinebyuuid(uuid)
				.then(function(res) {
					
					
					if(res.data.response.deleted){
						if(res.data.response.old.pattern){
							line = res.data.response.old.pattern
							console.log(uuid + " Successfully Deleted")
							
							vm.checklineusage(vm.line)
						}

					}
					//console.log(res)
			  }, function(error) {
					alert('An error occurred');
			  });
			
		}
		
		vm.updatecucmlinecfa = function(line) {
			console.log(line)
			console.log("Update CFA: " + line.uuid)
			
			var line_update = {};
			line_update.cfa_destination = {};
			line_update.cfa_destination = line.newcfa; 
			line_update.pattern = line.line_details.pattern;
			
			cucmService.updatelinecfa(line_update)
				.then(function(res) {
					
					if(res.data.message){
						alert(res.data.message)
					}
					
					if(res.data.response){
						vm.refresh()
					}

			  }, function(error) {
					alert('An error occurred');
			  });
			
		}
		  
		
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
				vm.checklineusage(vm.line)
			}, 2000);
				
		}
		


		
	}])
	
	
