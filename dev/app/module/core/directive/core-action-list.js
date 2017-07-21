define([
    'text!tpl/core/core-action-list.html'
], function(text) {

    return function() {
        return {
            restrict: 'E',
            template: text,
            controller: 'CoreActionListCtrl',
        };
    };
});