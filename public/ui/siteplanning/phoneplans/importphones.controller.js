angular
	.module('app')
	.controller('importphones.IndexController', ['telephonyService', 'sitePhonePlanService', '$location', '$state', '$stateParams', function(telephonyService, sitePhonePlanService, $location, $state, $stateParams) {
		
		var vm = this;
		
		vm.refresh = function (){
			$state.reload();
		};
		
		vm.messages = 'Loading...';
		
		var id = $stateParams.id;
		
		
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
				name: 'comma'
			}, {
				id: 2,
				name: 'tab'
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
				phonearray['dn'] = phones[phone][5];
				phonearray['language'] = phones[phone][6];
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
				alert('An error occurred while updating the event')
			});
			//$state.reload();
		}
		
		
		vm.insertphones = function(phones){
			
			for (phone in phones){
				phone = phones[phone];
				console.log(phone);
				vm.createphone(phone);
			}
			$location.path('phoneplan/'+id);
			//return $state.go('getphoneplan/{id}');
			
		}

	}]);
