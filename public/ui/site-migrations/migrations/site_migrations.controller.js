angular
	.module('app')
	.controller('Site.Migration.Controller', ['siteMigrationService', 'siteService', 'cucmService', '$location', '$state', '$stateParams', function(siteMigrationService, siteService, cucmService, $location, $state, $stateParams) {
	
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
		

		var sitecode = $stateParams.id;
		vm.sitecode = $stateParams.id;
		console.log(sitecode)

		vm.siteForm = {}
		vm.siteForm.sitecode = ""
		vm.siteForm.sitecode = $stateParams.id;
		
		function get_site_migrations(sitecode) {
			siteMigrationService.listSiteMigrationsBySitecode(sitecode)
			
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
						
						vm.migrations = res.data.result;
						console.log(vm.migrations)
						vm.loading = false;
						
					}
					
				}, function(err){
					console.log(err)
					alert(err);
				});
			}
			
		// Call the function 
		get_site_migrations(sitecode);

		
		
		// Create DID Block 
		vm.submitsite = function(form) {

			form.sitecode = form.sitecode.toUpperCase();

			console.log(form);
			
			siteMigrationService.createSiteMigration(angular.copy(form))
				.then(function(data) {
					alert("Migration Added Succesfully" + data);
					$location.path('/sitemigrations/migrations/' + vm.sitecode);
			}, function(error) {
				console.log(error)
				console.log(error.data.message)
				alert('Error: ' + error.data.message + " | Status: " + error.status);
			});

		}
		
		// Edit state for DID block Edit button. 
		vm.edit = {};
		
		// Update DID Block service called by the save button. 
		vm.update = function(migration) {
			// Put the variable that we need into an array to send. We only want to send name, carrier and comment for updates. 
			/*
			var site_update = {};
			site_update.name = site.name;
			site_update.carrier = site.carrier;
			site_update.comment = site.comment;
			
			// Send Block ID and the updated variables to the update service. 
			siteService.updatesite(site.id, site_update).then(function(data) {
			*/	
			siteMigrationService.updateSiteMigration(migration.id, migration).then(function(data) {
			  //alert('Site Updated!')
			  $location.path('/sitemigrations/migrations/' + vm.sitecode);
			}, function(error) {
				alert('An error occurred while updating the site')
			});
			//$state.reload();
		}
		
		
		// Delete DID Block 
		vm.delete = function(migration) {
			siteMigrationService.deleteSiteMigration(migration.id).then(function(data) {

			
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
	

