define([
    'angular',
    'myapp',
    'ultimatevideo/directive/ultimatevideo-list-video',
    'ultimatevideo/directive/ultimatevideo-list-history',
    'ultimatevideo/directive/ultimatevideo-playlist-video',
    'ultimatevideo/directive/ultimatevideo-list-playlist',
    'ultimatevideo/directive/ultimatevideo-search-video',
    'ultimatevideo/directive/ultimatevideo-search-playlist',
    'ultimatevideo/directive/ultimatevideo-search-history',
    'ultimatevideo/directive/ultimatevideo-edit-playlist-video',
],function(){
    angular.module('myapp.directives')
    .directive('ultimatevideoListVideoDir', require('ultimatevideo/directive/ultimatevideo-list-video'))
    .directive('ultimatevideoListHistoryDir', require('ultimatevideo/directive/ultimatevideo-list-history'))
    .directive('ultimatevideoPlaylistVideoDir', require('ultimatevideo/directive/ultimatevideo-playlist-video'))
    .directive('ultimatevideoListPlaylistDir', require('ultimatevideo/directive/ultimatevideo-list-playlist'))
    .directive('ultimatevideoSearchVideoDir', require('ultimatevideo/directive/ultimatevideo-search-video'))
    .directive('ultimatevideoSearchPlaylistDir', require('ultimatevideo/directive/ultimatevideo-search-playlist'))
    .directive('ultimatevideoSearchHistoryDir', require('ultimatevideo/directive/ultimatevideo-search-history'))
    .directive('ultimatevideoEditPlaylistVideoDir', require('ultimatevideo/directive/ultimatevideo-edit-playlist-video'));
});
