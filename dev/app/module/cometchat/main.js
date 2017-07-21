define([
    'cometchat/controllers',
    'cometchat/directives',
    'cometchat/services',
    'text!tpl/cometchat/cometchat-home.html'
], function() {

    angular.module('myapp').config(function($stateProvider, $urlRouterProvider, gettext) {
        $stateProvider.state('app.cometchat', {
            module: 'cometchat',
            url: '/cometchat',
            cache: false,
            history: {
                title: gettext('Chat'),
                isRoot: true
            },
            views: {
                menuContent: {
                    template: require('text!tpl/cometchat/cometchat-home.html'),
                    controller: 'CometchatHomeCtrl'
                }
            }
        }).state('app.cometchatid', {
            module: 'cometchat',
            url: '/cometchat/:id',
            cache: false,
            history: {
                title: gettext('Chat'),
                isRoot: true
            },
            views: {
                menuContent: {
                    template: require('text!tpl/cometchat/cometchat-home.html'),
                    controller: 'CometchatHomeCtrl'
                }
            }
        });
    });
});