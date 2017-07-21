define([
    'photo/controller/photo-album-add',
    'photo/controller/photo-album-detail',
    'photo/controller/photo-album-edit',
    'photo/controller/photo-album-item',
    'photo/controller/photo-album-list',
    'photo/controller/photo-my-album',
    'photo/controller/photo-album-search',
    'photo/controller/photo-browse-album',
    'photo/controller/photo-browse-photo',
    'photo/controller/photo-my-photo',
    'photo/controller/photo-item',
    'photo/controller/photo-search',
    'photo/controller/photo-detail',
    'photo/controller/photo-home',
    'photo/controller/photo-thumb'
], function() {
    angular.module('myapp.controllers')
        .controller('PhotoHomeCtrl', require('photo/controller/photo-home'))
        .controller('PhotoAlbumEditCtrl', require('photo/controller/photo-album-edit'))
        .controller('PhotoAlbumSearchCtrl', require('photo/controller/photo-album-search'))
        .controller('PhotoAlbumAddCtrl', require('photo/controller/photo-album-add'))
        .controller('PhotoAlbumDetailCtrl', require('photo/controller/photo-album-detail'))
        .controller('PhotoItemCtrl', require('photo/controller/photo-item'))
        .controller('PhotoAlbumListCtrl', require('photo/controller/photo-album-list'))
        .controller('PhotoDetailCtrl', require('photo/controller/photo-detail'))
        .controller('PhotoAlbumItemCtrl', require('photo/controller/photo-album-item'))
        .controller('PhotoBrowseAlbumCtrl', require('photo/controller/photo-browse-album'))
        .controller('PhotoMyPhotoCtrl', require('photo/controller/photo-my-photo'))
        .controller('PhotoBrowsePhotoCtrl', require('photo/controller/photo-browse-photo'))
        .controller('PhotoMyAlbumCtrl', require('photo/controller/photo-my-album'))
        .controller('PhotoSearchCtrl', require('photo/controller/photo-search'))
        .controller('PhotoThumbCtrl', require('photo/controller/photo-thumb'));
});