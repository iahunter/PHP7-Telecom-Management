angular
	.module('app')
	.controller('getPhonePlan.IndexController', ['sitePhonePlanService', 'cucmService', '$location', '$state', '$stateParams', function(sitePhonePlanService, cucmService, $location, $state, $stateParams) {
		
		var vm = this;
		
		vm.refresh = function (){
			$state.reload();
		};
		
		
		vm.isArray = angular.isArray;
		
		

		vm.messages = 'Loading sites...';
		
		var id = $stateParams.id;
		
		vm.deploybutton = false;
		
		vm.getphoneplan = sitePhonePlanService.getphoneplan(id)
			.then(function(res){
				// Check if Token has expired. If so then direct them to login screen. 
				if(res.message == "Token has expired"){
					vm.tokenexpired = true;
					//alert("Token has expired, Please relogin");
					//alert(res.message);
					$state.go('logout');
				}
				vm.site = res.data.result;
				
				return vm.site
				
				
			}, function(err){
				//Error
			});
		
		vm.getphoneplanphones = sitePhonePlanService.getphoneplanphones(id)
			.then(function(res){
				// Check if Token has expired. If so then direct them to login screen. 
				if(res.message == "Token has expired"){
					vm.tokenexpired = true;
					//alert("Token has expired, Please relogin");
					//alert(res.message);
					$state.go('logout');
				}
				vm.phones = res.data.result;
				
				return vm.phones
				
				
			}, function(err){
				//Error
			});
			
	
		

		
		// Edit state for phone block Edit button.
		vm.edit = {};
		
		// Update phone Block service called by the save button.
		vm.update = function(phone) {
			// Put the variable that we need into an array to send. We only want to send name, carrier and comment for updates. 
			var phone_update = {};
			phone_update.name = phone.name;
			phone_update.status = phone.status;
			phone_update.system_id = phone.system_id;
			
			// Send Block ID and the updated variables to the update service. 
			sitePhonePlanService.updatephone(phone.id, phone).then(function(data) {
			  //return $state.reload();
			}, function(error) {
				alert('An error occurred while updating the event')
			});
			//$state.reload();
		}
		
		// Delete DID Block 
		vm.delete = function(phone) {
			sitePhonePlanService.deletephone(phone.id).then(function(data) {

			
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
