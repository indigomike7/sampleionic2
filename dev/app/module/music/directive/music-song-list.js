define([
    'text!tpl/music/music-song-list.html'
], function(text) {

    return function() {

        return {
            restrict: 'E',
            template: text,
            controller: 'MusicSongListCtrl',
        };
    };
});