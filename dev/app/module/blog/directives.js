define([
    'blog/directive/blog-all-list-dir',
    'blog/directive/blog-my-list-dir',
    'blog/directive/blog-search-dir'
], function() {

    angular.module('myapp.directives')
        .directive('blogAllListDir', require('blog/directive/blog-all-list-dir'))
        .directive('blogMyListDir', require('blog/directive/blog-my-list-dir'))
        .directive('blogSearchDir', require('blog/directive/blog-search-dir'));
});