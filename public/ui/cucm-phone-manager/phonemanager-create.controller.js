angular
	.module('app')
	.controller('phoneManagerCreate.Controller', ['telephonyService', 'LDAPService','sitePhonePlanService', 'siteService', 'cucmService', 'cupiService', 'PageService', 'cucmReportService', '$timeout', '$location', '$state', '$stateParams', function(telephonyService, LDAPService, sitePhonePlanService, siteService, cucmService, cupiService, PageService, cucmReportService, $timeout, $location, $state, $stateParams) {
		
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
		
		vm.getphoneplanphones = sitePhonePlanService.getphoneplanphones(id)
			.then(function(res){
				// Check if Token has expired. If so then direct them to login screen. 
				if(res.message == "Token has expired"){
					vm.tokenexpired = true;
					//alert("Token has expired, Please relogin");
					//alert(res.message);
					$state.go('logout');
				}
				
				vm.phones = res.data.result;
				//console.log(phones);
				//return vm.phones = phones;
				//vm.phones = [];
				angular.forEach(vm.phones, function(phone) {
					// Had to call the API directly inside the loop because the call backs weren't coming back fast enough to set the object. 
					//console.log(phone.dn)
					

					// Check Valid MAC Address format for Phones not IP Communicator
					if(phone.device != "IP Communicator"){
						
						// Check if valid MAC
						var regexp = /^[0-9a-f]{1,12}$/gi;
						if(!phone.name.match(regexp)){
							console.log("NO REGEX MATCH FOUND ON NAME")
							phone.nameinvalid = true;
						}
						// If not it should be 12 digits long. 
						if(phone.name.length != 12){
							phone.nameinvalid = true;
						}
					}
					/*
					var mac = phone.name.split("");
					angular.forEach(mac, function(character) {
						var regexp = /^[0-9a-f]{1,12}$/gi
						if str.match(regexp)
					}*/
					
					if((phone.dn > 1000000000) && (phone.dn < 9999999999)){
						//console.log(phone.dn)
						phone.dnint = true;
					}
					

					if((phone.username != "") && (phone.username != null)){
						LDAPService.getusername(phone.username)
						.then(function(res){
							user = [];
							//console.log(res);
							//user.username = username;
							
							result = res.data.result;
							
							if(result != undefined){
								if (result.user == ""){
									phone.aduser = ""
									phone.adipphone = ""
								
								}if (result.disabled == true){
									phone.aduser = "";
								
								}else{
									phone.adipphone = result.ipphone
									phone.aduser = result.user
								}
							}else{
								phone.aduser = ""
								phone.adipphone = ""
							}

							
							
							//console.log(phone);
							
						});
					}else{
						phone.aduser = ""
						phone.adipphone = ""
					}
				});
				
				
				return vm.phones;

				
			}, function(err){
				//Error
			});
			
			
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
		
		// Set Display Unity to False 
		vm.displayunityusers = false;
		
		
		// This function runs thru the users in unity to check whats is in use and adds it to the object. 
		vm.getusersfromcupi = function(phones){
			vm.cupiphones = [];
			
			angular.forEach(phones, function(phone) {
				phone.voicemail = angular.lowercase(phone.voicemail);
				if((phone.voicemail == 'true') || (phone.voicemail == 't') || (phone.voicemail == 'y') || (phone.voicemail == 'yes')){
				
					if(phone.username != ""){
						phone.username = angular.lowercase(phone.username);
						cupiService.getuser(phone.username)
						.then(function(res){
							user = [];
							//console.log(res);
							//user.username = username;
							
							
							result = res.data.response;
							if(result['@total'] == 0){
								phone.unityuser = null;
							}else{
								phone.unityuser = result['User'];
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
								phone.unityldapuser = null;
							}else{
								phone.unityldapuser = result['ImportUser'];
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
								phone.unity_mailbox_inuse = null;
							}else{
								phone.unity_mailbox_inuse = result['User'];
								phone.unity_mailbox_inuse.Alias = angular.lowercase(result['User']['Alias']);
							}
							
						}, function(err){
							// Error
						});
					
				}
				
				
				//console.log(phone);
				vm.displayunityusers = true;
			});
			
			
		}
		
		vm.trest = vm.getusersfromcupi(vm.phones);
		
		// Get the list of Unfied Messaging Services
		vm.cupilistexternalservices = cupiService.listexternalservices()
						.then(function(res){
							//console.log(res);
							vm.externalservices = res.data.response.ExternalService;
							
						}, function(err){
							// Error
						});
		
		
		vm.getusers_um_fromcupi = function(phones){
				// Get the UM info for account mailbox
				angular.forEach(phones, function(phone) {
					if(phone.unityuser != null){
						
						cupiService.getuserunifiedmessaging(phone.unityuser.ObjectId)
						.then(function(extservice){
							user = [];
							//console.log(extservice);
							//user.username = username;
							
							//console.log(extservice);
							extserviceresult = extservice.data.return.response;
							if(extserviceresult['@total'] == 0){
								phone.unityuser.externalserviceaccountid = null;
							}else{
								phone.unityuser.externalserviceaccountid = extserviceresult['ExternalServiceAccount']['ExternalServiceObjectId'];
								angular.forEach(vm.externalservices, function(service) {
									
									//console.log(service);
									if (service.ObjectId == phone.unityuser.externalserviceaccountid){
										// set field to the display name to make it readable. 
										phone.unityuser.externalserviceaccount = service.DisplayName;
									}
								});
							}

						}, function(err){
							// Error
						});
					}
				});
		}
		
		
		vm.importcupiusers = function(phones){
				// Get the UM info for account mailbox
				vm.creatednewmailboxes = [];
				vm.importedldapmailboxes = [];
				vm.changedldapmailboxes = [];
				angular.forEach(phones, function(phone) {
					phone.voicemail = phone.voicemail.toLowerCase(phone.voicemail);
					if((phone.voicemail == 'true') || (phone.voicemail == 't') || (phone.voicemail == 'y') || (phone.voicemail == 'yes')){
						
						var user = {};
						user.username = phone.username;
						user.dn = phone.dn;
						
						if(phone.vm_user_template){
							user.template = phone.vm_user_template;
						}else{
							if((user.username == "") || (user.username == null)){
								user.username = phone.firstname + " " + phone.lastname + " " + phone.dn;
								user.template = vm.phoneplan.nonemployee_vm_user_template;
								
								// If Username exists and voicemail is set then we assume user is not an Employee and we create a mailbox without Unified Messaging. 
								
								// Import LDAP User / Update User Mailbox Extension
								console.log("Creating New User for NonEmployee...")
								cupiService.createuser(user)
									.then(function(res){
										
										console.log(res.data)
										
										//var mailbox = res.data;
										
										user.response = res.data;
										vm.creatednewmailboxes.push(user);
										console.log(vm.creatednewmailboxes);
										
									}, function(err){
										console.log(err);
										user.error = err.data.message;
										vm.creatednewmailboxes.push(user);
									});
								
							}else{
								
								// If Username is set then we assume that user exists by now. 
								user.template = vm.phoneplan.employee_vm_user_template;
								
								// Import LDAP User / Update User Mailbox Extension
								console.log("Importing User from LDAP...")
								cupiService.importldapuser(user)
									.then(function(res){
										
										console.log(res.data)
										
										user.response = res.data
										vm.importedldapmailboxes.push(user);
										console.log(vm.importedldapmailboxes);
										
									}, function(err){
										console.log(err);
										user.error = err.data.message;
										vm.importedldapmailboxes.push(user);
									});
							
							}
						}
						


					}else{
						console.log(phone.id + " Skipping, No Voicemail" )
					}
				});
		}
		
		vm.getusernames = function(phones){
			vm.users = [];
			
			angular.forEach(phones, function(phone) {
				// Had to call the API directly inside the loop because the call backs weren't coming back fast enough to set the object. 
				LDAPService.getusername(phone.username)
				.then(function(res){
					user = [];
					//console.log(res);
					//user.username = username;
					
					user.id = phone.id;
					user.username = phone.username
					user.newipphone = phone.dn;
					
					result = res.data.result;
					
					// Must do these inline inside the API Call or callbacks can screw you with black objects!!!! 
					if(result != undefined){
						if (result.user == ""){
							user.user = "User Not Found"
						}else{
							user.ipphone = result.ipphone
							user.user = result.user
						}
						if(result.disabled){
							user.disabled = true;
						}
					}else{
						user.user = "User Not Found"
					}
					
					// Must do the push inline inside the API Call or callbacks can screw you with black objects!!!! 
					vm.users.push(user);

				}, function(err){
					// Error
				});

				
				
			});
			
			//console.log(vm.users);
		}
		
		// Update LDAP AD IP Phone field in user account
		vm.updateadipphones = function(phones){
			vm.ipphoneupdates = [];
			
			angular.forEach(phones, function(phone) {
				
				//console.log(phone);
				
				if((phone.username != null) && (phone.username != "")){
					var update = {};
					update.username = phone.username;
					update.ipphone = phone.dn;
					
					//console.log(update);
					//return update
					// Had to call the API directly inside the loop because the call backs weren't coming back fast enough to set the object. 
					LDAPService.updateadipphone(update)
					.then(function(res){

						result = res.data.result;

						//console.log(result);
						
						// Must do the push inline inside the API Call or callbacks can screw you with black objects!!!! 
						vm.ipphoneupdates.push(result);
						
					}, function(err){
						// Error
					});
				}
			});
			
			//console.log(vm.ipphoneupdates);
			
			
			$timeout(function(){
				// Tell CUCM to do a LDAP Sync to retrieve the updates after AD account change
				cucmService.initiate_cucm_ldap_sync()
					.then(function(res){
						result = res.data;
						//console.log(result);
						
						vm.getldapsyncstatus();
					}, function(err){
						// Error
					});
			}, 5000);
			/*
			cucmService.initiate_cucm_ldap_sync()
					.then(function(res){
						result = res.data;
						//console.log(result);
					}, function(err){
						// Error
					});*/
			//vm.getldapsyncstatus();
		}
		
		
		vm.getldapsyncstatus = function() {
			cucmService.get_cucm_ldap_sync_status()
					.then(function(res){
						var ldapsyncstatus = res.data;
						vm.ldapsyncstatus = ldapsyncstatus.trim();
						console.log(vm.ldapsyncstatus);
						if(vm.ldapsyncstatus == "" || vm.ldapsyncstatus == "Sync is currently under process" || vm.ldapsyncstatus == "Sync is initiated"){
							
							$timeout(function(){
								vm.getldapsyncstatus();
							}, 5000);
						}
							
					}, function(err){
						// Error
					});
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

		// Create Phone 
		vm.createphone = function(phone) {
			phone.phoneplan = id;
			phone.site = vm.phoneplan.site;
			
			//console.log(phone);
			
			sitePhonePlanService.createphone(phone).then(function(data) {
			  //alert('phone was added successfully');
			  return $state.reload();
			}, function(error) {
				alert(error.data.message)
			});
			//$state.reload();
		}
		
		vm.submitDevice = function(phone) {
			console.log(phone)
		}
		
		// Edit state for phone block Edit button.
		vm.edit = {};
		
		// Update 
		vm.update = function(phone) {
			
			sitePhonePlanService.updatephone(phone.id, phone).then(function(data) {
			  //return $state.reload();
			}, function(error) {
				alert('An error occurred while updating the event')
			});
			//$state.reload();
		}
		
		
		// Delete 
		vm.delete = function(phone) {
			sitePhonePlanService.deletephone(phone.id).then(function(data) {

			
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
		
		
		
		
		// Show Failures for Deploy Mailboxes to Unity Connection
		vm.showunityfailuresonly = function() {
			vm.showunityemployeefailures = [];
			angular.forEach(vm.importedldapmailboxes, function(phone) {
				if(!phone.error){
					// Hide Phones with No Errors for Show Errors button
					phone.hide = true;
				}
			})
			vm.showunitynonemployeefailures = [];
			angular.forEach(vm.creatednewmailboxes, function(phone) {
				if(!phone.error){
					// Hide Phones with No Errors for Show Errors button
					phone.hide = true;
				}
			})
			vm.showunityemployeefailures = true
			vm.showunitynonemployeefailures = true
		}
		
		
		// Show Failures for Deploy Phones to CUCM
		vm.showfailuresonly = function() {
			vm.phonefailures = [];
			angular.forEach(vm.newphones, function(phone) {
				if(phone.Line.status == "error" || ""  && phone.Phone.status == "error"){
					//console.log(phone.Line.status);
					//console.log(phone.Line.status);
					vm.phonefailures.push(phone);
				}
			})
			return vm.phonefailures
		}
		
		
		/*
		Old function that executed in parralel. Had issues with locking on cucm db. see new function below to execute in series using service. 
		vm.deployphonescucm = function() {
			angular.forEach(vm.phones, function(phone) {
				phone.sitecode = vm.site.sitecode;
				phone.extlength = vm.site.extlen;
				console.log(phone);
				cucmService.createphone(phone)
				.then(function(res) {
					

					console.log(res)
				}, function(error) {
					alert('An error occurred');
				});
			  
			});
		*/
		
		// default variable to false. 
		vm.ignoreexistingphones = false;
		
		// This still needs work. Needed to execute in series vs. parallel or CUCM blew up. 
		vm.deployphonescucm = function() {
			vm.newphones = "";
			angular.forEach(vm.phones, function(phone) {
				phone.sitecode = vm.site.sitecode;
				phone.extlength = vm.site.extlen;
				//console.log(phone);
			});
			
				//angular.copy(vm.phones)
				var newphones = cucmService.createphones(angular.copy(vm.phones), vm.ignoreexistingphones);
			
				vm.newphones = newphones;
				

				//console.log(vm.newphones);
			
		};
		
		
		
	}])
	
	
