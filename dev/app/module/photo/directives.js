define([
    'photo/directive/photo-album-search',
    'photo/directive/photo-search',
    'photo/directive/photo-album-list',
    'photo/directive/photo-list-simple',
    'photo/directive/photo-list',
    'photo/directive/photo-thumb-dir',
],function(){
    angular.module('myapp.directives')
    .directive('photoAlbumSearchDir', require('photo/directive/photo-album-search'))
    .directive('photoAlbumListDir', require('photo/directive/photo-album-list'))
    .directive('photoListDir', require('photo/directive/photo-list'))
    .directive('photoSearchDir', require('photo/directive/photo-search'))
    .directive('photoListSimpleDir', require('photo/directive/photo-list-simple'))
    .directive('photoThumbDir', require('photo/directive/photo-thumb-dir'))
    ;
});
