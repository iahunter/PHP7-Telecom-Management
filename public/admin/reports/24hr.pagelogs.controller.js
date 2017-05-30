angular
	.module('app')
	.controller('LogReport.24hr.Pagelogs.Controller', ['LogService','$location', '$interval', '$state', '$scope', '$stateParams', function(LogService, $location, $interval, $state, $scope,$stateParams) {
	
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

		pagelogs();

		function pagelogs(){
			LogService.getlast24hrpagelogs()
				.then(function(res){
					// Check for errors and if token has expired. 
					if(res.data.message){
						//console.log(res);
						vm.message = res.data.message;
						//console.log(vm.message);
					}else{
						//console.log(res)
						vm.logs = res.data.result;
						//console.log(vm.logs)
						
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
						/*
						// Use this to change chart colors. 
						//Chart.defaults.global.colors = ['#FD1F5E','#1EF9A1','#7FFD1F','#68F000'];
						vm.e911stats = res.data.e911stats
						
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
					*/	
						//console.log(vm.trunking)
						
						
					}
					
				}, function(err){
					alert(err);
				});
		}

		var pulllogactivity = $interval(pagelogs,5000); 
		
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

