angular
	.module('app')
	.controller('Site.IndexController', ['siteService', 'cucmService', 'PageService', '$location', '$state', '$stateParams', function(siteService, cucmService, PageService, $location, $state, $stateParams) {
	
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
		
		
		if(!vm.permissions.read.Site){
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
		
		vm.getsites = siteService.Getsites()
		
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
					var sites = res.data.sites;
				
					//console.log('callback from siteService.Getsites responded ');
					//var sites = siteService.sites;
					
					vm.sites = [];
					// Get list of CUCM Sites
					vm.listcucmsites = cucmService.listcucmsites()
						.then(function(res){
							
							vm.cucmsites = res.data.response;
							
							//console.log(vm.cucmsites);
							//return vm.cucmsites;
							
							angular.forEach(sites, function(key,value) {
								site = key.sitecode;
								if(isInArrayNgForeach(site, vm.cucmsites)){

									key.cucmprovisioned = true;
									vm.sites.push(key);
									//console.log(key);
								}else{
									key.cucmprovisioned = false;
									vm.sites.push(key);
									//console.log(key);
								}
								
							});

						}, function(err){
							alert(err);
						});
					
					//console.log(vm.sites)
									
					vm.loading = false;
					
				}
				
			}, function(err){
				console.log(err)
				alert(err);
			});


		vm.getdatetimegrps = cucmService.getcucmdatetimegrps()
			.then(function(res){
				var groups = res.data.response;

				// Create our blank simple array for datatimegrps 
				vm.datetimegrps = [];
				
				// Loop thru and append to a simple array so we can do a simple select on it with ng-options.
				angular.forEach(groups, function(value, key) {
				  // Push value to array. 
				  vm.datetimegrps.push(value);
				});
				
				//console.log(vm.datetimegrps);
				return vm.datetimegrps;
				
				
			}, function(err){
				alert(err);
			});

		vm.getdidblocks = siteService.getdidblocks()
			.then(function(res){
				vm.didblocks = res.data.didblocks;

				return vm.didblocks;

			}, function(err){
				alert(err);
			});
		

		vm.languages = [{
				id: 1,
				name: 'english'
			}, {
				id: 2,
				name: 'french'
			}];
			
		
		
		// Drop down values to use in Add form. 
		vm.extlen = [4,5,10];
		

		var id = $stateParams.id;
		
		if(id != undefined){
			// Fix undefined site error on site list loading.
			vm.getsite = siteService.getsite(id)
			.then(function(res){

				vm.site = res.data.result;
			
			}, function(err){
							//Error
			});
		}
		
		
		// Create DID Block 
		vm.submitsite = function(form) {

			form.sitecode = form.sitecode.toUpperCase();
			form.didrange = form.didrange.toUpperCase();
			console.log(form);
			
			siteService.createsite(angular.copy(form)).then(function(data) {
				alert("site Added Succesfully" + data);
				$state.go('site');
			}, function(error) {
				console.log(error)
				console.log(error.data.message)
				alert('Error: ' + error.data.message + " | Status: " + error.status);
			});

		}
		
		// Edit state for DID block Edit button. 
		vm.edit = {};
		
		// Update DID Block service called by the save button. 
		vm.update = function(site) {
			// Put the variable that we need into an array to send. We only want to send name, carrier and comment for updates. 
			/*
			var site_update = {};
			site_update.name = site.name;
			site_update.carrier = site.carrier;
			site_update.comment = site.comment;
			
			// Send Block ID and the updated variables to the update service. 
			siteService.updatesite(site.id, site_update).then(function(data) {
			*/	
			siteService.updatesite(site.id, site).then(function(data) {
			  //alert('Site Updated!')
			  $location.path('/site/'+id);
			}, function(error) {
				alert('An error occurred while updating the site')
			});
			//$state.reload();
		}
		
		
		// Delete DID Block 
		vm.delete = function(site) {
			siteService.deletesite(site.id).then(function(data) {

			
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
	}])
	
	// Be nice to use a directive at some point to help template HTML
	.directive('trRow', function ($compile) {

		return {
			template: '<tr><td ng-bind="row.id"></td><td><strong ng-bind="row.name"></strong></td><td ng-bind="row.description"></td></tr>'
		};
	});

