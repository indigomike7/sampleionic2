define([
    'text!tpl/videochannel/videochannel-detail-channel-info.html'
], function(text) {

    return function() {
        return {
            restrict: 'E',
            template: text,
            controller: 'VideoChannelDetailChannelInfoCtrl'
        };
    };
});