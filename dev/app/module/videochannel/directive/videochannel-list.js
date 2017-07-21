define([
    'text!tpl/videochannel/videochannel-list.html',
    'videochannel/controller/videochannel-list',
], function(text, Controller) {

    return function() {
        return {
            restrict: 'E',
            template: text,
            controller: Controller
        };
    };
});