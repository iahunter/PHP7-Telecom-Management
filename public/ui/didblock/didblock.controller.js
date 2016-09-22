angular
	.module('app')
	.controller('Didblock.IndexController', ['telephonyService', '$location', '$state', function(telephonyService, $location, $state) {
		
		var vm = this;
		
		initController();
		
		vm.didblockForm = {};
		
		vm.refresh = function (){
			$state.reload();
		};

		vm.messages = 'Loading Didblocks...';
		vm.didblocks = [{}];
		
		//vm.search = "";
		

		function initController() {
			telephonyService.GetDidblocks(function (result) {
				console.log('callback from telephonyService.GetDidblocks responded ' + result);
				vm.didblocks = telephonyService.didblocks;
				
				console.log(vm.didblocks);
				vm.messages = JSON.stringify(vm.didblocks, null, "    ");
				//$scope.accounts = vm.accounts;
			});
		}
		
		
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
		
		// Create DID Block 
		vm.submitDidblock = function(form) {
			form.status = this.selectedOption.name;
			form.type = this.selectedtype.name;
			console.log("Category: " + form.category);
			
			
			telephonyService.createDidblock(angular.copy(form)).then(function(data) {
				alert("Didblock Added Succesfully" + data);
				$state.go('didblock');
			}, function(error) {
				console.log(error)
				console.log(error.data.message)
				alert('Error: ' + error.data.message + " | Status: " + error.status);
			});

		}
		
		// Edit state for DID block Edit button. 
		vm.edit = {};
		
		// Update DID Block service called by the save button. 
		vm.update = function(didblock) {
			// Put the variable that we need into an array to send. We only want to send name, carrier and comment for updates. 
			var didblock_update = {};
			didblock_update.name = didblock.name;
			didblock_update.carrier = didblock.carrier;
			didblock_update.comment = didblock.comment;
			
			// Send Block ID and the updated variables to the update service. 
			telephonyService.updateDidblock(didblock.id, didblock_update).then(function(data) {
			  return $state.reload();
			}, function(error) {
				alert('An error occurred while updating the event')
			});
			$state.reload();
		}
		
		
		// Delete DID Block 
		vm.delete = function(didblock) {
			telephonyService.deleteDidblock(didblock.id).then(function(data) {
				return $state.reload();
          }, function(error) {
				alert('An error occurred');
          });

		}
		
		
		
	}]);
