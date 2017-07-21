define([
	'forum/controllers',
	'forum/directives',
    'forum/plugin/activity',
    'text!tpl/forum/forum-browse.html',
    'text!tpl/forum/forum-detail.html',
    'text!tpl/forum/forum-home.html',
    'text!tpl/forum/forum-post-edit.html',
    'text!tpl/forum/forum-post-search.html',
    'text!tpl/forum/forum-thread-add.html',
    'text!tpl/forum/forum-thread-browse.html',
    'text!tpl/forum/forum-thread-detail.html',
    'text!tpl/forum/forum-thread-edit.html',
    'text!tpl/forum/forum-thread-reply.html',
    'text!tpl/forum/forum-thread-search.html'
], function() {

    angular.module('myapp').config(function($stateProvider, $urlRouterProvider, gettext) {
        $stateProvider.state('app.forums', {
            module: 'forum',
            url: '/forums',
            cache: false,
            history: {
                title: gettext('Forums'),
                isRoot: true
            },
            views: {
                'menuContent': {
                    template: require('text!tpl/forum/forum-home.html'),
                    controller: 'ForumHomeCtrl',
                },
                'tabContent@app.forums': {
                    template: require('text!tpl/forum/forum-browse.html'),
                    controller: 'ForumBrowseCtrl',
                }
            }
        }).state('app.forumThreads', {
            module: 'forum',
            url: '/forum_threads/:sView',
            cache: false,
            history: {
                title: gettext('Forums'),
                isRoot: true
            },
            views: {
                'menuContent': {
                    template: require('text!tpl/forum/forum-home.html'),
                    controller: 'ForumHomeCtrl',
                },
                'tabContent@app.forumThreads': {
                    template: require('text!tpl/forum/forum-thread-browse.html'),
                    controller: 'ForumThreadBrowseCtrl',
                }
            }
        }).state('app.forumThreadsSearch', {
            module: 'forum',
            url: '/forum_threads/search/:sHash',
            cache: false,
            history: {
                title: gettext('Forums')
            },
            views: {
                'menuContent': {
                    template: require('text!tpl/forum/forum-home.html'),
                    controller: 'ForumHomeCtrl',
                },
                'tabContent@app.forumThreadsSearch': {
                    template: require('text!tpl/forum/forum-thread-search.html'),
                    controller: 'ForumThreadSearchCtrl',
                }
            }
        }).state('app.forumPostsSearch', {
            module: 'forum',
            url: '/forum_posts/search/:sHash',
            cache: false,
            history: {
                title: gettext('Forums')
            },
            views: {
                'menuContent': {
                    template: require('text!tpl/forum/forum-home.html'),
                    controller: 'ForumHomeCtrl',
                },
                'tabContent@app.forumPostsSearch': {
                    template: require('text!tpl/forum/forum-post-search.html'),
                    controller: 'ForumPostSearchCtrl',
                }
            }
        }).state('app.forum', {
            module: 'forum',
            url: '/forum/:iForumId',
            cache: false,
            views: {
                'menuContent': {
                    template: require('text!tpl/forum/forum-detail.html'),
                    controller: 'ForumDetailCtrl',
                }
            }
        }).state('app.forumThreadAdd', {
            module: 'forum',
            url: '/forum/:iForumId/thread_add',
            cache: false,
            history: false,
            views: {
                'menuContent': {
                    template: require('text!tpl/forum/forum-thread-add.html'),
                    controller: 'ForumThreadAddCtrl',
                }
            }
        }).state('app.forumThread', {
            module: 'forum',
            url: '/forum_thread/:iThreadId',
            cache: false,
            views: {
                'menuContent': {
                    template: require('text!tpl/forum/forum-thread-detail.html'),
                    controller: 'ForumThreadDetailCtrl',
                }
            }
        }).state('app.forumPost', {
            module: 'forum',
            url: '/forum_post/:iThreadId',
            cache: false,
            views: {
                'menuContent': {
                    template: require('text!tpl/forum/forum-thread-detail.html'),
                    controller: 'ForumThreadDetailCtrl',
                }
            }
        }).state('app.forumThreadEdit', {
            module: 'forum',
            url: '/forum_thread/:iThreadId/edit',
            cache: false,
            history: false,
            views: {
                'menuContent': {
                    template: require('text!tpl/forum/forum-thread-edit.html'),
                    controller: 'ForumThreadEditCtrl',
                }
            }
        }).state('app.forumThreadReply', {
            module: 'forum',
            url: '/forum_thread/:iThreadId/reply',
            cache: false,
            history: false,
            views: {
                'menuContent': {
                    template: require('text!tpl/forum/forum-thread-reply.html'),
                    controller: 'ForumThreadReplyCtrl',
                }
            }
        }).state('app.forumThreadQuote', {
            module: 'forum',
            url: '/forum_thread/:iThreadId/reply/:iPostId',
            cache: false,
            history: false,
            views: {
                'menuContent': {
                    template: require('text!tpl/forum/forum-thread-reply.html'),
                    controller: 'ForumThreadReplyCtrl',
                }
            }
        }).state('app.forumPostEdit', {
            module: 'forum',
            url: '/forum_post/:iPostId/edit',
            cache: false,
            history: false,
            views: {
                'menuContent': {
                    template: require('text!tpl/forum/forum-post-edit.html'),
                    controller: 'ForumPostEditCtrl',
                }
            }
        });
    });
});