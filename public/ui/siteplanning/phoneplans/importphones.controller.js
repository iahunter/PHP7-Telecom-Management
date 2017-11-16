angular
	.module('app')
	.controller('importphones.IndexController', ['telephonyService', 'sitePhonePlanService', 'PageService', '$location', '$timeout', '$state', '$stateParams', function(telephonyService, sitePhonePlanService, PageService, $location, $timeout, $state, $stateParams) {
		
		// This controller is used to insert phones into the Planning Database only. Not used for systems integration. 
		
		var vm = this;
		
		vm.refresh = function (){
			$state.reload();
		};
		
		vm.messages = 'Loading...';
		
		var id = $stateParams.id;
		
		
		// Match the window permission set in login.js and app.js - may want to user a service or just do an api call to get these. will decide later. 
		vm.permissions = window.telecom_mgmt_permissions;

		if(!vm.permissions.create.Phone){
			$location.path('/accessdenied');
		}
		
		vm.clear = function(variable){
			console.log(variable);
			variable = "";
			console.log(variable);
			return variable;
		}
		
		vm.toggle = function(variable){
			console.log(variable);
			if(variable == true){
				variable = false;
			}else{
				if(variable == false){
					variable = true;
				}
			}
			return variable;
		}
		
		vm.states = [{
				id: 1,
				name: 'tab'
			}, {
				id: 2,
				name: 'comma'
			}, {
				id: 3,
				name: 'space'
			}];
		
		vm.selectedOption = {};
		

		vm.getphoneplan = sitePhonePlanService.getphoneplan(id)
			.then(function(res){
				// Check if Token has expired. If so then direct them to login screen. 
				if(res.message == "Token has expired"){
					vm.tokenexpired = true;
					//alert("Token has expired, Please relogin");
					//alert(res.message);
					$state.go('logout');
				}

				vm.site = res.data.result;
				console.log(vm.site);
				return vm.site
				
				
			}, function(err){
				//Error
			});

		// Create DID Block 
		vm.submitDidblock = function(form) { 
		
			//console.log(form);
			
			vm.loading = true;
			var numbers = {};
			numbers['blocks'] = "";
			numbers['blocks'] = form.blocks.split("\n");
			numbers['delimiter'] = vm.selectedOption.name;
			
			
			console.log(numbers);
			phones = [];
			for (key in numbers.blocks){
				
				value = numbers.blocks[key];
				
				if (numbers['delimiter'] == "space"){
					var phone = value.split(" ");
				}else if (numbers['delimiter'] == "comma"){
					var phone = value.split(",");
				}else if (numbers['delimiter'] == "tab"){
					var phone = value.split("\t");
				}
				
				//console.log(phone);
				if((phone[0] == "") && (phone[1] == "") && (phone[2] == "") && (phone[3] == "")){
					continue;
				}
				
				
				//console.log(phone);
				phones.push(phone);
			}
			
			//console.log(phones);

			vm.count = 0;
			
			//vm.numberstable = phones;
			
			
			
			var phonesarray = [];
			for (phone in phones){
				phonearray = {};
				phonearray['phoneplan'] = id;
				phonearray['firstname'] = phones[phone][0];
				phonearray['lastname'] = phones[phone][1];
				phonearray['username'] = phones[phone][2];
				phonearray['name'] = phones[phone][3];
				phonearray['device'] = phones[phone][4];
				
				// Trim the Cisco off of the device type as this is auto added in the backend. 
				if(phonearray['device']){
					console.log(phonearray['device'])
					var device = phonearray['device'].toLowerCase();
					
					var regexp = /^cisco/;
					
					if(device.match(regexp)){
						//console.log("Regex matching Cisco ")
						
						device = device.split("cisco")
						console.log(device)
						if (device.length > 0){
							device = device[1]
							device = device.trim()
							phonearray['device'] = device;
						}
					}
				}
				
				//phonearray['dn'] = phones[phone][5];
				phonearray['dn'] = phones[phone][5].replace(/[()-]/g, "");
				
				phonearray['language'] = phones[phone][6];
				
				phonearray['language'] = phonearray['language'].trim()
				
				if(phonearray['language']){
					phonearray['language'] = phonearray['language'].toLowerCase();
					if(phonearray['language'] == "e" || phonearray['language'] == "en" || phonearray['language'] == "eng"){
						phonearray['language'] = 'english';
					}
					if(phonearray['language'] == "f" || phonearray['language'] == "fr" || phonearray['language'] == "fre"){
						phonearray['language'] = 'french';
					}
				}else{
					phonearray['language'] = 'english';
				}
				
				
				phonearray['vmpass'] = phones[phone][7];
				phonearray['voicemail'] = phones[phone][8];
				phonearray['notes'] = phones[phone][9];
				
				phonesarray.push(phonearray);
				//console.log(phonearray);
			}
			
			vm.loading = false; 
			console.log(phonesarray);
			vm.numberstable = phonesarray;

		}
		
		
		// Create Phone 
		vm.createphone = function(phone) {
			phone.phoneplan = id;
			phone.site = vm.site.site;
			
			console.log(phone);
			
			sitePhonePlanService.createphone(phone).then(function(data) {
			  //alert('phone was added successfully');
			  //return $state.reload();
			}, function(error) {
				alert('An error occurred while adding phone' + error.data.message)
				console.log(error)
			});
			//$state.reload();
		}
		
		
		vm.insertphones = function(phones){
			
			for (phone in phones){
				phone = phones[phone];
				console.log(phone);
				vm.createphone(phone);
			}
			
			$timeout(function(){
				$location.path('phoneplan/'+id);
			}, 2500);
			//$location.path('phoneplan/'+id);
			//return $state.go('getphoneplan/{id}');
			
		}
		
		
		// Edit state for Phone Edit button. 
		vm.edit = {};
		
		
		// Update Phone in List with save button. 
		vm.update = function(number) {
			console.log(number)
		}
		
		
		// Delete Phone from List
		vm.delete = function(number) {

			var index = vm.numberstable.indexOf(number);
			vm.numberstable.splice(index, 1);  

		}

	}]);
