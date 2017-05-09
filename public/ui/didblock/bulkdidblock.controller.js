angular
	.module('app')
	.controller('bulkdidblock.IndexController', ['telephonyService', 'sitePhonePlanService', 'PageService', '$location', '$timeout', '$state', '$stateParams', function(telephonyService, sitePhonePlanService, PageService, $location, $timeout, $state, $stateParams) {
		
		// This controller is used to add bulk DID blocks into DID Database.
		var vm = this;
		
		vm.refresh = function (){
			$state.reload();
		};
		
		vm.messages = 'Loading...';
		
		var id = $stateParams.id;
		
		vm.getpage = PageService.getpage('importIntoPhonePlan-' + id)
										//what should this be?

		// Match the window permission set in login.js and app.js - may want to user a service or just do an api call to get these. will decide later. 
		vm.permissions = window.telecom_mgmt_permissions;

		/*if(!vm.permissions.create.Phone){
								//change Phone to BulkDID?
								
			$location.path('/accessdenied');
		}*/
		if(!vm.permissions.create.DIDBlock){
								//change Phone to BulkDID?
								
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
		
		//Copy this and do something similar?
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
		// I haven't done anything here yet...
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
			
			//This was commented out already
			//vm.numberstable = phones;
			//vm.didblocktable = blocks;
			
			// make phonesarray didblockarray, modify variables accordingly
			
			/*var phonesarray = [];
			for (phone in phones){
				phonearray = {};
				phonearray['phoneplan'] = id;
				phonearray['firstname'] = phones[phone][0];
				phonearray['lastname'] = phones[phone][1];
				phonearray['username'] = phones[phone][2];
				phonearray['name'] = phones[phone][3];
				phonearray['device'] = phones[phone][4];
				phonearray['dn'] = phones[phone][5];
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
			}*/
			var didblocksarray = [];
			for (block in blocks){
				didblockarray = {};
				//didblockarray['phoneplan'] = id; probably not needed since we're not tieing to an ID
				didblockarray['firstname'] = blocks[block][0];
				didblockarray['lastname'] = blocks[block][1];
				didblockarray['username'] = blocks[block][2];
				didblockarray['name'] = blocks[block][3];
				didblockarray['device'] = blocks[block][4];
				didblockarray['dn'] = blocks[block][5];
				didblockarray['language'] = blocks[block][6];
				didblockarray['language'] = didblockarray['language'].trim()
				
				if(didblockarray['language']){
					didblockarray['language'] = didblockarray['language'].toLowerCase();
					if(didblockarray['language'] == "e" || didblockarray['language'] == "en" || didblockarray['language'] == "eng"){
						didblockarray['language'] = 'english';
					}
					if(didblockarray['language'] == "f" || didblockarray['language'] == "fr" || didblockarray['language'] == "fre"){
						didblockarray['language'] = 'french';
					}
				}else{
					didblockarray['language'] = 'english';
				}
				
				didblockarray['vmpass'] = blocks[block][7];
				didblockarray['voicemail'] = blocks[block][8];
				didblockarray['notes'] = blocks[block][9];
				didblocksarray.push(didblockarray);
				//console.log(didblockarray);
			}			
			vm.loading = false; 
			//console.log(phonesarray);
			console.log(didblocksarray);
			//vm.numberstable = phonesarray;
			vm.didblocktable = phonesarray;

		}
		
		
		// Create Phone 
		/*vm.createphone = function(phone) {
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
		}*/
		// Create DID Blocks
		vm.createdidblock = function(didbock) {
			didbock.phoneplan = id;
			didbock.site = vm.site.site;
			
			console.log(didbock);
			
			//sitePhonePlanService.createphone(phone).then(function(data) {
			telephonyService.createdidblock(phone).then(function(data) {
			  //alert('phone was added successfully');
			  //return $state.reload();
			}, function(error) {
				alert('An error occurred while adding phone' + error.data.message)
				console.log(error)
			});
			//$state.reload();
		}
		
		
		/*vm.insertphones = function(phones){
			
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
			
		} */
		vm.insertdidblocks = function(phones){
			
			for (block in blocks){
				block = blocks[block];
				console.log(block);
				vm.createdidblock(block);
			}
			
			$timeout(function(){
				$location.path('phoneplan/'+id);
			}, 2500);
			//$location.path('phoneplan/'+id);
			//return $state.go('getphoneplan/{id}');
			
		}
		
		
		// Edit state for Phone Edit button. 
		// Edit state for DID Block Edit button. 
		vm.edit = {};
		
		
		// Update Phone in List with save button. 
		/*vm.update = function(number) {
			console.log(number)
		}*/
		// Update DID Block in List with save button. 
		vm.update = function(number) { //what should number be?
			console.log(number)
		}		
		
		// Delete Phone from List
		/*vm.delete = function(number) {
			var index = vm.numberstable.indexOf(number);
			vm.numberstable.splice(index, 1);  
		}*/
		// Delete DID Block from List
		vm.delete = function(number) { 
			var index = vm.didblocktable.indexOf(number);
			vm.didblocktable.splice(index, 1);
		}

	}]);
