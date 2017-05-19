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
		if(!vm.permissions.create.Didblock){
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
			blocks = [];
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
				blocks.push(phone);
			}
			
			//console.log(phones);

			vm.count = 0;
			
			var didblocksarray = [];
			for (block in blocks){
				didblockarray = {};
				//didblockarray['phoneplan'] = id; probably not needed since we're not tieing to an ID
				didblockarray['name'] = blocks[block][0];
				didblockarray['carrier'] = blocks[block][1];
				didblockarray['comment'] = blocks[block][2];
				didblockarray['country_code'] = blocks[block][3];
				didblockarray['start'] = blocks[block][4];
				didblockarray['end'] = blocks[block][5];
				didblockarray['type'] = blocks[block][6];
				didblockarray['reserved'] = blocks[block][7]
				//didblockarray['reserved'] = didblockarray['language'].trim()
				
				/*
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
				*/
				didblocksarray.push(didblockarray);
				//console.log(didblockarray);
			}			
			vm.loading = false; 
			//console.log(phonesarray);
			console.log(didblocksarray);
			//vm.numberstable = phonesarray;
			vm.didblocktable = didblocksarray;

		}
		

		// Create DID Blocks
		vm.createdidblock = function(didbock) {
			console.log('Creating DIDBlock:')
			console.log(didbock);
			
			
			//sitePhonePlanService.createphone(phone).then(function(data) {
			telephonyService.createDidblock(didbock)
				.then(function(data) {
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
		vm.insertdidblocks = function(blocks){
			console.log("Blocks")
			console.log(blocks);
			for (block in blocks){
				//block = blocks[block];
				//console.log(block);
				vm.createdidblock(blocks[block]);
			}
			
			
			$timeout(function(){
				$location.path('didblock');
			}, 2500);
			
		
		}
		
		
		// Edit state for Phone Edit button. 
		// Edit state for DID Block Edit button. 
		vm.edit = {};
		
		
		// Update Phone in List with save button. 
		/*vm.update = function(number) {
			console.log(number)
		}*/
		// Update DID Block in List with save button. 
		vm.update = function(number) { 
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
