(function () {
    'use strict';

    angular
        .module('app')
        .controller('Admin.IndexController', Controller);

	
    function Controller($location, UserService) {
        var vm = this;
		
		
        initController();

		vm.messages = 'Loading Userinfo...';
		vm.userinfo = {};

        function initController() {
			UserService.Getuserinfo(function (result) {
				console.log('callback from UserService.userinfo responded ' + result);
				vm.userinfo = UserService.userinfo;
				//vm.username = vm.userinfo.cn[0];
				vm.username = vm.userinfo.cn[0];
				vm.title = vm.userinfo.title[0];
				vm.photo = vm.userinfo.thumbnailphoto[0];
				
				//console.log(vm.userinfo);
				vm.messages = JSON.stringify(vm.userinfo, null, "    ");
				//$scope.accounts = vm.accounts;
			});
        }
		
    }
	
	

})();