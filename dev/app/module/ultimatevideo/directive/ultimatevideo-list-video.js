define([
    'text!tpl/ultimatevideo/ultimatevideo-list-video.html',
    'ultimatevideo/controller/ultimatevideo-list-video',
], function(text, Controller) {

    return function() {
        return {
            restrict: 'E',
            template: text,
            controller: Controller
        };
    };
});