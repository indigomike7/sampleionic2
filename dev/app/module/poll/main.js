define([
    'poll/controllers',
    'poll/directives',
    'poll/plugin/activity',
    'text!tpl/poll/poll-add.html',
    'text!tpl/poll/poll-browse-poll.html',
    'text!tpl/poll/poll-detail.html',
    'text!tpl/poll/poll-edit.html',
    'text!tpl/poll/poll-home.html',
    'text!tpl/poll/poll-list.html',
    'text!tpl/poll/poll-my-poll.html',
    'text!tpl/poll/poll-search.html',
], function() {
    
    angular.module('myapp')
    .config(function($stateProvider, $urlRouterProvider, gettext) {
        $stateProvider
        .state('app.PollDetail', {
            module: 'poll',
            url: '/poll/:iPollId',
            cache: false,
            history: ionic.Platform.isIPad() ? {
                title: gettext('Polls'),
                isRoot: true
            } : {},
            views: {
                'menuContent': ionic.Platform.isIPad() ? {
                    template: require('text!tpl/poll/poll-home.html'),
                    controller: 'PollHomeCtrl'
                } : {
                    template: require('text!tpl/poll/poll-detail.html'),
                    controller: 'PollDetailCtrl'
                }
            }
        })
        .state('app.PollEdit', {
            module: 'poll',
            url: '/polls/edit/:iPollId',
            cache: false,
            history: false,
            views: {
                'menuContent': {
                    template: require('text!tpl/poll/poll-edit.html'),
                    controller: 'PollEditCtrl'
                }
            }
        })
        .state('app.PollAdd', {
            module: 'poll',
            url: '/polls/add',
            cache: false,
            history: false,
            views: {
                'menuContent': {
                    template: require('text!tpl/poll/poll-add.html'),
                    controller: 'PollAddCtrl'
                }
            }
        })
        .state('app.PollBrowse', {
            module: 'poll',
            url: '/polls',
            cache: false,
            history: {
                title: gettext('Polls'),
                isRoot: true
            },
            views: {
                'menuContent': {
                    template: require('text!tpl/poll/poll-home.html'),
                    controller: 'PollHomeCtrl'
                },
                'tabContent@app.PollBrowse': {
                    template: require('text!tpl/poll/poll-browse-poll.html'),
                    controller: 'PollBrowsePollCtrl'
                }
            }
        })
         .state('app.PollMy', {
            module: 'poll',
            url: '/polls/my',
            cache: false,
            history: {
                title: gettext('Polls'),
                isRoot: true
            },
            views: {
                'menuContent': {
                    template: require('text!tpl/poll/poll-home.html'),
                    controller: 'PollHomeCtrl'
                },
                'tabContent@app.PollMy': {
                    template: require('text!tpl/poll/poll-my-poll.html'),
                    controller: 'PollMyPollCtrl'
                }
            }
        });
    });
});