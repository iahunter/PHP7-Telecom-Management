angular
	.module('app')
	.controller('checkdidblockusage.IndexController', ['telephonyService', 'PageService', '$location', '$state', '$stateParams', function(telephonyService, PageService, $location, $state, $stateParams) {
		
		var vm = this;
		
		vm.refresh = function (){
			$state.reload();
		};

		vm.messages = 'Loading Didblocks...';
		
		var id = $stateParams.id;
		
		vm.showassignments = false;
		
		vm.toggle = function(){
			//console.log('toggle');
			//console.log('vm.showassignments');
			if(vm.showassignments == true){
				vm.showassignments = false;
			}else{
				if(vm.showassignments == false){
				vm.showassignments = true;
				}
			}
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

		// Create DID Block 
		vm.submitDidblock = function(form) { 
		
			console.log(form);
			
			vm.loading = true;
			var numbers = {};
			numbers['blocks'] = "";
			//numbers['blocks'] = form.blocks.split("\n");
			numbers['delimiter'] = vm.selectedOption.name;
			
			
			var blocks = form.blocks.split("\n");
			console.log("Blocks")
			var format = [];
			for(i in blocks){
				var v = blocks[i];
				format.push(v.replace(/[()-]/g, ""));
			}
			
			console.log(format)

			numbers['blocks'] = format
			numbers['delimiter'] = vm.selectedOption.name;

			console.log(numbers);
			
			vm.count = 0;
			
			console.log(numbers);
			
			vm.count = 0;
			
			telephonyService.searchDidblockNumbersinArray(angular.copy(numbers)).then(function(res) {
				//alert("Didblock Added Succesfully" + data);
				//console.log(data);
				var checkdidresult = "";
				
				checkdidresult = res.data.result;
				
				var numberstable = [];
				//console.log(vm.checkdidresult);
				
				
				for (k in checkdidresult){
					var v = checkdidresult[k];
					
					console.log(v);
					
					
					for (key in v){
						
						// Object to add to table array
						var number = {};
						
						number['number'] = key;
						number['details'] = [];
						
						var value = v[key];

						if (value == false){
							number['details']['status'] = "Not Found";
							numberstable.push(number);
							continue;
						}

						number['details'] = value[0];
						
						// Push Number info onto the table data array. 
						vm.count = vm.count + 1;
						numberstable.push(number);
					};
				};
				
				vm.loading = false; 
				vm.numberstable = numberstable;
				console.log("vm.numberstable");
				console.log(vm.numberstable);
				
				
				//console.log(vm.checkdidresult);
			}, function(error) {
				console.log(error)
				console.log(error.data.message)
				alert('Error: ' + error.data.message + " | Status: " + error.status);
			});
			


		}

	}]);
