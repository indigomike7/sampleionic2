define([
    'text!tpl/ultimatevideo/ultimatevideo-search-video.html'
], function(text) {

    return function() {
        return {
            restrict: 'E',
            template: text,
            controller: 'UltimateVideoSearchVideoCtrl'
        };
    };
});