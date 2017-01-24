angular
	.module('app')
	.controller('getSite.IndexController', ['siteService', 'cucmService', '$location', '$state', '$stateParams', function(siteService, cucmService, $location, $state, $stateParams) {
		
		var vm = this;
		
		vm.refresh = function (){
			$state.reload();
		};
		
		
		vm.isArray = angular.isArray;
		
		

		vm.messages = 'Loading sites...';
		
		var id = $stateParams.id;
		
		vm.deploybutton = false;
		
		/*
		vm.getsite = siteService.getsite(id)
			.then(function(res){

				vm.site = res.data.site;
				vm.sitecode = vm.site.sitecode;
				//console.log(vm.sitecode);
				
			}, function(err){
				//Error
			});
		*/
		
		
		vm.getsitephones = siteService.getsitephones(id)
			.then(function(res){
				//success
				//console.log("HERE ");console.log(res)
				//console.log(res);
				vm.phones = res.data.phones;
				
				
				/*
				// Loop thru all the phones and get phone
				angular.forEach(vm.phones,function(phone){
				console.log(phone);
				
					// Loop thru and get all the assignments
					angular.forEach(phone,function(assignments){
					console.log(assignments);
					
					// Can we extract and push a key:value to an existing object

					})

				})
				*/
				
				
				return vm.phones
				
				
			}, function(err){
				//Error
			});
		

		// Drop down values to use in Add form.
		vm.states = [{
				id: 1,
				name: 'available'
			}, {
				id: 2,
				name: 'reserved'
			}];
		
		vm.types = [{
				id: 1,
				name: 'public'
			}, {
				id: 2,
				name: 'private'
			}];
		
		
		// Edit state for phone block Edit button.
		vm.edit = {};
		
		// Update phone Block service called by the save button.
		vm.update = function(phone) {
			// Put the variable that we need into an array to send. We only want to send name, carrier and comment for updates. 
			var phone_update = {};
			phone_update.name = phone.name;
			phone_update.status = phone.status;
			phone_update.system_id = phone.system_id;
			
			// Send Block ID and the updated variables to the update service. 
			siteService.updatephone(phone.id, phone_update).then(function(data) {
			  //return $state.reload();
			}, function(error) {
				alert('An error occurred while updating the event')
			});
			//$state.reload();
		}
		
		
		
		vm.getsitesummary = function (id) {
			
			vm.site = "";
			vm.sitecode = "";
			
			vm.getsite = siteService.getsite(id)
				.then(function(res){

					vm.site = res.data.site;
					vm.sitecode = res.data.site.sitecode;
					//console.log(vm.sitecode);
					
						// Check CUCM for Site Config After we have the sitecode from the database. 
						cucmService.getsitesummary(vm.sitecode)
							.then(function(res){
								
								var cucmsitesummary = res.data.response;
								
								//console.log(cucmsitesummary);
								
								if (res.data.response == 0){
									vm.deploybutton = true;
									
									return vm.cucmsitesummary = false;
								}else{
									cucmsitesummary = res.data.response;
								}
								
								vm.cucmsitesummary = {};
								// Loop thru and append to a simple array so we can do a simple select on it with ng-options.
								
								angular.forEach(cucmsitesummary, function(k,v) {
									
										//console.log("VALUE: " + v);
										//vm.cucmsitesummary
										angular.forEach(k, function(key,object) {
											if(key.length != 0){
												//vm.cucmsitesummary['length']++;
												if (!vm.cucmsitesummary[v]){
													vm.cucmsitesummary[v] = [];
													if(key){
														vm.cucmsitesummary[v].push(key);
													}
													
												}else{
													if(key){
														vm.cucmsitesummary[v].push(key);
													}
												}
												
											}
										});
									
									//console.log(vm.cucmsitesummary);
									
								});
								
								if(vm.cucmsitesummary == 0){
									console.log("Does not exist in CUCM");
									vm.cucmsitesummary = false;
								}
								
							}, function(err){
								//Error
							});
					
				}, function(err){
					//Error
				});
				
				
				

		};
		
		var getsitesummary = vm.getsitesummary(id)
		
		
		vm.deploycucmsite = function () {
			// Update $scope values to form data. 

			console.log(vm.site);
			vm.cucmloading = true;
			
			var site = {};
			site.sitecode = vm.site.sitecode;
			
			if(vm.site.trunking == 'sip' && vm.site.e911 == '911enable' ){
				site.type = 1;
			}
			else if(vm.site.trunking == 'local' && vm.site.e911 == '911enable' ){
				site.type = 2;
			}
			else if(vm.site.trunking == 'sip' && vm.site.e911 == 'local' ){
				site.type = 3;
			}
			else if(vm.site.trunking == 'local' && vm.site.e911 == 'local' ){
				site.type = 4;
			}
			
			site.srstip = vm.site.srstip;
			site.h323ip = vm.site.h323ip;
			site.timezone = vm.site.timezone;
			site.npa = vm.site.npa;
			site.nxx = vm.site.nxx;
			site.didrange = vm.site.didrange;
			site.operator = vm.site.operator;
			
			console.log(site);
		
			
			// Call the validate address service. 
			cucmService.createcucmsite(site).then(function(data) {
				
				vm.deploysiteresult = data.data.response;
				
				vm.cucmloading = false;
				return vm.deploysiteresult;
				
				/*
				// Set valid and invalid address variable so we can alert success or failure. 
				if($scope.validateAddress.success==true){
					$scope.validaddress=true;
				}
				else{
					$scope.invalidaddress=true;
				}
				*/
			});
			
		};

	}]);