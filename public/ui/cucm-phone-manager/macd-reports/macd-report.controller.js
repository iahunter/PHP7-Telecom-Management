angular
	.module('app')
	.controller('MacdReport.Controller', ['macdService','$location', '$interval', '$state', '$scope', '$stateParams', function(macdService, $location, $interval, $state, $scope,$stateParams) {
	
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
		vm.loading = true;


		// Match the window permission set in login.js and app.js - may want to user a service or just do an api call to get these. will decide later. 
		vm.permissions = window.telecom_mgmt_permissions;

		if(!vm.permissions.read.Cucmclass){
			
			if(!vm.permissions.read.Cucmclass){
				$location.path('/accessdenied');
			}
		}
		

		list_macds_parents();

		function list_macds_parents(){
			macdService.list_macds_parents()
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
					}else{
						//console.log(res)
						vm.logs = res.data.result;
						console.log(vm.logs)
						
						// Convert DB Timestamp to local PC Time. 
						angular.forEach(vm.logs, function(log) {

							// Convert UTC to local time
							var dateString = log.created_at;
							//console.log(dateString)
							created_at = moment().utc().format(dateString);
							created_at = moment.utc(created_at).toDate();
							log.created_at_local = created_at.toLocaleString()
							//console.log(log.created_at_local)

						});
						
						vm.loading = false;

					}
					
				}, function(err){
					alert(err);
				});
		}

		var pulllogactivity = $interval(list_macds_parents,60000); 
		
		// Get current local Date and Time to display on page
		function getdatetime(){
			date = new Date();
			vm.datetime = date.toLocaleString()
		}	
		
		// Update the date and time every second. 
		var updatetime = $interval(getdatetime,1000); 
		
		// Stop polling when you leave the page. 
		$scope.$on('$destroy', function() {
			//console.log($scope);
            $interval.cancel(pulllogactivity);
			$interval.cancel(updatetime);
		});
		

	}])
