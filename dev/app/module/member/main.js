define([
    'member/controllers',
    'member/directives',
    'text!tpl/member/member-browse.html',
], function() {
    angular.module('myapp')
    .config(function($stateProvider, $urlRouterProvider, gettext) {
        $stateProvider.state('app.memberBrowse', {
            module: 'member',
            url: '/members',
            cache: false,
            history: {
                title: gettext('Members'),
                isRoot: true
            },
            views: {
                'menuContent': {
                    template: require('text!tpl/member/member-browse.html'),
                    controller: 'MemberBrowseCtrl',
                }
            }
        });
    });
});