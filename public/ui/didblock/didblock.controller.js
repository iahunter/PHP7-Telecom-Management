angular
	.module('app')
	.controller('Didblock.IndexController', ['telephonyService', '$location', function(telephonyService, $location) {
		
		var vm = this;
		
		initController();
		
		vm.didblockForm = {};

		vm.messages = 'Loading Didblocks...';
		vm.didblocks = [{}];
		
		//vm.search = "";

		function initController() {
			telephonyService.GetDidblock(function (result) {
				console.log('callback from telephonyService.GetDidblock responded ' + result);
				vm.didblocks = telephonyService.didblocks;
				
				console.log(vm.didblocks);
				vm.messages = JSON.stringify(vm.didblocks, null, "    ");
				//$scope.accounts = vm.accounts;
			});
		}
		
		
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
		
		vm.submitDidblock = function(form) {
			form.status = this.selectedOption.name;
			form.type = this.selectedtype.name;
			console.log("Category: " + form.category);
			
			
			telephonyService.createDidblock(angular.copy(form)).then(function(data) {
				alert("Didblock Added Succesfully" + data);
			}, function(error) {
				console.log(error)
				console.log(error.data.message)
				alert('Error: ' + error.data.message + " | Status: " + error.status);
			});

		}
		
		
		
	}]);
