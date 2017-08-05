angular
	.module('app')
	.controller('phoneManagerCreate.Controller', ['telephonyService', 'macdService', 'LDAPService','sitePhonePlanService', 'siteService', 'cucmService', 'cupiService', 'PageService', 'cucmReportService', '$timeout', '$location', '$state', '$stateParams', function(telephonyService, macdService, LDAPService, sitePhonePlanService, siteService, cucmService, cupiService, PageService, cucmReportService, $timeout, $location, $state, $stateParams) {
		
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
		
		vm.sitecode = $stateParams.id;

		vm.deviceForm = {};
		
		vm.deviceForm.sitecode = $stateParams.id; 
		
		
		vm.deviceForm.device = $stateParams.device;
		var name = $stateParams.name;
		vm.deviceForm.name = name.toUpperCase();
		
		vm.deviceForm.dn = $stateParams.dn;

		vm.deploybutton = false;
		
		// Match the window permission set in login.js and app.js - may want to user a service or just do an api call to get these. will decide later. 
		vm.permissions = window.telecom_mgmt_permissions;

		if(!vm.permissions.read.Phoneplan){
			$location.path('/accessdenied');
		}


		vm.languages = [{
				id: 1,
				name: 'english'
			}, {
				id: 2,
				name: 'french'
			}];
		
		
		vm.lookupuser = function(username){
			vm.nouserfound = false;
			phone = {}
			
			if(username.length >= 6){
				//console.log(username)
					if((username != "") && (username != null)){
						LDAPService.getusername(username)
						.then(function(res){
							user = [];
							//console.log(res);
							//user.username = username;
							
							result = res.data.result;
							
							if(result != undefined){
								if (result.user == ''){
									phone.aduser = ""
									phone.adipphone = ""
								
								}if (result.disabled == true){
									phone.aduser = "";
								
								}else{
									phone.adipphone = result.ipphone
									phone.aduser = result.user
									
									if(phone.aduser){
										if(result.firstname){
											vm.deviceForm.firstname = result.firstname;
										}
										if(result.lastname){
											vm.deviceForm.lastname = result.lastname;
										}
										vm.nouserfound = false;
									}
									
									console.log(phone.aduser);
								}
							}else{
								phone.aduser = ""
								phone.adipphone = ""
							}

							
							
							//console.log(phone);
							
						});
					}
			
				if(!phone.aduser){
					vm.nouserfound = true;
				}
				
				//console.log(phone)
				vm.aduser = phone
				console.log(vm.aduser)
			
			}
		}
			
		vm.getusername = function(username){
			//console.log(username);
			var user = {};
			LDAPService.getusername(username)
				.then(function(res){
					result = res.data.result;

					//console.log(result.user);
					user.username = username
					if (result.user == ""){
						user.user = "User Not Found"
					}else{
						user.ipphone = result.ipphone
						user.user = result.user
					}
					//console.log(user)
					return user;
					
				}, function(err){
					// Error
				});
		}
		
		vm.getsite = function(){
						cucmService.getsitesummary(vm.sitecode)
							.then(function(res){
								
								var cucmsitesummary = res.data.response;
								
								//console.log(cucmsitesummary);
								
								if (res.data.response == 0){
									vm.deploybutton = true;
									
									return vm.cucmsitesummary = false;
								}else{
									cucmsitesummary = res.data.response;
								}
								console.log(cucmsitesummary);
								
								vm.cucmsite = {};
								vm.cucmsite.summary = {};
								vm.cucmsite.details = {};
								// Loop thru and append to a simple array so we can do a simple select on it with ng-options.
								
								angular.forEach(cucmsitesummary, function(k,v) {
									
										//console.log("VALUE: " + v);
										//console.log(k);
										//vm.cucmsite.summary
										angular.forEach(k, function(key,object) {
											if(key.length != 0){
												//vm.cucmsite.summary['length']++;
												if (!vm.cucmsite.summary[v]){
													vm.cucmsite.summary[v] = [];
													if(key){
														vm.cucmsite.summary[v].push(key);
													}
													
													
												}else{
													if(key){
														vm.cucmsite.summary[v].push(key);
													}
													
												}
												console.log(object)
												
												// Get object details for popover
												cucmService.get_object_type_by_uuid(object, v)
														.then(function(res) {
															
															
															vm.cucmsite.details[key] = [];
															//vm.cucmsite.details[key] = res.data.response;
															
															// Json stringify to make object readable in popover
															var response = JSON.stringify(res.data.response, undefined, 2);
															//console.log(response)
															vm.cucmsite.details[key] = response;
															
															

														}, function(error) {
															alert('An error occurred while getting object')
														});
												
											}
										});
									
									//console.log(vm.cucmsite.details);
									
								});
								
								if(vm.cucmsite.summary == 0){
									console.log("Does not exist in CUCM");
									vm.cucmsite.summary = false;
								}
								
								//vm.deviceForm
								
								//console.log(vm.cucmsite.details)
								console.log(vm.cucmsite.summary)
							}, function(err){
								//Error
							});
							
							
						cupiService.listusertemplatesbysite(vm.sitecode)
							.then(function(res) {
								
								vm.siteusertemplates = res.data;
								//console.log(vm.siteusertemplates);
								
								if(vm.siteusertemplates.length > 0){
									vm.usertemplatedeploybutton = false;
								}
								if(vm.siteusertemplates.length == 0){
									vm.usertemplatedeploybutton = true;
								}
								

							}, function(error) {
								alert('An error occurred while getting user templates from unity connection')
							});
		}
					
		vm.getsite();
		
		vm.getphonesfromcucm = function(phones){
			vm.cucmphones = [];
			angular.forEach(phones, function(phone) {
				// Had to call the API directly inside the loop because the call backs weren't coming back fast enough to set the object. 
				
				//console.log(phone);
				if(phone.device == "ATA190"){
					name = "ATA"+ phone.name
				}
				else if(phone.device == "IP Communicator"){
					name = phone.name
				}else{
					name = phone.name
					name = "SEP"+ phone.name
				}
				
				cucmService.getphone(name)
				.then(function(res){
					user = [];
					//console.log(res);
					//user.username = username;
					
					
					result = res.data.response;
					

					//console.log(result);

					// Must do the push inline inside the API Call or callbacks can screw you with black objects!!!! 
					if(result != "Not Found"){
						result.phoneid = phone.id;
						phone.inuse = true;
						vm.cucmphones.push(result);
					}
					if(result == "Not Found"){
						phone.inuse = false;
					}
					

				}, function(err){
					// Error
				});

				
				
			});
			
			//console.log(vm.cucmphones);
			
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
		
		// Set Display Unity to False
		vm.displayunityusers = false;
		
		
		// This function runs thru the users in unity to check whats is in use and adds it to the object. 
		vm.getusersfromcupi = function(phone){
			//console.log("Hitting getusersfromcupi")
			phone.voicemail = angular.lowercase(phone.voicemail);
			//console.log(phone)
			if((phone.voicemail == true) || (phone.voicemail == 'true') || (phone.voicemail == 't') || (phone.voicemail == 'y') || (phone.voicemail == 'yes')){
			
				if(phone.username != ""){
					phone.username = angular.lowercase(phone.username);
					cupiService.getuser(phone.username)
					.then(function(res){
						user = [];
						//console.log(res);
						//user.username = username;
						
						
						result = res.data.response;
						if(result['@total'] == 0){
							vm.unityuser = null;
						}else{
							vm.unityuser = result['User'];
							console.log("vm.unityuser")
							console.log(vm.unityuser)
						}
						
					}, function(err){
						// Error
					});
					
					cupiService.getldapuser(phone.username)
					.then(function(res){
						user = [];
						//console.log(res);
						//user.username = username;
						
						
						result = res.data.response;
						//console.log(result)
						if(result['@total'] == 0){
							vm.unityldapuser = null;
						}else{
							vm.unityldapuser = result['ImportUser'];
							console.log("vm.unityldapuser")
							console.log(vm.unityldapuser)
						}
						

						
					}, function(err){
						// Error
					});
				
				}
				
				cupiService.getmailboxbyextension(phone.dn)
					.then(function(res){
						user = [];
						//console.log(res);
						//user.username = username;
						
						
						result = res.data.response;
						if(result['@total'] == 0){
							vm.unity_mailbox_extension_inuse = null;
						}else{
							vm.unity_mailbox_extension_inuse = result['User'];
							vm.unity_mailbox_extension_inuse.Alias = angular.lowercase(result['User']['Alias']);
							console.log("vm.unity_mailbox_extension_inuse")
							console.log(vm.unity_mailbox_extension_inuse)
						}
						
					}, function(err){
						// Error
					});
				
			}			
			
		}
		

			
		vm.truefalse = [{
				id: 1,
				name: "true"
			}, {
				id: 0,
				name: "false"
			}];
	
		vm.neworexisting = [{
				id: 1,
				name: "new"
			}, {
				id: 0,
				name: "existing"
			}];

		
		vm.submitDevice = function(phone) {
			console.log(phone)
			
			macdService.create_macd_add(phone)
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
						
						vm.macobjects = res.data.result;
						
						console.log(vm.macobjects)
										
						vm.loading = false;
						
					}
					
				}, function(err){
					console.log(err)
					alert(err);
				});
			
		}

		
		// Need to build a timeout that tracks the objects that were returned every 5 seconds.
		
		// Maybe pop that into a modal. 
		
		

		// Delete Cupi User
		vm.delete_cupi_mailbox = function(user) {
			cupiService.deleteuser(user).then(function(data) {
				
				vm.getusersfromcupi(vm.deviceForm);
			
          }, function(error) {
				alert('An error occurred');
          });

		}
		
		vm.selecttouched = function(){
			vm.deleteall = true;
		}
		
		vm.deleteselected = function(phones){
			angular.forEach(phones, function(phone) {
				if(phone.select == true){
					console.log(phone);
					vm.delete(phone);
				}
				
			});
		}
		
		vm.checkAll = function() {
			angular.forEach(vm.phones, function(phone) {
			  phone.select = vm.selectAll;
			  //console.log(phone);
			  vm.selecttouched();
			});
		  };
		
		
		vm.checkAllcucmphones = function() {
			angular.forEach(vm.cucmphones, function(phone) {
			  phone.select = vm.selectAll;
			  //console.log(phone);
			  vm.cucmphoneselecttouched();
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
				vm.getphonesfromcucm(vm.phones)
			}, 2000);
				
		}
		
		
		vm.cucmphonecheckAll = function() {
			angular.forEach(vm.cucmphones, function(phone) {
			  phone.select = vm.cucmphoneselectAll;
			  //console.log(phone);
			  //vm.selecttouched();
			});
		  };
		
		
		//$timeout(function(),5000, false)
		
		$timeout(function(){
            vm.getphonesfromcucm(vm.phones)
        }, 500);
		
		
		

		
	}])
	
	
