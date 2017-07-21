define([
    'text!tpl/core/core-private-msg.html'
], function() {

    return function() {

        return {
            restrict: 'E, A',
            template: require('text!tpl/core/core-private-msg.html')
        };
    };
});