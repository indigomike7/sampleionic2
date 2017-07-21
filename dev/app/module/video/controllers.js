define([
    'angular',
    'myapp',
    'video/controller/video-add',
    'video/controller/video-browse-video',
    'video/controller/video-detail',
    'video/controller/video-edit',
    'video/controller/video-home',
    'video/controller/video-item',
    'video/controller/video-list',
    'video/controller/video-my-video',
    'video/controller/video-search',
    
], function() {
    angular.module('myapp.controllers')
        .controller('VideoAddCtrl', require('video/controller/video-add'))
        .controller('VideoHomeCtrl', require('video/controller/video-home'))
        .controller('VideoBrowseVideoCtrl', require('video/controller/video-browse-video'))
        .controller('VideoMyVideoCtrl', require('video/controller/video-my-video'))
        .controller('VideoEditCtrl', require('video/controller/video-edit'))
        .controller('VideoItemCtrl',require('video/controller/video-item'))
        .controller('VideoDetailCtrl', require('video/controller/video-detail'))
        .controller('VideoSearchCtrl', require('video/controller/video-search'));
});