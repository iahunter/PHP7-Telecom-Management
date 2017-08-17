angular
	.module('app')
	.controller('egwErl.Report', ['west911EnableService', 'cucmReportService', 'cucmService', '$location', '$state', '$scope', '$stateParams', '$filter',  function(west911EnableService, cucmReportService, cucmService, $location, $state, $scope,$stateParams, $filter) {
	
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

		vm.messages = 'Loading...';
		vm.loading = true;

		// Match the window permission set in login.js and app.js - may want to user a service or just do an api call to get these. will decide later. 
		vm.permissions = window.telecom_mgmt_permissions;

		if(!vm.permissions.read.Cucmclass){
			$location.path('/accessdenied');
		}

		vm.get_erl = west911EnableService.list_erls()
			.then(function(res){
				// Check for errors and if token has expired. 
				if(res.data.message){
					//console.log(res);
					vm.message = res.data.message;
					//console.log(vm.message);
				}else{
					
					vm.report = res.data.result
					console.log(vm.report)
					vm.loading = false;
				}
				
			}, function(err){
				alert(err);
			});
		
		

	}])
