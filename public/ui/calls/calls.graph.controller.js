angular
	.module('app')
	.controller('CallGraph.IndexController', ['CallService', '$location', '$state', '$stateParams', function(CallService, $location, $state, $stateParams) {
		
		var vm = this;
		
		vm.refresh = function (){
			$state.reload();
		};

		vm.error = false;
		

		vm.createcallgraph = CallService.listcallstats()
			.then(function(res){
				
				// Check for errors and if token has expired. 
				if(res.data.message){
					console.log(res);
					vm.message = res.data.message;
					console.log(vm.message);
					
					if(vm.message == "Token has expired"){
						// Send user to login page if token expired. 
						alert(vm.message);
						$state.go('logout');
					}
					vm.error = true;
					return vm.message;
				}
				
				//** Loop thru and create chart data for block. 
				vm.calls = res.data.result;
				
				//console.log(vm.calls);
				
				vm.callgraph = {};
				
				//console.log(block.stats);
				vm.callgraph['chartlabels'] = [];
				vm.callgraph['chartdata'] = [];
				vm.callgraph['chartseries'] = [];
				
				vm.sbcs = [];
				vm.sbcs['totalCalls'] = [];
				
				angular.forEach(vm.calls, function(key, value) {

					vm.sbcs['totalCalls'].push(key.totalCalls);
					
					//console.log(key.stats);
					angular.forEach(key.stats, function(k, v) {
						
						// Create the SBC arrays for individual call counts. 
						if (vm.sbcs[v]){
							vm.sbcs[v].push(k.totalCalls);
						}else{
							vm.sbcs[v] = [];
							vm.sbcs[v].push(k.totalCalls);
						}
					
					});
					
					// Push date and time onto callgraph array for x axis labels. 
					vm.callgraph['chartlabels'].push(key.created_at);
				
				});
				
				// Push data onto the chartgraph array
				for (key in vm.sbcs){
					//console.log(key);
					var value = vm.sbcs[key];
					//console.log(value);
					vm.callgraph['chartseries'].push(key);
					vm.callgraph['chartdata'].push(value);
				}
				
				// Enable the Options to be generated for the chart. 
				vm.callgraph.chartoptions = { responsive: true, legend: { display: true}, title: {display:false, text:'SBC Call Summary'}};
				
				//console.log(vm.callgraph);

				return vm.calls;
				
			}, function(err){
				//Error
			});
	}]);