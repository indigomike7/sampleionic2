define([
    'text!tpl/ultimatevideo/ultimatevideo-search-history.html'
], function(text) {

    return function() {
        return {
            restrict: 'E',
            template: text,
            controller: 'UltimateVideoSearchHistoryCtrl'
        };
    };
});