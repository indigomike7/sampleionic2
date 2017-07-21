define([
    'angular',
    'myapp',
    'photo/controllers',
    'photo/directives',
    'photo/plugin/activity',
    'photo/plugin/message',
    'text!tpl/photo/photo-album-detail.html',
    'text!tpl/photo/photo-album-edit.html',
    'text!tpl/photo/photo-album-add.html',
    'text!tpl/photo/photo-browse-photo.html',
    'text!tpl/photo/photo-browse-album.html',
    'text!tpl/photo/photo-my-album.html',
    'text!tpl/photo/photo-my-photo.html',
    'text!tpl/photo/photo-detail.html',
    'text!tpl/photo/photo-home.html',
], function(angular, myapp) {
    
    angular.module('myapp')
    .config(function($stateProvider, $urlRouterProvider, gettext) {
    
        $stateProvider
        .state('app.PhotoAddAlbums', {
            module: 'photo',
            url: '/albums/add',
            history: false,
            cache: false,
            views: {
                'menuContent': {
                    template: require('text!tpl/photo/photo-album-add.html'),
                    controller: 'PhotoAlbumAddCtrl',
                }
            }
        })
        $stateProvider
        .state('app.PhotoAddAlbumsType', {
            module: 'photo',
            url: '/albums/add/:sParentType/:iParentId',
            cache: false,
            history: false,
            views: {
                'menuContent': {
                    template: require('text!tpl/photo/photo-album-add.html'),
                    controller: 'PhotoAlbumAddCtrl',
                }
            }
        })
        .state('app.PhotoEditAlbums',{
            module: 'photo',
            url: '/albums/edit/{iAlbumId}',
            cache: false,
            history: false,
            views: {
                'menuContent': {
                    template: require('text!tpl/photo/photo-album-edit.html'),
                    controller: 'PhotoAlbumEditCtrl',
                }
            }
        })
        .state('app.PhotoMyAlbum', {
            module: 'photo',
            url: '/albums/my',
            cache: false,
            history: {
                title: gettext('Albums'),
                isRoot: true
            },
            views: {
                'menuContent': {
                    template: require('text!tpl/photo/photo-home.html'),
                    controller: 'PhotoHomeCtrl',
                },
                'tabContent@app.PhotoMyAlbum': {
                    template: require('text!tpl/photo/photo-my-album.html'),
                    controller: 'PhotoMyAlbumCtrl',
                }
            }
        })
        .state('app.PhotoMyPhoto', {
            module: 'photo',
            url: '/photos/my',
            cache: false,
            history: {
                title: gettext('Photos'),
                isRoot: true
            },
            views: {
                'menuContent': {
                    template: require('text!tpl/photo/photo-home.html'),
                    controller: 'PhotoHomeCtrl',
                },
                'tabContent@app.PhotoMyPhoto': {
                    template: require('text!tpl/photo/photo-my-photo.html'),
                    controller: 'PhotoMyPhotoCtrl',
                }
            }
        })
        .state('app.PhotoBrowseAlbum', {
            module: 'photo',
            url: '/albums',
            cache: false,
            history: {
                title: gettext('Albums'),
                isRoot: true
            },
            views: {
                'menuContent': {
                    template: require('text!tpl/photo/photo-home.html'),
                    controller: 'PhotoHomeCtrl',
                },
                'tabContent@app.PhotoBrowseAlbum': {
                    template: require('text!tpl/photo/photo-browse-album.html'),
                    controller: 'PhotoBrowseAlbumCtrl',
                }
            }
        })
        .state('app.PhotoAlbumDetail', {
            module: 'photo',
            url: '/album/:iAlbumId',
            cache: false,
            views: {
                'menuContent': {
                    template: require('text!tpl/photo/photo-album-detail.html'),
                    controller: 'PhotoAlbumDetailCtrl',
                }
            }
        })
        .state('app.PhotoAdvAlbumDetail', {
            module: 'photo',
            url: '/advancedphoto_album/:iAlbumId',
            cache: false,
            sItemType: 'advancedphoto_album',
            views: {
                'menuContent': {
                    template: require('text!tpl/photo/photo-album-detail.html'),
                    controller: 'PhotoAlbumDetailCtrl',
                }
            }
        })
        .state('app.PhotoSlider', {
            module: 'photo',
            url: '/photos/:sParentType/:iParentId/:sItemType/:iPhotoId',
            cache: false,
            views: {
                'menuContent': {
                    template: require('text!tpl/photo/photo-detail.html'),
                    controller: 'PhotoDetailCtrl',
                }
            }
        })
        .state('app.AlbumPhotoDetail', {
            module: 'photo',
            sItemType: 'album_photo',
            url: '/album_photo/:iPhotoId',
            cache: false,
            views: {
                'menuContent': {
                    template: require('text!tpl/photo/photo-detail.html'),
                    controller: 'PhotoDetailCtrl',
                }
            }
        })
        .state('app.PhotoDetail', {
            module: 'photo',
            sItemType: 'photo',
            url: '/photo/:iPhotoId',
            cache: false,
            views: {
                'menuContent': {
                    template: require('text!tpl/photo/photo-detail.html'),
                    controller: 'PhotoDetailCtrl',
                }
            },
        })
        .state('app.AdvalbumPhotoDetail', {
            module: 'photo',
            sItemType: 'advancedphoto',
            url: '/advancedphoto/:iPhotoId',
            cache: false,
            views: {
                'menuContent': {
                    template: require('text!tpl/photo/photo-detail.html'),
                    controller: 'PhotoDetailCtrl',
                }
            },
        })
        .state('app.GroupPhotoDetail', {
            module: 'photo',
            sItemType: 'group_photo',
            url: '/group_photo/:iPhotoId',
            cache: false,
            views: {
                'menuContent': {
                    template: require('text!tpl/photo/photo-detail.html'),
                    controller: 'PhotoDetailCtrl',
                }
            },
        })
        .state('app.AdvgroupPhotoDetail', {
            module: 'photo',
            sItemType: 'advgroup_photo',
            url: '/advgroup_photo/:iPhotoId',
            cache: false,
            views: {
                'menuContent': {
                    template: require('text!tpl/photo/photo-detail.html'),
                    controller: 'PhotoDetailCtrl',
                }
            },
        })
        .state('app.EventPhotoDetail', {
            module: 'photo',
            sItemType: 'event_photo',
            url: '/event_photo/:iPhotoId',
            cache: false,
            views: {
                'menuContent': {
                    template: require('text!tpl/photo/photo-detail.html'),
                    controller: 'PhotoDetailCtrl',
                }
            },
        })
        .state('app.YneventPhotoDetail', {
            module: 'photo',
            sItemType: 'ynevent_photo',
            url: '/ynevent_photo/:iPhotoId',
            cache: false,
            views: {
                'menuContent': {
                    template: require('text!tpl/photo/photo-detail.html'),
                    controller: 'PhotoDetailCtrl',
                }
            },
        })
        .state('app.ListingPhotoDetail', {
            module: 'photo',
            sItemType: 'listing_photo',
            url: '/listing_photo/:iPhotoId',
            cache: false,
            views: {
                'menuContent': {
                    template: require('text!tpl/photo/photo-detail.html'),
                    controller: 'PhotoDetailCtrl',
                }
            },
        })
        .state('app.PhotoBrowsePhoto', {
            module: 'photo',
            url: '/photos',
            cache: false,
            history: {
                title: gettext('Photos'),
                isRoot: true
            },
            views: {
                'menuContent': {
                    template: require('text!tpl/photo/photo-home.html'),
                    controller: 'PhotoHomeCtrl',
                },
                'tabContent@app.PhotoBrowsePhoto': {
                    template: require('text!tpl/photo/photo-browse-photo.html'),
                    controller: 'PhotoBrowsePhotoCtrl',
                }
            }
        })
        ;
    });
});