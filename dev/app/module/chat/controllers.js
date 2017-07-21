define([
	'chat/controller/chat-detail',
    'chat/controller/chat-home',
    'chat/controller/chat-list',
    'chat/controller/chat-message-list',
    'chat/controller/chat-ocn',
    'chat/controller/chat-side-menu-list',
    'chat/controller/chat-item-message'
], function() {

    angular.module('myapp.controllers')
	    .controller('ChatDetailCtrl', require('chat/controller/chat-detail'))
        .controller('ChatHomeCtrl', require('chat/controller/chat-home'))
        .controller('ChatListCtrl', require('chat/controller/chat-list'))
        .controller('ChatMessageListCtrl', require('chat/controller/chat-message-list'))
        .controller('ChatOcnCtrl', require('chat/controller/chat-ocn'))
        .controller('ChatSideMenuListCtrl', require('chat/controller/chat-side-menu-list'))
        .controller('ChatItemMessageCtrl', require('chat/controller/chat-item-message'));
});