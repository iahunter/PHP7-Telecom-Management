angular
	.module('app')
	.controller('Site.Migration.Summary.Controller', ['siteMigrationService', 'siteService', 'cucmService', '$location', '$state', '$stateParams', function(siteMigrationService, siteService, cucmService, $location, $state, $stateParams) {
	
		var vm = this;
		
		// Match the window permission set in login.js and app.js - may want to user a service or just do an api call to get these. will decide later. 
		vm.permissions = window.telecom_mgmt_permissions;
		
		
		//initController();
		
		vm.siteForm = {};
		
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
		
		vm.loading = true;
		
		// Page Request
		//vm.getpage = PageService.getpage('listsites')
		
		if(!vm.permissions.read.SiteMigration){
			$location.path('/accessdenied');
		}
		
		
		function isInArrayNgForeach(field, arr) {
			var result = false;
			//console.log("HERRE")
			//console.log(field);
			//console.log(arr);
			
			angular.forEach(arr, function(value, key) {
				//console.log(value);
				if(field == value)
					result = true;
			});

			return result;
		}
		
		var id = $stateParams.id;
		
		function get_migration_summary(id) {
			siteMigrationService.getSiteMigration(id)
			
				.then(function(res){
					
					//console.log(res)
					// Check for errors and if token has expired. 
					if(res.data.message){
						//console.log(res);
						vm.message = res.data.message;
						console.log(vm.message);
						
						if(vm.message == "Token has expired"){
							// Send user to login page if token expired. 
							//alert("Token has expired, Please relogin");
							$state.go('logout');
						}

						return vm.message;
					}else{
						
						vm.migration = res.data.result;
						vm.sitecode = vm.migration.sitecode
						console.log(vm.migration)
						
						/*
						// Change Site type based on site design user chooses. This is needed for the Laravel Controller
						if(vm.migration.trunking == 'sip' && vm.migration.e911 == '911enable' ){
							vm.migration.type = 1;
						}
						else if(vm.migration.trunking == 'local' && vm.migration.e911 == '911enable' ){
							vm.migration.type = 2;
						}
						else if(vm.migration.trunking == 'sip' && vm.migration.e911 == 'local' ){
							vm.migration.type = 3;
						}
						else if(vm.migration.trunking == 'local' && vm.migration.e911 == 'local' ){
							vm.migration.type = 4;
						}
						*/
						
						summary = siteMigrationService.getSiteMigrationSummary(vm.migration)
						.then(function(res){
					
							//console.log(res)
							// Check for errors and if token has expired. 
							if(res.data.message){
								//console.log(res);
								vm.message = res.data.message;
								console.log(vm.message);

								return vm.message;
							}else{
								vm.migration.change_summary = [];
								vm.migration.change_summary = res.data.response.changes;
								vm.migration.type = res.data.response.type;
								//vm.migration.change_summary.Add = Object.keys(vm.migration.change_summary.Add).map(key => vm.migration.change_summary.Add[key]);
								console.log(vm.migration.change_summary)
								vm.loading = false;

								
								console.log(vm.migration)
								
							}
						
						}, function(err){
							console.log(err)
							alert(err);
						});
					}
			});
		}
			
		// Call the function 
		get_migration_summary(id);

		
		
	}]);
	

