angular
	.module('app')
	.controller('Site.IndexController', ['siteService', '$location', '$state', function(siteService, $location, $state) {
	
		var vm = this;
		
		initController();
		
		vm.siteForm = {};
		
		vm.refresh = function (){
			$state.reload();
		};

		vm.messages = 'Loading sites...';
		vm.sites = [{}];
		vm.loading = true;

		function initController() {
			siteService.Getsites(function (result) {
				console.log('callback from siteService.Getsites responded ' + result);
				vm.sites = siteService.sites;
				
				vm.loading = false;
				vm.messages = JSON.stringify(vm.sites, null, "    ");

			});
		}
		
		vm.getdatetimegrps = siteService.getcucmdatetimegrps()
			.then(function(res){
				//success
				//console.log("HERE ");console.log(res)
				//console.log(res);
				
				var groups = res.data.response;
				
				
				// Create our blank simple array for datatimegrps 
				vm.datetimegrps = [];
				
				// Loop thru and append to a simple array so we can do a simple select on it with ng-options.
				angular.forEach(groups, function(value, key) {
				  console.log(value);
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
				
				console.log(vm.didblocks);
				/*
				// Create our blank simple array for datatimegrps 
				vm.datetimegrps = [];
				
				// Loop thru and append to a simple array so we can do a simple select on it with ng-options.
				angular.forEach(groups, function(value, key) {
				  console.log(value);
				  // Push value to array. 
				  vm.datetimegrps.push(value);
				});
				*/
				
				//console.log(vm.datetimegrps);
				return vm.didblocks;
				
				
			}, function(err){
				alert(err);
			});
		
		
		
		// Drop down values to use in Add form. 
		vm.extlength = [4,5,10];
		

		
		// Create DID Block 
		vm.submitsite = function(form) {

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
				//alert('Saved')
			  //return $state.reload();
			}, function(error) {
				alert('An error occurred while updating the event')
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

