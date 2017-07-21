define([
    'like/directive/like-stat-dir',
], function(likeStatDir) {

    angular.module('myapp.directives')
        .directive('likeStatDir', likeStatDir);
});