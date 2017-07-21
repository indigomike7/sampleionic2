define([
    'text!tpl/ultimatevideo/ultimatevideo-list-history.html',
    'ultimatevideo/controller/ultimatevideo-list-history',
], function(text, Controller) {

    return function() {
        return {
            restrict: 'E',
            template: text,
            controller: Controller
        };
    };
});