define([
    'angular',
    'myapp',
    'video/directive/video-list',
    'video/directive/video-search',
],function(){
    angular.module('myapp.directives')
    .directive('videoListDir', require('video/directive/video-list'))
    .directive('videoSearchDir', require('video/directive/video-search'));
});
