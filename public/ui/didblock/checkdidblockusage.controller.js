angular
	.module('app')
	.controller('checkdidusage.IndexController', ['telephonyService', '$location', '$state', '$stateParams', function(telephonyService, $location, $state, $stateParams) {
		
		var vm = this;
		
		vm.refresh = function (){
			$state.reload();
		};

		vm.messages = 'Loading Didblocks...';
		
		var id = $stateParams.id;
		
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
		
		vm.types = [{
				id: 1,
				name: 'public'
			}, {
				id: 2,
				name: 'private'
			}];
		
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
			
			
			telephonyService.searchDidblockNumbersinArray(angular.copy(vm.checkdid)).then(function(res) {
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
						
						var value = v[key];
						
						console.log(value);
						
						/*
						if (typeof(value) == "undefined" && value == null){
							number['status'] = "Not Found";
							numberstable.push(number);
							continue;
						*/
						if (value == false){
							number['status'] = "Not Found";
							numberstable.push(number);
							continue;
						}
						
						//console.log('value');
						//console.log(value[0].status);

						number['status'] = value[0].status;
						
						// Push Number info onto the table data array. 
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
