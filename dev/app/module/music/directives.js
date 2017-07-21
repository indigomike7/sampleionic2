define([
	'music/directive/music-playlist-detail-content',
    'music/directive/music-playlist-list',
    'music/directive/music-playlist-search',
	'music/directive/music-song-detail-content',
    'music/directive/music-song-list',
    'music/directive/music-song-search'
], function(){
    angular.module('myapp.directives')
        .directive('musicPlaylistDetailContent', require('music/directive/music-playlist-detail-content'))
        .directive('musicPlaylistListDir', require('music/directive/music-playlist-list'))
        .directive('musicPlaylistSearchDir', require('music/directive/music-playlist-search'))
        .directive('musicSongDetailContent', require('music/directive/music-song-detail-content'))
        .directive('musicSongListDir', require('music/directive/music-song-list'))
        .directive('musicSongSearchDir', require('music/directive/music-song-search'))
        ;
});
