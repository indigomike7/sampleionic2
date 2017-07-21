define([
    'myapp',
    'core/controllers',
    'core/directives',
    'core/filters',
    'core/services',
    'core/plugin/activity',
    'core/plugin/message',
    'text!tpl/core/core-init.html',
    'text!tpl/core/core-menu.html',
    'text!tpl/core/core-private-page.html',
], function() {
    angular.module('myapp').config(function($stateProvider, $urlRouterProvider, gettext) {
        $stateProvider.state('app', {
            url: "/app",
            cache: false,
            abstract: true,
            template: require('text!tpl/core/core-menu.html'),
            controller: 'AppCtrl'
        }).state('app.init', {
            url: '/core/init',
            cache: false,
            history: false,
            views: {
                'menuContent': {
                    template: require('text!tpl/core/core-init.html'),
                    controller: 'CoreInitCtrl',
                }
            }
        }).state('app.PrivatePage', {
            url: '/core/private-page',
            cache: false,
            history: false,
            views: {
                'menuContent': {
                    template: require('text!tpl/core/core-private-page.html'),
                    controller: 'CorePrivatePageCtrl',
                }
            }
        });
    });

    angular.module('myapp').run(function($site, $location, $state) {
        var current = $location.path();
        $site.home = current || $site.home;

        $state.go('app.init');
    });
});