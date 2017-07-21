define([
    'text!tpl/music/music-song-search.html'
], function(text) {

    return function() {

        return {
            restrict: 'E',
            template: text,
            controller: 'MusicSongSearchCtrl'
        };
    };
});