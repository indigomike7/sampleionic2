define([
    'dislike/controller/disliked-list'
], function() {

    angular.module('myapp.controllers')
        .controller('DislikedListCtrl', require('dislike/controller/disliked-list'));
});