define([
    'angular',
    'myapp',
    'video/controllers',
    'video/directives',
    'video/plugin/activity',
    'video/plugin/message',
    'text!tpl/video/video-add.html',
    'text!tpl/video/video-attachment.html',
    'text!tpl/video/video-browse-video.html',
    'text!tpl/video/video-detail.html',
    'text!tpl/video/video-edit.html',
    'text!tpl/video/video-home.html',
    'text!tpl/video/video-message-attachment.html',
    'text!tpl/video/video-my-video.html',
    'text!tpl/video/video-search.html',
], function(angular, myapp) {
    angular.module('myapp')
    .config(function($stateProvider, $urlRouterProvider, gettext) {

        $stateProvider
        .state('app.addVideo',{
            module: 'video',
            url: '/videos/add',
            cache: false,
            history: false,
            views:{
                'menuContent': {
                    template: require('text!tpl/video/video-add.html'),
                    controller: 'VideoAddCtrl',
                }
            }
        })
        .state('app.addVideoType',{
            module: 'video',
            url: '/videos/add/:sParentType/:iParentId',
            cache: false,
            history: false,
            views:{
                'menuContent': {
                    template: require('text!tpl/video/video-add.html'),
                    controller: 'VideoAddCtrl',
                }
            }
        })
        .state('app.editVideos',{
            module: 'video',
            url: '/videos/edit/{iVideoId}',
            cache: false,
            history: false,
            views:{
                'menuContent': {
                    template: require('text!tpl/video/video-edit.html'),
                    controller: 'VideoEditCtrl',
                }
            }
        })
        .state('app.videoDetail',{
            module: 'video',
            url: '/video/{iVideoId}',
            cache: false,
            views: {
                'menuContent': {
                    template: require('text!tpl/video/video-detail.html'),
                    controller: 'VideoDetailCtrl',
                }
            }
        })
        .state('app.VideoBrowseVideo', {
            module: 'video',
            url: '/videos',
            cache: false,
            history: {
                title: gettext('Videos'),
                isRoot: true
            },
            views: {
                'menuContent': {
                    template: require('text!tpl/video/video-home.html'),
                    controller: 'VideoHomeCtrl',
                },
                'tabContent@app.VideoBrowseVideo': {
                    template: require('text!tpl/video/video-browse-video.html'),
                    controller: 'VideoBrowseVideoCtrl',
                }
            }
        })
        .state('app.VideoMyVideo', {
            module: 'video',
            url: '/videos/my',
            cache: false,
            history: {
                title: gettext('Videos'),
                isRoot: true
            },
            views: {
                'menuContent': {
                    template: require('text!tpl/video/video-home.html'),
                    controller: 'VideoHomeCtrl',
                },
                'tabContent@app.VideoMyVideo': {
                    template: require('text!tpl/video/video-my-video.html'),
                    controller: 'VideoMyVideoCtrl',
                }
            }
        })
        ;

    });
});