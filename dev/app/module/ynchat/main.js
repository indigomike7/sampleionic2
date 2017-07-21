define([
    'ynchat/controllers',
    'ynchat/directives',
    'ynchat/services',
    'text!tpl/ynchat/ynchat-detail.html',
    'text!tpl/ynchat/ynchat-history.html',
    'text!tpl/ynchat/ynchat-home.html'
], function() {

    angular.module('myapp').config(function($stateProvider, $urlRouterProvider, gettext) {
        $stateProvider.state('app.ynchat', {
            module: 'ynchat',
            url: '/ynchat',
            cache: false,
            history: {
                title: gettext('Chat'),
                isRoot: true
            },
            views: {
                menuContent: {
                    template: require('text!tpl/ynchat/ynchat-home.html'),
                    controller: 'YnchatHomeCtrl'
                }
            }
        }).state('app.ynchatid', {
            module: 'ynchat',
            url: '/ynchat/:id',
            cache: false,
            views: {
                menuContent: ionic.Platform.isIPad() ? {
                    template: require('text!tpl/ynchat/ynchat-home.html'),
                    controller: 'YnchatHomeCtrl'
                } : {
                    template: require('text!tpl/ynchat/ynchat-detail.html'),
                    controller: 'YnchatDetailCtrl'
                }
            }
        }).state('app.ynchatidhistory', {
            module: 'ynchat',
            url: '/ynchat/:id/history',
            cache: false,
            views: {
                menuContent: {
                    template: require('text!tpl/ynchat/ynchat-history.html'),
                    controller: 'YnchatHistoryCtrl'
                }
            }
        });
    });
});