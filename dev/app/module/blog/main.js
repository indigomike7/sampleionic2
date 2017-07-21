define([
    'blog/controllers',
    'blog/directives',
    'blog/plugin/activity',
    'text!tpl/blog/blog-add.html',
    'text!tpl/blog/blog-all.html',
    'text!tpl/blog/blog-detail.html',
    'text!tpl/blog/blog-edit.html',
    'text!tpl/blog/blog-home.html',
    'text!tpl/blog/blog-my.html'
], function() {

    angular.module('myapp').config(function($stateProvider, $urlRouterProvider, gettext) {
        $stateProvider.state('app.blogs', {
            module: 'blog',
            url: '/blogs',
            cache: false,
            history: {
                title: gettext('Blogs'),
                isRoot: true
            },
            views: {
                'menuContent': {
                    template: require('text!tpl/blog/blog-home.html'),
                    controller: 'BlogHomeCtrl'
                },
                'tabContent@app.blogs': {
                    template: require('text!tpl/blog/blog-all.html'),
                    controller: 'BlogAllCtrl'
                }
            }
        }).state('app.blogsmy', {
            module: 'blog',
            url: '/blogs/my',
            cache: false,
            history: {
                title: gettext('Blogs'),
                isRoot: true
            },
            views: {
                'menuContent': {
                    template: require('text!tpl/blog/blog-home.html'),
                    controller: 'BlogHomeCtrl'
                },
                'tabContent@app.blogsmy': {
                    template: require('text!tpl/blog/blog-my.html'),
                    controller: 'BlogMyCtrl'
                }
            }
        }).state('app.blogsadd', {
            module: 'blog',
            url: '/blogs/add',
            cache: false,
            history: false,
            views: {
                'menuContent': {
                    template: require('text!tpl/blog/blog-add.html'),
                    controller: 'BlogAddCtrl'
                }
            }
        }).state('app.blogsaddtype', {
            module: 'blog',
            url: '/blogs/add/:sParentType/:iParentId',
            cache: false,
            history: false,
            views: {
                'menuContent': {
                    template: require('text!tpl/blog/blog-add.html'),
                    controller: 'BlogAddCtrl'
                }
            }
        }).state('app.blogid', {
            module: 'blog',
            url: '/blog/:id',
            cache: false,
            views: {
                'menuContent': {
                    template: require('text!tpl/blog/blog-detail.html'),
                    controller: 'BlogDetailCtrl'
                }
            }
        }).state('app.blogidedit', {
            module: 'blog',
            url: '/blog/:id/edit',
            cache: false,
            history: false,
            views: {
                'menuContent': {
                    template: require('text!tpl/blog/blog-edit.html'),
                    controller: 'BlogEditCtrl'
                }
            }
        });
    });
});