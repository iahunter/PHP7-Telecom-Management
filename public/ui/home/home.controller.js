angular
	.module('app')
	.controller('Home.IndexController', ['UserService', 'PageService', '$location', '$state', function(UserService, PageService, $location, $state) {
		var vm = this;

		initController();

		vm.messages = 'Loading Userinfo...';
		vm.userinfo = {};
		
		vm.getpage = PageService.getpage('home');
		
		vm.gettest = PageService.gettest()
			.then(function(res){
				// Check for errors and if token has expired. 
				console.log(res)
				vm.test = res;

			}, function(err){
				console.log(err)
			});
			


		function initController() {
			UserService.Getuserinfo(function (result) {
				//console.log('callback from UserService.userinfo responded ' + result);
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
		

	}]);
	
	