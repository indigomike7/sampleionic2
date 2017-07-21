define([
    'music/controller/music-browse-playlist',
    'music/controller/music-browse-song',
    'music/controller/music-home-ipad',
    'music/controller/music-home',
    'music/controller/music-my-playlist',
    'music/controller/music-my-song',
    'music/controller/music-playlist-detail',
    'music/controller/music-playlist-item',
    'music/controller/music-playlist-list',
    'music/controller/music-song-detail',
    'music/controller/music-song-item',
    'music/controller/music-song-list',
    'music/controller/music-song-search'
], function() {
    
    angular.module('myapp')
    .controller('MusicBrowsePlaylistCtrl', require('music/controller/music-browse-playlist'))
    .controller('MusicBrowseSongCtrl', require('music/controller/music-browse-song'))
    .controller('MusicHomeIpadCtrl', require('music/controller/music-home-ipad'))
    .controller('MusicHomeCtrl', require('music/controller/music-home'))
    .controller('MusicMyPlaylistCtrl', require('music/controller/music-my-playlist'))
    .controller('MusicMySongCtrl', require('music/controller/music-my-song'))
    .controller('MusicPlaylistDetailCtrl', require('music/controller/music-playlist-detail'))
    .controller('MusicPlaylistItemCtrl', require('music/controller/music-playlist-item'))
    .controller('MusicPlaylistListCtrl', require('music/controller/music-playlist-list'))
    .controller('MusicSongDetailCtrl', require('music/controller/music-song-detail'))
    .controller('MusicSongItemCtrl', require('music/controller/music-song-item'))
    .controller('MusicSongListCtrl', require('music/controller/music-song-list'))
    .controller('MusicSongSearchCtrl', require('music/controller/music-song-search'))
    ;
});