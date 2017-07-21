define([
    'activity/controllers',
    'activity/directives',
    'activity/services',
    'text!tpl/activity/feed-add.html',
    'text!tpl/activity/feed-detail.html',
    'text!tpl/activity/newsfeeds.html'
],function(Controllers, Directives, Services, feedAddTpl, feedDetailTpl, newsfeedsTpl){
    
    angular.module('myapp')
    .config(function($stateProvider, $urlRouterProvider, gettext) {
        $stateProvider.state('app.feedadd', {
            module: 'activity',
            url: '/feed/add/:action',
            cache: false,
            history: false,
            views: {
                'menuContent': {
                    template: feedAddTpl,
                    controller: 'FeedAddController',
                }
            }
        }).state('app.feedadditem', {
            module: 'activity',
            url: '/feed/add/:action/:itemType/:itemId',
            cache: false,
            history: false,
            views: {
                'menuContent': {
                    template: feedAddTpl,
                    controller: 'FeedAddController',
                }
            }
        }).state('app.newsfeed', {
            module: 'activity',
            url: '/newsfeed',
            history: {
                title: gettext('News Feed'),
                isRoot: true
            },
            views: {
                'menuContent': {
                    template: newsfeedsTpl,
                    controller: 'ActivityNewsFeedController',
                }
            }
        }).state('app.newsfeedHashTag', {
            module: 'activity',
            url: '/newsfeed/hashtag/:hashtag',
            history: {
                title: gettext('News Feed'),
                isRoot: true
            },
            views: {
                'menuContent': {
                    template: newsfeedsTpl,
                    controller: 'ActivityNewsFeedController',
                }
            }
        }).state('app.feed', {
            module: 'activity',
            url: '/feed/:id/:parentModuleId',
            cache: false,
            views: {
                'menuContent': {
                    template: feedDetailTpl,
                    controller: 'FeedDetailController',
                }
            }
        }).state('app.userstatusid', {
            module: 'activity',
            url: '/user_status/:id',
            cache: false,
            views: {
                'menuContent': {
                    template: feedDetailTpl,
                    controller: 'FeedDetailController',
                }
            }
        });
    });
});
