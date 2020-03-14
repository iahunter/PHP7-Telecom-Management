angular
	.module('app')
	.controller('ConfigRepo.Controller', ['PageService', 'CompanyService', '$scope', '$location', '$state', '$stateParams', '$sce',  function(PageService, CompanyService, $scope, $location, $state, $stateParams, $sce) {
	
		var vm = this;
		
		vm.permissions = window.telecom_mgmt_permissions;
		
		var id = $stateParams.id; 
		
		//console.log($stateParams); 
		//console.log($state.current.name); 
		
		if($state.current.name == "ciscositeconfig"){
			
			// Get Subversion URL and Concatinate the ID. 
			vm.getcompanycontent = CompanyService.getcompanycontent()
				.then(function(res){
					
					// "ciscositeconfig": "//svnserver.domain.com/websvn/filedetails.php?repname=Cisco+CallManager+Repository&path=%2Fsites%2F",
					
					var url = res.data.ciscositeconfig;

					// Build Trusted URL
					vm.iframe = $sce.trustAsResourceUrl(url + id); 
					
				}, function(err){
					vm.loading = false;
				});
		}
		
		if($state.current.name == "ciscophoneconfig"){
			
			// Get Subversion URL and Concatinate the ID. 
			vm.getcompanycontent = CompanyService.getcompanycontent()
				.then(function(res){
					
					// "ciscophoneconfig": "//svnserver.domain.com/websvn/filedetails.php?repname=Cisco+CallManager+Repository&path=%2Fphones%2F"
	
					var url = res.data.ciscophoneconfig;
					
					// Build Trusted URL
					vm.iframe = $sce.trustAsResourceUrl(url + id); 
					
				}, function(err){
					vm.loading = false;
				});
		}
		
	}])
	