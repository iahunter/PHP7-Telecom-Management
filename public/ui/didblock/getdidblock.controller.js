angular
	.module('app')
	.controller('getDidblock.IndexController', ['telephonyService', '$location', '$state', '$stateParams', function(telephonyService, $location, $state, $stateParams) {
		
		var vm = this;
		
		vm.refresh = function (){
			$state.reload();
		};

		vm.messages = 'Loading Didblocks...';
		//vm.didblock = [{}];
		vm.didblock = [{}];
		vm.dids = [{}];
		
		//vm.search = "";
		
		var id = $stateParams.id;
		
		vm.didblock = telephonyService.getDidblock(id);
		console.log(vm.didblock);
		
		vm.dids = telephonyService.getDidblockDids(id);
		console.log(vm.dids);
		
		function initController() {
			telephonyService.getDidblockDids(function (result, id) {
				console.log('callback from telephonyService.GetDidblock responded ' + result);
				vm.dids = telephonyService.dids;
				
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
		
		
		// Edit state for DID block Edit button.
		vm.edit = {};
		
		// Update DID Block service called by the save button.
		vm.update = function(did) {
			// Put the variable that we need into an array to send. We only want to send name, carrier and comment for updates.
			var didblock_update = {};
			didblock_update.name = did.name;
			didblock_update.carrier = did.carrier;
			didblock_update.comment = did.comment;
			
			// Send Block ID and the updated variables to the update service.
			telephonyService.updateDid(did.id, did_update).then(function(data) {
			  return $state.reload();
			}, function(error) {
				alert('An error occurred while updating the event')
			});
			$state.reload();
		}

	}]);
