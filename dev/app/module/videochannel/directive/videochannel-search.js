define([
    'text!tpl/videochannel/videochannel-search.html'
], function(text) {

    return function() {
        return {
            restrict: 'E',
            template: text,
            controller: 'VideoChannelSearchCtrl'
        };
    };
});