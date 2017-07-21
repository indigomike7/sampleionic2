define([
    'angular',
    'myapp',
    'ultimatevideo/controller/ultimatevideo-browse-video',
    'ultimatevideo/controller/ultimatevideo-browse-playlist',
    'ultimatevideo/controller/ultimatevideo-my-playlist',
    'ultimatevideo/controller/ultimatevideo-my-video',
    'ultimatevideo/controller/ultimatevideo-history',
    'ultimatevideo/controller/ultimatevideo-favorite-video',
    'ultimatevideo/controller/ultimatevideo-watch-later-video',
    'ultimatevideo/controller/ultimatevideo-home',
    'ultimatevideo/controller/ultimatevideo-item-video',
    'ultimatevideo/controller/ultimatevideo-item-playlist',
    'ultimatevideo/controller/ultimatevideo-list-video',
    'ultimatevideo/controller/ultimatevideo-playlist-video',
    'ultimatevideo/controller/ultimatevideo-list-playlist',
    'ultimatevideo/controller/ultimatevideo-search-video',
    'ultimatevideo/controller/ultimatevideo-search-playlist',
    'ultimatevideo/controller/ultimatevideo-search-history',
    'ultimatevideo/controller/ultimatevideo-detail-video',
    'ultimatevideo/controller/ultimatevideo-detail-playlist',
    'ultimatevideo/controller/ultimatevideo-add-video',
    'ultimatevideo/controller/ultimatevideo-add-playlist',
    'ultimatevideo/controller/ultimatevideo-edit-playlist',
    'ultimatevideo/controller/ultimatevideo-edit-video',
    'ultimatevideo/controller/ultimatevideo-add-to-playlist',

], function() {
    angular.module('myapp.controllers')
        .controller('UltimateVideoHomeCtrl', require('ultimatevideo/controller/ultimatevideo-home'))
        .controller('UltimateVideoBrowseVideoCtrl', require('ultimatevideo/controller/ultimatevideo-browse-video'))
        .controller('UltimateVideoBrowsePlaylistCtrl', require('ultimatevideo/controller/ultimatevideo-browse-playlist'))
        .controller('UltimateVideoMyPlaylistCtrl', require('ultimatevideo/controller/ultimatevideo-my-playlist'))
        .controller('UltimateVideoMyVideoCtrl', require('ultimatevideo/controller/ultimatevideo-my-video'))
        .controller('UltimateVideoFavoriteVideoCtrl', require('ultimatevideo/controller/ultimatevideo-favorite-video'))
        .controller('UltimateVideoWatchLaterVideoCtrl', require('ultimatevideo/controller/ultimatevideo-watch-later-video'))
        .controller('UltimateVideoItemVideoCtrl',require('ultimatevideo/controller/ultimatevideo-item-video'))
        .controller('UltimateVideoItemPlaylistCtrl',require('ultimatevideo/controller/ultimatevideo-item-playlist'))
        .controller('UltimateVideoSearchVideoCtrl', require('ultimatevideo/controller/ultimatevideo-search-video'))
        .controller('UltimateVideoSearchPlaylistCtrl', require('ultimatevideo/controller/ultimatevideo-search-playlist'))
        .controller('UltimateVideoSearchHistoryCtrl', require('ultimatevideo/controller/ultimatevideo-search-history'))
        .controller('UltimateVideoDetailVideoCtrl', require('ultimatevideo/controller/ultimatevideo-detail-video'))
        .controller('UltimateVideoDetailPlaylistCtrl', require('ultimatevideo/controller/ultimatevideo-detail-playlist'))
        .controller('UltimateVideoAddVideoCtrl', require('ultimatevideo/controller/ultimatevideo-add-video'))
        .controller('UltimateVideoAddPlaylistCtrl', require('ultimatevideo/controller/ultimatevideo-add-playlist'))
        .controller('UltimateVideoEditPlaylistCtrl', require('ultimatevideo/controller/ultimatevideo-edit-playlist'))
        .controller('UltimateVideoEditVideoCtrl', require('ultimatevideo/controller/ultimatevideo-edit-video'))
        .controller('UltimateVideoAddToPlaylistCtrl', require('ultimatevideo/controller/ultimatevideo-add-to-playlist'))
        .controller('UltimateVideoHistoryCtrl', require('ultimatevideo/controller/ultimatevideo-history'))
    ;
});