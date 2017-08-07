angular
	.module('app')
	.controller('macdJobSummary.Controller', ['telephonyService', 'macdService', 'LDAPService','sitePhonePlanService', 'siteService', 'cucmService', 'cupiService', 'PageService', 'cucmReportService', '$interval', '$timeout', '$location', '$state', '$stateParams', '$scope', function(telephonyService, macdService, LDAPService, sitePhonePlanService, siteService, cucmService, cupiService, PageService, cucmReportService, $interval, $timeout, $location, $state, $stateParams, $scope) {
		
		// This controller does planning and systems provisioning. 
		
		var vm = this;
		
		vm.messages = 'Loading sites...';
		
		var id = $stateParams.id;


		// Match the window permission set in login.js and app.js - may want to user a service or just do an api call to get these. will decide later. 
		vm.permissions = window.telecom_mgmt_permissions;

		if(!vm.permissions.read.Cucmclass){
			$location.path('/accessdenied');
		}
		
		
		
		vm.list_macd_and_children_by_id = function(){
		
			macdService.list_macd_and_children_by_id(id)
				.then(function(res){
					// Check if Token has expired. If so then direct them to login screen. 
					if(res.message == "Token has expired"){
						vm.tokenexpired = true;
						//alert("Token has expired, Please relogin");
						//alert(res.message);
						$state.go('logout');
					}

					vm.macd_details = res.data.result;

					console.log(vm.macd_details);
					
					vm.deviceForm = vm.macd_details.macd.form_data
					
					vm.mactasks = vm.macd_details.tasks
					
					if(!vm.aduser){
						vm.lookupuser(vm.deviceForm.username)
						vm.getusername(vm.deviceForm.username)
					}
					
					if(!vm.line_number){
						vm.checklineusage(vm.deviceForm.dn)
					}

					
					if(!vm.phone){
						vm.checkname(vm.deviceForm)
					}

					vm.getusersfromcupi(vm.deviceForm)
					
					
				}, function(err){
					//Error
				});
		}
		
		vm.list_macd_and_children_by_id()

		
		var pull = $interval(vm.list_macd_and_children_by_id,5000); 
		
		$scope.$on('$destroy', function() {
			//console.log($scope);
			$interval.cancel(pull);
		});
		
		
		
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
		
		
		
		vm.checkphoneusage = function(phone){
			if(phone){
				//console.log(phone)
				
				cucmService.getphone(phone)
					.then(function(res){
						result = res.data.response;
						

						console.log(result);

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
								
								if(vm.linedetails.line_details.pattern){
									vm.line_number = vm.linedetails.line_details.pattern
									console.log(vm.line_number)
								}
								
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
		

		
	}])
	
	
