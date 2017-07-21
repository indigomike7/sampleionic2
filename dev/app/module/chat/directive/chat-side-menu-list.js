define([
    'text!tpl/chat/chat-side-menu-list.html'
], function() {
    return function() {
        return {
            restrict: 'E',
            template: require('text!tpl/chat/chat-side-menu-list.html'),
            controller: 'ChatSideMenuListCtrl'
        };
    };
});