define([
    'text!tpl/video/video-search.html'
], function(text) {

    return function() {
        return {
            restrict: 'E',
            template: text,
            controller: 'VideoSearchCtrl'
        };
    };
});