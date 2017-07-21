define([
    'text!tpl/ultimatevideo/ultimatevideo-search-playlist.html'
], function(text) {

    return function() {
        return {
            restrict: 'E',
            template: text,
            controller: 'UltimateVideoSearchPlaylistCtrl'
        };
    };
});