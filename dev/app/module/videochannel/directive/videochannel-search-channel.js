define([
    'text!tpl/videochannel/videochannel-search-channel.html'
], function(text) {

    return function() {
        return {
            restrict: 'E',
            template: text,
            controller: 'VideoChannelSearchChannelCtrl'
        };
    };
});