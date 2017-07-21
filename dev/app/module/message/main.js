define([
    'message/controllers',
    'message/directives',
    'text!tpl/message/message-home.html',
    'text!tpl/message/conversation-add.html',
    'text!tpl/message/conversation-detail.html',
    'text!tpl/message/conversation-home.html',
    'text!tpl/message/conversation-sent.html',
    'text!tpl/message/conversation-reply.html'
], function() {

    angular.module('myapp').config(function($stateProvider, $urlRouterProvider, gettext) {
        $stateProvider
        .state('app.messageInbox', {
            module: 'message',
            url: '/messages',
            cache: false,
            history: {
                title: gettext('Messages'),
                isRoot: true
            },
            views: {
                'menuContent': {
                    template: require('text!tpl/message/conversation-home.html'),
                    controller: 'ConversationInboxCtrl'
                }
            }
        }).state('app.messageSent', {
            module: 'message',
            url: '/messages/sent',
            cache: false,
            history: {
                title: gettext('Mail'),
                isRoot: true
            },
            views: {
                'menuContent': {
                    template: require('text!tpl/message/message-home.html'),
                    controller: 'MessageHomeCtrl',
                },
                'tabContent@app.messageSent': {
                    template: require('text!tpl/message/conversation-sent.html'),
                    controller: 'ConversationSentCtrl',
                },
            }
        }).state('app.messagesadd', {
            module: 'message',
            url: '/messages/add',
            cache: false,
            history: false,
            views: {
                'menuContent': {
                    template: require('text!tpl/message/conversation-add.html'),
                    controller: 'ConversationAddCtrl',
                }
            }
        }).state('app.messagesadd_type', {
            module: 'message',
            url: '/messages/add/:sItemType/:iItemId/:sName',
            cache: false,
            history: false,
            views: {
                'menuContent': {
                    template: require('text!tpl/message/conversation-add.html'),
                    controller: 'ConversationAddCtrl',
                }
            }
        }).state('app.messagesAddModule', {
            module: 'message',
            url: '/messages/add/:sModule/:iModuleItemId/:iItemId/:sName',
            cache: false,
            history: false,
            views: {
                'menuContent': {
                    template: require('text!tpl/message/conversation-add.html'),
                    controller: 'ConversationAddCtrl',
                }
            }
        }).state('app.messageid', {
            module: 'message',
            url: '/message/:id',
            cache: false,
            views: {
                'menuContent': {
                    template: require('text!tpl/message/conversation-detail.html'),
                    controller: 'ConversationDetailCtrl',
                }
            }
        }).state('app.messagesconversationid', {
            module: 'message',
            url: '/messages_conversation/:id',
            cache: false,
            views: {
                'menuContent': {
                    template: require('text!tpl/message/conversation-detail.html'),
                    controller: 'ConversationDetailCtrl',
                }
            }
        }).state('app.messageidreply', {
            module: 'message',
            url: '/message/:id/reply',
            cache: false,
            history: false,
            views: {
                'menuContent': {
                    template: require('text!tpl/message/conversation-reply.html'),
                    controller: 'ConversationReplyCtrl',
                }
            }
        });
    });
});