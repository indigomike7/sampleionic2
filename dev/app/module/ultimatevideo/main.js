define([
    'angular',
    'myapp',
    'ultimatevideo/controllers',
    'ultimatevideo/directives',
    'ultimatevideo/plugin/activity',
    'text!tpl/ultimatevideo/ultimatevideo-browse-video.html',
    'text!tpl/ultimatevideo/ultimatevideo-browse-playlist.html',
    'text!tpl/ultimatevideo/ultimatevideo-my-playlist.html',
    'text!tpl/ultimatevideo/ultimatevideo-home.html',
    'text!tpl/ultimatevideo/ultimatevideo-my-video.html',
    'text!tpl/ultimatevideo/ultimatevideo-favorite-video.html',
    'text!tpl/ultimatevideo/ultimatevideo-watch-later-video.html',
    'text!tpl/ultimatevideo/ultimatevideo-detail-video.html',
    'text!tpl/ultimatevideo/ultimatevideo-detail-playlist.html',
    'text!tpl/ultimatevideo/ultimatevideo-add-video.html',
    'text!tpl/ultimatevideo/ultimatevideo-add-playlist.html',
    'text!tpl/ultimatevideo/ultimatevideo-edit-playlist.html',
    'text!tpl/ultimatevideo/ultimatevideo-edit-video.html',
    'text!tpl/ultimatevideo/ultimatevideo-add-to-playlist.html',
    'text!tpl/ultimatevideo/ultimatevideo-history.html',
], function(angular, myapp) {
    angular.module('myapp')
    .config(function($stateProvider, $urlRouterProvider, gettext) {

        $stateProvider
            .state('app.UltimateVideoBrowseVideo', {
                module: 'ultimatevideo',
                url: '/ultimatevideo',
                cache: false,
                history: {
                    title: gettext('Ultimate Videos'),
                    isRoot: true
                },
                views: {
                    'menuContent': {
                        template: require('text!tpl/ultimatevideo/ultimatevideo-home.html'),
                        controller: 'UltimateVideoHomeCtrl',
                    },
                    'tabContent@app.UltimateVideoBrowseVideo': {
                        template: require('text!tpl/ultimatevideo/ultimatevideo-browse-video.html'),
                        controller: 'UltimateVideoBrowseVideoCtrl',
                    }
                }
            })
            .state('app.UltimateVideoMyVideo', {
                module: 'ultimatevideo',
                url: '/ultimatevideo/my',
                cache: false,
                history: {
                    title: gettext('My Videos'),
                    isRoot: true
                },
                views: {
                    'menuContent': {
                        template: require('text!tpl/ultimatevideo/ultimatevideo-home.html'),
                        controller: 'UltimateVideoHomeCtrl',
                    },
                    'tabContent@app.UltimateVideoMyVideo': {
                        template: require('text!tpl/ultimatevideo/ultimatevideo-my-video.html'),
                        controller: 'UltimateVideoMyVideoCtrl',
                    }
                }
            })
            .state('app.UltimateVideoFavoriteVideo', {
                module: 'ultimatevideo',
                url: '/ultimatevideo/favorite',
                cache: false,
                history: {
                    title: gettext('Favorite Videos'),
                    isRoot: true
                },
                views: {
                    'menuContent': {
                        template: require('text!tpl/ultimatevideo/ultimatevideo-home.html'),
                        controller: 'UltimateVideoHomeCtrl',
                    },
                    'tabContent@app.UltimateVideoFavoriteVideo': {
                        template: require('text!tpl/ultimatevideo/ultimatevideo-favorite-video.html'),
                        controller: 'UltimateVideoFavoriteVideoCtrl',
                    }
                }
            })
            .state('app.UltimateVideoWatchLaterVideo', {
                module: 'ultimatevideo',
                url: '/ultimatevideo/watch-later',
                cache: false,
                history: {
                    title: gettext('Watch Later'),
                    isRoot: true
                },
                views: {
                    'menuContent': {
                        template: require('text!tpl/ultimatevideo/ultimatevideo-home.html'),
                        controller: 'UltimateVideoHomeCtrl',
                    },
                    'tabContent@app.UltimateVideoWatchLaterVideo': {
                        template: require('text!tpl/ultimatevideo/ultimatevideo-watch-later-video.html'),
                        controller: 'UltimateVideoWatchLaterVideoCtrl',
                    }
                }
            })

            .state('app.UltimateVideoDetailVideo',{
                module: 'ultimatevideo',
                url: '/ultimatevideo/video/{iVideoId:int}',
                cache: false,
                views: {
                    'menuContent': {
                        template: require('text!tpl/ultimatevideo/ultimatevideo-detail-video.html'),
                        controller: 'UltimateVideoDetailVideoCtrl',
                    }
                }
            })
            .state('app.UltimateVideoDetailPlaylist',{
                module: 'ultimatevideo',
                url: '/ultimatevideo/playlist/{iPlaylistId:int}',
                cache: false,
                views: {
                    'menuContent': {
                        template: require('text!tpl/ultimatevideo/ultimatevideo-detail-playlist.html'),
                        controller: 'UltimateVideoDetailPlaylistCtrl',
                    }
                }
            })

            .state('app.UltimateVideoAddVideo',{
                module: 'ultimatevideo',
                url: '/ultimatevideo/add/video',
                cache: false,
                history: false,
                views:{
                    'menuContent': {
                        template: require('text!tpl/ultimatevideo/ultimatevideo-add-video.html'),
                        controller: 'UltimateVideoAddVideoCtrl',
                    }
                }
            })

            .state('app.UltimateVideoAddPlaylist',{
                module: 'ultimatevideo',
                url: '/ultimatevideo/add/playlist',
                cache: false,
                history: false,
                views:{
                    'menuContent': {
                        template: require('text!tpl/ultimatevideo/ultimatevideo-add-playlist.html'),
                        controller: 'UltimateVideoAddPlaylistCtrl',
                    }
                }
            })

            .state('app.UltimateVideoEditPlaylist',{
                module: 'ultimatevideo',
                url: '/ultimatevideo/edit/playlist/{iPlaylistId:int}',
                cache: false,
                history: false,
                views:{
                    'menuContent': {
                        template: require('text!tpl/ultimatevideo/ultimatevideo-edit-playlist.html'),
                        controller: 'UltimateVideoEditPlaylistCtrl',
                    }
                }
            })
            .state('app.UltimateVideoBrowsePlaylist', {
                module: 'ultimatevideo',
                url: '/ultimatevideo/playlist',
                cache: false,
                history: {
                    title: gettext('All Playlists'),
                    isRoot: true
                },
                views: {
                    'menuContent': {
                        template: require('text!tpl/ultimatevideo/ultimatevideo-home.html'),
                        controller: 'UltimateVideoHomeCtrl',
                    },
                    'tabContent@app.UltimateVideoBrowsePlaylist': {
                        template: require('text!tpl/ultimatevideo/ultimatevideo-browse-playlist.html'),
                        controller: 'UltimateVideoBrowsePlaylistCtrl',
                    }
                }
            })

            .state('app.UltimateVideoMyPlaylist', {
                module: 'ultimatevideo',
                url: '/ultimatevideo/playlist/my',
                cache: false,
                history: {
                    title: gettext('My Playlists'),
                    isRoot: true
                },
                views: {
                    'menuContent': {
                        template: require('text!tpl/ultimatevideo/ultimatevideo-home.html'),
                        controller: 'UltimateVideoHomeCtrl',
                    },
                    'tabContent@app.UltimateVideoMyPlaylist': {
                        template: require('text!tpl/ultimatevideo/ultimatevideo-my-playlist.html'),
                        controller: 'UltimateVideoMyPlaylistCtrl',
                    }
                }
            })

            .state('app.UltimateVideoAddToPlaylist',{
                module: 'ultimatevideo',
                url: '/ultimatevideo/add-to-playlist/{iVideoId:int}',
                cache: false,
                views: {
                    'menuContent': {
                        template: require('text!tpl/ultimatevideo/ultimatevideo-add-to-playlist.html'),
                        controller: 'UltimateVideoAddToPlaylistCtrl',
                    }
                }
            })
            .state('app.UltimateVideoEditVideo',{
                module: 'ultimatevideo',
                url: '/ultimatevideo/edit/video/{iVideoId:int}',
                cache: false,
                history: false,
                views:{
                    'menuContent': {
                        template: require('text!tpl/ultimatevideo/ultimatevideo-edit-video.html'),
                        controller: 'UltimateVideoEditVideoCtrl',
                    }
                }
            })
            .state('app.UltimateVideoHistory', {
                module: 'ultimatevideo',
                url: '/ultimatevideo/history',
                cache: false,
                history: {
                    title: gettext('History'),
                    isRoot: true
                },
                views: {
                    'menuContent': {
                        template: require('text!tpl/ultimatevideo/ultimatevideo-home.html'),
                        controller: 'UltimateVideoHomeCtrl',
                    },
                    'tabContent@app.UltimateVideoHistory': {
                        template: require('text!tpl/ultimatevideo/ultimatevideo-history.html'),
                        controller: 'UltimateVideoHistoryCtrl',
                    }
                }
            })

        ;

    });
});