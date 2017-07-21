define([
    'angular',
    'myapp',
    'videochannel/directive/videochannel-list',
    'videochannel/directive/videochannel-list-channel',
    'videochannel/directive/videochannel-search',
    'videochannel/directive/videochannel-search-channel',
    'videochannel/directive/videochannel-detail-channel-info',
],function(){
    angular.module('myapp.directives')
    .directive('videochannelListDir', require('videochannel/directive/videochannel-list'))
    .directive('videochannelListChannelDir', require('videochannel/directive/videochannel-list-channel'))
    .directive('videochannelSearchDir', require('videochannel/directive/videochannel-search'))
    .directive('videochannelSearchChannelDir', require('videochannel/directive/videochannel-search-channel'))
    .directive('videochannelDetailChannelInfoDir', require('videochannel/directive/videochannel-detail-channel-info'))
    ;
});
