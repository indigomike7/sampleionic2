define([
    'angular',
    'myapp',
    'page/controllers',
    'page/directives',
    'page/plugin/activity',
    'text!tpl/page/page-add.html',
    'text!tpl/page/page-activity.html',
    'text!tpl/page/page-browse-page.html',
    'text!tpl/page/page-detail.html',
    'text!tpl/page/page-edit.html',
    'text!tpl/page/page-blog.html',
    'text!tpl/page/page-event.html',
    'text!tpl/page/page-music.html',
    'text!tpl/page/page-video.html',
    'text!tpl/page/page-videochannel.html',
    'text!tpl/page/page-home.html',
    'text!tpl/page/page-info.html',
    'text!tpl/page/page-invite-list.html',
    'text!tpl/page/page-list.html',
    'text!tpl/page/page-member-list.html',
    'text!tpl/page/page-my-page.html',
    'text!tpl/page/page-photo.html',
    'text!tpl/page/page-album.html',
    'text!tpl/page/page-search.html',
    'text!tpl/page/page-upload-photo.html',
    'text!tpl/page/page-edit-avatar.html',
    'text!tpl/page/page-edit-cover.html',
], function(angular, myapp) {
    angular.module('myapp')
        .config(function($stateProvider, $urlRouterProvider, gettext) {

            $stateProvider
                .state('app.PageEdit', {
                    module: 'page',
                    url: '/pages/edit/:iPageId',
                    cache: false,
                    history: false,
                    views: {
                        menuContent: {
                            template: require('text!tpl/page/page-edit.html'),
                            controller: 'PageEditCtrl'
                        }
                    }
                })
                .state('app.pageEditAvatar',{
                    module: 'page',
                    url: '/pages/edit-avatar/:iPageId',
                    cache: false,
                    history: false,
                    views: {
                        'menuContent': {
                            template: require('text!tpl/page/page-edit-avatar.html'),
                            controller: 'PageEditAvatarCtrl'
                        }
                    }
                })
                .state('app.pageEditCover',{
                    module: 'page',
                    url: '/pages/edit-cover/:iPageId',
                    cache: false,
                    history: false,
                    views: {
                        'menuContent': {
                            template: require('text!tpl/page/page-edit-cover.html'),
                            controller: 'PageEditCoverCtrl'
                        }
                    }
                })
                .state('app.PageAdd', {
                    module: 'page',
                    url: '/pages/add',
                    cache: false,
                    history: false,
                    views: {
                        menuContent: {
                            template: require('text!tpl/page/page-add.html'),
                            controller: 'PageAddCtrl'
                        }
                    }
                })
                .state('app.pageDetailInfo', {
                    module: 'page',
                    url: '/pages/:iPageId',
                    cache: false,
                    views: {
                        'menuContent':{
                            template: require('text!tpl/page/page-detail.html'),
                            controller: 'PageDetailCtrl'
                        },
                        'tabContent@app.pageDetailInfo': {
                            template: require('text!tpl/page/page-info.html'),
                            controller: 'PageInfoCtrl'
                        }
                    }
                })
                .state('app.pageAdvDetailInfo', {
                    module: 'advpage',
                    url: '/advpage/:iPageId',
                    cache: false,
                    views: {
                        'menuContent':{
                            template: require('text!tpl/page/page-detail.html'),
                            controller: 'PageDetailCtrl'
                        },
                        'tabContent@app.pageAdvDetailInfo': {
                            template: require('text!tpl/page/page-info.html'),
                            controller: 'PageInfoCtrl'
                        }
                    }
                })
                .state('app.pageDetailActivity', {
                    module: 'page',
                    url: '/page_activity/:iPageId',
                    cache: false,
                    views: {
                        'menuContent':{
                            template: require('text!tpl/page/page-detail.html'),
                            controller: 'PageDetailCtrl'
                        },
                        'tabContent@app.pageDetailActivity': {
                            template: require('text!tpl/page/page-activity.html'),
                            controller: 'PageActivityCtrl'
                        }
                    }
                })
                .state('app.pageDetailUploadPhoto', {
                    module: 'page',
                    url: '/page_uploadphoto/:iPageId',
                    cache: false,
                    views: {
                        'menuContent':{
                            template: require('text!tpl/page/page-detail.html'),
                            controller: 'PageDetailCtrl'
                        },
                        'tabContent@app.pageDetailUploadPhoto': {
                            template: require('text!tpl/page/page-upload-photo.html'),
                            controller: 'PageUploadPhotoCtrl'
                        }
                    }
                })
                .state('app.pageDetailBlog', {
                    module: 'page',
                    url: '/page_blogs/:iPageId',
                    cache: false,
                    views: {
                        'menuContent':{
                            template: require('text!tpl/page/page-detail.html'),
                            controller: 'PageDetailCtrl'
                        },
                        'tabContent@app.pageDetailBlog': {
                            template: require('text!tpl/page/page-blog.html'),
                            controller: 'PageBlogCtrl'
                        }
                    }
                })
                .state('app.pageDetailMusic', {
                    module: 'page',
                    url: '/page_music/:iPageId',
                    cache: false,
                    views: {
                        'menuContent':{
                            template: require('text!tpl/page/page-detail.html'),
                            controller: 'PageDetailCtrl'
                        },
                        'tabContent@app.pageDetailMusic': {
                            template: require('text!tpl/page/page-music.html'),
                            controller: 'PageMusicCtrl'
                        }
                    }
                })
                .state('app.pageDetailVideo', {
                    module: 'page',
                    url: '/page_videos/:iPageId',
                    cache: false,
                    views: {
                        'menuContent':{
                            template: require('text!tpl/page/page-detail.html'),
                            controller: 'PageDetailCtrl'
                        },
                        'tabContent@app.pageDetailVideo': {
                            template: require('text!tpl/page/page-video.html'),
                            controller: 'PageVideoCtrl'
                        }
                    }
                })
                .state('app.pageDetailVideochannel', {
                    module: 'page',
                    url: '/page_videochannel/:iPageId',
                    cache: false,
                    views: {
                        'menuContent':{
                            template: require('text!tpl/page/page-detail.html'),
                            controller: 'PageDetailCtrl'
                        },
                        'tabContent@app.pageDetailVideochannel': {
                            template: require('text!tpl/page/page-videochannel.html'),
                            controller: 'PageVideochannelCtrl'
                        }
                    }
                })
                .state('app.pageDetailPhoto', {
                    module: 'page',
                    url: '/page_photos/:iPageId',
                    cache: false,
                    views: {
                        'menuContent':{
                            template: require('text!tpl/page/page-detail.html'),
                            controller: 'PageDetailCtrl'
                        },
                        'tabContent@app.pageDetailPhoto': {
                            template: require('text!tpl/page/page-photo.html'),
                            controller: 'PagePhotoCtrl'
                        }
                    }
                })
                .state('app.pageDetailAlbum', {
                    module: 'page',
                    url: '/page_albums/:iPageId',
                    cache: false,
                    views: {
                        'menuContent':{
                            template: require('text!tpl/page/page-detail.html'),
                            controller: 'PageDetailCtrl'
                        },
                        'tabContent@app.pageDetailAlbum': {
                            template: require('text!tpl/page/page-album.html'),
                            controller: 'PageAlbumCtrl'
                        }
                    }
                })
                .state('app.pageDetailEvent', {
                    module: 'page',
                    url: '/page_events/:iPageId',
                    cache: false,
                    views: {
                        'menuContent':{
                            template: require('text!tpl/page/page-detail.html'),
                            controller: 'PageDetailCtrl'
                        },
                        'tabContent@app.pageDetailEvent': {
                            template: require('text!tpl/page/page-event.html'),
                            controller: 'PageEventCtrl'
                        }
                    }
                })
                .state('app.PageHomeBrowse', {
                    module: 'page',
                    url: '/pages',
                    cache: false,
                    history: {
                        title: gettext('Pages'),
                        isRoot: true
                    },
                    views: {
                        'menuContent': {
                            template: require('text!tpl/page/page-home.html'),
                            controller: 'PageHomeCtrl'
                        },
                        'tabContent@app.PageHomeBrowse':{
                            template: require('text!tpl/page/page-browse-page.html'),
                            controller: 'PageBrowsePageCtrl'
                        }
                    }
                })
                .state('app.PageHomeMy', {
                    module: 'page',
                    url: '/pages_my',
                    cache: false,
                    history: {
                        title: gettext('Pages'),
                        isRoot: true
                    },
                    views: {
                        'menuContent': {
                            template: require('text!tpl/page/page-home.html'),
                            controller: 'PageHomeCtrl'
                        },
                        'tabContent@app.PageHomeMy':{
                            template: require('text!tpl/page/page-my-page.html'),
                            controller: 'PageMyPageCtrl'
                        }
                    }
                });
        });
});