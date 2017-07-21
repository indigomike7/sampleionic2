define([
    'angular',
    'myapp',
    'group/controllers',
    'group/directives',
    'group/plugin/activity',
    'text!tpl/group/group-add.html',
    'text!tpl/group/group-activity.html',
    'text!tpl/group/group-browse-group.html',
    'text!tpl/group/group-detail.html',
    'text!tpl/group/group-edit.html',
    'text!tpl/group/group-blog.html',
    'text!tpl/group/group-event.html',
    'text!tpl/group/group-music.html',
    'text!tpl/group/group-video.html',
    'text!tpl/group/group-videochannel.html',
    'text!tpl/group/group-home.html',
    'text!tpl/group/group-info.html',
    'text!tpl/group/group-invite-list.html',
    'text!tpl/group/group-list.html',
    'text!tpl/group/group-member-list.html',
    'text!tpl/group/group-my-group.html',
    'text!tpl/group/group-photo.html',
    'text!tpl/group/group-album.html',
    'text!tpl/group/group-search.html',
    'text!tpl/group/group-upload-photo.html',
    'text!tpl/group/group-edit-avatar.html',
    'text!tpl/group/group-edit-cover.html',
], function(angular, myapp) {
    angular.module('myapp')
        .config(function($stateProvider, $urlRouterProvider, gettext) {

            $stateProvider
                .state('app.GroupEdit', {
                    module: 'group',
                    url: '/groups/edit/:iGroupId',
                    cache: false,
                    history: false,
                    views: {
                        menuContent: {
                            template: require('text!tpl/group/group-edit.html'),
                            controller: 'GroupEditCtrl'
                        }
                    }
                })
                .state('app.groupEditAvatar',{
                    module: 'group',
                    url: '/groups/edit-avatar/:iGroupId',
                    cache: false,
                    history: false,
                    views: {
                        'menuContent': {
                            template: require('text!tpl/group/group-edit-avatar.html'),
                            controller: 'GroupEditAvatarCtrl'
                        }
                    }
                })
                .state('app.groupEditCover',{
                    module: 'group',
                    url: '/groups/edit-cover/:iGroupId',
                    cache: false,
                    history: false,
                    views: {
                        'menuContent': {
                            template: require('text!tpl/group/group-edit-cover.html'),
                            controller: 'GroupEditCoverCtrl'
                        }
                    }
                })
                .state('app.GroupAdd', {
                    module: 'group',
                    url: '/groups/add',
                    cache: false,
                    history: false,
                    views: {
                        menuContent: {
                            template: require('text!tpl/group/group-add.html'),
                            controller: 'GroupAddCtrl'
                        }
                    }
                })
                .state('app.groupDetailInfo', {
                    module: 'group',
                    url: '/groups/:iGroupId',
                    cache: false,
                    views: {
                        'menuContent':{
                            template: require('text!tpl/group/group-detail.html'),
                            controller: 'GroupDetailCtrl'
                        },
                        'tabContent@app.groupDetailInfo': {
                            template: require('text!tpl/group/group-info.html'),
                            controller: 'GroupInfoCtrl'
                        }
                    }
                })
                .state('app.groupAdvDetailInfo', {
                    module: 'advgroup',
                    url: '/advgroup/:iGroupId',
                    cache: false,
                    views: {
                        'menuContent':{
                            template: require('text!tpl/group/group-detail.html'),
                            controller: 'GroupDetailCtrl'
                        },
                        'tabContent@app.groupAdvDetailInfo': {
                            template: require('text!tpl/group/group-info.html'),
                            controller: 'GroupInfoCtrl'
                        }
                    }
                })
                .state('app.groupDetailActivity', {
                    module: 'group',
                    url: '/group_activity/:iGroupId',
                    cache: false,
                    views: {
                        'menuContent':{
                            template: require('text!tpl/group/group-detail.html'),
                            controller: 'GroupDetailCtrl'
                        },
                        'tabContent@app.groupDetailActivity': {
                            template: require('text!tpl/group/group-activity.html'),
                            controller: 'GroupActivityCtrl'
                        }
                    }
                })
                .state('app.groupDetailUploadPhoto', {
                    module: 'group',
                    url: '/group_uploadphoto/:iGroupId',
                    cache: false,
                    views: {
                        'menuContent':{
                            template: require('text!tpl/group/group-detail.html'),
                            controller: 'GroupDetailCtrl'
                        },
                        'tabContent@app.groupDetailUploadPhoto': {
                            template: require('text!tpl/group/group-upload-photo.html'),
                            controller: 'GroupUploadPhotoCtrl'
                        }
                    }
                })
                .state('app.groupDetailBlog', {
                    module: 'group',
                    url: '/group_blogs/:iGroupId',
                    cache: false,
                    views: {
                        'menuContent':{
                            template: require('text!tpl/group/group-detail.html'),
                            controller: 'GroupDetailCtrl'
                        },
                        'tabContent@app.groupDetailBlog': {
                            template: require('text!tpl/group/group-blog.html'),
                            controller: 'GroupBlogCtrl'
                        }
                    }
                })
                .state('app.groupDetailMusic', {
                    module: 'group',
                    url: '/group_music/:iGroupId',
                    cache: false,
                    views: {
                        'menuContent':{
                            template: require('text!tpl/group/group-detail.html'),
                            controller: 'GroupDetailCtrl'
                        },
                        'tabContent@app.groupDetailMusic': {
                            template: require('text!tpl/group/group-music.html'),
                            controller: 'GroupMusicCtrl'
                        }
                    }
                })
                .state('app.groupDetailVideo', {
                    module: 'group',
                    url: '/group_videos/:iGroupId',
                    cache: false,
                    views: {
                        'menuContent':{
                            template: require('text!tpl/group/group-detail.html'),
                            controller: 'GroupDetailCtrl'
                        },
                        'tabContent@app.groupDetailVideo': {
                            template: require('text!tpl/group/group-video.html'),
                            controller: 'GroupVideoCtrl'
                        }
                    }
                })
                .state('app.groupDetailVideochannel', {
                    module: 'group',
                    url: '/group_videochannel/:iGroupId',
                    cache: false,
                    views: {
                        'menuContent':{
                            template: require('text!tpl/group/group-detail.html'),
                            controller: 'GroupDetailCtrl'
                        },
                        'tabContent@app.groupDetailVideochannel': {
                            template: require('text!tpl/group/group-videochannel.html'),
                            controller: 'GroupVideochannelCtrl'
                        }
                    }
                })
                .state('app.groupDetailPhoto', {
                    module: 'group',
                    url: '/group_photos/:iGroupId',
                    cache: false,
                    views: {
                        'menuContent':{
                            template: require('text!tpl/group/group-detail.html'),
                            controller: 'GroupDetailCtrl'
                        },
                        'tabContent@app.groupDetailPhoto': {
                            template: require('text!tpl/group/group-photo.html'),
                            controller: 'GroupPhotoCtrl'
                        }
                    }
                })
                .state('app.groupDetailAlbum', {
                    module: 'group',
                    url: '/group_albums/:iGroupId',
                    cache: false,
                    views: {
                        'menuContent':{
                            template: require('text!tpl/group/group-detail.html'),
                            controller: 'GroupDetailCtrl'
                        },
                        'tabContent@app.groupDetailAlbum': {
                            template: require('text!tpl/group/group-album.html'),
                            controller: 'GroupAlbumCtrl'
                        }
                    }
                })
                .state('app.groupDetailEvent', {
                    module: 'group',
                    url: '/group_events/:iGroupId',
                    cache: false,
                    views: {
                        'menuContent':{
                            template: require('text!tpl/group/group-detail.html'),
                            controller: 'GroupDetailCtrl'
                        },
                        'tabContent@app.groupDetailEvent': {
                            template: require('text!tpl/group/group-event.html'),
                            controller: 'GroupEventCtrl'
                        }
                    }
                })
                .state('app.GroupHomeBrowse', {
                    module: 'group',
                    url: '/groups',
                    cache: false,
                    history: {
                        title: gettext('Groups'),
                        isRoot: true
                    },
                    views: {
                        'menuContent': {
                            template: require('text!tpl/group/group-home.html'),
                            controller: 'GroupHomeCtrl'
                        },
                        'tabContent@app.GroupHomeBrowse':{
                            template: require('text!tpl/group/group-browse-group.html'),
                            controller: 'GroupBrowseGroupCtrl'
                        }
                    }
                })
                .state('app.GroupHomeMy', {
                    module: 'group',
                    url: '/groups_my',
                    cache: false,
                    history: {
                        title: gettext('Groups'),
                        isRoot: true
                    },
                    views: {
                        'menuContent': {
                            template: require('text!tpl/group/group-home.html'),
                            controller: 'GroupHomeCtrl'
                        },
                        'tabContent@app.GroupHomeMy':{
                            template: require('text!tpl/group/group-my-group.html'),
                            controller: 'GroupMyGroupCtrl'
                        }
                    }
                });
        });
});