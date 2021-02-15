angular
	.module('app')
	.controller('siteTrunking911Report.IndexController', ['cucmReportService', 'PageService','$location', '$state', '$scope', '$stateParams', function(cucmReportService, PageService, $location, $state, $scope,$stateParams) {
	
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

		//vm.getpage = PageService.getpage('siteTrunking911Report')
		
		// Match the window permission set in login.js and app.js - may want to user a service or just do an api call to get these. will decide later. 
		vm.permissions = window.telecom_mgmt_permissions;

		if(!vm.permissions.read.Cucmsiteconfigs){
			$location.path('/accessdenied');
		}

		vm.cucmsitetrunkreport = cucmReportService.listsitetrunkingreport()
			.then(function(res){
				// Check for errors and if token has expired. 
				if(res.data.message){
					//console.log(res);
					vm.message = res.data.message;
					//console.log(vm.message);
				}else{
					vm.sites = res.data.response;
					vm.phonecount = 0
					// Use this to change chart colors. 
					//Chart.defaults.global.colors = ['#FD1F5E','#1EF9A1','#7FFD1F','#68F000'];
					vm.e911stats = res.data.e911stats
					
					//console.log(vm.sites)
					
					angular.forEach(vm.sites, function(site) {
						//console.log(site.phonecount)
						vm.phonecount =  vm.phonecount + site.phonecount; 
					})
					
					//console.log(block.stats);
					vm.e911 = [];
					vm.e911['chartlabels'] = [];
					vm.e911['chartdata'] = [];
					//vm.didblock['chartseries'] = [];
					
					for(var key in vm.e911stats){
						//console.log(key);
						//console.log(vm.didblock.stats[key]);
						
						vm.e911['chartlabels'].push(key);
						vm.e911['chartdata'].push(vm.e911stats[key]);
						//vm.didblock['chartseries'].push(key);
						
					}
					
					
					// Enable the Options to be generated for the chart. 
					//vm.didblock.chartoptions = { responsive: true, legend: { display: true}, title: {display:true, text:'Number Block Usage'}};
					vm.e911.chartoptions = { responsive: true, scales: {
																				xAxes: [{
																					ticks: {
																						beginAtZero:true
																					}
																				}]
																			}};
					
					//Chart.defaults.global.colors = ['#000000','#1EF9A1','#7FFD1F','#68F000'];
					vm.trunkingstats = res.data.trunkingstats
					
					//console.log(block.stats);
					vm.trunking = [];
					vm.trunking['chartlabels'] = [];
					vm.trunking['chartdata'] = [];
					//vm.didblock['chartseries'] = [];
					
					for(var key in vm.trunkingstats){
						//console.log(key);
						//console.log(vm.didblock.stats[key]);
						
						vm.trunking['chartlabels'].push(key);
						vm.trunking['chartdata'].push(vm.trunkingstats[key]);
						//vm.didblock['chartseries'].push(key);
						
					}
					
					
					// Enable the Options to be generated for the chart. 
					//vm.didblock.chartoptions = { responsive: true, legend: { display: true}, title: {display:true, text:'Number Block Usage'}};
					vm.trunking.chartoptions = { responsive: true, scales: {
																				xAxes: [{
																					ticks: {
																						beginAtZero:true
																					}
																				}]
																			}};
					
					console.log(vm.trunking)
					vm.loading = false;
				}
				
				console.log(vm.phonecount)
				
			}, function(err){
				alert(err);
			});
				

		

	}])

