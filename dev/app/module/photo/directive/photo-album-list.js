define([
    'photo/controller/photo-album-list',
    'text!tpl/photo/photo-album-list.html'
], function(Controller, text) {

    return function() {

        return {
            restrict: 'E',
            template: text,
            controller: 'PhotoAlbumListCtrl'
        };
    };
});