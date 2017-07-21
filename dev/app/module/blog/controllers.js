define([
    'blog/controller/blog-add',
    'blog/controller/blog-all',
    'blog/controller/blog-detail',
    'blog/controller/blog-edit',
    'blog/controller/blog-form',
    'blog/controller/blog-home',
    'blog/controller/blog-item',
    'blog/controller/blog-list',
    'blog/controller/blog-my',
    'blog/controller/blog-search'
], function() {

    angular.module('myapp.controllers')
        .controller('BlogAddCtrl', require('blog/controller/blog-add'))
	    .controller('BlogAllCtrl', require('blog/controller/blog-all'))
        .controller('BlogDetailCtrl', require('blog/controller/blog-detail'))
        .controller('BlogEditCtrl', require('blog/controller/blog-edit'))
        .controller('BlogFormCtrl', require('blog/controller/blog-form'))
        .controller('BlogHomeCtrl', require('blog/controller/blog-home'))
        .controller('BlogItemCtrl', require('blog/controller/blog-item'))
        .controller('BlogListCtrl', require('blog/controller/blog-list'))
        .controller('BlogMyCtrl', require('blog/controller/blog-my'))
        .controller('BlogSearchCtrl', require('blog/controller/blog-search'));
});