angular
	.module('app')
	
	.directive('tooltip', function(){
		// Directive for tooltips
		return {
			restrict: 'A',
			link: function(scope, element, attrs){
				$(element).hover(function(){
					// on mouseenter
					$(element).tooltip('show');
				}, function(){
					// on mouseleave
					$(element).tooltip('hide');
				});
			}
		};
	})
	
	.directive('bsPopover', function() {
		return function(scope, element, attrs) {
			element.find("a[rel=popover]").popover({placement: 'bottom', html: 'true'});
		};
	})
	
	.directive('lineDirective', function() {
		return {
			restrict: 'AECM',
			templateUrl: 'app-directives/line_form.html',
			replace: true
		};
	})
	
	.directive('speeddialDirective', function() {
		return {
			restrict: 'AECM',
			templateUrl: 'app-directives/speeddial_form.html',
			replace: true
		};
	})
	
	.directive('blfDirective', function() {
		return {
			restrict: 'AECM',
			templateUrl: 'app-directives/blf_form.html',
			replace: true
		};
	})
	
	.directive('addonmoduleDirective', function() {
		return {
			restrict: 'AECM',
			templateUrl: 'app-directives/addonmodule_form.html',
			replace: true
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
			templateUrl: 'app-directives/sitepopover.html',
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
		
		
	})
	
	