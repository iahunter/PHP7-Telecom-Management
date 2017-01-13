.directive('trRow', function ($compile) {

    return {
        template: '<tr><td ng-bind="row.id"></td><td><strong ng-bind="row.name"></strong></td><td ng-bind="row.description"></td></tr>'
    };
});