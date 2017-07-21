define(function() {
    return function($compile) {
        return {
            restrict: 'A',
            link: function(scope, element, attrs) {
                scope.$watch(attrs.ngHtmlCompile, function(newValue, oldValue) {
                    element.addClass('ng-html-compile');
                    element.html(newValue);
                    $compile(element.contents())(scope);
                });
            }
        };
    };
});