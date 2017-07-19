angular
	.module('app')
	.controller('Site.Migration.Summary.Controller', ['siteMigrationService', 'cucmReportService', 'siteService', 'cucmService', '$location', '$state', '$stateParams', function(siteMigrationService, cucmReportService, siteService, cucmService, $location, $state, $stateParams) {
	
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
		
		// Had to add jquery hack to close the active tab when another one is opened. 
		vm.tabclose = function (id){
			// jQuery Hack to fix body from the Model. 
					$(".tab-pane").removeClass("active in");
				// End of Hack */
		};
		
		// Had to add jquery hack to close the active accordian when another one is opened. 
		vm.accordianclose = function (){
			// jQuery Hack to fix body from the Model. 
					$(".panel-body").removeClass("in");
				// End of Hack */
		};
		
		vm.jsonPrettyprint = function(input) {
			return JSON.stringify(input, undefined, 2);
		}
		
		vm.isJson = function (input) {
			try {
				JSON.parse(input);
			} catch (e) {
				return false;
			}
			return true;
		}

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
							
						
							var getsite = cucmReportService.getsitesummary(vm.migration.sitecode)
								.then(function(res){
									
									// Check for errors and if token has expired. 
									if(res.data.message){
										//console.log(res);
										vm.message = res.data.message;
										//console.log(vm.message);
										
										if(vm.message == "Token has expired"){
											// Send user to login page if token expired. 
											alert(vm.message);
											$state.go('logout');
										}

										return vm.message;
									}

									var response = res.data.response;
									vm.site = response;
									//console.log(vm.cucmsite)
								}, function(err){
									console.log(err)
									alert(err);
								});
						
						
						}, function(err){
							console.log(err)
							alert(err);
						});
					}
			});
		}
			
		// Call the function 
		get_migration_summary(id);
		
		
		// Show Failures for Deploy Phones to CUCM
		vm.showfailuresonly = function() {
			vm.phonefailures = [];
			vm.phonefailures.Phone = [];
			
			angular.forEach(vm.newphones.Phone, function(phone) {
				if(phone.status == "error" || ""  && phone.status == "error"){
					//console.log(phone.Line.status);
					//console.log(phone.Line.status);
					vm.phonefailures.Phone.push(phone);
				}
			})
			vm.phonefailures.Line = [];
			angular.forEach(vm.newphones.Line, function(line) {
				if(line.status == "error" || ""  && line.status == "error"){
					//console.log(phone.Line.status);
					//console.log(phone.Line.status);
					vm.phonefailures.Line.push(phone);
				}
			})
			return vm.phonefailures
		}
		
		
		// Run the migration
		vm.phonemigration = function(migration) {
			vm.newphones = {};
			
			console.log(migration);
			var updated_phones = "";
			var updated_lines = "";
			//console.log(migration);
			var Phones = migration.Phone;
			var Lines = migration.Line;
			
			vm.deploycucmsiteloading = true;
			
			//console.log(Phones);
			console.log(Lines);
			
			// Run Migration
			
			if(Phones){
				//delete Phones.count
				var updated_phones = cucmService.updatephones(angular.copy(Phones));
				vm.newphones.Phone = updated_phones;
			}
			
			if(Lines){
				//Lines.length = angular.copy(Lines.count)
				//delete Lines.count
				console.log(Lines)
				console.log("sadfdsafds")
				var updated_lines = cucmService.updatelines(angular.copy(Lines));
				vm.newphones.Line = updated_lines;
			}
			
			
			console.log(vm.newphones)
			/*
			cucmService.updatephones(Phones)
				.then(function(res) {
					// Check for errors and if token has expired. 
					if(res.data.message){
						//console.log(res);
						vm.message = res.data.message;
						console.log(vm.message);
						return vm.message;
					}else{
						vm.deploysiteresult = res.data.response
					}
				});
			// placeholder for results
			*/
			
			vm.deploycucmsiteloading = false;
		}

		
		// Run the migration
		vm.runmigration = function(verb) {
			vm.deploysiteresult = {}
			var migration = {}
			migration.verb = verb;
			vm.deploycucmsiteloading = true;
			
			console.log(verb);
			
			if(verb == 'Add'){
				migration.migration = vm.migration.change_summary.Add;
			}
			else if(verb == 'Update'){
				migration.migration = vm.migration.change_summary.Update;
			}
			else if(verb == 'Delete'){
				migration.migration = vm.migration.change_summary.Delete;
			}
			else if(verb == 'PhoneUpdate'){
				migration = vm.migration.change_summary.PhoneUpdate;
				//console.log(migration)
				
				vm.phones = vm.migration.change_summary.PhoneUpdate.Phone
				vm.lines = vm.migration.change_summary.PhoneUpdate.Line
				return vm.phonemigration(migration);
				alert('phonemigration - turn on new migration to do in javascript');
			}
			
			console.log("This Ran and it wasn't supposed to")
			// Run Migration
			siteMigrationService.runMigration(migration)
				.then(function(res) {
					// Check for errors and if token has expired. 
					if(res.data.message){
						//console.log(res);
						vm.message = res.data.message;
						console.log(vm.message);
						return vm.message;
					}else{
						vm.deploysiteresult = res.data.response
					}
				});
			// placeholder for results

			vm.deploycucmsiteloading = false;
		}
		
		// Delete Migration
		vm.delete = function(migration) {
			siteMigrationService.deleteSiteMigration(migration.id).then(function(data) {

			
				// jQuery Hack to fix body from the Model. 
					$(".modal-backdrop").hide();
					$('body').removeClass("modal-open");
					$('body').removeClass("modal-open");
					$('body').removeAttr( 'style' );
				// End of Hack */
			
				$location.path('/sitemigrations/migrations/' + vm.sitecode);
				//return $state.reload();
          }, function(error) {
				alert('An error occurred');
          });

		}

	}]);
	

