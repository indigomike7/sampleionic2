define([
    'text!tpl/core/core-stars.html'
], function() {
    return function() {
        return {
            restrict: 'E',
            template: require('text!tpl/core/core-stars.html'),
            scope: {
                active: '@',
                total: '@'
            },
            link: function(scope, el, attrs) {
                scope.total = scope.total || 5;
            }
        };
    };
});
