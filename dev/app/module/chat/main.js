define([
    'chat/controllers',
    'chat/directives',
    'chat/services',
    'text!tpl/chat/chat-home.html'
], function() {

    angular.module('myapp').config(function($stateProvider, $urlRouterProvider, gettext) {
        $stateProvider.state('app.chat', {
            module: 'chat',
            url: '/chat',
            cache: false,
            history: {
                title: gettext('Chat'),
                isRoot: true
            },
            views: {
                menuContent: {
                    template: require('text!tpl/chat/chat-home.html'),
                    controller: 'ChatHomeCtrl'
                }
            }
        }).state('app.chatid', {
            module: 'chat',
            url: '/chat/:id',
            cache: false,
            history: {
                title: gettext('Chat'),
                isRoot: true
            },
            views: {
                menuContent: {
                    template: require('text!tpl/chat/chat-home.html'),
                    controller: 'ChatHomeCtrl'
                }
            }
        });
    });
});