define([
    'comment/controller/comment-list',
    'comment/controller/comment-item',
], function() {

    angular.module('myapp.controllers')
        .controller('CommentItemCtrl', require('comment/controller/comment-item'))
        .controller('CommentListCtrl', require('comment/controller/comment-list'))
        ;
});