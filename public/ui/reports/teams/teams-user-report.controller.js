angular
	.module('app')
	.controller('TeamsUserReport.IndexController', ['teamsReportService', 'PageService','$location', '$state', '$scope', '$stateParams', function(teamsReportService, PageService, $location, $state, $scope,$stateParams) {
	
		var vm = this;

		
		vm.refresh = function (){
			// jQuery Hack to fix body from the Model. 
					$(".modal-backdrop").hide();
					$('body').removeClass("modal-open");
					$('body').removeClass("modal-open");
					$('body').removeAttr( 'style' );
				// End of Hack */
			$state.reload();
		};

		vm.messages = 'Loading sites...';
		//vm.loading = true;

		//vm.getpage = PageService.getpage('siteTrunking911Report')
		
		// Match the window permission set in login.js and app.js - may want to user a service or just do an api call to get these. will decide later. 
		vm.permissions = window.telecom_mgmt_permissions;

		if(!vm.permissions.read.Cucmsiteconfigs){
			$location.path('/accessdenied');
		}
		
		vm.teamsusers = []

		vm.teamsuserreport = teamsReportService.getAllTeamsVoiceUsersbyNumber()
			.then(function(res){
				// Check for errors and if token has expired. 
				if(res.data.message){
					//console.log(res);
					vm.message = res.data.message;
					//console.log(vm.message);
				}else{
					vm.teamsnumbers = res.data.response;
					vm.phonecount = 0
					// Use this to change chart colors. 
					//Chart.defaults.global.colors = ['#FD1F5E','#1EF9A1','#7FFD1F','#68F000'];
					
					console.log(vm.teamsnumbers)
					
					angular.forEach(vm.teamsnumbers, function(number, value) {
						console.log(number)
						vm.phonecount = vm.phonecount + 1
						
						number.cucm = []
						if(number.assignments.CucmNA){
							number.cucmtrue = true
							angular.forEach(number.assignments.CucmNA, function(phone) {
								number.cucm.push(phone.routeDetail)
							})
						}else{
							number.cucmtrue = false
						}
						
						var teamsuser = number.assignments.MicrosoftTeams
						
						angular.forEach(teamsuser, function(user) {
							if(user.enterpriseVoiceEnabled == "True"){
								number.teamsuser = {}
								console.log(user.alias)
								number.teamsuser.alias = user.alias
								number.teamsuser.sipAddress = user.sipAddress
								vm.teamsusers.push(number)
							}
						})
						
						
						console.log(teamsuser)
					
					})
					
					console.log(vm.teamsusers)
						
				}
				
			}, function(err){
				alert(err);
			});
				

		

	}])

