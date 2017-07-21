define([
    'text!tpl/core/core-header-stat.html'
], function(text) {

    return function() {

        return {
            restrict: 'E',
            template: text,
            controller: 'CoreHeaderStatCtrl',
            scope: {
                obj: '='
            }
        };
    };
});