define([
    'friend/controllers',
    'friend/directives',
    'friend/plugin/activity',
    'text!tpl/friend/friend-autocomplete-list.html',
    'text!tpl/friend/friend-browse.html',
    'text!tpl/friend/friend-list.html',
    'text!tpl/friend/friend-search.html',
], function() {
    angular.module('myapp')
    .config(function($stateProvider, $urlRouterProvider, gettext) {
        $stateProvider.state('app.browseFriends', {
            module: 'friend',
            url: '/friends',
            cache: false,
            history: {
                title: gettext('Friends'),
                isRoot: true
            },
            views: {
                'menuContent': {
                    template: require('text!tpl/friend/friend-browse.html'),
                    controller: 'FriendBrowseFriendCtrl',
                }
            }
        });
    });
});