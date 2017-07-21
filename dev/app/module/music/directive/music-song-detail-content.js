define([
    'text!tpl/music/music-song-detail-content.html'
], function(text) {

    return function() {

        return {
            restrict: 'E',
            template: text
        };
    };
});