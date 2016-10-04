angular
	.module('app')
	.filter('sumofValue', function () {
		return function (data, key) {        
			//console.log(key);
			
			if (angular.isUndefined(data) && angular.isUndefined(key))
				return 0;        
			
			var sum = 0;
			angular.forEach(data,function(value){
				//console.log(value);
				//console.log(parseInt(value[key]));
				sum = sum + (parseInt(value[key]));
			});
			//console.log(sum);
			return sum;
		}
	})

	// Look at moving this inside my controller. Too slow and bulky. Can't extract varibles out to display. 
	.filter('countTypes', function () {
		return function (data, key) {        
			
			var obj = {};
			angular.forEach(data,function(value){

			//obj['name'] = 'test';
			//console.log(obj);
			
			//console.log(value);
			
			if (obj[value[key]] == undefined){
				//console.log("WE ARE HERE INSIDE IF");
				obj[value[key]] = 1;
				//obj[key] = obj[value.key] + 1;
				//console.log(obj);
			}else{
				//console.log("WE ARE HERE INSIDE ELSE");
				obj[value[key]] = obj[value[key]] + 1;;
			}
				
			//console.log(obj);
			
			
			});
		
			var returns = '';
			angular.forEach(obj,function(value, key){
			//console.log(key);
			//console.log(value);

			returns += key + ": " + value + ', ';
			});

			return returns;
		
		}
		
	})
	
	

	.controller('Didblock.IndexController', ['telephonyService', '$location', '$state', function(telephonyService, $location, $state) {
	
		var vm = this;
		
		initController();
		
		vm.didblockForm = {};
		
		vm.refresh = function (){
			$state.reload();
		};

		vm.messages = 'Loading Didblocks...';
		vm.didblocks = [{}];
		vm.loading = true;
		//vm.search = "";
		
		/*
		$scope.getTotal = function(){
			var total = 0;
			for(var i = 0; i < $scope.cart.products.length; i++){
				var product = $scope.cart.products[i];
				total += (product.price * product.quantity);
			}
			return total;
		}
		*/
		


		function initController() {
			telephonyService.GetDidblocks(function (result) {
				console.log('callback from telephonyService.GetDidblocks responded ' + result);
				vm.didblocks = telephonyService.didblocks;
				
				// Work on getting percentages 
				
				
				//** Loop thru and create chart data for each block. 
				angular.forEach(vm.didblocks,function(block){
					//console.log(block.stats);
					block['chartlabels'] = [];
					block['chartdata'] = [];
					block['chartseries'] = [];
					
					for(var key in block.stats){
						//console.log(key);
						//console.log(block.stats[key]);
						
						block['chartlabels'].push(key);
						block['chartdata'].push(block.stats[key]);
						block['chartseries'].push(key);
						
					}
				})
				//** End of Chart Data

				vm.loading = false;
				//console.log(vm.didblocks);
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
				//alert('Saved')
			  //return $state.reload();
			}, function(error) {
				alert('An error occurred while updating the event')
			});
			//$state.reload();
		}
		
		
		// Delete DID Block 
		vm.delete = function(didblock) {
			telephonyService.deleteDidblock(didblock.id).then(function(data) {

			
				// jQuery Hack to fix body from the Model. 
					$(".modal-backdrop").hide();
					$('body').removeClass("modal-open");
					$('body').removeClass("modal-open");
					$('body').removeAttr( 'style' );
				// End of Hack */
			
				return $state.reload();
          }, function(error) {
				alert('An error occurred');
          });

		}
		
		
		
	}]);
