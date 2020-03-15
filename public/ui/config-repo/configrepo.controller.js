angular
	.module('app')
	.controller('ConfigRepo.Controller', ['PageService', 'CompanyService', '$scope', '$location', '$state', '$stateParams', '$sce',  function(PageService, CompanyService, $scope, $location, $state, $stateParams, $sce) {
	
		var vm = this;
		
		vm.permissions = window.telecom_mgmt_permissions;
		
		var id = $stateParams.id; 
		
		//console.log($stateParams); 
		//console.log($state.current.name); 
		
		if($state.current.name == "sonusconfigrepo"){
			
			// Get Subversion URL and Concatinate the ID. 
			vm.getcompanycontent = CompanyService.getcompanycontent()
				.then(function(res){
					
					// "sonusconfigrepo": "//svnserver.domain.com/websvn/listing.php?repname=Sonus+Repository",
					
					var url = res.data.sonusconfigrepo;

					// Build Trusted URL
					vm.iframe = $sce.trustAsResourceUrl(url); 
					
				}, function(err){
					vm.loading = false;
				});
		}
		
		if($state.current.name == "cucmconfigrepo"){
			
			// Get Subversion URL and Concatinate the ID. 
			vm.getcompanycontent = CompanyService.getcompanycontent()
				.then(function(res){
					
					// "cucmconfigrepo": "//svnserver.domain.com/websvn/listing.php?repname=Cisco+CallManager+Repository",
					
					var url = res.data.cucmconfigrepo;
					
					// Build Trusted URL
					vm.iframe = $sce.trustAsResourceUrl(url); 
					
					
				}, function(err){
					vm.loading = false;
				});
		}
		
		if($state.current.name == "cucmsiteconfig"){
			
			// Get Subversion URL and Concatinate the ID. 
			vm.getcompanycontent = CompanyService.getcompanycontent()
				.then(function(res){
					
					// "cucmsiteconfig": "//svnserver.domain.com/websvn/log.php?repname=Cisco+CallManager+Repository&path=%2Fsites%2F",
					
					var url = res.data.cucmsiteconfig;

					// Build Trusted URL
					vm.iframe = $sce.trustAsResourceUrl(url + id); 
					
				}, function(err){
					vm.loading = false;
				});
		}
		
		if($state.current.name == "cucmphoneconfig"){
			
			// Get Subversion URL and Concatinate the ID. 
			vm.getcompanycontent = CompanyService.getcompanycontent()
				.then(function(res){
					
					// "cucmphoneconfig": "//svnserver.domain.com/websvn/log.php?repname=Cisco+CallManager+Repository&path=%2Fphones%2F"
	
					var url = res.data.cucmphoneconfig;
					
					// Build Trusted URL
					vm.iframe = $sce.trustAsResourceUrl(url + id); 
					
				}, function(err){
					vm.loading = false;
				});
		}
		
	}])
	