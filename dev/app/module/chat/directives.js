define([
    'chat/directive/chat-list-dir',
    'chat/directive/chat-message-list-dir',
    'chat/directive/chat-search-dir',
    'chat/directive/chat-ocn-dir',
    'chat/directive/chat-side-menu',
    'chat/directive/chat-side-menu-list'
], function() {

    angular.module('myapp.directives')
        .directive('chatListDir', require('chat/directive/chat-list-dir'))
        .directive('chatMessageListDir', require('chat/directive/chat-message-list-dir'))
        .directive('chatSearchDir', require('chat/directive/chat-search-dir'))
        .directive('chatOcnDir', require('chat/directive/chat-ocn-dir'))
        .directive('chatSideMenu', require('chat/directive/chat-side-menu'))
        .directive('chatSideMenuList', require('chat/directive/chat-side-menu-list'));
});