angular
	.module('app')
	.controller('lineCleanup.Report.IndexController', ['cucmReportService', 'cucmService', 'PageService','$location', '$state', '$scope', '$stateParams', '$filter',  function(cucmReportService, cucmService, PageService, $location, $state, $scope,$stateParams, $filter) {
	
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

		vm.line_cleanup_report = cucmReportService.line_cleanup_report()
			.then(function(res){
				// Check for errors and if token has expired. 
				if(res.data.message){
					//console.log(res);
					vm.message = res.data.message;
					//console.log(vm.message);
				}else{
					
					vm.reports = res.data.response
					vm.report = res.data.response.lines_to_delete;
					//vm.report = res.data.response.lines_with_mailbox_built;
					//vm.report = res.data.response.lines_with_cfa_active;
					
					vm.options = [];
					angular.forEach(vm.reports, function(value, key) {
						vm.options.push(key)
					});
					vm.reports = Object.keys(vm.reports).map(key => vm.reports[key]);
					//console.log(vm.options)

					// Convert Object to array to allow sorting 
					vm.report = Object.keys(vm.report).map(key => vm.report[key]);
					//console.log(vm.report)
					
 
					vm.loading = false;
				}
				
			}, function(err){
				alert(err);
			});
		
		vm.change_report = function (newreport){
			
			vm.selectAll = false;
			
			angular.forEach(vm.report, function(line) {
			  line.select = false;
			  //console.log(line);
			  //vm.cucmphoneselecttouched();
			});

			angular.forEach(vm.reports, function(value, key) {
				if (key == newreport){
					
					vm.report = Object.keys(value).map(key => value[key]);
					
					console.log(vm.report)
				}
			
			});
			
		}
		
		vm.checkAllLines = function() {
			
			console.log("Hitting check all")
			
			// The filter allows us to only select ones that are matching our filter if one exists. 
			var filter = $filter('filter'); 
			
			var filtered = filter(vm.report, vm.search);
			
			angular.forEach(filtered, function(line) {
			  line.select = vm.selectAll;
			  //console.log(line);
			  //vm.cucmphoneselecttouched();
			});
		  };
		  
		
		/* Old Way
		vm.checkAllLines = function() {
			console.log("Hitting check all")

			angular.forEach(vm.report, function(line) {
			  line.select = vm.selectAll;
			  //console.log(line);
			  //vm.cucmphoneselecttouched();
			});
		  };
		*/
		
		
		vm.deletecucmline = function(line) {
			
			console.log(line.pattern)
			
			cucmService.deletelinebyuuid(line.uuid)
				.then(function(res) {
					
					console.log(res)
					
					if(res.data.deleted){
						console.log(line.pattern + " Successfully Deleted")
						phone = null;
					}
					//console.log(res)
					line.deleted = true;
					console.log(line)
					
			  }, function(error) {
					alert('An error occurred');
			  });
			
		}
		
		vm.cucmlinedeleteselected = function(lines){
			angular.forEach(lines, function(line) {
				if(line.select == true){
					//console.log(phone);
					vm.deletecucmline(line);
				}
				
			});
				
		}

	}])

