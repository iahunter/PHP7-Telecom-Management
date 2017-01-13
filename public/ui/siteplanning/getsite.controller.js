angular
	.module('app')
	.controller('getSite.IndexController', ['siteService', '$location', '$state', '$stateParams', function(siteService, $location, $state, $stateParams) {
		
		var vm = this;
		
		vm.refresh = function (){
			$state.reload();
		};

		vm.messages = 'Loading sites...';
		
		var id = $stateParams.id;
		
		vm.getsite = siteService.getsite(id)
			.then(function(res){
				

				vm.site = res.data.site;
				//return vm.site;
				
			}, function(err){
				//Error
			});
		
		vm.getsitephones = siteService.getsitephones(id)
			.then(function(res){
				//success
				//console.log("HERE ");console.log(res)
				console.log(res);
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

	}]);
