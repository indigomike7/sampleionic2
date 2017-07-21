define([
    'text!tpl/ultimatevideo/ultimatevideo-playlist-video.html',
    'ultimatevideo/controller/ultimatevideo-playlist-video',
], function(text, Controller) {

    return function() {
        return {
            restrict: 'E',
            template: text,
            controller: Controller
        };
    };
});