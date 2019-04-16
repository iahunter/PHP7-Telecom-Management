angular
	.module('app')
	.controller('Users.Permissions.Controller', ['PermissionsService','$location', '$interval', '$state', '$scope', '$stateParams', function(PermissionsService, $location, $interval, $state, $scope,$stateParams) {
	
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

		if(!vm.permissions.read.Cucmsiteconfigs){
			$location.path('/accessdenied');
		}

		permissions();

		function permissions(){
			PermissionsService.getuserspermissions()
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
						vm.usersreponse = res.data.result;
						
						
						// Convert DB Timestamp to local PC Time. 
						vm.users = []
						angular.forEach(vm.usersreponse, function(value,key) {
							vm.user = {}; 
							console.log(value)
							
							vm.user.username = key
							vm.user.permissions = {}
							if(value.read){
								vm.user.permissions.read = value.read
							}else{vm.user.permissions.read = {}}
							vm.user.permissions.create = value.create
							vm.user.permissions.update = value.update
							vm.user.permissions.delete = value.delete
							
							vm.users.push(vm.user)
						});
						
						console.log(vm.users)
						vm.loading = false;

					}
					
				}, function(err){
					alert(err);
				});
		}

		//var pulllogactivity = //$interval(permissions,5000); 
		
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

