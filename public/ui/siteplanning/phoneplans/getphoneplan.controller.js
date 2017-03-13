angular
	.module('app')
	.controller('getPhonePlan.IndexController', ['LDAPService','sitePhonePlanService', 'siteService', 'cucmService', 'cupiService', '$timeout', '$location', '$state', '$stateParams', function(LDAPService, sitePhonePlanService, siteService, cucmService, cupiService, $timeout, $location, $state, $stateParams) {
		
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
					console.log(phone.dn)
					
					if(phone.name.length != 12){
						phone.nameinvalid = true;
					}
					
					// Check Valid MAC Address format for Phones not IP Communicator
					if(phone.device != "IP Communicator"){
						var regexp = /^[0-9a-f]{1,12}$/gi;
						if(!phone.name.match(regexp)){
							console.log("NO REGEX MATCH FOUND ON NAME")
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
				
				
				console.log(phone);
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

									}, function(err){
										// Error
									});
								
							}else{
								
								// If Username is set then we assume that user exists by now. 
								user.template = vm.phoneplan.employee_vm_user_template;
								
								// Import LDAP User / Update User Mailbox Extension
								console.log("Importing User from LDAP...")
								cupiService.importldapuser(user)
									.then(function(res){
										
										//console.log(res.data)

									}, function(err){
										// Error
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
			
			// Tell CUCM to do a LDAP Sync to retrieve the updates after AD account change
			cucmService.initiate_cucm_ldap_sync()
					.then(function(res){
						result = res.data;
						//console.log(result);
					}, function(err){
						// Error
					});
		}
	
		

		// Create Phone 
		vm.createphone = function(phone) {
			phone.phoneplan = id;
			phone.site = vm.phoneplan.site;
			
			//console.log(phone);
			
			sitePhonePlanService.createphone(phone).then(function(data) {
			  //alert('phone was added successfully');
			  return $state.reload();
			}, function(error) {
				alert('An error occurred while updating the event')
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
					console.log(res)
			  }, function(error) {
					alert('An error occurred');
			  });
			
		}
		
		vm.cucmphonedeleteselected = function(phones){
			angular.forEach(phones, function(phone) {
				if(phone.select == true){
					console.log(phone);
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
		
		/*
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
		
		vm.ignoreexistingphones = false;
		
		vm.showfailuresonly = function() {
			vm.phonefailures = [];
			angular.forEach(vm.newphones, function(phone) {
				if(phone.Line.status == "error" || ""  && phone.Phone.status == "error"){
					console.log(phone.Line.status);
					console.log(phone.Line.status);
					vm.phonefailures.push(phone);
				}
			})
			return vm.phonefailures
		}

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
				

				console.log(vm.newphones);
			
		};
		
		
		
	}]);
