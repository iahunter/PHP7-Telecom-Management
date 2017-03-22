angular
	.module('app')
	.controller('getSite.IndexController', ['siteService', 'sitePhonePlanService', 'cucmService', 'cupiService', '$location', '$state', '$stateParams', '$scope', '$timeout', '$compile', '$templateCache', function(siteService, sitePhonePlanService, cucmService, cupiService, $location, $state, $stateParams, $scope, $timeout, $compile, $templateCache) {
		
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
		
		
		vm.isArray = angular.isArray;
		
		vm.loading = true;

		vm.messages = 'Loading sites...';
		
		var id = $stateParams.id;
		
		vm.deploybutton = false;
		
		vm.getsitephoneplans = sitePhonePlanService.getsitephoneplans(id)
			.then(function(res){
				// Check if Token has expired. If so then direct them to login screen. 
				if(res.message == "Token has expired"){
					vm.tokenexpired = true;
					//alert("Token has expired, Please relogin");
					//alert(res.message);
					$state.go('logout');
				}
				//console.log(res);
				vm.phoneplans = res.data.result;
				

				vm.loading = false;
				return vm.phoneplans
				
				
			}, function(err){
				vm.loading = false;
			});
		

		// Drop down values to use in Add form.
		vm.states = [{
				id: 1,
				name: 'available'
			}, {
				id: 2,
				name: 'reserved'
			}];
		
		vm.types = [{
				id: 1,
				name: 'public'
			}, {
				id: 2,
				name: 'private'
			}];
		
		/*
		vm.getcupitimezones = cupiService.listtimezones()
			.then(function(res) {
				
				vm.cupitimezones = res.data;
				console.log(vm.cupitimezones)
			}, function(error) {
				alert('An error occurred while updating the event')
			});
		*/
		
		vm.getvmusertemplates = cupiService.listusertemplatesnames()
			.then(function(res) {
				
				vm.usertemplates = res.data;

			}, function(error) {
				alert('An error occurred while updating the event')
			});
		
		
		// Edit state for phoneplan block Edit button.
		vm.edit = {};
		
		
		// Create Phone 
		vm.createphoneplan = function(phoneplan) {
			phoneplan.site = vm.site.id;
			
			//console.log(phoneplan);
			
			sitePhonePlanService.createphoneplan(phoneplan).then(function(data) {
			  //alert('phoneplan was added successfully');
			  return $state.reload();
			}, function(error) {
				alert('An error occurred while creating phone plan')
			});
			//$state.reload();
		}
		
		// Update phoneplan Block service called by the save button.
		vm.updatephoneplan = function(phoneplan) {
			// Put the variable that we need into an array to send. We only want to send name, carrier and comment for updates. 
			var phoneplan_update = {};
			phoneplan_update.name = phoneplan.name;
			phoneplan_update.description = phoneplan.description;
			phoneplan_update.language = phoneplan.language;
			
			// Send Block ID and the updated variables to the update service. 
			sitePhonePlanService.updatephoneplan(phoneplan.id, phoneplan_update).then(function(data) {
			  //return $state.reload();
			}, function(error) {
				alert('An error occurred while updating the event')
			});
			//$state.reload();
		}
		
		// Delete 
		vm.deletephoneplan = function(phoneplan) {
			sitePhonePlanService.deletephoneplan(phoneplan.id).then(function(data) {

			
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
		
		vm.showaddrow = false;
		
		vm.phoneplanaddtoggle = function(){
			if(vm.showaddrow == true){
				vm.showaddrow = false;
			}else{
				if(vm.showaddrow == false){
				vm.showaddrow = true;
				}
			}
		}
		
		vm.usertemplatedeploybutton = false;
		
		
		
		// Create User Templates - ######## THIS NEEDS WORK!!! ########
		vm.create_cupi_usertemplates = function() {
			var language = [];
			if(!vm.site.languages){
				language.push("english");
				//console.log(language);
			}else{
				language = vm.site.languages;
				//console.log(language);
			}
			//console.log(language);
			
			angular.forEach(language, function(values) {
				//console.log(values);
				template = {};
				template.sitecode = vm.site.sitecode;
				template.language = values;
				template.timezone = vm.site.timezone;
				template.operator = vm.site.npa + vm.site.nxx + vm.site.operator;
				
				//console.log(template)
				
				cupiService.createusertemplatesforsite(template).then(function(data) {
					
					
					// Do something here to pring out results for user. 
					
					
				}, function(error) {
					alert('An error occurred creating the user templates')
				});

				return $state.reload();
			});

			
			
			
			/*
			angular.forEach(vm.site.languages, function(k,v) {
									
										//console.log("VALUE: " + v);
										//vm.cucmsitesummary
										angular.forEach(k, function(key,object) {
											if(key.length != 0){
												//vm.cucmsitesummary['length']++;
												if (!vm.cucmsitesummary[v]){
													vm.cucmsitesummary[v] = [];
													if(key){
														vm.cucmsitesummary[v].push(key);
													}
													
												}else{
													if(key){
														vm.cucmsitesummary[v].push(key);
													}
												}
												
											}
										});
									
									//console.log(vm.cucmsitesummary);
									
								});
			
			console.log(templates);
			
			cupiService.createusertemplate(template).then(function(data) {
			  //alert('phoneplan was added successfully');
			  return $state.reload();
			}, function(error) {
				alert('An error occurred while creating phone plan')
			});
			//$state.reload();
			*/
		}

		
		vm.getsitesummary = function (id) {
			
			vm.site = "";
			vm.sitecode = "";
			
			vm.getsite = siteService.getsite(id)
				.then(function(res){

					vm.site = res.data.result;
					vm.sitecode = res.data.result.sitecode;
					//console.log(vm.sitecode);
					
						// Check CUCM for Site Config After we have the sitecode from the database. 
						cucmService.getsitesummary(vm.sitecode)
							.then(function(res){
								
								var cucmsitesummary = res.data.response;
								
								//console.log(cucmsitesummary);
								
								if (res.data.response == 0){
									vm.deploybutton = true;
									
									return vm.cucmsitesummary = false;
								}else{
									cucmsitesummary = res.data.response;
								}
								console.log(cucmsitesummary);
								
								vm.cucmsite = {};
								vm.cucmsite.summary = {};
								vm.cucmsite.details = {};
								// Loop thru and append to a simple array so we can do a simple select on it with ng-options.
								
								angular.forEach(cucmsitesummary, function(k,v) {
									
										//console.log("VALUE: " + v);
										//console.log(k);
										//vm.cucmsite.summary
										angular.forEach(k, function(key,object) {
											if(key.length != 0){
												//vm.cucmsite.summary['length']++;
												if (!vm.cucmsite.summary[v]){
													vm.cucmsite.summary[v] = [];
													if(key){
														vm.cucmsite.summary[v].push(key);
													}
													
													
												}else{
													if(key){
														vm.cucmsite.summary[v].push(key);
													}
													
												}
												console.log(object)
												
												// Get object details for popover
												cucmService.get_object_type_by_uuid(object, v)
														.then(function(res) {
															
															
															vm.cucmsite.details[key] = [];
															//vm.cucmsite.details[key] = res.data.response;
															
															// Json stringify to make object readable in popover
															var response = JSON.stringify(res.data.response, undefined, 2);
															//console.log(response)
															vm.cucmsite.details[key] = response;
															
															

														}, function(error) {
															alert('An error occurred while getting object')
														});
												
											}
										});
									
									//console.log(vm.cucmsite.details);
									
								});
								
								if(vm.cucmsite.summary == 0){
									console.log("Does not exist in CUCM");
									vm.cucmsite.summary = false;
								}
								
								//console.log(vm.cucmsite.details)
								console.log(vm.cucmsite.summary)
							}, function(err){
								//Error
							});
							
							
						cupiService.listusertemplatesbysite(vm.sitecode)
							.then(function(res) {
								
								vm.siteusertemplates = res.data;
								//console.log(vm.siteusertemplates);
								
								if(vm.siteusertemplates.length > 0){
									vm.usertemplatedeploybutton = false;
								}
								if(vm.siteusertemplates.length == 0){
									vm.usertemplatedeploybutton = true;
								}
								

							}, function(error) {
								alert('An error occurred while getting user templates from unity connection')
							});
					
					
				}, function(err){
					//Error
				});
				
				
				

		};
		
		vm.cucm_object_details = {};
		vm.get_cucm_object_type_by_name = function (name, type) {

				//console.log("Mouse Over");
				
				cucmService.get_object_type_by_name(name, type)
					.then(function(res) {
						var object = res.data.response;
						//console.log(object)
						
						return $scope.object = object

					}, function(error) {
						alert('An error occurred while getting object')
					});
					
				$timeout(function() {
					//console.log($scope.object)
					//return $scope.object
				}, 1000);
				
				return $scope.object
				//console.log($scope.object)
			
		}
		
		var getsitesummary = vm.getsitesummary(id)
		

		vm.languages = [{
				id: 1,
				name: 'english'
			}, {
				id: 2,
				name: 'french'
			}];
		
		vm.deploycucmsite = function () {
			// Update $scope values to form data. 

			//console.log(vm.site);
			vm.deploycucmsiteloading = true;
			
			var site = {};
			site.sitecode = vm.site.sitecode;
			
			// Change Site type based on site design user chooses. This is needed for the Laravel Controller
			if(vm.site.trunking == 'sip' && vm.site.e911 == '911enable' ){
				site.type = 1;
			}
			else if(vm.site.trunking == 'local' && vm.site.e911 == '911enable' ){
				site.type = 2;
			}
			else if(vm.site.trunking == 'sip' && vm.site.e911 == 'local' ){
				site.type = 3;
			}
			else if(vm.site.trunking == 'local' && vm.site.e911 == 'local' ){
				site.type = 4;
			}
			
			site.srstip = vm.site.srstip;
			site.h323ip = vm.site.h323ip;
			site.timezone = vm.site.timezone;
			site.npa = vm.site.npa;
			site.nxx = vm.site.nxx;
			site.didrange = vm.site.didrange;
			site.operator = vm.site.operator;
			
			//console.log(site);
		
			
			// Call the validate address service. 
			cucmService.createcucmsite(site).then(function(data) {
				
				vm.deploysiteresult = data.data.response;
				
				vm.deploycucmsiteloading = false;
				
				//alert("Site Deployed to CUCM");
				//$state.reload();
				return vm.deploysiteresult;
				
				/*
				// Set valid and invalid address variable so we can alert success or failure. 
				if($scope.validateAddress.success==true){
					$scope.validaddress=true;
				}
				else{
					$scope.invalidaddress=true;
				}
				*/
			}, function(error) {
				alert('An error occurred while creating the site \n' + error.data.message)
			});
			
		};

	}])

	.directive('bsPopover', function() {
		return function(scope, element, attrs) {
			element.find("a[rel=popover]").popover({placement: 'bottom', html: 'true'});
		};
	})
	
	.directive('popOver', function ($compile, $templateCache) {
		var getTemplate = function () {
		
			//console.log($templateCache.get("siteplanning/sitepopover.html"));
			return $templateCache.get("siteplanning/sitepopover.html");
		}
		return {
			restrict: "A",
			//transclude: true,
			//template: "<span ng-transclude></span>",
			templateUrl: 'siteplanning/sitepopover.html',
			//replace : true,
			link: function (scope, element, attrs) {
				//console.log(scope)
				var popOverContent;
				var html = getTemplate();
				popOverContent = html;    
				popOverContent = $compile(html)(scope); 
				//console.log(scope.objvalue);
				var options = {
					content: popOverContent,
					//placement: "bottom",
					html: true,
					title: scope.objkey, 
				};
				//$(element).popover(options);
				element.find("a[rel=popover]").popover(options);
			},
		};
	});
	