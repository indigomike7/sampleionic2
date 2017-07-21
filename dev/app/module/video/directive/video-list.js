define([
    'text!tpl/video/video-list.html',
    'video/controller/video-list',
], function(text, Controller) {

    return function() {
        return {
            restrict: 'E',
            template: text,
            controller: Controller
        };
    };
});