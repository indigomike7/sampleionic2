define([
    'text!tpl/videochannel/videochannel-list-channel.html',
    'videochannel/controller/videochannel-list-channel',
], function(text, Controller) {

    return function() {
        return {
            restrict: 'E',
            template: text,
            controller: Controller
        };
    };
});