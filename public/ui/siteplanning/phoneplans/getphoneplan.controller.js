angular
	.module('app')
	.controller('getPhonePlan.IndexController', ['LDAPService','sitePhonePlanService', 'siteService', 'cucmService', '$timeout', '$location', '$state', '$stateParams', function(LDAPService, sitePhonePlanService, siteService, cucmService, $timeout, $location, $state, $stateParams) {
		
		var vm = this;
		
		vm.refresh = function (){
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
				console.log(vm.phoneplan);
				
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
					LDAPService.getusername(phone.username)
					.then(function(res){
						user = [];
						//console.log(res);
						//user.username = username;
						
						result = res.data.result;

						if (result.user == ""){
							phone.aduser = ""
							phone.adipphone = ""
							
						}else{
							phone.adipphone = result.ipphone
							phone.aduser = result.user
						}
						
						console.log(phone);
						
					});
					
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
					console.log(user)
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
				
				if(phone.device != "IP Communicator"){
					name = "SEP"+ phone.name
				}else{
					name = phone.name
				}
				
				cucmService.getphone(name)
				.then(function(res){
					user = [];
					//console.log(res);
					//user.username = username;
					
					
					result = res.data.response;

					console.log(result);

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
			
			console.log(vm.cucmphones);
			
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
					
					
					result = res.data.result;

					// Must do these inline inside the API Call or callbacks can screw you with black objects!!!! 
					user.id = phone.id;
					user.username = phone.username
					
					if (result.user == ""){
						user.user = "User Not Found"
					}else{
						user.ipphone = result.ipphone
						user.user = result.user
					}
					
					console.log(user);
					
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
				
				console.log(phone);
				
				if((phone.username != null) && (phone.username != "")){
					var update = {};
					update.username = phone.username;
					update.ipphone = phone.dn;
					
					console.log(update);
					//return update
					// Had to call the API directly inside the loop because the call backs weren't coming back fast enough to set the object. 
					LDAPService.updateadipphone(update)
					.then(function(res){

						result = res.data.result;

						console.log(result);
						
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
						console.log(result);
					}, function(err){
						// Error
					});
		}
	
		

		// Create Phone 
		vm.createphone = function(phone) {
			phone.phoneplan = id;
			phone.site = vm.phoneplan.site;
			
			console.log(phone);
			
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
			  console.log(phone);
			  //vm.selecttouched();
			});
		  };
		
		
		//$timeout(function(),5000, false)
		
		 $timeout(function(){
            vm.getphonesfromcucm(vm.phones)
        }, 500);
		
		
		vm.deployphonescucm = function() {
			angular.forEach(vm.phones, function(phone) {
				phone.sitecode = vm.site.sitecode;
				phone.extlength = vm.site.extlen;
				console.log(phone);
				cucmService.createphone(phone)
				.then(function(res) {
					
					/*
					may want to show a table of the log here or something. 
					*/
					console.log(res)
				}, function(error) {
					alert('An error occurred');
				});
			  
			});
			
			/*
			$timeout(function(){
				$state.reload();
			}, 2000);
			*/
			
		};
		
		
		
	}]);
