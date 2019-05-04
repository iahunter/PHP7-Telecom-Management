angular
	.module('app')
	.controller('getPhonePlan.IndexController', ['LDAPService','sitePhonePlanService', 'siteService', 'cucmService', 'cupiService', 'macdService', 'PageService', 'cucmReportService', '$interval', '$timeout', '$location', '$state', '$stateParams', '$scope', function(LDAPService, sitePhonePlanService, siteService, cucmService, cupiService, macdService, PageService, cucmReportService, $interval, $timeout, $location, $state, $stateParams, $scope) {
		
		// This controller does planning and systems provisioning. 
		
		var vm = this;
		
		vm.refresh = function (){
			
			// Call this to clear out the log table from Deploy Phones. 
			cucmService.clearphoneadds();
			
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
		
		vm.showaddrow = false;
		
		vm.phoneaddtoggle = function(){
			if(vm.showaddrow == true){
				vm.showaddrow = false;
			}else{
				if(vm.showaddrow == false){
				vm.showaddrow = true;
				}
			}
		}
		
		

		vm.messages = 'Loading sites...';
		
		var id = $stateParams.id;
		
		
		vm.deploybutton = false;
		
		// Match the window permission set in login.js and app.js - may want to user a service or just do an api call to get these. will decide later. 
		vm.permissions = window.telecom_mgmt_permissions;

		if(!vm.permissions.read.Phoneplan){
			$location.path('/accessdenied');
		}
		
		
		vm.getphoneplan = sitePhonePlanService.getphoneplan(id)
			.then(function(res){
				// Check if Token has expired. If so then direct them to login screen. 
				if(res.message == "Token has expired"){
					vm.tokenexpired = true;
					//alert("Token has expired, Please relogin");
					//alert(res.message);
					$state.go('logout');
				}

				vm.phoneplan = res.data.result;
				//console.log(vm.phoneplan);
				
				vm.getsite = siteService.getsite(vm.phoneplan.site)
				.then(function(result){
					vm.site = result.data.result
					//console.log(vm.site);
				}, function(err){
					//Error
				});
				return vm.phoneplan
				
				
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
		
		getphoneplanphones()
		
		function getphoneplanphones(){
		sitePhonePlanService.getphoneplanphones(id)
			.then(function(res){
				// Check if Token has expired. If so then direct them to login screen. 
				if(res.message == "Token has expired"){
					vm.tokenexpired = true;
					//alert("Token has expired, Please relogin");
					//alert(res.message);
					$state.go('logout');
				}
				
				vm.phoneplan_loading = true
				
				vm.phones = res.data.result;
				
				if(vm.phones){
					phonecheck()
				}

				//vm.phonecheck(vm.phones); 

				//vm.getphonesfromcucm(vm.phones);
				
				
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
				
				vm.phoneplan_loading = false
				
			}, function(err){
				//Error
			});
		}
			
		vm.getusername = function(username){
			//console.log(username);
			var user = {};
			LDAPService.getusername(username)
				.then(function(res){
					// Check if Token has expired. If so then direct them to login screen. 
					if(res.message == "Token has expired"){
						vm.tokenexpired = true;
						//alert("Token has expired, Please relogin");
						//alert(res.message);
						$state.go('logout');
					}
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
		
		
		vm.phonecheck = $interval(phonecheck,60000); 
		
		$scope.$on('$destroy', function() {
				//console.log($scope);
				$interval.cancel(vm.phonecheck);
			});
		
		// Moved this up and out of function because was annoyed that it kept changing on refresh. If one gets removed you just need to reload the page to update the count. 
		vm.phonenumbercheckresult = []
		
		function phonecheck(){
		//vm.phonecheck = function(phones){
			if(!vm.phones.length){
				console.log("No Phones Found")
				$interval.cancel(vm.phonecheck);
			}
			phones = vm.phones
			console.log("Phone Check initiated")
			var phonecheck = {};
			phonecheck.phones = [];
			phonecheck.numbers = [];
			angular.forEach(phones, function(phone) {
				// Get the npa nxx for the phones and get all numbers in cucm with that npa nxx so we don't have to call each one. 
				number = phone.dn.substr(0,6)
				if(!phonecheck.numbers.includes(number)){
					phonecheck.numbers.push(number)
				}
				
				//_.uniq(phonecheck.numbers)
				//console.log(phonecheck.numbers);
				
				if(phone.device == "ATA190"){
					phone.realname = "ATA"+ phone.name
				}
				else if(phone.device == "IP Communicator"){
					phone.realname = phone.name
				}else{
					phone.realname = "SEP"+ phone.name
				}
				phonecheck.phones.push(phone.realname)
			});
			
			//console.log(phonecheck); 
			
			cucmService.phonecheck(phonecheck)
				.then(function(res){
					
					// Check if Token has expired. If so then direct them to login screen. 
					if(res.message == "Token has expired"){
						vm.tokenexpired = true;
						//alert("Token has expired, Please relogin");
						//alert(res.message);
						$state.go('logout');
					}
					
					//console.log(res)

					result = res.data;
					//console.log(result);
					
					vm.phonecheckresult = []
					
					angular.forEach(phones, function(phone) {
						phone.cucmfound = false;
						phonename = phone.realname.toUpperCase()
						
						angular.forEach(result, function(name) {
							uppername = name.toUpperCase()
							if (uppername == phonename) {
								//console.log("Found phone: "+phone.name)
								phone.cucmfound = true;
								//console.log(phone)
								vm.phonecheckresult.push(name)
								
							}
						})
					});
					
				}, function(err){
					// Error
				});
				

						
				//vm.phonenumbercheckresult = []
				
				angular.forEach(phonecheck.numbers, function(phonenumber) {
					cucmService.getNumberbyRoutePlan(phonenumber + "%")
					.then(function(res){
						
						// Check if Token has expired. If so then direct them to login screen. 
						if(res.message == "Token has expired"){
							vm.tokenexpired = true;
							//alert("Token has expired, Please relogin");
							//alert(res.message);
							$state.go('logout');
						}
						
						//console.log(res)

						result = res.data.response;
						//console.log(result)
						if(result){
							angular.forEach(phones, function(phone) {
								
								if(phone.dn.substr(0,6) == result[0].dnOrPattern.substr(0,6)){
									//console.log(phone.dn.substr(0,6))
									phone.numberfound = false;
								
									angular.forEach(result, function(number) {
										//console.log(number)
										number = number.dnOrPattern
										if (number == phone.dn) {
											//console.log("Found phone: "+phone.dn)
											phone.numberfound = true;
											//console.log(number)
											if(!vm.phonenumbercheckresult.includes(number)){
												vm.phonenumbercheckresult.push(number)
											}

										}
									})
								}
								
								//console.log(result[0].dnOrPattern.substr(0,6))
								//console.log(phone.dn.substr(0,6))
								//phone.numberfound = false;

								
							});
						}
						
						
						
					}, function(err){
						// Error
					});
				})

				//console.log(vm.phonenumbercheckresult)
			
		}
		
		
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
					// Check if Token has expired. If so then direct them to login screen. 
					if(res.message == "Token has expired"){
						vm.tokenexpired = true;
						//alert("Token has expired, Please relogin");
						//alert(res.message);
						$state.go('logout');
					}
					user = [];
					//console.log(res);
					//user.username = username;
					
					
					result = res.data.response;
					
					//console.log("Print Result")
					//console.log(result);

					// Must do the push inline inside the API Call or callbacks can screw you with black objects!!!! 
					if(result != ""){
						if(phone.id){
							result.phoneid = phone.id;
							phone.inuse = true;
							vm.cucmphones.push(result);
						}
					}
					if(result == ""){
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
										
										//console.log(res.data)
										
										//var mailbox = res.data;
										
										user.response = res.data;
										vm.creatednewmailboxes.push(user);
										//console.log(vm.creatednewmailboxes);
										
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
										//console.log(vm.importedldapmailboxes);
										
									}, function(err){
										console.log(err);
										user.error = err.data.message;
										vm.importedldapmailboxes.push(user);
									});
							
							}
						}
						


					}else{
						//console.log(phone.id + " Skipping, No Voicemail" )
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
						//console.log(vm.ldapsyncstatus);
						if(vm.ldapsyncstatus == "" || vm.ldapsyncstatus == "Sync is currently under process" || vm.ldapsyncstatus == "Sync is initiated"){
							
							$timeout(function(){
								vm.getldapsyncstatus();
							}, 5000);
						}
							
					}, function(err){
						// Error
					});
		}
	
		vm.languages = [{
				id: 1,
				name: 'english'
			}, {
				id: 2,
				name: 'french'
			}];
			
		vm.truefalse = [{
				id: 1,
				name: "true"
			}, {
				id: 0,
				name: "false"
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
			console.log("loading")
			vm.phoneplan_loading = true
			
			angular.forEach(phones, function(phone) {
				if(phone.select == true){
					//console.log(phone);
					//vm.delete(phone);
					sitePhonePlanService.deletephone(phone.id).then(function(res) {
						
						}, function(error) {
							//alert('An error occurred');
						});
				}
				
			});
			
			console.log("No more loading")
			vm.phoneplan_loading = false
			
			$timeout(function(){
				console.log("Getting MACDs for Plan ID: " + id)
				vm.refresh();
			}, 5000);

			
			//vm.refresh()
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
		
		// Pull the phone plan macd when loading the page. 
		getphoneplanmacds(); 
		vm.getmacdscount = 0
		
		function getphoneplanmacds(){
			macdService.list_macds_by_phoneplan_id(id)
				.then(function(res){
					// Check for errors and if token has expired. 
					//console.log(res)
					if(res.data.message){
						//console.log(res);
						vm.message = res.data.message;
						//console.log(vm.message);
						if(vm.message == "Token has expired"){
						// Send user to login page if token expired. 
							alert(vm.message);
							$state.go('logout');
						}
					}else{
						//console.log(res)
						vm.macds = res.data.result;
						//console.log(vm.macds)
						vm.getmacds = false
						// Convert DB Timestamp to local PC Time. 
						var errors = 0
						var complete = 0
						var working = 0
						angular.forEach(vm.macds, function(log) {

							// Convert UTC to local time
							var dateString = log.created_at;
							//console.log(dateString)
							created_at = moment().utc().format(dateString);
							created_at = moment.utc(created_at).toDate();
							log.created_at_local = created_at.toLocaleString()
							//console.log(log.created_at_local)
							//console.log(log)

							//log.status = "job received" 	// Test Gif Loader
							if(log.status != "error" && log.status != "complete"){
								// Keep polling the macds until these status are met. 
								console.log("getmacs = true")
								vm.getmacds = true
								working += 1
								vm.getmacdscount = 0 // Reset to 0 if still working jobs. 
							}else{
								if(log.status == "error"){
									errors += 1
								}
								if(log.status == "complete"){
									complete += 1
								}
							}
							
							
						});
						
						vm.macdworking = working
						vm.macderrors = errors
						vm.macdcomplete = complete
						
						
						if(vm.getmacds == false){
							vm.getmacdscount +=1 // Allow this to run 2 additional polls to make sure all errors are done being cleaned up. 
							console.log(vm.getmacdscount)
							if(vm.getmacdscount > 2){
								console.log("Cancel Pull interval of MACDs")
								console.log(vm.getmacds)
								$interval.cancel(vm.pull);
							}
						}

						
						vm.loading = false;

					}
					
				}, function(err){
					console.log(err);
				});
		};
		
		vm.macdcheckAll = function() {
			vm.macdselecttouched()
			angular.forEach(vm.macds, function(macd) {
			  macd.select = vm.macdselectAll;
			  //console.log(macd);
			  //vm.selecttouched();
			});
		};
		
		vm.macdselecttouched = function(){
			vm.macddeleteall = true;
		}
		
		
		
		vm.deletemacd = function(macd) {
			
			macd = macd.id;
			macdService.delete_macd_by_id(macd)
				.then(function(res) {
					
					
					if(res.data.deleted_at){
						console.log(macd + " Successfully Deleted")
						macd = null;
					}
					//console.log(res)
			  }, function(error) {
					alert('An error occurred');
			  });
			
		}
		
		vm.macddeleteselected = function(macds){
			vm.loading = true
			angular.forEach(macds, function(macd) {
				if(macd.select == true){
					macd.hide = true; 
					//delete macds.macd;
					//console.log(macd);
					vm.deletemacd(macd);
				}
			});
			
			//vm.loading = false
			
			$timeout(function(){
				console.log("Getting MACDs for Plan ID: " + id)
				getphoneplanmacds(); 
			},10000);
				
		}
		

		
		vm.submitmacd = function(phone) {
			//console.log(phone)
			
			object = vm.buildphoneobject(phone)
			
			//console.log(object)
			
			
			macdService.create_macd_add(object)
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
						// Do Nothing
						/*
						vm.macobjects = res.data.result;
						
						console.log(vm.macobjects)
										
						vm.loading = false;
						
						*/ 
						
						
					}
					
				}, function(err){
					console.log(err)
					
				});
		}
		
		vm.buildphoneobject = function(phone) {
			// Build phone object from phone
			var object = {}
			
			object.sitecode = vm.site.sitecode
			object.device = phone.device
			object.name = phone.name
			object.firstname = phone.firstname
			object.lastname = phone.lastname
			object.dn = phone.dn
			object.usenumber = "new"
			object.extlength = vm.site.extlen
			object.language =  phone.language
			
			if(phone.voicemail){
				
				phone.voicemail = phone.voicemail.toLowerCase();
				
				if(phone.voicemail == "y" || phone.voicemail == "yes" || phone.voicemail == true || phone.voicemail == "true"){
					object.voicemail = "true"
				}else{
					console.log("Else does not match one of above voicemail")
					object.voicemail = "false"; 
				}
				if(phone.username){
					object.username = phone.username;
					object.template = vm.phoneplan.employee_vm_user_template
				}else{
					object.username = ""; 
					object.template = vm.phoneplan.nonemployee_vm_user_template; 
				}
			}else{
				console.log("Else no voicemail")
				object.voicemail = "false"; 
			}

			object.phoneplan_id = phone.phoneplan
			object.ticket_number =  "Phone Plan: " + object.phoneplan_id
			
			//console.log(object)
			
			return object

		}
		
		vm.submitmacds = function(jobs){
			console.log("MACD Deploy Button Pushed.. Start polling MACDs for Plan")
			
			vm.loading = true; 
			//vm.pull = $interval(getphoneplanmacds,1000); 
			
			angular.forEach(jobs, function(job) {
				vm.submitmacd(job); 
			});
			
			//vm.loading = false; 

		}
		
		vm.submitmacdsbulk = function(jobs){
			console.log("MACD Deploy Button Pushed.. Start polling MACDs for Plan")
			
			vm.loading = true; 
			vm.pull = $interval(getphoneplanmacds,10000); 
			var array = []; 
			angular.forEach(jobs, function(job) {
				array.push(vm.buildphoneobject(job)); 
			});

			var macds = {}; 
			macds.macds = array
			
			macdService.create_macd_batch(macds)
				.then(function(res){

					console.log(res)
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
						// Do Nothing
					}
					
				}, function(err){
					console.log(err)
					
				});
		}
		
		
		vm.pull = $interval(getphoneplanmacds,5000); 
		
		$scope.$on('$destroy', function() {
				//console.log($scope);
				$interval.cancel(vm.pull);
			});

		$timeout(function(){
				console.log("Getting MACDs for Plan ID: " + id)
				//vm.getphonesfromcucm(vm.phones);
			}, 5000);
		
	}])
	
	
