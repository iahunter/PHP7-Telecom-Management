angular
	.module('app')
	.controller('getDidblock.IndexController', ['telephonyService', 'PageService', '$location', '$state', '$stateParams', function(telephonyService, PageService, $location, $state, $stateParams) {
		
		var vm = this;
		
		vm.refresh = function (){
			$state.reload();
		};

		vm.messages = 'Loading Didblocks...';
		
		// Match the window permission set in login.js and app.js - may want to user a service or just do an api call to get these. will decide later. 
		vm.permissions = window.telecom_mgmt_permissions;
		
		var id = $stateParams.id;
		
		//vm.getpage = PageService.getpage('getdidblock-' + id)
		
		vm.getdidblock = telephonyService.getDidblock(id)
			.then(function(res){
				
				//** Loop thru and create chart data for block. 
					vm.didblock = res.data.didblock;
					//console.log(block.stats);
					vm.didblock['chartlabels'] = [];
					vm.didblock['chartdata'] = [];
					//vm.didblock['chartseries'] = [];
					
					for(var key in vm.didblock.stats){
						//console.log(key);
						//console.log(vm.didblock.stats[key]);
						
						vm.didblock['chartlabels'].push(key);
						vm.didblock['chartdata'].push(vm.didblock.stats[key]);
						//vm.didblock['chartseries'].push(key);
						
					}
					
					
					// Enable the Options to be generated for the chart. 
					//vm.didblock.chartoptions = { responsive: true, legend: { display: true}, title: {display:true, text:'Number Block Usage'}};
					vm.didblock.chartoptions = { responsive: true,};
					
					//console.log(vm.didblock.chartoptions);
					
				//** End of Chart Data
				//console.log(vm.didblock.stats.inuse);
				
				//Calculate size and usage percentage. 
				vm.didblock.size = vm.didblock.end - vm.didblock.start + 1;
				vm.didblock.capacity = vm.didblock.stats.inuse / vm.didblock.size * 100;
					
				//console.log(vm.didblock);
				return vm.didblock;
				
			}, function(err){
				//Error
			});
		
		vm.getdidblockdids = telephonyService.getDidblockDids(id)
			.then(function(res){
				//success
				//console.log("HERE ");console.log(res)
				
				vm.dids = res.data.dids;
				
				/*
				// Loop thru all the dids and get did
				angular.forEach(vm.dids,function(did){
				//console.log(did);
				
					// Loop thru and get all the assignments
					angular.forEach(did,function(assignments){
					//console.log(assignments);
					
					// Can we extract and push a key:value to an existing object

					})

				})
				*/
				
				return vm.dids
				
				
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
				//alert('Saved')
			  //return $state.reload();
			}, function(error) {
				alert('An error occurred while updating Number Block')
			});
			//$state.reload();
		}
		
		
		// Edit state for DID block Edit button.
		vm.editdid = {};
		
		// Update DID Block service called by the save button.
		vm.updatedid = function(did) {
			// Put the variable that we need into an array to send. We only want to send name, carrier and comment for updates. 
			var did_update = {};
			did_update.name = did.name;
			did_update.status = did.status;
			did_update.system_id = did.system_id;
			
			// Send Block ID and the updated variables to the update service. 
			telephonyService.updateDid(did.id, did_update).then(function(data) {
			  //return $state.reload();
			}, function(error) {
				alert('An error occurred while updating the event')
			});
			//$state.reload();
		}

	}]);
