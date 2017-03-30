angular
	.module('app')
	.controller('siteTrunking911Report.IndexController', ['cucmReportService', 'PageService','$location', '$state', '$stateParams', function(cucmReportService, PageService, $location, $state, $stateParams) {
	
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

		vm.getpage = PageService.getpage('siteTrunking911Report')

		vm.cucmsitetrunkreport = cucmReportService.listsitetrunkingreport()
			.then(function(res){
				// Check for errors and if token has expired. 
				if(res.data.message){
					console.log(res);
					vm.message = res.data.message;
					console.log(vm.message);
				}else{
					vm.sites = res.data.response;
					vm.loading = false;
				}
				
			}, function(err){
				alert(err);
			});
				

		

	}])

