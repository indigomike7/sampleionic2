define([
    'angular',
    'myapp',
    'videochannel/controller/videochannel-add',
    'videochannel/controller/videochannel-add-channel',
    'videochannel/controller/videochannel-add-channel-url',
    'videochannel/controller/videochannel-add-channel-keywords',
    'videochannel/controller/videochannel-add-channel-list',
    'videochannel/controller/videochannel-add-channel-detail',
    'videochannel/controller/videochannel-add-channel-video',
    'videochannel/controller/videochannel-browse-video',
    'videochannel/controller/videochannel-browse-channel',
    'videochannel/controller/videochannel-my-channel',
    'videochannel/controller/videochannel-detail',
    'videochannel/controller/videochannel-detail-channel',
    'videochannel/controller/videochannel-detail-channel-info',
    'videochannel/controller/videochannel-edit',
    'videochannel/controller/videochannel-edit-channel',
    'videochannel/controller/videochannel-home',
    'videochannel/controller/videochannel-item',
    'videochannel/controller/videochannel-item-channel',
    'videochannel/controller/videochannel-list',
    'videochannel/controller/videochannel-list-channel',
    'videochannel/controller/videochannel-my-video',
    'videochannel/controller/videochannel-search',
    'videochannel/controller/videochannel-search-channel',
    'videochannel/controller/videochannel-favorite-video',
    'videochannel/controller/videochannel-friend-video',
    'videochannel/controller/videochannel-add-more-video',
], function() {
    angular.module('myapp.controllers')
        .controller('VideoChannelAddCtrl', require('videochannel/controller/videochannel-add'))
        .controller('VideoChannelAddChannelCtrl', require('videochannel/controller/videochannel-add-channel'))
        .controller('VideoChannelAddChannelUrlCtrl', require('videochannel/controller/videochannel-add-channel-url'))
        .controller('VideoChannelAddChannelKeywordsCtrl', require('videochannel/controller/videochannel-add-channel-keywords'))
        .controller('VideoChannelAddChannelListCtrl', require('videochannel/controller/videochannel-add-channel-list'))
        .controller('VideoChannelAddChannelDetailCtrl', require('videochannel/controller/videochannel-add-channel-detail'))
        .controller('VideoChannelAddChannelVideoCtrl', require('videochannel/controller/videochannel-add-channel-video'))
        .controller('VideoChannelHomeCtrl', require('videochannel/controller/videochannel-home'))
        .controller('VideoChannelBrowseVideoCtrl', require('videochannel/controller/videochannel-browse-video'))
        .controller('VideoChannelBrowseChannelCtrl', require('videochannel/controller/videochannel-browse-channel'))
        .controller('VideoChannelMyChannelCtrl', require('videochannel/controller/videochannel-my-channel'))
        .controller('VideoChannelMyVideoCtrl', require('videochannel/controller/videochannel-my-video'))
        .controller('VideoChannelEditCtrl', require('videochannel/controller/videochannel-edit'))
        .controller('VideoChannelEditChannelCtrl', require('videochannel/controller/videochannel-edit-channel'))
        .controller('VideoChannelItemCtrl',require('videochannel/controller/videochannel-item'))
        .controller('VideoChannelItemChannelCtrl',require('videochannel/controller/videochannel-item-channel'))
        .controller('VideoChannelDetailCtrl', require('videochannel/controller/videochannel-detail'))
        .controller('VideoChannelDetailChannelCtrl', require('videochannel/controller/videochannel-detail-channel'))
        .controller('VideoChannelDetailChannelInfoCtrl', require('videochannel/controller/videochannel-detail-channel-info'))
        .controller('VideoChannelSearchCtrl', require('videochannel/controller/videochannel-search'))
        .controller('VideoChannelSearchChannelCtrl', require('videochannel/controller/videochannel-search-channel'))
        .controller('VideoChannelListChannelCtrl', require('videochannel/controller/videochannel-list-channel'))
        .controller('VideoChannelFavoriteVideoCtrl', require('videochannel/controller/videochannel-favorite-video'))
        .controller('VideoChannelFriendVideoCtrl', require('videochannel/controller/videochannel-friend-video'))
        .controller('VideoChannelAddMoreVideoCtrl', require('videochannel/controller/videochannel-add-more-video'))
    ;
});