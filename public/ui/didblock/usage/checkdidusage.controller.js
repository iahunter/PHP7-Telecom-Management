angular
	.module('app')
	.controller('checkdidusage.IndexController', ['telephonyService', 'PageService', '$location', '$state', '$stateParams', function(telephonyService, PageService, $location, $state, $stateParams) {
		
		var vm = this;
		
		vm.refresh = function (){
			$state.reload();
		};

		vm.messages = 'Loading Didblocks...';
		
		var id = $stateParams.id;

		console.log($state)
		
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
		
		//console.log(vm.toggle);
		
		// Create DID Block 
		vm.submitDidblock = function(form) { 
		
			vm.loading = true;
			var numbers = form.numbers.trim();
			
			numbers = numbers.replace(/(\r\n|\n|\r)/gm,",");
			//console.log(numbers);
			
			var numbers = numbers.split(',');
			//console.log(numbers);
			
			var checkdid = [];
			for (key in numbers){
				value = numbers[key];
				if(value != ""){
					checkdid.push(value);
				}
			}
			
			vm.checkdid = checkdid;
			
			//console.log(vm.checkdid);
			
			vm.count = 0;
			
			telephonyService.searchDidNumbersinArray(angular.copy(vm.checkdid)).then(function(res) {
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
