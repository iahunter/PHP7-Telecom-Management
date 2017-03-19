angular
	.module('app')
	.controller('siteTrunking911Report.IndexController', ['siteService', 'cucmService','$location', '$state', '$stateParams', function(siteService, cucmService, $location, $state, $stateParams) {
	
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
		vm.sites = [{}];
		vm.loading = true;
		
		
		
		function isInArrayNgForeach(field, arr) {
			var result = false;
			//console.log("HERRE")
			//console.log(field);
			//console.log(arr);
			
			angular.forEach(arr, function(value, key) {
				//console.log(value);
				if(field == value)
					result = true;
			});

			return result;
		}



		vm.listcucmsites = cucmService.listcucmsites()
			.then(function(res){
				listdevicepools = res.data.response;
				
				//console.log(listdevicepools);
				//listdevicepools = ['TRAVIS01', 'KHONEKSS']
				filter = ["CENTRAL_SBC_SIPTRUNKS", "TEMPLATE"]
				devicepools = []
				angular.forEach(listdevicepools, function(site) {
					if(isInArrayNgForeach(site, filter)){
						console.log("inarray " + site)
					}else{
						console.log("Not Inarray")
						devicepools.push(site)
					}
				});

				devicepools = devicepools.sort();
				vm.sites = {}
				angular.forEach(devicepools, function(devicepool) {
					if(devicepool){
						vm.sites[devicepool] = {}
					}console.log(devicepool)
					
				});
				
				
				angular.forEach(devicepools, function(devicepool) {
					console.log(devicepool)
					cucmService.getsitesummary(devicepool)
							.then(function(res){
								
								var cucmsitesummary = res.data.response;
								
								//console.log(cucmsitesummary);
								
								if (res.data.response == 0){
									vm.deploybutton = true;
									
									return cucmsitesummary = false;
								}else{
									cucmsitesummary = res.data.response;
								}
								//vm.sites = {}
								//vm.sites[devicepool] = {};
								vm.sites[devicepool].name = devicepool;
								vm.sites[devicepool].summary = {};
								vm.sites[devicepool].details = {};
								
								vm.sipsites = {}
								vm.e911sites = {}
								// Loop thru and append to a simple array so we can do a simple select on it with ng-options.
								
								angular.forEach(cucmsitesummary, function(k,v) {
									
										//console.log("VALUE: " + v);
										//cucmsite.summary
										
										angular.forEach(k, function(key,object) {
											if(key.length != 0){
												//vm.cucmsite.summary['length']++;
												if (!vm.sites[devicepool].summary[v]){
													vm.sites[devicepool].summary[v] = [];
													if(key){
														vm.sites[devicepool].summary[v].push(key);
													}
													
													
												}else{
													if(key){
														vm.sites[devicepool].summary[v].push(key);
													}
													
												}
												
												// Get object details for popover
												if(v == "DevicePool" || v == "Css"){
													cucmService.get_object_type_by_name(key, v)
															.then(function(res) {
																
																
																vm.sites[devicepool].details[key] = [];
																if(v == "DevicePool"){
																	devicepooldetails = res.data.response
																	//console.log(devicepooldetails)
																	localRouteGroup = devicepooldetails.localRouteGroup
																	//console.log(localRouteGroup.value)
																	if (localRouteGroup.value == 'RG_CENTRAL_SBC_GRP'){
																		vm.sites[devicepool].summary.sipsite = true;
																	}else{
																		vm.sites[devicepool].summary.sipsite = 'local';
																	}
																}
																
																if(v == "Css"){
																	console.log('Getting: ' + key)
																	
																	// Only check the CSSs we know about following naming standard of CSS_SITE or CSS_SITE_DEVICE
																	if(key == "CSS_"+devicepool || key == "CSS_"+devicepool+"_DEVICE"){
																		vm.sites[devicepool].summary.e911site = 'local';
																		cssdetails = res.data.response
																		//console.log(cssdetails)
																		cssmembers = cssdetails.members.member
																		
																		angular.forEach(cssmembers, function(member) {
																			//console.log(member.routePartitionName._)
																			if(member.routePartitionName._ == 'PT_911Enable'){
																				vm.sites[devicepool].summary.e911site = true;
																			}
																		});
																	}

																}
																	res.data.response
																//cucmsite.details[key] = res.data.response;
																
																// Json stringify to make object readable in popover
																var response = JSON.stringify(res.data.response, undefined, 2);
																//console.log(response)
																vm.sites[devicepool].details[key] = response;
																
																

															}, function(error) {
																alert('An error occurred while getting object')
															});
												}
											}
										});
									
									//console.log(vm.cucmsite.details);
									
								});
								
								if(vm.sites[devicepool].summary == 0){
									console.log("Does not exist in CUCM");
									vm.sites[devicepool].summary = false;
								}
								
								//console.log(vm.cucmsite.details)
								//console.log(cucmsite.summary)
								
							}, function(err){
								//Error
							});
				});
				vm.loading = false
				console.log(vm.sites)
			}, function(err){
				alert(err);
			});
				


		var id = $stateParams.id;
		
		vm.getsite = siteService.getsite(id)
			.then(function(res){

				vm.site = res.data.result;
			
			}, function(err){
							//Error
			});
		

	}])
	
	// Be nice to use a directive at some point to help template HTML
	.directive('trRow', function ($compile) {

		return {
			template: '<tr><td ng-bind="row.id"></td><td><strong ng-bind="row.name"></strong></td><td ng-bind="row.description"></td></tr>'
		};
	});

