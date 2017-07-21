define([
    'angular',
    'myapp',
    'group/controller/group-activity',
    'group/controller/group-add',
    'group/controller/group-browse-group',
    'group/controller/group-detail',
    'group/controller/group-edit',
    'group/controller/group-event',
    'group/controller/group-blog',
    'group/controller/group-video',
    'group/controller/group-videochannel',
    'group/controller/group-music',
    'group/controller/group-home',
    'group/controller/group-info',
    'group/controller/group-invite-list',
    'group/controller/group-item',
    'group/controller/group-list',
    'group/controller/group-my-group',
    'group/controller/group-photo',
    'group/controller/group-album',
    'group/controller/group-search',
    'group/controller/group-upload-photo',
    'group/controller/group-edit-avatar',
    'group/controller/group-edit-cover',
], function() {
    angular.module('myapp.controllers')
        .controller('GroupActivityCtrl', require('group/controller/group-activity'))
        .controller('GroupAddCtrl', require('group/controller/group-add'))
        .controller('GroupBrowseGroupCtrl', require('group/controller/group-browse-group'))
        .controller('GroupDetailCtrl', require('group/controller/group-detail'))
        .controller('GroupEditCtrl', require('group/controller/group-edit'))
        .controller('GroupEventCtrl', require('group/controller/group-event'))
        .controller('GroupBlogCtrl', require('group/controller/group-blog'))
        .controller('GroupMusicCtrl', require('group/controller/group-music'))
        .controller('GroupVideoCtrl', require('group/controller/group-video'))
        .controller('GroupVideochannelCtrl', require('group/controller/group-videochannel'))
        .controller('GroupHomeCtrl', require('group/controller/group-home'))
        .controller('GroupInfoCtrl', require('group/controller/group-info'))
        .controller('GroupInviteListCtrl', require('group/controller/group-invite-list'))
        .controller('GroupItemCtrl', require('group/controller/group-item'))
        .controller('GroupListCtrl', require('group/controller/group-list'))
        .controller('GroupMyGroupCtrl', require('group/controller/group-my-group'))
        .controller('GroupPhotoCtrl', require('group/controller/group-photo'))
        .controller('GroupAlbumCtrl', require('group/controller/group-album'))
        .controller('GroupSearchCtrl', require('group/controller/group-search'))
        .controller('GroupUploadPhotoCtrl', require('group/controller/group-upload-photo'))
        .controller('GroupEditAvatarCtrl', require('group/controller/group-edit-avatar'))
        .controller('GroupEditCoverCtrl', require('group/controller/group-edit-cover'))
        ;
});