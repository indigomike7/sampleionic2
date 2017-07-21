define([
    'angular',
    'myapp',
    'videochannel/controllers',
    'videochannel/directives',
    'videochannel/services',
    'videochannel/plugin/activity',
    'videochannel/plugin/message',
    'text!tpl/videochannel/videochannel-add.html',
    'text!tpl/videochannel/videochannel-add-channel.html',
    'text!tpl/videochannel/videochannel-add-channel-url.html',
    'text!tpl/videochannel/videochannel-add-channel-keywords.html',
    'text!tpl/videochannel/videochannel-add-channel-list.html',
    'text!tpl/videochannel/videochannel-add-channel-detail.html',
    'text!tpl/videochannel/videochannel-add-channel-video.html',
    'text!tpl/videochannel/videochannel-attachment.html',
    'text!tpl/videochannel/videochannel-browse-video.html',
    'text!tpl/videochannel/videochannel-browse-channel.html',
    'text!tpl/videochannel/videochannel-detail.html',
    'text!tpl/videochannel/videochannel-detail-channel.html',
    'text!tpl/videochannel/videochannel-edit.html',
    'text!tpl/videochannel/videochannel-edit-channel.html',
    'text!tpl/videochannel/videochannel-home.html',
    'text!tpl/videochannel/videochannel-message-attachment.html',
    'text!tpl/videochannel/videochannel-my-video.html',
    'text!tpl/videochannel/videochannel-my-channel.html',
    'text!tpl/videochannel/videochannel-search.html',
    'text!tpl/videochannel/videochannel-browse-channel.html',
    'text!tpl/videochannel/videochannel-favorite-video.html',
    'text!tpl/videochannel/videochannel-friend-video.html',
    'text!tpl/videochannel/videochannel-add-more-video.html',
], function(angular, myapp) {
    angular.module('myapp')
        .config(function($stateProvider, $urlRouterProvider, gettext) {
            $stateProvider
                .state('app.addVideoChannel',{
                    module: 'videochannel',
                    url: '/videochannel/add',
                    cache: false,
                    history: false,
                    views:{
                        'menuContent': {
                            template: require('text!tpl/videochannel/videochannel-add.html'),
                            controller: 'VideoChannelAddCtrl',
                        }
                    }
                })
                .state('app.addChannel',{
                    module: 'videochannel',
                    url: '/videochannel/add/channel',
                    cache: false,
                    history: {
                        title: gettext('Channel Source'),
                        isRoot: false
                    },
                    views:{
                        'menuContent': {
                            template: require('text!tpl/videochannel/videochannel-add-channel.html'),
                            controller: 'VideoChannelAddChannelCtrl',
                        }
                    }
                })
                .state('app.addChannelUrl',{
                    module: 'videochannel',
                    url: '/videochannel/add/channel/url',
                    cache: false,
                    history: {
                        title: gettext('Channel URL'),
                        isRoot: false
                    },
                    views:{
                        'menuContent': {
                            template: require('text!tpl/videochannel/videochannel-add-channel-url.html'),
                            controller: 'VideoChannelAddChannelUrlCtrl',
                        }
                    }
                })
                .state('app.addChannelKeywords',{
                    module: 'videochannel',
                    url: '/videochannel/add/channel/keywords',
                    cache: false,
                    history: {
                        title: gettext('Keywords'),
                        isRoot: false
                    },
                    views:{
                        'menuContent': {
                            template: require('text!tpl/videochannel/videochannel-add-channel-keywords.html'),
                            controller: 'VideoChannelAddChannelKeywordsCtrl',
                        }
                    }
                })
                .state('app.addChannelList',{
                    module: 'videochannel',
                    url: '/videochannel/add/channel/list',
                    cache: false,
                    history: {
                        title: gettext('Select Channel'),
                        isRoot: false
                    },
                    views:{
                        'menuContent': {
                            template: require('text!tpl/videochannel/videochannel-add-channel-list.html'),
                            controller: 'VideoChannelAddChannelListCtrl',
                        }
                    }
                })
                .state('app.addChannelDetail',{
                    module: 'videochannel',
                    url: '/videochannel/add/channel/detail',
                    cache: false,
                    history: {
                        title: gettext('Channel Detail'),
                        isRoot: false
                    },
                    views:{
                        'menuContent': {
                            template: require('text!tpl/videochannel/videochannel-add-channel-detail.html'),
                            controller: 'VideoChannelAddChannelDetailCtrl',
                        }
                    }
                })
                .state('app.addChannelVideo',{
                    module: 'videochannel',
                    url: '/videochannel/add/channel/video',
                    cache: false,
                    history: false,
                    views:{
                        'menuContent': {
                            template: require('text!tpl/videochannel/videochannel-add-channel-video.html'),
                            controller: 'VideoChannelAddChannelVideoCtrl',
                        }
                    }
                })
                .state('app.addVideoChannelType',{
                    module: 'videochannel',
                    url: '/videochannel/add/:sParentType/:iParentId',
                    cache: false,
                    history: false,
                    views:{
                        'menuContent': {
                            template: require('text!tpl/videochannel/videochannel-add.html'),
                            controller: 'VideoChannelAddCtrl',
                        }
                    }
                })
                .state('app.VideoChannelMyVideo', {
                    module: 'videochannel',
                    url: '/videochannel/my',
                    cache: false,
                    history: {
                        title: gettext('My Videos'),
                        isRoot: true
                    },
                    views: {
                        'menuContent': {
                            template: require('text!tpl/videochannel/videochannel-home.html'),
                            controller: 'VideoChannelHomeCtrl',
                        },
                        'tabContent@app.VideoChannelMyVideo': {
                            template: require('text!tpl/videochannel/videochannel-my-video.html'),
                            controller: 'VideoChannelMyVideoCtrl',
                        }
                    }
                })
                .state('app.VideoChannelFavoriteVideo', {
                    module: 'videochannel',
                    url: '/videochannel/favorite',
                    cache: false,
                    history: {
                        title: gettext('My Favorites'),
                        isRoot: true
                    },
                    views: {
                        'menuContent': {
                            template: require('text!tpl/videochannel/videochannel-home.html'),
                            controller: 'VideoChannelHomeCtrl',
                        },
                        'tabContent@app.VideoChannelFavoriteVideo': {
                            template: require('text!tpl/videochannel/videochannel-favorite-video.html'),
                            controller: 'VideoChannelFavoriteVideoCtrl',
                        }
                    }
                })
                .state('app.VideoChannelFriendVideo', {
                    module: 'videochannel',
                    url: '/videochannel/friend',
                    cache: false,
                    history: {
                        title: gettext('Friend\'s Videos'),
                        isRoot: true
                    },
                    views: {
                        'menuContent': {
                            template: require('text!tpl/videochannel/videochannel-home.html'),
                            controller: 'VideoChannelHomeCtrl',
                        },
                        'tabContent@app.VideoChannelFriendVideo': {
                            template: require('text!tpl/videochannel/videochannel-friend-video.html'),
                            controller: 'VideoChannelFriendVideoCtrl',
                        }
                    }
                })

                .state('app.editVideoChannel',{
                    module: 'videochannel',
                    url: '/videochannel/edit/{iVideoId}',
                    cache: false,
                    history: false,
                    views:{
                        'menuContent': {
                            template: require('text!tpl/videochannel/videochannel-edit.html'),
                            controller: 'VideoChannelEditCtrl',
                        }
                    }
                })
                .state('app.videoChannelEditChannel',{
                    module: 'videochannel',
                    url: '/videochannel-channel/edit/{iChannelId}',
                    cache: false,
                    history: {
                        title: gettext('Edit Channels'),
                        isRoot: false
                    },
                    views:{
                        'menuContent': {
                            template: require('text!tpl/videochannel/videochannel-edit-channel.html'),
                            controller: 'VideoChannelEditChannelCtrl',
                        }
                    }
                })
                .state('app.videoChannelAddMoreVideo',{
                    module: 'videochannel',
                    url: '/videochannel/add-video/{iChannelId}',
                    cache: false,
                    history: false,
                    views:{
                        'menuContent': {
                            template: require('text!tpl/videochannel/videochannel-add-more-video.html'),
                            controller: 'VideoChannelAddMoreVideoCtrl',
                        }
                    }
                })

                .state('app.videoChannelDetail',{
                    module: 'videochannel',
                    url: '/videochannel/{iVideoId:int}',
                    cache: false,
                    views: {
                        'menuContent': {
                            template: require('text!tpl/videochannel/videochannel-detail.html'),
                            controller: 'VideoChannelDetailCtrl',
                        }
                    }
                })
                .state('app.VideoChannelDetailChannel',{
                    module: 'videochannel',
                    url: '/videochannel-channel/{iChannelId:int}',
                    cache: false,
                    views: {
                        'menuContent': {
                            template: require('text!tpl/videochannel/videochannel-detail-channel.html'),
                            controller: 'VideoChannelDetailChannelCtrl',
                        }
                    }
                })
                .state('app.VideoChannelBrowseChannel', {
                    module: 'videochannel',
                    url: '/videochannel/all_channels',
                    cache: false,
                    history: {
                        title: gettext('All Channels'),
                        isRoot: true
                    },
                    views: {
                        'menuContent': {
                            template: require('text!tpl/videochannel/videochannel-home.html'),
                            controller: 'VideoChannelHomeCtrl',
                        },
                        'tabContent@app.VideoChannelBrowseChannel': {
                            template: require('text!tpl/videochannel/videochannel-browse-channel.html'),
                            controller: 'VideoChannelBrowseChannelCtrl',
                        }
                    }
                })
                .state('app.VideoChannelMyChannel', {
                    module: 'videochannel',
                    url: '/videochannel/my_channels',
                    cache: false,
                    history: {
                        title: gettext('My Channels'),
                        isRoot: true
                    },
                    views: {
                        'menuContent': {
                            template: require('text!tpl/videochannel/videochannel-home.html'),
                            controller: 'VideoChannelHomeCtrl',
                        },
                        'tabContent@app.VideoChannelMyChannel': {
                            template: require('text!tpl/videochannel/videochannel-my-channel.html'),
                            controller: 'VideoChannelMyChannelCtrl',
                        }
                    }
                })
                .state('app.VideoChannelBrowseVideo', {
                    module: 'videochannel',
                    url: '/videochannel',
                    cache: false,
                    history: {
                        title: gettext('Videos'),
                        isRoot: true
                    },
                    views: {
                        'menuContent': {
                            template: require('text!tpl/videochannel/videochannel-home.html'),
                            controller: 'VideoChannelHomeCtrl',
                        },
                        'tabContent@app.VideoChannelBrowseVideo': {
                            template: require('text!tpl/videochannel/videochannel-browse-video.html'),
                            controller: 'VideoChannelBrowseVideoCtrl',
                        }
                    }
                })
            ;

        });
});