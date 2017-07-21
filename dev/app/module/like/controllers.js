define([
    'like/controller/like-stat',
    'like/controller/liked-list'
], function(LikeStatCtrl, LikedListCtrl) {

    angular.module('myapp.controllers')
        .controller('LikeStatCtrl', LikeStatCtrl)
        .controller('LikedListCtrl', LikedListCtrl);
});