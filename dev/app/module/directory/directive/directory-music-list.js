define([
    'text!tpl/directory/directory-music-list.html'
], function(text) {
    return function() {
        return {
            restrict: 'E',
            replace: true,
            template: text,
            controller: 'DirectoryMusicListCtrl'
        };
    };
});
