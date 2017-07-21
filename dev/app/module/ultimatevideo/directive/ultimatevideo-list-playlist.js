define([
    'text!tpl/ultimatevideo/ultimatevideo-list-playlist.html',
    'ultimatevideo/controller/ultimatevideo-list-playlist',
], function(text, Controller) {

    return function() {
        return {
            restrict: 'E',
            template: text,
            controller: Controller
        };
    };
});