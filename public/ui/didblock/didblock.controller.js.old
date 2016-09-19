(function () {
    'use strict';

    angular
        .module('app')
        .controller('Didblock.IndexController', Controller);

	
    function Controller($location, TelephonyService) {
        var vm = this;

		
        initController();

		vm.messages = 'Loading Didblocks...';
		vm.didblocks = {};

        function initController() {
			TelephonyService.GetDidblock(function (result) {
				console.log('callback from TelephonyService.GetDidblock responded ' + result);
				vm.didblocks = TelephonyService.didblocks;
				
				console.log(vm.didblocks);
				vm.messages = JSON.stringify(vm.didblocks, null, "    ");
				//$scope.accounts = vm.accounts;
			});
        }
		
    }
	
	

})();