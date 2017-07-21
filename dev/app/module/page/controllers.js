define([
    'angular',
    'myapp',
    'page/controller/page-activity',
    'page/controller/page-add',
    'page/controller/page-browse-page',
    'page/controller/page-detail',
    'page/controller/page-edit',
    'page/controller/page-event',
    'page/controller/page-blog',
    'page/controller/page-video',
    'page/controller/page-videochannel',
    'page/controller/page-music',
    'page/controller/page-home',
    'page/controller/page-info',
    'page/controller/page-invite-list',
    'page/controller/page-item',
    'page/controller/page-list',
    'page/controller/page-my-page',
    'page/controller/page-photo',
    'page/controller/page-album',
    'page/controller/page-search',
    'page/controller/page-upload-photo',
    'page/controller/page-edit-avatar',
    'page/controller/page-edit-cover',
], function() {
    angular.module('myapp.controllers')
        .controller('PageActivityCtrl', require('page/controller/page-activity'))
        .controller('PageAddCtrl', require('page/controller/page-add'))
        .controller('PageBrowsePageCtrl', require('page/controller/page-browse-page'))
        .controller('PageDetailCtrl', require('page/controller/page-detail'))
        .controller('PageEditCtrl', require('page/controller/page-edit'))
        .controller('PageEventCtrl', require('page/controller/page-event'))
        .controller('PageBlogCtrl', require('page/controller/page-blog'))
        .controller('PageMusicCtrl', require('page/controller/page-music'))
        .controller('PageVideoCtrl', require('page/controller/page-video'))
        .controller('PageVideochannelCtrl', require('page/controller/page-videochannel'))
        .controller('PageHomeCtrl', require('page/controller/page-home'))
        .controller('PageInfoCtrl', require('page/controller/page-info'))
        .controller('PageInviteListCtrl', require('page/controller/page-invite-list'))
        .controller('PageItemCtrl', require('page/controller/page-item'))
        .controller('PageListCtrl', require('page/controller/page-list'))
        .controller('PageMyPageCtrl', require('page/controller/page-my-page'))
        .controller('PagePhotoCtrl', require('page/controller/page-photo'))
        .controller('PageAlbumCtrl', require('page/controller/page-album'))
        .controller('PageSearchCtrl', require('page/controller/page-search'))
        .controller('PageUploadPhotoCtrl', require('page/controller/page-upload-photo'))
        .controller('PageEditAvatarCtrl', require('page/controller/page-edit-avatar'))
        .controller('PageEditCoverCtrl', require('page/controller/page-edit-cover'))
        ;
});