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

		vm.get_erl = west911EnableService.list_erls_and_phone_counts()
			.then(function(res){
				// Check for errors and if token has expired. 
				if(res.data.message){
					//console.log(res);
					vm.message = res.data.message;
					//console.log(vm.message);
				}else{
					
					vm.report = res.data.result
					console.log(vm.report)
					
					vm.totalphones = 0
					
					angular.forEach(vm.report, function(erl){
						vm.totalphones = vm.totalphones + erl.phonecount
						
						if(erl.phonecount){
							console.log(erl)
							
							// Get the Device Pool that most of the phones are using in that erl. 
							cucmReportService.get_devicepool_from_phones_in_erl(erl.erl_id)
							.then(function(res){
								// Check for errors and if token has expired. 
								
								console.log(res)
								if(res.data.message){
									//console.log(res);
									vm.message = res.data.message;
									//console.log(vm.message);
								}else{
									
									erl.devicepool_site = res.data.response
									console.log(erl.devicepool_site)

								}
								
							}, function(err){
								alert(err);
							});
						}
						
					});
					
					//console.log(vm.totalphones)
					
					
					vm.loading = false;
				}
				
			}, function(err){
				alert(err);
			});
		
		

	}])
